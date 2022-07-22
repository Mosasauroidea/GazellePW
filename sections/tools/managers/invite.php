<?php
if (!check_perms('users_edit_invites')) {
    error(403);
}

if (isset($_REQUEST['addinvite'])) {
    authorize();
    $Invites = intval($_REQUEST['numinvite']);
    $UserID = false;
    if (isset($_REQUEST['userid'])) {
        $UserID = intval($_REQUEST['userid']);
        if ($UserID <= 0) {
            error(403);
        }
    }
    if ($Invites <= 0) {
        error(403);
    }
    $Datetime = false;
    if (isset($_REQUEST['datetime'])) {
        $Datetime = intval($_REQUEST['datetime']);
        if ($Datetime <= 0) {
            error(403);
        }
    }
    $DB->query("UPDATE users_main
        SET Invites = Invites + $Invites
        WHERE " . ($UserID ? "ID=$UserID" : "Enabled='1'"));
    $HasUser = $DB->affected_rows();
    if ($HasUser) {
        if ($Datetime) {
            if ($UserID) {
                for ($i = 0; $i < $Invites; $i++) {
                    $DB->query("INSERT into invites_typed (`UserID`,`EndTime`,`Type`) VALUES ($UserID, date_add(now(), INTERVAL $Datetime HOUR), 'time');");
                }
                $Cache->delete_value('user_info_heavy_' . $UserID);
            } else {
                for ($i = 0; $i < $Invites; $i++) {
                    $DB->query("INSERT into invites_typed (`UserID`,`EndTime`,`Type`)  
                    select id, date_add(now(), INTERVAL $Datetime HOUR), 'time' from users_main where Enabled='1'");
                }
                $DB->query("select max(id) from users_main");
                list($MaxID) = $DB->next_record();
                for ($i = 0; $i < $MaxID; $i++) {
                    $Cache->delete_value('user_info_heavy_' . $MaxID);
                }
            }
        }
        $message = "<strong>已为 " . ($UserID ? Users::format_username($UserID) : "所有启用用户") . " 添加 $Invites 个邀请" . ($Datetime ? "，$Datetime 小时后到期" : "") . "。</strong><br><br>";
    } else {
        $message = '<strong>用户不存在</strong><br /><br />';
    }
}
View::show_header(Lang::get('tools.add_invites'));

?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools.add_invites_to_users') ?></h2>
</div>
<script>
    function timed() {
        document.getElementById("datetime").disabled = !document.getElementById("time").checked
    }

    function user() {
        document.getElementById("userid").disabled = !document.getElementById("one").checked
    }
</script>
<div class="BoxBody" style="margin-left: auto; margin-right: auto; text-align: center; max-width: 40%;">
    <?= $message ?>
    <form class="add_form" name="invites" method="post">
        <input type="hidden" name="action" value="invite" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input id="all" type="radio" name="all" value="all" onclick="user()" checked><label for="all"><?= Lang::get('tools.all_enabled_users') ?></label>
        <input id="one" type="radio" name="all" value="one" onclick="user()"><label for="one"><?= Lang::get('tools.userid') ?></label>
        <br>
        <input class="Input" type="number" id="userid" name="userid" size="5" style="text-align: right;" value="0" min="0" disabled />
        <br><br>
        <label for="numinvite"><?= Lang::get('tools.invites') ?>: </label><br>
        <input class="Input" type="number" id="numinvite" name="numinvite" size="5" style="text-align: right;" value="0" min="0" />
        <br><br>
        <div class="Checkbox">
            <input class="Input" type="checkbox" id="time" onclick="timed()" />
            <label class="Checkbox-label" for="time"><?= Lang::get('tools.period_of_validity') ?></label>
        </div>
        <br>
        <input class="Input" type="number" name="datetime" id="datetime" disabled><br><?= Lang::get('tools.hours') ?>

        <br><br>
        <input class="Button" type="submit" name="addinvite" value="Add invites" />
    </form>
</div>
<?

View::show_footer()
?>