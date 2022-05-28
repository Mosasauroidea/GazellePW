<?
include(SERVER_ROOT . "/sections/login/close.php");
if ($CloseLogin) {
    $DB->query("select loginkey from login_link where userid=$UserID");
    list($LoginKey) = $DB->next_record();
}
View::show_header(Lang::get('register', 'register_fail'), '', 'PageRegisterStep3');
?>
<div style="margin-top: 2.5rem;">
    <?= Lang::get('register', 'register_fail_note') ?>
</div>
<?
View::show_footer();
?>