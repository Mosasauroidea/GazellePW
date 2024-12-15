<?php

use Gazelle\Manager\ActionTrigger;

authorize();

include(CONFIG['SERVER_ROOT'] . '/classes/validate.class.php');
$Val = new VALIDATE;

$P = array();
$P = db_array($_POST);

if ($P['category'] != $PersonalCollageCategoryCat || check_perms('site_collages_renamepersonal')) {
    $Val->SetFields('name', '1', 'string', t('server.collages.the_name_must_between'), array('maxlength' => 100, 'minlength' => 3));
} else {
    // Get a collage name and make sure it's unique
    $name = $LoggedUser['Username'] . "'s personal collage";
    $P['name'] = db_string($name);
    $DB->query("
		SELECT ID
		FROM collages
		WHERE Name = '" . $P['name'] . "'");
    $i = 2;
    while ($DB->has_results()) {
        $P['name'] = db_string("$name no. $i");
        $DB->query("
			SELECT ID
			FROM collages
			WHERE Name = '" . $P['name'] . "'");
        $i++;
    }
}
$Val->SetFields('description', '1', 'string', t('server.collages.the_description_must_between'), array('maxlength' => 65535, 'minlength' => 10));

$Err = $Val->ValidateForm($_POST);
if (!$Err && $P['category'] == $PersonalCollageCategoryCat) {
    $DB->query("
		SELECT COUNT(ID)
		FROM collages
		WHERE UserID = '$LoggedUser[ID]'
			AND CategoryID = '0'
			AND Deleted = '0'");
    list($CollageCount) = $DB->next_record();
    if (($CollageCount >= $LoggedUser['Permissions']['MaxCollages']) || !check_perms('site_collages_personal')) {
        $Err = t('server.collages.you_may_not_create_a_personal_collage');
    } elseif (check_perms('site_collages_renamepersonal') && !stristr($P['name'], $LoggedUser['Username'])) {
        $Err = t('server.collages.your_personal_collage_must');
    }
}

if (!$Err) {
    $DB->query("
		SELECT ID, Deleted
		FROM collages
		WHERE Name = '$P[name]'");
    if ($DB->has_results()) {
        list($ID, $Deleted) = $DB->next_record();
        if ($Deleted) {
            $Err = t('server.collages.that_collage_already_exists_1');
        } else {
            $Err = t('server.collages.that_collage_already_exists_2');
        }
    }
}

if (!$Err) {
    if (!in_array($P['category'], $CollageCats)) {
        $Err = t('server.collages.please_select_a_category');
    }
}

if ($Err) {
    $Name = $_POST['name'];
    $Category = $_POST['category'];
    $Tags = $_POST['tags'];
    $Description = $_POST['description'];
    include(CONFIG['SERVER_ROOT'] . '/sections/collages/new.php');
    die();
}

$TagList = explode(',', $_POST['tags']);
foreach ($TagList as $ID => $Tag) {
    $TagList[$ID] = Misc::sanitize_tag($Tag);
}
$TagList = implode(' ', $TagList);

$DB->query("
	INSERT INTO collages
		(Name, Description, UserID, TagList, CategoryID)
	VALUES
		('$P[name]', '$P[description]', $LoggedUser[ID], '$TagList', '$P[category]')");

$CollageID = $DB->inserted_id();
$Cache->delete_value("collage_$CollageID");
Misc::write_log("Collage $CollageID (" . $_POST['name'] . ') was created by ' . $LoggedUser['Username']);

$trigger = new ActionTrigger;
$trigger->triggerCreateCollage($CollageID);
header("Location: collages.php?id=$CollageID");
