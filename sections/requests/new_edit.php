<?php

/*
 * Yeah, that's right, edit and new are the same place again.
 * It makes the page uglier to read but ultimately better as the alternative means
 * maintaining 2 copies of almost identical files.
 */


$NewRequest = $_GET['action'] === 'new';

$Type = $_GET['type'];
$RequestTaxPercent = ($RequestTax * 100);

if (!$NewRequest) {
    $RequestID = $_GET['id'];
    if (!is_number($RequestID)) {
        error(404);
    }
}
$GroupID = null;
if (!empty($_GET['groupid']) && is_number($_GET['groupid'])) {
    $GroupID = $_GET['groupid'];
}
$TorrentID = $_GET['torrentid'];


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
        $SubName = $Request['Subtitle'];
        $SourceTorrent = $Request['SourceTorrent'];
        $PurchasableAt = $Request['PurchasableAt'];
        $Description = $Request['Description'];


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
        $Tags = implode(', ', array_values(Tags::get_sub_name($Request['Tags'])));
    }
}

if ($NewRequest && $GroupID) {
    $AllArtist = Artists::get_artist($_GET['groupid']);
    $ArtistForm = [Artists::Director => $AllArtist[Artists::Director]];
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
    if ($Request = $DB->next_record(MYSQLI_ASSOC)) {

        $GroupID = trim($_REQUEST['groupid']);
        $Title = $Request['Name'];
        $Year = $Request['Year'];
        $SubName = $Request['SubName'];
        $IMDBID = $Request['IMDBID'];
        $ReleaseType = $Reques['ReleaseType'];
        $Tags = $Request['Tags'];
        $CategoryID = $Request['CategoryID'];
        $Image = $Request['WikiImage'];
        $Disabled = ' disabled';
        $DisabledFlag = true;
    }
}

$GenreTags = Tags::get_genre_tag();

View::show_header(($NewRequest ? t('server.requests.new_create') : t('server.requests.new_edit')), 'form_validate', 'PageRequestNewEdit
');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.requests.requests')  ?></div>
        <? if (isset($GroupID)) { ?>
            <div class="BodyHeader-subNav"><?= Torrents::group_name($Request)  ?></div>
        <? } ?>
    </div>
    <div class="BodyContent">
        <form class="Form-rowList Form FormRequest Box <?= $Type == '2' ? 'u-formNewRequest' : '' ?> <?= $Type == '1' ? 'u-formSeedRequest' : '' ?>" action="" method="post" id="request_form" onsubmit="globalapp.requestCalculate();" variant="header">
            <div>
                <? if (!$NewRequest) { ?>
                    <input type="hidden" name="requestid" value="<?= $RequestID ?>" />
                <?  } ?>
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="action" value="<?= ($NewRequest ? 'takenew' : 'takeedit') ?>" />
            </div>

            <table>
                <tr class="Form-rowHeader">
                    <td class="Form-title"><?= $NewRequest ? t('server.requests.new_create')  : t('server.requests.new_edit')  ?></td>
                </tr>
                <tr class="Form-row">
                    <td colspan="2" class="center"><?= t('server.requests.new_rules') ?></td>
                </tr>
                <? if ($NewRequest) { ?>
                    <tr class="Form-row">
                        <td class="Form-label">
                            <?= t('server.requests.request_type') ?>:
                        </td>
                        <td class="Form-items">
                            <div id="request_type" class="RadioGroup">
                                <div class="Radio">
                                    <input class="Input" type="radio" id="new_request" name="request_type" onchange="globalapp.requestNewTorrent();" value="1" <?= $Type == '1' ? 'checked' : '' ?>>
                                    <label class="Radio-label" for="new_request"><?= t('server.requests.new_torrent') ?></label>
                                </div>
                                <div class="Radio">
                                    <input class="Input" type="radio" id="seed_request" name="request_type" onchange="globalapp.requestSeedTorrent();" value="2" <?= $Type == '2' ? 'checked' : '' ?>>
                                    <label class="Radio-label" for="mixed_subtitles"><?= t('server.requests.seed_torrent') ?></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                <? } else {
                ?>
                    <tr class="Form-row">
                        <input type="hidden" id="new_request" name="request_type" value=1 />
                    </tr>

                <?
                }
                ?>
            </table>
            <? if ($NewRequest) { ?>
                <div class="TableContainer Form u-formRequestSeedCollapse">
                    <table>
                        <tr class="Form-row">
                            <td class="Form-label">
                                <?= t('server.torrents.permalink') ?>:
                            </td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" id="link" name="link" value="<?= $TorrentID ? CONFIG['SITE_URL'] . "/torrents.php?torrentid=$TorrentID" : '' ?>">
                            </td>
                        </tr>
                    </table>
                </div>
            <? } ?>
            <div class="TableContainer Form <?= $NewRequest ? 'u-formRequestNewCollapse' : '' ?>">
                <table>
                    <tr class="Form-row hidden">
                        <td class="Form-label">
                            <?= t('server.upload.type') ?>:
                        </td>
                        <td class="Form-items">
                            <div class="Form-inputs">
                                <select class="Input" id="categories" name="type" onchange="globalapp.requestCategories();">
                                    <? foreach (Misc::display_array($Categories) as $Cat) { ?>
                                        <option class="Select-option" value="<?= $Cat ?>" <?= (!empty($CategoryName) && ($CategoryName === $Cat) ? ' selected="selected"' : '') ?>><?= $Cat ?></option>
                                    <?
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="imdb_tr">
                        <td class="Form-label"><?= t('server.requests.link') ?>:</td>
                        <td class="Form-items" id="imdbfield">
                            <div class="Form-inputs">
                                <input class="Input is-small" type="text" id="imdb" name="imdb" size="45" placeholder="IMDb" value="<?= $IMDBID ?>" <?= $Disabled ?>>
                                <input class=" Input" type="text" id="group" name="group" size="45" placeholder="<?= t('server.requests.t_group') ?>" value="<?= $GroupID ? CONFIG['SITE_URL'] . '/torrents.php?id=' . $GroupID : '' ?>" <?= $Disabled ?>>
                                <? if ($NewRequest) { ?>
                                    <button <?= $Disabled ?> id="imdb_button" class="Button autofill" variant="primary" type="button" onclick="globalapp.requestMovieAutofill()">
                                        <span><?= t('server.upload.movie_fill') ?></span>
                                        <span class="Loader"></span>
                                    </button>
                                    <div <?= $Disabled ?> class="Checkbox">
                                        <input class="Input" type="checkbox" name="no_imdb_link" id="no_imdb_link" onchange="globalapp.requestNoImdbId()" <?= $Disabled ?>>
                                        <label class="Checkbox-label" data-tooltip="<?= t('server.requests.link_empty_warning') ?>" for="no_imdb_link">&nbsp;<?= t('server.requests.no_link') ?></label>
                                    </div>
                            </div>

                            <div class="u-formRequestNoImdbNote"><?= t('server.requests.auto_fill_note') ?></div>
                        <? } ?>
                        </td>
                    </tr>
                </table>

                <div class="TableContainer <?= $NewRequest ? 'u-formRequestCollapse' : '' ?>">
                    <table class="Form">
                        <tr class="Form-row" id="releasetypes_tr">
                            <td class="Form-label"><?= t('server.requests.release_list') ?>:</td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <select <?= $Disabled ?> class="Input" id="releasetype" name="releasetype">
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

                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.upload.movie_title') ?>:</td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <input class="Input" type="text" id="name" name="name" <?= $Disabled ?> size="45" value="<?= (!empty($Title) ? $Title : '') ?>" />
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.upload.sub_title') ?>:</td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <input class="Input" type="text" id="subname" name="subname" <?= $Disabled ?> size="45" value="<?= (!empty($SubName) ? $SubName : '') ?>" />
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row" id="year_tr">
                            <td class="Form-label"><?= t('server.requests.year') ?>:</td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <input class="Input" type="text" id="year" name="year" <?= $Disabled ?> size="5" value="<?= (!empty($Year) ? $Year : '') ?>" />
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row" id="artist_tr">
                            <td class="Form-label"><?= t('server.common.director') ?>:</td>
                            <td class="Form-items is-artist u-formRequestArtistList" id="artistfields">
                                <?
                                if (!empty($ArtistForm)) {
                                    $FirstArtist = true;
                                    foreach ($ArtistForm as $Importance => $Artists) {
                                        foreach ($Artists as $Artist) {
                                ?>
                                            <div class="Form-inputs is-artist">
                                                <input class="Input is-small" type="text" id="artist_id" name="artist_ids[]" value="<?= display_str($Artist['IMDBID']) ?>" size="45" <?= $Disabled ?> />
                                                <input class="Input is-small" type="text" id="artist" name="artists[]" size="45" value="<?= display_str($Artist['Name']) ?>" <? Users::has_autocomplete_enabled('other'); ?><?= $Disabled ?> />
                                                <input class="Input is-small" type="text" id="artist_sub" data-tooltip="<?= t('server.upload.sub_name') ?>" name="artists_sub[]" size="25" value="<?= display_str($Artist['SubName']) ?>" <? Users::has_autocomplete_enabled('other'); ?><?= $Disabled ?> />
                                                <input type="hidden" id="importance" name="importance[]" value=1 />
                                                <?
                                                if ($FirstArtist) {
                                                    if (!$Disabled) {
                                                ?>
                                                        <a id="add_artist" href="javascript:globalapp.requestAddArtistField(true)" class="brackets">+</a> <a id="remove_artist" href="javascript:globalapp.requestRemoveArtistField()" class="brackets">&minus;</a>
                                                <?
                                                    }
                                                    $FirstArtist = false;
                                                }
                                                ?>
                                            </div>
                                    <?
                                        }
                                    }
                                } else {
                                    ?>
                                    <div class="Form-inputs">
                                        <input class="Input is-small" type="text" id="artist_id" name="artist_ids[]" size="45" placeholder="<?= t('server.upload.movie_imdb') ?>" />
                                        <input class="Input is-small" type="text" id="artist" name="artists[]" size="45" <?
                                                                                                                            Users::has_autocomplete_enabled('other'); ?><?= $Disabled ?> placeholder="<?= t('server.upload.english_name') ?>" />
                                        <input class="Input is-small" type="text" id="artist_sub" name="artists_sub[]" size="25" placeholder="<?= t('server.upload.sub_name') ?>" <?
                                                                                                                                                                                    Users::has_autocomplete_enabled('other'); ?><?= $Disabled ?> />
                                        <input type="hidden" id="importance" name="importance[]" value=1 />
                                        <a id="add_artist" href="#" onclick="globalapp.requestAddArtistField(true); return false;" class="brackets add-artist">+</a>
                                        <a id="remove_artist" href="#" onclick="globalapp.requestRemoveArtistField(); return false;" class="brackets remove-artist">&minus;</a>
                                    </div>
                                <? } ?>
                                <div class="show-more hidden">
                                    <a href='#' onclick="globalapp.requestArtistsShowMore(); return false"><?= t('server.upload.show_more') ?></a>
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
                                    if ($GenreTags) {
                                    ?>
                                        <select <?= $Disabled ?> class="Input" id="genre_tags" name="genre_tags" onchange="globalapp.requestAddTag(); return false;">
                                            <option class="Select-option">---</option>
                                            <? foreach (Misc::display_array($GenreTags) as $Genre) { ?>
                                                <option class="Select-option" value="<?= $Genre ?>"><?= $Genre ?></option>
                                            <?  } ?>
                                        </select>
                                    <?
                                    }
                                    ?>
                                    <input class="Input" type="text" <?= $Disabled ?> id="tags" name="tags" size="45" value="<?= (!empty($Tags) ? display_str($Tags) : '') ?>" <? Users::has_autocomplete_enabled('other'); ?> />
                                </div>
                                <div>
                                    <?= t('server.requests.tags_note') ?>
                                </div>
                            </td>
                        </tr>
                        <?
                        if ($NewRequest || $CanEdit) {
                        ?>
                            <tr class="Form-row" id="sources_tr">
                                <td class="Form-label"><?= t('server.requests.acceptable_sources') ?>:</td>
                                <td class="Form-items">
                                    <div class="Form-inputs">
                                        <div class="Checkbox">
                                            <input type="checkbox" name="all_sources" id="toggle_sources" onchange="globalapp.allToggle('sources', <?= ($NewRequest ? 1 : 0) ?>);" <?= !empty($SourceArray) && (count($SourceArray) === count($Sources)) ? ' checked="checked"' : ''; ?> /><label for="toggle_sources"> <?= t('server.requests.all') ?></label>
                                        </div>
                                        <? foreach ($Sources as $Key => $Val) { ?>
                                            <div class="Checkbox"><input type="checkbox" name="sources[]" value="<?= $Key ?>" onchange="if (!this.checked) { $('#toggle_sources').raw().checked = false; }" id="source_<?= $Key ?>" <?= (!empty($SourceArray) && in_array($Key, $SourceArray) ? ' checked="checked"' : '') ?> /><label for="source_<?= $Key ?>"> <?= $Val ?></label></div>
                                        <?      } ?>
                                    </div>
                                </td>
                            </tr>
                            <tr class="Form-row" id="codecs_tr">
                                <td class="Form-label"><?= t('server.requests.acceptable_codecs') ?>:</td>
                                <td class="Form-items">
                                    <div class="Form-inputs">
                                        <div class="Checkbox">
                                            <input type="checkbox" name="all_codecs" id="toggle_codecs" onchange="globalapp.allToggle('codecs', <?= ($NewRequest ? 1 : 0) ?>);" <?= (!empty($CodecArray) && (count($CodecArray) === count($Codecs)) ? ' checked="checked"' : '') ?> /><label for="toggle_codecs"> <?= t('server.requests.all') ?></label>
                                        </div>
                                        <? foreach ($Codecs as $Key => $Val) { ?>
                                            <div class="Checkbox">
                                                <input type="checkbox" name="codecs[]" value="<?= $Key ?>" id="codec_<?= $Key ?>" <?= (!empty($CodecArray) && in_array($Key, $CodecArray) ? ' checked="checked" ' : '') ?> onchange="if (!this.checked) { $('#toggle_codecs').raw().checked = false; }" /><label for="codec_<?= $Key ?>"> <?= $Val ?></label>
                                            </div>
                                        <?      } ?>
                                    </div>
                                </td>
                            </tr>
                            <tr class="Form-row" id="containers_tr">
                                <td class="Form-label"><?= t('server.requests.acceptable_containers') ?>:</td>
                                <td class="Form-items">
                                    <div class="Form-inputs">
                                        <div class="Checkbox">
                                            <input type="checkbox" name="all_containers" id="toggle_containers" onchange="globalapp.allToggle('containers', <?= ($NewRequest ? 1 : 0) ?>);" <?= (!empty($ContainerArray) && (count($ContainerArray) === count($Containers)) ? ' checked="checked"' : '') ?> /><label for="toggle_containers"> <?= t('server.requests.all') ?></label>
                                        </div>
                                        <? foreach ($Containers as $Key => $Val) { ?>
                                            <div class="Checkbox">
                                                <input type="checkbox" name="containers[]" value="<?= $Key ?>" id="container_<?= $Key ?>" <?= (!empty($ContainerArray) && in_array($Key, $ContainerArray) ? ' checked="checked" ' : '') ?> onchange="if (!this.checked) { $('#toggle_containers').raw().checked = false; }" /><label for="container_<?= $Key ?>"> <?= $Val ?></label>
                                            </div>
                                        <?      } ?>
                                    </div>
                                </td>
                            </tr>
                            <tr class="Form-row" id="resolutions_tr">
                                <td class="Form-label"><?= t('server.requests.acceptable_resolutions') ?>:</td>
                                <td class="Form-items">
                                    <div class="Form-inputs">
                                        <div class="Checkbox">
                                            <input type="checkbox" name="all_resolutions" id="toggle_resolutions" onchange="globalapp.allToggle('resolutions', <?= ($NewRequest ? 1 : 0) ?>);" <?= (!empty($ResolutionArray) && (count($ResolutionArray) === count($Resolutions)) ? ' checked="checked"' : '') ?> /><label for="toggle_resolutions"> <?= t('server.requests.all') ?></label>
                                        </div>
                                        <? foreach ($Resolutions as $Key => $Val) { ?>
                                            <div class="Checkbox">
                                                <input type="checkbox" name="resolutions[]" value="<?= $Key ?>" id="resolution_<?= $Key ?>" <?= (!empty($ResolutionArray) && in_array($Key, $ResolutionArray) ? ' checked="checked" ' : '') ?> onchange="if (!this.checked) { $('#toggle_resolutions').raw().checked = false; }" /><label for="resolution_<?= $Key ?>"> <?= $Val ?></label>
                                            </div>
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
                                <? new TEXTAREA_PREVIEW("description", "description", (!empty($Request['Description']) ? display_str($Request['Description']) : '')) ?>
                                <div>
                                    <?= t('server.requests.description_note') ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="TableContainer <?= $NewRequestu ? 'u-formRequestCommonCollapse' : '' ?>">
                <table>
                    <?
                    if ($NewRequest) { ?>
                        <tr class="Form-row" id="voting">
                            <td class="Form-label"><?= t('server.requests.bounty') ?>:</td>
                            <td class="Form-inputs">
                                <input class="Input is-small" type="number" pattern="[0-9]" min="1" id="amount_box" size="8" value="<?= (!empty($Bounty) ? $Bounty : '1') ?>" oninput="globalapp.requestCalculate();" />
                                <select class="Input u-hidden" id="unit" name="unit" onchange="globalapp.requestCalculate();">
                                    <option class="Select-option" value="gb" <?= (!empty($_POST['unit']) && $_POST['unit'] === 'gb' ? ' selected="selected"' : '') ?>>GB</option>
                                </select>
                                <?= $RequestTax > 0 ? "<strong>{$RequestTaxPercent}% of this is deducted as tax by the system.</strong>" : '' ?>
                                <input type="hidden" id="amount" name="amount" value="<?= (!empty($Bounty) ? $Bounty : '100') ?>" />
                                <input type="hidden" id="current_uploaded" value="<?= $LoggedUser['BytesUploaded'] ?>" />
                                <input type="hidden" id="current_downloaded" value="<?= $LoggedUser['BytesDownloaded'] ?>" />
                                <?= $RequestTax > 0 ? '<div>Bounty after tax: <strong><span id="bounty_after_tax">90.00 MB</span></strong></div>' : '' ?>
                                <div>
                                    <?= t('server.requests.pst_body', ['Values' => [
                                        "<strong><span id='new_bounty'>1GB</span></strong>"
                                    ]]) ?>:
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
                                <button class="Button" type="submit" id="button" value="Create request" /><?= t('server.requests.new_create') ?></button>
                            </td>
                        </tr>
                    <?  } else { ?>
                        <tr class="Form-row">
                            <td class="Form-submit" colspan="2">
                                <button class="Button" type="submit" id="button" value="Edit request" /><?= t('server.common.edit') ?></button>
                            </td>
                        </tr>
                    <?  } ?>
                </table>
            </div>
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