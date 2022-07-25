<?
$UserVotes = Votes::get_user_votes($LoggedUser['ID']);
$GroupVotes = Votes::get_group_votes($GroupID);

$TotalVotes = $GroupVotes['Total'];
$UpVotes    = $GroupVotes['Ups'];
$DownVotes  = $TotalVotes - $UpVotes;

$Voted = isset($UserVotes[$GroupID]) ? $UserVotes[$GroupID]['Type'] : false;
$Score = Votes::binomial_score($UpVotes, $TotalVotes);
$Percentage = $TotalVotes > 0 ? number_format($UpVotes / $TotalVotes * 100, 1) : '--';
if (CONFIG['ENABLE_VOTES']) {
?>
    <div class="caidan artbox">
        <div class="sec-title"><span><?= t('server.torrents.album_votes') ?></span></div>
        <div class="album_votes body">
            <span class="favoritecount" data-tooltip="<?= $UpVotes . ($UpVotes == 1 ? t('server.torrents.votes_upvote') : t('server.torrents.votes_upvotes')) ?>"><span id="upvotes"><?= number_format($UpVotes) ?></span> <span class="vote_album_up">
                    <?= icon('vote-up') ?>
                </span></span>
            &nbsp; &nbsp;
            <span class="favoritecount" data-tooltip="<?= $DownVotes . ($DownVotes == 1 ? t('server.torrents.votes_downvote') : t('server.torrents.votes_downvotes')) ?>"><span id="downvotes"><?= number_format($DownVotes) ?></span> <span class="vote_album_down">
                    <?= icon('vote-down') ?>
                </span></span>
            &nbsp; &nbsp;
            <span class="favoritecount" id="totalvotes"><?= number_format($TotalVotes) ?></span> <?= t('server.torrents.votes_total') ?>
            <br /><br />
            <span data-tooltip-interactive="&lt;span style=&quot;font-weight: bold;&quot;&gt;Score: <?= number_format($Score * 100, 4) ?>&lt;/span&gt;&lt;br /&gt;&lt;br /&gt;<?= t('server.torrents.votes_score_note') ?>" data-title-plain="Score: <?= number_format($Score * 100, 4) ?>. This is the lower bound of the binomial confidence interval described in the Favorite Album Votes wiki article, multiplied by 100."><?= t('server.torrents.votes_score') ?>:&nbsp;<span class="favoritecount"><?= number_format($Score * 100, 1) ?></span></span>
            &nbsp; | &nbsp;
            <span class="favoritecount"><?= $Percentage ?>%</span> <?= t('server.torrents.votes_positive') ?>
            <br /><br />
            <span id="upvoted" <?= (($Voted != 'Up') ? ' class="hidden"' : '') ?>><?= t('server.torrents.votes_upvoted') ?><br /><br /></span>
            <span id="downvoted" <?= (($Voted != 'Down') ? ' class="hidden"' : '') ?>><?= t('server.torrents.votes_downvoted') ?><br /><br /></span>
            <? if (check_perms('site_album_votes')) { ?>
                <span<?= ($Voted ? ' class="hidden"' : '') ?> id="vote_message"><a href="#" class="brackets upvote" onclick="UpVoteGroup(<?= $GroupID ?>, '<?= $LoggedUser['AuthKey'] ?>'); return false;"><?= t('server.torrents.votes_upvote') ?></a> - <a href="#" class="brackets downvote" onclick="DownVoteGroup(<?= $GroupID ?>, '<?= $LoggedUser['AuthKey'] ?>'); return false;"><?= t('server.torrents.votes_downvote') ?></a></span>
                <?  } ?>
                <span<?= ($Voted ? '' : ' class="hidden"') ?> id="unvote_message">
                    <?= t('server.torrents.votes_changed') ?>
                    <br />
                    <a href="#" onclick="UnvoteGroup(<?= $GroupID ?>, '<?= $LoggedUser['AuthKey'] ?>'); return false;" class="brackets"><?= t('server.torrents.votes_clear') ?></a>
                    </span>
        </div>
    <?
}
    ?>