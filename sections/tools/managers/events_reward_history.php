<?
function userIDs2Usernames($UserIDs, $Space) {
    $UserIDs = explode(',', $UserIDs);
    $Usernames = '';
    foreach ($UserIDs as $UserID) {
        $Usernames .= Users::format_username($UserID) . $Space;
    }
    return $Usernames;
}

const PAGE_COUNT = 50;

View::show_header(t('server.tools.events_reward_history'));
?>
<div class="LayoutPage">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.events_reward_history') ?></h2>
    </div>
    <div class="BodyNavLinks">
        <div class="center"><a href="tools.php?action=events_reward" class="brackets"><?= t('server.tools.events_reward') ?></a></div>
    </div>
    <?
    if (isset($_GET['id'])) {
        $DB->query("SELECT * from events_reward_log where id=" . intval($_GET['id']));
        $Recode = $DB->next_record(MYSQLI_ASSOC);
    ?>
        <div class="HtmlText">
            <h2><?= t('server.tools.userids') ?></h2>
            <p><?= userIDs2Usernames($Recode['UserIDs'], ', ') ?></p>
            <h2><?= t('server.tools.byuserid') ?></h2>
            <p><?= !empty($Recode['ByUserID']) ? Users::format_username($Recode['ByUserID']) : 'System' ?></p>
            <? if ($Recode['Invites'] > 0) { ?>
                <h2><?= t('server.tools.invites') ?></h2>
                <p><?= $Recode['Invites'] ?><?= $Recode['InvitesTime'] ? '(' .  $Recode['InvitesTime'] . ')' : '' ?></p>
            <? } ?>
            <? if ($Recode['Tokens'] > 0) { ?>
                <h2><?= t('server.tools.tokens') ?></h2>
                <p><?= $Recode['Tokens'] ?></p><?= $Recode['TokensTime'] ? '(' .  $Recode['TokensTime'] . ')' : '' ?></p>
            <? } ?>
            <? if ($Recode['Bonus'] > 0) { ?>
                <h2><?= t('server.tools.Bonus') ?></h2>
                <p><?= $Recode['Bonus'] ?></p>
            <? } ?>
            <? if ($Recode['Badge']) { ?>
                <h2><?= t('server.tools.badge') ?></h2>
                <p>
                    <?
                    $Badge = Badges::get_badges_by_id($Recode['Badge']);
                    echo t("server.badges.${Badge['Label']}_badge_name");
                    ?></p>
            <? } ?>
            <h2><?= t('server.tools.remark') ?></h2>
            <p><?= $Recode['Remark'] ?></p>
            <h2><?= t('server.tools.time') ?></h2>
            <p><?= $Recode['Time'] ?></p>
        </div>
    <?
    } else {
        list($Page, $Limit) = Format::page_limit(PAGE_COUNT);
        $Records = $DB->query("SELECT SQL_CALC_FOUND_ROWS * from events_reward_log ORDER BY ID DESC LIMIT $Limit");
        $DB->query('SELECT FOUND_ROWS()');
        list($Results) = $DB->next_record();
        $Pages = Format::get_pages($Page, $Results, PAGE_COUNT, 5);

        $DB->set_query_id($Records);
        $Histories = $DB->to_array(false, MYSQLI_ASSOC);
    ?>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
        <div class="TableContainer">
            <table class="TableEventRewardHistory Table">
                <tr class="Table-rowHeader">
                    <th class="Table-cell"><?= t('server.tools.userids') ?></th>
                    <th class="Table-cell"><?= t('server.tools.byuserid') ?></th>
                    <th class="Table-cell"><?= t('server.tools.events_reward') ?></th>
                    <th class="Table-cell"><?= t('server.tools.remark') ?></th>
                    <th class="Table-cell"><?= t('server.tools.time') ?></th>
                </tr>
                <?
                foreach ($Histories as $Recode) {
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= count(explode(',', $Recode['UserIDs'])) > 1 ? '<a target="_blank" href="tools.php?action=events_reward_history&id=' . $Recode['ID'] . '">' . t('server.tools.show_all') . '</a>' : userIDs2Usernames($Recode['UserIDs'], ' ') ?></td>
                        <td class="Table-cell"><?= Users::format_username($Recode['ByUserID']) ?></td>
                        <td class="Table-cell"><?
                                                $Strs = [];
                                                if ($Recode['Invites']) {
                                                    $Str = t('server.tools.space_invite', ['Count' => $Recode['Invites'], 'Values' => [$Recode['Invites']]]);
                                                    if ($Recode['InvitesTime']) {
                                                        $Str .= " (" . substr($Recode['InvitesTime'], 0, 10) . ")";
                                                    }
                                                    $Strs[] = $Str;
                                                }
                                                if ($Recode['Tokens']) {
                                                    $Str = t('server.tools.space_token', ['Count' => $Recode['Tokens'], 'Values' => [$Recode['Tokens']]]);
                                                    if ($Recode['TokensTime']) {
                                                        $Str .= " (" . substr($Recode['TokensTime'], 0, 10) . ")";
                                                    }
                                                    $Strs[] = $Str;
                                                }
                                                if ($Recode['Bonus']) {
                                                    $Strs[] = $Recode['Bonus'] . t('server.tools.space_bonus');
                                                }
                                                if ($Recode['Badge']) {
                                                    $Badge = Badges::get_badges_by_id($Recode['Badge']);
                                                    $Strs[] = t("server.badges.${Badge['Label']}_badge_name");
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
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
    <?
    }
    ?>
</div>
<?
View::show_footer()
?>