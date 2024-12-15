<?php

if (check_perms('admin_reports') && !empty($_GET['remove']) && is_number($_GET['remove'])) {
	$DB->query("
		DELETE FROM torrents_bad_tags
		WHERE TorrentID = " . $_GET['remove']);
	$DB->query("
		SELECT GroupID
		FROM torrents
		WHERE ID = " . $_GET['remove']);
	list($GroupID) = $DB->next_record();
	$Cache->delete_value('torrents_details_' . $GroupID);
}


if (!empty($_GET['filter']) && $_GET['filter'] == 'all') {
	$Join = '';
	$All = true;
} else {
	$Join = "JOIN xbt_snatched AS x ON x.fid = tbt.TorrentID AND x.uid = " . $LoggedUser['ID'];
	$All = false;
}

View::show_header(Lang::get('better', 'torrents_with_bad_tags'));
$DB->query("
	SELECT tbt.TorrentID, t.GroupID
	FROM torrents_bad_tags AS tbt
		JOIN torrents AS t ON t.ID = tbt.TorrentID
		$Join
	ORDER BY tbt.TimeAdded ASC");
$TorrentsInfo = $DB->to_array('TorrentID', MYSQLI_ASSOC);
foreach ($TorrentsInfo as $Torrent) {
	$GroupIDs[] = $Torrent['GroupID'];
}
$Results = Torrents::get_groups($GroupIDs);
?>
<div class="header">
	<? if ($All) { ?>
		<h2><?= Lang::get('better', 'all_torrents_trumpable_for_bad_tags') ?></h2>
	<? } else { ?>
		<h2><?= Lang::get('better', 'torrents_trumpable_for_bad_tags_that_you_have_snatched') ?></h2>
	<? } ?>

	<div class="linkbox">
		<a href="better.php" class="brackets"><?= Lang::get('better', 'back_to_better_php_list') ?></a>
		<? if ($All) { ?>
			<a href="better.php?method=tags" class="brackets"><?= Lang::get('better', 'show_only_those_you_have_snatched') ?></a>
		<? } else { ?>
			<a href="better.php?method=tags&amp;filter=all" class="brackets"><?= Lang::get('better', 'show_all') ?></a>
		<? } ?>
	</div>
</div>

<div class="thin box pad">
	<h3><?= Lang::get('better', 'there_are_torrents_remaining_before') ?> <?= number_format(count($TorrentsInfo)) ?> <?= Lang::get('better', 'there_are_torrents_remaining_after') ?></h3>
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
				$DisplayName .= "<a href=\"torrents.php?id=$GroupID&amp;torrentid=$TorrentID#torrent$TorrentID\" class=\"tooltip\" title=\"" . Lang::get('global', 'view_torrent') . "\" dir=\"ltr\">$GroupName</a>";
				if ($GroupYear > 0) {
					$DisplayName .= " [$GroupYear]";
				}
				if ($ReleaseType > 0) {
					$DisplayName .= ' [' . $ReleaseTypes[$ReleaseType] . ']';
				}

				$ExtraInfo = Torrents::torrent_info($Torrents[$TorrentID]);
				if ($ExtraInfo) {
					$DisplayName .= ' - ' . $ExtraInfo;
				}
			?>
				<tr class="torrent torrent_row<?= $Torrents[$TorrentID]['IsSnatched'] ? ' snatched_torrent' : '' ?>">
					<td>
						<span class="torrent_links_block">
							<a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" class="brackets tooltip" title="<?= Lang::get('better', 'download') ?>">DL</a>
						</span>
						<?= $DisplayName ?>
						<? if (check_perms('admin_reports')) { ?>
							<a href="better.php?method=tags&amp;remove=<?= $TorrentID ?>" class="brackets">X</a>
						<? 	} ?>
						<div class="tags"><?= $TorrentTags->format() ?></div>
					</td>
				</tr>
			<?
			}
			?>
		</table>
	</div>
</div>
<?
View::show_footer();
?>