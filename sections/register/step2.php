<?
if (CONFIG['CLOSE_LOGIN']) {
    $DB->query("select loginkey from login_link where userid=$UserID");
    list($LoginKey) = $DB->next_record();
}
View::show_header(t('server.register.register_complete'), '', 'PageResiterStep2');
?>
<div style="margin-top: 2.5rem;">
    <?
    $register_complete_note = t('server.register.register_complete_note');
    if (CONFIG['CLOSE_LOGIN']) {
        $register_complete_note = str_replace("login.php", "login.php?loginkey=$LoginKey", $register_complete_note);
    }
    ?>
    <?= $register_complete_note ?>
</div>
<?
View::show_footer();
?>