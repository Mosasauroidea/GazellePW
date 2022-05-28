<?

use Gazelle\Manager\Donation;
use Gazelle\Manager\PrepaidCardStatus;

if (!check_perms('users_give_donor')) {
    error(403);
}

$Result = $_POST['result'];
$ID = $_POST['id'];

$donation = new Donation();

if (!in_array($Result, [PrepaidCardStatus::Passed, PrepaidCardStatus::Reject]) || empty($ID)) {
    error(Lang::get('tools', 'error'));
}

if ($Result == PrepaidCardStatus::Passed) {
    $errText = $donation->prepaidCardDonate($ID, $LoggedUser['ID']);
    if ($errText) {
        error($errText);
    }
} else if ($Result == PrepaidCardStatus::Reject) {
    $errText = $donation->rejectPrepaidCard($ID);
    if ($errText) {
        error($errText);
    }
}

header('Location: tools.php?action=prepaid_card');
