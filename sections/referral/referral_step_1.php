<?php

// redirect if referrals are currently closed
if (!CONFIG['OPEN_EXTERNAL_REFERRALS']) {

    include('closed.php');
    die();
}

// get service from post value
$Service = $_POST['service'];
// save service to session


// generate token
$Token = $Referral->generate_token();



View::show_header(Lang::get('referral', 'external_tracker_referrals'), '', 'PageReferralStep1');
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

    label {
        margin-left: 15px;
    }

    #referral-code {
        color: #f5f5f5;
        padding: 10px;
        background-color: #151515;
        text-align: center;
    }
</style>
<div style="width: 500px; text-align: left">
    <h1><?= Lang::get('referral', 'external_tracker_referrals') ?></h1>
    <br />
    <p><?= Lang::get('referral', 'another_tracker_we_trust_1') ?><?php echo CONFIG['SITE_NAME']; ?><?= Lang::get('referral', 'another_tracker_we_trust_2') ?></p>
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
    <br />
    <h2><?= Lang::get('referral', 'step_1') ?></h2>
    <br />
    <p><?= Lang::get('referral', 'copy_and_paste_the_code_1') ?><?php echo $Service; ?><?= Lang::get('referral', 'copy_and_paste_the_code_2') ?></p>
    <br />
    <br />
    <p id="referral-code"><?php echo $Token; ?></p>
    <br />
    <br />
    <p><?= Lang::get('referral', 'enter_username_at_1') ?><?php echo $Service; ?><?= Lang::get('referral', 'enter_username_at_2') ?></p>
    <br />
    <form name="referral_service" method="post" action="">
        <input class="Input" type="text" name="username" /><label for="username"><?= Lang::get('referral', 'username') ?></label>
        <br />
        <br />
        <input class="Input" type="text" name="email" /><label for="Email"><?= Lang::get('referral', 'email_address') ?></label>
        <input type="hidden" name="token" value="<?php echo $Token; ?>" />
        <input type="hidden" name="service" value="<?php echo $Service; ?>" />
        <br />
        <br />
        <input class="Button" type="submit" name="submit" value="Verify" />
    </form>

</div>
<?php View::show_footer(); ?>