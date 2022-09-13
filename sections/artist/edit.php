<?

/************************************************************************
||------------|| Edit artist wiki page ||------------------------------||

This page is the page that is displayed when someone feels like editing
an artist's wiki page.

It is called when $_GET['action'] == 'edit'. $_GET['artistid'] is the
ID of the artist, and must be set.

 ************************************************************************/

$ArtistID = $_GET['artistid'];
if (!is_number($ArtistID)) {
    error(0);
}

// Get the artist name and the body of the last revision
$Artist = Artists::get_artist_by_id($ArtistID);
$ArtistName = Artists::display_artist($Artist);
$SubName = $Artist['SubName'];
$Name = $Artist['Name'];
$Body = $Artist['Body'];
$MainBody = $Artist['MainBody'];
$Image = $Artist['Image'];
$IMDBID = $Artist['IMDBID'];

// Start printing form
View::show_header(t('server.artist.edit_artist'), 'PageArtistEdit');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.artist.edit_artist')  ?></div>
        <div class="BodyHeader-subNav"><?= $ArtistName ?></div>
    </div>
    <form class="edit_form Form-rowList" name="artist" action="artist.php" method="post">
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
        <table class="Table" variant="header">
            <tr class="Form-rowHeader">
                <td class="Form-title"><?= t('server.artist.edit_artist') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.artist.imdb_artist_id') ?>:
                </td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" name="imdb_id" size="20" placeholder="nm1234567" value="<?= $IMDBID ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.artist.artist_name') ?>:
                </td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" name="name" size="20" placeholder="" value="<?= $Name ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.artist.sub_name') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" name="sub_name" size="20" placeholder="" value="<?= $SubName ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.artist.image') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="image" size="92" value="<?= $Image ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.artist.chinese_biography') ?>:
                </td>
                <td class="Form-items">
                    <textarea class=" Input" name="body" cols="91" rows="20"><?= $Body ?></textarea>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.artist.english_biography') ?>:
                </td>
                <td class="Form-items">
                    <textarea class=" Input" name="mainbody" cols="91" rows="20"><?= $MainBody ?></textarea>
                </td>
            </tr>

            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.artist.edit_summary') ?>:
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="summary" size="92" />
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2">
                    <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                </td>
            </tr>
        </table>
    </form>

    <form class="Form-rowList merge_form" name="artist" action="artist.php" method="post">
        <input type="hidden" name="action" value="change_artistid" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
        <table class="Table" variant="header">
            <tr class="Form-rowHeader">
                <td class="Form-title"><?= t('server.artist.make_into') ?></td>
            </tr>
            <tr class="Form-row">
                <td colspan="2">
                    <p><?= t('server.artist.make_into_note') ?></p><br />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <label for="newartistid"><?= t('server.artist.artist_id') ?>: </label>
                </td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" id="newartistid" name="newartistid" size="40" value="" />
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2">
                    <input class="Button" type="submit" value="<?= t('server.artist.change_artist_id') ?>" />
                </td>
            </tr>
        </table>
    </form>

    <form class="Form-rowList add_form" name="aliases" action="artist.php" method="post">
        <input type="hidden" name="action" value="add_alias" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
        <table class="Table" variant="header">

            <tr class="Form-rowHeader">
                <td class="Form-title"><?= t('server.artist.artist_aliases') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-row" colspan="2">
                    <?= t('server.artist.add_alias_note') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.artist.aliases_list') ?>:</td>
                <td class="Form-items">
                    <div class="Box-body">
                        <ul>
                            <?
                            $DB->query(
                                "SELECT AliasID, Name, UserID
                            		FROM artists_alias
		                            WHERE ArtistID = '$ArtistID'"
                            );
                            while (list($AliasID, $AliasName, $User) = $DB->next_record(MYSQLI_NUM, true)) {
                            ?>
                                <li>
                                    <span data-tooltip="Alias ID"><?= $AliasID ?></span>. <span data-tooltip="Alias name"><?= $AliasName ?></span>
                                    <? if ($User) { ?>
                                        <a href="user.php?id=<?= $User ?>" data-tooltip="Alias creator" class="brackets"><?= t('server.artist.user') ?></a>
                                    <?      }
                                    ?>

                                    <a href="artist.php?action=delete_alias&amp;aliasid=<?= $AliasID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= t('server.artist.delete_this_alias') ?>" class="brackets">X</a>
                                </li>
                            <?  }
                            ?>
                        </ul>
                    </div>
                </td>
            </tr>

            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.artist.name') ?>:
                </td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" name="name" size="20" value="<?= $Name ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2">
                    <input class="Button" type="submit" value="<?= t('server.common.add') ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>
<? View::show_footer() ?>