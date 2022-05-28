<?

use Gazelle\Manager\Donation;

$UserID = $LoggedUser['ID'];
$CardNum = $_POST['card_num'];
$CardSecret = $_POST['card_secret'];
$FaceValue = $_POST['face_value'];
if (empty($CardNum) || strlen($CardNum) != 17) {
    error(Lang::get('donate', 'incorrect_card_num'));
}

if (empty($CardSecret) || strlen($CardSecret) != 18) {
    error(Lang::get('donate', 'incorrect_card_secret'));
}

if (!in_array($FaceValue, [50, 100, 300, 500])) {
    error(Lang::get('donate', 'incorrect_face_value'));
}

$donation = new Donation();
$ErrorText = $donation->preDonatePrepaidCard($UserID, $CardNum, $CardSecret, $FaceValue);
if ($ErrorText) {
    error($ErrorText);
}

header('Location: donate.php?action=step2');
