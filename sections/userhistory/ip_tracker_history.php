<?php

/************************************************************************
||------------|| User IP history page ||---------------------------||

This page lists previous IPs a user has connected to the site with. It
gets called if $_GET['action'] == 'ips'.

It also requires $_GET['userid'] in order to get the data for the correct
user.

 ************************************************************************/

define('IPS_PER_PAGE', 25);

if (!check_perms('users_mod')) {
    error(403);
}

$UserID = $_GET['userid'];
if (!is_number($UserID)) {
    error(404);
}

$DB->query("
	SELECT um.Username,
		p.Level AS Class
	FROM users_main AS um
		LEFT JOIN permissions AS p ON p.ID = um.PermissionID
	WHERE um.ID = $UserID");
list($Username, $Class) = $DB->next_record();

if (!check_perms('users_view_ips', $Class)) {
    error(403);
}

$UsersOnly = $_GET['usersonly'];

View::show_header(Lang::get('userhistory.tracker_ip_address_history_for_before') . "$Username" . Lang::get('userhistory.tracker_ip_address_history_for_after'), '', 'PageUserHistoryIPTracker');
?>
<script type="text/javascript">
    function ShowIPs(rowname) {
        $('tr[name="' + rowname + '"]').gtoggle();
    }
</script>
<?
list($Page, $Limit) = Format::page_limit(IPS_PER_PAGE);

$TrackerIps = $DB->query("
	SELECT IP, fid, tstamp
	FROM xbt_snatched
	WHERE uid = $UserID
		AND IP != ''
	ORDER BY tstamp DESC
	LIMIT $Limit");

$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();
$DB->set_query_id($TrackerIps);

$Pages = Format::get_pages($Page, $NumResults, IPS_PER_PAGE, 9);

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('userhistory.tracker_ip_address_history_for_before') ?><a href="user.php?id=<?= $UserID ?>"><?= $Username ?></a><?= Lang::get('userhistory.tracker_ip_address_history_for_after') ?></h2>
    </div>
    <div class="BodyNavLinks"><?= $Pages ?></div>
    <div class="TableContainer">
        <table class="TableUserTrakcerIPHistory Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= Lang::get('userhistory.ip_address') ?></td>
                <td class="Table-cell"><?= Lang::get('global.torrent') ?></td>
                <td class="Table-cell"><?= Lang::get('userhistory.time') ?></td>
            </tr>
            <?
            $Results = $DB->to_array();
            foreach ($Results as $Index => $Result) {
                list($IP, $TorrentID, $Time) = $Result;

            ?>
                <tr class="Table-row">
                    <td class="Table-cell">
                        <?= $IP ?> (<?= Tools::get_country_code_by_ajax($IP) ?>)<br /><?= Tools::get_host_by_ajax($IP) ?>
                        <a href="http://whatismyipaddress.com/ip/<?= display_str($IP) ?>" class="brackets" data-tooltip="<?= Lang::get('userhistory.search_wimia_com') ?>">WI</a>
                    </td>
                    <td class="Table-cell"><a href="torrents.php?torrentid=<?= $TorrentID ?>"><?= $TorrentID ?></a></td>
                    <td class="Table-cell"><?= date('Y-m-d g:i:s', $Time) ?></td>
                </tr>
            <?
            }
            ?>
        </table>
    </div>
    <div class="BodyNavLinks">
        <?= $Pages ?>
    </div>
</div>

<?
View::show_footer();
?>