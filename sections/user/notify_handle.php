<?
if (!check_perms('site_torrents_notify')) {
    error(403);
}
authorize();

$FormID = '';
$ArtistList = '';
$TagList = '';
$NotTagList = '';
$ReleaseTypeList = '';
$CodecList = '';
$SourceList = '';
$ContainerList = '';
$ResolutionList = '';
$ProcessingList = '';
$FromYear = 0;
$ToYear = 0;
$FromIMDBRating = 0;
$FromSize = 0;
$ToSize = 0;
$Users = '';
$NotUsers = '';
$HasFilter = false;
$RemasterTitle = '';
$FreeTorrentList = '';

if ($_POST['formid'] && is_number($_POST['formid'])) {
    $FormID = $_POST['formid'];
}

if ($_POST['artists' . $FormID]) {
    $Artists = explode(',', $_POST['artists' . $FormID]);
    $ParsedArtists = array();
    foreach ($Artists as $Artist) {
        if (trim($Artist) != '') {
            $ParsedArtists[] = db_string(trim($Artist));
        }
    }
    if (count($ParsedArtists) > 0) {
        $ArtistList = '|' . implode('|', $ParsedArtists) . '|';
        $HasFilter = true;
    }
}

if ($_POST['newgroupsonly' . $FormID]) {
    $NewGroupsOnly = '1';
    $HasFilter = true;
} else {
    $NewGroupsOnly = '0';
}

if ($_POST['tags' . $FormID]) {
    $TagList = '|';
    $Tags = explode(',', $_POST['tags' . $FormID]);
    foreach ($Tags as $Tag) {
        $TagList .= db_string(trim($Tag)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['remastertitles' . $FormID]) {
    $RemasterTitleList = '|';
    $RemasterTitles = explode(',', $_POST['remastertitles' . $FormID]);
    foreach ($RemasterTitles as $RemasterTitle) {
        $RemasterTitleList .= db_string(trim($RemasterTitle)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['regions' . $FormID]) {
    $RegionList = '|';
    $Regions = explode(',', $_POST['regions' . $FormID]);
    foreach ($Regions as $Region) {
        $RegionList .= db_string(trim($Region)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['languages' . $FormID]) {
    $LanguageList = '|';
    $Languages = explode(',', $_POST['languages' . $FormID]);
    foreach ($Languages as $Language) {
        $LanguageList .= db_string(trim($Language)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['nottags' . $FormID]) {
    $NotTagList = '|';
    $Tags = explode(',', $_POST['nottags' . $FormID]);
    foreach ($Tags as $Tag) {
        $NotTagList .= db_string(trim($Tag)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['categories' . $FormID]) {
    $CategoryList = '|';
    foreach ($_POST['categories' . $FormID] as $Category) {
        $CategoryList .= db_string(trim($Category)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['releasetypes' . $FormID]) {
    $ReleaseTypeList = '|';
    foreach ($_POST['releasetypes' . $FormID] as $ReleaseType) {
        $ReleaseTypeList .= db_string(trim($ReleaseType)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['codecs' . $FormID]) {
    $CodecList = '|';
    foreach ($_POST['codecs' . $FormID] as $Codec) {
        $CodecList .= db_string(trim($Codec)) . '|';
    }
    $HasFilter = true;
}


if ($_POST['frees' . $FormID]) {
    $FreeTorrentList = '|';
    foreach ($_POST['frees' . $FormID] as $FT) {
        $FreeTorrentList .= db_string(trim($FT)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['processings' . $FormID]) {
    $ProcessingList = '|';
    foreach ($_POST['processings' . $FormID] as $Processing) {
        $ProcessingList .= db_string(trim($Processing)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['sources' . $FormID]) {
    $SourceList = '|';
    foreach ($_POST['sources' . $FormID] as $Source) {
        $SourceList .= db_string(trim($Source)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['containers' . $FormID]) {
    $ContainerList = '|';
    foreach ($_POST['containers' . $FormID] as $Container) {
        $ContainerList .= db_string(trim($Container)) . '|';
    }
    $HasFilter = true;
}

if ($_POST['resolutions' . $FormID]) {
    $ResolutionList = '|';
    foreach ($_POST['resolutions' . $FormID] as $Resolution) {
        $ResolutionList .= db_string(trim($Resolution)) . '|';
    }
    $HasFilter = true;
}


if ($_POST['fromyear' . $FormID] && is_number($_POST['fromyear' . $FormID])) {
    $FromYear = trim($_POST['fromyear' . $FormID]);
    $HasFilter = true;
    if ($_POST['toyear' . $FormID] && is_number($_POST['toyear' . $FormID])) {
        $ToYear = trim($_POST['toyear' . $FormID]);
    } else {
        $ToYear = date('Y') + 3;
    }
}

if ($_POST['fromsize' . $FormID] && is_number($_POST['fromsize' . $FormID])) {
    $FromSize = trim($_POST['fromsize' . $FormID]);
    $FromSize *= 1024 * 1024 * 1024;
    $HasFilter = true;
}
if ($_POST['tosize' . $FormID] && is_number($_POST['tosize' . $FormID])) {
    $HasFilter = true;
    $ToSize = trim($_POST['tosize' . $FormID]);
    $ToSize *= 1024 * 1024 * 1024;
}

if ($_POST['fromimdbrating' . $FormID] && is_number($_POST['fromimdbrating' . $FormID])) {
    $HasFilter = true;
    $FromIMDBRating = $_POST['fromimdbrating' . $FormID];
}
if ($_POST['users' . $FormID]) {
    $Usernames = explode(',', $_POST['users' . $FormID]);
    $EscapedUsernames = array();
    foreach ($Usernames as $Username) {
        $EscapedUsernames[] = db_string(trim($Username));;
    }

    $DB->query("
		SELECT ID
		FROM users_main
		WHERE Username IN ('" . implode("', '", $EscapedUsernames) . "')
			AND ID != $LoggedUser[ID]");
    while (list($UserID) = $DB->next_record()) {
        $Users .= '|' . $UserID . '|';
        $HasFilter = true;
    }
}


if ($_POST['notusers' . $FormID]) {
    $Usernames = explode(',', $_POST['notusers' . $FormID]);
    $EscapedUsernames = array();
    foreach ($Usernames as $Username) {
        $EscapedUsernames[] = db_string(trim($Username));;
    }
    $DB->query("
		SELECT ID
		FROM users_main
		WHERE Username IN ('" . implode("', '", $EscapedUsernames) . "')
			AND ID != $LoggedUser[ID]");
    while (list($UserID) = $DB->next_record()) {
        $NotUsers .= '|' . $UserID . '|';
        $HasFilter = true;
    }
}


if (!$HasFilter) {
    $Err = 'You must add at least one criterion to filter by';
} elseif (!$_POST['label' . $FormID] && !$_POST['id' . $FormID]) {
    $Err = 'You must add a label for the filter set';
}

if (($ToSize && $FromSize > $ToSize)
    || $FromYear > $ToYear
    || $FromSize < 0
    || $FromIMDBID < 0
    || ($ToSize != 0 && $ToSize < 500)
    || $ToYear < 0
) {
    $Err = '请输入有效数值！';
}

if ($Err) {
    error($Err);
    header('Location: user.php?action=notify');
    die();
}

$ArtistList = str_replace('||', '|', $ArtistList);
$TagList = str_replace('||', '|', $TagList);
$NotTagList = str_replace('||', '|', $NotTagList);
$Users = str_replace('||', '|', $Users);
$NotUsers = str_replace('||', '|', $NotUsers);

if ($_POST['id' . $FormID] && is_number($_POST['id' . $FormID])) {
    $DB->query("
		UPDATE users_notify_filters
		SET
			Artists='$ArtistList',
			ExcludeVA='$ExcludeVA',
			NewGroupsOnly='$NewGroupsOnly',
			Tags='$TagList',
			NotTags='$NotTagList',
			ReleaseTypes='$ReleaseTypeList',
			Categories='$CategoryList',
			Codecs='$CodecList',
            Sources='$SourceList',
			Containers='$ContainerList',
			Resolutions='$ResolutionList',
			FreeTorrents='$FreeTorrentList',
			Processings='$ProcessingList',
			FromYear='$FromYear',
			ToYear='$ToYear',
			FromSize='$FromSize',
			ToSize='$ToSize',
			Users='$Users',
			NotUsers='$NotUsers',
            FromIMDBRating='$FromIMDBRating',
            Regions='$RegionList',
            Languages='$LanguageList',
            RemasterTitles='$RemasterTitleList'
		WHERE ID='" . $_POST['id' . $FormID] . "'
			AND UserID='$LoggedUser[ID]'");
} else {
    $DB->query("
		INSERT INTO users_notify_filters
			(UserID, Label, Artists, ExcludeVA, NewGroupsOnly, Tags, NotTags, ReleaseTypes, Categories, Codecs, Sources, Containers, Resolutions, FromYear, ToYear, FromSize, ToSize, Users, NotUsers, FromIMDBRating, Regions, Languages, RemasterTitles)
		VALUES
			('$LoggedUser[ID]','" . db_string($_POST['label' . $FormID]) . "','$ArtistList','$ExcludeVA','$NewGroupsOnly','$TagList', '$NotTagList', '$ReleaseTypeList','$CategoryList','$CodecList','$SourceList','$ContainerList', '$ResolutionList', '$FromYear', '$ToYear', '$FromSize', '$ToSize', '$Users', '$NotUsers', '$FromIMDBRating', '$RegionList', '$LanguageList', '$RemasterTitles')");
}

$Cache->delete_value('notify_filters_' . $LoggedUser['ID']);
if (($Notify = $Cache->get_value('notify_artists_' . $LoggedUser['ID'])) !== false && $Notify['ID'] == $_POST['id' . $FormID]) {
    $Cache->delete_value('notify_artists_' . $LoggedUser['ID']);
}
header('Location: user.php?action=notify');
