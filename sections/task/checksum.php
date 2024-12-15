<?php

$_GET['filter'] = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$Join = '';
$Where = '';
$Filter = 0;
if ($_GET['filter'] === 'snatched') {
	$Join = 'JOIN xbt_snatched AS x ON x.fid = t.ID AND x.uid = ' . $LoggedUser['ID'];
	$Filter = 1;
} elseif ($_GET['filter'] === 'uploaded') {
	$Where = "AND t.UserID = {$LoggedUser['ID']}";
	$Filter = 2;
}

$DB->query("SELECT count(t.ID) as count FROM torrents AS t {$Join} WHERE t.HasLogDB='1' AND t.LogChecksum='0' {$Where}");
$Row = $DB->next_record();
$Total = $Row['count'];
$TotalStr = number_format($Total);
$Page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
$Page = max(1, $Page);
$Limit = TORRENTS_PER_PAGE;
$Offset = TORRENTS_PER_PAGE * ($Page - 1);
$Pages = Format::get_pages($Page, $Total, TORRENTS_PER_PAGE);

View::show_header(Lang::get('better', 'torrents_with_bad_missing_checksum'));
$DB->query("
	SELECT
		t.ID,
		t.GroupID
	FROM torrents AS t
		{$Join}
	WHERE t.HasLogDB = '1' AND t.LogChecksum = '0' {$Where}
	ORDER BY t.ID ASC
	LIMIT {$Limit} OFFSET {$Offset}");

$TorrentsInfo = $DB->to_array('ID', MYSQLI_ASSOC);
$GroupIDs = array();
foreach ($TorrentsInfo as $Torrent) {
	$GroupIDs[] = $Torrent['GroupID'];
}
$Results = (count($GroupIDs) > 0) ? Torrents::get_groups($GroupIDs) : array();
?>
<div class="header">
	<? if ($Filter === 0) { ?>
		<h2><?= Lang::get('better', 'all_torrents_trumpable_for_bad_missing_checksum') ?></h2>
	<? } elseif ($Filter === 1) { ?>
		<h2><?= Lang::get('better', 'torrents_trumpable_for_bad_missing_checksum_that_you_have_snatched') ?></h2>
	<? } elseif ($Filter === 2) { ?>
		<h2><?= Lang::get('better', 'torrents_trumpable_for_bad_missing_checksum_that_you_have_uploaded') ?></h2>
	<? } ?>

	<div class="linkbox">
		<a href="better.php" class="brackets"><?= Lang::get('better', 'back_to_better_php_list') ?></a>
		<? if ($Filter !== 0) { ?>
			<a href="better.php?method=checksum&amp;filter=all" class="brackets"><?= Lang::get('better', 'show_all') ?></a>
		<? } ?>
		<? if ($Filter !== 1) { ?>
			<a href="better.php?method=checksum&amp;filter=snatched" class="brackets"><?= Lang::get('better', 'show_only_those_you_have_snatched') ?></a>
		<? } ?>
		<? if ($Filter !== 2) { ?>
			<a href="better.php?method=checksum&amp;filter=uploaded" class="brackets"><?= Lang::get('better', 'show_only_those_you_have_uploaded') ?></a>
		<? } ?>
	</div>
	<div class="linkbox">
		<?= $Pages ?>
	</div>
</div>
<div class="thin box pad">
	<h3><?= Lang::get('better', 'there_are_torrents_remaining_before') ?> <?= $TotalStr ?> <?= Lang::get('better', 'there_are_torrents_remaining_after') ?></h3>
	<div class="table_container border">
		<table class="torrent_table">
			<?
			foreach ($TorrentsInfo as $TorrentID => $Info) {
				extract(Torrents::array_group($Results[$Info['GroupID']]));
				$TorrentTags = new Tags($TagList);

				if (!empty($ExtendedArtists[1]) || !empty($ExtendedArtists[4]) || !empty($ExtendedArtists[5]) || !empty($ExtendedArtists[6])) {
					unset($ExtendedArtists[2]);
					unset($ExtendedArtists[3]);
					$DisplayName = Artists::display_artists($ExtendedArtists);
				} else {
					$DisplayName = '';
				}
				$DisplayName .= "<a href=\"torrents.php?id=$GroupID&amp;torrentid=$TorrentID#torrent$TorrentID\" class=\"tooltip\" title=\"" . Lang::get('global', 'view_torrent_group') . "\" dir=\"ltr\">$GroupName</a>";
				if ($GroupYear > 0) {
					$DisplayName .= " [$GroupYear]";
				}
				if ($ReleaseType > 0) {
					$DisplayName .= ' [' . $ReleaseTypes[$ReleaseType] . ']';
				}

				$ExtraInfo = Torrents::torrent_info($Torrents[$TorrentID]);
				if ($ExtraInfo) {
					$DisplayName .= " - $ExtraInfo";
				}
			?>
				<tr class="torrent torrent_row<?= $GroupFlags['IsSnatched'] ? ' snatched_torrent"' : '' ?>">
					<td>
						<span class="torrent_links_block">
							<a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" class="brackets tooltip" title="<?= Lang::get('better', 'download') ?>">DL</a>
						</span>
						<?= $DisplayName ?>
						<? if (check_perms('admin_reports')) { ?>
							<a href="better.php?method=files&amp;remove=<?= $TorrentID ?>" class="brackets">X</a>
						<? 	} ?>
						<div class="tags"><?= $TorrentTags->format() ?></div>
					</td>
				</tr>
			<?
			} ?>
		</table>
	</div>
</div>
<?
View::show_footer();
?>