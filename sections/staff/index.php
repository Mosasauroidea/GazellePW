<?
enforce_login();
View::show_header(t('server.staff.index'), '', 'PageStaffHome');

include(CONFIG['SERVER_ROOT'] . '/sections/staff/functions.php');

$SupportStaff = get_support();

$action = $_GET['action'];

list($Secondary, $Staff) = $SupportStaff;
$SupportStaffList = [];
foreach ($Secondary as $StaffMember) {
    list($ID, $ClassID, $Class, $ClassName, $StaffGroup, $Username, $Paranoia, $LastAccess, $Remark) = $StaffMember;
    $SupportStaffList[$ClassName][] = $StaffMember;
}
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= CONFIG['SITE_NAME'] ?> <?= t('server.staff.index') ?></h2>
    </div>
    <div class="BodyContent">
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.staff.contact_staff') ?>
                </div>
            </div>
            <div class="Group-body"><?= t('server.staff.contact_staff_note') ?>
                <? View::parse('generic/reply/staffpm.php', array('Hidden' => $action == 'donate' ? false : true)); ?>
            </div>
        </div>
        <div class="Group" id=" role-apply">
            <div class="Group-header">
                <span class="Group-headerTitle"><?= t('server.staff.role_applications') ?></span>
            </div>
            <div class="Group-body">
                <?= t('server.staff.role_applications_note') ?>
            </div>
            <? View::parse('generic/reply/staffpm.php', array('Hidden' => true)); ?>
        </div>
        <div class="Group">
            <div class="Group-header">
                <strong class="Group-headerTitle"><?= t('server.staff.community_help') ?></strong>
            </div>
            <div class="Group-body">
                <?= t('server.staff.fl_support_note') ?>
                <table class="TableUser Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" width="200px"><?= t('server.apply.role') ?></td>
                        <td class="Table-cell"><?= t('server.common.user') ?></td>
                    </tr>
                    <?
                    foreach ($SupportStaffList as $ClassName => $StaffMembers) {

                        $HTMLID = str_replace(' ', '_', strtolower($ClassName));
                        $UserNameList = implode(', ', array_map(function ($StaffMember) {
                            list($ID, $ClassID, $Class, $ClassName, $StaffGroup, $Username, $Paranoia, $LastAccess, $Remark) = $StaffMember;
                            return Users::format_username($ID, false, false, false);
                        }, $StaffMembers));
                    ?>
                        <tr class="Table-row">
                            <td class="Table-cell" id="<?= $HTMLID ?>"><?= $ClassName ?></td>
                            <td class="Table-cell"><?= $UserNameList ?></td>
                        </tr>
                    <?
                    }
                    ?>
                </table>
            </div>
        </div>
        <?
        ?>
        <? if (check_perms('show_admin_team')) {

            foreach ($Staff as $SectionName => $StaffSection) {
                if (count($StaffSection) === 0) {
                    continue;
                }
        ?>
                <div class=" Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle"><?= $SectionName ?></div>
                    </div>
                    <div class="Group-body">
                        <?
                        $CurClass = 0;
                        $CloseTable = false;
                        foreach ($StaffSection as $StaffMember) {
                            list($ID, $ClassID, $Class, $ClassName, $StaffGroup, $Username, $Paranoia, $LastAccess, $Remark) = $StaffMember;
                            if ($Class != $CurClass) { // Start new class of staff members
                                $Row = 'a';
                                if ($CloseTable) {
                                    $CloseTable = false;
                                    // the "\t" and "\n" are used here to make the HTML look pretty
                                    echo "</table></div></div>";
                                }
                                $CurClass = $Class;
                                $CloseTable = true;

                                $HTMLID = str_replace(' ', '_', strtolower($ClassName));
                        ?>
                                <div class="Box">
                                    <div class="Box-header">
                                        <div class="Box-headerTitle">
                                            <i id="<?= $HTMLID ?>"><?= $ClassName ?></i>
                                        </div>
                                    </div>
                                    <div class="Box-body">
                                        <table class="TableUser Table is-inner">
                                            <tr class="Table-rowHeader">
                                                <td class="Table-cell" style="width: 130px;"><?= t('server.staff.username') ?></td>
                                                <td class="Table-cell" style="width: 200px;"><?= t('server.staff.lastseen') ?></td>
                                                <td class="Table-cell"><?= t('server.staff.remark') ?></td>
                                            </tr>
                                    <?
                                } // End new class header

                                $HiddenBy = t('server.staff.hidden_by_staff_member');

                                // Display staff members for this class
                                $Row = make_staff_row($Row, $ID, $Paranoia, $Class, $LastAccess, $Remark, $HiddenBy);
                            }
                                    ?>
                                        </table>
                                    </div>
                                </div>
                    </div>
                </div>
        <?
            }
        } ?>
    </div>
</div>
<? View::show_footer(); ?>