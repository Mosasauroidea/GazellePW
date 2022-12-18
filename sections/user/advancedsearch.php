<?php

if (isset($_GET['search'])) {
    $_GET['search'] = trim($_GET['search']);
}

if (!empty($_GET['search'])) {
    if (preg_match('/^' . IP_REGEX . '$/', $_GET['search'])) {
        $_GET['ip'] = $_GET['search'];
    } elseif (preg_match('/^' . EMAIL_REGEX . '$/i', $_GET['search'])) {
        $_GET['email'] = $_GET['search'];
    } elseif (preg_match(USERNAME_REGEX, $_GET['search'])) {
        $DB->query("
			SELECT ID
			FROM users_main
			WHERE Username = '" . db_string($_GET['search']) . "'");
        if (list($ID) = $DB->next_record()) {
            header("Location: user.php?id=$ID");
            die();
        }
        $_GET['username'] = $_GET['search'];
    } else {
        $_GET['comment'] = $_GET['search'];
    }
}

foreach (array('ip', 'email', 'username', 'comment') as $field) {
    if (isset($_GET[$field])) {
        $_GET[$field] = trim($_GET[$field]);
    }
}

define('USERS_PER_PAGE', 30);

function wrap($String, $ForceMatch = '', $IPSearch = false) {
    if (!$ForceMatch) {
        global $Match;
    } else {
        $Match = $ForceMatch;
    }
    if ($Match == ' REGEXP ') {
        if (strpos($String, '\'') !== false || preg_match('/^.*\\\\$/i', $String)) {
            error(t('server.user.regex_contains_illegal_characters'));
        }
    } else {
        $String = db_string($String);
    }
    if ($Match == ' LIKE ') {
        // Fuzzy search
        // Stick in wildcards at beginning and end of string unless string starts or ends with |
        if (($String[0] != '|') && !$IPSearch) {
            $String = "%$String";
        } elseif ($String[0] == '|') {
            $String = substr($String, 1, strlen($String));
        }

        if (substr($String, -1, 1) != '|') {
            $String = "$String%";
        } else {
            $String = substr($String, 0, -1);
        }
    }
    $String = "'$String'";
    return $String;
}

function date_compare($Field, $Operand, $Date1, $Date2 = '') {
    $Date1 = db_string($Date1);
    $Date2 = db_string($Date2);
    $Return = array();

    switch ($Operand) {
        case 'on':
            $Return[] = " $Field >= '$Date1 00:00:00' ";
            $Return[] = " $Field <= '$Date1 23:59:59' ";
            break;
        case 'before':
            $Return[] = " $Field < '$Date1 00:00:00' ";
            break;
        case 'after':
            $Return[] = " $Field > '$Date1 23:59:59' ";
            break;
        case 'between':
            $Return[] = " $Field >= '$Date1 00:00:00' ";
            $Return[] = " $Field <= '$Date2 00:00:00' ";
            break;
    }

    return $Return;
}


function num_compare($Field, $Operand, $Num1, $Num2 = '') {

    if ($Num1 != 0) {
        $Num1 = db_string($Num1);
    }
    if ($Num2 != 0) {
        $Num2 = db_string($Num2);
    }

    $Return = array();

    switch ($Operand) {
        case 'equal':
            $Return[] = " $Field = '$Num1' ";
            break;
        case 'above':
            $Return[] = " $Field > '$Num1' ";
            break;
        case 'below':
            $Return[] = " $Field < '$Num1' ";
            break;
        case 'between':
            $Return[] = " $Field > '$Num1' ";
            $Return[] = " $Field < '$Num2' ";
            break;
        default:
            print_r($Return);
            die();
    }
    return $Return;
}

// Arrays, regexes, and all that fun stuff we can use for validation, form generation, etc

$DateChoices = array('inarray' => array('on', 'before', 'after', 'between'));
$SingleDateChoices = array('inarray' => array('on', 'before', 'after'));
$NumberChoices = array('inarray' => array('equal', 'above', 'below', 'between', 'buffer'));
$YesNo = array('inarray' => array('any', 'yes', 'no'));
$DisabledField = array('inarray' => array("DisableAnyone", "DisablePosting", "DisableAvatar", "DisableForums", "DisableIRC", "DisablePM", "DisableLeech", "DisableRequests", "DisableUpload", "DisablePoints", "DisableTagging", "DisableWiki", "DisableInvites", "DisableCheckAll", "DisableCheckSelf"));
$OrderVals = array(
    'inarray' =>
    array('Username', 'Ratio', 'IP Address', 'Email', 'Joined', 'Last Seen', 'Uploaded', 'Downloaded', 'Invites', 'Snatches')
);
$WayVals = array(
    'inarray' => array('Ascending', 'Descending')
);

$email_history_checked = true;
$ip_history_checked = true;
$disabled_ip_checked = true;

if (count($_GET)) {
    if (!empty($_GET['email_history']) || !empty($_GET['disabled_id']) || !empty($_GET['ip_history'])) {
        if (empty($_GET['email_history'])) {
            $email_history_checked = false;
        }
        if (empty($_GET['disabled_ip'])) {
            $disabled_ip_checked = false;
        }
        if (empty($_GET['ip_history'])) {
            $ip_history_checked = false;
        }
    }
    $DateRegex = array('regex' => '/\d{4}-\d{2}-\d{2}/');

    $ClassIDs = array();
    $SecClassIDs = array();
    foreach ($Classes as $ClassID => $Value) {
        if ($Value['Secondary']) {
            $SecClassIDs[] = $ClassID;
        } else {
            $ClassIDs[] = $ClassID;
        }
    }

    $Val->SetFields('comment', '0', 'string', 'Comment is too long.', array('maxlength' => 512));
    $Val->SetFields('disabled_invites', '0', 'inarray', 'Invalid disabled_invites field', $YesNo);


    $Val->SetFields('joined', '0', 'inarray', 'Invalid joined field', $DateChoices);
    $Val->SetFields('join1', '0', 'regex', 'Invalid join1 field', $DateRegex);
    $Val->SetFields('join2', '0', 'regex', 'Invalid join2 field', $DateRegex);

    $Val->SetFields('lastactive', '0', 'inarray', 'Invalid lastactive field', $DateChoices);
    $Val->SetFields('lastactive1', '0', 'regex', 'Invalid lastactive1 field', $DateRegex);
    $Val->SetFields('lastactive2', '0', 'regex', 'Invalid lastactive2 field', $DateRegex);

    $Val->SetFields('ratio', '0', 'inarray', 'Invalid ratio field', $NumberChoices);
    $Val->SetFields('uploaded', '0', 'inarray', 'Invalid uploaded field', $NumberChoices);
    $Val->SetFields('downloaded', '0', 'inarray', 'Invalid downloaded field', $NumberChoices);
    //$Val->SetFields('snatched', '0', 'inarray', 'Invalid snatched field', $NumberChoices);

    $Val->SetFields('matchtype', '0', 'inarray', 'Invalid matchtype field', array('inarray' => array('strict', 'fuzzy', 'regex')));

    $Val->SetFields('lockedaccount', '0', 'inarray', 'Invalid locked account field', array('inarray' => array('any', 'locked', 'unlocked')));

    $Val->SetFields('enabled', '0', 'inarray', 'Invalid enabled field', array('inarray' => array('', 0, 1, 2)));
    $Val->SetFields('class', '0', 'inarray', 'Invalid class', array('inarray' => $ClassIDs));
    $Val->SetFields('secclass', '0', 'inarray', 'Invalid class', array('inarray' => $SecClassIDs));
    $Val->SetFields('donor', '0', 'inarray', 'Invalid donor field', $YesNo);
    $Val->SetFields('warned', '0', 'inarray', 'Invalid warned field', $YesNo);
    $Val->SetFields('disabled', '0', 'inarray', 'Invalid disabled field', $DisabledField);

    $Val->SetFields('order', '0', 'inarray', 'Invalid ordering', $OrderVals);
    $Val->SetFields('way', '0', 'inarray', 'Invalid way', $WayVals);

    $Val->SetFields('passkey', '0', 'string', 'Invalid passkey', array('maxlength' => 32));
    $Val->SetFields('avatar', '0', 'string', 'Avatar URL too long', array('maxlength' => 512));
    $Val->SetFields('stylesheet', '0', 'inarray', 'Invalid stylesheet', array_unique(array_keys($Stylesheets)));
    $Val->SetFields('cc', '0', 'inarray', 'Invalid Country Code', array('maxlength' => 2));

    $Err = $Val->ValidateForm($_GET);

    if (!$Err) {
        // Passed validation. Let's rock.
        $RunQuery = false; // if we should run the search

        if (isset($_GET['matchtype']) && $_GET['matchtype'] == 'strict') {
            $Match = ' = ';
        } elseif (isset($_GET['matchtype']) && $_GET['matchtype'] == 'regex') {
            $Match = ' REGEXP ';
        } else {
            $Match = ' LIKE ';
        }

        $OrderTable = array(
            'Username' => 'um1.Username',
            'Joined' => 'ui1.JoinDate',
            'Email' => 'um1.Email',
            'IP' => 'um1.IP',
            'Last Seen' => 'um1.LastAccess',
            'Uploaded' => 'um1.Uploaded',
            'Downloaded' => 'um1.Downloaded',
            'Ratio' => '(um1.Uploaded / um1.Downloaded)',
            'Invites' => 'um1.Invites',
            'Snatches' => 'Snatches'
        );

        $WayTable = array('Ascending' => 'ASC', 'Descending' => 'DESC');

        $Where = array();
        $Having = array();
        $Join = array();
        $Group = array();
        $Distinct = '';
        $Order = '';


        $SQL = '
				SQL_CALC_FOUND_ROWS
				um1.ID,
				um1.Username,
				um1.Uploaded,
				um1.Downloaded,';
        if ($_GET['snatched'] == 'off') {
            $SQL .= "'X' AS Snatches,";
        } else {
            $SQL .= "
				(
					SELECT COUNT(xs.uid)
					FROM xbt_snatched AS xs
					WHERE xs.uid = um1.ID
				) AS Snatches,";
        }
        if ($_GET['invitees'] == 'off') {
            $SQL .= "'X' AS Invitees,";
        } else {
            $SQL .= "
			(
				SELECT COUNT(ui2.UserID)
				FROM users_info AS ui2
				WHERE um1.ID = ui2.Inviter
  			) AS Invitees,";
        }
        $SQL .= '
				um1.PermissionID,
				um1.Email,
				um1.Enabled,
				um1.IP,
				um1.Invites,
				ui1.DisableInvites,
				ui1.Warned,
				ui1.Donor,
				ui1.JoinDate,
				um1.LastAccess
			FROM users_main AS um1
				JOIN users_info AS ui1 ON ui1.UserID = um1.ID ';


        if (!empty($_GET['username'])) {
            $Where[] = 'um1.Username' . $Match . wrap($_GET['username']);
        }

        if (!empty($_GET['email'])) {
            if (isset($_GET['email_history'])) {
                $Distinct = 'DISTINCT ';
                $Join['he'] = ' JOIN users_history_emails AS he ON he.UserID = um1.ID ';
                $Where[] = ' he.Email ' . $Match . wrap($_GET['email']);
            } else {
                $Where[] = 'um1.Email' . $Match . wrap($_GET['email']);
            }
        }

        if (!empty($_GET['email_cnt']) && is_number($_GET['email_cnt'])) {
            $Query = "
				SELECT UserID
				FROM users_history_emails
				GROUP BY UserID
				HAVING COUNT(DISTINCT Email) ";
            if ($_GET['emails_opt'] === 'equal') {
                $operator = '=';
            }
            if ($_GET['emails_opt'] === 'above') {
                $operator = '>';
            }
            if ($_GET['emails_opt'] === 'below') {
                $operator = '<';
            }
            $Query .= $operator . ' ' . $_GET['email_cnt'];
            $DB->query($Query);
            $Users = implode(',', $DB->collect('UserID'));
            if (!empty($Users)) {
                $Where[] = "um1.ID IN ($Users)";
            }
        }


        if (!empty($_GET['ip'])) {
            if (isset($_GET['ip_history'])) {
                $Distinct = 'DISTINCT ';
                $Join['hi'] = ' JOIN users_history_ips AS hi ON hi.UserID = um1.ID ';
                $Where[] = ' hi.IP ' . $Match . wrap($_GET['ip'], '', true);
            } else {
                $Where[] = 'um1.IP' . $Match . wrap($_GET['ip'], '', true);
            }
        }

        if ($_GET['lockedaccount'] != '' && $_GET['lockedaccount'] != 'any') {
            $Join['la'] = '';

            if ($_GET['lockedaccount'] == 'unlocked') {
                $Join['la'] .= ' LEFT';
                $Where[] = ' la.UserID IS NULL';
            }

            $Join['la'] .= ' JOIN locked_accounts AS la ON la.UserID = um1.ID ';
        }



        if (!empty($_GET['cc'])) {
            if ($_GET['cc_op'] == 'equal') {
                $Where[] = "um1.ipcc = '" . db_string($_GET['cc']) . "'";
            } else {
                $Where[] = "um1.ipcc != '" . db_string($_GET['cc']) . "'";
            }
        }

        if (!empty($_GET['tracker_ip'])) {
            $Distinct = 'DISTINCT ';
            $Join['xfu'] = ' JOIN xbt_files_users AS xfu ON um1.ID = xfu.uid ';
            $Where[] = '( xfu.ip ' . $Match . wrap($_GET['tracker_ip'], '', true) . 'or  xfu.ipv6 ' . $Match . wrap($_GET['tracker_ip'], '', true) . ')';
        }

        //      if (!empty($_GET['tracker_ip'])) {
        //              $Distinct = 'DISTINCT ';
        //              $Join['xs'] = ' JOIN xbt_snatched AS xs ON um1.ID = xs.uid ';
        //              $Where[] = ' xs.IP '.$Match.wrap($_GET['ip']);
        //      }

        if (!empty($_GET['comment'])) {
            $Where[] = 'ui1.AdminComment' . $Match . wrap($_GET['comment']);
        }


        if (strlen($_GET['invites1'])) {
            $Invites1 = round($_GET['invites1']);
            $Invites2 = round($_GET['invites2']);
            $Where[] = implode(' AND ', num_compare('Invites', $_GET['invites'], $Invites1, $Invites2));
        }

        if (strlen($_GET['invitees1']) && $_GET['invitees'] != 'off') {
            $Invitees1 = round($_GET['invitees1']);
            $Invitees2 = round($_GET['invitees2']);
            $Having[] = implode(' AND ', num_compare('Invitees', $_GET['invitees'], $Invitees1, $Invitees2));
        }

        if ($_GET['disabled_invites'] == 'yes') {
            $Where[] = 'ui1.DisableInvites = \'1\'';
        } elseif ($_GET['disabled_invites'] == 'no') {
            $Where[] = 'ui1.DisableInvites = \'0\'';
        }

        if ($_GET['disabled']) {
            $DisabledSQL = array("DisablePosting", "DisableAvatar", "DisableForums", "DisableIRC", "DisablePM", "DisableRequests", "DisableUpload", "DisablePoints", "DisableTagging", "DisableWiki", "DisableInvites", "DisableCheckAll", "DisableCheckSelf");
            if ($_GET['disabled'] == "DisableLeech") {
                $Where[] = 'um1.can_leech = \'0\'';
            } else if ($_GET['disabled'] == "DisableAnyone") {
                $sql = "um1.can_leech = '0'";
                foreach ($DisabledSQL as $d) {
                    $sql .= " or ui1.$d = '1'";
                }
                $Where[] = "($sql)";
            } else {
                $Where[] = 'ui1.' . $_GET['disabled'] . ' = \'1\'';
            }
        }

        if ($_GET['join1']) {
            $Where[] = implode(' AND ', date_compare('ui1.JoinDate', $_GET['joined'], $_GET['join1'], $_GET['join2']));
        }

        if ($_GET['lastactive1']) {
            $Where[] = implode(' AND ', date_compare('um1.LastAccess', $_GET['lastactive'], $_GET['lastactive1'], $_GET['lastactive2']));
        }

        if ($_GET['ratio1']) {
            $Decimals = strlen(array_pop(explode('.', $_GET['ratio1'])));
            if (!$Decimals) {
                $Decimals = 0;
            }
            $Where[] = implode(' AND ', num_compare("ROUND(Uploaded/Downloaded,$Decimals)", $_GET['ratio'], $_GET['ratio1'], $_GET['ratio2']));
        }

        if (strlen($_GET['uploaded1'])) {
            $Upload1 = round($_GET['uploaded1']);
            $Upload2 = round($_GET['uploaded2']);
            if ($_GET['uploaded'] != 'buffer') {
                $Where[] = implode(' AND ', num_compare('ROUND(Uploaded / 1024 / 1024 / 1024)', $_GET['uploaded'], $Upload1, $Upload2));
            } else {
                $Where[] = implode(' AND ', num_compare('ROUND((Uploaded / 1024 / 1024 / 1024) - (Downloaded / 1024 / 1024 / 1024))', 'between', $Upload1 * 0.9, $Upload1 * 1.1));
            }
        }

        if (strlen($_GET['downloaded1'])) {
            $Download1 = round($_GET['downloaded1']);
            $Download2 = round($_GET['downloaded2']);
            $Where[] = implode(' AND ', num_compare('ROUND(Downloaded / 1024 / 1024 / 1024)', $_GET['downloaded'], $Download1, $Download2));
        }

        if (strlen($_GET['snatched1'])) {
            $Snatched1 = round($_GET['snatched1']);
            $Snatched2 = round($_GET['snatched2']);
            $Having[] = implode(' AND ', num_compare('Snatches', $_GET['snatched'], $Snatched1, $Snatched2));
        }

        if ($_GET['enabled'] != '') {
            $Where[] = 'um1.Enabled = ' . wrap($_GET['enabled'], '=');
        }

        if ($_GET['class'] != '') {
            $Where[] = 'um1.PermissionID = ' . wrap($_GET['class'], '=');
        }

        if ($_GET['secclass'] != '') {
            $Join['ul'] = ' JOIN users_levels AS ul ON um1.ID = ul.UserID ';
            $Where[] = 'ul.PermissionID = ' . wrap($_GET['secclass'], '=');
        }

        if ($_GET['donor'] == 'yes') {
            $Where[] = 'ui1.Donor = \'1\'';
        } elseif ($_GET['donor'] == 'no') {
            $Where[] = 'ui1.Donor = \'0\'';
        }

        if ($_GET['warned'] == 'yes') {
            $Where[] = 'ui1.Warned != \'0000-00-00 00:00:00\'';
        } elseif ($_GET['warned'] == 'no') {
            $Where[] = 'ui1.Warned = \'0000-00-00 00:00:00\'';
        }

        if ($_GET['disabled_ip']) {
            $Distinct = 'DISTINCT ';
            if ($_GET['ip_history']) {
                if (!isset($Join['hi'])) {
                    $Join['hi'] = ' JOIN users_history_ips AS hi ON hi.UserID = um1.ID ';
                }
                $Join['hi2'] = ' JOIN users_history_ips AS hi2 ON hi2.IP = hi.IP ';
                $Join['um2'] = ' JOIN users_main AS um2 ON um2.ID = hi2.UserID AND um2.Enabled = \'2\' ';
            } else {
                $Join['um2'] = ' JOIN users_main AS um2 ON um2.IP = um1.IP AND um2.Enabled = \'2\' ';
            }
        }

        if (!empty($_GET['passkey'])) {
            $Where[] = 'um1.torrent_pass' . $Match . wrap($_GET['passkey']);
        }

        if (!empty($_GET['avatar'])) {
            $Where[] = 'ui1.Avatar' . $Match . wrap($_GET['avatar']);
        }

        if ($_GET['stylesheet'] != '') {
            $Where[] = 'ui1.StyleID = ' . wrap($_GET['stylesheet'], '=');
        }

        if ($OrderTable[$_GET['order']] && $WayTable[$_GET['way']]) {
            $Order = ' ORDER BY ' . $OrderTable[$_GET['order']] . ' ' . $WayTable[$_GET['way']] . ' ';
        }

        //---------- Finish generating the search string

        $SQL = 'SELECT ' . $Distinct . $SQL;
        $SQL .= implode(' ', $Join);

        if (count($Where)) {
            $SQL .= ' WHERE ' . implode(' AND ', $Where);
        }

        if (count($Group)) {
            $SQL .= " GROUP BY " . implode(' ,', $Group);
        }

        if (count($Having)) {
            $SQL .= ' HAVING ' . implode(' AND ', $Having);
        }

        $SQL .= $Order;

        list($Page, $Limit) = Format::page_limit(USERS_PER_PAGE);
        $SQL .= " LIMIT $Limit";
    } else {
        error($Err);
    }
}
$Results = $DB->query($SQL);
$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();
$Pages = Format::get_pages($Page, $NumResults, USERS_PER_PAGE, 11);
View::show_header(t('server.user.user_search'), '', 'PageUserAdvancedSearch');
$DB->set_query_id($Results);
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.user.user_search') ?>
        </div>
    </div>
    <div class="BodyContent">
        <form class="Form SearchPage Box SearchUserAdvanced is-fullWidth" name="users" action="user.php" method="get">
            <input type="hidden" name="action" value="search" />
            <div class="SearchPageBody">
                <table class="Form-rowList">
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.user_basic') ?>:</td>
                        <td class="Form-inputs is-splitEven">
                            <input placeholder="<?= t('server.user.username') ?>" class="Input" type="text" name="username" size="20" value="<?= display_str($_GET['username']) ?>" />
                            <input placeholder="<?= t('server.user.email_address') ?>" class="Input" type="text" name="email" size="20" value="<?= display_str($_GET['email']) ?>" />
                            <input placeholder="<?= t('server.user.passkey') ?>" class="Input" type="text" name="passkey" size="20" value="<?= display_str($_GET['passkey']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.to_fuzzy_search_for_a_block_of_addresses_title') ?>"><?= t('server.user.ip_address') ?>:</td>
                        <td class="Form-inputs is-splitEven">
                            <input placeholder="<?= t('server.user.ip_address') ?>" class="Input" type="text" name="ip" size="20" value="<?= display_str($_GET['ip']) ?>" />
                            <input placeholder="<?= t('server.user.tracker_ip') ?>" class="Input" type="text" name="tracker_ip" size="20" value="<?= display_str($_GET['tracker_ip']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.account_info') ?>:</td>
                        <td class="Form-inputs">

                            <select class="Input" name="enabled">
                                <option class="Select-option" value="" <? if ($_GET['enabled'] === '') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.enabled') ?></option>
                                <option class="Select-option" value="0" <? if ($_GET['enabled'] === '0') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.unconfirmed') ?></option>
                                <option class="Select-option" value="1" <? if ($_GET['enabled'] === '1') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.enabled') ?></option>
                                <option class="Select-option" value="2" <? if ($_GET['enabled'] === '2') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.disabled') ?></option>
                            </select>
                            <select class="Input" name="lockedaccount">
                                <option class="Select-option" value="any" <? if ($_GET['lockedaccount'] == 'any') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.locked_account') ?></option>
                                <option class="Select-option" value="locked" <? if ($_GET['lockedaccount'] == 'locked') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.locked') ?></option>
                                <option class="Select-option" value="unlocked" <? if ($_GET['lockedaccount'] == 'unlocked') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.ulocked') ?></option>
                            </select>

                            <select class="Input" name="disabled">
                                <option class="Select-option" value="" <? if ($_GET['disabled'] === '') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.disabled_privilege') ?></option>
                                <option class="Select-option" value="DisableAnyone" <? if ($_GET['disabled'] === 'DisableAnyone') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.anyone') ?></option>
                                <option class="Select-option" value="DisablePosting" <? if ($_GET['disabled'] === 'DisablePosting') {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.user.posting') ?></option>
                                <option class="Select-option" value="DisableAvatar" <? if ($_GET['disabled'] === 'DisableAvatar') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.avatar') ?></option>
                                <option class="Select-option" value="DisableForums" <? if ($_GET['disabled'] === 'DisableForums') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.forums') ?></option>
                                <option class="Select-option" value="DisableIRC" <? if ($_GET['disabled'] === 'DisableIRC') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.irc') ?></option>
                                <option class="Select-option" value="DisablePM" <? if ($_GET['disabled'] === 'DisablePM') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.pm') ?></option>
                                <option class="Select-option" value="DisableLeech" <? if ($_GET['disabled'] === 'DisableLeech') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.leech') ?></option>
                                <option class="Select-option" value="DisableRequests" <? if ($_GET['disabled'] === 'DisableRequests') {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.common.requests') ?></option>
                                <option class="Select-option" value="DisableUpload" <? if ($_GET['disabled'] === 'DisableUpload') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.torrent_upload') ?></option>
                                <option class="Select-option" value="DisablePoints" <? if ($_GET['disabled'] === 'DisablePoints') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.bonus_points') ?></option>
                                <option class="Select-option" value="DisableTagging" <? if ($_GET['disabled'] === 'DisableTagging') {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.user.tagging') ?></option>
                                <option class="Select-option" value="DisableWiki" <? if ($_GET['disabled'] === 'DisableWiki') {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= t('server.user.wiki') ?></option>
                                <option class="Select-option" value="DisableInvites" <? if ($_GET['disabled'] === 'DisableInvites') {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.user.invites') ?></option>
                                <option class="Select-option" value="DisableCheckAll" <? if ($_GET['disabled'] === 'DisableCheckAll') {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.user.check_all_torrents') ?></option>
                                <option class="Select-option" value="DisableCheckSelf" <? if ($_GET['disabled'] === 'DisableCheckSelf') {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.user.check_self_torrents') ?></option>
                            </select>
                            <select class="Input" name="disabled_invites">
                                <option class="Select-option" value="" <? if ($_GET['disabled_invites'] === '') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.disabled_invites') ?></option>
                                <option class="Select-option" value="yes" <? if ($_GET['disabled_invites'] === 'yes') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.yes') ?></option>
                                <option class="Select-option" value="no" <? if ($_GET['disabled_invites'] === 'no') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.no') ?></option>
                            </select>

                            <select class="Input" name="warned">
                                <option class="Select-option" value="" <? if ($_GET['warned'] === '') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.warned') ?></option>
                                <option class="Select-option" value="yes" <? if ($_GET['warned'] === 'yes') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.yes') ?></option>
                                <option class="Select-option" value="no" <? if ($_GET['warned'] === 'no') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.no') ?></option>
                            </select>
                            <select class="Input" name="donor">
                                <option class="Select-option" value="" <? if ($_GET['donor'] === '') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.donor') ?></option>
                                <option class="Select-option" value="yes" <? if ($_GET['donor'] === 'yes') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.yes') ?></option>
                                <option class="Select-option" value="no" <? if ($_GET['donor'] === 'no') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.no') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.joined') ?>:</td>
                        <td class="Form-inputs ">
                            <select class="Input" name="joined">
                                <option class="Select-option" value="on" <? if ($_GET['joined'] === 'on') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.on') ?></option>
                                <option class="Select-option" value="before" <? if ($_GET['joined'] === 'before') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.before') ?></option>
                                <option class="Select-option" value="after" <? if ($_GET['joined'] === 'after') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.after') ?></option>
                                <option class="Select-option" value="between" <? if ($_GET['joined'] === 'between') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.between') ?></option>
                            </select>
                            <input class="Input is-small" type="date" name="join1" size="10" value="<?= display_str($_GET['join1']) ?>" placeholder="YYYY-MM-DD" />
                            <input class="Input is-small" type="date" name="join2" size="10" value="<?= display_str($_GET['join2']) ?>" placeholder="YYYY-MM-DD" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.last_active') ?>:</td>
                        <td class="Form-inputs ">
                            <select class="Input" name="lastactive">
                                <option class="Select-option" value="on" <? if ($_GET['lastactive'] === 'on') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.on') ?></option>
                                <option class="Select-option" value="before" <? if ($_GET['lastactive'] === 'before') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.before') ?></option>
                                <option class="Select-option" value="after" <? if ($_GET['lastactive'] === 'after') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.after') ?></option>
                                <option class="Select-option" value="between" <? if ($_GET['lastactive'] === 'between') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.between') ?></option>
                            </select>
                            <input class="Input is-small" type="date" name="lastactive1" size="10" value="<?= display_str($_GET['lastactive1']) ?>" placeholder="YYYY-MM-DD" />
                            <input class="Input is-small" type="date" name="lastactive2" size="10" value="<?= display_str($_GET['lastactive2']) ?>" placeholder="YYYY-MM-DD" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.p_class') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="class">
                                <option class="Select-option" value="" <? if ($_GET['class'] === '') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.primary_class') ?></option>
                                <? foreach ($ClassLevels as $Class) {
                                    if ($Class['Secondary']) {
                                        continue;
                                    }
                                ?>
                                    <option class="Select-option" value="<?= $Class['ID'] ?>" <? if ($_GET['class'] === $Class['ID']) {
                                                                                                    echo ' selected="selected"';
                                                                                                } ?>><?= Format::cut_string($Class['Name'], 10, 1, 1) . ' (' . $Class['Level'] . ')' ?></option>
                                <?  } ?>
                            </select>
                            <select class="Input" name="secclass">
                                <option class="Select-option" value="" <? if ($_GET['secclass'] === '') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.user.secondary_class') ?></option>
                                <? $Secondaries = array();
                                // Neither level nor ID is particularly useful when searching secondary classes, so let's do some
                                // kung-fu to sort them alphabetically.
                                $fnc = function ($Class1, $Class2) {
                                    return strcmp($Class1['Name'], $Class2['Name']);
                                };
                                foreach ($ClassLevels as $Class) {
                                    if (!$Class['Secondary']) {
                                        continue;
                                    }
                                    $Secondaries[] = $Class;
                                }
                                usort($Secondaries, $fnc);
                                foreach ($Secondaries as $Class) {
                                ?>
                                    <option class="Select-option" value="<?= $Class['ID'] ?>" <? if ($_GET['secclass'] === $Class['ID']) {
                                                                                                    echo ' selected="selected"';
                                                                                                } ?>><?= Format::cut_string($Class['Name'], 20, 1, 1) ?></option>
                                <?  } ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.ratio') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="ratio">
                                <option class="Select-option" value="equal" <? if ($_GET['ratio'] === 'equal') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.equal') ?></option>
                                <option class="Select-option" value="above" <? if ($_GET['ratio'] === 'above') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.above') ?></option>
                                <option class="Select-option" value="below" <? if ($_GET['ratio'] === 'below') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.below') ?></option>
                                <option class="Select-option" value="between" <? if ($_GET['ratio'] === 'between') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.between') ?></option>
                            </select>
                            <input class="Input is-small" type="number" name="ratio1" size="6" value="<?= display_str($_GET['ratio1']) ?>" />
                            <input class="Input is-small" type="number" name="ratio2" size="6" value="<?= display_str($_GET['ratio2']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">

                        <td class="Form-label" data-tooltip="<?= t('server.user.units_are_in_gibibytes') ?>"><?= t('server.user.uploaded') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="uploaded">
                                <option class="Select-option" value="equal" <? if ($_GET['uploaded'] === 'equal') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.equal') ?></option>
                                <option class="Select-option" value="above" <? if ($_GET['uploaded'] === 'above') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.above') ?></option>
                                <option class="Select-option" value="below" <? if ($_GET['uploaded'] === 'below') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.below') ?></option>
                                <option class="Select-option" value="between" <? if ($_GET['uploaded'] === 'between') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.between') ?></option>
                                <option class="Select-option" value="buffer" <? if ($_GET['uploaded'] === 'buffer') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.buffer') ?></option>
                            </select>
                            <input class="Input is-small" type="number" name="uploaded1" size="6" value="<?= display_str($_GET['uploaded1']) ?>" />
                            <input class="Input is-small" type="number" name="uploaded3" size="6" value="<?= display_str($_GET['uploaded2']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.units_are_in_gibibytes') ?>"><?= t('server.user.downloaded') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="downloaded">
                                <option class="Select-option" value="equal" <? if ($_GET['downloaded'] === 'equal') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.equal') ?></option>
                                <option class="Select-option" value="above" <? if ($_GET['downloaded'] === 'above') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.above') ?></option>
                                <option class="Select-option" value="below" <? if ($_GET['downloaded'] === 'below') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.below') ?></option>
                                <option class="Select-option" value="between" <? if ($_GET['downloaded'] === 'between') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.between') ?></option>
                            </select>
                            <input class="Input is-small" type="number" name="downloaded1" size="6" value="<?= display_str($_GET['downloaded1']) ?>" />
                            <input class="Input is-small" type="number" name="downloaded2" size="6" value="<?= display_str($_GET['downloaded2']) ?>" />
                        </td>

                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.common.snatched') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="snatched">
                                <option class="Select-option" value="equal" <? if (isset($_GET['snatched']) && $_GET['snatched'] === 'equal') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.equal') ?></option>
                                <option class="Select-option" value="above" <? if (isset($_GET['snatched']) && $_GET['snatched'] === 'above') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.above') ?></option>
                                <option class="Select-option" value="below" <? if (isset($_GET['snatched']) && $_GET['snatched'] === 'below') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.below') ?></option>
                                <option class="Select-option" value="between" <? if (isset($_GET['snatched']) && $_GET['snatched'] === 'between') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.between') ?></option>
                                <option class="Select-option" value="off" <? if (!isset($_GET['snatched']) || $_GET['snatched'] === 'off') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.off') ?></option>
                            </select>
                            <input class="Input is-small" type="number" name="snatched1" size="6" value="<?= display_str($_GET['snatched1']) ?>" />
                            <input class="Input is-small" type="number" name="snatched2" size="6" value="<?= display_str($_GET['snatched2']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.number_of_invites') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="invites">
                                <option class="Select-option" value="equal" <? if ($_GET['invites'] === 'equal') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.equal') ?></option>
                                <option class="Select-option" value="above" <? if ($_GET['invites'] === 'above') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.above') ?></option>
                                <option class="Select-option" value="below" <? if ($_GET['invites'] === 'below') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.below') ?></option>
                                <option class="Select-option" value="between" <? if ($_GET['invites'] === 'between') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.between') ?></option>
                            </select>
                            <input class="Input is-small" type="number" name="invites1" size="6" value="<?= display_str($_GET['invites1']) ?>" />
                            <input class="Input is-small" type="number" name="invites2" size="6" value="<?= display_str($_GET['invites2']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.number_of_invitees') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="invitees">
                                <option class="Select-option" value="equal" <?= isset($_GET['invitees']) && $_GET['invitees'] == 'equal' ? 'selected' : '' ?>><?= t('server.user.equal') ?></option>
                                <option class="Select-option" value="above" <?= isset($_GET['invitees']) && $_GET['invitees'] == 'above' ? 'selected' : '' ?>><?= t('server.user.above') ?></option>
                                <option class="Select-option" value="below" <?= isset($_GET['invitees']) && $_GET['invitees'] == 'below' ? 'selected' : '' ?>><?= t('server.user.below') ?></option>
                                <option class="Select-option" value="between" <?= isset($_GET['invitees']) && $_GET['invitees'] == 'between' ? 'selected' : '' ?>><?= t('server.user.between') ?></option>
                                <option class="Select-option" value="off" <?= !isset($_GET['invitees']) || $_GET['invitees'] == 'off' ? 'selected' : '' ?>><?= t('server.user.off') ?></option>
                            </select>
                            <input class="Input is-small" type="number" name="invitees1" size="6" value="<?= display_str($_GET['invitees1']) ?>" />
                            <input class="Input is-small" type="number" name="invitees2" size="6" value="<?= display_str($_GET['invitees2']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.number_of_emails') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="emails_opt">
                                <option class="Select-option" value="equal" <? if ($_GET['emails_opt'] === 'equal') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.equal') ?></option>
                                <option class="Select-option" value="above" <? if ($_GET['emails_opt'] === 'above') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.above') ?></option>
                                <option class="Select-option" value="below" <? if ($_GET['emails_opt'] === 'below') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.below') ?></option>
                            </select>
                            <input class="Input is-small" type="date" name="email_cnt" size="6" value="<?= display_str($_GET['email_cnt']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.supports_partial_url_matching') ?>"><?= t('server.user.avatar_url') ?>:</td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" name="avatar" size="20" value="<?= display_str($_GET['avatar']) ?>" />
                        </td>
                    </tr>
                    <? if (check_perms('users_mod')) { ?>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.user.staff_notes') ?>:</td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" name="comment" size="20" value="<?= display_str($_GET['comment']) ?>" />
                            </td>
                        </tr>
                    <?  } ?>

                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.common.others') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Checkbox" data-tooltip="<?= t('server.user.disabled_accounts_linked_by_ip_title') ?>">
                                <input class="Input" type="checkbox" name="disabled_ip" id="disabled_ip" />
                                <label class="Checkbox-label" for="disabled_ip"><?= t('server.user.disabled_accounts_linked_by_ip') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ip_history" id="ip_history" <? if ($ip_history_checked) {
                                                                                                            echo ' checked="checked"';
                                                                                                        } ?> />
                                <label class="Checkbox-label" data-tooltip="<?= t('server.user.disabled_accounts_linked_by_ip_must_also_be_checked') ?>" for="ip_history"> <?= t('server.user.ip_history') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="email_history" id="email_history" <? if ($email_history_checked) {
                                                                                                                    echo ' checked="checked"';
                                                                                                                } ?> />
                                <label class="Checkbox-label" data-tooltip="<?= t('server.user.also_search_the_email_addresses_the_member_used_in_the_past') ?>" for="email_history"><?= t('server.user.email_history') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.stylesheet') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="stylesheet" id="stylesheet">
                                <option class="Select-option" value=""><?= t('server.user.do_not_care') ?></option>
                                <? foreach ($Stylesheets as $Style) { ?>
                                    <option class="Select-option" value="<?= $Style['ID'] ?>" <? Format::selected('stylesheet', $Style['ID']) ?>><?= $Style['ProperName'] ?></option>
                                <?                  } ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.country_code_title') ?>"><?= t('server.user.country_code') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="cc_op">
                                <option class="Select-option" value="equal" <? if ($_GET['cc_op'] === 'equal') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.user.equals') ?></option>
                                <option class="Select-option" value="not_equal" <? if ($_GET['cc_op'] === 'not_equal') {
                                                                                    echo ' selected="selected"';
                                                                                } ?>><?= t('server.user.not_equal') ?></option>
                            </select>
                            <input class="Input is-small" type="text" name="cc" size="2" value="<?= display_str($_GET['cc']) ?>" />
                        </td>
                    </tr>

                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.search_type') ?>:</td>
                        <td class="Form-inputs">
                            <div class="RadioGroup">
                                <div class="Radio">
                                    <input class="Input" type="radio" name="matchtype" id="strict_match_type" value="strict" <? if ($_GET['matchtype'] == 'strict' || !$_GET['matchtype']) {
                                                                                                                                    echo ' checked="checked"';
                                                                                                                                } ?> />
                                    <label class="Radio-label" data-tooltip="<?= t('server.user.search_type_strict_title') ?>" for="strict_match_type"><?= t('server.user.search_type_strict') ?></label>
                                </div>
                                <div class="Radio">
                                    <input class="Input" type="radio" name="matchtype" id="fuzzy_match_type" value="fuzzy" <? if ($_GET['matchtype'] == 'fuzzy' || !$_GET['matchtype']) {
                                                                                                                                echo ' checked="checked"';
                                                                                                                            } ?> />
                                    <label class="Radio-label" data-tooltip="<?= t('server.user.search_type_fuzzy_title') ?>" for="fuzzy_match_type"><?= t('server.user.search_type_fuzzy') ?></label>
                                </div>
                                <div class="Radio">
                                    <input class="Input" type="radio" name="matchtype" id="regex_match_type" value="regex" <? if ($_GET['matchtype'] == 'regex') {
                                                                                                                                echo ' checked="checked"';
                                                                                                                            } ?> />
                                    <label class="Radio-label" data-tooltip="<?= t('server.user.search_type_regex_title') ?>" for="regex_match_type"><?= t('server.user.search_type_regex') ?></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.order') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="order">
                                <?
                                foreach (array_shift($OrderVals) as $Cur) { ?>
                                    <option class="Select-option" value="<?= $Cur ?>" <? if (isset($_GET['order']) && $_GET['order'] == $Cur || (!isset($_GET['order']) && $Cur == 'Joined')) {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.user.' . str_replace(' ', '_', strtolower($Cur))) ?></option>
                                <?                      } ?>
                            </select>
                            <select class="Input" name="way">
                                <? foreach (array_shift($WayVals) as $Cur) { ?>
                                    <option class="Select-option" value="<?= $Cur ?>" <? if (isset($_GET['way']) && $_GET['way'] == $Cur || (!isset($_GET['way']) && $Cur == 'Descending')) {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= t('server.tools.' . strtolower($Cur)) ?></option>
                                <?                      } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="SearchPageFooter">
                <div class="SearchPageFooter-resultCount">
                    <?= number_format($NumResults) ?><?= t('server.user.space_results') ?>
                </div>
                <div class="SearchPageFooter-actions">
                    <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
                </div>
            </div>
        </form>
        <?

        if ($NumResults != 0) {
        ?>
            <div class="BodyNavLinks">
                <?
                echo $Pages;
                ?>
            </div>
            <div class="TableContainer">
                <table class="TableUserSearch Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.user.username') ?></td>
                        <td class="Table-cell"><?= t('server.user.ratio') ?></td>
                        <td class="Table-cell"><?= t('server.user.ip_address') ?></td>
                        <td class="Table-cell"><?= t('server.user.email') ?></td>
                        <td class="Table-cell"><?= t('server.user.joined') ?></td>
                        <td class="Table-cell"><?= t('server.user.last_seen') ?></td>
                        <td class="Table-cell"><?= t('server.user.uploaded') ?></td>
                        <td class="Table-cell"><?= t('server.user.downloaded') ?></td>
                        <td class="Table-cell"><?= t('server.user.downloads') ?></td>
                        <td class="Table-cell"><?= t('server.common.snatched') ?></td>
                        <td class="Table-cell"><?= t('server.user.invites') ?></td>
                        <? if (isset($_GET['invitees']) && $_GET['invitees'] != 'off') { ?>
                            <td class="Table-cell"><?= t('server.user.invitees') ?></td>
                        <?      } ?>
                    </tr>
                    <?
                    while (list($UserID, $Username, $Uploaded, $Downloaded, $Snatched, $Invitees, $Class, $Email, $Enabled, $IP, $Invites, $DisableInvites, $Warned, $Donor, $JoinDate, $LastAccess) = $DB->next_record()) { ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><?= Users::format_username($UserID, true, true, true, true) ?></td>
                            <td class="Table-cell"><?= Format::get_ratio_html($Uploaded, $Downloaded) ?></td>
                            <td class="Table-cell"><?= display_str($IP) ?> (<?= Tools::get_country_code_by_ajax($IP) ?>)</td>
                            <td class="Table-cell"><?= display_str($Email) ?></td>
                            <td class="Table-cell"><?= time_diff($JoinDate) ?></td>
                            <td class="Table-cell"><?= time_diff($LastAccess) ?></td>
                            <td class="Table-cell"><?= Format::get_size($Uploaded) ?></td>
                            <td class="Table-cell"><?= Format::get_size($Downloaded) ?></td>
                            <? $DB->query("
				SELECT COUNT(ud.UserID)
				FROM users_downloads AS ud
					JOIN torrents AS t ON t.ID = ud.TorrentID
				WHERE ud.UserID = $UserID");
                            list($Downloads) = $DB->next_record();
                            $DB->set_query_id($Results);
                            ?>
                            <td class="Table-cell"><?= number_format((int)$Downloads) ?></td>
                            <td class="Table-cell"><?= (is_numeric($Snatched) ? number_format($Snatched) : display_str($Snatched)) ?></td>
                            <td class="Table-cell"><? if ($DisableInvites) {
                                                        echo 'X';
                                                    } else {
                                                        echo number_format($Invites);
                                                    } ?></td>
                            <? if (isset($_GET['invitees']) && $_GET['invitees'] != 'off') { ?>
                                <td class="Table-cell"><?= number_format($Invitees) ?></td>
                            <?      } ?>
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
<? } else {
            View::line(t('server.common.no_results'));
        }
?>
</div>
<?
View::show_footer();
?>