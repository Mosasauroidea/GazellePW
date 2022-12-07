<?php

if (!check_perms('admin_manage_ipbans')) {
    error(403);
}
if (isset($_GET['perform'])) {
    $IP = db_string($_GET['ip']);
    if ($_GET['perform'] == 'delete') {
        if (!is_number($_GET['id']) || $_GET['id'] == '') {
            error(0);
        }
        $DB->query('DELETE FROM ip_bans WHERE ID=' . $_GET['id']);
        $Bans = $Cache->delete_value('ip_bans_' . $IP);
    } elseif ($_GET['perform'] == 'create') {
        $Notes = db_string($_GET['notes']);
        $DB->query("
			INSERT INTO ip_bans (FromIP, ToIP, Reason)
			VALUES (INET6_ATON('$IP'),INET6_ATON('$IP'), '$Notes')");
        $Cache->delete_value('ip_bans_' . $IP);
    }
}
