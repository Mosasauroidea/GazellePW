<?
//******************************************************************************//
//--------------- Delete request -----------------------------------------------//

authorize();

$RequestID = $_POST['id'];
if (!is_number($RequestID)) {
    error(0);
}

Requests::delete_request($RequestID, G::$LoggedUser['ID'], G::$LoggedUser['Username'], $_POST['reason']);
header('Location: requests.php');
