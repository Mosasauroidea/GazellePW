<?
if (empty($_FILES['file'])) {
    error('invalid request');
}

$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);        // 获取文件后缀名
if (!ImageTools::valid_extension($extension)) {
    error('invalid ext');
}
$user_id = $LoggedUser['ID'];

$path = 'user/' . $user_id . '/' . date('Ymd', time()) . '/' . uniqid() . '.' . $extension;
$RetPath = ImageTools::fetch_upload($path, $_FILES["file"]["tmp_name"]);
echo json_encode(['name' => $RetPath]);
