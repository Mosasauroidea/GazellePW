<?php

function getClassObject($name, $twig, $config) {
    $name = "Gazelle\\API\\" . str_replace("_", "", ucwords($name, "_"));
    return new $name($twig, $config);
}

$available = [
    'generate_invite',
    'user',
    'wiki',
    'forum',
    'request',
    'artist',
    'collage',
    'torrent',
    "upload",
    'movie_info',
    "imgupload"
];

if (in_array($_GET['action'], $available)) {
    $config = [
        'Categories' => $Categories,
        'CollageCats' => $CollageCats,
        'ReleaseTypes' => $ReleaseTypes,
        'Debug' => $Debug
    ];
    $class = getClassObject($_GET['action'], $Twig, $config);
} else {
    json_error('invalid action');
}

if (empty($_GET['api_key'])) {
    json_error('invalid parameters');
}

$token = $_GET['api_key'];

$app = $Cache->get_value("api_apps_{$token}");
if (!is_array($app)) {
    $DB->prepared_query("
        SELECT Token, Name, UserID
        FROM api_applications
        WHERE Token = ?
        LIMIT 1", $token);
    if ($DB->record_count() === 0) {
        json_error('invalid token');
    }
    $app = $DB->to_array(false, MYSQLI_ASSOC);
    $Cache->cache_value("api_apps_{$token}", $app, 0);
}
$app = $app[0];

if ($app['Token'] !== $token) {
    json_error('invalid token');
}

$response = $class->run();
print(json_encode(['status' => 200, 'response' => $response], JSON_UNESCAPED_SLASHES));
//$Debug->profile();
