<?php
if (CONFIG['CLOSE_LOGIN']) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . CONFIG['CLOSE_REDIRECT_URL']);
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
