<?
View::show_header(t('server.donate.donate'), '', 'PageDonateStep1');
?>

<div class="LayoutBody" id="donate_information">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.donate.donate') ?></h2>
    </div>
    <div class="Post">
        <div class="PostBody PostArticle donation_info HtmlText" id="Donate-Overview-mdx"></div>
    </div>
</div>

<?

use Gazelle\Manager\Donation;

global $WINDOW_DATA;
$donation = new Donation();
$WINDOW_DATA['donationProgress'] = $donation->getYearProgress() . '%';

View::show_footer();
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Donate/Overview.mdx')
    })
</script>