<?

/************************************************************************
||------------|| Edit torrent group wiki page ||-----------------------||

This page is the page that is displayed when someone feels like editing
a torrent group's wiki page.

It is called when $_GET['action'] == 'edit'. $_GET['groupid'] is the
ID of the torrent group and must be set.

The page inserts a new revision into the wiki_torrents table, and clears
the cache for the torrent group page.

 ************************************************************************/

$GroupID = $_GET['groupid'];
if (!is_number($GroupID) || !$GroupID) {
    error(0);
}

// Get the torrent group name and the body of the last revision
$DB->query("
	SELECT
        tg.ID,
		tg.Name,
		tg.SubName,
        tg.IMDBID,
        tg.DoubanID,
        tg.RTTitle,
		tg.WikiImage,
		tg.WikiBody,
		tg.Year,
		tg.ReleaseType,
		tg.CategoryID
	FROM torrents_group AS tg
	WHERE tg.ID = '$GroupID'");
if (!$DB->has_results()) {
    error(404);
}
$Group = $DB->next_record(MYSQLI_ASSOC);
$Name = $Group['Name'];
$SubName = $Group['SubName'];
$IMDBID = $Group['IMDBID'];
$RTTitle = $Group['RTTItle'];
$Image = $Group['WikiImage'];
$Body = $Group['WikiBody'];
$DoubanID = $Group['DoubanID'];
$Year = $Group['Year'];
$ReleaseType = $Group['ReleaseType'];
$CategoryID = $Group['CategoryID'];


View::show_header(t('server.torrents.edit_torrent_group'), '', 'PageTorrentEditGroup');

// Start printing form
?>
<div class="LayoutBody">

    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= page_title_conn([t('server.common.edit'), Torrents::group_name($Group)]) ?></h2>
    </div>
    <div class="BodyNavLinks">
        <a class="brackets" href="#movie_info_edit"><?= t('server.torrents.torrent_group_meta_editing') ?></a>
        <a class="brackets" href="#group_edit"><?= t('server.torrents.non_wiki_torrent_group_editing') ?></a>
        <a class="brackets" href="#rename"><?= t('server.torrents.rename') ?></a>
        <a class="brackets" href="#merge"><?= t('server.torrents.merge_with') ?></a>

    </div>

    <div>
        <form class="edit_form" name="torrent_group" action="torrents.php" method="post">
            <input type="hidden" name="action" value="takegroupedit" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
            <table id="database_ids_table" class="Form-rowList Table" variant="header">
                <tr class="Form-rowHeader" id="movie_info_edit">
                    <td class="Form-title"><?= t('server.torrents.torrent_group_meta_editing') ?></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-items">
                        <?= t('server.torrents.image') ?>:
                        <input class="Input" type="text" name="image" size="92" value="<?= $Image ?>" />
                    </td>
                </tr>
                <tr class="Form-row"">
                    <td class=" Form-items"><?= t('server.upload.chinese_movie_synopsis') ?>:
                    <?php new TEXTAREA_PREVIEW('body', 'body', $Body, 91, 20, false, false); ?>
                    </td>
                </tr>
                <tr class="Form-row" variant="">
                    <td class="Form-items">
                        <div>
                            <?= t('server.torrents.release_type') ?>:
                            <select class="Input" id="releasetype" name="releasetype">
                                <? foreach ($ReleaseTypes as $Key) { ?>
                                    <option class="Select-option" value="<?= $Key ?>" <?= ($Key == $ReleaseType ? ' selected="selected"' : '') ?>>
                                        <?= t('server.torrents.release_types')[$Key] ?>
                                    </option>
                                <? } ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="FormOneLine Form-inputs">
                        <div>
                            <div><?= t('server.torrents.imdb_id') ?>:</div>
                            <div><input class="Input is-small" type="text" <?= check_perms('users_mod') ? '' : 'readonly' ?> name="imdbid" size="20" value="<?= $IMDBID ?>" placeholder="tt1234567">
                                <input class="hidden" id='lack_of_imdb_info'>
                                <label class="hidden" for="lack_of_imdb_info"><?= t('server.torrents.lack_of_info_now') ?></label>
                            </div>
                        </div>
                        <div>
                            <div><?= t('server.torrents.douban_id') ?>:</div>
                            <div><input class=" Input is-small" type="text" name="doubanid" size="20" value="<?= $DoubanID ? $DoubanID : '' ?>" placeholder="12345678" />
                                <input class="hidden" id='lack_of_douban_info'>
                                <label class="hidden" for="lack_of_douban_info"><?= t('server.torrents.lack_of_info_now') ?></label>
                            </div>
                        </div>
                        <div>
                            <div><?= t('server.torrents.rt_title') ?>:</div>

                            <div><input class=" Input is-small" type="text" name="rttitle" value="<?= $RTTitle ?>" size="20" placeholder="english_name" />
                                <input class="hidden" id='lack_of_rt_info'>
                                <label class="hidden" for="lack_of_rt_info"><?= t('server.torrents.lack_of_info_now') ?></label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-items"><?= t('server.torrents.edit_summary') ?>:
                        <input class=" Input" type="text" name="summary" size="92" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-items">
                        <div style="text-align: center;">
                            <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                        </div>

                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?
    $DB->query("
		SELECT UserID
		FROM torrents
		WHERE GroupID = $GroupID");
    //Users can edit the group info if they've uploaded a torrent to the group or have torrents_edit
    if (in_array($LoggedUser['ID'], $DB->collect('UserID')) || check_perms('torrents_edit')) { ?>
        <div>
            <form class="edit_form" name="torrent_group" action="torrents.php" method="post">
                <input type="hidden" name="action" value="nonwikiedit" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                <table cellpadding="3" cellspacing="1" border="0" class="Table" width="100%">
                    <tr class="Form-rowHeader" id="group_edit">
                        <td class="Form-title"><?= t('server.torrents.non_wiki_torrent_group_editing') ?></td>
                    </tr>
                    <tr class="Form-row">
                        <td colspan="2" class="center"><?= t('server.torrents.torrent_group_editing_note') ?></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.torrents.ft_year') ?>:</td>
                        <td class="Form-inputs">
                            <input class="Input is-small" type="text" name="year" size="10" value="<?= $Year ?>" />
                        </td>
                    </tr>

                    <? if (check_perms('torrents_freeleech')) { ?>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.torrents.torrent_group_leech_status') ?>:</td>
                            <td class="Form-inputs">
                                <input type="checkbox" id="unfreeleech" name="unfreeleech" /><label for="unfreeleech"> <?= t('server.torrents.reset') ?></label>
                                <input type="checkbox" id="freeleech" name="freeleech" /><label for="freeleech"> <?= t('server.torrents.freeleech') ?></label>
                                <input type="checkbox" id="neutralleech" name="neutralleech" /><label for="neutralleech"> <?= t('server.torrents.neutral_leech') ?></label>
                                <input type="checkbox" id="off25leech" name="off25leech" /><label for="off25leech"> <?= t('server.torrents.off25') ?></label>
                                <input type="checkbox" id="off50leech" name="off50leech" /><label for="off50leech"> <?= t('server.torrents.off50') ?></label>
                                <input type="checkbox" id="off75leech" name="off75leech" /><label for="off75leech"> <?= t('server.torrents.off75') ?></label>
                                <?= t('server.torrents.because') ?>
                                <select class="Input" name="freeleechtype" <? $FL = array('N/A', 'Staff Pick', 'Perma-FL');
                                                                            // TODO 种子组的free类型当前没有记忆功能
                                                                            foreach ($FL as $Key => $FLType) { ?> <option class="Select-option" value="<?= $Key ?>"><?= $FLType ?></option>
                                <?      } ?>
                                </select>
                            </td>
                        </tr>
                    <?  } ?>
                    <tr class="Form-row">
                        <td rowspan="2">
                            <div style="text-align: center;">
                                <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    <?
    }
    if (check_perms('torrents_edit')) {
    ?>
        <div>
            <form class="rename_form" name="torrent_group" action="torrents.php" method="post">
                <input type="hidden" name="action" value="rename" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                <table cellpadding="3" cellspacing="1" border="0" class="Form-rowList" variant="header" width="100%">
                    <tr class="Form-rowHeader" id="rename">
                        <td class="Form-title"><?= t('server.torrents.rename') ?></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.torrents.group_title') ?>:</td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" name="name" size="92" value="<?= $Name ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.torrents.sub_title') ?>:</td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" name="subname" size="92" value="<?= $SubName ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td rowspan="2">
                            <div style="text-align: center;">
                                <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>

        <div>
            <form class="merge_form" name="torrent_group" action="torrents.php" method="post">
                <div>
                    <input type="hidden" name="action" value="merge" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                    <table cellpadding="3" cellspacing="1" border="0" class="Form-rowList" width="100%" variant="header">
                        <tr class="Form-rowHeader" id="merge">
                            <td class="Form-title"><?= t('server.torrents.merge_with') ?></td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.torrents.merge_target') ?></td>
                            <td class="Form-inputs FormOneLine">
                                <input class="Input is-small" type="text" name="targetgroupid" size="10" />
                                <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td rowspan="2">
                                <div style="text-align: center;">
                            </td>
                        </tr>
                </div>

                </table>
        </div>
        </form>
</div>
<?  } ?>
</div>
<? View::show_footer(); ?>