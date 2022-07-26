<?php

// redirect if referrals are currently closed
if (!CONFIG['OPEN_EXTERNAL_REFERRALS']) {

    include('closed.php');
    die();
}

// get needed information from post values
$Service = $_POST['service'];
$Email = $_POST['email'];

// let's sanitize the email before we continue
$SanitizedEmail = filter_var($Email, FILTER_SANITIZE_EMAIL);
if (!filter_var($SanitizedEmail, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['verify_error'] = "Invalid Email Address, Please Try Again";
}

// check post token vs session
if ($_POST['token'] !== $_SESSION['referral_token']) {
    die('Invalid Token, please try again.');
}

// verify external user with token match
$Verify = $Referral->verify($Service, $_POST['username']);
if ($Verify === TRUE) {
    // success
    $Invited = $Referral->create_invite($Service, $SanitizedEmail, $_POST['username']);
} else {
    $error = $_SESSION['verify_error'];
}

View::show_header(t('server.referral.external_tracker_referrals'), '', 'PageReferralStep2');
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
    <h1><?= t('server.referral.external_tracker_referrals') ?></h1>
    <br />
    <p><?= t('server.referral.another_tracker_we_trust') ?></p>
    <br />
    <h4><?= t('server.referral.process_follows') ?></h4>
    <br />
    <ol>
        <li><?= t('server.referral.choose_a_tracker_you_are_member') ?></li>
        <li><?php echo CONFIG['SITE_NAME']; ?><?= t('server.referral.will_generate_string') ?></li>
        <li><?= t('server.referral.paste_string') ?></li>
        <li><?= t('server.referral.enter_your_username') ?></li>
        <li><?= t('server.referral.join') ?> <?php echo CONFIG['SITE_NAME']; ?><?= t('server.referral.exclamation_mark') ?></li>
    </ol>
    <br />
    <h2><?= t('server.referral.step_2') ?><?php echo CONFIG['SITE_NAME']; ?></h2>
    <br />
    <?php if (!$Verify || $error) : ?>
        <h3>
            <?= t('server.referral.an_error', ['Values' => [
                $Service
            ]]) ?>
        </h3>
        <br />
        <p><?php echo $error; ?></p>
    <?php else : ?>
        <h3>
            <?= t('server.referral.congratulations', ['Values' => [$Service]]) ?>
        </h3>
    <?php endif; ?>
    <br />
</div>
<?php View::show_footer(); ?>