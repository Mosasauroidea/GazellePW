<?
enforce_login();
View::show_header(Lang::get('staff.index'), '', 'PageStaffHome');

include(CONFIG['SERVER_ROOT'] . '/sections/staff/functions.php');

$SupportStaff = get_support();

$action = $_GET['action'];

list($FrontLineSupport, $Staff) = $SupportStaff;
?>



<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= CONFIG['SITE_NAME'] ?> <?= Lang::get('staff.index') ?></h2>
    </div>
    <? if (check_perms('admin_manage_applicants')) { ?>
        <!-- <div class="BodyNavLinks">
    <a href="apply.php"><?= Lang::get('staff.role_applications') ?></a>
</div> -->
    <?  } ?>
    <div class="Box">
        <div class="Box-header">
            <span class="Post-headerTitle"><?= Lang::get('staff.contact_staff') ?></span>
        </div>
        <div class="Box-body"><?= Lang::get('staff.contact_staff_note') ?></div>
        <? View::parse('generic/reply/staffpm.php', array('Hidden' => $action == 'donate' ? false : true)); ?>
    </div>
    <div class="Box"" id=" role-apply">
        <div class="Post-header Box-header">
            <span class="Post-headerTitle"><?= Lang::get('staff.role_applications') ?></span>
            <div class="Post-headerActions"><?= Lang::get('staff.role_applications_sub') ?></div>
        </div>
        <div class="Box-body">
            <?= Lang::get('staff.role_applications_note') ?>
            <div><?= Lang::get('apply.referral_note') ?></div>
        </div>
        <? View::parse('generic/reply/staffpm.php', array('Hidden' => true)); ?>
    </div>
    <? if (check_perms('show_admin_team')) { ?>
        <div class="Box">
            <div class="Box-header">
                <strong class="Post-headerTitle"><?= Lang::get('staff.community_help') ?></strong>
            </div>
            <div class="Box-body">
                <?= Lang::get('staff.fl_support_note') ?><br />
                <div>
                    <h3 id="fls" style="font-size: 17px;"><i><?= Lang::get('staff.first_line_support') ?></i></h3>
                    <table class="TableUser Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" style="width: 130px;"><?= Lang::get('staff.username') ?></td>
                            <td class="Table-cell" style="width: 200px;"><?= Lang::get('staff.lastseen') ?></td>
                            <td class="Table-cell"><?= Lang::get('staff.support') ?></td>
                        </tr>
                        <?
                        $Row = 'a';
                        foreach ($FrontLineSupport as $Support) {
                            list($ID, $Class, $Username, $Paranoia, $LastAccess, $SupportFor) = $Support;
                            $Row = make_staff_row($Row, $ID, $Paranoia, $Class, $LastAccess, $SupportFor);
                        } ?>
                    </table>
                    <h3 style="font-size: 17px;" id="fls"><i><?= Lang::get('staff.torrent_inspector') ?></i></h3>
                    <table class="TableUser Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" style="width: 130px;"><?= Lang::get('staff.username') ?></td>
                            <td class="Table-cell" style="width: 200px;"><?= Lang::get('staff.lastseen') ?></td>
                            <td class="Table-cell"><?= Lang::get('staff.support') ?></td>
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
                <br />
                <?php

                foreach ($Staff as $SectionName => $StaffSection) {
                    if (count($StaffSection) === 0) {
                        continue;
                    }
                ?>
                    <div>
                        <h2 style='text-align: left;'><?= $SectionName ?></h2>
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
                                    echo "\t\t</table>\n\t\t<br />\n";
                                }
                                $CurClass = $Class;
                                $CloseTable = true;

                                $HTMLID = str_replace(' ', '_', strtolower($ClassName));
                                echo "\t\t<h3 style=\"font-size: 17px;\" id=\"$HTMLID\"><i>" . $ClassName . "s</i></h3>\n";
                        ?>
                                <table class="TableUser Table">
                                    <tr class="Table-rowHeader">
                                        <td class="Table-cell" style="width: 130px;"><?= Lang::get('staff.username') ?></td>
                                        <td class="Table-cell" style="width: 200px;"><?= Lang::get('staff.lastseen') ?></td>
                                        <td class="Table-cell"><?= Lang::get('staff.remark') ?></td>
                                    </tr>
                            <?
                            } // End new class header

                            $HiddenBy = Lang::get('staff.hidden_by_staff_member');

                            // Display staff members for this class
                            $Row = make_staff_row($Row, $ID, $Paranoia, $Class, $LastAccess, $Remark, $HiddenBy);
                        } ?>
                                </table>

                    </div>
                    <br />
                <? } ?>
            </div>
        <? } ?>
        </div>
</div>
<? View::show_footer(); ?>