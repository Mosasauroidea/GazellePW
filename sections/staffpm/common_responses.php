<?
if (!($IsFLS)) {
    // Logged in user is not FLS or Staff
    error(403);
}

View::show_header('Staff PMs', 'staffpm', 'PageStaffPMResponse');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"> <?= t('server.staffpm.staff_pm') ?> > <?= t('server.staffpm.manage_common_response') ?></h2>
        <div class="BodyNavLinks">

            <?
            if ($IsStaff) {
            ?>
                <a href="staffpm.php" class="brackets"><?= t('server.staffpm.view_your_unanswered') ?></a>
            <?
            }
            if ($IsFLS) {
            ?>
                <a href="staffpm.php?view=unanswered" class="brackets"><?= t('server.staffpm.view_all_unanswered') ?></a>
                <a href="staffpm.php?view=open" class="brackets"><?= t('server.staffpm.view_unresolved') ?></a>
                <a href="staffpm.php?view=resolved" class="brackets"><?= t('server.staffpm.view_resolved') ?></a>
            <?
            }
            if ($ConvID = (int)$_GET['convid']) { ?>
                <a href="staffpm.php?action=viewconv&amp;id=<?= $ConvID ?>" class="brackets"><?= t('server.staffpm.back_to_conversation') ?></a>
            <?  }
            ?>
        </div>
    </div>
    <div id="commonresponses">
        <form id="response_new" class="Form send_form" name="response" id="response_form_0" action="">
            <div class="Form-rowList" variant="header">
                <div class="Form-rowHeader"><?= t('server.common.new') ?></div>
                <div class="Form-row">
                    <div class="Form-label">
                        <?= t('server.common.name') ?>:
                    </div>
                    <div class="Form-inputs">
                        <input class="Input" type="text" id="response_name_0" size="87" />
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label">
                        <?= t('server.common.content') ?>:
                    </div>
                    <div class="Form-items">
                        <? new TEXTAREA_PREVIEW('', 'response_message_0', $Event['Body'], 60, 8, true, true, false); ?>
                    </div>
                </div>
                <div class="Form-row FormOneLine">
                    <input class="Button" type="button" value="<?= t('client.common.save') ?>" id="save_0" onclick="SaveMessage(0);" />
                </div>
                <div class="Form-row FormOneLine">
                    <div id="ajax_message_0" class="hidden center alertbar"></div>
                </div>
            </div>
        </form>
    </div>
    <?
    // List common responses
    $DB->query("
	SELECT ID, Message, Name
	FROM staff_pm_responses
	ORDER BY Name ASC");
    while (list($ID, $Message, $Name) = $DB->next_record()) {

    ?>
        <div id="response_<?= $ID ?>">
            <form class="Form send_form" name="response" id="response_form_<?= $ID ?>" action="">
                <input type="hidden" name="id" value="<?= $ID ?>" />
                <div class="Form-rowList" variant="header">
                    <div class="Form-rowHeader"><?= t('server.common.edit') ?></div>
                    <div class="Form-row">
                        <div class="Form-label">
                            <?= t('server.common.name') ?>:
                        </div>
                        <div class="Form-inputs">
                            <input class="Input" type="text" name="name" id="response_name_<?= $ID ?>" size="87" value="<?= display_str($Name) ?>" />
                        </div>
                    </div>
                    <div class="Form-row">
                        <div class="Form-label">
                            <?= t('server.common.content') ?>:
                        </div>
                        <div class="Form-items">
                            <? new TEXTAREA_PREVIEW('message', "response_message_$ID",  display_str($Message), 60, 8, true, true, false); ?>
                        </div>
                    </div>
                    <div class="Form-row FormOneLine">
                        <input variant="primary" class="Button" type="button" value="<?= t('client.common.save') ?>" id="save_<?= $ID ?>" onclick="SaveMessage(<?= $ID ?>);" />
                        <input class="Button" type="button" value="<?= t('server.common.delete') ?>" onclick="DeleteMessage(<?= $ID ?>);" />
                    </div>
                    <div class="Form-row FormOneLine">
                        <div id="ajax_message_<?= $ID ?>" class="hidden center alertbar"></div>
                    </div>
                </div>
            </form>
        </div>
    <?
    }
    ?>
</div>
<? View::show_footer(); ?>