<?
/*
New post page

This is the page that's loaded if someone wants to make a new topic.

Information to be expected in $_GET:
    forumid: The ID of the forum that it's being posted in

*/

$ForumID = $_GET['forumid'];
if (!is_number($ForumID)) {
    error(404);
}
$Forum = Forums::get_forum_info($ForumID);
if ($Forum === false) {
    error(404);
}


if (!Forums::check_forumperm($ForumID, 'Write') || !Forums::check_forumperm($ForumID, 'Create')) {
    error(403);
}
View::show_header('Forums &gt; ' . $Forum['Name'] . ' &gt; New Topic', 'comments,bbcode,jquery.validate,form_validate', 'PageForumNewThread');
?>
<div class="LayoutBody">
    <h2><a href="forums.php"><?= t('server.forums.forums') ?></a> &gt; <a href="forums.php?action=viewforum&amp;forumid=<?= $ForumID ?>"><?= $Forum['Name'] ?></a> </h2>
    <div class="hidden" id="newthreadpreview">
        <div class="BodyNavLinks">
            <div class="center">
                <a href="#" onclick="return false;" class="brackets"><?= t('server.forums.report') ?></a>
                <a href="#" onclick="return false;" class="brackets"><?= !empty($HeavyInfo['AutoSubscribe']) ? 'Unsubscribe' : 'Subscribe' ?></a>
            </div>
        </div>
        <? if (check_perms('forums_polls_create')) { ?>
            <div class="box thin clear hidden" id="pollpreview">
                <div class="head colhead_dark"><strong><?= t('server.forums.poll') ?></strong> <a href="#" onclick="$('#threadpoll').gtoggle(); return false;" class="brackets"><?= t('server.forums.view') ?></a></div>
                <div class="pad" id="threadpoll">
                    <p><strong id="pollquestion"></strong></p>
                    <div id="pollanswers"></div>
                    <input type="radio" name="vote" id="answer_0" value="0" /> <label for="answer_0"><?= t('server.forums.blank_show_results') ?></label>
                    <input class="Button" type="button" style="float: left;" value="Vote" />
                </div>
            </div>
        <?  } ?>
        <div class="TableContainer">
            <table class="TableForumPost Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell" colspan="<?= Users::has_avatars_enabled() ? 2 : 1 ?>">
                        <div class="TableForumPostHeader">
                            <div class="TableForumPostHeader-info">
                                <a href="#newthreadpreview">#XXXXXX</a>
                                by <strong><?= Users::format_username($LoggedUser['ID'], true, true, true, true, true) ?></strong>
                                Just now
                                </span>
                            </div>
                            <div class="TableForumPostHeader-actions" id="barpreview">
                                <a href="#newthreadpreview" class="brackets">
                                    <?= t('server.forums.report') ?>
                                </a>
                            </div>
                    </td>
                </tr>
                <tr class="TableForumPost-cellContent Table-row">
                    <? if (Users::has_avatars_enabled()) { ?>
                        <td class="TableForumPost-cellAvatar Table-cell">
                            <?= Users::show_avatar($LoggedUser['Avatar'], $LoggedUser['ID'], $LoggedUser['Username'], $HeavyInfo['DisableAvatars']) ?>
                        </td>
                    <?  } ?>
                    <td class="TableForumPost-cellBody Table-cell">
                        <div id="contentpreview" style="text-align: left;"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <form class="Form create_form" name="forum_thread" action="" id="newthreadform" method="post">
        <input type="hidden" name="action" value="new" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="forum" value="<?= $ForumID ?>" />
        <div class="Form-rowList" id="newthreadtext" variant="header">
            <div class="Form-rowHeader">
                <div class="Form-title">
                    <span id="newthreadtitle"><?= t('server.forums.new_topic') ?></span>
                    <? ?>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.forums.title') ?></div>
                <div class="Form-inputs">
                    <input class="Input required" type="text" id="title" name="title" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.forums.body') ?></div>
                <div class="Form-items">
                    <? new TEXTAREA_PREVIEW('body', 'posttext') ?>
                </div>
            </div>

            <?
            if (check_perms('forums_polls_create')) {
            ?>
                <script type="text/javascript">
                    //<![CDATA[
                    var AnswerCount = 1;

                    function AddAnswerField() {
                        if (AnswerCount >= 50) {
                            return;
                        }
                        var AnswerField = document.createElement("input");
                        AnswerField.type = "text";
                        AnswerField.id = "answer_" + AnswerCount;
                        AnswerField.className = "Input required";
                        AnswerField.name = "answers[]";

                        var x = $('#answer_block').raw();
                        x.appendChild(AnswerField);
                        AnswerCount++;
                    }

                    function RemoveAnswerField() {
                        if (AnswerCount == 1) {
                            return;
                        }
                        var x = $('#answer_block').raw();
                        for (i = 0; i < 2; i++) {
                            x.removeChild(x.lastChild);
                        }
                        AnswerCount--;
                    }
                    //]]>
                </script>
                <div class="Form-rowSubHeader">
                    <?= t('server.forums.poll_settings') ?>
                    <a href="#" onclick="globalapp.toggleAny(event, '#poll_question, #poll_answers'); return false;" class="brackets">
                        <span class="u-toggleAny-show"><?= t('server.common.show') ?></span>
                        <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                    </a>
                </div>
                <div id="poll_question" class="u-hidden Form-row">
                    <div class="Form-label"><?= t('server.forums.question') ?>:</div>
                    <div class="Form-inputs"><input class="Input required" type="text" name="question" id="pollquestionfield" /></div>
                </div>
                <div id="poll_answers" class="u-hidden Form-row">
                    <div class="Form-label"><?= t('server.forums.answers') ?>:</div>
                    <div id="answer_block" class="Form-inputs FormOneLine">
                        <a href="#" onclick="AddAnswerField();return false;" class="brackets">+</a>
                        <a href="#" onclick="RemoveAnswerField();return false;" class="brackets">&minus;</a>
                        <input class="Input required" type="text" name="answers[]" />
                    </div>
                </div>
                <div id="poll_answers" class="u-hidden Form-row">
                    <div class="Form-label">可选数量:</div>
                    <div id="answer_block" class="Form-inputs">
                        <input class="Input required is-small" type="number" name="maxcount" min="1" max="25" value="1" />
                    </div>
                </div>

            <? } ?>
            <div class="Form-row">
                <input id="subscribebox" type="checkbox" name="subscribe" <?= !empty($HeavyInfo['AutoSubscribe']) ? ' checked="checked"' : '' ?> onchange="$('#subscribeboxpreview').raw().checked=this.checked;" />
                <label for="subscribebox"><?= t('server.forums.subscribe_to_topic') ?></label>
                <div id="buttons" class="center">
                    <input class="Button" type="submit" id="submit_button" value="<?= t('server.common.submit') ?>" />
                </div>

            </div>
        </div>
    </form>
</div>
<? View::show_footer(); ?>