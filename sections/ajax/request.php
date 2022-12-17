<?
$RequestTax = CONFIG['REQUEST_TAX'];

// Minimum and default amount of upload to remove from the user when they vote.
// Also change in static/functions/requests.js
$MinimumVote = CONFIG['REQUEST_MIN_VOTE'];

/*
 * This is the page that displays the request to the end user after being created.
 */

if (empty($_GET['id']) || !is_number($_GET['id'])) {
    ajax_json_error();
}

$RequestID = (int)$_GET['id'];

//First things first, lets get the data for the request.

$Request = Requests::get_request($RequestID);
if ($Request === false) {
    ajax_json_error();
}

$CategoryID = $Request['CategoryID'];
$Requestor = Users::user_info($Request['UserID']);
$Filler = $Request['FillerID'] ? Users::user_info($Request['FillerID']) : null;
//Convenience variables
$IsFilled = !empty($Request['TorrentID']);
$CanVote = !$IsFilled && check_perms('site_vote');

if ($CategoryID == 0) {
    $CategoryName = 'Unknown';
} else {
    $CategoryName = $Categories[$CategoryID - 1];
}

//Votes time
$RequestVotes = Requests::get_votes_array($RequestID);
$VoteCount = count($RequestVotes['Voters']);
$ProjectCanEdit = (check_perms('project_team') && !$IsFilled && (($CategoryID == 0)));
$UserCanEdit = (!$IsFilled && $LoggedUser['ID'] == $Request['UserID'] && $VoteCount < 2);
$CanEdit = ($UserCanEdit || $ProjectCanEdit || check_perms('site_moderate_requests'));


$JsonTopContributors = array();
$VoteMax = ($VoteCount < 5 ? $VoteCount : 5);
for ($i = 0; $i < $VoteMax; $i++) {
    $User = array_shift($RequestVotes['Voters']);
    $JsonTopContributors[] = array(
        'userId' => (int)$User['UserID'],
        'userName' => $User['Username'],
        'bounty' => (int)$User['Bounty']
    );
}
reset($RequestVotes['Voters']);

list($NumComments, $Page, $Thread) = Comments::load('requests', $RequestID, false);

$JsonRequestComments = array();
foreach ($Thread as $Key => $Post) {
    list($PostID, $AuthorID, $AddedTime, $Body, $EditedUserID, $EditedTime, $EditedUsername) = array_values($Post);
    list($AuthorID, $Username, $PermissionID, $Paranoia, $Artist, $Donor, $Warned, $Avatar, $Enabled, $UserTitle) = array_values(Users::user_info($AuthorID));
    $JsonRequestComments[] = array(
        'postId' => (int)$PostID,
        'authorId' => (int)$AuthorID,
        'name' => $Username,
        'donor' => $Donor == 1,
        'warned' => ($Warned != '0000-00-00 00:00:00'),
        'enabled' => ($Enabled == 2 ? false : true),
        'class' => Users::make_class_string($PermissionID),
        'addedTime' => $AddedTime,
        'avatar' => $Avatar,
        'comment' => Text::full_format($Body),
        'editedUserId' => (int)$EditedUserID,
        'editedUsername' => $EditedUsername,
        'editedTime' => $EditedTime
    );
}

$JsonTags = array();
foreach ($Request['Tags'] as $Tag) {
    $JsonTags[] = $Tag;
}
ajax_json_success(array(
    'requestId' => (int)$RequestID,
    'requestorId' => (int)$Request['UserID'],
    'requestorName' => $Requestor['Username'],
    'isBookmarked' => Bookmarks::has_bookmarked('request', $RequestID),
    'requestTax' => $RequestTax,
    'timeAdded' => $Request['TimeAdded'],
    'canEdit' => $CanEdit,
    'canVote' => $CanVote,
    'minimumVote' => $MinimumVote,
    'voteCount' => $VoteCount,
    'lastVote' => $Request['LastVote'],
    'topContributors' => $JsonTopContributors,
    'totalBounty' => (int)$RequestVotes['TotalBounty'],
    'categoryId' => (int)$CategoryID,
    'categoryName' => $CategoryName,
    'title' => $Request['Title'],
    'year' => (int)$Request['Year'],
    'image' => $Request['Image'],
    'bbDescription' => $Request['Description'],
    'description' => Text::full_format($Request['Description']),
    'releaseType' => (int)$Request['ReleaseType'],
    'releaseName' => $ReleaseName,
    'isFilled' => $IsFilled,
    'fillerId' => (int)$Request['FillerID'],
    'fillerName' => ($Filler ? $Filler['Username'] : ''),
    'torrentId' => (int)$Request['TorrentID'],
    'timeFilled' => $Request['TimeFilled'],
    'tags' => $JsonTags,
    'comments' => $JsonRequestComments,
    'commentPage' => (int)$Page,
    'commentPages' => (int)ceil($NumComments / CONFIG['TORRENT_COMMENTS_PER_PAGE']),
));
