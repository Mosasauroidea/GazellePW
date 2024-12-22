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
        tg.MainWikiBody,
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
$MainBody = $Group['MainWikiBody'];
$DoubanID = $Group['DoubanID'];
$Year = $Group['Year'];
$ReleaseType = $Group['ReleaseType'];
$CategoryID = $Group['CategoryID'];

View::show_header(t('server.torrents.edit_torrent_group'), '', 'PageTorrentEditGroup');

// Start printing form
?>
<div class="LayoutBody">

    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.torrents.edit_torrent_group') ?></div>
        <div class="BodyHeader-subNav"><?= Torrents::group_name($Group) ?></div>
    </div>
    <div class="BodyNavLinks">
        <a class="brackets" href="#movie_info_edit"><?= t('server.torrents.torrent_group_meta_editing') ?></a>
        <a class="brackets" href="#group_edit"><?= t('server.torrents.non_wiki_torrent_group_editing') ?></a>
        <a class="brackets" href="#merge"><?= t('server.torrents.merge_with') ?></a>

    </div>

    <div>
        <form class="edit_form Form-rowList" name="torrent_group" action="torrents.php" method="post">
            <input type="hidden" name="action" value="takegroupedit" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
            <table id="database_ids_table" class="Table" variant="header">
                <tr class="Form-rowHeader" id="movie_info_edit">
                    <td class="Form-title"><?= t('server.torrents.torrent_group_meta_editing') ?></td>
                </tr>

                <tr class="Form-row" variant="">
                    <td class="Form-label">
                        <?= t('server.torrents.release_type') ?>:
                    </td>
                    <td class="Form-inputs">
                        <select class="Input" id="releasetype" name="releasetype">
                            <? foreach ($ReleaseTypes as $Key) { ?>
                                <option class="Select-option" value="<?= $Key ?>" <?= ($Key == $ReleaseType ? ' selected="selected"' : '') ?>>
                                    <?= t('server.torrents.release_types')[$Key] ?>
                                </option>
                            <? } ?>
                        </select>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.torrents.ft_year') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input is-small" type="text" name="year" size="10" value="<?= $Year ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.torrents.imdb_id') ?>:
                    </td>
                    <td class="Form-inputs">
                        <div>
                            <input class="Input is-small" type="text" <?= check_perms('users_mod') ? '' : 'readonly' ?> name="imdbid" size="20" value="<?= $IMDBID ?>" placeholder="tt1234567">
                            <input class="hidden" id='lack_of_imdb_info'>
                            <label class="hidden" for="lack_of_imdb_info"><?= t('server.torrents.lack_of_info_now') ?></label>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.torrents.douban_id') ?>:
                    </td>
                    <td class="Form-inputs">
                        <div>
                            <input class=" Input is-small" type="text" name="doubanid" size="20" value="<?= $DoubanID ? $DoubanID : '' ?>" placeholder="12345678" />
                            <input class="hidden" id='lack_of_douban_info'>
                            <label class="hidden" for="lack_of_douban_info"><?= t('server.torrents.lack_of_info_now') ?></label>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.torrents.rt_title') ?>:
                    </td>
                    <td class="Form-inputs">
                        <div>
                            <input class=" Input is-small" type="text" name="rttitle" value="<?= $RTTitle ?>" size="20" placeholder="english_name" />
                            <input class="hidden" id='lack_of_rt_info'>
                            <label class="hidden" for="lack_of_rt_info"><?= t('server.torrents.lack_of_info_now') ?></label>
                        </div>
                    </td>
                </tr>



                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.upload.chinese_movie_synopsis') ?>:
                    </td>
                    <td class="Form-items">
                        <?php new TEXTAREA_PREVIEW('body', 'body', $Body, 91, 20, false, false); ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.upload.english_movie_synopsis') ?>:
                    </td>
                    <td class="Form-items">
                        <?php new TEXTAREA_PREVIEW('mainbody', 'mainbody', $MainBody, 91, 20, false, false); ?>
                    </td>
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
                    <td class="Form-label">
                        <?= t('server.torrents.image') ?>:
                    </td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="image" size="92" value="<?= $Image ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.torrents.edit_summary') ?>:
                    </td>
                    <td class="Form-inputs">
                        <input class=" Input" type="text" name="summary" size="92" />
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

                    <? if (check_perms('torrents_freeleech')) { ?>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.torrents.torrent_group_leech_status') ?>:</td>
                            <td class="Form-inputs">
                                <select class="Input" name="freeleech">
                                    <?
                                    $FL = Torrents::freeleech_option();
                                    foreach ($FL as $Key => $Name) {
                                    ?>
                                        <option class="Select-option" value="<?= $Key ?>" <?= ($Key == $Torrent['FreeTorrent'] ? ' selected="selected"' : '') ?>>
                                            <?= $Name ?></option>
                                    <?              } ?>
                                </select>
                                <script>
                                    $(document).ready(() => {
                                        $("#limit-time").click(() => {
                                            if ($("#limit-time")[0].checked) {
                                                $("#input-free-date,#input-free-time").show()
                                                if (<?= $Torrent['FreeEndTime'] ? "false" : "true" ?>) {
                                                    const d = new Date()
                                                    $("#input-free-date")[0].value = d.getFullYear() + "-" + ("0" + (d.getMonth() +
                                                        1)).substr(-2) + "-" + ("0" + d.getDate()).substr(-2)
                                                    $("#input-free-time")[0].value = ("0" + d.getHours()).substr(-2) + ":" + ("0" + d
                                                        .getMinutes()).substr(-2)
                                                }

                                            } else {
                                                $("#input-free-date,#input-free-time").hide()
                                            }
                                        })
                                    })
                                </script>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" id="limit-time" name="limit-time" <?= $Torrent['FreeEndTime'] ? " checked=\"checked\"" : "" ?> />
                                    <label class="Checkbox-label" for="limit-time" style="display: inline;">定时</label>
                                </div>
        </div>
        <input class="Input" type="date" id="input-free-date" name="free-date" <?= $Torrent['FreeEndTime'] ? "value=\"" . substr($Torrent['FreeEndTime'], 0, 10) . "\"" : "style=\"display:none;\"" ?> /><input class="Input" id="input-free-time" name="free-time" type="time" <?= $Torrent['FreeEndTime'] ? "value=\"" . substr($Torrent['FreeEndTime'], 11, 5) . "\"" : "style=\"display:none;\"" ?> />
        <?= t('server.upload.because') ?>
        <select class="Input" name="freeleechtype">
            <?
                        $FL = array("N/A", "Staff Pick", "Perma-FL");
                        foreach ($FL as $Key => $Name) {
            ?>
                <option class="Select-option" value="<?= $Key ?>" <?= ($Key == $Torrent['FreeLeechType'] ? ' selected="selected"' : '') ?>><?= $Name ?></option>
            <?              } ?>
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
                        <td class="Form-label"><?= t('server.torrents.merge_target') ?>:</td>
                        <td class="Form-inputs FormOneLine">
                            <input class="Input is-small" type="text" name="targetgroupid" size="10" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td rowspan="2">
                            <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
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