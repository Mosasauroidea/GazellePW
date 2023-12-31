<?

use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

View::show_header(t('server.user.two_factor_authentication'), '', 'PageUser2FAStep1');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.user.two_factor_authentication') ?>
        </div>
    </div>

    <div class="BoxBody BodyContent">
        <p><?= t('server.user.two_factor_authentication_note') ?></p>
        <p><?= t('server.user.two_factor_authentication_note_3') ?></p>

        <div class="center">
            <img src="data:image/png;base64,<?
                                            echo base64_encode(
                                                Builder::create()->writer(new PngWriter())
                                                    ->writerOptions([])
                                                    ->data('otpauth://totp/' . CONFIG['SITE_NAME'] . '?secret=' . $_SESSION['private_key'])
                                                    ->size(300)
                                                    ->margin(10)
                                                    ->labelText($_SESSION['private_key'])
                                                    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                                                    ->foregroundColor(new Color(0, 0, 0))
                                                    ->backgroundColor(new Color(255, 255, 255))
                                                    ->build()->getString()
                                            );
                                            ?>">
        </div>
        <div class="center">
            <strong class="twofa_text"><?= t('server.user.secret_text') ?>: <span><?= $_SESSION['private_key'] ?></span></strong>
        </div>

        <div class="center">
            <button class="Button" onclick="location.href='user.php?action=2fa&do=enable2&userid=<?= G::$LoggedUser['ID'] ?>'" id="pad_next"><?= t('server.user.next') ?></button>
        </div>
        <? if (isset($_GET['invalid'])) : ?>
            <div class="center">
                <p class="u-colorWarning"><?= t('server.user.secret_text_invalid') ?></p>
            </div>
        <? endif; ?>
    </div>

</div>

<? View::show_footer(); ?>