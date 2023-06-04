<?

use Gazelle\Manager\Donation;
use Gazelle\Torrent\TorrentSlot;

$UserID = $_REQUEST['userid'];
if (!is_number($UserID)) {
    error(404);
}

$DB->query("
	SELECT
		m.Username,
		m.Email,
		m.IRCKey,
		m.Paranoia,
		m.2FA_Key,
		i.Info,
		i.Avatar,
		i.StyleID,
		i.StyleURL,
        i.StyleTheme,
		i.SiteOptions,
		i.UnseededAlerts,
		i.ReportedAlerts,
		i.RequestsAlerts,
		i.DownloadAlt,
		p.Level AS Class,
		i.InfoTitle,
		i.NotifyOnDeleteSeeding,
		i.NotifyOnDeleteSnatched,
		i.NotifyOnDeleteDownloaded,
		i.Lang,
		i.TGID,
		right(m.torrent_pass,8),
        i.SettingTorrentTitle
	FROM users_main AS m
		JOIN users_info AS i ON i.UserID = m.ID
		LEFT JOIN permissions AS p ON p.ID = m.PermissionID
	WHERE m.ID = '" . db_string($UserID) . "'");
list($Username, $Email, $IRCKey, $Paranoia, $TwoFAKey, $Info, $Avatar, $StyleID, $StyleURL, $StyleTheme, $SiteOptions, $UnseededAlerts, $ReportedAlerts, $RequestsAlerts, $DownloadAlt, $Class, $InfoTitle, $NotifyOnDeleteSeeding, $NotifyOnDeleteSnatched, $NotifyOnDeleteDownloaded, $Lang, $TGID, $Right8Passkey, $SettingTorrentTitle) = $DB->next_record(MYSQLI_NUM, array(3, 9, 10, 23));
$SettingTorrentTitle = $SettingTorrentTitle ? json_decode($SettingTorrentTitle, true) : [];


if ($UserID != $LoggedUser['ID'] && !check_perms('users_edit_profiles', $Class)) {
    error(403);
}

$Paranoia = unserialize($Paranoia);
if (!is_array($Paranoia)) {
    $Paranoia = array();
}

// Fetching API Token
$apiToken = $DB->query(
    "
    SELECT Token FROM api_applications
    WHERE UserID = $UserID"
);
list($apiToken) = $DB->next_record();

function paranoia_level($Setting) {
    global $Paranoia;
    // 0: very paranoid; 1: stats allowed, list disallowed; 2: not paranoid
    return (in_array($Setting . '+', $Paranoia)) ? 0 : (in_array($Setting, $Paranoia) ? 1 : 2);
}

function display_paranoia($FieldName) {
    $Level = paranoia_level($FieldName);
    print "<div class=\"Checkbox\">";
    print "\t\t\t\t\t<input class=\"Input\" id=\"input-p_{$FieldName}_c\" type=\"checkbox\" name=\"p_{$FieldName}_c\"" . checked($Level >= 1) . " onchange=\"AlterParanoia()\" />\n<label for=\"input-p_{$FieldName}_c\">" . t('server.user.show_count') . "</label>" . "\n";
    print "</div>";
    print "<div class=\"Checkbox\">";
    print "\t\t\t\t\t<input class=\"CheckBox-label\" id=\"input-p_{$FieldName}_l\" type=\"checkbox\" name=\"p_{$FieldName}_l\"" . checked($Level >= 2) . " onchange=\"AlterParanoia()\" />\n<label for=\"input-p_{$FieldName}_l\">" . t('server.user.show_list') . "</label>" . "\n";
    print "</div>";
}

function checked($Checked) {
    return ($Checked ? ' checked="checked"' : '');
}
$SiteOptions = unserialize_array($SiteOptions);
$SiteOptions = array_merge(Users::default_site_options(), $SiteOptions);

View::show_header("$Username &gt; " . t('server.user.setting'), 'user,jquery-ui,release_sort,password_validate,validate,cssgallery,preview_paranoia,bbcode,user_settings,donor_titles', 'PageUserEdit');

$donation = new Donation();

$DonorRank = $donation->rank($UserID);
$DonorIsVisible = $donation->isVisible($UserID);

if ($DonorIsVisible === null) {
    $DonorIsVisible = true;
}

extract($donation->enabledRewards($UserID));
$Rewards = $donation->rewards($UserID);
$ProfileRewards = $donation->profileRewards($UserID);
$DonorTitles = $donation->titles($UserID);


echo $Val->GenerateJS('userform');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, false, false, false) ?> &gt; <?= t('server.user.setting') ?></h2>
    </div>
    <form class="edit_form" name="user" id="userform" action="" method="post" autocomplete="off">
        <div class="LayoutMainSidebar">
            <div class="Sidebar LayoutMainSidebar-sidebar">
                <div class="SidebarItemUserSettings SidebarItem Box" id="settings_sections">
                    <div class="SidebarItem-header Box-header">
                        <strong><?= t('server.user.menu') ?></strong>
                    </div>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <li class="SidebarList-item">
                            <a class="Link" href="#site_appearance_settings" data-tooltip="<?= t('server.user.st_style_title') ?>">
                                <?= t('server.user.st_style') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#torrent_settings" data-tooltip="<?= t('server.user.st_torrents_title') ?>">
                                <?= t('server.user.st_torrents') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#community_settings" data-tooltip="<?= t('server.user.st_community_title') ?>">
                                <?= t('server.user.st_community') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#notification_settings" data-tooltip="<?= t('server.user.st_notification_title') ?>">
                                <?= t('server.user.st_notification') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#personal_settings" data-tooltip="<?= t('server.user.st_personal_title') ?>">
                                <?= t('server.user.st_personal') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#paranoia_settings" data-tooltip="<?= t('server.user.st_paranoia_title') ?>">
                                <?= t('server.user.st_paranoia') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#access_settings" data-tooltip="<?= t('server.user.st_access_title') ?>">
                                <?= t('server.user.st_access') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <input class="Input" type="text" id="settings_search" onclick="location.href='#'" placeholder="<?= t('server.user.st_search') ?>" />
                        </li>
                        <li class="SidebarList-item">
                            <input class="Button" type="submit" id="submit" value="<?= t('server.user.st_save') ?>" />
                        </li>
                    </ul>
                </div>
            </div>
            <div class="LayoutMainSidebar-main Form is-longLabel">
                <div>
                    <input type="hidden" name="action" value="take_edit" />
                    <input type="hidden" name="userid" value="<?= $UserID ?>" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                </div>
                <table class="Form-rowList" variant="header" id="site_appearance_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.st_style') ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-stylesheet" id="site_style_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.stylesheet_title') ?>"><strong><?= t('server.user.style') ?></strong></td>
                        <td class="Form-items">
                            <div class="Form-inputs">
                                <select class="Input" name="stylesheet" id="stylesheet">
                                    <? foreach ($Stylesheets as $Style) { ?>
                                        <option class="Select-option" value="<?= ($Style['ID']) ?>" <?= $Style['ID'] == $StyleID ? ' selected="selected"' : '' ?>>
                                            <?= ($Style['ProperName']) ?>
                                        </option>
                                    <?  } ?>
                                </select>
                                <a class="Link" href="#" onclick="globalapp.toggleAny(event, '.StyleGallery', { updateText: true })">
                                    <span class="u-toggleAny-show"><?= t('server.user.show_gallery') ?></span>
                                    <span class="u-toggleAny-hide u-hidden"><?= t('server.user.hide_gallery') ?></span>
                                </a>
                            </div>
                            <div class="StyleGallery u-hidden">
                                <? $LangForFile = $Lang === 'chs' ? 'zh' : $Lang ?>
                                <? foreach ($Stylesheets as $Style) { ?>
                                    <div class="StyleGallery-item">
                                        <a class="StyleGallery-imageLink" name="<?= ($Style['Name']) ?>" href="<?= CONFIG['STATIC_SERVER'] . 'stylespreview/' . $LangForFile . '-' . $Style['Name'] . '-dark.png' ?>" target="_blank">
                                            <img class="StyleGallery-image" src="<?= CONFIG['STATIC_SERVER'] . 'stylespreview/thumb-' . $Style['Name'] . '.jpg' ?>" alt="<?= $Style['Name'] ?>" />
                                        </a>
                                        <div class="StyleGallery-name Radio">
                                            <input class="Input" type="radio" name="stylesheet_gallery" id="input-stylesheet-<?= $Style['ID'] ?>" value="<?= ($Style['ID']) ?>" />
                                            <label class="Radio-label" for="input-stylesheet-<?= $Style['ID'] ?>"> <?= ($Style['ProperName']) ?></label>
                                        </div>
                                    </div>
                                <?  } ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><strong><?= t('server.user.theme') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Radio">
                                <input class="Input" type="radio" name="style_theme" value="auto" id="theme-auto" <?= $StyleTheme == 'auto' ? 'checked' : '' ?> />
                                <label class="Radio-label" for="theme-auto">
                                    <?= t('server.user.theme_auto') ?>
                                </label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="style_theme" value="light" id="theme-light" <?= $StyleTheme == 'light' ? 'checked' : '' ?> />
                                <label class="Radio-label" for="theme-light">
                                    <?= t('server.user.theme_light') ?>
                                </label>
                            </div>
                            <div class="Input">
                                <input class="Input" type="radio" name="style_theme" value="dark" id="theme-dark" <?= $StyleTheme == 'dark' ? 'checked' : '' ?> />
                                <label class="Radio-label" for="theme-dark">
                                    <?= t('server.user.theme_dark') ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="site_extstyle_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.ex_style_title') ?>"><strong><?= t('server.user.ex_style') ?></strong></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" size="40" name="styleurl" id="styleurl" value="<?= display_str($StyleURL) ?>" />
                        </td>
                    </tr>
                    <? if (check_perms('users_mod')) { ?>
                        <tr class="Form-row" id="site_autostats_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.base_stats_title') ?>"><strong><?= t('server.user.base_stats') ?></strong></td>
                            <td class="Form-inputs">
                                <div class="Checkbox">
                                    <input class="Input" id="input-autoload_comm_stats" type="checkbox" name="autoload_comm_stats" <? Format::selected('AutoloadCommStats', 1, 'checked', $SiteOptions); ?> />
                                    <label class="Checkbox-label" for="input-autoload_comm_stats"><?= t('server.user.base_stats_note') ?></label>
                                </div>
                            </td>
                        </tr>
                    <?  } ?>
                </table>
                <table class="Form-rowList" variant="header" id="torrent_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.st_torrents') ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-torrentTitle" id="custom_torrent_title">
                        <td class="Form-label" data-tooltip="<?= t('server.user.SettingTorrentTitleTooltip') ?>">
                            <strong>
                                <?= t('server.user.SettingTorrentTitle') ?>
                            </strong>
                        </td>
                        <td class="Form-items">
                            <div class="Form-inputs">
                                <div class="Checkbox">
                                    <? $Checked = $SettingTorrentTitle['Alternative'] ? 'checked' : '' ?>
                                    <input class="Input" type="checkbox" name="settingTorrentTitleAlternative" id="same_width" <?= $Checked ?> />
                                    <label class="Checkbox-label" for="same_width">
                                        <?= t('server.user.SettingAlternative') ?>
                                    </label>
                                </div>
                                <div class="Checkbox">
                                    <? $Checked = $SettingTorrentTitle['ReleaseGroup'] ? 'checked' : '' ?>
                                    <input class="Input" type="checkbox" name="settingTorrentTitleReleaseGroup" id="release_group" <?= $Checked ?> />
                                    <label class="Checkbox-label" for="release_group">
                                        <?= t('server.user.SettingShowReleaseGroup') ?>
                                    </label>
                                </div>
                            </div>

                            <?
                            $TableTorrentClass = $SettingTorrentTitle['Alternative'] ? 'is-alternative' : '';
                            ?>
                            <div class="Form-inputs TorrentTitle-previews TableTorrent TableTorrent--preview <?= $TableTorrentClass ?>">
                                <strong><?= t('server.user.donorforum_4') ?>:</strong>
                                <?
                                $Previews = [
                                    ['Codec' => 'x265', 'Source' => 'WEB', 'Resolution' => '720p', 'Container' => 'MKV', 'Processing' => 'Encode', 'Slot' => TorrentSlot::TorrentSlotTypeEnglishQuality, 'RemasterTitle' => 'dolby_vision / dolby_atmos / masters_of_cinema', 'ReleaseGroup' => 'Release Group'],
                                ];
                                ?>
                                <? foreach ($Previews as $Preview) {
                                ?>
                                    <div>
                                        <?= Torrents::torrent_info(
                                            $Preview,
                                            true,
                                            [
                                                'Class' => 'TorrentTitle--standalone is-preview',
                                                'SettingTorrentTitle' => $SettingTorrentTitle,
                                            ]
                                        ) ?>
                                    </div>
                                <? } ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row is-torrentTitle" id="custom_torrent_title">
                        <td class="Form-label" data-tooltip="<?= t('server.user.torrent_title_drag_note') ?>">
                            <strong>
                                <?= t('server.user.setting_torrent_title_seq') ?>
                            </strong>
                        </td>
                        <td class="Form-inputs">
                            <input id="SettingTorrentTitleInput" type="hidden" name="settingTorrentTitleItems" value='<?= implode(',', $SettingTorrentTitle['Items'])  ?>' />
                            <?= Torrents::settingTorrentTitle(
                                $SettingTorrentTitle,
                                [
                                    'Class' => 'TorrentTitle--standalone is-edit'
                                ]
                            ) ?>
                            <button class="Button" size="tiny" onclick="globalapp.userEditSettingTorrentTitleReset()" type="submit">
                                <?= t('server.user.reset') ?>
                            </button>
                        </td>
                    </tr>
                    <? if (check_perms('site_advanced_search')) { ?>
                        <tr class="Form-row" id="tor_searchtype_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.default_search_title') ?>"><strong><?= t('server.user.default_search') ?></strong></td>
                            <td class="Form-inputs">
                                <div class="Radio">
                                    <input class="Input" type="radio" name="searchtype" id="search_type_simple" value="0" <?= $SiteOptions['SearchType'] == 0 ? ' checked="checked"' : '' ?> />
                                    <label class="Radio-label" for="search_type_simple">
                                        <?= t('server.user.base') ?>
                                    </label>
                                </div>
                                <div class="Radio">
                                    <input class="Input" type="radio" name="searchtype" id="search_type_advanced" value="1" <?= $SiteOptions['SearchType'] == 1 ? ' checked="checked"' : '' ?> />
                                    <label class="Radio-label" for="search_type_advanced">
                                        <?= t('server.user.advanced') ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <? } ?>
                    <tr class="Form-row" id="tor_group_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.torrents_group_title') ?>"><strong><?= t('server.user.torrents_group') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="option_group">
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="disablegrouping" id="disablegrouping" <?= $SiteOptions['DisableGrouping2'] == 0 ? ' checked="checked"' : '' ?> />
                                    <label class="Checkbox-label" for="disablegrouping"><?= t('server.user.torrents_group_tool') ?></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_gdisp_search_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.torrents_group_display_title') ?>"><strong><?= t('server.user.torrents_group_display') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Radio">
                                <input class="Input" type="radio" name="torrentgrouping" id="torrent_grouping_open" value="0" <?= $SiteOptions['TorrentGrouping'] == 0 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="torrent_grouping_open">
                                    <?= t('server.user.enabled') ?>
                                </label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="torrentgrouping" id="torrent_grouping_closed" value="1" <?= $SiteOptions['TorrentGrouping'] == 1 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="torrent_grouping_closed">
                                    <?= t('server.user.disabled') ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_gdisp_artist_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.torrents_artists_display_title') ?>"><strong><?= t('server.user.torrents_artists_display') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Radio">
                                <input class="Input" type="radio" name="discogview" id="discog_view_open" value="0" <?= $SiteOptions['DiscogView'] == 0 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="discog_view_open">
                                    <?= t('server.user.enabled') ?>
                                </label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="discogview" id="discog_view_closed" value="1" <?= $SiteOptions['DiscogView'] == 1 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="discog_view_closed">
                                    <?= t('server.user.disabled') ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_reltype_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.torrents_artists_display_type_title') ?>"><strong><?= t('server.user.torrents_artists_display_type') ?></strong></td>
                        <td class="Form-items">
                            <div>
                                <a class="Link" href="#" onclick="globalapp.toggleAny(event, '#sortable_container, #reset_sortable');return false;">
                                    <span class="u-toggleAny-show "><?= t('server.common.show') ?></span>
                                    <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                                </a>
                                <a class="Link u-hidden" href="#" id="reset_sortable"><?= t('server.user.reset_to_default') ?></a>
                            </div>
                            <div id="sortable_container" class="u-hidden">
                                <p><?= t('server.user.drag_and_drop_change_order') ?></p>
                                <ul class="sortable_list" id="sortable">
                                    <? Users::release_order($SiteOptions) ?>
                                </ul>
                                <script type="text/javascript" id="sortable_default">
                                    //<![CDATA[
                                    var sortable_list_default = <?= Users::release_order_default_js($SiteOptions) ?>;
                                    //]]>
                                </script>
                            </div>
                            <input type="hidden" id="sorthide" name="sorthide" value="" />
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_snatched_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.torrents_snatched_title') ?>"><strong><?= t('server.user.torrents_snatched') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="showsnatched" id="showsnatched" <?= !empty($SiteOptions['ShowSnatched']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="showsnatched"><?= t('server.user.enabled') ?></label>
                            </div>
                        </td>
                    </tr>
                    <!--            <tr class="Form-row">
                <td class="Form-label"><strong>Collage album art view</strong></td>
                <td class="Form-inputs">
                    <select class="Input" name="hidecollage" id="hidecollage">
                        <option class="Select-option" value="0"<?= $SiteOptions['HideCollage'] == 0 ? ' selected="selected"' : '' ?>>Show album art</option>
                        <option class="Select-option" value="1"<?= $SiteOptions['HideCollage'] == 1 ? ' selected="selected"' : '' ?>>Hide album art</option>
                    </select>
                </td>
            </tr>-->
                    <tr class="Form-row" id="tor_cover_tor_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.torrents_cover_title') ?>"><strong><?= t('server.user.torrents_cover') ?></strong></td>
                        <td class="Form-inputs">
                            <input type="hidden" name="coverart" value="" />
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="coverart" id="coverart" <?= $SiteOptions['CoverArt'] ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="coverart"><?= t('server.user.enabled') ?></label>
                            </div>
                        </td>
                    </tr>

                    <tr class="Form-row" id="tor_cover_coll_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.cover_coll_title') ?>"><strong><?= t('server.user.cover_coll') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="collagecovers" id="collagecovers">
                                <option class="Select-option" value="10" <?= $SiteOptions['CollageCovers'] == 10 ? ' selected="selected"' : '' ?>>10</option>
                                <option class="Select-option" value="25" <?= ($SiteOptions['CollageCovers'] == 25 || !isset($SiteOptions['CollageCovers'])) ? ' selected="selected"' : '' ?>>25 (<?= t('server.user.default') ?>)</option>
                                <option class="Select-option" value="50" <?= $SiteOptions['CollageCovers'] == 50 ? ' selected="selected"' : '' ?>>50</option>
                                <option class="Select-option" value="100" <?= $SiteOptions['CollageCovers'] == 100 ? ' selected="selected"' : '' ?>>100</option>
                                <option class="Select-option" value="1000000" <?= $SiteOptions['CollageCovers'] == 1000000 ? ' selected="selected"' : '' ?>><?= t('server.user.collage_covers_all') ?></option>
                                <option class="Select-option" value="0" <?= ($SiteOptions['CollageCovers'] === 0 || (!isset($SiteOptions['CollageCovers']) && $SiteOptions['HideCollage'])) ? ' selected="selected"' : '' ?>><?= t('server.user.collage_covers_none') ?></option>
                            </select>
                            <?= t('server.user.cover_coll_number') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_showfilt_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.filt_tr_title') ?>"><strong><?= t('server.user.filt_tr') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="showtfilter" id="showtfilter" <?= (!isset($SiteOptions['ShowTorFilter']) || $SiteOptions['ShowTorFilter'] ? ' checked="checked"' : '') ?> />
                                <label class="Checkbox-label" for="showtfilter"><?= t('server.user.filt_tr_show') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_showhotmovie_tr">
                        <td class="Form-label"><strong><?= t('server.index.popular_movies') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="showhotmovie" id="showtfilter" <?= (isset($SiteOptions['ShowHotMovieOnHomePage']) && $SiteOptions['ShowHotMovieOnHomePage'] ? ' checked="checked"' : '') ?> />
                                <label class="Checkbox-label" for="showhotmovie"><?= t('server.user.show_hot_movie_at_home') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_autocomp_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.autocomp_title') ?>"><strong><?= t('server.user.autocomp') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="autocomplete">
                                <option class="Select-option" value="0" <?= empty($SiteOptions['AutoComplete']) ? ' selected="selected"' : '' ?>><?= t('server.user.autocomp_0') ?></option>
                                <option class="Select-option" value="2" <?= $SiteOptions['AutoComplete'] === 2 ? ' selected="selected"' : '' ?>><?= t('server.user.autocomp_2') ?></option>
                                <option class="Select-option" value="1" <?= $SiteOptions['AutoComplete'] === 1 ? ' selected="selected"' : '' ?>><?= t('server.user.autocomp_1') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_voting_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.voting_title') ?>"><strong><?= t('server.user.voting') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="novotelinks" id="novotelinks" <?= !empty($SiteOptions['NoVoteLinks']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="novotelinks"><?= t('server.user.voting_disable') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_dltext_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.dltext_title') ?>"><strong><?= t('server.user.dltext') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="downloadalt" id="downloadalt" <?= $DownloadAlt ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="downloadalt"><?= t('server.user.dltext_tr') ?></label>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="Form-rowList" variant="header" id="community_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.st_community') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_ppp_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.ppp_title') ?>"><strong><?= t('server.user.ppp') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="postsperpage" id="postsperpage">
                                <option class="Select-option" value="25" <?= $SiteOptions['PostsPerPage'] == 25 ? ' selected="selected"' : '' ?>>25 (<?= t('server.user.default') ?>)</option>
                                <option class="Select-option" value="50" <?= $SiteOptions['PostsPerPage'] == 50 ? ' selected="selected"' : '' ?>>50</option>
                                <option class="Select-option" value="100" <?= $SiteOptions['PostsPerPage'] == 100 ? ' selected="selected"' : '' ?>>100</option>
                            </select>
                            <?= t('server.user.ppp_number') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_inbsort_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.inbsort_title') ?>"><strong><?= t('server.user.inbsort') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="list_unread_pms_first" id="list_unread_pms_first" <?= !empty($SiteOptions['ListUnreadPMsFirst']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="list_unread_pms_first"><?= t('server.user.inbsort_un') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_emot_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.emot_title') ?>"><strong><?= t('server.user.emot') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="disablesmileys" id="disablesmileys" <?= !empty($SiteOptions['DisableSmileys']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="disablesmileys"><?= t('server.user.emot_disable') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_mature_tr">
                        <td class="Form-label" data-tooltip-interactive="<?= t('server.user.mature_title') ?>"><strong><?= t('server.user.mature') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="enablematurecontent" id="enablematurecontent" <?= !empty($SiteOptions['EnableMatureContent']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="enablematurecontent"><?= t('server.user.mature_show') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_avatars_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.avatars_title') ?>"><strong><?= t('server.user.avatars') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="disableavatars" id="disableavatars" onchange="ToggleIdenticons();">
                                <option class="Select-option" value="1" <?= $SiteOptions['DisableAvatars'] == 1 ? ' selected="selected"' : '' ?>><?= t('server.user.disabled') ?></option>
                                <option class="Select-option" value="0" <?= $SiteOptions['DisableAvatars'] == 0 ? ' selected="selected"' : '' ?>><?= t('server.user.avatars_0') ?></option>
                                <option class="Select-option" value="2" <?= $SiteOptions['DisableAvatars'] == 2 ? ' selected="selected"' : '' ?>><?= t('server.user.avatars_2') ?></option>
                                <option class="Select-option" value="3" <?= $SiteOptions['DisableAvatars'] == 3 ? ' selected="selected"' : '' ?>><?= t('server.user.avatars_3') ?></option>
                            </select>
                            <select class="Input" name="identicons" id="identicons">
                                <option class="Select-option" value="0" <?= $SiteOptions['Identicons'] == 0 ? ' selected="selected"' : '' ?>>Identicon</option>
                                <option class="Select-option" value="1" <?= $SiteOptions['Identicons'] == 1 ? ' selected="selected"' : '' ?>>MonsterID</option>
                                <option class="Select-option" value="2" <?= $SiteOptions['Identicons'] == 2 ? ' selected="selected"' : '' ?>>Wavatar</option>
                                <option class="Select-option" value="3" <?= $SiteOptions['Identicons'] == 3 ? ' selected="selected"' : '' ?>>Retro</option>
                                <option class="Select-option" value="4" <?= $SiteOptions['Identicons'] == 4 ? ' selected="selected"' : '' ?>>Robots 1</option>
                                <option class="Select-option" value="5" <?= $SiteOptions['Identicons'] == 5 ? ' selected="selected"' : '' ?>>Robots 2</option>
                                <option class="Select-option" value="6" <?= $SiteOptions['Identicons'] == 6 ? ' selected="selected"' : '' ?>>Robots 3</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_autosave_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.autosave_title') ?>"><strong><?= t('server.user.autosave') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="disableautosave" id="disableautosave" <?= !empty($SiteOptions['DisableAutoSave']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="disableautosave"><?= t('server.user.disabled') ?></label>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="Form-rowList" variant="header" id="notification_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.st_notification') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_autosubscribe_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.autosubscribe_title') ?>"><strong><?= t('server.user.autosubscribe') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="autosubscribe" id="autosubscribe" <?= !empty($SiteOptions['AutoSubscribe']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="autosubscribe"><?= t('server.user.enabled') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_requests_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.autosubscribe_your_request_title') ?>"><strong><?= t('server.user.autosubscribe_your_request') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="requestsalerts" id="requestsalerts" <?= checked($RequestsAlerts) ?> />
                                <label class="Checkbox-label" for="requestsalerts"><?= t('server.user.enabled') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_notifyondeleteseeding_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.notifyondeleteseeding_title') ?>"><strong><?= t('server.user.notifyondeleteseeding') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="notifyondeleteseeding" id="notifyondeleteseeding" <?= !empty($NotifyOnDeleteSeeding) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="notifyondeleteseeding"><?= t('server.user.notifyondeleteseeding_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_notifyondeletesnatched_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.notifyondeletesnatched_title') ?>"><strong><?= t('server.user.notifyondeletesnatched') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="notifyondeletesnatched" id="notifyondeletesnatched" <?= !empty($NotifyOnDeleteSnatched) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="notifyondeletesnatched"><?= t('server.user.notifyondeletesnatched_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_notifyondeletedownloaded_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.notifyondeletedownloaded_title') ?>"><strong><?= t('server.user.notifyondeletedownloaded') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="notifyondeletedownloaded" id="notifyondeletedownloaded" <?= !empty($NotifyOnDeleteDownloaded) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="notifyondeletedownloaded"><?= t('server.user.notifyondeletedownloaded_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_unseeded_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.unseeded_title') ?>"><strong><?= t('server.user.unseeded') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="unseededalerts" id="unseededalerts" <?= checked($UnseededAlerts) ?> />
                                <label class="Checkbox-label" for="unseededalerts"><?= t('server.user.unseeded_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_reported_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.reported_title') ?>"><strong><?= t('server.user.reported') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="reportedalerts" id="reportedalerts" <?= checked($ReportedAlerts) ?> />
                                <label class="Checkbox-label" for="reportedalerts"><?= t('server.user.reported_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <? NotificationsManagerView::render_settings(NotificationsManager::get_settings($UserID)); ?>
                </table>
                <table class="Form-rowList" variant="header" id="personal_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.st_personal') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="pers_avatar_tr">
                        <td class="Form-label" data-tooltip-interactive="<?= t('server.user.st_avatar_title') ?>"><strong><?= t('server.user.st_avatar') ?></strong></td>
                        <td class="Form-items">
                            <div class="FormOneLine">
                                <input class="Input" type="text" size="50" name="avatar" id="avatar" value="<?= display_str($Avatar) ?>" />
                            </div>
                        </td>
                    </tr>
                    <? if ($HasSecondAvatar) { ?>
                        <tr class="Form-row" id="pers_avatar2_tr">
                            <td class="Form-label" data-tooltip-interactive="<?= t('server.user.st_avatar_2_title') ?>"><strong><?= t('server.user.st_avatar_2') ?></strong></td>
                            <td class="Form-items">
                                <div class="FormOneLine">
                                    <input class="Input" type="text" size="50" name="second_avatar" id="second_avatar" value="<?= $Rewards['SecondAvatar'] ?>" />
                                </div>
                            </td>
                        </tr>
                    <?  }
                    if ($HasAvatarMouseOverText) { ?>
                        <tr class="Form-row" id="pers_avatarhover_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_avatarhover_title') ?>"><strong><?= t('server.user.st_avatarhover') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="avatar_mouse_over_text" id="avatar_mouse_over_text" value="<?= $Rewards['AvatarMouseOverText'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasDonorIconMouseOverText) { ?>
                        <tr class="Form-row" id="pers_donorhover_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_donorhover_title') ?>"><strong><?= t('server.user.st_donorhover') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="donor_icon_mouse_over_text" id="donor_icon_mouse_over_text" value="<?= $Rewards['IconMouseOverText'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasDonorIconLink) { ?>
                        <tr class="Form-row" id="pers_donorlink_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_donorlink_title') ?>"><strong><?= t('server.user.st_donorlink') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="donor_icon_link" id="donor_icon_link" value="<?= $Rewards['CustomIconLink'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasCustomDonorIcon) { ?>
                        <tr class="Form-row" id="pers_donoricon_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_donoricon_title') ?>"><strong><?= t('server.user.st_donoricon') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="donor_icon_custom_url" id="donor_icon_custom_url" value="<?= $Rewards['CustomIcon'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasDonorForum) { ?>
                        <tr class="Form-row" id="pers_donorforum_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_donorforum_title') ?>"><strong><?= t('server.user.st_donorforum') ?></strong></td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <label>
                                        <strong><?= t('server.user.donorforum_1') ?>:</strong>
                                        <input class="Input is-small" type="text" id="input-donor_title_prefix" size="30" maxlength="30" name="donor_title_prefix" id="donor_title_prefix" value="<?= $DonorTitles['Prefix'] ?>" /></label>
                                    <label for="input-donor_title_suffix"><strong><?= t('server.user.donorforum_2') ?>:</strong>
                                        <input class="Input is-small" type="text" id="input-donor_title_suffix" size="30" maxlength="30" name="donor_title_suffix" id="donor_title_suffix" value="<?= $DonorTitles['Suffix'] ?>" /></label>
                                    <label for="input-donor_title_comma"><strong><?= t('server.user.donorforum_3') ?>:</strong>
                                        <input id="input-donor_title_comma" type="checkbox" id="donor_title_comma" name="donor_title_comma" <?= !$DonorTitles['UseComma'] ? ' checked="checked"' : '' ?> /></label>
                                </div>
                                <div>
                                    <strong><?= t('server.user.donorforum_4') ?>:</strong> <span id="donor_title_prefix_preview"></span><?= $Username ?><span id="donor_title_comma_preview">, </span><span id="donor_title_suffix_preview"></span>
                                </div>
                            </td>
                        </tr>
                    <?  } ?>

                    <tr class="Form-row" id="pers_proftitle_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.st_proftitle1_title') ?>"><strong><?= t('server.user.st_proftitle1') ?></strong></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" size="50" name="profile_title" id="profile_title" value="<?= display_str($InfoTitle) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row" id="pers_profinfo_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.st_profinfo1_title') ?>"><strong><?= t('server.user.st_profinfo1') ?></strong></td>
                        <td class="Form-items">
                            <?php $textarea = new TEXTAREA_PREVIEW('info', 'info', display_str($Info), 40, 8); ?>
                        </td>
                    </tr>
                    <!-- Excuse this numbering confusion, we start numbering our profile info/titles at 1 in the donor_rewards table -->
                    <? if ($HasProfileInfo1) { ?>
                        <tr class="Form-row" id="pers_proftitle2_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_proftitle2_title') ?>"><strong><?= t('server.user.st_proftitle2') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="profile_title_1" id="profile_title_1" value="<?= display_str($ProfileRewards['ProfileInfoTitle1']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo2_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_profinfo2_title') ?>"><strong><?= t('server.user.st_profinfo2') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_1', 'profile_info_1', display_str($ProfileRewards['ProfileInfo1']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasProfileInfo2) { ?>
                        <tr class="Form-row" id="pers_proftitle3_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_proftitle3_title') ?>"><strong><?= t('server.user.st_proftitle3') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="profile_title_2" id="profile_title_2" value="<?= display_str($ProfileRewards['ProfileInfoTitle2']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo3_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_profinfo3_title') ?>"><strong><?= t('server.user.st_profinfo3') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_2', 'profile_info_2', display_str($ProfileRewards['ProfileInfo2']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasProfileInfo3) { ?>
                        <tr class="Form-row" id="pers_proftitle4_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_proftitle4_title') ?>"><strong><?= t('server.user.st_proftitle4') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="profile_title_3" id="profile_title_3" value="<?= display_str($ProfileRewards['ProfileInfoTitle3']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo4_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_profinfo4_title') ?>"><strong><?= t('server.user.st_profinfo4') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_3', 'profile_info_3', display_str($ProfileRewards['ProfileInfo3']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasProfileInfo4) { ?>
                        <tr class="Form-row" id="pers_proftitle5_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_proftitle5_title') ?>"><strong><?= t('server.user.st_proftitle5') ?></strong></td>
                            <td class="Form-inputs"><input class="Input" type="text" size="50" name="profile_title_4" id="profile_title_4" value="<?= display_str($ProfileRewards['ProfileInfoTitle4']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo5_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_profinfo5_title') ?>"><strong><?= t('server.user.st_profinfo5') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_4', 'profile_info_4', display_str($ProfileRewards['ProfileInfo4']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasUnlimitedColor) { ?>
                        <tr class="Form-row" id="pers_unlimitedcolor_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.unlimitedcolor_title') ?>"><strong><?= t('server.user.unlimitedcolor') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="is-small Input" type="text" onkeyup="previewColorUsername()" size="50" name="unlimitedcolor" placeholder="<?= t('server.user.unlimitedcolor_placeholder') ?>" id="unlimitedcolor" value="<?= display_str($Rewards['ColorUsername']) ?>" />
                            </td>
                        </tr>
                    <?  } else if ($HasLimitedColorName) {
                        $LimitedColors = [
                            "#ed5a65" => t('server.user.limitedcolor_red'),
                            "#2474b5" => t('server.user.limitedcolor_blue'),
                            "#428675" => t('server.user.limitedcolor_green'),
                            "#f2ce2b" => t('server.user.limitedcolor_yellow'),
                            "#fb8b05" => t('server.user.limitedcolor_orange'),
                            "#8b2671" => t('server.user.limitedcolor_purple')
                        ];
                    ?>
                        <tr class="Form-row" id="pers_limitedcolorname_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.limitedcolor_title') ?>"><strong><?= t('server.user.limitedcolor') ?></strong></td>
                            <td class="Form-inputs">
                                <select class="Input" name="limitedcolor" id="limitedcolor" onchange="previewColorUsername()">
                                    <?
                                    foreach ($LimitedColors as $LimitedColor => $ColorName) {
                                        echo "<option class='Select-option' value=\"$LimitedColor\"" . ($Rewards['ColorUsername'] == $LimitedColor ? ' selected="selected"' : '') . ">$ColorName</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    <?  }
                    if ($HasGradientsColor) { ?>
                        <tr class="Form-row" id="pers_gradientscolor_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.gradientscolor_title') ?>"><strong><?= t('server.user.gradientscolor') ?></strong></td>
                            <td class="Form-inputs"><input class="is-small Input" type="text" onkeyup="previewColorUsername()" size="50" name="gradientscolor" placeholder="<?= t('server.user.gradientscolor_placeholder') ?>" id="gradientscolor" value="<?= display_str($Rewards['GradientsColor']) ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasGradientsColor || $HasLimitedColorName || $HasUnlimitedColor) { ?>
                        <tr class="Form-row" id="pers_colornamepreview_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.colornamepreview_title') ?>"><strong><?= t('server.user.colornamepreview') ?></strong></td>
                            <td class="Form-inputs"><a class="Link" id="preview_color_username" href="user.php?id=<?= $UserID ?>"><?= $Username ?></a></td>
                        </tr>
                    <?  } ?>
                </table>
                <table class="Form-rowList" variant="header" id="paranoia_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.st_paranoia') ?>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"></td>
                        <td class="Form-items">
                            <div>
                                <?= t('server.user.st_paranoia_note') ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_lastseen_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.st_lastseen_title') ?>"><strong><?= t('server.user.st_lastactivity') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input id="input-p_lastseen" class="Input" type="checkbox" name="p_lastseen" <?= checked(!in_array('lastseen', $Paranoia)) ?> />
                                <label class="Checkbox-label" for="input-p_lastseen"> <?= t('server.user.st_lastseen') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_presets_tr">
                        <td class="Form-label"><strong><?= t('server.user.st_presets') ?></strong></td>
                        <td class="Form-inputs">
                            <input class="Button" type="button" onclick="ParanoiaResetOff();" value="<?= t('server.user.st_presets_0') ?>" />
                            <input class="Button" type="button" onclick="ParanoiaResetStats();" value="<?= t('server.user.st_presets_1') ?>" />
                            <input class="Button" type="button" onclick="ParanoiaResetOn();" value="<?= t('server.user.st_presets_2') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_donations_tr">
                        <td class="Form-label"><strong><?= t('server.user.st_donations') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="p_donor_stats" name="p_donor_stats" onchange="AlterParanoia();" <?= $DonorIsVisible ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="p_donor_stats"><?= t('server.user.st_donations_0') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="p_donor_heart" name="p_donor_heart" onchange="AlterParanoia();" <?= checked(!in_array('hide_donor_heart', $Paranoia)) ?> />
                                <label class="Checkbox-label" for="p_donor_heart"><?= t('server.user.st_donations_1') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_stats_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.para_stats_title') ?>"><strong><?= t('server.user.para_stats') ?></strong></td>
                        <td class="Form-inputs">
                            <?
                            $UploadChecked = checked(!in_array('uploaded', $Paranoia));
                            $DownloadChecked = checked(!in_array('downloaded', $Paranoia));
                            $RatioChecked = checked(!in_array('ratio', $Paranoia));
                            $BonusCheched = checked(!in_array('bonuspoints', $Paranoia));
                            ?>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_uploaded" type="checkbox" name="p_uploaded" onchange="AlterParanoia();" <?= $UploadChecked ?> />
                                <label class="Checkbox-label" for="input-p_uploaded"> <?= t('server.user.para_uploaded') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_downloaded" type="checkbox" name="p_downloaded" onchange="AlterParanoia();" <?= $DownloadChecked ?> />
                                <label class="Checkbox-label" for="input-p_downloaded"> <?= t('server.user.para_downloaded') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_ratio" type="checkbox" name="p_ratio" onchange="AlterParanoia();" <?= $RatioChecked ?> />
                                <label class="Checkbox-label" for="input-p_ratio"> <?= t('server.user.para_ratio') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_bonuspoints" type="checkbox" name="p_bonuspoints" <?= $BonusCheched ?> />
                                <label class="Checkbox-label" for="input-p_bonuspoints"> <?= t('server.user.para_bonus') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_reqratio_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_reratio') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" id="input-p_requiredratio" type="checkbox" name="p_requiredratio" <?= checked(!in_array('requiredratio', $Paranoia)) ?> />
                                <label class="Checkbox-label" for="input-p_requiredratio"> <?= t('server.user.para_reratio') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_comments_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_comments') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('torrentcomments'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_collstart_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_collstart') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('collages'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_collcontr_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_collcontr') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('collagecontribs'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_reqfill_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_reqfill') ?></strong></td>
                        <td class="Form-inputs">
                            <?
                            $RequestsFilledCountChecked = checked(!in_array('requestsfilled_count', $Paranoia));
                            $RequestsFilledBountyChecked = checked(!in_array('requestsfilled_bounty', $Paranoia));
                            $RequestsFilledListChecked = checked(!in_array('requestsfilled_list', $Paranoia));
                            ?>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_requestsfilled_count" type="checkbox" name="p_requestsfilled_count" onchange="AlterParanoia();" <?= $RequestsFilledCountChecked ?> />
                                <label class="Checkbox-label" for="input-p_requestsfilled_count"> <?= t('server.user.show_count') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_requestsfilled_bounty" type="checkbox" name="p_requestsfilled_bounty" onchange="AlterParanoia();" <?= $RequestsFilledBountyChecked ?> />
                                <label class="Checkbox-label" for="input-p_requestsfilled_bounty"> <?= t('server.user.show_bounty') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_requestsfilled_list" type="checkbox" name="p_requestsfilled_list" onchange="AlterParanoia();" <?= $RequestsFilledListChecked ?> />
                                <label class="Checkbox-label" for="input-p_requestsfilled_list"> <?= t('server.user.show_list') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_reqvote_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_reqvote') ?></strong></td>
                        <td class="Form-inputs">
                            <?
                            $RequestsVotedCountChecked = checked(!in_array('requestsvoted_count', $Paranoia));
                            $RequestsVotedBountyChecked = checked(!in_array('requestsvoted_bounty', $Paranoia));
                            $RequestsVotedListChecked = checked(!in_array('requestsvoted_list', $Paranoia));
                            ?>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_requestsvoted_count" type="checkbox" name="p_requestsvoted_count" onchange="AlterParanoia();" <?= $RequestsVotedCountChecked ?> />
                                <label class="Checkbox-label" for="input-p_requestsvoted_count"> <?= t('server.user.show_count') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_requestsvoted_bounty" type="checkbox" name="p_requestsvoted_bounty" onchange="AlterParanoia();" <?= $RequestsVotedBountyChecked ?> />
                                <label class="Checkbox-label" for="input-p_requestsvoted_bounty"> <?= t('server.user.show_bounty') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" id="input-p_requestsvoted_list" type="checkbox" name="p_requestsvoted_list" onchange="AlterParanoia();" <?= $RequestsVotedListChecked ?> />
                                <label class="Checkbox-label" for="input-p_requestsvoted_list"> <?= t('server.user.show_list') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_upltor_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_upltor') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('uploads'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_original_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_original') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('originals'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_uplunique_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_uplunique') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('uniquegroups'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_torseed_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_torseed') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('seeding'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_torleech_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_torleech') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('leeching'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_torsnatch_tr">
                        <td class="Form-label"><strong><?= t('server.user.para_torsnatch') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('snatched'); ?>
                        </td>
                    </tr>
                    <!--
            <tr class="Form-row" id="para_torsubscr_tr">
                <td class="Form-label" data-tooltip="This option allows other users to subscribe to your torrent uploads."><strong><?= t('server.user.para_torsubscr') ?></strong></td>
                <td class="Form-inputs">
                    <input id="input-p_notifications" type="checkbox" name="p_notifications"<?= checked(!in_array('notifications', $Paranoia)) ?> />
                    <label for="input-p_notifications"> <?= t('server.user.para_torsubscr_note') ?></label>
                </td>
            </tr>
            -->
                    <tr class="Form-row" id="para_emailshowtotc_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.para_emailshowtotc_title') ?>"><strong><?= t('server.user.para_emailshowtotc') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" id="input-p_emailshowtotc" type="checkbox" name="p_emailshowtotc" <?= checked(in_array('emailshowtotc', $Paranoia)) ?> />
                                <label class="Checkbox-label" for="input-p_emailshowtotc"> <?= t('server.user.para_emailshowtotc_label') ?></label>
                            </div>
                        </td>
                    </tr>
                    <?
                    $DB->query("
	SELECT COUNT(UserID)
	FROM users_info
	WHERE Inviter = '$UserID'");
                    list($Invited) = $DB->next_record();
                    ?>
                    <tr class="Form-row" id="para_invited_tr">
                        <td class="Form-label" data-tooltip="This option controls the display of your <?= CONFIG['SITE_NAME'] ?> invitees."><strong><?= t('server.user.para_invited') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" id="input-p_invitedcount" type="checkbox" name="p_invitedcount" <?= checked(!in_array('invitedcount', $Paranoia)) ?> />
                                <label class="Checkbox-label" for="input-p_invitedcount"> <?= t('server.user.show_count') ?></label>
                            </div>
                        </td>
                    </tr>
                    <?
                    $DB->query("
	SELECT COUNT(ArtistID)
	FROM torrents_artists
	WHERE UserID = $UserID");
                    list($ArtistsAdded) = $DB->next_record();
                    ?>
                    <tr class="Form-row" id="para_artistsadded_tr">
                        <td class="Form-label" data-tooltip="<?= t('server.user.para_artistsadded_title') ?>"><strong><?= t('server.user.para_artistsadded') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" id="input-p_artistsadded" type="checkbox" name="p_artistsadded" <?= checked(!in_array('artistsadded', $Paranoia)) ?> />
                                <label class="Checkbox-label" for="input-p_artistsadded"> <?= t('server.user.show_count') ?></label>
                            </div>
                        </td>
                    </tr>
                    <?
                    if (CONFIG['ENABLE_BADGE']) {
                    ?>
                        <tr class="Form-row" id="para_badgedisplay_tr">
                            <td class="Form-label" data-tooltip="para_badgedisplay_title"><strong><?= t('server.user.para_badgedisplay') ?></strong></td>
                            <td class="Form-inputs">
                                <div class="Checkbox">
                                    <input class="Input" id="input-p_badgedisplay" type="checkbox" name="p_badgedisplay" <?= checked(!in_array('badgedisplay', $Paranoia)) ?> />
                                    <label class="Checkbox-label" for="input-p_badgedisplay"> <?= t('server.user.para_badgedisplay_label') ?></label>
                                </div>
                            </td>
                        </tr>
                    <?
                    }
                    ?>
                    <tr class="Form-row" id="para_preview_tr">
                        <td class="Form-inputs"></td>
                        <td class="Form-inputs"><a class="Link" href="#" id="preview_paranoia"><?= t('server.user.para_preview') ?></a></td>
                    </tr>
                </table>
                <table class="Form-rowList" variant="header" id="access_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.st_access') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="acc_resetpk_tr">
                        <td class="Form-label" data-tooltip-interactive="<?= t('server.user.resetpk_title') ?>"><strong><?= t('server.user.resetpk') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" id="input-resetpasskey" type="checkbox" name="resetpasskey" id="resetpasskey" />
                                <label class="Checkbox-label" for="input-resetpasskey"><?= t('server.user.resetpk_note') ?></label>
                            </div>
                        </td>
                    </tr>
                    <? /*
                    <tr class="Form-row" id="acc_irckey_tr">
                        <td class="Form-label"><strong><?= t('server.user.irckey') ?></strong></td>
                        <td class="Form-items">
                            <div class="FormOneLine">
                                <input class="Input" type="text" size="50" name="irckey" id="irckey" value="<?= display_str($IRCKey) ?>" />
                                <input class="Button" type="button" onclick="RandomIRCKey();" value="<?= t('server.user.irckey_title') ?>" />
                            </div>
                            <div>
                                <?= t('server.user.irckey_note_1') ?> <?= CONFIG['BOT_NICK'] ?> <?= t('server.user.irckey_note_2') ?>
                            </div>
                        </td>
                    </tr>
                    /*
                    ?>
                    <? /*
                    <tr class="Form-row" id="acc_tg_tr">
                        <td class="Form-label"><strong><?= t('server.user.tg_binding') ?></strong></td>
                        <td class="Form-items">
                            <div>
                                <span><?= t('server.user.tg_binding_span') ?></span>
                                <ul class="postlist">
                                    <li><?= t('server.user.tg_binding_key') ?><code><?= $Right8Passkey ?></code><?= t('server.user.tg_binding_right8') ?></li>
                                    <li><?= t('server.user.tg_binding_status') ?><span id="tg_bind"><?= $TGID ? t('server.user.tg_binding_binded') : t('server.user.tg_binding_unbind') ?></span> <input id="tg_unbind_button" type="button" onclick="Unbind_tg(<?= $UserID ?>);" value="解绑" style="<?= $TGID ? "" : "display: none;" ?>" /></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    */ ?>
                    <?
                    if (check_perms('users_edit_profiles', $Class)) {
                    ?>
                        <tr class="Form-row" id="acc_email_tr">
                            <td class="Form-label" data-tooltip="<?= t('server.user.st_email_title') ?>"><strong><?= t('server.user.st_email') ?></strong></td>
                            <td class="Form-items">
                                <div>
                                    <input class="Input" type="email" size="50" name="email" id="email" value="<?= display_str($Email) ?>" />
                                </div>
                                <div><?= t('server.user.st_email_note') ?></div>
                            </td>
                        </tr>
                    <?
                    } else {
                    ?>
                        <input type="hidden" name="email" id="email" value="<?= display_str($Email) ?>" />
                    <?
                    }
                    ?>
                    <tr class="Form-row" id="acc_password_tr">
                        <td class="Form-label"><strong><?= t('server.user.st_password') ?></strong></td>
                        <td class="Form-items">
                            <div class="Form-row">
                                <div class="Form-label"><?= t('server.user.st_password_old') ?>:</div>
                                <div class="Form-inputs">
                                    <input class="Input is-small" type="password" size="40" name="cur_pass" id="cur_pass" value="" />
                                </div>
                            </div>
                            <div class="Form-row">
                                <div class="Form-label"><?= t('server.user.st_password_new') ?>:</div>
                                <div class="Form-inputs">
                                    <input class="is-small Input" type="password" size="40" name="new_pass_1" id="new_pass_1" value="" />
                                    <strong id="pass_strength"></strong>
                                </div>
                            </div>
                            <div class="Form-row">
                                <div class="Form-label"><?= t('server.user.st_password_re') ?>:</div>
                                <div class="Form-inputs">
                                    <input class="is-small Input" type="password" size="40" name="new_pass_2" id="new_pass_2" value="" />
                                    <strong id="pass_match"></strong>
                                </div>
                            </div>
                            <div class="Form-row">
                                <div class="Form-label"></div>
                                <div class="Form-inputs">
                                    <div class="setting_description">
                                        <?= t('server.user.st_password_note') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr class="Form-row" id="acc_2fa_tr">
                        <td class="Form-label"><strong><?= t('server.user.2fa') ?></strong></td>
                        <td class="Form-items">
                            <div>
                                <?= t('server.user.st_2fa_note1') ?> <strong class="<?= $TwoFAKey ? 'u-colorSuccess' : 'u-colorWarning'; ?>"><?= $TwoFAKey ? t('server.user.st_2fa_enabled') : t('server.user.st_2fa_disabled'); ?></strong>
                                <a class="Link" href="user.php?action=2fa&do=<?= $TwoFAKey ? 'disable' : 'enable'; ?>&userid=<?= $UserID ?>"><?= t('server.user.st_2fa_note3') . ($TwoFAKey ? t('server.user.st_2fa_disable') : t('server.user.st_2fa_enable')) . t('server.user.st_2fa_after') ?></a>
                            </div>
                        </td>
                    </tr>

                    <tr class="Form-row" id="api_token">
                        <td class="Form-label"><strong><?= t('server.user.api') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" id="input-resetApiKey" type="checkbox" name="resetApiKey" id="resetApiKey" />
                                <label class="Checkbox-label" for="input-resetApiKey"><?= t('server.user.api_note') ?></label>
                            </div>
                            <?php if (isset($apiToken)) { ?>
                                <div class="FormOneLine">
                                    <input class="Input" type="text" size="50" disabled name="api_token_value" id="api_token_value" value="<?php echo "$apiToken" ?>" />
                                    <!-- <input class="Button" type="button" onclick="copy('#api_token_value');" value="<?= t('server.user.api_copy') ?>" /> -->
                                </div>
                            <?php } ?>
                        </td>
                    </tr>

                </table>
            </div>
        </div>
    </form>
</div>
<? View::show_footer([], 'userEdit.js'); ?>