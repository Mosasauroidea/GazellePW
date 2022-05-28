<?
View::show_header('Locked Account', '', 'PageLockedAccount');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Locked Account</h2>
</div>
<? if (G::$LoggedUser['LockedAccount'] == STAFF_LOCKED) { ?>
    <div class="Box">
        <div class="Box-body">
            <p>Your account has been locked. Please send a <a href="staffpm.php">Staff PM</a> to find out how this happened.</p>
        </div>
    </div>
<? }
View::show_footer();
