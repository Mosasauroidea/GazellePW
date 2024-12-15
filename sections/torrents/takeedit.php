<?
//******************************************************************************//
//--------------- Take edit ----------------------------------------------------//
// This pages handles the backend of the 'edit torrent' function. It checks     //
// the data, and if it all validates, it edits the values in the database       //
// that correspond to the torrent in question.                                  //
//******************************************************************************//

enforce_login();
authorize();

require(CONFIG['SERVER_ROOT'] . '/classes/validate.class.php');

use Gazelle\Torrent\EditionInfo;
use Gazelle\Torrent\Notification;
use Gazelle\Torrent\TorrentSlot;

$Validate = new VALIDATE;

//******************************************************************************//
//--------------- Set $Properties array ----------------------------------------//
// This is used if the form doesn't validate, and when the time comes to enter  //
// it into the database.                                                        //
//******************************************************************************//

$Properties = array();
$TypeID = (int)$_POST['type'];
$Type = $Categories[$TypeID - 1];
$TorrentID = (int)$_POST['torrentid'];

$Properties['Scene'] = (isset($_POST['scene'])) ? 1 : 0;
$Properties['TorrentID'] = $TorrentID;
$Properties['Jinzhuan'] = (isset($_POST['jinzhuan'])) ? 1 : 0;
$Properties['Diy'] = (isset($_POST['diy'])) ? 1 : 0;
$Properties['Makers'] = $_POST['makers'];
$Properties['Buy'] = (isset($_POST['buy'])) ? 1 : 0;
$Properties['Allow'] = (isset($_POST['allow'])) ? 1 : 0;
$Properties['BadTags'] = (isset($_POST['bad_tags'])) ? 1 : 0;
$Properties['BadFolders'] = (isset($_POST['bad_folders'])) ? 1 : 0;
$Properties['BadImg'] = (isset($_POST['bad_img'])) ? 1 : 0;
$Properties['BadFiles'] = (isset($_POST['bad_files'])) ? 1 : 0;
$Properties['BadCompress'] = (isset($_POST['bad_compress'])) ? 1 : 0;
$Properties['NoSub'] = (isset($_POST['no_sub'])) ? 1 : 0;
$Properties['HardSub'] = (isset($_POST['hardcode_sub'])) ? 1 : 0;
$Properties['CustomTrumpable'] = $_POST['custom_trumpable'];
$Properties['RemasterYear'] = $_POST['remaster_year'];
$Properties['NotMainMovie'] = isset($_POST['not_main_movie']) ? 1 : 0;
$Properties['SpecialSub'] = isset($_POST['special_effects_subtitles']) ? 1 : 0;
$Properties['ChineseDubbed'] = isset($_POST['chinese_dubbed']) ? 1 : 0;
$Properties['Source'] = $_POST['source'];
$Properties['Codec'] = $_POST['codec'];
$Properties['Source'] = $_POST['source'];
$Properties['SubtitleType'] = $_POST['subtitle_type'];

if ($Properties['Source'] == 'Other') {
    $Properties['Source'] = $_POST['source_other'];
}
if ($Properties['Codec'] == 'Other') {
    $Properties['Codec'] = $_POST['codec_other'];
}
$Properties['Container'] = $_POST['container'];
if ($Properties['Container'] == 'Other') {
    $Properties['Container'] = $_POST['container_other'];
}
$Properties['Resolution'] = $_POST['resolution'];
if ($Properties['Resolution'] == 'Other' && $_POST['resolution_width'] && $_POST['resolution_height']) {
    $Properties['Resolution'] = $_POST['resolution_width'] . '×' . $_POST['resolution_height'];
}
$Properties['Subtitles'] = implode(',', $_POST['subtitles']);
$Properties['Processing'] = $_POST['processing'];
if ($_POST['processing_other']) {
    $Properties['Processing'] = $_POST['processing_other'];
}

$Properties['RemasterTitle'] = $_POST['remaster_title'];
if (!EditionInfo::validate($Properties['RemasterTitle'])) {
    die("invalid remaster_title");
}
$Properties['RemasterTitle'] = EditionInfo::mergeAdvanceFeature($Properties['RemasterTitle'], $_POST);

$Properties['RemasterCustomTitle'] = $_POST['remaster_custom_title'];
$Properties['TorrentDescription'] = $_POST['release_desc'];
$Properties['Name'] = $_POST['title'];
if ($_POST['desc']) {
    $Properties['GroupDescription'] = $_POST['desc'];
}
if (check_perms('torrents_freeleech')) {
    $Free = (int)$_POST['freeleech'];
    if (!in_array($Free, array(0, 1, 2, 11, 12, 13))) {
        error(404);
    }
    $Properties['FreeLeech'] = $Free;

    if ($Free == 0) {
        $FreeType = 0;
    } else {
        $FreeType = (int)$_POST['freeleechtype'];
        if (!in_array($Free, array(0, 1, 2, 3, 11, 12, 13))) {
            error(404);
        }
    }
    $Properties['FreeLeechType'] = $FreeType;
    $LimitFree = isset($_POST['limit-time']) ? 1 : 0;
    if (in_array($Free, array(1, 11, 12, 13)) && $LimitFree) {
        $FreeDate = db_string($_POST['free-date']);
        $FreeTime = db_string(substr($_POST['free-time'], 0, 2));
        $DB->query("INSERT INTO `freetorrents_timed`(`TorrentID`, `EndTime`) VALUES ($TorrentID,'$FreeDate $FreeTime:00') ON DUPLICATE KEY UPDATE EndTime=VALUES(EndTime)");
    }
}
$Properties['MediaInfo']  = $_POST['mediainfo'] ? json_encode($_POST['mediainfo']) : null;
$Properties['Note'] = trim($_POST['staff_note']);


//******************************************************************************//
//--------------- Validate data in edit form -----------------------------------//

$DB->query("
	SELECT UserID, FreeTorrent
	FROM torrents
	WHERE ID = $TorrentID");
if (!$DB->has_results()) {
    error(404);
}
list($UserID, $CurFreeLeech) = $DB->next_record(MYSQLI_BOTH, false);

if ($LoggedUser['ID'] != $UserID && !check_perms('torrents_edit')) {
    error(403);
}

$Validate->SetFields('type', '1', 'number', 'Not a valid type.', array('maxlength' => count($Categories), 'minlength' => 1));
$Validate->SetFields('custom_trumpable', '0', 'string', 'Invalid release description.', array('maxlength' => 1000, 'minlength' => 0));
switch ($Type) {
    case 'Movie':
        $Validate->SetFields('source', '1', 'inarray', 'Not a valid source.', array('inarray' => $Sources));
        $Validate->SetFields('codec', '1', 'inarray', 'Not a valid codec.', array('inarray' => $Codecs));
        $Validate->SetFields('resolution', '1', 'inarray', 'Not a valid resolution.', array('inarray' => $Resolutions));
        $Validate->SetFields('container', '1', 'inarray', 'Not a valid container.', array('inarray' => $Containers));
}

$Err = $Validate->ValidateForm($_POST); // Validate the form

// Strip out Amazon's padding
$AmazonReg = '/(http:\/\/ecx.images-amazon.com\/images\/.+)(\._.*_\.jpg)/i';
$Matches = array();
if (preg_match($AmazonReg, $Properties['Image'], $Matches)) {
    $Properties['Image'] = $Matches[1] . '.jpg';
}
ImageTools::blacklisted($Properties['Image']);

if ($Err) { // Show the upload form, with the data the user entered
    if (check_perms('site_debug')) {
        die($Err);
    }
    error($Err);
}


//******************************************************************************//
//--------------- Make variables ready for database input ----------------------//

// Shorten and escape $Properties for database input
$T = array();
foreach ($Properties as $Key => $Value) {
    $T[$Key] = "'" . db_string(trim($Value)) . "'";
    if (!$T[$Key]) {
        $T[$Key] = null;
    }
}


//******************************************************************************//
//--------------- Start database stuff -----------------------------------------//

$DBTorVals = array();
$DB->query("
	SELECT Source, Codec, Container, Resolution, Subtitles, Makers , Scene, Jinzhuan, Diy, Buy, Allow, Description, FileList, RemasterTitle, RemasterCustomTitle, Processing, NotMainMovie, SpecialSub, ChineseDubbed, MediaInfo, Note, SubtitleType, RemasterYear
	FROM torrents
	WHERE ID = $TorrentID");
$DBTorVals = $DB->to_array(false, MYSQLI_ASSOC, ['MediaInfo']);
$DBTorVals = $DBTorVals[0];

$LogDetails = '';
foreach ($DBTorVals as $Key => $Value) {
    $Value = "'$Value'";
    // shit special logic
    if ($Key == 'MediaInfo') {
        if ("'" . $Properties[$Key] . "'" == $Value) {
            continue;
        }
    }
    if ($Value != $T[$Key]) {
        if ($Key == 'Resolution') {
            $Slot = TorrentSlot::CalSlot($Properties);
            var_dump($Slot);
        }
        if (!isset($T[$Key])) {
            continue;
        }
        if ((empty($Value) && empty($T[$Key])) || ($Value == "'0'" && $T[$Key] == "''")) {
            continue;
        }

        if ($LogDetails == '') {
            $LogDetails = "$Key: $Value -> " . $T[$Key];
        } else {
            $LogDetails = "$LogDetails, $Key: $Value -> " . $T[$Key];
        }
    }
}

// Update info for the torrent
$SQL = "
	UPDATE torrents AS t";
$SQL .= "
	SET
		Source = $T[Source],
		Codec = $T[Codec],
		Container = $T[Container],
		Resolution = $T[Resolution],
		Subtitles = $T[Subtitles],
		Scene = $T[Scene],
		RemasterTitle = $T[RemasterTitle],
		RemasterCustomTitle = $T[RemasterCustomTitle],
		RemasterYear = $T[RemasterYear],
        Processing = $T[Processing],
        NotMainMovie = $T[NotMainMovie],
        ChineseDubbed = $T[ChineseDubbed],
        SpecialSub = $T[SpecialSub],
        MediaInfo = $T[MediaInfo],
        SubtitleType = $T[SubtitleType],
		Allow = $T[Allow],";
if (check_perms("users_mod")) {
    $SQL .= "
		Buy = $T[Buy],
		Jinzhuan = $T[Jinzhuan],
        Note = $T[Note],
        Makers = $T[Makers],
		Diy = $T[Diy],";
}

if ($Slot !== null) {
    $SQL .= "Slot = $Slot,";
}

if (check_perms('torrents_freeleech')) {
    $SQL .= "FreeTorrent = $T[FreeLeech],";
    $SQL .= "FreeLeechType = $T[FreeLeechType],";
}

if (check_perms('torrents_trumpable')) {
    $DB->query("
		SELECT TorrentID
		FROM torrents_bad_folders
		WHERE TorrentID = '$TorrentID'");
    list($bfID) = $DB->next_record();

    if (!$bfID && $Properties['BadFolders']) {
        $DB->query("
			INSERT INTO torrents_bad_folders
			VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "')");
    }
    if ($bfID && !$Properties['BadFolders']) {
        $DB->query("
			DELETE FROM torrents_bad_folders
			WHERE TorrentID = '$TorrentID'");
    }

    $DB->query("
		SELECT TorrentID
		FROM torrents_bad_files
		WHERE TorrentID = '$TorrentID'");
    list($bfiID) = $DB->next_record();

    if (!$bfiID && $Properties['BadFiles']) {
        $DB->query("
			INSERT INTO torrents_bad_files
			VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "')");
    }
    if ($bfiID && !$Properties['BadFiles']) {
        $DB->query("
			DELETE FROM torrents_bad_files
			WHERE TorrentID = '$TorrentID'");
    }

    $DB->query("
		SELECT TorrentID
		FROM torrents_no_sub
		WHERE TorrentID = '$TorrentID'");
    list($bnsID) = $DB->next_record();

    if (!$bnsID && $Properties['NoSub']) {
        $DB->query("
			INSERT INTO torrents_no_sub
			VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "')");
    }

    if ($bnsID && !$Properties['NoSub']) {
        $DB->query("
			DELETE FROM torrents_no_sub
			WHERE TorrentID = '$TorrentID'");
    }

    $DB->query("
		SELECT TorrentID
		FROM torrents_hard_sub
		WHERE TorrentID = '$TorrentID'");
    list($bhsID) = $DB->next_record();

    if (!$bhsID && $Properties['HardSub']) {
        $DB->query("
			INSERT INTO torrents_hard_sub
			VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "')");
    }

    if ($bhsID && !$Properties['HardSub']) {
        $DB->query("
			DELETE FROM torrents_hard_sub
			WHERE TorrentID = '$TorrentID'");
    }

    $DB->query("
	DELETE FROM torrents_custom_trumpable
	WHERE TorrentID = '$TorrentID'");
    if (!empty($Properties['CustomTrumpable']) && check_perms('users_mod')) {
        $DB->query("
			INSERT INTO torrents_custom_trumpable
			VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "', '" . db_string(trim($Properties['CustomTrumpable'])) . "')");
    }
}

$SQL .= "
		Description = $T[TorrentDescription]
	WHERE ID = $TorrentID";
$DB->query($SQL);

$DB->query("
	SELECT GroupID,  Time
	FROM torrents
	WHERE ID = '$TorrentID'");
list($GroupID, $Body, $TagList, $Time) = $DB->next_record();
$Properties['GroupID'] = $GroupID;
$Group = Torrents::get_group($GroupID);


if (check_perms('torrents_freeleech') && $Properties['FreeLeech'] != $CurFreeLeech) {
    Torrents::freeleech_torrents($TorrentID, $Properties['FreeLeech'], $Properties['FreeLeechType']);
}


Misc::write_log("Torrent $TorrentID in group $GroupID was edited by " . $LoggedUser['Username'] . " ($LogDetails)"); // TODO: this is probably broken
if ($LogDetails) {
    Torrents::write_group_log($GroupID, $TorrentID, $LoggedUser['ID'], $LogDetails, 0);
}

$Cache->delete_value("torrents_details_$GroupID");
$Cache->delete_value("torrent_download_$TorrentID");

Torrents::update_hash($GroupID);

// All done!

header("Location: torrents.php?id=$GroupID");
