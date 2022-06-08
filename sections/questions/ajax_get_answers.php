<?
$ID = (int)$_GET['id'];
if (empty($ID)) {
    die();
}
Text::$TOC = true;
$UserID = (int)$_GET['userid'];
$UserIDSQL = "";
if (!empty($UserID)) {
    $UserIDSQL = " AND UserID != '$UserID' ";
}

G::$DB->query("
		SELECT UserID, Answer, Date
		FROM staff_answers
		WHERE QuestionID = '$ID'
			$UserIDSQL
		ORDER BY DATE DESC");

$Answers = G::$DB->to_array(false, MYSQLI_ASSOC);
foreach ($Answers as $Answer) {
?>
    <div class="Box">
        <div class="Box-header">
            <span>
                Answer by <?= Users::format_username($Answer['UserID']) ?> - <?= time_diff($Answer['Date']) ?>
            </span>
        </div>
        <div class="Box-body HtmlText">
            <?= Text::full_format($Answer['Answer']) ?>
        </div>
    </div>
<? } ?>