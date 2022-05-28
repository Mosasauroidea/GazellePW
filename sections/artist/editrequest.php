<?php

if (empty($_GET['artistid']) || !is_numeric($_GET['artistid'])) {
    error(404);
}
$ArtistID = intval($_GET['artistid']);

$Artist = Artists::get_artist_by_id($ArtistID, true);
$Name = Artists::display_artist($Artist);

View::show_header(Lang::get('artist', 'request_an_edit'), '', 'PageArtistEditReqeust');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('artist', 'request_an_edit') ?> > <?= $Name ?></h2>
    </div>
    <div class="Box">
        <div class="Box-body">
            <?= Lang::get('artist', 'you_are_req_for_note') ?>
        </div>
    </div>
    <div class="Form-rowList" variant="header">
        <div class="Form-rowHeader"><strong class="u-colorWarning"><?= Lang::get('artist', 'edit_details') ?></strong></div>
        <form action="artist.php" method="POST">
            <input type="hidden" name="action" value="takeeditrequest" />
            <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
            <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
            <div>
                <textarea class="Input" name="edit_details" required="required"></textarea>
            </div>
            <div class="Form-row">
                <input class="Button" type="submit" value="<?= Lang::get('global', 'submit') ?>" />
            </div>
        </form>
    </div>
</div>
<?php
View::show_footer();
