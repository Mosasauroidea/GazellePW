<?php

//------------- Disable inactive user accounts --------------------------//

//57 => Official Inviter
sleep(5);
// Send email
$DB->query("
		SELECT um.Username, um.Email
		FROM users_info AS ui
			JOIN users_main AS um ON um.ID = ui.UserID
			LEFT JOIN users_levels AS ul ON ul.UserID = um.ID AND (ul.PermissionID = '" . CONFIG['USER_CLASS']['CELEB'] . "' or ul.PermissionID = '57')
			LEFT JOIN users_donor_ranks as udr on udr.UserID = ui.UserID
		WHERE um.PermissionID IN ('" . CONFIG['USER_CLASS']['USER'] . "', '" . CONFIG['USER_CLASS']['MEMBER'] . "')
			AND um.LastAccess < '" . time_minus(3600 * 24 * 110, true) . "'
			AND um.LastAccess > '" . time_minus(3600 * 24 * 111, true) . "'
			AND um.LastAccess != '0000-00-00 00:00:00'
			AND udr.SpecialRank = 0
			AND um.Enabled != '2'
			AND ul.UserID IS NULL
		GROUP BY um.ID");
while (list($Username, $Email) = $DB->next_record()) {
    Misc::send_email_with_tpl($Email, 'inactive_notice', ['SiteURL' => CONFIG['SITE_URL'],  'UserName' => $Username]);
}

$DB->query("
		SELECT um.ID
		FROM users_info AS ui
			JOIN users_main AS um ON um.ID = ui.UserID
			LEFT JOIN users_levels AS ul ON ul.UserID = um.ID AND (ul.PermissionID = '" . CONFIG['USER_CLASS']['CELEB'] . "' or ul.PermissionID = '57')
			LEFT JOIN users_donor_ranks as udr on udr.UserID = ui.UserID
		WHERE um.PermissionID IN ('" . CONFIG['USER_CLASS']['USER'] . "', '" . CONFIG['USER_CLASS']['MEMBER'] . "')
			AND um.LastAccess < '" . time_minus(3600 * 24 * 30 * 4) . "'
			AND um.LastAccess != '0000-00-00 00:00:00'
			AND (udr.SpecialRank = 0 or udr.SpecialRank is NULL)
			AND um.Enabled != '2'
			AND ul.UserID IS NULL
		GROUP BY um.ID");
if ($DB->has_results()) {
    Tools::disable_users($DB->collect('ID'), 'Disabled for inactivity.', 3);
}
