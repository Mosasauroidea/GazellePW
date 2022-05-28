<?php
authorize();
$Stats = $Bonus->purchaseHNR(G::$LoggedUser['ID']);
// TODO by qwerty i18N
if ($Stats == 2) {
    error("没有种子需要消除H&R.");
} else if ($Stats != 1) {
    error("消除失败，请重试.");
}

header('Location: bonus.php?complete=' . urlencode($Label));
