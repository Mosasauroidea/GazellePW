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
$Image = $Artist['Image'];
$IMDBID = $Artist['IMDBID'];

// Start printing form
View::show_header(t('server.artist.edit_artist'), 'PageArtistEdit');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.common.edit') ?><?= $ArtistName ?></h2>
    </div>
    <form class="edit_form" name="artist" action="artist.php" method="post">
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.artist.edit_artist') ?>
            </div>
            <div class="Form-row">
                <div class="Form-items"><?= t('server.artist.image') ?>:<input class="Input" type="text" name="image" size="92" value="<?= $Image ?>" /></div>
            </div>
            <div class="Form-row">
                <div class="Form-items"><?= t('server.artist.artist_info') ?>:<textarea class=" Input" name="body" cols="91" rows="20"><?= $Body ?></textarea>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-inputs">
                    <div>
                        <?= t('server.artist.imdb_artist_id') ?>:
                        <input class="Input is-small" type="text" name="imdb_id" size="20" placeholder="nm1234567" value="<?= $IMDBID ?>" />
                    </div>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-inputs">
                    <div><?= t('server.artist.artist_name') ?>:
                        <input class="Input is-small" type="text" name="name" size="20" placeholder="" value="<?= $Name ?>" />
                    </div>
                    <div><?= t('server.artist.sub_name') ?>:
                        <input class="Input is-small" type="text" name="sub_name" size="20" placeholder="" value="<?= $SubName ?>" />
                    </div>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-items"><?= t('server.artist.edit_summary') ?>:
                    <input class="Input" type="text" name="summary" size="92" />
                </div>
            </div>
            <div class="Form-row">
                <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
            </div>
        </div>
    </form>
</div>

<form class="merge_form" name="artist" action="artist.php" method="post">
    <input type="hidden" name="action" value="change_artistid" />
    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
    <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
    <div class="Form-rowList" variant="header">
        <div class="Form-rowHeader"><?= t('server.artist.make_into') ?></div>
        <div class="Form-row">
            <p><?= t('server.artist.make_into_note', ['Values' => [
                    $Name,
                    $Name
                ]]) ?></p><br />
        </div>
        <div class="Form-row">
            <div><label for="newartistid"><?= t('server.artist.artist_id') ?>: </label></div>
            <div>
                <input class="Input is-small" type="text" id="newartistid" name="newartistid" size="40" value="" />
            </div>
            <strong><?= t('server.artist.or') ?></strong>
            <div><label for=" newartistid"><?= t('server.artist.artist_name') ?>: </label></div>
            <div><input class="Input is-small" type="text" id="newartistname" name="newartistname" size="40" value="" /></div>
            <input class="Button" type="submit" value="<?= t('server.artist.change_artist_id') ?>" />
        </div>
    </div>
</form>

<div class="Form-rowList" variant="header">
    <div class="Form-rowHeader"><?= t('server.artist.artist_aliases') ?></div>
    <div class="Form-row">
        <div class="Form-items"><?= t('server.artist.aliases_list') ?>:
            <div class="Box-body">
                <ul class=>
                    <?
                    $NonRedirectingAliases = array();
                    $DB->query("
		SELECT AliasID, Name, UserID, Redirect
		FROM artists_alias
		WHERE ArtistID = '$ArtistID'");
                    while (list($AliasID, $AliasName, $User, $Redirect) = $DB->next_record(MYSQLI_NUM, true)) {
                        if ($AliasName == $Name) {
                            $DefaultRedirectID = $AliasID;
                        }
                    ?>
                        <li>
                            <span data-tooltip="Alias ID"><?= $AliasID ?></span>. <span data-tooltip="Alias name"><?= $AliasName ?></span>
                            <? if ($User) { ?>
                                <a href="user.php?id=<?= $User ?>" data-tooltip="Alias creator" class="brackets"><?= t('server.artist.user') ?></a>
                            <?      }
                            if ($Redirect) { ?>
                                (<?= t('server.artist.writes_redirect_to') ?> <span data-tooltip="Target alias ID"><?= $Redirect ?></span>)
                            <?      } else {
                                $NonRedirectingAliases[$AliasID] = $AliasName;
                            }
                            ?>

                            <a href="artist.php?action=delete_alias&amp;aliasid=<?= $AliasID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= t('server.artist.delete_this_alias') ?>" class="brackets">X</a>
                        </li>
                    <?  }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="Form-row">
        <div class="Form-items">
            <div><?= t('server.artist.add_alias') ?></div>
            <?= t('server.artist.add_alias_note') ?>
        </div>
    </div>
    <form class="add_form" name="aliases" action="artist.php" method="post">
        <input type="hidden" name="action" value="add_alias" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
        <div class="Form-row">
            <div class="Form-inputs">
                <div>
                    <?= t('server.artist.name') ?>:
                    <input class="Input is-small" type="text" name="name" size="20" value="<?= $Name ?>" />
                </div>
                <div>
                    <?= t('server.artist.redirect_to') ?>:
                    <select class="Input" name="redirect">
                        <option class="Select-option" value="0"><?= t('server.artist.non_redirecting_alias') ?></option>
                        <? foreach ($NonRedirectingAliases as $AliasID => $AliasName) { ?>
                            <option class="Select-option" value="<?= $AliasID ?>" <?= $AliasID == $DefaultRedirectID ? " selected" : "" ?>><?= $AliasName ?></option>
                        <?  } ?>
                    </select>
                </div>
                <input class="Button" type="submit" value="<?= t('server.common.add') ?>" />
            </div>
        </div>
        <div class="Form-row">
        </div>
    </form>
</div>
<? View::show_footer() ?>