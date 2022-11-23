<?php

function getClassObject($name, $twig, $config) {
    $name = "Gazelle\\API\\" . str_replace("_", "", ucwords($name, "_"));
    return new $name($twig, $config);
}
$config = [
    'Categories' => $Categories,
    'CollageCats' => $CollageCats,
    'ReleaseTypes' => $ReleaseTypes,
    'Debug' => $Debug
];

$class = getClassObject($_GET['action'], G::$Twig, $config);
$response = $class->run();
print(json_encode(['status' => 200, 'response' => $response], JSON_UNESCAPED_SLASHES));
//$Debug->profile();
