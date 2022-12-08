<?php

enforce_login();
authorize();

$UserID = $LoggedUser['ID'];
$Language = '';
switch (trim($_POST['language'])) {
  case t('server.common.lang_en'):
    $Language = Lang::EN;
    break;
  case t('server.common.lang_chs'):
    $Language = Lang::CHS;
    break;
  case t('server.common.lang_pt'):
    $Language = Lang::PT;
    break;
  default:
    error(404);
}

$Cache->cache_value("lang_$UserID", $Language);

$SQL = "
	UPDATE users_info AS i
	SET
		i.Lang = '" . db_string($Language) . "'";
$SQL .= " WHERE i.UserID = '" . db_string($UserID) . "'";
$DB->query($SQL);

$Location = (empty($_SERVER['HTTP_REFERER'])) ? "/index.php" : $_SERVER['HTTP_REFERER'];
header("Location: $Location");
