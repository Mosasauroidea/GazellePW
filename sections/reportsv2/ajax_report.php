<?
/*
 * The backend to changing the report type when making a report.
 * It prints out the relevant report_messages from the array, then
 * prints the relevant report_fields and whether they're required.
 */
authorize();

?>
<div id="reports_rule">
    <ul class="postlist">

        <?
        $CategoryID = $_POST['categoryid'];
        if (array_key_exists($_POST['type'], $Types[$CategoryID])) {
            $ReportType = $Types[$CategoryID][$_POST['type']];
        } elseif (array_key_exists($_POST['type'], $Types['master'])) {
            $ReportType = $Types['master'][$_POST['type']];
        } else {
            echo 'HAX IN REPORT TYPE';
        ?>
    </ul>
</div>
<?
            die();
        }
?>
<?
echo Lang::get('reportsv2', 'report_introduction');
foreach ($ReportType['report_messages'] as $Message) {
?>
    <li><?= $Message ?></li>
<?
}
?>
</ul>
</div>
<?
if (array_key_exists('image', $ReportType['report_fields'])) {
?>
    <?= Lang::get('reportsv2', 'image_s') ?><?= ($ReportType['report_fields']['image'] == '1' ? ' <strong class="u-colorWarning">(' . Lang::get('reportsv2', 'required') . ')</strong>' : '') ?>:
    <input class="Input" type="text" id="image" name="image" size="50" value="<?= (!empty($_POST['image']) ? display_str($_POST['image']) : '') ?>" />
    <<?
    }
    if (array_key_exists('track', $ReportType['report_fields'])) {
        ?> <?= Lang::get('reportsv2', 'track_number_s') ?><?= ($ReportType['report_fields']['track'] == '1' || $ReportType['report_fields']['track'] == '2' ? ' <strong class="u-colorWarning">(' . Lang::get('reportsv2', 'required') . ')</strong>' : '') ?>: <input class="Input" type="text" id="track" name="track" size="8" value="<?= (!empty($_POST['track']) ? display_str($_POST['track']) : '') ?>" /><?= ($ReportType['report_fields']['track'] == '1' ? '<input id="all_tracks" type="checkbox" onclick="AllTracks()" /> All' : '') ?>
<?
    }
    if (array_key_exists('link', $ReportType['report_fields'])) {
?>
    <?= Lang::get('reportsv2', 'link_s_to_external_source') ?><?= ($ReportType['report_fields']['link'] == '1' ? ' <strong class="u-colorWarning">(' . Lang::get('reportsv2', 'required') . ')</strong>' : '') ?>:
    <input class="Input" type="text" id="link" name="link" size="50" value="<?= (!empty($_POST['link']) ? display_str($_POST['link']) : '') ?>" />
<?
    }
    if (array_key_exists('sitelink', $ReportType['report_fields'])) {
?>
    <?= Lang::get('reportsv2', 'pl_to_other_relevant_torrent_s') ?><?= ($ReportType['report_fields']['sitelink'] == '1' ? ' <strong class="u-colorWarning">(' . Lang::get('reportsv2', 'required') . ')</strong>' : '') ?>:
    <input class="Input" type="text" id="sitelink" name="sitelink" size="50" value="<?= (!empty($_POST['sitelink']) ? display_str($_POST['sitelink']) : '') ?>" />
<?
    }
    if (array_key_exists('proofimages', $ReportType['report_fields'])) {
?>
    <?= Lang::get('reportsv2', 'link_s_to_proof_images') ?><?= ($ReportType['report_fields']['proofimages'] == '1' ? ' <strong class="u-colorWarning">(' . Lang::get('reportsv2', 'required') . ')</strong>' : '') ?>:
    <input class="Input" type="text" id="image" name="image" size="50" value="<?= (!empty($_POST['proofimages']) ? display_str($_POST['proofimages']) : '') ?>" />
<?
    }
?>
<?= Lang::get('reportsv2', 'comments') ?> <strong class="u-colorWarning">(<?= Lang::get('reportsv2', 'required') ?>)</strong>:
<textarea class="Input" id="extra" rows="5" cols="60" name="extra"><?= display_str($_POST['extra']) ?></textarea>