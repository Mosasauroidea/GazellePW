<?php
//******************************************************************************//
//--------------- Add a recommendation -----------------------------------------//
authorize();

if (!check_perms('site_recommend_own') && !check_perms('site_manage_recommendations')) {
    error(403);
}

$URL = trim($_POST['url']);

// Make sure the URL they entered is on our site, and is a link to a torrent
$Val->SetFields(
    'url',
    '1',
    'regex',
    'The URL must be a link to a torrent on the site.',
    array('regex' => '/^' . TORRENT_GROUP_REGEX . '/i')
);
$Err = $Val->ValidateForm($_POST); // Validate the form

$Location = (empty($_SERVER['HTTP_REFERER'])) ? "tools.php?action=recommend" : $_SERVER['HTTP_REFERER'];
if ($Err) { // if something didn't validate
    error($Err);
    header("Location: {$Location}");
    exit;
}

// Get torrent ID
preg_match('/^' . TORRENT_GROUP_REGEX . '/i', $URL, $Matches);
$GroupID = $Matches[2];

if (empty($GroupID) || !is_number($GroupID)) {
    error(404);
}

$DB->query("INSERT INTO torrents_recommended (GroupID, UserID, Time) VALUES ('" . db_string($GroupID) . "', $LoggedUser[ID], '" . sqltime() . "')");
Torrents::freeleech_groups($GroupID, 2, 3);

$Cache->delete_value('recommend');
header("Location: {$Location}");
