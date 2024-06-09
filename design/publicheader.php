<?
global $LoggedUser, $SSL, $LoginKey;
define('FOOTER_FILE', CONFIG['SERVER_ROOT'] . '/design/publicfooter.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title><?= display_str($PageTitle) ?></title>
    <meta http-equiv="X-UA-Compatible" content="chrome=1; IE=edge" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="referrer" content="same-origin" />
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <? if (CONFIG['IS_DEV']) { ?>
        <link rel="stylesheet" type="text/css" media="screen" href="/src/css/publicstyle/style.css" />
    <? } else { ?>
        <link rel="stylesheet" type="text/css" media="screen" href="/app/publicstyle/style.css?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/app/publicstyle/style.css') ?>" />
    <? } ?>
    <script src="<?= CONFIG['STATIC_SERVER'] ?>functions/jquery.js" type="text/javascript"></script>
    <script src="<?= CONFIG['STATIC_SERVER'] ?>functions/script_start.js?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/static/functions/script_start.js') ?>" type="text/javascript"></script>
    <script src="<?= CONFIG['STATIC_SERVER'] ?>functions/ajax.class.js?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/static/functions/ajax.class.js') ?>" type="text/javascript"></script>
    <script src="<?= CONFIG['STATIC_SERVER'] ?>functions/cookie.class.js?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/static/functions/cookie.class.js') ?>" type="text/javascript"></script>
    <script src="<?= CONFIG['STATIC_SERVER'] ?>functions/storage.class.js?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/static/functions/storage.class.js') ?>" type="text/javascript"></script>
    <script src="<?= CONFIG['STATIC_SERVER'] ?>functions/global.js?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/static/functions/global.js') ?>" type="text/javascript"></script>
</head>

<body>
    <div id="head">
        <tr>
            <td>
                <select class="Input" name="language" id="language" onchange="change_lang(this.options[this.options.selectedIndex].value)">
                    <option class="Select-option" value="chs" <? if (empty($_COOKIE['lang']) || $_COOKIE['lang'] == 'chs') { ?>selected<? } ?>>简体中文</option>
                    <?
                    if (true) {
                    ?>
                        <option class="Select-option" value="en" <? if (!empty($_COOKIE['lang']) && $_COOKIE['lang'] == 'en') { ?>selected<? } ?>>English</option>
                    <?
                    }
                    ?>
                </select>
            </td>
        </tr>
    </div>
    <table class="layout" id="maincontent">
        <tr>
            <canvas class="cavs"></canvas>
            <td id="main-td" align="center" valign="middle">
                <div id="logo">
                    <ul id="index-login">
                        <?php if (CONFIG['SHOW_PUBLIC_INDEX']) { ?>
                            <li><a href="index.php"><?= t('server.pub.public_index') ?></a></li>
                        <?php } ?>
                        <li><a id="login-a" href="login.php<?= (isset($LoginKey) && $LoginKey) ? "?loginkey=$LoginKey" : "" ?>"><?= t('server.pub.login') ?></a></li>
                        <?php if (open_registration()) { ?>
                            <li><a href="register.php"><?= t('server.pub.register') ?></a></li>
                        <?php } ?>
                        <?php if (CONFIG['OPEN_EXTERNAL_REFERRALS']) { ?>
                            <li><a href="referral.php"><?= t('server.pub.referral') ?></a></li>
                        <?php } ?>

                    </ul>
                </div>