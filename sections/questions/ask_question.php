<?
View::show_header("Ask the Staff", 'PageQuestionAskQuestion');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">Ask Staff Anything</h2>
    </div>
    <div class="BodyNavLinks">
        <a class="brackets" href="questions.php?action=answers">View staff answers</a>
    </div>
    <div class="Box center">
        <form class="Box-body" method="post">
            <input type="hidden" name="action" value="take_ask_question" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <textarea class="Input" id="question" class="required" onkeyup="resize('question');" name="question" cols="90" rows="8"></textarea>
            <div id="buttons" class="center">
                <input class="Button" type="submit" id="submit_button" value="Ask question" />
            </div>
        </form>
    </div>
</div>
<?
View::show_footer();
