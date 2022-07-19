<?php

// redirect if referrals are currently closed

if (!CONFIG['OPEN_EXTERNAL_REFERRALS']) {

    include('closed.php');
    exit;
}

$Referral = new Referral();
$AvailableServices = $Referral->services_list();

// head to step 2 if are ready to verify an account at the external service
if (!empty($_POST['username']) && $_POST['submit'] === 'Verify') {
    include('referral_step_2.php');
    exit;
}

// head to step 1 if we've selected a tracker, and verified that it's still up
if (in_array($_POST['service'], $AvailableServices)) {
    include('referral_step_1.php');
    exit;
}

View::show_header(Lang::get('referral', 'external_tracker_referrals'), '', 'PageReferralHome');
?>
<style>
    * {
        margin: initial;
        padding: initial;
    }

    ol {
        -webkit-margin-before: 1em;
        -webkit-margin-after: 1em;
        -webkit-padding-start: 40px;
    }
</style>
<div style="width: 500px; text-align: left">
    <h1><?= Lang::get('referral', 'external_tracker_referrals') ?></h1>
    <br />
    <p><?= Lang::get('referral', 'another_tracker_we_trust_1') ?> <?php echo CONFIG['SITE_NAME']; ?> <?= Lang::get('referral', 'another_tracker_we_trust_2') ?></p>
    <br />
    <h4><?= Lang::get('referral', 'process_follows') ?></h4>
    <br />
    <ol>
        <li><?= Lang::get('referral', 'choose_a_tracker_you_are_member') ?></li>
        <li><?php echo CONFIG['SITE_NAME']; ?><?= Lang::get('referral', 'will_generate_string') ?></li>
        <li><?= Lang::get('referral', 'paste_string') ?></li>
        <li><?= Lang::get('referral', 'enter_your_username_1') ?><?php echo CONFIG['SITE_NAME']; ?> <?php echo CONFIG['SITE_NAME']; ?><?= Lang::get('referral', 'enter_your_username_2') ?></li>
        <li><?= Lang::get('referral', 'join') ?> <?php echo CONFIG['SITE_NAME']; ?><?= Lang::get('referral', 'exclamation_mark') ?></li>
    </ol>

    <?php if (!empty($AvailableServices)) : ?>
        <br />
        <h2><?= Lang::get('referral', 'choose_a_tracker') ?></h2>
        <br />
        <form name="referral_service" method="post" action="">
            <?php
            foreach ($AvailableServices as $Service) {
                echo '<input type="radio" name="service" value="' . $Service . '"/><label for="' . $Service . '">  ' . $Service . '</label><br/><br/>';
            } ?>
            <br />
            <input class="Button" type="submit" name="submit" value="<?= Lang::get('global', 'submit') ?>" />
        </form>
    <?php else : ?>
        <br />
        <h2><?= Lang::get('referral', 'please_try_again') ?></h2>
        <br />
    <?php endif; ?>
</div>
<?php View::show_footer(); ?>