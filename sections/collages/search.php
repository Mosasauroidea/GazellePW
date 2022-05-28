<?
if (!isset($_REQUEST['name']) || !isset($_REQUEST['type']) || !isset($_REQUEST['self'])) {
    error(403);
}
$Name = db_string($_REQUEST['name']);
$Self = $_REQUEST['self'] === 'true' ? true : false;
$ArtistCollage = $_REQUEST['type'] == 'artist' ? true : false;
if ($Self) {
    $where = "c.UserID=" . $LoggedUser['ID'] . " and";
} else if ($Name) {
    $where = "Name like '%$Name%' and";
}
$SQL =
    "SELECT c.ID, c.Name, c.UserID, u.Username, c.Description, c.Locked, c.CategoryID, NumTorrents, MaxGroups, MaxGroupsPerUser, count(" . ($ArtistCollage ? 'ArtistID' : 'GroupID') . ") NumGroupsByUser
from collages c 
left join " . ($ArtistCollage ? 'collages_artists' : 'collages_torrents') . ' ct 
on ID = CollageID and ct.UserID=' . $LoggedUser['ID'] . " 
left join users_main u on c.UserID=u.ID
where $where CategoryID" . ($ArtistCollage ? '=' : '!=') . "7 and Deleted='0'
group by ID";
$DB->query($SQL);
$Collages = $DB->to_array(false, MYSQLI_ASSOC);
$r = [];
$CollageCats = Lang::get('collages', 'collagecats');
foreach ($Collages as $Collage) {
    if ($Collage['CategoryID'] === '0' && !check_perms('site_collages_delete')) {
        if (!check_perms('site_collages_personal') || $Collage['UserID'] !== $LoggedUser['ID']) {
            continue;
        }
    }
    if (
        !check_perms('site_collages_delete')
        && ($Locked
            || ($Collage['MaxGroups'] > 0 && $$Collage['NumTorrents'] >= $$Collage['MaxGroups'])
            || ($$Collage['MaxGroupsPerUser'] > 0 && $$Collage['NumGroupsByUser'] >= $$Collage['MaxGroupsPerUser']))
    ) {
        continue;;
    }
    $r[$Collage['ID']] = [
        'id' => $Collage['ID'],
        'name' => $Collage['Name'],
        'userid' => $Collage['UserID'],
        'username' => $Collage['Username'],
        'description' => $Collage['Description'],
        'category' => $CollageCats[$Collage['CategoryID']]
    ];
}
echo json_encode($r);
