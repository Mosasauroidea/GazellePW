<script>
    function addPM() {
        if ($("#select-level").val() == "1000") {
            $("#subject").val("捐助事项咨询")
        }
    }
</script>
<div id="compose" class="Box-body <?= ($Hidden ? 'hidden' : '') ?>">
    <form class="send_form" name="staff_message" action="staffpm.php" method="post">
        <input type="hidden" name="action" value="takepost" />
        <h3><label for="subject"><?= Lang::get('staff', 'subject') ?></label></h3>
        <input class="Input" type="text" size="95" name="subject" id="subject" required />
        <h3><label for="message"><?= Lang::get('staff', 'message') ?></label></h3>
        <?
        $TextPrev = new TEXTAREA_PREVIEW('message', 'message', '', 95, 10, true, true, false, array(), true);
        ?>
        <div class="Post-bodyActions">
            <strong><?= Lang::get('staff', 'send_to') ?>: </strong>
            <select class="Input" id="select-level" name="level" onchange="addPM()">
                <? if (!isset(G::$LoggedUser['LockedAccount'])) { ?>
                    <option class="Select-option" value="0" <?= $_GET['action'] == 'first_line_support' ? 'selected="selected"' : '' ?>><?= Lang::get('staff', 'first_line_support') ?></option>
                    <!-- <option class="Select-option" value="800">Forum Moderators</option> -->
                    <!-- <option class="Select-option" value="850">Torrent Moderators</option> -->
                <?              } ?>
                <option class="Select-option" value="800" <?= $_GET['action'] == 'staff' ? 'selected="selected"' : '' ?>><?= Lang::get('staff', 'staff') ?></option>
                <option class="Select-option" value="1000" <?= $_GET['action'] == 'donate' ? 'selected="selected"' : '' ?>><?= Lang::get('staff', 'donation') ?></option>
            </select>
            <input class="Button" type="submit" value="<?= Lang::get('inbox', 'send_message') ?>" />
        </div>
    </form>
</div>
<script>
    addPM()
</script>