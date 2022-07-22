<?

use Gazelle\Torrent\EditionInfo;
use Gazelle\Torrent\EditionType;

/********************************************************************************
 ************ Torrent form class *************** upload.php and torrents.php ****
 ********************************************************************************
 ** This class is used to create both the upload form, and the 'edit torrent'  **
 ** form. It is broken down into several functions - head(), foot(),           **
 ** movie_form() [movie] and simple_form() [everything else].                  **
 ** When it is called from the edit page, the forms are shortened quite a bit. **
 ********************************************************************************/
class TORRENT_FORM {
    var $UploadForm = '';
    var $Categories = array();
    var $Sources = array();
    var $Codecs = array();
    var $Containers = array();
    var $Resolutions = array();
    var $Makers = array();
    var $NewTorrent = false;
    var $Torrent = array();
    var $Error = false;
    var $TorrentID = false;
    var $Disabled = '';
    var $DisabledFlag = false;

    const TORRENT_INPUT_ACCEPT = ['application/x-bittorrent', '.torrent'];
    const JSON_INPUT_ACCEPT = ['application/json', '.json'];

    function __construct($Torrent = array(), $Error = false, $NewTorrent = true) {

        $this->NewTorrent = $NewTorrent;
        $this->Torrent = $Torrent;
        $this->Error = $Error;

        global $UploadForm, $Categories, $Sources, $Codecs, $Containers, $Resolutions, $Processings, $Makers, $TorrentID;

        $this->UploadForm = $UploadForm;
        $this->Categories = $Categories;
        $this->Sources = $Sources;
        $this->Codecs = $Codecs;
        $this->Containers = $Containers;
        $this->Resolutions = $Resolutions;
        $this->Processings = $Processings;
        $this->Makers = $Makers;
        $this->TorrentID = $TorrentID;

        if ($this->Torrent && isset($this->Torrent['GroupID'])) {
            $this->Disabled = ' readonly';
            $this->DisabledFlag = true;
        }
    }
    function genRemasterTags($RemasterTags, $SelectedTitle) {
        for ($i = 0; $i < count($RemasterTags); $i++) {
            if ($i) echo ', ';
            $remasterStyle = '';
            if ($SelectedTitle && strstr($SelectedTitle, $RemasterTags[$i])) {
                $remasterStyle = ' style="color:#ffbb33" ';
            }
            echo '<a ' . $remasterStyle . 'onclick="remasterTags(this, \'' . $RemasterTags[$i] . '\')" href="javascript:void(0)">' . EditionInfo::text($RemasterTags[$i]) . '</a>';
        }
    }
    function group_name() {
        $Data = ['Name' => $this->Torrent['Name'], 'SubName' => $this->Torrent['SubName'], 'ID' => $this->Torrent['GroupID'], 'Year' => $this->Torrent['Year']];
        return Torrents::group_name($Data, true);
    }

    function head() {
        $AnnounceURL =  CONFIG['ANNOUNCE_URL'];
?>

        <div class="Form">
            <? if ($this->NewTorrent) { ?>
                <p style="text-align: center; margin-bottom: 1px !important;">
                    <?= Lang::get('upload.personal_announce') ?>:
                    <br />
                    <a onclick="return false" href="<?= $AnnounceURL . '/' . G::$LoggedUser['torrent_pass'] . '/announce' ?>"><?= Lang::get('upload.personal_announce_note') ?></a>
                </p>
            <?      }
            if ($this->Error) {
                echo "\t" . '<p style="text-align: center;" class="u-colorWarning">' . $this->Error . "</p>\n";
            }
            ?>

            <form class="Form-rowList FormUpload FormValidation Box <?= ($this->Error || ($this->Torrent && isset($this->Torrent['GroupID']))) ? "u-formUploadAutoFilled" : "" ?>" name="torrent" action="" enctype="multipart/form-data" method="post" id="upload_table">
                <div>
                    <input type="hidden" name="submit" value="true" />
                    <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
                    <? if (!$this->NewTorrent) { ?>
                        <input type="hidden" name="action" value="takeedit" />
                        <input type="hidden" name="torrentid" value="<?= display_str($this->TorrentID) ?>" />
                        <input type="hidden" name="type" value="<?= display_str($this->Torrent['CategoryID']) ?>" />
                        <?
                    } else {
                        if ($this->Torrent && $this->Torrent['GroupID']) {
                        ?>
                            <input type="hidden" name="groupid" value="<?= display_str($this->Torrent['GroupID']) ?>" />
                            <input type="hidden" name="type" value="<?= in_array($this->UploadForm, $this->Categories) ?>" />
                        <?
                        }
                        if ($this->Torrent && isset($this->Torrent['RequestID'])) {
                        ?>
                            <input type="hidden" name="requestid" value="<?= display_str($this->Torrent['RequestID']) ?>" />
                    <?
                        }
                    }
                    ?>
                </div>
                <? if ($this->NewTorrent) { ?>
                    <table class="Form-rowList">
                        <? if ($this->Torrent['GroupID']) { ?>
                            <tr class="Form-rowHeader">
                                <td class="Form-title"><?= Lang::get('torrents.add_format') ?> &gt; <?= $this->group_name($this->Torrent, true) ?></td>
                            </tr>
                        <? } ?>
                        <tr class="Form-row is-file">
                            <td class="Form-label">
                                <?= Lang::get('upload.torrent_file') ?><span class="u-colorWarning">*</span>:
                            </td>
                            <td class="Form-items Form-errorContainer">
                                <div class="Form-inputs">
                                    <input type="file" id="file" name="file_input" size="50" accept="<?= implode(',', self::TORRENT_INPUT_ACCEPT); ?>" />
                                </div>
                            </td>
                        </tr>

                        <tr class="Form-row hidden">
                            <td class="Form-label"><?= Lang::get('upload.type') ?>:</td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <select class="Input" id="categories" name="type" onchange="globalapp.uploadCategories()" <?= $this->Disabled ?>>
                                        <?
                                        foreach (Misc::display_array($this->Categories) as $Index => $Cat) {
                                            if ($Cat == "Applications") {
                                                if (!check_perms('users_mod')) {
                                                    continue;
                                                }
                                            }
                                            echo "\t\t\t\t\t\t<option value=\"$Index\"";
                                            if ($Cat == $this->Torrent['CategoryName']) {
                                                echo ' selected="selected"';
                                            }
                                            echo ">$Cat</option>\n";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    </table>
                <?      }/*if*/ ?>
                <div id="dynamic_form">
                <?
            } // function head


            function foot() {
                $Torrent = $this->Torrent;
                ?>
                </div>
                <div class="TableContainer u-formUploadCollapse buttons" id="set_free_torrent">
                    <table class="Table layout border slice" cellpadding="3" cellspacing="1" border="0" width="100%">
                        <?
                        if (!$this->NewTorrent) {
                            if (check_perms('torrents_freeleech')) {
                        ?>
                                <?
                                if (Torrents::global_freeleech()) {
                                ?>
                                    <tr class="Form-row">
                                        <td colspan="2">
                                            <i><strong class="important"><?= Lang::get('torrents.global_freeleech_text') ?></strong></i>
                                        </td>
                                    </tr>
                                <?
                                }
                                ?>
                                <tr class="Form-row" id="freetorrent">
                                    <td class="Form-label"><?= Lang::get('upload.freeleech') ?>:</td>
                                    <td class="Form-items">
                                        <div class="Form-inputs">
                                            <select class="Input" name="freeleech">
                                                <?
                                                $FL = Torrents::freeleech_option();
                                                foreach ($FL as $Key => $Name) {
                                                ?>
                                                    <option class="Select-option" value="<?= $Key ?>" <?= ($Key == $Torrent['FreeTorrent'] ? ' selected="selected"' : '') ?>>
                                                        <?= $Name ?></option>
                                                <?              } ?>
                                            </select>
                                            <script>
                                                $(document).ready(() => {
                                                    $("#limit-time").click(() => {
                                                        if ($("#limit-time")[0].checked) {
                                                            $("#input-free-date,#input-free-time").show()
                                                            if (<?= $Torrent['FreeEndTime'] ? "false" : "true" ?>) {
                                                                const d = new Date()
                                                                $("#input-free-date")[0].value = d.getFullYear() + "-" + ("0" + (d.getMonth() +
                                                                    1)).substr(-2) + "-" + ("0" + d.getDate()).substr(-2)
                                                                $("#input-free-time")[0].value = ("0" + d.getHours()).substr(-2) + ":" + ("0" + d
                                                                    .getMinutes()).substr(-2)
                                                            }

                                                        } else {
                                                            $("#input-free-date,#input-free-time").hide()
                                                        }
                                                    })
                                                })
                                            </script>
                                            <div class="Checkbox">
                                                <input class="Input" type="checkbox" id="limit-time" name="limit-time" <?= $Torrent['FreeEndTime'] ? " checked=\"checked\"" : "" ?> />
                                                <label class="Checkbox-label" for="limit-time" style="display: inline;">定时</label>
                                            </div>
                                            <input class="Input" type="date" id="input-free-date" name="free-date" <?= $Torrent['FreeEndTime'] ? "value=\"" . substr($Torrent['FreeEndTime'], 0, 10) . "\"" : "style=\"display:none;\"" ?> /><input id="input-free-time" name="free-time" type="time" <?= $Torrent['FreeEndTime'] ? "value=\"" . substr($Torrent['FreeEndTime'], 11, 5) . "\"" : "style=\"display:none;\"" ?> />
                                            <?= Lang::get('upload.because') ?>
                                            <select class="Input" name="freeleechtype">
                                                <?
                                                $FL = array("N/A", "Staff Pick", "Perma-FL");
                                                foreach ($FL as $Key => $Name) {
                                                ?>
                                                    <option class="Select-option" value="<?= $Key ?>" <?= ($Key == $Torrent['FreeLeechType'] ? ' selected="selected"' : '') ?>><?= $Name ?></option>
                                                <?              } ?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                        <?
                            }
                        }
                        ?>
                        <tr class="Form-row is-section">
                            <td class="FormSubmitCenter" colspan="2">
                                <?
                                if ($this->NewTorrent) {
                                ?>
                                    <br />
                                    <p>
                                        <?= Lang::get('upload.assurance') ?>
                                    </p>
                                    <? if ($this->NewTorrent) { ?>
                                        <?= Lang::get('upload.assurance_note') ?>
                                <?      }
                                }
                                ?>
                                <button class="Button" variant="primary" type="submit" id="post">
                                    <span class="text">
                                        <? if ($this->NewTorrent) {
                                            echo Lang::get('torrents.upload_torrent');
                                        } else {
                                            echo Lang::get('torrents.browser_edit_torrent');
                                        } ?>
                                    </span>
                                    <span class="Loader"></span>
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    <?
            } //function foot

            function movie_form($GenreTags) {
                $QueryID = G::$DB->get_query_id();
                $Torrent = $this->Torrent;
                $IsRemaster = true;
                $NoSub = isset($Torrent['NoSub']) ? $Torrent['NoSub'] : null;
                $HardSub = isset($Torrent['HardSub']) ? $Torrent['HardSub'] : null;
                $BadFolders = isset($Torrent['BadFolders']) ? $Torrent['BadFolders'] : null;
                $CustomTrumpable = isset($Torrent['CustomTrumpable']) ? $Torrent['CustomTrumpable'] : null;
                $RemasterTitle = isset($Torrent['RemasterTitle']) ? $Torrent['RemasterTitle'] : null;
                $RemasterYear = isset($Torrent['RemasterYear']) ? $Torrent['RemasterYear'] : null;
                $RemasterCustomTitle = isset($Torrent['RemasterCustomTitle']) ? $Torrent['RemasterCustomTitle'] : null;
                $Scene = isset($Torrent['Scene']) ? $Torrent['Scene'] : null;
                $TorrentDescription = isset($Torrent['TorrentDescription']) ? $Torrent['TorrentDescription'] : null;
                $TorrentCodec = isset($Torrent['Codec']) ? $Torrent['Codec'] : null;
                $TorrentSource = isset($Torrent['Source']) ? $Torrent['Source'] : null;
                $TorrentContainer = isset($Torrent['Container']) ? $Torrent['Container'] : null;
                $TorrentResolution = isset($Torrent['Resolution']) ? $Torrent['Resolution'] : null;
                $TorrentProcessing = isset($Torrent['Processing']) ? $Torrent['Processing'] : null;
                $Subtitles = isset($Torrent['Subtitles']) ? $Torrent['Subtitles'] : null;
                $Buy = isset($Torrent['Buy']) ? $Torrent['Buy'] : null;
                $Diy = isset($Torrent['Diy']) ? $Torrent['Diy'] : null;
                $Jinzhuan = isset($Torrent['Jinzhuan']) ? $Torrent['Jinzhuan'] : null;
                $IMDBID = isset($Torrent['IMDBID']) ? $Torrent['IMDBID'] : null;
                $SpecialSub = isset($Torrent['SpecialSub']) ? $Torrent['SpecialSub'] : null;
                $ChineseDubbed = isset($Torrent['ChineseDubbed']) ? $Torrent['ChineseDubbed'] : null;
                $MediaInfos = isset($Torrent['MediaInfo']) ? json_decode($Torrent['MediaInfo']) : null;
                $SubtitleType = isset($Torrent['SubtitleType']) ? $Torrent['SubtitleType'] : null;
                $Note = isset($Torrent['Note']) ? $Torrent['Note'] : null;
                global $ReleaseTypes;
    ?>
        <table cellpadding="3" cellspacing="1" border="0" class="layout border<? if ($this->NewTorrent) {
                                                                                    echo ' slice';
                                                                                } ?>" width="100%">
            <? if ($this->NewTorrent) { ?>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('upload.movie_imdb') ?><span class="u-colorWarning">*</span>:</td>
                    <td class="Form-items Form-errorContainer">
                        <div class="Form-inputs">
                            <input class="Input" type="text" id="imdb" name="imdb" size="45" placeholder="IMDB" <?= $this->Disabled ?> value=<?= $IMDBID ?>>
                            <button class='Button autofill' variant="primary" id="imdb_button" onclick="globalapp.uploadMovieAutofill()" <?= $this->Disabled ? "disabled" : '' ?> type='button'>
                                <span class="text"><?= Lang::get('upload.movie_fill') ?></span>
                                <span class="Loader"></span>
                            </button>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="no_imdb_link" id="no_imdb_link" onchange="globalapp.uploadNoImdbId()" <?= $this->Disabled ? "disabled" : '' ?>>
                                <label class="Checkbox-label" for="no_imdb_link">&nbsp;<?= Lang::get('upload.no_imdb_link') ?></label>
                            </div>
                        </div>
                        <div class="u-formUploadNoImdbNote"><?= Lang::get('upload.imdb_empty_warning') ?></div>
                        <div class="u-formUploadNoImdbNote hidden u-colorWarning"><?= Lang::get('upload.no_imdb_note') ?></div>
                        <div class="imdb Form-errorMessage"></div>
                    </td>
                </tr>
        </table>
        <div class="TableContainer u-formUploadCollapse">
            <table class="Form <?= $this->NewTorrent ?: 'slice' ?>">
                <tr class="Form-row is-section" id="releasetype_tr">
                    <td class="Form-label">
                        <span id="movie_type"><?= Lang::get('upload.movie_type') ?><span class="u-colorWarning">*</span>:</span>
                    </td>
                    <td class="Form-items Form-errorContainer">
                        <div class="Form-inputs">
                            <select class="Input" id="releasetype" name="releasetype" <?= $this->Disabled ?>>
                                <option value=''>---</option>
                                <? foreach ($ReleaseTypes as $Key) {
                                    echo "\t\t\t\t\t\t<option value=\"$Key\"";
                                    if ($Key == $Torrent['ReleaseType']) {
                                        echo ' selected="selected"';
                                    } else if ($this->DisabledFlag) {
                                        echo "disabled";
                                    }
                                    echo ">" . Lang::get('torrents.release_types')[$Key] . "</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row" id="title_tr">
                    <td class="Form-label"><?= Lang::get('upload.movie_title') ?><span class="u-colorWarning">*</span>:</td>
                    <td class="Form-items Form-errorContainer">
                        <div class="Form-inputs">
                            <input class="Input" type="text" id="name" name="name" size="45" value="<?= display_str($Torrent['Name']) ?>" <?= $this->Disabled ?> />
                            <strong class="how_to_toggle_container">[<a href="javascript:void(0);" onclick="$('#title_how_to_blockquote').new_toggle();"><strong class="how_to_toggle"><?= Lang::get('upload.title_how_to_toggle') ?></strong></a>]</strong>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row" id="aliases_tr">
                    <td class="Form-label"><?= Lang::get('upload.movie_aliases') ?>:</td>
                    <td class="Form-items">
                        <div class="Form-inputs">
                            <input class="Input" type="text" id="subname" name="subname" size="45" value="<?= display_str($Torrent['SubName']) ?>" <?= $this->Disabled ?> />
                        </div>
                        <div class="FormUpload-explain" class="x" id="title_how_to_blockquote" style="display: none">
                            <?= Lang::get('upload.title_how_to_blockquote') ?>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row" id="year_tr">
                    <td class="Form-label">
                        <span id="year_label_not_remaster" <? if ($IsRemaster) {
                                                                echo ' class="hidden"';
                                                            }
                                                            ?>><?= Lang::get('upload.year') ?>:</span>
                        <span id="year_label_remaster" <? if (!$IsRemaster) {
                                                            echo ' class="hidden"';
                                                        }
                                                        ?>><?= Lang::get('upload.year_remaster') ?><span class="u-colorWarning">*</span>:</span>
                    </td>

                    <td class="Form-items Form-errorContainer">
                        <p id="yearwarning" class="hidden"><?= Lang::get('upload.year_remaster_title') ?></p>
                        <div class="Form-inputs">
                            <input class="Input" type="text" id="year" name="year" size="5" value="<?= display_str($Torrent['Year']) ?>" <?= $this->Disabled ?> />
                        </div>
                    </td>
                </tr>
                <tr class="Form-row" id="artist_tr">
                    <td class="Form-label"><?= Lang::get('global.artist') ?><span class="u-colorWarning">*</span>:</td>
                    <td class="Form-items is-artist u-formUploadArtistList" id="artistfields">
                        <p id="vawarning" class="hidden"><?= Lang::get('upload.artist_note') ?></p>
                        <?
                        if (!empty($Torrent['Artists'])) {
                            $FirstArtist = true;
                            foreach ($Torrent['Artists'] as $Importance => $Artists) {
                                foreach ($Artists as $Artist) {
                        ?>
                                    <div class="Form-inputs">
                                        <input type="hidden" id="artist_id" name="artist_ids[]" value="<?= display_str($Artist['imdbid']) ?>" size="45" />
                                        <input class="Input" type="text" id="artist" name="artists[]" size="45" value="<?= display_str($Artist['name']) ?>" <? Users::has_autocomplete_enabled('other'); ?><?= $this->Disabled ?> />
                                        <input class="Input is-small" type="text" id="artist_chinese" data-tooltip="<?= Lang::get('upload.chinese_name') ?>" name="artists_chinese[]" size="25" value="<?= display_str($Artist['name']) ?>" <? Users::has_autocomplete_enabled('other'); ?><?= $this->Disabled ?> />
                                        <select class="Input" id="importance" name="importance[]" <?= $this->Disabled ?>>
                                            <option class="Select-option" value="1" <?= ($Importance == '1' ? ' selected="selected"' : ($this->DisabledFlag ? 'disabled' : '')) ?>>
                                                <?= Lang::get('upload.director') ?></option>
                                            <option class="Select-option" value="2" <?= ($Importance == '2' ? ' selected="selected"' : ($this->DisabledFlag ? 'disabled' : '')) ?>>
                                                <?= Lang::get('upload.writer') ?></option>
                                            <option class="Select-option" value="3" <?= ($Importance == '3' ? ' selected="selected"' : ($this->DisabledFlag ? 'disabled' : '')) ?>>
                                                <?= Lang::get('upload.movie_producer') ?></option>
                                            <option class="Select-option" value="4" <?= ($Importance == '4' ? ' selected="selected"' : ($this->DisabledFlag ? 'disabled' : '')) ?>>
                                                <?= Lang::get('upload.composer') ?></option>
                                            <option class="Select-option" value="5" <?= ($Importance == '5' ? ' selected="selected"' : ($this->DisabledFlag ? 'disabled' : '')) ?>>
                                                <?= Lang::get('upload.cinematographer') ?></option>
                                            <option class="Select-option" value="6" <?= ($Importance == '6' ? ' selected="selected"' : ($this->DisabledFlag ? 'disabled' : '')) ?>>
                                                <?= Lang::get('upload.actor') ?></option>
                                        </select>
                                        <?
                                        if ($FirstArtist) {
                                            if (!$this->DisabledFlag) {
                                        ?>
                                                <a href="javascript:globalapp.uploadAddArtistField(true)" class="brackets">+</a> <a href="javascript:globalapp.globalapp.uploadRemoveArtistField()" class="brackets">&minus;</a>
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
                                <input type="hidden" id="artist_id" name="artist_ids[]" size="45" />
                                <input class="Input" type="text" id="artist" name="artists[]" size="45" <?
                                                                                                        Users::has_autocomplete_enabled('other'); ?><?= $this->Disabled ?> />
                                <input class="Input is-small" type="text" id="artist_chinese" name="artists_chinese[]" size="25" placeholder="<?= Lang::get('upload.chinese_name') ?>" <?
                                                                                                                                                                                        Users::has_autocomplete_enabled('other'); ?><?= $this->Disabled ?> />
                                <select class="Input" id="importance" name="importance[]" <?= $this->Disabled ?>>
                                    <option class="Select-option" value="1"><?= Lang::get('upload.director') ?></option>
                                    <option class="Select-option" value="2"><?= Lang::get('upload.writer') ?></option>
                                    <option class="Select-option" value="3"><?= Lang::get('upload.movie_producer') ?></option>
                                    <option class="Select-option" value="4"><?= Lang::get('upload.composer') ?></option>
                                    <option class="Select-option" value="5"><?= Lang::get('upload.cinematographer') ?></option>
                                    <option class="Select-option" value="6"><?= Lang::get('upload.actor') ?></option>
                                </select>
                                <a href="#" onclick="globalapp.uploadAddArtistField(true); return false;" class="brackets add-artist">+</a>
                                <a href="#" onclick="globalapp.uploadRemoveArtistField(); return false;" class="brackets remove-artist">&minus;</a>
                            </div>
                        <? } ?>
                        <div class="show-more" style="display: none;">
                            <a href='#' onclick="globalapp.uploadArtistsShowMore(); return false"><?= Lang::get('upload.show_more') ?></a>
                        </div>
                    </td>
                </tr>

                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('upload.movie_cover') ?><span class="u-colorWarning">*</span>:</td>
                    <td class="Form-items Form-errorContainer">
                        <div class="Form-inputs">
                            <input class="Input" type="text" ondrop="globalapp.imgDrop(event)" ondragover="globalapp.imgAllowDrop(event)" id="image" name="image" size="60" value="<?= display_str($Torrent['Image']) ?>" <?= $this->Disabled ?> />
                            <input class="Button" type="button" onclick="globalapp.imgUpload()" <?= $this->Disabled ? "disabled" : '' ?> value="<?= Lang::get('upload.upload_img') ?>">
                            <span id="imgUploadPer"></span>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('upload.trailer_link') ?>:</td>
                    <td class="Form-items">
                        <div class="Form-inputs">
                            <input class="Input" type="text" id="trailer_link" name="trailer_link" size="60" <?= $this->Disabled ?> />
                        </div>
                    </td>
                </tr>

                <?
                    if ($this->NewTorrent) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= Lang::get('upload.tags') ?><span class="u-colorWarning">*</span>:</td>
                        <td class="Form-items Form-errorContainer">
                            <div class="Form-inputs">
                                <? if ($GenreTags) { ?>
                                    <select class="Input" id="genre_tags" name="genre_tags" onchange="globalapp.uploadAddTag(); return false;" <?= $this->Disabled ?>>
                                        <? foreach (Misc::display_array($GenreTags) as $Genre) { ?>
                                            <option class="Select-option" value="<?= $Genre ?>"><?= $Genre ?></option>
                                        <? } ?>
                                    </select>
                                <? } ?>
                                <input class="Input" type="text" id="tags" name="tags" size="40" value="<?= display_str($Torrent['TagList']) ?>" <?
                                                                                                                                                    Users::has_autocomplete_enabled('other'); ?><?= $this->Disabled ?> />
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= Lang::get('upload.chinese_movie_synopsis') ?><span class="u-colorWarning">*</span>:</td>
                        <td class="Form-items Form-errorContainer">
                            <? new TEXTAREA_PREVIEW('desc', 'desc', display_str($Torrent['GroupDescription']), 60, 8, false, false, false, array($this->Disabled)); ?>
                            <h7><?= Lang::get('upload.chinese_movie_synopsis_note') ?></h7>
                        </td>
                    </tr>
                <? } ?>
            <? } ?>
            <? if (!$this->NewTorrent) { ?>
                <tr class="Form-rowHeader" id="edit_torrent">
                    <td class="Form-title">
                        <?= Lang::get('torrents.browser_edit_torrent') ?>
                    </td>
                </tr>
            <? } ?>
            <tr class="Form-row is-section is-text">
                <td class="Form-label">
                    <?= Lang::get('upload.movie_scene') ?>:
                </td>
                <td class="Form-items">
                    <div class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" id="scene" name="scene" <?= empty($Scene) ?: 'checked="checked"' ?> />
                            <label class="Checkbox-label" for="scene"><?= Lang::get('upload.movie_scene_label') ?></label>
                        </div>
                    </div>
                    <p class="upload_form_note">
                        <?= Lang::get('upload.movie_scene_note') ?>
                    </p>
                </td>
            </tr>
            <tr class="Form-row is-text">
                <td class="Form-label">
                    <?= Lang::get('upload.not_main_movie') ?>:
                </td>
                <td class="Form-items">
                    <div class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" id="not_main_movie" name="not_main_movie" <?= $Torrent['NotMainMovie'] ? "checked" : "" ?> <?= $this->Disabled ?> />
                            <label class="Checkbox-label" for="not_main_movie"><?= Lang::get('upload.not_main_movie_label') ?></label>
                        </div>
                    </div>
                    <p class="upload_form_note">
                        <?= Lang::get('upload.not_main_movie_note') ?>
                    </p>
                </td>
            </tr>
            <tr class="Form-row is-text" id="movie_edition_information_tr">
                <td class="Form-label">
                    <?= Lang::get('upload.movie_edition_information') ?>:
                </td>
                <td class="Form-items Form-errorContainer">
                    <div class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" onclick="$('#movie_edition_information_container').new_toggle()" id="movie_edition_information" name="movie_edition_information" <?= $RemasterTitle || $RemasterCustomTitle || $RemasterYear ? "checked " : "" ?>>
                            <label class="Checkbox-label" for="movie_edition_information"><?= Lang::get('upload.movie_edition_information_label') ?></label>
                        </div>
                    </div>
                    <div>
                        <?= Lang::get('upload.movie_edition_information_examples') ?>
                        <input type="hidden" id="remaster_title_hide" name="remaster_title" value="<?= display_str($RemasterTitle) ?>" />
                        <div id="movie_edition_information_container" style="display: none">
                            <div>
                                <?= Lang::get('upload.movie_information') ?>: <input class="Input" type="text" name="remaster_title_show" readonly id="remaster_title_show" size="80" value="<?= $RemasterTitle ? display_str(Torrents::display_edition_info($RemasterTitle)) : '' ?>" />
                            </div>
                            <div id="movie_remaster_tags">
                                <div>
                                    <?= Lang::get('editioninfo.collections') ?>:
                                    <? $this->genRemasterTags(EditionInfo::allEditionKey(EditionType::Collection), $RemasterTitle); ?>
                                </div>
                                <div>
                                    <?= Lang::get('editioninfo.editions') ?>:
                                    <? $this->genRemasterTags(EditionInfo::allEditionKey(EditionType::Edition), $RemasterTitle); ?>
                                </div>
                                <div>
                                    <?= Lang::get('editioninfo.features') ?>:
                                    <? $this->genRemasterTags(EditionInfo::allEditionKey(EditionType::Feature), $RemasterTitle); ?>
                                </div>
                            </div>
                            <div class="items">
                                <div class="item">
                                    <input class="Button" id="other-button" onclick="$('#other-input').new_toggle()" type="button" value="<?= Lang::get('upload.other') ?>">
                                    <input class="Button" id="year-button" onclick="$('#year-input').new_toggle()" type="button" value="<?= Lang::get('upload.year') ?>">
                                </div>
                                <div class="item" id="other-input" style="display: none;">
                                    <?= Lang::get('upload.other') ?>: <input class="Input" type="text" value="<?= $RemasterCustomTitle ?>" name="remaster_custom_title">
                                </div>
                                <div class="item" id="year-input" style="display: none;">
                                    <?= Lang::get('upload.year') ?>: <input class="Input" type="number" value="<?= $RemasterYear ?>" name="remaster_year">
                                </div>
                            </div>
                        </div>
                        <? if ($RemasterTitle || $RemasterCustomTitle || $RemasterYear) { ?>
                            <script>
                                $('#movie_edition_information_container').new_toggle();
                            </script>
                        <? } ?>
                        <? if ($RemasterCustomTitle) { ?>
                            <script>
                                $('#other-input').new_toggle();
                            </script>
                        <? } ?>
                        <? if ($RemasterYear) { ?>
                            <script>
                                $('#year-input').new_toggle();
                            </script>
                        <? } ?>
                    </div>
                </td>
            </tr>
            <tr class="Form-row is-section">
                <td class="Form-label"></td>
                <td class="Form-items">
                    <div>
                        <strong class="how_to_toggle_container">[<a href="javascript:void(0);" onclick="$('#torrent_info_how_to_blockquote').new_toggle();"><strong class="how_to_toggle"><?= Lang::get('upload.torrent_info_how_to_toggle') ?></strong></a>]</strong>
                    </div>
                    <div class="FormUpload-explain" id="torrent_info_how_to_blockquote" style="display: none">
                        <?= Lang::get('upload.torrent_info_how_to_blockquote') ?>
                    </div>
                </td>
            </tr>
            <tr class="Form-row is-specification">
                <td class="Form-label"><?= Lang::get('upload.movie_source') ?><span class="u-colorWarning">*</span>:</td>
                <td class="Form-items Form-errorContainer">
                    <div class="Form-inputs">
                        <div class="SelectInput">
                            <select class="Input" id="source" name="source">
                                <option class="Select-option" value=""><?= Lang::get('upload.auto_detect') ?></option>
                                <?
                                $SourceOther = null;
                                if (!in_array($TorrentSource, $this->Sources)) {
                                    $SourceOther = $TorrentSource;
                                }
                                foreach (Misc::display_array($this->Sources) as $Source) {
                                    echo "\t\t\t\t\t\t<option value=\"$Source\"";
                                    if ($Source == $TorrentSource) {
                                        echo ' selected="selected"';
                                    } else if ($Source == 'Other' && $SourceOther) {
                                        echo ' selected="selected"';
                                    }
                                    echo ">$Source</option>\n";
                                    // <option class="Select-option" value="$Source" selected="selected">$Source</option>
                                }
                                ?>
                            </select>
                            <input class="Input is-small hidden" type="text" name="source_other" value="<?= !in_array($TorrentSource, $this->Sources) ? $TorrentSource : '' ?>" />
                        </div>
                    </div>
                    <span id="source_warning" class="u-colorWarning"></span>
                </td>
            </tr>
            <tr class="Form-row is-specification <?= $this->NewTorrent || in_array($TorrentSource, ['HDTV', 'WEB', "TV"]) ? 'hidden' : '' ?> " id="processing-container">
                <td class="Form-label"><?= Lang::get('upload.movie_processing') ?><span class="u-colorWarning">*</span>:</td>
                <td class="Form-items Form-errorContainer">
                    <div class="Form-inputs">
                        <select class="Input" id="processing" name="processing">
                            <?
                            $SelectedProcessing = $TorrentProcessing;
                            if ($TorrentProcessing && !in_array($TorrentProcessing, $this->Processings)) {
                                $SelectedProcessing = 'Untouched';
                            }

                            foreach (Misc::display_array($this->Processings) as $Processing) {
                                echo "\t\t\t\t\t\t<option value=\"$Processing\"";
                                if ($Processing == $SelectedProcessing) {
                                    echo ' selected="selected"';
                                }
                                echo ">$Processing</option>\n";
                            }
                            ?>
                        </select>
                        <select class="Input <?= in_array($TorrentSource, ['Blu-ray', 'DVD']) && $SelectedProcessing == 'Untouched' ? '' : 'hidden' ?>" name="processing_other">
                            <option class="Select-Option" value=''><?= Lang::get('upload.auto_detect') ?></option>
                            <option class="Select-Option bd <?= $TorrentSource == 'Blu-ray' ? '' : 'hidden' ?>" value='BD25' <?= $TorrentProcessing == 'BD25' ? 'selected="selected"' : '' ?>>BD25</option>
                            <option class="Select-Option bd <?= $TorrentSource == 'Blu-ray' ? '' : 'hidden' ?>" value='BD50' <?= $TorrentProcessing == 'BD50' ? 'selected="selected"' : '' ?>>BD50</option>
                            <option class="Select-Option bd <?= $TorrentSource == 'Blu-ray' ? '' : 'hidden' ?>" value='BD66' <?= $TorrentProcessing == 'BD66' ? 'selected="selected"' : '' ?>>BD66</option>
                            <option class="Select-Option bd <?= $TorrentSource == 'Blu-ray' ? '' : 'hidden' ?>" value='BD100' <?= $TorrentProcessing == 'BD100' ? 'selected="selected"' : '' ?>>BD100</option>
                            <option class="Select-Option dvd <?= $TorrentSource == 'DVD' ? '' : 'hidden' ?>" value='DVD5' <?= $TorrentProcessing == 'DVD5' ? 'selected="selected"' : '' ?>>DVD5</option>
                            <option class="Select-Option dvd <?= $TorrentSource == 'DVD' ? '' : 'hidden' ?>" value='DVD9' <?= $TorrentProcessing == 'DVD9' ? 'selected="selected"' : '' ?>>DVD9</option>
                        </select>
                    </div>
                </td>
            </tr>

            <tr class="Form-row is-specification">
                <td class="Form-label"><?= Lang::get('upload.movie_codec') ?><span class="u-colorWarning">*</span>:</td>
                <td class="Form-items Form-errorContainer">
                    <div class="Form-inputs">
                        <div class="SelectInput">
                            <select class="Input" id="codec" name="codec">
                                <option value=''><?= Lang::get('upload.auto_detect') ?></option>
                                <?
                                $CodecOther = null;
                                if (!in_array($TorrentCodec, $this->Codecs)) {
                                    $CodecOther = $TorrentCodec;
                                }
                                foreach (Misc::display_array($this->Codecs) as $Codec) {
                                    echo "\t\t\t\t\t\t<option value=\"$Codec\"";
                                    if ($Codec == $TorrentCodec) {
                                        echo ' selected="selected"';
                                    } else if ($Codec == 'Other' && $CodecOther) {
                                        echo ' selected="selected"';
                                    }
                                    echo ">$Codec</option>\n";
                                }
                                ?>
                            </select>
                            <input class="Input is-small hidden" type="text" name="codec_other" value="<?= !in_array($TorrentCodec, $this->Codecs) ? $TorrentCodec : '' ?>" />
                        </div>
                    </div>
                    <span id="codex_warning" class="u-colorWarning"></span>
                </td>
            </tr>
            <tr class="Form-row is-specification">
                <td class="Form-label"><?= Lang::get('upload.movie_resolution') ?><span class="u-colorWarning">*</span>:</td>
                <td class="Form-items Form-errorContainer">
                    <div class="Form-inputs">
                        <div class="SelectInput">
                            <select class="Input" id="resolution" name="resolution">
                                <option class="Select-option" value=""><?= Lang::get('upload.auto_detect') ?></option>
                                <?
                                $resolution = $TorrentResolution;
                                $resolution_width = '';
                                $resolution_height = '';
                                if ($resolution && !in_array($resolution, $this->Resolutions)) {
                                    $resolution = "Other";
                                    list($resolution_width, $resolution_height) = explode('&times;', $Torrent['Resolution']);
                                }
                                foreach (Misc::display_array($this->Resolutions) as $Resolution) {
                                    echo "\t\t\t\t\t\t<option value=\"$Resolution\"";
                                    if ($Resolution == $resolution) {
                                        echo ' selected="selected"';
                                    }
                                    echo ">$Resolution</option>\n";
                                    // <option class="Select-option" value="$Resolution" selected="selected">$Resolution</option>
                                }
                                ?>
                            </select>
                            <span class="hidden">
                                <input class="Input is-small" type="number" id="resolution_width" name="resolution_width" value="<?= $resolution_width ?>">
                                <span>×</span>
                                <input class="Input is-small" type="number" id="resolution_height" name="resolution_height" , value="<?= $resolution_height ?>">
                            </span>
                        </div>
                    </div>
                    <span id="resolution_warning" class="u-colorWarning"></span>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= Lang::get('upload.movie_container') ?><span class="u-colorWarning">*</span>:</td>
                <td class="Form-items Form-errorContainer">
                    <div class="Form-inputs">
                        <div class="SelectInput">
                            <select class="Input" id="container" name="container">
                                <option class="Select-option" value=""><?= Lang::get('upload.auto_detect') ?></option>
                                <?
                                $ContainerOther = null;
                                if (!in_array($TorrentContainer, $this->Containers)) {
                                    $ContainerOther = $TorrentContainer;
                                }
                                foreach (Misc::display_array($this->Containers) as $Container) {
                                    echo "\t\t\t\t\t\t<option value=\"$Container\"";
                                    if ($Container == $TorrentContainer) {
                                        echo ' selected="selected"';
                                    } else if ($Container == 'Other' && $ContainerOther) {
                                        echo ' selected="selected"';
                                    }
                                    echo ">$Container</option>\n";
                                    // <option class="Select-option" value="$Container" selected="selected">$Container</option>
                                }
                                ?>
                            </select>
                            <input class="Input hidden" type="text" name="container_other" value="<?= !in_array($TorrentContainer, $this->Containers) ? $TorrentContainer : '' ?>" />
                        </div>
                    </div>
                    <span id="container_warning" class="u-colorWarning"></span>
                </td>
            </tr>
            <?
                if (check_perms("users_mod") && !$this->NewTorrent) {
            ?>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('upload.staff_note') ?>:</td>
                    <td class="Form-items">
                        <div class="Form-inputs">
                            <textarea class="Input" name="staff_note" id="staff_note"><?= $Note ?></textarea>
                        </div>
                    </td>
                </tr>
            <?  } ?>
            <tr class="Form-row">
                <td class="Form-label"></td>
                <td class="Form-items">
                    <div class="FormUpload-mediaInfoActions">
                        <a id="add-mediainfo" href="#" class="brackets">+</a>
                        <a id="remove-mediainfo" href="#" class="brackets">&minus;</a>
                    </div>
                </td>
            </tr>
            <tr class="Form-row is-mediainfo" id="mediainfo">
                <td class="Form-label">MediaInfo/BDInfo<span class="u-colorWarning">*</span>:</td>
                <td class="Form-items">
                    <? if ($this->NewTorrent) {
                        $GroupClass = "group1";
                    } else {
                        $GroupClass = "group0";
                    } ?>
                    <?
                    if ($MediaInfos) {
                        foreach ($MediaInfos as $MediaInfo) {
                    ?>
                            <div class="Form-errorContainer">
                                <div class="hidden">
                                    <div class="BBCodePreview-html <?= $GroupClass ?>"></div>
                                </div>
                                <div>
                                    <textarea class="Input BBCodePreview-text <?= $GroupClass ?>" name="mediainfo[]" data-type="mediainfo" placeholder="<?= Lang::get('upload.mediainfo_bdinfo_placeholder') ?>"><?= $MediaInfo ?></textarea>
                                </div>
                            </div>
                            <p class="upload_form_note"><?= Lang::get('upload.mediainfo_bdinfo_note') ?></p>
                        <?
                        }
                    } else {
                        ?>
                        <div class="Form-errorContainer">
                            <div class="hidden">
                                <div class="BBCodePreview-html <?= $GroupClass ?>"></div>
                            </div>
                            <div>
                                <textarea class="Input BBCodePreview-text <?= $GroupClass ?>" name="mediainfo[]" data-type="mediainfo" placeholder="<?= Lang::get('upload.mediainfo_bdinfo_placeholder') ?>"></textarea>
                            </div>
                        </div>
                    <?
                    }
                    ?>
                </td>
            </tr>

            <tr class="Form-row is-description" id="description-container">
                <td class="Form-label"><?= Lang::get('upload.movie_torrent_description') ?><span class="u-colorWarning">*</span>:</td>
                <td class="Form-items">
                    <div class="Form-errorContainer Form-vstack">
                        <?php new TEXTAREA_PREVIEW('release_desc', 'release_desc', display_str($TorrentDescription), 60, 8, true, true, false); ?>
                    </div>
                </td>
            </tr>
            <tr class="Form-row is-text">
                <td class="Form-label">
                    <?= Lang::get('upload.movie_subtitles') ?><span class="u-colorWarning">*</span>:
                </td>
                <td class="Form-items">
                    <div id="subtitles_container" class="Form-errorContainer">
                        <div id="type_of_subtitles" class="RadioGroup">
                            <div class="Radio">
                                <input class="Input" type="radio" id="mixed_subtitles" name="subtitle_type" data-value="mixed-sub" value="1" <?= $SubtitleType == 1 ? 'checked' : '' ?>>
                                <label class="Radio-label" for="mixed_subtitles"><?= Lang::get('upload.mixed_subtitles') ?></label>
                            </div>
                            <div class="Ratio">
                                <input class="Input" type="radio" id="hardcoded_subtitles" name="subtitle_type" value="2" data-value="hardcoded-sub" <?= $SubtitleType == 2 ? 'checked' : '' ?>>
                                <label class="Radio-label" for="hardcoded_subtitles"><?= Lang::get('upload.hardcode_sub') ?></label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" id="no_subtitles" name="subtitle_type" value="3" data-value="no-sub" <?= $SubtitleType == 3 ? 'checked' : '' ?>>
                                <label class="Radio-label" for="no_subtitles"><?= Lang::get('upload.no_subtitles') ?></label>
                            </div>
                        </div>
                        <div id="other_subtitles" class="<?= in_array($SubtitleType, [1, 2]) ? '' : 'hidden' ?>">
                            <div class="FormUpload-flags">
                                <?
                                function genSubcheckboxes($Labels, $Subtitles) {
                                    for ($i = 0; $i < count($Labels); $i++) {
                                        echo '<div class="Checkbox">
                                        <input class="Input" type="checkbox" id="' . $Labels[$i] . '" name="subtitles[]" value="' . $Labels[$i] . '"' . (strpos($Subtitles, $Labels[$i]) === false ? "" : "checked=\"checked\"") . '>
                                        <label class="Checkbox-label" for="' . $Labels[$i] . '">' .
                                            icon("flag/$Labels[$i]") .  Lang::get("upload.${Labels[$i]}") . '
                                        </label>
                                    </div>';
                                    }
                                }
                                $Labels = ['chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean'];
                                genSubcheckboxes($Labels,  $Subtitles);
                                ?>

                                <?
                                $Labels = ['arabic', 'brazilian_port', 'bulgarian', 'croatian', 'czech', 'danish', 'dutch', 'estonian', 'finnish', 'french', 'german', 'greek', 'hebrew', 'hindi', 'hungarian', 'icelandic', 'indonesian', 'italian', 'latvian', 'lithuanian', 'norwegian', 'persian', 'polish', 'portuguese', 'romanian', 'russian', 'serbian', 'slovak', 'slovenian', 'spanish', 'swedish', 'thai', 'turkish', 'ukrainian', 'vietnamese'];
                                genSubcheckboxes($Labels,  $Subtitles);
                                ?>
                            </div>
                        </div>
                    </div>
                    <!-- <p class="upload_form_note"><?= Lang::get('upload.movie_subtitles_note') ?></p> -->
                </td>
            </tr>
            <?
                if (!$this->NewTorrent && check_perms('users_mod')) {
            ?>
                </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('upload.custom_trumpable') ?>:</td>
                    <td class="Form-items">
                        <div class="Form-inputs">
                            <textarea class="Input" style="min-height: auto;" name="custom_trumpable" id="custom_trumpable" cols="60" rows="1"><?= $CustomTrumpable ?></textarea>
                        </div>
                    </td>
                </tr>
            <?
                }
                if ($this->NewTorrent) {
            ?>
            <?      } /* if new torrent */ ?>
            <tr class="Form-row is-text" id="movie_feature_tr">
                <td class="Form-label"><?= Lang::get('upload.movie_feature') ?>:</td>
                <td class="Form-items">
                    <div class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" id="chinese_dubbed" name="chinese_dubbed" <?= $ChineseDubbed ? "checked" : "" ?> />
                            <label class="Checkbox-label" for="chinese_dubbed"><?= Lang::get('upload.chinese_dubbed_label') ?></label>
                        </div>
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" id="special_effects_subtitles" name="special_effects_subtitles" <?= $SpecialSub ? "checked" : "" ?> />
                            <label class="Checkbox-label" for="special_effects_subtitles"><?= Lang::get('upload.special_effects_subtitles_label') ?></label>
                        </div>
                    </div>
                </td>
            </tr>
            <? if (check_perms("torrents_trumpable")) { ?>
                <tr class="Form-row" id="trumpable_tr">
                    <td class="Form-label"><?= Lang::get('upload.movie_trumpable') ?>:</td>
                    <td class="Form-items">
                        <div class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="no_sub" name="no_sub" <?= $this->Disabled ?> <?= $NoSub ? "checked" : "" ?> />
                                <label class="Checkbox-label" for="no_sub"><?= Lang::get('upload.no_sub') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="hardcode_sub" name="hardcode_sub" <?= $this->Disabled ?> <?= $HardSub ? "checked" : "" ?> />
                                <label class="Checkbox-label" for="hardcode_sub"><?= Lang::get('upload.hardcode_sub') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="bad_folders" name="bad_folders" <?= $this->Disabled ?> <?= $BadFolders ? "checked" : "" ?> />
                                <label class="Checkbox-label" for="bad_folders"><?= Lang::get('upload.bad_folders') ?></label>
                            </div>
                        </div>
                    </td>
                </tr>
            <?
                }
                if (check_perms('users_mod') || $this->NewTorrent) {
            ?>
                <tr class="Form-row">
                    <td class="Form-label"></td>
                    <td class="Form-items">
                        <div>
                            <strong class="how_to_toggle_container">[<a href="javascript:void(0);" onclick="$('#marks_how_to_blockquote').new_toggle();"><strong class="how_to_toggle"><?= Lang::get('upload.marks_how_to_toggle') ?></strong></a>]</strong>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row" id="marks_tr">
                    <td class="Form-label"><?= Lang::get('upload.marks') ?>:</td>
                    <td class="Form-items">
                        <div class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" onchange="globalapp.uploadAlterOriginal()" id="self_purchase" name="buy" <? if ($Buy) {
                                                                                                                                                    echo 'checked="checked" ';
                                                                                                                                                } ?> />
                                <label class="Checkbox-label" for="self_purchase"><?= Lang::get('upload.self_purchase') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" onchange="globalapp.uploadAlterOriginal()" id="self_rip" name="diy" <? if ($Diy) {
                                                                                                                                                echo 'checked="checked" ';
                                                                                                                                            } ?> />
                                <label class="Checkbox-label" for="self_rip"><?= Lang::get('upload.self_rip') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="jinzhuan" name="jinzhuan" <? if ($Jinzhuan) {
                                                                                                        echo 'checked="checked" ';
                                                                                                    } ?><?= !$Buy && !$Diy && !check_perms("users_mod") ? "disabled" : "" ?> />
                                <label class="Checkbox-label" for="jinzhuan"><?= Lang::get('upload.jinzhuan') ?></label>
                            </div>
                        </div>
                        <div style="padding: 10px 0 0;"><?= Lang::get('upload.marks_warning') ?></div>
                        <div class="FormUpload-explain" id="marks_how_to_blockquote" style="display: none;">
                            <?= Lang::get('upload.marks_how_to_blockquote') ?>
                        </div>
                    </td>
                </tr>
            <?
                }
            ?>

            </table>
        </div>
<?
                //  For AJAX requests,
                //  we don't need to include all scripts, but we do need to include the code
                //  that generates previews. It will have to be eval'd after an AJAX request.
                if ($_SERVER['SCRIPT_NAME'] === '/ajax.php')
                    TEXTAREA_PREVIEW::JavaScript(false);

                G::$DB->set_query_id($QueryID);
            } //function movie_form
        } //class
?>