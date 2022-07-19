<?
View::show_header(Lang::get('register', 'register_closed'), 'PageRegisterClosed');
?>
<div style="margin-top: 2.5rem;">
    <!-- <strong>Sorry, the site is currently invite only.</strong>
    <br> -->
    <? if (empty(CONFIG['OPEN_REGISTRATION_EMAIL'])) {
    ?>
        <strong><?= Lang::get('register', 'register_closed_note') ?></strong>
    <?
    } else {
    ?>
        <strong><?= Lang::get('register', 'register_closed_note2') ?></strong>
        <ul style="width:80px; text-align:left">
            <?
            foreach (CONFIG['OPEN_REGISTRATION_EMAIL'] as $Email) {
            ?>
                <li>
                    <?= $Email ?>
                </li>
            <?
            }
            ?>
        </ul>
    <?
    }
    ?>
</div>
<?
View::show_footer();
?>