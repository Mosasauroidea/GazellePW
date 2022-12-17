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

$FileTree = Torrents::build_file_tree($FilePath, $AllFile);


?>
<div class="TorrentDetail-row is-fileList is-block">
    <div class="TorrentDetailFileList-header" id="filelist_box_header">
        <strong class="TorrentDetailfileList-title" id="filelist_box_title"><?= t('server.torrents.file_names') ?>:</strong>
        <?
        if (check_perms('users_mod')) {
        ?>
            <span class="floatright">
                <a href="torrents.php?action=regen_filelist&torrentid=<?= $TorrentID ?>"><?= t('server.torrents.regenerate') ?></a>
            </span>
        <?
        }
        ?>
    </div>
    <div class="filelist_path">
        <?= gen_list($FileTree, true) ?>
    </div>
</div>
<?
function gen_list($FileList, $Root = false) {
    if (count($FileList) == 0) {
        return;
    }
?>
    <ul class="<?= $Root ? 'MenuList' : 'MenuList SubMenu' ?> TorrentDetailfileList-fileList">
        <? foreach ($FileList as $Name => $File) {
        ?>
            <li class="TorrentDetailfileListItem-fileListItem" variant="<?= $Root ? 'root' : '' ?>">
                <div class="TorrentDetailfileList-fileItem">
                    <? if (count($File['children']) > 0) { ?>
                        <a class="TorrentDetailfileList-fileName" href="#" onclick="$(this).parent().next().gtoggle();return false">
                            <?= icon('Torrent/folder') ?>
                            <?= $Name ?>
                        </a>
                    <? } else { ?>
                        <div class="TorrentDetailfileList-fileName">
                            <?= icon('Torrent/file') ?>
                            <?= $Name ?>
                        </div>
                    <? } ?>

                    <div class="TorrentDetailfileList-fileSize">
                        <?= Format::get_size($File['size']) ?>
                    </div>
                </div>
                <?
                if ($File['children']) {
                ?>
                    <div class="hidden">
                        <?
                        gen_list($File['children']);
                        ?>
                    </div>
                <?
                }
                ?>
            </li>
        <? } ?>

    </ul>
<?
}
