<?php
authorize();

if (!preg_match('/^(upload)-[1234]$/', $Label, $match)) {
    error(403);
}

authorize();

if (!$Bonus->purchaseUpload(G::$LoggedUser['ID'], $Label)) {
    error('Purchase not concluded.');
}

header('Location: bonus.php?complete=' . urlencode($Label));
