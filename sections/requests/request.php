<?php

/*
 * This is the page that displays the request to the end user after being created.
 */
if (empty($_GET['id']) || !is_number($_GET['id'])) {
    error(0);
}

$RequestID = $_GET['id'];
$RequestTaxPercent = ($RequestTax * 100);

//First things first, lets get the data for the request.

$Request = Requests::get_request($RequestID);
if ($Request === false) {
    error(404);
}
$RequestType = $Request['RequestType'];
if ($RequestType == 2) {
    $Link =  $Request['SourceTorrent'];
    if (!preg_match('/' . TORRENT_REGEX . '/i', $Link, $Matches)) {
        error('invlaid request');
    } else {
        $SourceTorrentID = $Matches[2];
        $SourceTorrent = Torrents::get_torrent($SourceTorrentID);
    }
}

//Convenience variables
$IsFilled = !empty($Request['TorrentID']);
$CanVote = !$IsFilled && check_perms('site_vote');


if ($Request['CategoryID'] === '0') {
    $CategoryName = 'Unknown';
} else {
    $CategoryName = $Categories[$Request['CategoryID'] - 1];
}

//Do we need to get artists?
$ArtistForm = Requests::get_artists($RequestID);
$Director = Artists::get_first_directors($ArtistForm);
$ArtistName = Artists::display_artists($ArtistForm, false, false);
$RequestGroupName = Torrents::group_name($Request, false);

if ($IsFilled) {
    $DisplayLink = "<a href=\"torrents.php?torrentid=$Request[TorrentID]\" dir=\"ltr\">$RequestGroupName</a>";
} else {
    $DisplayLink = '<span dir="ltr">' . $RequestGroupName . "</span>";
}
$FullName = $RequestGroupName;

$CodecString = implode(', ', explode('|', $Request['CodecList']));
$ResolutionString = implode(', ', explode('|', $Request['ResolutionList']));
$ContainerString = implode(', ', explode('|', $Request['ContainerList']));
$SourceString =  implode(', ', explode('|', $Request['SourceList']));


if (empty($Request['ReleaseType'])) {
    $ReleaseName = 'Unknown';
} else {
    $ReleaseName = t('server.torrents.release_types')[$Request['ReleaseType']];
}

//Votes time
$RequestVotes = Requests::get_votes_array($RequestID);
$VoteCount = count($RequestVotes['Voters']);
$ProjectCanEdit = (check_perms('project_team') && !$IsFilled && ($Request['CategoryID'] === '0'));
$UserCanEdit = (!$IsFilled && $LoggedUser['ID'] === $Request['UserID'] && $VoteCount < 2);
$CanEdit = ($UserCanEdit || $ProjectCanEdit || check_perms('site_moderate_requests')) &&  $RequestType != 2;

// Comments (must be loaded before View::show_header so that subscriptions and quote notifications are handled properly)
list($NumComments, $Page, $Thread, $LastRead) = Comments::load('requests', $RequestID);

$GroupName = Lang::choose_content($Request['Name'], $Request['SubName']);
$SubName = Lang::choose_content($Request['SubName'], $Request['Name']);
$GroupYear = $Request['Year'];
View::show_header(t('server.requests.view_request') . ": $FullName", 'comments,bbcode,subscriptions', 'PageRequestShow');

$Pages = Format::get_pages($Page, $NumComments, CONFIG['TORRENT_COMMENTS_PER_PAGE'], 9, '#comments');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.common.requests') ?></div>
        <div class="BodyNavLinks">
            <? if ($CanEdit) { ?>
                <a href="requests.php?action=edit&amp;id=<?= $RequestID ?>" class="brackets"><?= t('server.common.edit') ?></a>
            <?  }
            if ($UserCanEdit || check_perms('users_mod')) { ?>
                <a href="requests.php?action=delete&amp;id=<?= $RequestID ?>" class="brackets"><?= t('server.common.delete') ?></a>
            <?  }
            if (Bookmarks::has_bookmarked('request', $RequestID)) { ?>
                <a href="#" id="bookmarklink_request_<?= $RequestID ?>" onclick="Unbookmark('request', <?= $RequestID ?>, '<?= t('server.common.add_bookmark') ?>'); return false;" class="brackets"><?= t('server.common.remove_bookmark') ?></a>
            <?  } else { ?>
                <a href="#" id="bookmarklink_request_<?= $RequestID ?>" onclick="Bookmark('request', <?= $RequestID ?>, '<?= t('server.common.remove_bookmark') ?>'); return false;" class="brackets"><?= t('server.common.add_bookmark') ?></a>
            <?  } ?>
            <a href="#" id="subscribelink_requests<?= $RequestID ?>" class="brackets" onclick="SubscribeComments('requests',<?= $RequestID ?>, '<?= Subscriptions::has_subscribed_comments('requests', $RequestID) !== false ? t('server.torrents.subscribe') : t('server.torrents.unsubscribe') ?>');return false;"><?= Subscriptions::has_subscribed_comments('requests', $RequestID) !== false ? t('server.torrents.unsubscribe') : t('server.torrents.subscribe') ?></a>
            <a href="reports.php?action=report&amp;type=request&amp;id=<?= $RequestID ?>" class="brackets"><?= t('server.requests.report_request') ?></a>
            <?
            if (!$IsFilled) {
                if ($RequestType == 2) { ?>
                    <a href="torrents.php?action=download&amp;id=<?= $SourceTorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="Download"><?= t('server.requests.seed_torrent') ?></a>
                <?
                } else {
                ?>
                    <a href="upload.php?requestid=<?= $RequestID ?><?= ($Request['GroupID'] ? "&amp;groupid=$Request[GroupID]" : '') ?>" class="brackets"><?= t('server.requests.upload_request') ?></a>
                <?
                }
            }
            if (!$IsFilled && ($Request['CategoryID'] === '0')) { ?>
                <a href="reports.php?action=report&amp;type=request_update&amp;id=<?= $RequestID ?>" class="brackets"><?= t('server.requests.request_update') ?></a>
            <? } ?>
            <?
            $google_url  = "https://www.blu-ray.com/search/?quicksearch=1&quicksearch_country=all&section=bluraymovies&quicksearch_keyword=" . display_str($Request['Name']);
            ?>
            <?
            if ($RequestType != 2) {
            ?>
                <a target="_blank" href="<? echo $google_url; ?>" class="brackets"><?= t('server.requests.find_in_stores') ?></a>
            <?
            }
            ?>
        </div>
    </div>
    <div class="MovieInfo MovieInfoMovie Box">
        <div class="MovieInfo-left">
            <img class="MovieInfo-poster" src="<?= ImageTools::process($Request['Image']) ?>" onclick="lightbox.init(this, $(this).width());">
        </div>
        <div class="MovieInfo-titleContainer">
            <span class="MovieInfo-title">
                <?= display_str($GroupName) ?>
            </span>
            <i class="MovieInfo-year">(<? print_r($GroupYear) ?>)</i>
            <? if ($SubName) {
                echo "<div class='MovieInfo-subTitle'>" . display_str($SubName) . "</div>";
            } ?>
        </div>
        <div class="MovieInfo-tagContainer">
            <div class="MovieInfo-facts">
                <a class="MovieInfo-fact" data-tooltip="<?= t('server.common.imdb_rating') ?>" target="_blank" href="https://www.imdb.com/title/<? print_r($Request['IMDBID']) ?>">
                    <?= icon('imdb') ?>
                    <span>--</span>
                </a>
                <a class="MovieInfo-fact" data-tooltip="<?= t('server.upload.director') ?>" href="/artist.php?id=<?= $Director['ArtistID'] ?>" dir="ltr">
                    <?= icon('movie-director') ?>
                    <span><?= $ArtistName ?></span>
                </a>
                <span class="MovieInfo-fact TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.upload.movie_type') ?>">
                    <?= icon('movie-type') ?>
                    <span><?= $ReleaseName ?></span>
                </span>
                <?
                if ($Request['GroupID']) {
                    if ($RequestType == 2) {
                ?>
                        <span class="MovieInfo-fact TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.common.torrent') ?>">
                            <a href="torrents.php?torrentid=<?= $SourceTorrentID ?>">torrents.php?torrentid=<?= $SourceTorrentID ?></a>
                        </span>
                    <?
                    } else {
                    ?>
                        <span class="MovieInfo-fact TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.requests.torrent_group') ?>">
                            <a href="torrents.php?id=<?= $Request['GroupID'] ?>">torrents.php?id=<?= $Request['GroupID'] ?></a>
                        </span>
                <?
                    }
                }
                ?>
            </div>
            <div class="MovieInfo-tags">
                <?
                $TagNames = [];
                $Tags = Tags::get_sub_name($Request['Tags']);
                foreach ($Tags as $TagKey => $TagName) {
                    $TagNames[] = $TagName;
                }
                $TagsFormat = new Tags(implode(' ', $TagNames));
                ?>
                <i>
                    <?= $TagsFormat->format('torrents.php?action=advanced&amp;taglist=', '', 'MovieInfo-tag')
                    ?>
                </i>
            </div>
        </div>

        <?
        if ($RequestType == 2) {
            Torrents::render_media_info($SourceTorrent['MediaInfo']);
        } else {
        ?>
            <div class="MovieInfo-synopsis" ?>
                <div class="HtmlText">
                    <? View::long_text('movie_info_synopsis', display_str($Request['Description']), 2); ?>
                </div>
            </div>
            <table class="RequestDetailTable Table">
                <tr class="Table-row">
                    <td class="Table-cell">
                        <?= t('server.requests.acceptable_codecs') ?>:
                    </td>
                    <td class="Table-cell">
                        <?= $CodecString ?>
                    </td>
                </tr>
                <tr class="Table-row">
                    <td class="Table-cell">
                        <?= t('server.requests.acceptable_containers') ?>:
                    </td>
                    <td class="Table-cell">
                        <?= $ContainerString ?>
                    </td>
                </tr>
                <tr class="Table-row">
                    <td class="Table-cell">
                        <?= t('server.requests.acceptable_resolutions') ?>:
                    </td>
                    <td class="Table-cell">
                        <?= $ResolutionString ?>
                    </td>
                </tr>
                <tr class="Table-row">
                    <td class="Table-cell">
                        <?= t('server.requests.acceptable_sources') ?>:
                    </td>
                    <td class="Table-cell">
                        <?= $SourceString ?>
                    </td>
                </tr>
                <?
                if ($Request['SourceTorrent']) {
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell">
                            <?= t('server.requests.source_torrent') ?>:
                        </td>
                        <td class="Table-cell">
                            <?= $Request['SourceTorrent'] ?>
                        </td>
                    </tr>

                <?  } ?>
                <? if ($Request['PurchasableAt']) { ?>
                    <tr class="Table-row">
                        <td class="Table-cell">
                            <?= t('server.requests.purchasable_at') ?>:
                        </td>
                        <td class="Table-cell">
                            <?= $Request['PurchasableAt'] ?>
                        </td>
                    </tr>
                <? } ?>
            </table>
        <?
        }
        ?>

    </div>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <div class="SidebarItemRequestInfo SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong>
                        <?= t('server.requests.basic_info') ?></strong>
                </div>
                <ul class="SidebarList SidebarItem-body Box-body">
                    <li class="SidebarList-item"><?= t('server.requests.created') ?>:&nbsp;<?= time_diff($Request['TimeAdded']) ?></li>
                    <li class="SidebarList-item"><?= t('server.requests.created_by') ?>:&nbsp; <strong><?= Users::format_username($Request['UserID'], false, false, false) ?></strong></li>
                    <li class="SidebarList-item"><?= t('server.requests.bounty') ?>:&nbsp;<div id="formatted_bounty"><?= Format::get_size($RequestVotes['TotalBounty']) ?></div>
                    </li>
                    <? if ($Request['LastVote'] > $Request['TimeAdded']) { ?>
                        <li class="SidebarList-item"><?= t('server.requests.last_voted') ?>:&nbsp;<?= time_diff($Request['LastVote']) ?> </li>
                    <? } ?>
                    <? if ($IsFilled) {
                        $TimeCompare = 1267643718; // Requests v2 was implemented 2010-03-03 20:15:18
                    ?>
                        <li class="SidebarList-item"><?= t('server.index.filled') ?>:&nbsp; <strong><a href="torrents.php?<?= (strtotime($Request['TimeFilled']) < $TimeCompare ? 'id=' : 'torrentid=') . $Request['TorrentID'] ?>"><?= t('server.requests.yes') ?></a></strong>
                            <? if ($LoggedUser['ID'] == $Request['UserID'] || $LoggedUser['ID'] == $Request['FillerID'] || check_perms('site_moderate_requests')) { ?>
                                &nbsp;|&nbsp; <strong><a data-tooltip="<?= t('server.requests.unfilling_a_request_without_reason') ?>" href="requests.php?action=unfill&amp;id=<?= $RequestID ?>" class="brackets"><?= t('server.requests.unfill') ?></a></strong>
                            <?  } ?>
                        </li>
                        <li class="SidebarList-item"><?= t('server.requests.filled_user') ?>:&nbsp; <?= Users::format_username($Request['FillerID'], false, false, false) ?></li>
                    <? } else { ?>
                        <li class="SidebarList-item"><?= t('server.index.filled') ?>:&nbsp;<?= t('server.reports.no') ?></li>
                    <? } ?>
                </ul>
            </div>
            <div class="SidebarItemVotes SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= t('server.requests.top_contributors') ?></strong>
                </div>
                <table class="SidebarItem-body Box-body layout" id="request_top_contrib">
                    <?
                    $VoteMax = ($VoteCount < 5 ? $VoteCount : 5);
                    $ViewerVote = false;
                    for ($i = 0; $i < $VoteMax; $i++) {
                        $User = array_shift($RequestVotes['Voters']);
                        $Boldify = false;
                        if ($User['UserID'] === $LoggedUser['ID']) {
                            $ViewerVote = true;
                            $Boldify = true;
                        }
                    ?>
                        <tr>
                            <td>
                                <a href="user.php?id=<?= $User['UserID'] ?>"><?= ($Boldify ? '<strong>' : '') . display_str($User['Username']) . ($Boldify ? '</strong>' : '') ?></a>
                            </td>
                            <td class="number_column">
                                <?= ($Boldify ? '<strong>' : '') . Format::get_size($User['Bounty']) . ($Boldify ? "</strong>\n" : "\n") ?>
                            </td>
                        </tr>
                        <?  }
                    reset($RequestVotes['Voters']);
                    if (!$ViewerVote) {
                        foreach ($RequestVotes['Voters'] as $User) {
                            if ($User['UserID'] === $LoggedUser['ID']) { ?>
                                <tr>
                                    <td>
                                        <a href="user.php?id=<?= $User['UserID'] ?>"><strong><?= display_str($User['Username']) ?></strong></a>
                                    </td>
                                    <td class="number_column">
                                        <strong><?= Format::get_size($User['Bounty']) ?></strong>
                                    </td>
                                </tr>
                    <?          }
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
        <div class="LayoutMainSidebar-main">
            <div class="TableContainer">
                <? if ($CanVote) { ?>
                    <table class="Form-rowList FormRequestFill Table">
                        <tr class="Form-rowHeader">
                            <td class="Form-itle"><?= t('server.common.actions') ?></td>
                        </tr>

                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.requests.quick_vote') ?></td>
                            <td class="Form-items">
                                <div>
                                    <span id="votecount"><?= number_format($VoteCount) ?></span>
                                    &nbsp;&nbsp;<a href="javascript:globalapp.requestVote(0)" class="brackets"><strong>+</strong></a>
                                    <strong><?= t('server.requests.costs') ?> <?= Format::get_size($MinimumVote, 0) ?></strong>
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row" id="voting">
                            <td class="Form-label" data-tooltip="<?= t('server.requests.custom_vote_title') ?>">
                                <?= t('server.requests.custom_vote') ?>:
                            </td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <form class="u-vstack add_form" name="request" action="requests.php" method="get" id="request_form">
                                        <input type="hidden" name="action" value="vote" />
                                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                        <input type="hidden" id="request_tax" value="<?= $RequestTax ?>" />
                                        <input type="hidden" id="requestid" name="id" value="<?= $RequestID ?>" />
                                        <input type="hidden" id="auth" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                        <input type="hidden" id="amount" name="amount" value="0" />
                                        <input type="hidden" id="current_uploaded" value="<?= $LoggedUser['BytesUploaded'] ?>" />
                                        <input type="hidden" id="current_downloaded" value="<?= $LoggedUser['BytesDownloaded'] ?>" />
                                        <input type="hidden" id="current_rr" value="<?= (float)$LoggedUser['RequiredRatio'] ?>" />
                                        <input id="total_bounty" type="hidden" value="<?= $RequestVotes['TotalBounty'] ?>" />
                                        <div>
                                            <input class="Input is-small" type="number" pattern="[0-9]" min="1" id="amount_box" size="8" oninput="globalapp.requestCalculate();" />
                                            <select class="Input hidden" id="unit" name="unit" onchange="globalapp.requestCalculate();">
                                                <option class="Select-option" value="gb">GB</option>
                                            </select>
                                            <input class="Button" type="button" id="button" value="<?= t('server.requests.custom_vote') ?>" disabled="disabled" onclick="document.querySelector('#amount_box').reportValidity() && globalapp.requestVote();" />
                                        </div>
                                        <div>
                                            <?= $RequestTax > 0 ? "<strong>{$RequestTaxPercent}% " . t('server.requests.system_taxed') : '' ?>
                                            <? $Class = $RequestTax > 0 ? '' : 'u-hidden' ?>
                                            <div class="<?= $Class ?>">Bounty after tax: <strong><span id="bounty_after_tax">90.00 MB</span></strong></div>
                                            <?= t('server.requests.if_you_add_the_entered') ?>
                                            <strong><span id="new_bounty">0.00 GB</span></strong>
                                            <?= t('server.requests.of_bounty_your_new_stats') ?>:
                                            <?= t('server.requests.uploaded') ?>: <span id="new_uploaded"><?= Format::get_size($LoggedUser['BytesUploaded']) ?></span>,
                                            <?= t('server.requests.ratio') ?>: <span id="new_ratio"><?= Format::get_ratio_html($LoggedUser['BytesUploaded'], $LoggedUser['BytesDownloaded']) ?></span>
                                        </div>

                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label" valign="top"><?= t('server.requests.fill_request') ?>:</td>
                            <td class="Form-items">
                                <form class="u-vstack edit_form" name="request" action="" method="post">
                                    <input type="hidden" name="action" value="takefill" />
                                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                    <input type="hidden" name="requestid" value="<?= $RequestID ?>" />
                                    <?
                                    if ($RequestType == 1) {
                                    ?>
                                        <div class="field_div">

                                            <input placeholder="<?= t('server.requests.should_be_pl_to_the_torrent') ?><?= site_url() ?>torrents.php?torrentid=xxxx" class="Input" type="text" size="50" name="link" <?= (!empty($Link) ? " value=\"$Link\"" : '') ?> />
                                        </div>
                                    <?
                                    }
                                    ?>
                                    <div class="field_div">
                                        <? if (check_perms('site_moderate_requests')) { ?>
                                            <?= t('server.requests.for_user') ?>: <input class="Input is-small" type="text" size="25" name="user" <?= (!empty($FillerUsername) ? " value=\"$FillerUsername\"" : '') ?> />
                                        <?      } ?>
                                        <button class="Button" type="submit" value="Fill request" /><?= t('server.requests.fill_request') ?></button>
                                        <?
                                        if ($RequestType == 2) {
                                        } else {
                                        ?>
                                            <a href="upload.php?requestid=<?= $RequestID ?><?= ($Request['GroupID'] ? "&amp;groupid=$Request[GroupID]" : '') ?>"><button class="Button" type="button" id="upload" value="Upload request"><?= t('server.requests.upload_request') ?></button></a>
                                            <strong style="margin-bottom: 10px;">[<a href="javascript:void(0);" onclick="$('#fill_a_request_how_to_blockquote').toggle();"><strong class="how_to_toggle"><?= t('server.requests.fill_a_request_how_to_toggle') ?></strong></a>]</strong>
                                        <?
                                        }
                                        ?>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label"></td>
                            <td class="Form-items">
                                <blockquote id="fill_a_request_how_to_blockquote" style="display: none; margin: 5px 0;"><?= t('server.requests.fill_a_request_how_to_blockquote') ?></blockquote>
                            </td>
                        </tr>
                    </table>
                <? } ?>
            </div>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.collages.comments') ?>
                    </div>
                </div>
                <div class="Group-body" id="request_comments">
                    <? View::pages($Pages) ?>
                    <?

                    //---------- Begin printing
                    CommentsView::render_comments($Thread, $LastRead, "requests.php?action=view&amp;id=$RequestID");

                    View::pages($Pages);
                    View::parse('generic/reply/quickreply.php', array(
                        'InputName' => 'pageid',
                        'InputID' => $RequestID,
                        'Action' => 'comments.php?page=requests',
                        'InputAction' => 'take_post',
                        'SubscribeBox' => true
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<? View::show_footer(); ?>