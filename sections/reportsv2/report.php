<?php
/*
 * This is the frontend of reporting a torrent, it's what users see when
 * they visit reportsv2.php?id=xxx
 */

include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

//If we're not coming from torrents.php, check we're being returned because of an error.
if (!isset($_GET['id']) || !is_number($_GET['id'])) {
    if (!isset($Err)) {
        error(404);
    }
}
$TorrentID = $_GET['id'];
$Torrent = Torrents::get_torrent($TorrentID);
$AltName = $Title = $DisplayName = Torrents::group_name($Torrent['Group'], true);
$Group = $Torrent['Group'];
$CategoryID = $Group['CategoryID'];
$GroupSnatched = Torrents::parse_group_snatched($Group);

View::show_header(Lang::get('reportsv2', 'report'), 'reportsv2,browse,torrent,bbcode,recommend', 'PageReportV2Show');
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('reportsv2', 'report') ?></h2>
    </div>
    <?
    $DetailOption = new DetailOption;
    $DetailOption->WithReport = false;
    $DetailOption->ReadOnly = true;
    $tableRender = new UngroupTorrentSimpleListView([$Torrent]);
    $tableRender->with_self(false)->with_detail('report', $DetailOption)->render();
    ?>


    <form class="create_form Form" name="report" action="reportsv2.php?action=takereport" enctype="multipart/form-data" method="post" id="reportform">
        <div>
            <input type="hidden" name="submit" value="true" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="torrentid" value="<?= $TorrentID ?>" />
            <input type="hidden" name="categoryid" value="<?= $CategoryID ?>" />
        </div>
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td class="Form-title"><?= Lang::get('reportsv2', 'report_a_torrent') ?></td>
            </tr>
            <tr class="Form-row">
                <td colspan="2">
                    <div><?= Lang::get('reportsv2', 'reason') ?>:
                        <select class=" Input" id="type" name="type" onchange="ChangeReportType();">
                            <?
                            if (!empty($Types[$CategoryID])) {
                                $TypeList = $Types['master'] + $Types[$CategoryID];
                                $Priorities = array();
                                foreach ($TypeList as $Key => $Value) {
                                    $Priorities[$Key] = $Value['priority'];
                                }
                                array_multisort($Priorities, SORT_ASC, $TypeList);
                            } else {
                                $TypeList = $Types['master'];
                            }
                            foreach ($TypeList as $Type => $Data) {
                            ?>
                                <option class="Select-option" value="<?= ($Type) ?>" <?= $Type == $_GET['type'] ? 'selected="selected"' : "" ?>><?= ($Data['title']) ?></option>
                            <?              } ?>
                        </select>
                    </div>
                </td>
            </tr>

            <tr class="Form-row">
                <td colspan="2">
                    <div id="dynamic_form">
                        <?
                        /*
                 * THIS IS WHERE SEXY AJAX COMES IN
                 * The following malarky is needed so that if you get sent back here, the fields are filled in.
                 */
                        ?>
                        <input id="sitelink" type="hidden" name="sitelink" size="50" value="<?= (!empty($_POST['sitelink']) ? display_str($_POST['sitelink']) : '') ?>" />
                        <input id="image" type="hidden" name="image" size="50" value="<?= (!empty($_POST['image']) ? display_str($_POST['image']) : '') ?>" />
                        <input id="track" type="hidden" name="track" size="8" value="<?= (!empty($_POST['track']) ? display_str($_POST['track']) : '') ?>" />
                        <input id="link" type="hidden" name="link" size="50" value="<?= (!empty($_POST['link']) ? display_str($_POST['link']) : '') ?>" />
                        <input id="extra" type="hidden" name="extra" value="<?= (!empty($_POST['extra']) ? display_str($_POST['extra']) : '') ?>" />

                        <script type="text/javascript">
                            ChangeReportType();
                        </script>
                    </div>
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2">
                    <div align="center" id="submit-report">
                        <input class="Button" type="submit" value="<?= Lang::get('global', 'submit') ?>" />
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php
View::show_footer();
?>