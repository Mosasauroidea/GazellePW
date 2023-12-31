<?
require(__DIR__ . '/../classes/includes.php');

$StartGroupID = $argv[1];
$EndGroupID = $argv[2];
$Select = 'ID, IMDBID, MainWikiBody, WikiImage, WikiBody, IMDBID, DoubanID, RTTitle, Name, SubName, Year, ReleaseType, RevisionID';
if ($StartGroupID && $EndGroupID) {
    echo "Handle group: $StartGroupID-$EndGroupID\n";
    $DB->prepared_query("SELECT $Select FROM torrents_group WHERE ID >= ? and ID < ?", $StartGroupID, $EndGroupID);
} else {
    echo "Handle all group\n";
    $DB->prepared_query("SELECT $Select FROM torrents_group");
}

$Datas = [];

while ($Data = $DB->next_record(MYSQLI_NUM)) {
    $Datas[] = $Data;
}
foreach ($Datas as $Data) {
    list($ID, $IMDBID, $MainWikiBody, $WikiImage, $WikiBody, $IMDBID, $DoubanID, $RTTitle, $Name, $SubName, $Year, $ReleaseType, $RevisionID) = $Data;
    $NewRevision = empty($RevisionID);
    if (!empty($IMDBID) && empty($MainWikiBody)) {
        try {
            $MainPlot = Movie::get_main_plot($IMDBID);
            if (!empty($MainPlot)) {
                $MainWikiBody = $MainPlot;
                $NewRevision = true;
                echo "Get main plot success for group id: $ID\n";
            }
        } catch (Exception $e) {
            echo "Get IMDb info failed for group id:$ID, imdb id:$IMDBID\n, exception:$e";
        }
    }
    if ($NewRevision) {
        $DB->prepared_query("INSERT INTO wiki_torrents
			(
                PageID, 
                Body, 
                MainBody,
                UserID, 
                Summary, 
                Time, 
                Image, 
                IMDBID, 
                DoubanID, 
                Year, 
                Name, 
                SubName,
                ReleaseType)
		VALUES
			(
                $ID,
                '$WikiBody',
                '" . db_string($MainWikiBody) . "',
                0,
                'Auto load',
                '" . sqltime() . "',
                '$WikiImage',
                '$IMDBID',
                '$DoubanID',
                '$Year',
                '$Name',
                '$SubName',
                '$ReleaaseType'
            )
                ");
        $RevisionID = $DB->inserted_id();
        G::$DB->prepared_query("UPDATE torrents_group SET RevisionID = $RevisionID, MainWikiBody = '" . db_string($MainWikiBody) . "' WHERE ID = $ID");
        echo "Add new revision for group:$ID, revision id:$RevisionID\n";
    } else {
        $DB->prepared_query("UPDATE wiki_torrents SET Body = '$Body', MainBody='$MainBody', Image='$Image', IMDBID='$IMDBID', DoubanID='$DoubanID', Year='$Year', Name='$Name', SubName='$SubName', ReleaseType='$ReleaseType'");
    }
    echo "Update group: $ID wiki and main body info success\n";
}
