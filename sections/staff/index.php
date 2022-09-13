<?
enforce_login();
View::show_header(t('server.staff.index'), '', 'PageStaffHome');

include(CONFIG['SERVER_ROOT'] . '/sections/staff/functions.php');

$SupportStaff = get_support();

$action = $_GET['action'];

list($FrontLineSupport, $Staff) = $SupportStaff;
?>



<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= CONFIG['SITE_NAME'] ?> <?= t('server.staff.index') ?></h2>
    </div>
    <? if (check_perms('admin_manage_applicants')) { ?>
        <!-- <div class="BodyNavLinks">
    <a href="apply.php"><?= t('server.staff.role_applications') ?></a>
</div> -->
    <?  } ?>
    <div class="Box is-noBorder">
        <div class="Box-header">
            <span class="Box-headerTitle"><?= t('server.staff.contact_staff') ?></span>
        </div>
        <div class="Box-body"><?= t('server.staff.contact_staff_note') ?>
            <? View::parse('generic/reply/staffpm.php', array('Hidden' => $action == 'donate' ? false : true)); ?>
        </div>
    </div>
    <div class="Box is-noBorder" id=" role-apply">
        <div class="Box-header">
            <span class="Box-headerTitle"><?= t('server.staff.role_applications') ?></span>
            <div class="Box-headerActions"><?= t('server.staff.role_applications_sub') ?></div>
        </div>
        <div class="Box-body">
            <?= t('server.staff.role_applications_note') ?>
            <div><?= t('server.apply.referral_note') ?></div>
        </div>
        <? View::parse('generic/reply/staffpm.php', array('Hidden' => true)); ?>
    </div>
    <div class="Box is-noBorder">
        <div class="Box-header">
            <strong class="Box-headerTitle"><?= t('server.staff.community_help') ?></strong>
        </div>
        <div class="Box-body">
            <?= t('server.staff.fl_support_note') ?>
            <div class="Box is-noBorder">
                <div class="Box-header">
                    <div class="Box-headerTitle">
                        <div id="fls"><i><?= t('server.staff.first_line_support') ?></i></div>
                    </div>
                </div>
                <div class="Box-body">
                    <table class="TableUser Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" style="width: 130px;"><?= t('server.staff.username') ?></td>
                            <td class="Table-cell" style="width: 200px;"><?= t('server.staff.lastseen') ?></td>
                            <td class="Table-cell"><?= t('server.staff.support') ?></td>
                        </tr>
                        <?
                        $Row = 'a';
                        foreach ($FrontLineSupport as $Support) {
                            list($ID, $Class, $Username, $Paranoia, $LastAccess, $SupportFor) = $Support;
                            $Row = make_staff_row($Row, $ID, $Paranoia, $Class, $LastAccess, $SupportFor);
                        } ?>
                    </table>
                </div>
            </div>
            <div class="Box is-noBorder">
                <div class="Box-header">
                    <div class="Box-headerTitle">
                        <div id="fls"><i><?= t('server.staff.torrent_inspector') ?></i></div>
                    </div>
                </div>
                <div class="Box-body">
                    <table class="TableUser Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" style="width: 130px;"><?= t('server.staff.username') ?></td>
                            <td class="Table-cell" style="width: 200px;"><?= t('server.staff.lastseen') ?></td>
                            <td class="Table-cell"><?= t('server.staff.support') ?></td>
                        </tr>
                        <?
                        $Row = 'a';
                        $TorrentWatching = get_tw();
                        foreach ($TorrentWatching as $tw) {
                            list($ID, $Class, $Username, $Paranoia, $LastAccess, $SupportFor) = $tw;
                            $Row = make_staff_row($Row, $ID, $Paranoia, $Class, $LastAccess, $SupportFor);
                        } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <? if (check_perms('show_admin_team')) {

        foreach ($Staff as $SectionName => $StaffSection) {
            if (count($StaffSection) === 0) {
                continue;
            }
    ?>
            <div class="Box is-noBorder">
                <div class="Box-header">
                    <div class="Box-headerTitle"><?= $SectionName ?></div>
                </div>
                <div class="Box-body">
                    <div class="BoxList">
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
                                <div class="Box is-noBorder">
                                    <div class="Box-header">
                                        <div class="Box-headerTitle">
                                            <i id="<?= $HTMLID ?>"><?= $ClassName ?></i>
                                        </div>
                                    </div>
                                    <div class="Box-body">
                                        <table class="TableUser Table">
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
            </div>
    <?
        }
    } ?>
</div>
<? View::show_footer(); ?>