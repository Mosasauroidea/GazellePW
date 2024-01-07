<?
if (empty($_FILES['images'])) {
    ajax_json_error('invalid request');
}

$names = $_FILES["images"]['name'];
$tmp_names = $_FILES["images"]['tmp_name'];

$user_id = $LoggedUser['ID'];
$Data = [];
for ($i = 0; $i < count($names); $i += 1) {
    $name = $names[$i];
    $tmp_name = $tmp_names[$i];
    $extension = strtolower(end(explode(".", $name)));        // 获取文件后缀名
    if (!ImageTools::valid_extension($extension)) {
        ajax_json_error("Invalid ext: $extension");
    }
    $path = CONFIG['IMAGE_PATH_PREFIX'] . '/user/' . $user_id . '/' . date('Ymd', time()) . '/' . uniqid() . '.' . $extension;
    $Data[] = ['Url' => $tmp_name, 'Name' => $path, 'Ext' => $extension];
}
try {
    $RetPath = ImageTools::multi_fetch_upload($Data);
} catch (Exception $e) {
    ajax_json_error($e->getMessage());
}
$Ret = [];
foreach ($RetPath as $RetName) {
    $Ret[] = ['name' => $RetName];
}
echo json_encode(['files' => $Ret]);
