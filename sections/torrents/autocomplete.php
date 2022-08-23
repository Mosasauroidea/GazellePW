<?
header('Content-Type: application/json; charset=utf-8');

$FullName = rawurldecode($_GET['query']);

$MaxKeySize = 50;
$KeySize = min($MaxKeySize, max(1, strlen($FullName)));

$Letters = strtolower(substr($FullName, 0, $KeySize));
$AutoSuggest = $Cache->get("autocomplete_torrents_{$KeySize}_$Letters");

if (!$AutoSuggest) {
    $Limit = (($KeySize === $MaxKeySize) ? 250 : 10);
    $DB->query("
		SELECT ID, Name, SubName, Year
		FROM torrents_group
		WHERE 
			Name LIKE '" . db_string(str_replace('\\', '\\\\', $Letters), true) . "%' OR  SubName LIKE '" . db_string(str_replace('\\', '\\\\', $Letters), true) . "%'
		ORDER BY Year DESC
		LIMIT $Limit");
    $AutoSuggest = $DB->to_array(false, MYSQLI_NUM, false);
    $Cache->cache_value("autocomplete_torrents_{$KeySize}_$Letters", $AutoSuggest, 1800 + 7200 * ($MaxKeySize - $KeySize)); // Can't cache things for too long in case names are edited
}

$Matched = 0;
$ArtistIDs = array();
$Response = array(
    'query' => $FullName,
    'suggestions' => array()
);
foreach ($AutoSuggest as $Suggestion) {
    list($GroupID, $Name, $SubName, $Year) = $Suggestion;
    if (stripos($Name, $FullName) === 0 || stripos($SubName, $FullName) === 0) {
        if ($SubName) {
            $Value = "[$SubName] ";
        }
        $Value .= $Name;
        $Value .= " ($Year)";
        $Response['suggestions'][] = array('value' => $Value, 'data' => $GroupID);
        if (++$Matched > 9) {
            break;
        }
    }
}
echo json_encode($Response);
