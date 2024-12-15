<?php
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
require(CONFIG['SERVER_ROOT'] . '/classes/top10_movies.class.php');
require(CONFIG['SERVER_ROOT'] . '/classes/forums.class.php');
Text::$TOC = true;

$NewsCount = 3;
if (!$News = $Cache->get_value('news')) {
    $ForumID = CONFIG['NEWS_FORUM_ID'];
    $DB->query("SELECT `ID`, `Title`, `CreatedTime` , `IsSticky` FROM `forums_topics` WHERE `ForumID` = '$ForumID' AND  `IsNotice` = '1'  ORDER BY `IsSticky` DESC, `CreatedTime` DESC LIMIT $NewsCount");
    $topics = $DB->to_array(false, MYSQLI_NUM, false);
    $News = array();
    foreach ($topics as $key => $topic) {
        $ID = $topic[0];
        $Title = $topic[1];
        $DB->query("SELECT `Body` FROM `forums_posts` WHERE `TopicID` = '$ID'");
        $Bodys = $DB->to_array(false, MYSQLI_NUM, false);
        $Body = $Bodys[0][0];
        $Time = $topic[2];
        $IsSticky = $topic[3];
        $News[] = array($ID, $Title, $Body, $Time, $IsSticky);
    }
    $Cache->cache_value('news', $News, 3600 * 24 * 30);
    if (count($News) > 0) {
        $Cache->cache_value('news_latest_id', $News[0][0], 0);
        $Cache->cache_value('news_latest_title', $News[0][1], 0);
    }
}
if ($_SERVER['REQUEST_URI'] === '/index.php') {
    if (count($News) > 0 && $LoggedUser['LastReadNews'] != $News[0][0]) {
        $Cache->begin_transaction("user_info_heavy_$UserID");
        $Cache->update_row(false, array('LastReadNews' => $News[0][0]));
        $Cache->commit_transaction(0);
        $DB->query("
			UPDATE users_info
			SET LastReadNews = '" . $News[0][0] . "'
			WHERE UserID = $UserID");
        $LoggedUser['LastReadNews'] = $News[0][0];
    }
}

View::show_header(t('server.index.index'), 'comments', 'PageHome');
?>

<div class="LayoutMainSidebar">
    <div class="Sidebar LayoutMainSidebar-sidebar">
        <!-- Poll -->
        <?
        if (($TopicID = $Cache->get_value('polls_featured')) === false) {
            $DB->query("
		SELECT TopicID
		FROM forums_polls
		ORDER BY Featured DESC
		LIMIT 1");
            list($TopicID) = $DB->next_record();
            $Cache->cache_value('polls_featured', $TopicID, 0);
        }
        if ($TopicID) {
            if (($Poll = $Cache->get_value("polls_$TopicID")) === false) {
                $DB->query("
			SELECT Question, Answers, Featured, Closed, MaxCount
			FROM forums_polls
			WHERE TopicID = '$TopicID'");
                list($Question, $Answers, $Featured, $Closed, $MaxCount) = $DB->next_record(MYSQLI_NUM, array(1));
                $Answers = unserialize($Answers);
                $DB->query("
			SELECT Vote, COUNT(UserID)
			FROM forums_polls_votes
			WHERE TopicID = '$TopicID'
				AND Vote != '0'
			GROUP BY Vote");
                $VoteArray = $DB->to_array(false, MYSQLI_NUM);
                $Votes = array();
                foreach ($VoteArray as $VoteSet) {
                    list($Key, $Value) = $VoteSet;
                    $Votes[$Key] = $Value;
                }
                for ($i = 1, $il = count($Answers); $i <= $il; ++$i) {
                    if (!isset($Votes[$i])) {
                        $Votes[$i] = 0;
                    }
                }
                $Cache->cache_value("polls_$TopicID", array($Question, $Answers, $Votes, $Featured, $Closed, $MaxCount), 0);
            } else {
                list($Question, $Answers, $Votes, $Featured, $Closed, $MaxCount) = $Poll;
            }
            if (!$Closed) {
                if (!empty($Votes)) {
                    $TotalVotes = array_sum($Votes);
                    $MaxVotes = max($Votes);
                    $DB->query("SELECT count(distinct `UserID`) FROM `forums_polls_votes` WHERE `TopicID`='$TopicID' and vote!=0");
                    list($PeopleCount) = $DB->next_record();
                } else {
                    $TotalVotes = 0;
                    $MaxVotes = 0;
                    $PeopleCount = 0;
                }
                $DB->query("
		SELECT Vote
		FROM forums_polls_votes
		WHERE UserID = '" . $LoggedUser['ID'] . "'
			AND TopicID = '$TopicID'");
                $UserResponses = $DB->to_array();
                $BlankVote = false;
                foreach ($UserResponses as $UserResponse) {
                    $UserResponse = $UserResponse[0];
                    if (!empty($UserResponse) && $UserResponse != 0) {
                        $Answers[$UserResponse] = '&raquo; ' . $Answers[$UserResponse];
                    } else {
                        if (!empty($UserResponse) && $RevealVoters) {
                            $Answers[$UserResponse] = '&raquo; ' . $Answers[$UserResponse];
                        }
                    }
                    if (!$UserResponse) {
                        $BlankVote = true;
                    }
                }
                $PollStatus = $Closed ? ' [' . t('server.forums.closed') . ']' : ''
        ?>
                <div class="SidebarItemPoll SidebarItem Box is-limitHeight">
                    <div class="SidebarItem-header Box-header">
                        <?= t('server.index.poll') ?><?= $PollStatus ?>
                    </div>
                    <div class="SidebarItem-body Box-body">
                        <div class="Poll">
                            <a class="Poll-question" href="forums.php?action=viewthread&amp;threadid=<?= $TopicID ?>">
                                <?= display_str($Question) . " (" . t('server.forums.limited', ['Values' => [$MaxCount]])  .  ")" ?>
                            </a>
                            <? if ($UserResponse !== null || $Closed) { ?>
                                <ul class="Poll-answers">
                                    <? foreach ($Answers as $i => $Answer) {
                                        if ($TotalVotes > 0) {
                                            $Ratio = $Votes[$i] / $MaxVotes;
                                            $Percent = $Votes[$i] / $TotalVotes;
                                        } else {
                                            $Ratio = 0;
                                            $Percent = 0;
                                        }
                                    ?>
                                        <li class="Poll-answerItem">
                                            <div class="Poll-answerText"><?= display_str($Answers[$i]) ?> (<?= $Votes[$i] . ", " . number_format($Percent * 100, 2) ?>%)</div>
                                            <progress class="Progress" value="<?= $Ratio ?>"></progress>
                                        </li>
                                    <? } ?>
                                </ul>
                                <div class="Poll-count">
                                    <?= t('server.forums.votes') ?>: <?= number_format($TotalVotes) ?>, <?= t('server.forums.voters') ?>: <?= $PeopleCount ?>
                                </div>
                            <?  } else { ?>
                                <div id="PollContainer">
                                    <form class="Poll-voteForm" name="poll" id="poll" action="">
                                        <input type="hidden" name="action" value="poll" />
                                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                        <input type="hidden" name="topicid" value="<?= $TopicID ?>" />
                                        <ul class="Poll-answers">
                                            <? foreach ($Answers as $i => $Answer) { ?>
                                                <li class="Poll-answerItem Checkbox">
                                                    <input class="Input" type="checkbox" name="vote[]" id="answer_<?= $i ?>" value="<?= $i ?>" onclick="PollCount(<?= $MaxCount ?>)" />
                                                    <label class="Checkbox-label" for="answer_<?= $i ?>">
                                                        <?= display_str($Answers[$i]) ?>
                                                    </label>
                                                </li>
                                            <? } ?>
                                            <li class="Poll-answerItem Checkbox">
                                                <input class="Input" type="checkbox" name="vote[]" id="answer_0" value="0" onclick="PollCount(0)" />
                                                <label class="Checkbox-label" for="answer_0"><?= t('server.index.blank') ?></label>
                                            </li>
                                        </ul>
                                        <input class="Poll-voteFormSubmit Button" type="button" onclick="ajax.post('index.php', 'poll', function(response) { $('#PollContainer').raw().innerHTML = response } );" value='<?= t('server.index.vote') ?>' />
                                    </form>
                                </div>
                            <?  } ?>
                        </div>
                    </div>
                </div>
            <? } ?>
        <?  } ?>

        <!-- Upload rank -->
        <? include('upload_rank.php'); ?>

        <!-- Featured Movie -->
        <? include('feature_movie.php'); ?>

        <!-- Forum Latest Topic -->
        <?
        $LatestThread = Forums::latest_thread();
        if ($LatestThread) {
        ?>
            <div class="SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <a href="forum.php"><?= t('server.index.latest_thread') ?></a>
                </div>
                <ul class="SidebarItem-body Box-body SidebarList is-ordered">
                    <?
                    foreach ($LatestThread as $Thread) {
                        $Title = $Thread['Title'];
                        $ThreadID = $Thread['ID'];
                        $PostID = $Thread['LastPostID'];
                    ?>
                        <li class="SidebarList-item">
                            <a href="forums.php?action=viewthread&threadid=<?= $ThreadID ?>&postid=<?= $PostID ?>#post<?= $PostID ?>"><?= $Title ?></a>
                        </li>
                    <?
                    }
                    ?>
                </ul>
            </div>
        <?  } ?>


        <? if (is_array(CONFIG['INDEX_FORUM_IDS'])) {

            foreach (CONFIG['INDEX_FORUM_IDS'] as $ForumId) {

                if (Forums::check_forumperm($ForumId)) {
                    $ForumsInfo = Forums::get_forum_info($ForumId);
        ?>

                    <div class="SidebarItemStaffBlog SidebarItem Box">
                        <div class="SidebarItem-header Box-header">
                            <a href="forums.php?action=viewforum&forumid=<?= $ForumId ?>"><?= $ForumsInfo['Name'] ?></a>
                        </div>

                        <?
                        $Forum = $Cache->get_value("forums_index_$ForumId");
                        if (!isset($Forum) || !is_array($Forum)) {
                            $DB->query("
                      SELECT
                        ID,
                        Title,
                        CreatedTime
                      FROM forums_topics
                      WHERE ForumID = '$ForumId'
                      ORDER BY CreatedTime DESC
                      LIMIT 5"); // Can be cached until someone makes a new post
                            $Forum = $DB->to_array();
                            $Cache->cache_value("forums_index_$ForumId", $Forum);
                        }
                        ?>
                        <ul class="SidebarItem-body Box-body SidebarList is-ordered">
                            <?
                            $End = min(count($Forum), 5);
                            for ($i = 0; $i < $End; $i++) {
                                list($TopicID, $Title) = $Forum[$i];
                            ?>
                                <li class="SidebarList-item">
                                    <a href="forums.php?action=viewthread&threadid=<?= $TopicID ?>"><?= $Title ?></a>
                                </li>
                            <?
                            }
                            ?>
                        </ul>
                    </div>

        <?php }
            }
        } ?>

        <!-- Site History -->
        <?
        include('contest_leaderboard.php');
        if (CONFIG['ENABLE_SITEHISTORY']) {
            SiteHistoryView::render_recent_sidebar(SiteHistory::get_events(null, null, null, null, null, null, 5));
        }
        ?>

        <!-- Stats -->
        <div class="SidebarItemStats SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <div class="SidebarItem-headerTitle">
                    <?= t('server.index.stats') ?>
                </div>
                <div class="SidebarItem-headerActions">

                </div>
            </div>
            <ul class="SidebarItem-body Box-body SidebarList">
                <? if (CONFIG['USER_LIMIT'] > 0) { ?>
                    <li><?= t('server.index.user_limit') ?>: <?= number_format(CONFIG['USER_LIMIT']) ?></li>
                <?
                }
                if (($UserCount = $Cache->get_value('stats_user_count')) === false) {
                    $DB->query("
		SELECT COUNT(ID)
		FROM users_main
		WHERE Enabled = '1'");
                    list($UserCount) = $DB->next_record();
                    $Cache->cache_value('stats_user_count', $UserCount, 3600 * 24); //inf cache
                }
                $UserCount = (int)$UserCount;
                ?>
                <li class="SidebarList-item">
                    <?= t('server.index.enable_users') ?>:
                    <?= number_format($UserCount) ?>&nbsp;
                    <a href="stats.php?action=users" class="brackets"><?= t('server.index.details') ?></a>
                </li>
                <?
                if (($UserStats = $Cache->get_value('stats_users')) === false) {
                    $DB->query("
		SELECT COUNT(ID)
		FROM users_main
		WHERE Enabled = '1'
			AND LastAccess > '" . time_minus(3600 * 24) . "'");
                    list($UserStats['Day']) = $DB->next_record();
                    $DB->query("
		SELECT COUNT(ID)
		FROM users_main
		WHERE Enabled = '1'
			AND LastAccess > '" . time_minus(3600 * 24 * 7) . "'");
                    list($UserStats['Week']) = $DB->next_record();
                    $DB->query("
		SELECT COUNT(ID)
		FROM users_main
		WHERE Enabled = '1'
			AND LastAccess > '" . time_minus(3600 * 24 * 30) . "'");
                    list($UserStats['Month']) = $DB->next_record();
                    $Cache->cache_value('stats_users', $UserStats, 0);
                }
                ?>
                <li class="SidebarList-item"><?= t('server.index.day_visit') ?>: <?= number_format($UserStats['Day']) ?> (<?= number_format($UserStats['Day'] / $UserCount * 100, 2) ?>%)</li>
                <li class="SidebarList-item"><?= t('server.index.wek_visit') ?>: <?= number_format($UserStats['Week']) ?> (<?= number_format($UserStats['Week'] / $UserCount * 100, 2) ?>%)</li>
                <li class="SidebarList-item"><?= t('server.index.mon_visit') ?>: <?= number_format($UserStats['Month']) ?> (<?= number_format($UserStats['Month'] / $UserCount * 100, 2) ?>%)</li>
                <?
                if (($TorrentCount = $Cache->get_value('stats_torrent_count')) === false) {
                    $DB->query("
		SELECT COUNT(ID)
		FROM torrents");
                    list($TorrentCount) = $DB->next_record();
                    $Cache->cache_value('stats_torrent_count', $TorrentCount, 604800); // staggered 1 week cache
                }
                if (($MoviesCount = $Cache->get_value('stats_album_count')) === false) {
                    $DB->query("
		SELECT COUNT(ID)
		FROM torrents_group
		WHERE CategoryID = '1'");
                    list($MoviesCount) = $DB->next_record();
                    $Cache->cache_value('stats_album_count', $MoviesCount, 86400); // staggered 1 day cache
                }
                if (($DramaCount = $Cache->get_value('stats_drama_count')) === false) {
                    $DB->query("
		SELECT COUNT(ID)
		FROM torrents_group
		WHERE CategoryID = '2'");
                    list($DramaCount) = $DB->next_record();
                    $Cache->cache_value('stats_drama_count', $DramaCount, 604830); // staggered 1 week cache
                }
                if (($ArtistCount = $Cache->get_value('stats_artist_count')) === false) {
                    $DB->query("
		SELECT COUNT(ArtistID)
		FROM artists_group");
                    list($ArtistCount) = $DB->next_record();
                    $Cache->cache_value('stats_artist_count', $ArtistCount, 604860); // staggered 1 week cache
                }
                ?>
                <li class="SidebarList-item"><?= t('server.common.torrents') ?>: <?= number_format($TorrentCount) ?>&nbsp;<a href="stats.php?action=torrents" class="brackets"><?= t('server.index.details') ?></a>
                </li>
                <li class="SidebarList-item"><?= t('server.index.moviegroups') ?>: <?= number_format($MoviesCount) ?></li>
                <li class="SidebarList-item"><?= t('server.common.artist') ?>: <?= number_format($ArtistCount) ?></li>
                <?
                //End Torrent Stats
                if (($CollageCount = $Cache->get_value('stats_collages')) === false) {
                    $DB->query("
		SELECT COUNT(ID)
		FROM collages");
                    list($CollageCount) = $DB->next_record();
                    $Cache->cache_value('stats_collages', $CollageCount, 11280); //staggered 1 week cache
                }
                if (CONFIG['ENABLE_COLLAGES']) {
                ?>
                    <li class="SidebarList-item"><?= t('server.index.collage') ?>: <?= number_format($CollageCount) ?></li>
                <?
                }
                if (($RequestStats = $Cache->get_value('stats_requests')) === false) {
                    $DB->query("
		SELECT COUNT(ID)
		FROM requests");
                    list($RequestCount) = $DB->next_record();
                    $DB->query("
		SELECT COUNT(ID)
		FROM requests
		WHERE FillerID > 0");
                    list($FilledCount) = $DB->next_record();
                    $Cache->cache_value('stats_requests', array($RequestCount, $FilledCount), 11280);
                } else {
                    list($RequestCount, $FilledCount) = $RequestStats;
                }
                $RequestPercentage = $RequestCount > 0 ? $FilledCount / $RequestCount * 100 : 0;
                ?>
                <li class="SidebarList-item"><?= t('server.common.requests') ?>: <?= number_format($RequestCount) ?> (<?= number_format($RequestPercentage, 2) ?>% <?= t('server.index.filled') ?>)</li>
                <?
                if ($SnatchStats = $Cache->get_value('stats_snatches')) {
                ?>
                    <li class="SidebarList-item"><?= t('server.index.snatches') ?>: <?= number_format($SnatchStats) ?></li>
                <?
                }
                if (($PeerStats = $Cache->get_value('stats_peers')) === false) {
                    //Cache lock!
                    $PeerStatsLocked = $Cache->get_value('stats_peers_lock');
                    if (!$PeerStatsLocked) {
                        $Cache->cache_value('stats_peers_lock', 1, 30);
                        $DB->query("
			SELECT IF(remaining=0,'Seeding','Leeching') AS Type, COUNT(uid)
			FROM xbt_files_users
			WHERE active = 1
			GROUP BY Type");
                        $PeerCount = $DB->to_array(0, MYSQLI_NUM, false);
                        $SeederCount = $PeerCount['Seeding'][1] ?: 0;
                        $LeecherCount = $PeerCount['Leeching'][1] ?: 0;
                        $Cache->cache_value('stats_peers', array($LeecherCount, $SeederCount), 1209600); // 2 week cache
                        $Cache->delete_value('stats_peers_lock');
                    }
                } else {
                    $PeerStatsLocked = false;
                    list($LeecherCount, $SeederCount) = $PeerStats;
                }
                if (!$PeerStatsLocked) {
                    $Ratio = Format::get_ratio_html($SeederCount, $LeecherCount);
                    $PeerCount = number_format($SeederCount + $LeecherCount);
                    $SeederCount = number_format($SeederCount);
                    $LeecherCount = number_format($LeecherCount);
                } else {
                    $PeerCount = $SeederCount = $LeecherCount = $Ratio = 'Server busy';
                }
                ?>
                <li class="SidebarList-item"><?= t('server.index.peers') ?>: <?= $PeerCount ?>&nbsp;<a href="stats.php?action=peers" class="brackets"><?= t('server.index.details') ?></a></li>
                <li class="SidebarList-item"><?= t('server.common.seeders') ?>: <?= $SeederCount ?></li>
                <li class="SidebarList-item"><?= t('server.common.leechers') ?>: <?= $LeecherCount ?></li>
                <li><?= t('server.index.s_l_ratio') ?>: <?= $Ratio ?></li>
            </ul>
        </div>

        <!-- Social Links -->
        <div class="Box">
            <div class="Social Box-header"><?= t('server.index.links') ?></div>
            <div class="Social Box-body">

                <a target="_blank" href="<?= CONFIG['TG_GROUP'] ?>" data-tooltip="<?= t('server.common.telegram') ?>">
                    <?= icon('telegram') ?>
                </a>
                <a target="_blank" href="https://github.com/Mosasauroidea/GazellePW" data-tooltip="<?= t('server.common.github') ?>">
                    <?= icon('github') ?>
                </a>
                <a target="_blank" href="https://crowdin.com/project/gazellepw" data-tooltip="Crowdin">
                    <?= icon('crowdin') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="LayoutMainSidebar-main">

        <? include('hot_movies.php'); ?>

        <? if (count($News) > 0) { ?>
            <!-- Anouncements -->
            <div class="Group">
                <div class="Group-body">
                    <?
                    $Count = 0;
                    foreach ($News as $NewsItem) {
                        list($NewsID, $Title, $Body, $NewsTime, $IsSticky) = $NewsItem;
                        if (strtotime($NewsTime) > time()) {
                            continue;
                        }
                    ?>
                        <div id="news<?= $NewsID ?>" class="Post news_post ">
                            <div class="Post-header">
                                <div class="Post-headerLeft">
                                    <div class="Post-headerTitle HtmlText  <?= $IsSticky ? 'is-sticky' : '' ?>" href="#">
                                        <?= Text::full_format($Title) ?>
                                    </div>
                                    - <?= time_diff($NewsTime); ?>
                                </div>
                                <div class="Post-headerActions">
                                    <a class="brackets" href="forums.php?action=viewthread&amp;threadid=<?= $NewsID ?>">
                                        <?= t('server.index.discuss') ?>
                                    </a>
                                    - <a href="#" onclick="globalapp.toggleAny(event, '#newsbody<?= $NewsID ?>');return false;">
                                        <span class="u-toggleAny-show u-hidden"><?= t('server.common.show') ?></span>
                                        <span class="u-toggleAny-hide"><?= t('server.common.hide') ?></span>
                                    </a>
                                </div>

                            </div>
                            <div id="newsbody<?= $NewsID ?>" class="HtmlText PostArticle Post-body">
                                <?= Text::full_format($Body) ?>
                            </div>
                        </div>
                        <?
                        if (++$Count > ($NewsCount - 1)) {
                            break;
                        }
                        ?>
                    <? } ?>
                    <div class="PostList-actions">
                        <a href="forums.php?action=viewforum&amp;forumid=<?= CONFIG['NEWS_FORUM_ID'] ?>">
                            <?= t('server.torrents.view_all') ?>
                        </a>
                    </div>
                </div>
            </div>
        <? } ?>
    </div>
</div>
<?

View::show_footer(array('disclaimer' => true));

function contest() {
    global $DB, $Cache, $LoggedUser;

    list($Contest, $TotalPoints) = $Cache->get_value('contest');
    if (!$Contest) {
        $DB->query("
			SELECT
				UserID,
				SUM(Points),
				Username
			FROM users_points AS up
				JOIN users_main AS um ON um.ID = up.UserID
			GROUP BY UserID
			ORDER BY SUM(Points) DESC
			LIMIT 20");
        $Contest = $DB->to_array();

        $DB->query("
			SELECT SUM(Points)
			FROM users_points");
        list($TotalPoints) = $DB->next_record();

        $Cache->cache_value('contest', array($Contest, $TotalPoints), 600);
    }

?>
    <!-- Contest Section -->
    <div class="box box_contest">
        <div class="head colhead_dark"><strong>Quality time scoreboard</strong></div>
        <div class="pad">
            <ol>
                <?
                foreach ($Contest as $User) {
                    list($UserID, $Points, $Username) = $User;
                ?>
                    <li><?= Users::format_username($UserID, false, false, false) ?> (<?= number_format($Points) ?>)</li>
                <?  } ?>
            </ol>
            Total uploads: <?= $TotalPoints ?><br />
            <a href="index.php?action=scoreboard">Full scoreboard</a>
        </div>
    </div>
    <!-- END contest Section -->
<?
} // contest()
?>