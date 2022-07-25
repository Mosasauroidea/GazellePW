<?
View::show_header(t('server.user.two_factor_authentication'), '', 'PageUser2FAStep1');
?>

<div class="BoxBody">
    <p><?= t('server.user.two_factor_authentication_note_1') ?> <?= CONFIG['SITE_NAME'] ?> <?= t('server.user.two_factor_authentication_note_2') ?></p>

    <p><?= t('server.user.two_factor_authentication_note_3') ?></p>
</div>

<div class="box box2">
    <div class="center pad">
        <div>
            <img src="data:image/png;base64,<?
                                            echo base64_encode(
                                                (new QrCode())->setText('otpauth://totp/' . CONFIG['SITE_NAME'] . '?secret=' . $_SESSION['private_key'])
                                                    ->setSize(300)
                                                    ->setPadding(10)
                                                    ->setLabel($_SESSION['private_key'])
                                                    ->setErrorCorrection('high')
                                                    ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
                                                    ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
                                                    ->get('png')
                                            );
                                            ?>">
            <div class="twofa_text"><?= t('server.user.secret_text') ?>: <span><?= $_SESSION['private_key'] ?></span></div>

            <? if (isset($_GET['invalid'])) : ?>
                <p class="u-colorWarning"><?= t('server.user.secret_text_invalid') ?></p>
            <? endif; ?>
        </div>

        <a href="user.php?action=2fa&do=enable2&userid=<?= G::$LoggedUser['ID'] ?>" id="pad_next"><?= t('server.user.next') ?> &raquo;</a>
    </div>
</div>

<? View::show_footer(); ?>