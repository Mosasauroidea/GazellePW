<?
function userIDs2Usernames($UserIDs, $Space) {
    $UserIDs = explode(',', $UserIDs);
    $Usernames = '';
    foreach ($UserIDs as $UserID) {
        $Usernames .= Users::format_username($UserID) . $Space;
    }
    return $Usernames;
}
if (isset($_GET['id'])) {
    $DB->query("SELECT * from events_reward_log where id=" . intval($_GET['id']));
    $Recode = $DB->next_record(MYSQLI_ASSOC);
?>
    <h3><?= Lang::get('tools', 'userids') ?></h3>
    <p><?= userIDs2Usernames($Recode['UserIDs'], '<br>') ?></p>
    <h3><?= Lang::get('tools', 'byuserid') ?></h3>
    <p><?= Users::format_username($Recode['ByUserID']) ?></p>
    <h3><?= Lang::get('tools', 'invites') ?></h3>
    <p><?= $Recode['Invites'] ?></p>
    <h3><?= Lang::get('tools', 'invitestime') ?></h3>
    <p><?= $Recode['InvitesTime'] ?></p>
    <h3><?= Lang::get('tools', 'tokens') ?></h3>
    <p><?= $Recode['Tokens'] ?></p>
    <h3><?= Lang::get('tools', 'tokenstime') ?></h3>
    <p><?= $Recode['TokensTime'] ?></p>
    <h3><?= Lang::get('tools', 'Bonus') ?></h3>
    <p><?= $Recode['Bonus'] ?></p>
    <h3><?= Lang::get('tools', 'badge') ?></h3>
    <p><?
        if ($Recode['Badge']) {
            $Badge = Badges::get_badges_by_id($Recode['Badge']);
            echo Lang::get('badges', $Badge['Label'] . '_badge_name');
        }
        ?></p>
    <h3><?= Lang::get('tools', 'remark') ?></h3>
    <p><?= $Recode['Remark'] ?></p>
    <h3><?= Lang::get('tools', 'time') ?></h3>
    <p><?= $Recode['Time'] ?></p>
<?
    exit();
}
View::show_header(Lang::get('tools', 'events_reward_history'));
$DB->query("SELECT * from events_reward_log");
$Histories = $DB->to_array(false, MYSQLI_ASSOC);
?>

<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools', 'events_reward_history') ?></h2>
</div>
<div class="BodyNavLinks">
    <div class="center"><a href="tools.php?action=events_reward" class="brackets"><?= Lang::get('tools', 'events_reward') ?></a></div>
</div>
<div class="TableContainer">
    <table class="TableEventRewardHistory Table">
        <tr class="Table-rowHeader">
            <th class="Table-cell"><?= Lang::get('tools', 'userids') ?></th>
            <th class="Table-cell"><?= Lang::get('tools', 'byuserid') ?></th>
            <th class="Table-cell"><?= Lang::get('tools', 'events_reward') ?></th>
            <th class="Table-cell"><?= Lang::get('tools', 'remark') ?></th>
            <th class="Table-cell"><?= Lang::get('tools', 'time') ?></th>
        </tr>
        <?
        foreach ($Histories as $Recode) {
        ?>
            <tr class="Table-row">
                <td class="Table-cell"><?= strlen($Recode['UserIDs']) > 15 ? '<a target="_blank" href="tools.php?action=events_reward_history&id=' . $Recode['ID'] . '">' . Lang::get('tools', 'show_all') . '</a>' : userIDs2Usernames($Recode['UserIDs'], ' ') ?></td>
                <td class="Table-cell"><?= Users::format_username($Recode['ByUserID']) ?></td>
                <td class="Table-cell"><?
                                        $Strs = [];
                                        if ($Recode['Invites']) {
                                            $Str = $Recode['Invites'] . Lang::get('tools', 'space_invite') . ($Recode['Invites'] > 1 ? Lang::get('tools', 's') : "");
                                            if ($Recode['InvitesTime']) {
                                                $Str .= " (" . substr($Recode['InvitesTime'], 0, 10) . ")";
                                            }
                                            $Strs[] = $Str;
                                        }
                                        if ($Recode['Tokens']) {
                                            $Str = $Recode['Tokens'] . Lang::get('tools', 'space_token') . ($Recode['Tokens'] > 1 ? Lang::get('tools', 's') : "");
                                            if ($Recode['TokensTime']) {
                                                $Str .= " (" . substr($Recode['TokensTime'], 0, 10) . ")";
                                            }
                                            $Strs[] = $Str;
                                        }
                                        if ($Recode['Bonus']) {
                                            $Strs[] = $Recode['Bonus'] . Lang::get('tools', 'space_bonus');
                                        }
                                        if ($Recode['Badge']) {
                                            $Badge = Badges::get_badges_by_id($Recode['Badge']);
                                            $Strs[] = Lang::get('badges', $Badge['Label'] . '_badge_name');
                                        }
                                        if (count($Strs)) {
                                            echo implode(", ", $Strs);
                                        }
                                        ?></td>
                <td class="Table-cell"><?= $Recode['Remark'] ?></td>
                <td class="Table-cell"><?= $Recode['Time'] ?></td>
            </tr>
        <?
        }
        ?>
    </table>
</div>
<?
View::show_footer()
?>