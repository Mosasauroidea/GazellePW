<?
include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
if (!isset($_GET['torrentid']) || !is_number($_GET['torrentid'])) {
	error(404);
}
$TorrentID = $_GET['torrentid'];

$TorrentInfo = Torrents::get_torrent($TorrentID);


$FileList = $TorrentInfo['FileList'];
$FilePath = $TorrentInfo['FilePath'];

$AllFile = [];

if (substr($FileList, -3) == '}}}') { // Old style
	$FileListSplit = explode('|||', $FileList);
	foreach ($FileListSplit as $File) {
		$NameEnd = strrpos($File, '{{{');
		$Name = substr($File, 0, $NameEnd);
		if ($Spaces = strspn($Name, ' ')) {
			$Name = str_replace(' ', '&nbsp;', substr($Name, 0, $Spaces)) . substr($Name, $Spaces);
		}
		$FileSize = substr($File, $NameEnd + 3, -3);
		$AllFile[] = [$Name, $FileSize];
	}
} else {
	$FileListSplit = explode("\n", $FileList);
	foreach ($FileListSplit as $File) {
		$FileInfo = Torrents::filelist_get_file($File);
		$AllFile[] = [$FileInfo['name'], $FileInfo['size']];
	}
}

?>
<div class="TableContainer">
	<table class="TableTorrentFileList Table">
		<tr class="Table-rowHeader">
			<td class="Table-cell">
				<div class="filelist_title" style="float: left;"> <?= Lang::get('torrents.file_names') ?>
					<?
					if (check_perms('users_mod')) {
					?>
						<a href="torrents.php?action=regen_filelist?torrentid=<?= $TorrentID ?>" class="brackets"><?= Lang::get('torrents.regenerate') ?></a>
					<?
					}
					?>
				</div>
				<div class="filelist_path" style="float: right;"><?= $FilePath ? "/$FilePath/" : '/' ?></div>
			</td>
			<td class="Table-cell">
				<strong><?= Lang::get('torrents.size') ?></strong>
			</td>
		</tr>
		<?
		foreach ($AllFile as $File) {
		?>
			<tr class="Table-row">
				<td class="Table-cell"><?= $File[0] ?></td>
				<td class="Table-row Table-rowRight"><?= Format::get_size($File[1]) ?></td>
			</tr>
		<?
		}
		?>
	</table>
</div>