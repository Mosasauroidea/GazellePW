<?
if (CONFIG['CLOSE_LOGIN']) {
    $DB->query("select loginkey from login_link where userid=$UserID");
    list($LoginKey) = $DB->next_record();
}
View::show_header(t('server.register.register_fail'), '', 'PageRegisterStep3');
?>
<div style="margin-top: 2.5rem;">
    <?= t('server.register.register_fail_note') ?>
</div>
<?
View::show_footer();
?>