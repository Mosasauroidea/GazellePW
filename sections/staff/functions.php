<?

/**
 * Generate a table row for a staff member on staff.php
 *
 * @param String $Row used for alternating row colors
 * @param $ID the user ID of the staff member
 * @param $Paranoia the user's paranoia
 * @param $Class the user class
 * @param $LastAccess datetime the user last browsed the site
 * @param String $Remark the "Staff remark" or FLS' "Support for" text
 * @param String $HiddenBy the text that is displayed when a staff member's
 *                       paranoia hides their LastAccess time
 * @return string $Row
 */
function make_staff_row($Row, $ID, $Paranoia, $Class, $LastAccess, $Remark = '', $HiddenBy = 'Hidden by user') {
    echo "\t\t\t<tr class=\"Table-row\">
				<td class=\"Table-cell\">
					" . Users::format_username($ID, false, false, false) . "
				</td>
				<td class=\"Table-cell\">
					"; //used for proper indentation of HTML
    if (check_paranoia('lastseen', $Paranoia, $Class)) {
        echo time_diff($LastAccess);
    } else {
        echo "$HiddenBy";
    }
    echo "\n\t\t\t\t</td>
				<td class=\"Table-cell\">"
        . Text::full_format($Remark) .
        "</td>
			</tr>\n"; // the "\n" is needed for pretty HTML
    // the foreach loop that calls this function needs to know the new value of $Row
    return $Row;
}

function get_secondary() {
    global $Cache, $DB;
    static $Secondary;
    if (is_array($Secondary)) {
        return $Secondary;
    }
    if (($Secondary = $Cache->get_value('secondary')) === false) {
        $DB->prepared_query("
			SELECT
				m.ID,
                p.ID as LevelID,
			    p.Level,
			    p.Name,
				m.Username,
                IFNULL(sg.Name, '') AS StaffGroup,
				m.Paranoia,
				m.LastAccess,
				i.SupportFor
			FROM users_info AS i
				JOIN users_main AS m ON m.ID = i.UserID
				JOIN users_levels AS l ON l.UserID = i.UserID
				JOIN permissions AS p ON p.ID = l.PermissionID
                INNER JOIN staff_groups AS sg ON sg.ID = p.StaffGroup
            WHERE p.DisplayStaff = '1' AND Secondary = 1
			ORDER BY p.Name, m.Username");
        $Secondary = $DB->to_array(false, MYSQLI_BOTH, array(3, 'Paranoia'));
        $Cache->cache_value('secondary', $Secondary, 180);
    }
    return $Secondary;
}

function get_staff() {
    global $Cache, $DB;
    static $Staff;
    if (is_array($Staff)) {
        return $Staff;
    }

    if (($Staff = $Cache->get_value('staff')) === false) {
        $DB->prepared_query("
		SELECT
			m.ID,
			p.ID as LevelID,
			p.Level,
			p.Name,
			IFNULL(sg.Name, '') AS StaffGroup,
			m.Username,
			m.Paranoia,
			m.LastAccess,
			i.SupportFor
		FROM users_main AS m
			JOIN users_info AS i ON m.ID = i.UserID
			JOIN permissions AS p ON p.ID = m.PermissionID
			INNER JOIN staff_groups AS sg ON sg.ID = p.StaffGroup
		WHERE p.DisplayStaff = '1' AND Secondary = 0
		ORDER BY p.Level, m.Username");
        $TmpStaff = $DB->to_array(false, MYSQLI_BOTH, array(6, 'Paranoia'));
        $DB->prepared_query("
			SELECT Name
			FROM staff_groups
			ORDER BY Sort");
        $Groups = $DB->collect('Name');
        array_unshift($Groups, 'Staff');
        $Staff = [];
        foreach ($Groups as $g) {
            $Staff[$g] = [];
        }
        foreach ($TmpStaff as $Class) {
            $Staff[$Class['StaffGroup']][] = $Class;
        }
        $Cache->cache_value('staff', $Staff, 180);
    }
    return $Staff;
}

function get_support() {
    return array(
        get_secondary(),
        get_staff(),
    );
}

function printSectionDiv($ClassName) {
?>
    </div><br />
    <div class='BoxBody' style='padding: 10px 10px 10px 10px;'>
        <h2 style='text-align: left;'><?= $ClassName ?></h2>
    <?
}
