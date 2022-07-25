<?

View::show_header(t('server.tools.application_management'), '', 'PageToolApplyList');
$Status = $_REQUEST['status'];
$Where = " WHERE 1=1 ";
if (isset($Status)) {
    if ($Status != 99) {
        $Where = $Where . " && apply_status='" . db_string($Status) . "'";
    }
} else {
    $Where = $Where . " && apply_status='0'";
}
if (isset($_GET['email'])) {
    $Email = $_GET['email'];
    $Where = $Where . " && r.email like '%" . db_string($Email) . "%'";
}
if (isset($_GET['ip'])) {
    $IP = $_GET['ip'];
    $Where = $Where . " && (r.ipv4 like '%" . db_string($IP) . "%' || r.ipv6 like '%" . db_string($IP) . "%')";
}
$Page = empty($_GET['page']) ? 1 : $_GET['page'];
$DB->query("SELECT count(*) cnt FROM `register_apply` r" . $Where);
$NumResults = $DB->collect("cnt")[0];
$DB->query("SELECT
	r.`ID`,
	r.`email`,
	r.`site`,
	r.`ipv4`,
	r.`ipv6`,
	r.`site_ss`,
	r.`client_ss`,
	r.`introduction`,
	r.`apply_status`,
	r.`apply_pw`,
	r.`note`,
	r.`waring`,
	r.`ts`,
 r.`id_mod`,
	u.`username`,
	r.`c_red`,
	r.`c_ops`,
	r.`c_nwcd`,
	r.`c_opencd`,
	r.`c_others`,
	r.`addnote`
	FROM `register_apply` r LEFT JOIN `users_main` u ON r.id_mod=u.id " . $Where . " ORDER BY `ID` ASC limit " . (($Page - 1) * 50) . ",50;");
?>

<script>
    var a = ""

    function submit_sure() {
        return confirm("\n" + a + "！" + "\n\n确定对此申请如此操作？");
    }

    function click_sure(action) {
        a = action
    }
</script>
<div class="header">
    <script type="text/javacript">document.getElementByID('content').style.overflow = 'visible';</script>
    <ul id="application_management_header">
        <li>
        </li>
        <li>
            <h2><?= t('server.tools.application_management') ?></h2>
        </li>
        <li>
            <form>
                <input name="action" type="hidden" value="apply_list">
                <input name="status" type="hidden" value="<?= $Status ?>">
                <label for="searchemail"><?= t('server.tools.application_email') ?>:</lable>
                    <input class="Input" type="text" id="searchemail" spellcheck="false" onfocus="if (this.value == 'Email') { this.value = ''; }" onblur="if (this.value == '') { this.value = 'Email'; }" value=<?= $Email ? $Email : "Email" ?> placeholder="Email" name="email">
            </form>
        </li>
        <li>
            <form>
                <input name="action" type="hidden" value="apply_list">
                <input name="status" type="hidden" value="<?= $Status ?>">
                <label for="searchip"><?= t('server.tools.application_ip') ?>:</lable>
                    <input class="Input" type="text" id="searchip" spellcheck="false" onfocus="if (this.value == 'IP') { this.value = ''; }" onblur="if (this.value == '') { this.value = 'IP'; }" value=<?= $IP ? $IP : "IP" ?> placeholder="IP" name="ip">
            </form>
        </li>
    </ul>
</div>
<div id="tab-switcher">
    <ul class="nav nav-tabs nav-justified" id="switcher">
        <li class="nav-item"><a class="nav-link" href="tools.php?action=apply_list&status=0"><?= t('server.tools.queuing') ?></a></li>
        <li class="nav-item"><a class="nav-link" href="tools.php?action=apply_list&status=1"><?= t('server.tools.passed') ?></a></li>
        <li class="nav-item"><a class="nav-link" href="tools.php?action=apply_list&status=2"><?= t('server.tools.rejected') ?></a></li>
        <li class="nav-item"><a class="nav-link" href="tools.php?action=apply_list&status=3"><?= t('server.tools.pending') ?></a></li>
        <li class="nav-item"><a class="nav-link" href="tools.php?action=apply_list&status=4"><?= t('server.tools.incomplete') ?></a></li>
        <li class="nav-item"><a class="nav-link" href="tools.php?action=apply_list&status=5"><?= t('server.tools.added') ?></a></li>
        <li class="nav-item"><a class="nav-link" href="tools.php?action=apply_list&status=99"><?= t('server.tools.all') ?></a></li>
    </ul>
</div>
<div id="dynamicImg">
    <img src="" alt="">
</div>
<?
/*
if ($NumResults > $Page * 50) {
    $Pages = Format::get_pages($Page, $NumResults, 50);
} else {
    $Pages = "<strong>1-".$NumResults."</strong>";
}
*/
if ($NumResults > ($Page - 1) * 50 + 1) {
    $Pages = Format::get_pages($Page, $NumResults, 50);
}
?>
<div class="BodyNavLinks"><?= $Pages ?></div>
<table class="TableAdminApplication Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell" id="th_group_number" data-tooltip="<?= t('server.tools.group_number') ?>"><?= t('server.tools.group_number') ?></td>
        <td class="Table-cell" id="th_id">ID</td>
        <td class="Table-cell" id="th_submitted_at"><?= t('server.tools.submitted_at') ?></td>
        <td class="Table-cell" id="th_status"><?= t('server.tools.status') ?></td>
        <td class="Table-cell" id="th_operator"><?= t('server.tools.operator') ?></td>
        <td class="Table-cell" id="th_email"><?= t('server.tools.email') ?></td>
        <td class="Table-cell" id="th_ip">IP</td>
        <td class="Table-cell" id="th_opinion"><?= t('server.tools.opinion') ?></td>
        <td class="Table-cell" id="th_tips"><?= t('server.tools.tips') ?></td>
        <td class="Table-cell" id="th_access"><?= t('server.tools.access') ?></td>

    </tr>
    <?
    $groupCount = 7;
    while (list($ID, $email, $site, $ipv4, $ipv6, $site_ss, $client_ss, $introduction, $apply_status, $apply_pw, $note, $waring, $ts, $id_mod, $username, $c_red, $c_ops, $c_nwcd, $c_opencd, $c_others, $addnote) = $DB->next_record()) {
        $ip = '0.0.0.0';
        if (empty($ipv4)) {
            $ip = $ipv6;
        } else {
            $ip = $ipv4;
        }
    ?>
        <tr class="Table-row">
            <form class="manage_form" name="apply_list" action="" method="post" onsubmit="return submit_sure()">
                <input type="hidden" name="id" value="<?= $ID ?>" />
                <input type="hidden" name="apply_status" value="<?= isset($Status) ? $Status : 0 ?>" />
                <input type="hidden" name="email" value="<?= $email ?>" />
                <input type="hidden" name="action" value="apply_alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <td class="Table-cell" onclick="toggleShow('detail<?= $ID ?>')">
                    <p><?= t('server.tools.group') ?> <?= ($ID % $groupCount) + 1 ?></p>
                </td>
                <td class="Table-cell" onclick="toggleShow('detail<?= $ID ?>')">
                    <p><?= $ID ?></p>
                </td>
                <td class="Table-cell" onclick="toggleShow('detail<?= $ID ?>')">
                    <p><?= $ts ?></p>
                </td>
                <td class="Table-cell" onclick="toggleShow('detail<?= $ID ?>')">
                    <?
                    $u_status = '';
                    switch ($apply_status) {
                        case 0:
                            $u_status = '排队中';
                            break;
                        case 3:
                            $u_status = '待定中';
                            break;
                        case 1:
                            $u_status = '已通过';
                            break;
                        case 2:
                            $u_status = '已拒绝';
                            break;
                        case 4:
                            $u_status = '待补充';
                            break;
                        case 5:
                            $u_status = '待复审';
                            break;
                        default:
                            $u_status = '查询出错,请稍后重试';
                            break;
                    }
                    ?>

                    <p><?= $u_status ?></p>
                </td>
                <td class="Table-cell">
                    <p><a href="user.php?id=<?= $id_mod ?>"><?= $username ?></a></p>
                </td>
                <td class="Table-cell">
                    <p><a href="user.php?action=search&username=&joined=on&join1=&join2=&enabled=&email=<?= $email ?>&lastactive=on&lastactive1=&lastactive2=&class=&ip=&lockedaccount=any&secclass=&ip_history=on&email_history=on&ratio=equal&ratio1=&ratio2=&donor=&disabled_invites=&uploaded=equal&uploaded1=&uploaded2=&disabled_uploads=&invites=equal&invites1=&invites2=&downloaded=equal&downloaded1=&downloaded2=&warned=&invitees=off&invitees1=&invitees2=&snatched=off&snatched1=&snatched2=&comment=&passkey=&avatar=&tracker_ip=&stylesheet=&cc_op=equal&cc=&matchtype=fuzzy&order=Joined&way=Descending&emails_opt=equal&email_cnt=" data-tooltip="<?= $email ?>"><?= $email ?></a></p>
                </td>
                <td class="Table-cell">
                    <p><a href="user.php?action=search&username=&joined=on&join1=&join2=&enabled=&email=&lastactive=on&lastactive1=&lastactive2=&class=&ip=<?= $ip ?>&lockedaccount=any&secclass=&ip_history=on&email_history=on&ratio=equal&ratio1=&ratio2=&donor=&disabled_invites=&uploaded=equal&uploaded1=&uploaded2=&disabled_uploads=&invites=equal&invites1=&invites2=&downloaded=equal&downloaded1=&downloaded2=&warned=&invitees=off&invitees1=&invitees2=&snatched=off&snatched1=&snatched2=&comment=&passkey=&avatar=&tracker_ip=&stylesheet=&cc_op=equal&cc=&matchtype=fuzzy&order=Joined&way=Descending&emails_opt=equal&email_cnt=" data-tooltip="<?= $ip ?>"><?= t('server.tools.search_ip') ?></a></p>
                </td>

                <td class="Table-cell">
                    <input class="Input" type="text" size="15" name="note" value="<?= base64_decode($note) ?>" data-tooltip="<?= base64_decode($note) ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="15" name="waring" value="<?= base64_decode($waring) ?>" data-tooltip="<?= base64_decode($waring) ?>" />
                </td>
                <td class="Table-cell">
                    <button class="pass" aria-hidden="true" type="submit" name="submit" value="Agree" data-tooltip="<?= t('server.tools.pass') ?>" onclick="click_sure('通过')">
                        <?= icon("Admin/pass") ?>
                    </button>
                    <button class="refuse" aria-hidden="true" type="submit" name="submit" value="Refuse" data-tooltip="<?= t('server.tools.reject') ?>" onclick="click_sure('拒绝')">
                        <?= icon("Admin/reject") ?>
                    </button>
                    <button class="pending" aria-hidden="true" type="submit" name="submit" value="Pending" data-tooltip="<?= t('server.tools.pend') ?>" onclick="click_sure('待定')">
                        <?= icon("Admin/pend") ?>
                    </button>
                    <button class="add" aria-hidden="true" type="submit" name="submit" value="<?= t('server.global.add') ?>" data-tooltip="<?= t('server.tools.add') ?>" onclick="click_sure('补充')">
                        <?= icon("Admin/add") ?>
                    </button>
                </td>

            </form>
        <tr class="Table-row" id="detail<?= $ID ?>" style="display:none">
            <td class="application_detail" colspan="10">
                <table class="application_detail_table">
                    <tr class="rowa">
                        <td class="detailLabel"><?= t('server.tools.had_trackers') ?></td>
                        <td><?= $site ?></td>
                    </tr>
                    <tr class="rowb">
                        <td class="detailLabel"><?= t('server.tools.tracker_screenshots') ?></td>
                        <td>
                            <? $pics = explode("http", $site_ss);
                            for ($i = 0; $i < count($pics); ++$i) {
                                if (!empty($pics[$i])) {
                                    if (strstr($pics[$i], "://")) { ?>
                                        <a class="pic" href="http<?= $pics[$i] ?>" target=“_blank">http<?= $pics[$i] ?></a><br />
                                    <? } else { ?>
                                        <label><?= $pics[$i] ?></label><br />
                            <? }
                                }
                            }
                            ?>

                        </td>
                    </tr>
                    <tr class="rowa">
                        <td class="detailLabel"><?= t('server.tools.client_screenshots') ?></td>
                        <td>
                            <? $pics = explode("http", $client_ss);
                            for ($i = 0; $i < count($pics); ++$i) {
                                if (!empty($pics[$i])) {
                                    if (strstr($pics[$i], "://")) { ?>
                                        <a class="pic" href="http<?= $pics[$i] ?>" target=“_blank">http<?= $pics[$i] ?></a><br />
                                    <? } else { ?>
                                        <label><?= $pics[$i] ?></label><br />
                            <? }
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr class="rowb">
                        <td class="detailLabel"><?= t('server.tools.introduction') ?></td>
                        <td>
                            <div id="introduction_container">
                                <!-- <pre style="width: 1020px; white-space: pre-wrap; word-wrap: break-word;"> -->
                                <?= base64_decode($introduction) ?>
                                <!-- </pre> -->
                            </div>
                        </td>
                    </tr>
                    <? if (!empty($addnote)) { ?>

                        <tr class="rowa">
                            <td class="detailLabel"><?= t('server.tools.supplementary_information') ?></td>

                            <td>
                                <div id="supplementary_information_container">
                                    <!-- <pre style="width: 1020px; white-space: pre-wrap; word-wrap: break-word;"> -->
                                    <?= base64_decode($addnote) ?>
                                    <!-- </pre> -->
                                </div>
                            </td>
                        </tr>
                    <? } ?>
                </table>
            </td>
        </tr>
        </tr>
    <? } ?>
    </div>
</table>
<?
if ($NumResults > ($Page - 1) * 50 + 1) {
    $Pages = Format::get_pages($Page, $NumResults, 50);
}
?>
<div class="BodyNavLinks"><?= $Pages ?></div>
<? View::show_footer(); ?>