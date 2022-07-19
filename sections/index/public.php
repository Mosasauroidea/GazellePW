<?php
include(CONFIG['SERVER_ROOT'] . "/sections/login/close.php");
if ($CloseLogin) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: https://kshare.club/');
    return;
}
if (!CONFIG['SHOW_PUBLIC_INDEX']) {
    header('Location: login.php');
    exit;
}

View::show_header('', '', 'PagePublicHome');

echo <<<HTML
<div class="poetry">
<p>
<br />


</p>
<br />
</div>
HTML;

View::show_footer();
