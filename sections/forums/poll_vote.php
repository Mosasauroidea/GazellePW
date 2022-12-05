<?

if (!isset($_POST['topicid']) || !is_number($_POST['topicid'])) {
    error(0, true);
}
$TopicID = $_POST['topicid'];

if (!empty($_POST['large'])) {
    $Size = 750;
} else {
    $Size = 140;
}

if (!$ThreadInfo = $Cache->get_value("thread_$TopicID" . '_info')) {
    $DB->query("
		SELECT
			t.Title,
			t.ForumID,
			t.IsLocked,
			t.IsSticky,
			COUNT(fp.id) AS Posts,
			t.LastPostAuthorID,
			ISNULL(p.TopicID) AS NoPoll
		FROM forums_topics AS t
			JOIN forums_posts AS fp ON fp.TopicID = t.ID
			LEFT JOIN forums_polls AS p ON p.TopicID = t.ID
		WHERE t.ID = '$TopicID'
		GROUP BY fp.TopicID");
    if (!$DB->has_results()) {
        die();
    }
    $ThreadInfo = $DB->next_record(MYSQLI_ASSOC);
    if (!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
        $Cache->cache_value("thread_$TopicID" . '_info', $ThreadInfo, 0);
    }
}
$ForumID = $ThreadInfo['ForumID'];

if (!list($Question, $Answers, $Votes, $Featured, $Closed, $MaxCount) = $Cache->get_value("polls_$TopicID")) {
    $DB->query("
		SELECT
			Question,
			Answers,
			Featured,
			Closed,
			MaxCount
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
}


if ($Closed) {
    error(403, true);
}

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

if (!isset($_POST['vote']) || !count($_POST['vote'])) {
?>
    <span class="u-colorWarning"><?= t('server.forums.please_select_an_option') ?></span><br />
    <form class="vote_form" name="poll" id="poll" action="">
        <input type="hidden" name="action" value="poll" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="large" value="<?= display_str($_POST['large']) ?>" />
        <input type="hidden" name="topicid" value="<?= $TopicID ?>" />
        <? for ($i = 1, $il = count($Answers); $i <= $il; $i++) { ?>
            <input class="poll_answer" type="checkbox" name="vote" id="answer_<?= $i ?>" value="<?= $i ?>" onclick="PollCount(<?= $MaxCount ?>)" />
            <label for="answer_<?= $i ?>"><?= display_str($Answers[$i]) ?></label><br />
        <?  } ?>
        <br /><input type="checkbox" name="vote" id="answer_0" value="0" onclick="PollCount(0)" /> <label for="answer_0"><?= t('server.forums.blank_show_results') ?></label><br /><br />
        <input class="Button" type="button" onclick="ajax.post('index.php', 'poll', function(response) { $('#poll_container').raw().innerHTML = response });" value="Vote" />
    </form>
<?
} else {
    authorize();
    $Vote = $_POST['vote'];
    foreach ($Vote as $v) {
        if (!is_number($v) || (!isset($Answers[$v]) && $v != 0)) {
            error(0, true);
        }
    }
    //Add our vote
    $DB->query("select count(1) from forums_polls_votes where TopicID=$TopicID and UserID=" . $LoggedUser['ID']);
    list($VoteCount) = $DB->next_record();
    if (!$VoteCount) {
        foreach ($Vote as $v) {
            $DB->query("
				INSERT IGNORE INTO forums_polls_votes
					(TopicID, UserID, Vote)
				VALUES
					($TopicID, " . $LoggedUser['ID'] . ", $v)");
            if ($v != 0) {
                $NoBlank = true;
                $Cache->begin_transaction("polls_$TopicID");
                $Cache->update_row(2, array($v => '+1'));
                $Cache->commit_transaction(0);
                $Votes[$v]++;
                $Answers[$v] = '=> ' . $Answers[$v];
                $TotalVotes++;
                $MaxVotes++;
            }
        }
        $DB->query("SELECT count(distinct `UserID`) FROM `forums_polls_votes` WHERE `TopicID`='$TopicID' and vote!=0");
        list($PeopleCount) = $DB->next_record();
    }
?>
    <ul class="Poll-answers">
        <?
        if ($ForumID != CONFIG['STAFF_FORUM']) {
            for ($i = 1, $il = count($Answers); $i <= $il; $i++) {
                if (!empty($Votes[$i]) && $TotalVotes > 0) {
                    $Ratio = $Votes[$i] / $MaxVotes;
                    $Percent = $Votes[$i] / $TotalVotes;
                } else {
                    $Ratio = 0;
                    $Percent = 0;
                }
        ?>
                <li class="Poll-answerItem">
                    <div class="Poll-answerText">
                        <?= display_str($Answers[$i]) ?> (<?= $Votes[$i] . ", " . number_format($Percent * 100, 2) ?>%)
                        <progress class="Progress" value="<?= $Ratio ?>"></progress>
                </li>
            <?
            }
        } else {
            //Staff forum, output voters, not percentages
            $DB->query("
				SELECT GROUP_CONCAT(um.Username SEPARATOR ', '),
					fpv.Vote
				FROM users_main AS um
					JOIN forums_polls_votes AS fpv ON um.ID = fpv.UserID
				WHERE TopicID = $TopicID
				GROUP BY fpv.Vote");

            $StaffVotes = $DB->to_array();
            foreach ($StaffVotes as $StaffVote) {
                list($StaffString, $StaffVoted) = $StaffVote;
            ?>
                <li class="Poll-answerItem">
                    <a href="forums.php?action=change_vote&amp;threadid=<?= $TopicID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>&amp;vote=<?= (int)$StaffVoted ?>"><?= display_str(empty($Answers[$StaffVoted]) ? 'Blank' : $Answers[$StaffVoted]) ?></a> - <?= $StaffString ?>
                </li>
        <?
            }
        }
        ?>
    </ul>
    <div class="Poll-count">
        <?= t('server.forums.votes') ?>: <?= number_format($TotalVotes) ?>,
        <?= t('server.forums.voters') ?>: <?= $PeopleCount ?>
    </div>
<?
}
