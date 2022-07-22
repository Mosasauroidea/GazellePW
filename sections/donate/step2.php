<?

use Gazelle\Manager\Donation;
use Gazelle\Manager\PrepaidCardStatus;

View::show_header(Lang::get('donate.donate'), '', 'PageDonateStep2');

$SiteName = CONFIG['SITE_NAME'];
$UserID = $LoggedUser['ID'];

$donation = new Donation();
$PrepaidCardDonations = $donation->getPrepaidCardDonations($UserID);
$Article = Wiki::get_article(CONFIG['DONATE_ARTILCE_ID'], false);
$Body = $Article['Body'];
list($Revision, $Title, $Body, $Read, $Edit, $Date, $AuthorID, $AuthorName, $Aliases, $UserIDs,, $FatherID) = array_shift($Article);
?>

<div class="LayoutBody" id="donate_information">
    <form class=" send_form pad" name="donate" action="donate.php" method="post">
        <h2><?= Lang::get('donate.donate') ?></h2>
        <input type="hidden" name="action" value="donate" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <div class="Form-title"><?= Lang::get('donate.prepaid_card') ?></div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= Lang::get('donate.card_num') ?>:</div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="card_num" size="60" placeholder="<?= Lang::get('donate.card_num_length') ?>" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= Lang::get('donate.card_secret') ?>:</div>
                <div class="Form-inputs"><input class="Input" type="text" name="card_secret" size="60" maxlength="180" placeholder="<?= Lang::get('donate.card_secret_length') ?>" /></div>
            </div>

            <div class="Form-row">
                <div class="Form-label"><?= Lang::get('donate.face_value') ?>:</div>
                <div class="Form-inputs">
                    <select class="Input" name="face_value">
                        <option class="Select-option" value="50"><?= '50 ' . Lang::get('donate.yuan') ?></option>
                        <option class="Select-option" value="100"><?= '100 ' . Lang::get('donate.yuan') ?> </option>
                        <option class="Select-option" value="300"><?= '300 ' . Lang::get('donate.yuan') ?> </option>
                        <option class="Select-option" value="500"><?= '500 ' . Lang::get('donate.yuan') ?> </option>
                    </select>
                </div>
            </div>
            <div class="Form-row Form-submit">
                <input class="Button" type="submit" value="<?= Lang::get('global.submit') ?>" />
            </div>
        </div>
    </form>
</div>
<?
if (count($PrepaidCardDonations) > 0) {
?>
    <h3><?= Lang::get('donate.history') ?>
    </h3>

    <div class="TableContainer">
        <table class="TableDonate Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= Lang::get('donate.added_time') ?></td>
                <td class="Table-cell"><?= Lang::get('donate.card_num') ?></td>
                <td class="Table-cell"><?= Lang::get('donate.card_secret') ?></td>
                <td class="Table-cell"><?= Lang::get('donate.face_value') ?></td>
                <td class="Table-cell"><?= Lang::get('donate.status') ?></td>
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
                            echo Lang::get('donate.pending');
                        } else if ($Status == PrepaidCardStatus::Passed) {
                            echo '<span class="u-colorSuccess">' . Lang::get('donate.success') . '</span>';
                        } else if ($Status == PrepaidCardStatus::Reject) {
                            echo '<span class="u-colorWarning">' . Lang::get('donate.failed') . '</span>';
                        } ?>
                    </td>
                </tr>
            <?  } ?>
        </table>
    </div>
<?
}
?>
<div class="Box donation_info">
    <div class="Box-header">
        <strong class="donation_info_title">
            <?= Lang::get('donate.tutorials') ?>
        </strong>
    </div>
    <div id="donate_guide" class="Box-body HtmlText PostArticle">
        <?= Text::full_format($Body) ?>
    </div>
</div>

<? View::show_footer();
