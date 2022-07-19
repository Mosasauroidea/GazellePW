<?
View::show_header('两步验证', '', 'PageUser2FAComplete');

$UserID = $_REQUEST['userid'];
if (!is_number($UserID)) {
    error(404);
}

$DB->query("SELECT Recovery FROM users_main WHERE ID = '" . db_string($UserID) . "'");

list($Recovery) = $DB->next_record(MYSQLI_NUM, false);

// don't worry about the permission check, we did that in the controller :)
?>

<div class="BoxBody">
    <p>请注意，如果你丢失了你的两步验证密钥以及所有的备用密钥，即使是 <?= CONFIG['SITE_NAME'] ?> 的工作人员也救不回你的账号。请确保你将备用密钥安置在妥当之处。</p>
</div>

<div class="BoxBody">
    <p>你的账号已启用两步验证。请务必记录下面的恢复密钥，它们是你丢失硬件设备时恢复账号的唯一救星。</p>

    <ul class="pad">
        <? foreach (unserialize($Recovery) as $r) : ?>
            <li><?= $r ?></li>
        <? endforeach; ?>
    </ul>
</div>

<? View::show_footer(); ?>