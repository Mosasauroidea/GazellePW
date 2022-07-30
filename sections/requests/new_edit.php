<?php

/*
 * Yeah, that's right, edit and new are the same place again.
 * It makes the page uglier to read but ultimately better as the alternative means
 * maintaining 2 copies of almost identical files.
 */


$NewRequest = $_GET['action'] === 'new';

$RequestTaxPercent = ($RequestTax * 100);

if (!$NewRequest) {
    $RequestID = $_GET['id'];
    if (!is_number($RequestID)) {
        error(404);
    }
}


if ($NewRequest && ($LoggedUser['BytesUploaded'] < 250 * 1024 * 1024 || !check_perms('site_submit_requests'))) {
    error(t('server.requests.you_do_not_have_enough_uploaded'));
}

if (!$NewRequest) {
    if (empty($ReturnEdit)) {

        $Request = Requests::get_request($RequestID);
        if ($Request === false) {
            error(404);
        }

        // Define these variables to simplify _GET['groupid'] requests later on
        $CategoryID = $Request['CategoryID'];
        $Title = $Request['Title'];
        $Year = $Request['Year'];
        $Image = $Request['Image'];
        $ReleaseType = $Request['ReleaseType'];
        $GroupID = $Request['GroupID'];
        $IMDBID = $Request['IMDBID'];
        $Subtitle = $Request['Subtitle'];
        $SourceTorrent = $Request['SourceTorrent'];
        $PurchasableAt = $Request['PurchasableAt'];


        $VoteArray = Requests::get_votes_array($RequestID);
        $VoteCount = count($VoteArray['Voters']);

        $IsFilled = !empty($Request['TorrentID']);
        $CategoryName = $Categories[$CategoryID - 1];

        $ProjectCanEdit = (check_perms('project_team') && !$IsFilled && ($CategoryID === '0'));
        $CanEdit = ((!$IsFilled && $LoggedUser['ID'] === $Request['UserID'] && $VoteCount < 2) || $ProjectCanEdit || check_perms('site_moderate_requests'));

        if (!$CanEdit) {
            error(403);
        }

        $ArtistForm = Requests::get_artists($RequestID);

        $CodecArray = array();
        if ($Request['CodecList'] == 'Any') {
            $CodecArray = array_keys($Codecs);
        } else {
            $CodecArray = array_keys(array_intersect($Codecs, explode('|', $Request['CodecList'])));
        }

        $ResolutionArray = array();
        if ($Request['ResolutionList'] == 'Any') {
            $ResolutionArray = array_keys($Resolutions);
        } else {
            foreach ($Resolutions as $Key => $Val) {
                if (strpos($Request['ResolutionList'], $Val) !== false) {
                    $ResolutionArray[] = $Key;
                }
            }
        }

        $ContainerArray = array();
        if ($Request['ContainerList'] == 'Any') {
            $ContainerArray = array_keys($Containers);
        } else {
            $ContainerTemp = explode('|', $Request['ContainerList']);
            foreach ($Containers as $Key => $Val) {
                if (in_array($Val, $ContainerTemp)) {
                    $ContainerArray[] = $Key;
                }
            }
        }

        $SourceArray = array();
        if ($Request['SourceList'] == 'Any') {
            $SourceArray = array_keys($Sources);
        } else {
            $SourceTemp = explode('|', $Request['SourceList']);
            foreach ($Sources as $Key => $Val) {
                if (in_array($Val, $SourceTemp)) {
                    $SourceArray[] = $Key;
                }
            }
        }
        $Tags = implode(', ', $Request['Tags']);
    }
}

if ($NewRequest && !empty($_GET['artistid']) && is_number($_GET['artistid'])) {
    $DB->query("
		SELECT Name
		FROM artists_group
		WHERE artistid = " . $_GET['artistid'] . "
		LIMIT 1");
    list($ArtistName) = $DB->next_record();
    $ArtistForm = array(
        1 => array(array('name' => trim($ArtistName))),
        2 => array(),
        3 => array()
    );
    // TODO by qwerty IMDBID autofill 兼容情况
} elseif ($NewRequest && !empty($_GET['groupid']) && is_number($_GET['groupid'])) {
    $ArtistForm = Artists::get_artist($_GET['groupid']);
    $DB->query("
		SELECT
            tg.ID,
			tg.Name,
			tg.Year,
			tg.SubName,
			tg.IMDBID,
			tg.ReleaseType,
			tg.WikiImage,
			GROUP_CONCAT(t.Name SEPARATOR ', ') as Tags,
			tg.CategoryID
		FROM torrents_group AS tg
			JOIN torrents_tags AS tt ON tt.GroupID = tg.ID
			JOIN tags AS t ON t.ID = tt.TagID
		WHERE tg.ID = " . $_GET['groupid']);
    if ($RequestInfo = $DB->next_record(MYSQLI_ASSOC)) {

        $GroupID = trim($_REQUEST['groupid']);
        $Title = $RequestInfo['Name'];
        $Year = $RequestInfo['Year'];
        $SubTitle = $RequestInfo['SubName'];
        $IMDBID = $RequestInfo['IMDBID'];
        $ReleaseType = $RequestInfo['ReleaseType'];
        $Tags = $RequestInfo['Tags'];
        $CategoryID = $RequestInfo['CategoryID'];
        $Image = $RequestInfo['WikiImage'];
        $Disabled = ' readonly';
        $DisabledFlag = true;
        $Subtitle = $SubName;
    }
}

View::show_header(($NewRequest ? t('server.requests.new_create') : t('server.requests.new_edit')), 'form_validate', 'PageRequestNewEdit
');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.requests.requests')  ?></h2>
    </div>
    <div class="BodyContent">
        <form class="Form FormRequestNew" action="" method="post" id="request_form" onsubmit="globalapp.requestCalculate();">
            <div>
                <? if (!$NewRequest) { ?>
                    <input type="hidden" name="requestid" value="<?= $RequestID ?>" />
                <?  } ?>
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="action" value="<?= ($NewRequest ? 'takenew' : 'takeedit') ?>" />
            </div>

            <table class="Form-rowList" variant="header">
                <tr class="Form-rowHeader">
                    <td class="Form-title"><?= $NewRequest ? t('server.requests.new_create') . (isset($GroupID) ? ' > ' . Torrents::group_name($RequestInfo) : '') : t('server.requests.new_edit')  ?></td>
                </tr>
                <tr class="Form-row">
                    <td colspan="2" class="center"><?= t('server.requests.new_rules') ?></td>
                </tr>
                <? if ($NewRequest || $CanEdit) { ?>
                    <tr class="Form-row">
                        <td class="Form-label">
                            <?= t('server.upload.type') ?>:
                        </td>
                        <td class="Form-items">
                            <div class="Form-inputs">
                                <select class="Input" id="categories" name="type" onchange="globalapp.requestCategories();">
                                    <? foreach (Misc::display_array($Categories) as $Cat) { ?>
                                        <option class="Select-option" value="<?= $Cat ?>" <?= (!empty($CategoryName) && ($CategoryName === $Cat) ? ' selected="selected"' : '') ?>><?= $Cat ?></option>
                                    <?      } ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="imdb_tr">
                        <td class="Form-label">IMDB:</td>
                        <td class="Form-items" id="imdbfield">
                            <div class="Form-inputs">
                                <input class="Input" type="text" id="imdb" name="imdb" size="45" placeholder="IMDB" value="<?= $IMDBID ?>" <?= $Disabled ?>>
                                <button class="Button" variant="primary" type="button" <?= $DisabledFlag ? 'disabled' : '' ?> onclick="globalapp.requestMovieAutofill(event)" id="imdb_button">
                                    <span><?= t('server.upload.movie_fill') ?></span>
                                    <span class="Loader"></span>
                                </button>
                                <input type="checkbox" name="no_imdb_link" id="no_imdb_link"><label for="no_imdb_link"><?= t('server.upload.no_imdb_link') ?></label>
                            </div>
                            <div class="imdb Form-errorMessage"><?= t('server.upload.imdb_empty_warning') ?></div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="artist_tr">
                        <td class="Form-label"><?= t('server.common.artist') ?>:</td>
                        <td class="Form-items" id="artistfields">
                            <?
                            if (!empty($ArtistForm)) {
                                $First = true;
                                $cnt = 0;
                                foreach ($ArtistForm[1] as $Artist) {
                            ?>
                                    <div class="Form-inputs">
                                        <input type="hidden" id="artist_id" name="artist_ids[]" value="<?= display_str($Artist['imdbid']) ?>" size="45" />
                                        <input class="Input" type="text" id="artist_<?= $cnt ?>" <?= $Disabled ?> name="artists[]" <? Users::has_autocomplete_enabled('other'); ?> size="45" value="<?= display_str($Artist['name']) ?>" />
                                        <input class="Input" type="text" id="artist_chinese" name="artists_chinese[]" size="25" value="<?= display_str($Artist['cname']) ?>" placeholder="<?= t('server.upload.chinese_name') ?>" <?
                                                                                                                                                                                                                                    Users::has_autocomplete_enabled('other'); ?> />
                                        <select class="Input" id="importance" name="importance[]">
                                            <option class="Select-option" value="1" <?= ($Importance == '1' ? ' selected="selected"' : '') ?>><?= t('server.upload.director') ?></option>
                                        </select>
                                        <? if ($First) { ?>
                                            <div>
                                                <a href="#" onclick="globalapp.requestAddArtistField(); return false;" class="brackets">+</a>
                                                <span> </span>
                                                <a href="#" onclick="globalapp.requestRemoveArtistField(); return false;" class="brackets">&minus;</a>
                                            </div>
                                        <? } ?>
                                    </div>
                                <?
                                    $cnt++;
                                }
                            } else {
                                ?>
                                <div class="Form-inputs">
                                    <input type="hidden" id="artist_id" name="artist_ids[]" size="45" />
                                    <input class="Input" type="text" id="artist" name="artists[]" <? Users::has_autocomplete_enabled('other'); ?> size="45" />
                                    <input class="Input" type="text" id="artist_chinese" name="artists_chinese[]" size="25" placeholder="<?= t('server.upload.chinese_name') ?>" <?
                                                                                                                                                                                    Users::has_autocomplete_enabled('other'); ?> />
                                    <select class="Input" id="importance" name="importance[]">
                                        <option class="Select-option" value="1"><?= t('server.upload.director') ?></option>
                                    </select>
                                    <div>
                                        <a href="#" onclick="globalapp.requestAddArtistField(); return false;" class="brackets">+</a>
                                        <a href="#" onclick="globalapp.requestRemoveArtistField(); return false;" class="brackets">&minus;</a>
                                    </div>
                                </div>
                            <?
                            }
                            ?>
    </div>
    </td>
    </tr>
    <tr class="Form-row">
        <td class="Form-label"><?= t('server.upload.movie_title') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input class="Input" type="text" id="title" name="title" <?= $Disabled ?> size="45" value="<?= (!empty($Title) ? $Title : '') ?>" />
            </div>
        </td>
    </tr>
    <tr class="Form-row">
        <td class="Form-label"><?= t('server.upload.chinese_title') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input class="Input" type="text" id="subtitle" name="subtitle" <?= $Disabled ?> size="45" value="<?= (!empty($Subtitle) ? $Subtitle : '') ?>" />
            </div>
        </td>
    </tr>
<?  } ?>
<tr class="Form-row" id="year_tr">
    <td class="Form-label"><?= t('server.requests.year') ?>:</td>
    <td class="Form-items">
        <div class="Form-inputs">
            <input class="Input" type="text" id="year" name="year" <?= $Disabled ?> size="5" value="<?= (!empty($Year) ? $Year : '') ?>" />
        </div>
    </td>
</tr>
<? if ($NewRequest || $CanEdit) { ?>
    <tr class="Form-row" id="image_tr">
        <td class="Form-label"><?= t('server.requests.image') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input class="Input" type="text" id="image" name="image" <?= $Disabled ?> size="45" value="<?= (!empty($Image) ? $Image : '') ?>" />
            </div>
        </td>
    </tr>
<?  } ?>
<tr class="Form-row">
    <td class="Form-label"><?= t('server.requests.tags') ?>:</td>
    <td class="Form-items">
        <div class="Form-inputs">
            <?
            $GenreTags = $Cache->get_value('genre_tags');
            if (!$GenreTags) {
                $DB->query('
			SELECT Name
			FROM tags
			WHERE TagType = \'genre\'
			ORDER BY Name');
                $GenreTags = $DB->collect('Name');
                $Cache->cache_value('genre_tags', $GenreTags, 3600 * 6);
            }
            ?>
            <select class="Input" id="genre_tags" name="genre_tags" onchange="globalapp.requestAddTag(); return false;">
                <option class="Select-option">---</option>
                <? foreach (Misc::display_array($GenreTags) as $Genre) { ?>
                    <option class="Select-option" value="<?= $Genre ?>"><?= $Genre ?></option>
                <?  } ?>
            </select>
            <input class="Input" type="text" <?= $Disabled ?> id="tags" name="tags" size="45" value="<?= (!empty($Tags) ? display_str($Tags) : '') ?>" <? Users::has_autocomplete_enabled('other'); ?> />
        </div>
        <div>
            <?= t('server.requests.tags_note') ?>
        </div>
    </td>
</tr>
<? if ($NewRequest || $CanEdit) { ?>
    <tr class="Form-row" id="releasetypes_tr">
        <td class="Form-label"><?= t('server.requests.release_list') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <select class="Input" id="releasetype" name="releasetype">
                    <?
                    foreach ($ReleaseTypes as $Key) {
                        //echo '<h1>'.$ReleaseType.'</h1>'; die();
                    ?> <option class="Select-option" value="<?= $Key ?>" <?= !empty($ReleaseType) ? ($Key == $ReleaseType ? ' selected="selected"' : ($DisabledFlag ? 'disabled' : '')) : '' ?>><?= t('server.torrents.release_types')[$Key] ?></option>
                    <?
                    }
                    ?>
                </select>
            </div>
        </td>
    </tr>
    <tr class="Form-row" id="sources_tr">
        <td class="Form-label"><?= t('server.requests.acceptable_sources') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input type="checkbox" name="all_sources" id="toggle_sources" onchange="globalapp.requestToggle('sources', <?= ($NewRequest ? 1 : 0) ?>);" <?= !empty($SourceArray) && (count($SourceArray) === count($Sources)) ? ' checked="checked"' : ''; ?> /><label for="toggle_sources"> <?= t('server.requests.all') ?></label>
                <? foreach ($Sources as $Key => $Val) { ?>
                    <input type="checkbox" name="sources[]" value="<?= $Key ?>" onchange="if (!this.checked) { $('#toggle_sources').raw().checked = false; }" id="source_<?= $Key ?>" <?= (!empty($SourceArray) && in_array($Key, $SourceArray) ? ' checked="checked"' : '') ?> /><label for="source_<?= $Key ?>"> <?= $Val ?></label>
                <?      } ?>
            </div>
        </td>
    </tr>
    <tr class="Form-row" id="codecs_tr">
        <td class="Form-label"><?= t('server.requests.acceptable_codecs') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input type="checkbox" name="all_codecs" id="toggle_codecs" onchange="globalapp.requestToggle('codecs', <?= ($NewRequest ? 1 : 0) ?>);" <?= (!empty($CodecArray) && (count($CodecArray) === count($Codecs)) ? ' checked="checked"' : '') ?> /><label for="toggle_codecs"> <?= t('server.requests.all') ?></label>
                <? foreach ($Codecs as $Key => $Val) { ?>
                    <input type="checkbox" name="codecs[]" value="<?= $Key ?>" id="codec_<?= $Key ?>" <?= (!empty($CodecArray) && in_array($Key, $COdecArray) ? ' checked="checked" ' : '') ?> onchange="if (!this.checked) { $('#toggle_codecs').raw().checked = false; }" /><label for="codec_<?= $Key ?>"> <?= $Val ?></label>
                <?      } ?>
            </div>
        </td>
    </tr>
    <tr class="Form-row" id="containers_tr">
        <td class="Form-label"><?= t('server.requests.acceptable_containers') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input type="checkbox" name="all_containers" id="toggle_containers" onchange="globalapp.requestToggle('containers', <?= ($NewRequest ? 1 : 0) ?>);" <?= (!empty($ContainerArray) && (count($ContainerArray) === count($Containers)) ? ' checked="checked"' : '') ?> /><label for="toggle_containers"> <?= t('server.requests.all') ?></label>
                <? foreach ($Containers as $Key => $Val) { ?>
                    <input type="checkbox" name="containers[]" value="<?= $Key ?>" id="container_<?= $Key ?>" <?= (!empty($ContainerArray) && in_array($Key, $ContainerArray) ? ' checked="checked" ' : '') ?> onchange="if (!this.checked) { $('#toggle_containers').raw().checked = false; }" /><label for="container_<?= $Key ?>"> <?= $Val ?></label>
                <?      } ?>
            </div>
        </td>
    </tr>
    <tr class="Form-row" id="resolutions_tr">
        <td class="Form-label"><?= t('server.requests.acceptable_resolutions') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input type="checkbox" name="all_resolutions" id="toggle_resolutions" onchange="globalapp.requestToggle('resolutions', <?= ($NewRequest ? 1 : 0) ?>);" <?= (!empty($ResolutionArray) && (count($ResolutionArray) === count($Resolutions)) ? ' checked="checked"' : '') ?> /><label for="toggle_resolutions"> <?= t('server.requests.all') ?></label>
                <? foreach ($Resolutions as $Key => $Val) { ?>
                    <input type="checkbox" name="resolutions[]" value="<?= $Key ?>" id="resolution_<?= $Key ?>" <?= (!empty($ResolutionArray) && in_array($Key, $ResolutionArray) ? ' checked="checked" ' : '') ?> onchange="if (!this.checked) { $('#toggle_resolutions').raw().checked = false; }" /><label for="resolution_<?= $Key ?>"> <?= $Val ?></label>
                <? } ?>
            </div>
        </td>
    </tr>
<?  } ?>
<tr class="Form-row">
    <td class="Form-label"><?= t('server.requests.source_torrent') ?>:</td>
    <td class="Form-items">
        <div class="Form-inputs">
            <input class="Input" type="text" name="source_torrent" value="<?= $SourceTorrent ?>" placeholder="<?= t('server.requests.source_torrent_placeholder') ?>">
        </div>
    </td>
</tr>
<tr class="Form-row">
    <td class="Form-label"><?= t('server.requests.purchasable_at') ?>:</td>
    <td class="Form-items">
        <div class="Form-inputs">
            <input class="Input" type="text" name="purchasable_at" value="<?= $PurchasableAt ?>" placeholder="<?= t('server.requests.purchasable_at_placeholder') ?>">
        </div>
    </td>
</tr>
<tr class="Form-row">
    <td class="Form-label"><?= t('server.requests.description') ?>:</td>
    <td class="Form-items">
        <div class="Form-inputs">
            <textarea class="Input" name="description" cols="70" rows="7"><?= (!empty($Request['Description']) ? $Request['Description'] : '') ?></textarea>
        </div>
        <div>
            <?= t('server.requests.description_note') ?>
        </div>
    </td>
</tr>
<? if (check_perms('site_moderate_requests')) { ?>
    <tr class="Form-row">
        <td class="Form-label"><?= t('server.requests.t_group') ?>:</td>
        <td class="Form-items">
            <div>
                <?= site_url() ?>torrents.php?id=<input class="Input" type="text" name="groupid" value="<?= $GroupID ?>" size="15" />
            </div>
            <div>
                <?= t('server.requests.t_group_note') ?>
            </div>
        </td>
    </tr>
<?  } elseif ($GroupID && ($CategoryID == 1)) { ?>
    <tr class="Form-row">
        <td class="Form-label"><?= t('server.requests.t_group') ?></td>
        <td class="Form-items">
            <div>
                <a href="torrents.php?id=<?= $GroupID ?>"><?= site_url() ?>torrents.php?id=<?= $GroupID ?></a>
            </div>
            <div>
                <?= t('server.requests.this_request') ?><?= ($NewRequest ? t('server.requests.will_be') : t('server.requests.is')) ?><?= t('server.requests.associated_with_the_above_torrent_group') ?>
                <? if (!$NewRequest) { ?>
                    <?= t('server.requests.if_this_is_incorrect_please') ?><a href="reports.php?action=report&amp;type=request&amp;id=<?= $RequestID ?>"><?= t('server.requests.report_this_request') ?></a><?= t('server.requests.so_that_staff_can_fix_it') ?>
                <?      }   ?>
                <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
            </div>
        </td>
    </tr>
<?  }
if ($NewRequest) { ?>
    <tr class="Form-row" id="voting">
        <td class="Form-label"><?= t('server.requests.bounty') ?>:</td>
        <td class="Form-items">
            <div class="Form-inputs">
                <input class="Input" type="text" id="amount_box" size="8" value="<?= (!empty($Bounty) ? $Bounty : '100') ?>" onchange="globalapp.requestCalculate();" />
                <select class="Input" id="unit" name="unit" onchange="globalapp.requestCalculate();">
                    <option class="Select-option" value="mb" <?= (!empty($_POST['unit']) && $_POST['unit'] === 'mb' ? ' selected="selected"' : '') ?>>MB</option>
                    <option class="Select-option" value="gb" <?= (!empty($_POST['unit']) && $_POST['unit'] === 'gb' ? ' selected="selected"' : '') ?>>GB</option>
                </select>
                <input class="Button" type="button" value="Preview" onclick="globalapp.requestCalculate();" />
            </div>
            <?= $RequestTax > 0 ? "<strong>{$RequestTaxPercent}% of this is deducted as tax by the system.</strong>" : '' ?>
        </td>
    </tr>
    <tr class="Form-row">
        <td class="Form-label"><?= t('server.requests.pst') ?>:</td>
        <td class="Form-items">
            <input type="hidden" id="amount" name="amount" value="<?= (!empty($Bounty) ? $Bounty : '100') ?>" />
            <input type="hidden" id="current_uploaded" value="<?= $LoggedUser['BytesUploaded'] ?>" />
            <input type="hidden" id="current_downloaded" value="<?= $LoggedUser['BytesDownloaded'] ?>" />
            <?= $RequestTax > 0 ? '<div>Bounty after tax: <strong><span id="bounty_after_tax">90.00 MB</span></strong></div>' : '' ?>
            <div>
                <?= t('server.requests.pst_body', ['Values' => [
                    "<strong><span id='new_bounty'>1GB</span></strong>"
                ]]) ?>
            </div>

            <div>
                <?= t('server.requests.uploaded') ?>: <span id="new_uploaded"><?= Format::get_size($LoggedUser['BytesUploaded']) ?></span>
            </div>
            <div>
                <?= t('server.requests.ratio') ?>: <span id="new_ratio"><?= Format::get_ratio_html($LoggedUser['BytesUploaded'], $LoggedUser['BytesDownloaded']) ?></span>
            </div>
        </td>
    </tr>
    <tr class="Form-row">
        <td class="Form-submit" colspan="2">
            <input class="Button" type="submit" id="button" value="Create request" />
        </td>
    </tr>
<?  } else { ?>
    <tr class="Form-row">
        <td class="Form-submit" colspan="2">
            <input class="Button" type="submit" id="button" value="Edit request" />
        </td>
    </tr>
<?  } ?>
</table>
</form>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        globalapp.requestCalculate();
    })
</script>
</div>
</div>
<?
View::show_footer();
?>