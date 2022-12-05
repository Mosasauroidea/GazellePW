<?

use Gazelle\Manager\Donation;
use Gazelle\Manager\PrepaidCardStatus;

View::show_header(t('server.donate.donate'), '', 'PageDonateStep2');

$SiteName = CONFIG['SITE_NAME'];
$UserID = $LoggedUser['ID'];

$donation = new Donation();
$PrepaidCardDonations = $donation->getPrepaidCardDonations($UserID);
$Article = Wiki::get_article(CONFIG['DONATE_ARTILCE_ID'], false);
$Body = $Article['Body'];
list($Revision, $Title, $Body, $Read, $Edit, $Date, $AuthorID, $AuthorName, $Aliases, $UserIDs,, $FatherID) = array_shift($Article);
?>

<div class="LayoutBody" id="donate_information">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= t('server.donate.donate') ?>
        </h2>
    </div>
    <form class="Form send_form pad" name="donate" action="donate.php" method="post">

        <input type="hidden" name="action" value="donate" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <div class="Form-title"><?= t('server.donate.prepaid_card') ?></div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.donate.card_num') ?>:</div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="card_num" size="60" placeholder="<?= t('server.donate.card_num_length') ?>" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.donate.card_secret') ?>:</div>
                <div class="Form-inputs"><input class="Input" type="text" name="card_secret" size="60" maxlength="180" placeholder="<?= t('server.donate.card_secret_length') ?>" /></div>
            </div>

            <div class="Form-row">
                <div class="Form-label"><?= t('server.donate.face_value') ?>:</div>
                <div class="Form-inputs">
                    <select class="Input" name="face_value">
                        <option class="Select-option" value="50"><?= '50 ' . t('server.donate.yuan') ?></option>
                        <option class="Select-option" value="100"><?= '100 ' . t('server.donate.yuan') ?> </option>
                        <option class="Select-option" value="300"><?= '300 ' . t('server.donate.yuan') ?> </option>
                        <option class="Select-option" value="500"><?= '500 ' . t('server.donate.yuan') ?> </option>
                    </select>
                </div>
            </div>
            <div class="Form-row Form-submit">
                <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
            </div>
        </div>
    </form>
    <?
    if (count($PrepaidCardDonations) > 0) {
    ?>
        <div class="Gruop">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.donate.history') ?>
                </div>
            </div>
            <div class="Group-body">
                <div class="TableContainer">
                    <table class="TableDonate Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.donate.added_time') ?></td>
                            <td class="Table-cell"><?= t('server.donate.card_num') ?></td>
                            <td class="Table-cell"><?= t('server.donate.card_secret') ?></td>
                            <td class="Table-cell"><?= t('server.donate.face_value') ?></td>
                            <td class="Table-cell"><?= t('server.donate.status') ?></td>
                        </tr>
                        <?
                        $Row = 'a';
                        foreach ($PrepaidCardDonations as $Item) {
                            list(,, $CreateTime, $CardNum, $CardSecret, $FaceValue, $Status) = $Item;
                            $Row = $Row === 'a' ? 'b' : 'a';
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell"><?= $CreateTime ?></td>
                                <td class="Table-cell"><?= $CardNum ?></td>
                                <td class="Table-cell"><?= $CardSecret ?></td>
                                <td class="Table-cell"><?= $FaceValue ?></td>
                                <td class="Table-cell">
                                    <? if ($Status == PrepaidCardStatus::Pending) {
                                        echo t('server.donate.pending');
                                    } else if ($Status == PrepaidCardStatus::Passed) {
                                        echo '<span class="u-colorSuccess">' . t('server.donate.success') . '</span>';
                                    } else if ($Status == PrepaidCardStatus::Reject) {
                                        echo '<span class="u-colorWarning">' . t('server.donate.failed') . '</span>';
                                    } ?>
                                </td>
                            </tr>
                        <?  } ?>
                    </table>
                </div>
            </div>
        </div>
    <?
    }
    ?>
    <div class="Post donation_info">
        <div class="Post-header">
            <div class="Post-headerTitle donation_info_title">
                <?= t('server.donate.tutorials') ?>
            </div>
        </div>
        <div id="donate_guide" class="Post-body HtmlText PostArticle">
            <?= Text::full_format($Body) ?>
        </div>
    </div>
</div>


<? View::show_footer();
