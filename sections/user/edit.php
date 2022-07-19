<?

use Gazelle\Manager\Donation;
use Gazelle\Torrent\TorrentSlotType;

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

function paranoia_level($Setting) {
    global $Paranoia;
    // 0: very paranoid; 1: stats allowed, list disallowed; 2: not paranoid
    return (in_array($Setting . '+', $Paranoia)) ? 0 : (in_array($Setting, $Paranoia) ? 1 : 2);
}

function display_paranoia($FieldName) {
    $Level = paranoia_level($FieldName);
    print "\t\t\t\t\t<input id=\"input-p_{$FieldName}_c\" type=\"checkbox\" name=\"p_{$FieldName}_c\"" . checked($Level >= 1) . " onchange=\"AlterParanoia()\" />\n<label for=\"input-p_{$FieldName}_c\">" . Lang::get('user', 'show_count') . "</label>" . "\n";
    print "\t\t\t\t\t<input id=\"input-p_{$FieldName}_l\" type=\"checkbox\" name=\"p_{$FieldName}_l\"" . checked($Level >= 2) . " onchange=\"AlterParanoia()\" />\n<label for=\"input-p_{$FieldName}_l\">" . Lang::get('user', 'show_list') . "</label>" . "\n";
}

function checked($Checked) {
    return ($Checked ? ' checked="checked"' : '');
}
$SiteOptions = unserialize_array($SiteOptions);
$SiteOptions = array_merge(Users::default_site_options(), $SiteOptions);

View::show_header("$Username &gt; " . Lang::get('user', 'setting'), 'user,jquery-ui,release_sort,password_validate,validate,cssgallery,preview_paranoia,bbcode,user_settings,donor_titles', 'PageUserEdit');

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
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, false, false, false) ?> &gt; <?= Lang::get('user', 'setting') ?></h2>
    </div>
    <form class="edit_form" name="user" id="userform" action="" method="post" autocomplete="off">
        <div class="LayoutMainSidebar">
            <div class="Sidebar LayoutMainSidebar-sidebar">
                <div class="SidebarItemUserSettings SidebarItem Box" id="settings_sections">
                    <div class="SidebarItem-header Box-header">
                        <strong><?= Lang::get('user', 'menu') ?></strong>
                    </div>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <li class="SidebarList-item">
                            <a class="Link" href="#site_appearance_settings" data-tooltip="<?= Lang::get('user', 'st_style_title') ?>">
                                <?= Lang::get('user', 'st_style') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#torrent_settings" data-tooltip="<?= Lang::get('user', 'st_torrents_title') ?>">
                                <?= Lang::get('user', 'st_torrents') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#community_settings" data-tooltip="<?= Lang::get('user', 'st_community_title') ?>">
                                <?= Lang::get('user', 'st_community') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#notification_settings" data-tooltip="<?= Lang::get('user', 'st_notification_title') ?>">
                                <?= Lang::get('user', 'st_notification') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#personal_settings" data-tooltip="<?= Lang::get('user', 'st_personal_title') ?>">
                                <?= Lang::get('user', 'st_personal') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#paranoia_settings" data-tooltip="<?= Lang::get('user', 'st_paranoia_title') ?>">
                                <?= Lang::get('user', 'st_paranoia') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <a class="Link" href="#access_settings" data-tooltip="<?= Lang::get('user', 'st_access_title') ?>">
                                <?= Lang::get('user', 'st_access') ?>
                            </a>
                        </li>
                        <li class="SidebarList-item">
                            <input class="Input" type="text" id="settings_search" onclick="location.href='#'" placeholder="<?= Lang::get('user', 'st_search') ?>" />
                        </li>
                        <li class="SidebarList-item">
                            <input class="Button" type="submit" id="submit" value="<?= Lang::get('user', 'st_save') ?>" />
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
                            <?= Lang::get('user', 'st_style') ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-stylesheet" id="site_style_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'stylesheet_title') ?>"><strong><?= Lang::get('user', 'style') ?></strong></td>
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
                                    <span class="u-toggleAny-show"><?= Lang::get('user', 'show_gallery') ?></span>
                                    <span class="u-toggleAny-hide u-hidden"><?= Lang::get('user', 'hide_gallery') ?></span>
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
                        <td class="Form-label"><strong><?= Lang::get('user', 'theme') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Radio">
                                <input class="Input" type="radio" name="style_theme" value="auto" id="theme-auto" <?= $StyleTheme == 'auto' ? 'checked' : '' ?> />
                                <label class="Radio-label" for="theme-auto">
                                    <?= Lang::get('user', 'theme_auto') ?>
                                </label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="style_theme" value="light" id="theme-light" <?= $StyleTheme == 'light' ? 'checked' : '' ?> />
                                <label class="Radio-label" for="theme-light">
                                    <?= Lang::get('user', 'theme_light') ?>
                                </label>
                            </div>
                            <div class="Input">
                                <input class="Input" type="radio" name="style_theme" value="dark" id="theme-dark" <?= $StyleTheme == 'dark' ? 'checked' : '' ?> />
                                <label class="Radio-label" for="theme-dark">
                                    <?= Lang::get('user', 'theme_dark') ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="site_extstyle_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'ex_style_title') ?>"><strong><?= Lang::get('user', 'ex_style') ?></strong></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" size="40" name="styleurl" id="styleurl" value="<?= display_str($StyleURL) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row" id="site_tooltips_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'style_tool_title') ?>"><strong><?= Lang::get('user', 'style_tool') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="usetooltipster" id="usetooltipster" <?= !isset($SiteOptions['Tooltipster']) || $SiteOptions['Tooltipster'] ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="usetooltipster"><?= Lang::get('user', 'enabled') ?></label>
                            </div>
                        </td>
                    </tr>
                    <? if (check_perms('users_mod')) { ?>
                        <tr class="Form-row" id="site_autostats_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'base_stats_title') ?>"><strong><?= Lang::get('user', 'base_stats') ?></strong></td>
                            <td class="Form-inputs"><input id="input-autoload_comm_stats" type="checkbox" name="autoload_comm_stats" <? Format::selected('AutoloadCommStats', 1, 'checked', $SiteOptions); ?> />
                                <label for="input-autoload_comm_stats"><?= Lang::get('user', 'base_stats_note') ?></label>
                            </td>
                        </tr>
                    <?  } ?>
                </table>
                <table class="Form-rowList" variant="header" id="torrent_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= Lang::get('user', 'st_torrents') ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-torrentTitle" id="custom_torrent_title">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'SettingTorrentTitleTooltip') ?>">
                            <strong>
                                <?= Lang::get('user', 'SettingTorrentTitle') ?>
                            </strong>
                        </td>
                        <td class="Form-items">
                            <div>
                                <div class="Checkbox">
                                    <? $Checked = $SettingTorrentTitle['Alternative'] ? 'checked' : '' ?>
                                    <input class="Input" type="checkbox" name="settingTorrentTitleAlternative" id="same_width" <?= $Checked ?> />
                                    <label class="Checkbox-label" for="same_width">
                                        <?= Lang::get('user', 'SettingAlternative') ?>
                                    </label>
                                </div>
                                <div class="Checkbox">
                                    <? $Checked = $SettingTorrentTitle['ReleaseGroup'] ? 'checked' : '' ?>
                                    <input class="Input" type="checkbox" name="settingTorrentTitleReleaseGroup" id="release_group" <?= $Checked ?> />
                                    <label class="Checkbox-label" for="release_group">
                                        <?= Lang::get('user', 'SettingReleaseGroup') ?>
                                    </label>
                                </div>
                            </div>
                            <? if (CONFIG['IS_DEV']) { ?>
                                <div>
                                    <input id="SettingTorrentTitleInput" type="hidden" name="settingTorrentTitleItems" value='<?= implode(',', $SettingTorrentTitle['Items'])  ?>' />
                                    <?= Torrents::settingTorrentTitle(
                                        $SettingTorrentTitle,
                                        [
                                            'Class' => 'TorrentTitle--standalone is-edit'
                                        ]
                                    ) ?>
                                    <button class="Button" size="tiny" onclick="globalapp.userEditSettingTorrentTitleReset()" type="submit">
                                        <?= Lang::get('user', 'reset') ?>
                                    </button>
                                </div>
                            <? } ?>
                            <?
                            $TableTorrentClass = $SettingTorrentTitle['Alternative'] ? 'is-alternative' : '';
                            ?>
                            <div class="TorrentTitle-previews TableTorrent TableTorrent--preview <?= $TableTorrentClass ?>">
                                <?
                                $Previews = [
                                    ['Codec' => 'x265', 'Source' => 'WEB', 'Resolution' => '720p', 'Container' => 'MKV', 'Processing' => 'Encode', 'Slot' => TorrentSlotType::EnglishQuality, 'RemasterTitle' => 'dolby_vision / dolby_atmos / masters_of_cinema', 'ReleaseGroup' => 'HANDJOB'],
                                    ['Codec' => 'x265', 'Source' => 'WEB', 'Resolution' => '720p', 'Container' => 'MKV', 'Processing' => 'Encode', 'Slot' => TorrentSlotType::ChineseQuality, 'RemasterTitle' => 'the_criterion_collection', 'ReleaseGroup' => 'HANDJOB'],
                                    ['Codec' => 'x265', 'Source' => 'WEB', 'Resolution' => '720p', 'Container' => 'MKV', 'Processing' => 'Encode', 'Slot' => TorrentSlotType::Feature, 'RemasterTitle' => 'warner_archive_collection', 'ReleaseGroup' => 'HANDJOB'],
                                    ['Codec' => 'x265', 'Source' => 'Blu-ray', 'Resolution' => '720p', 'Container' => 'MKV', 'Processing' => 'Remux', 'Slot' => TorrentSlotType::Remux, 'ReleaseGroup' => 'MZABI'],
                                    ['Codec' => 'x265', 'Source' => 'Blu-ray', 'Resolution' => '720p', 'Container' => 'm2ts', 'Processing' => 'BD50', 'Slot' => TorrentSlotType::DIY,  'ReleaseGroup' => 'Geek'],
                                    ['Codec' => 'x265', 'Source' => 'Blu-ray', 'Resolution' => '720p', 'Container' => 'm2ts', 'Processing' => 'BD50', 'Slot' => TorrentSlotType::Untouched, 'ReleaseGroup' => 'Geek'],
                                    ['Codec' => 'x265', 'Source' => 'WEB', 'Resolution' => '2160p', 'Container' => 'MKV', 'Processing' => 'Encode', 'Slot' => TorrentSlotType::ChineseQuality],
                                    ['Codec' => 'x265', 'Source' => 'Blu-ray', 'Resolution' => '2160p', 'Container' => 'MKV', 'Processing' => 'Remux', 'Slot' => TorrentSlotType::Remux],
                                    ['Codec' => 'x265', 'Source' => 'Blu-ray', 'Resolution' => '2160p', 'Container' => 'm2ts', 'Processing' => 'BD50', 'Slot' => TorrentSlotType::Untouched],
                                ];
                                ?>
                                <? foreach ($Previews as $Preview) { ?>
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
                    <? if (check_perms('site_advanced_search')) { ?>
                        <tr class="Form-row" id="tor_searchtype_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'default_search_title') ?>"><strong><?= Lang::get('user', 'default_search') ?></strong></td>
                            <td class="Form-inputs">
                                <div class="Radio">
                                    <input class="Input" type="radio" name="searchtype" id="search_type_simple" value="0" <?= $SiteOptions['SearchType'] == 0 ? ' checked="checked"' : '' ?> />
                                    <label class="Radio-label" for="search_type_simple">
                                        <?= Lang::get('user', 'base') ?>
                                    </label>
                                </div>
                                <div class="Radio">
                                    <input class="Input" type="radio" name="searchtype" id="search_type_advanced" value="1" <?= $SiteOptions['SearchType'] == 1 ? ' checked="checked"' : '' ?> />
                                    <label class="Radio-label" for="search_type_advanced">
                                        <?= Lang::get('user', 'advanced') ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <? } ?>
                    <tr class="Form-row" id="tor_group_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'torrents_group_title') ?>"><strong><?= Lang::get('user', 'torrents_group') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="option_group">
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="disablegrouping" id="disablegrouping" <?= $SiteOptions['DisableGrouping2'] == 0 ? ' checked="checked"' : '' ?> />
                                    <label class="Checkbox-label" for="disablegrouping"><?= Lang::get('user', 'torrents_group_tool') ?></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_gdisp_search_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'torrents_group_display_title') ?>"><strong><?= Lang::get('user', 'torrents_group_display') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Radio">
                                <input class="Input" type="radio" name="torrentgrouping" id="torrent_grouping_open" value="0" <?= $SiteOptions['TorrentGrouping'] == 0 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="torrent_grouping_open">
                                    <?= Lang::get('user', 'enabled') ?>
                                </label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="torrentgrouping" id="torrent_grouping_closed" value="1" <?= $SiteOptions['TorrentGrouping'] == 1 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="torrent_grouping_closed">
                                    <?= Lang::get('user', 'disabled') ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_gdisp_artist_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'torrents_artists_display_title') ?>"><strong><?= Lang::get('user', 'torrents_artists_display') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Radio">
                                <input class="Input" type="radio" name="discogview" id="discog_view_open" value="0" <?= $SiteOptions['DiscogView'] == 0 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="discog_view_open">
                                    <?= Lang::get('user', 'enabled') ?>
                                </label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="discogview" id="discog_view_closed" value="1" <?= $SiteOptions['DiscogView'] == 1 ? ' checked="checked"' : '' ?> />
                                <label class="Radio-label" for="discog_view_closed">
                                    <?= Lang::get('user', 'disabled') ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_reltype_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'torrents_artists_display_type_title') ?>"><strong><?= Lang::get('user', 'torrents_artists_display_type') ?></strong></td>
                        <td class="Form-items">
                            <div>
                                <a class="Link" href="#" id="toggle_sortable"><?= Lang::get('user', 'expand') ?></a>
                            </div>
                            <div id="sortable_container" style="display: none;">
                                <a class="Link" href="#" id="reset_sortable"><?= Lang::get('user', 'reset_to_default') ?></a>
                                <p><?= Lang::get('user', 'drag_and_drop_change_order') ?></p>
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
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'torrents_snatched_title') ?>"><strong><?= Lang::get('user', 'torrents_snatched') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="showsnatched" id="showsnatched" <?= !empty($SiteOptions['ShowSnatched']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="showsnatched"><?= Lang::get('user', 'enabled') ?></label>
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
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'torrents_cover_title') ?>"><strong><?= Lang::get('user', 'torrents_cover') ?></strong></td>
                        <td class="Form-inputs">
                            <input type="hidden" name="coverart" value="" />
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="coverart" id="coverart" <?= $SiteOptions['CoverArt'] ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="coverart"><?= Lang::get('user', 'enabled') ?></label>
                            </div>
                        </td>
                    </tr>

                    <tr class="Form-row" id="tor_cover_coll_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'cover_coll_title') ?>"><strong><?= Lang::get('user', 'cover_coll') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="collagecovers" id="collagecovers">
                                <option class="Select-option" value="10" <?= $SiteOptions['CollageCovers'] == 10 ? ' selected="selected"' : '' ?>>10</option>
                                <option class="Select-option" value="25" <?= ($SiteOptions['CollageCovers'] == 25 || !isset($SiteOptions['CollageCovers'])) ? ' selected="selected"' : '' ?>>25 (<?= Lang::get('user', 'default') ?>)</option>
                                <option class="Select-option" value="50" <?= $SiteOptions['CollageCovers'] == 50 ? ' selected="selected"' : '' ?>>50</option>
                                <option class="Select-option" value="100" <?= $SiteOptions['CollageCovers'] == 100 ? ' selected="selected"' : '' ?>>100</option>
                                <option class="Select-option" value="1000000" <?= $SiteOptions['CollageCovers'] == 1000000 ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'collage_covers_all') ?></option>
                                <option class="Select-option" value="0" <?= ($SiteOptions['CollageCovers'] === 0 || (!isset($SiteOptions['CollageCovers']) && $SiteOptions['HideCollage'])) ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'collage_covers_none') ?></option>
                            </select>
                            <?= Lang::get('user', 'cover_coll_number') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_showfilt_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'filt_tr_title') ?>"><strong><?= Lang::get('user', 'filt_tr') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="showtfilter" id="showtfilter" <?= (!isset($SiteOptions['ShowTorFilter']) || $SiteOptions['ShowTorFilter'] ? ' checked="checked"' : '') ?> />
                                <label class="Checkbox-label" for="showtfilter"><?= Lang::get('user', 'filt_tr_show') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="showtags" id="showtags" <? Format::selected('ShowTags', 1, 'checked', $SiteOptions); ?> />
                                <label class="Checkbox-label" for="showtags"><?= Lang::get('user', 'filt_tr_show_tags') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_autocomp_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'autocomp_title') ?>"><strong><?= Lang::get('user', 'autocomp') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="autocomplete">
                                <option class="Select-option" value="0" <?= empty($SiteOptions['AutoComplete']) ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'autocomp_0') ?></option>
                                <option class="Select-option" value="2" <?= $SiteOptions['AutoComplete'] === 2 ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'autocomp_2') ?></option>
                                <option class="Select-option" value="1" <?= $SiteOptions['AutoComplete'] === 1 ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'autocomp_1') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_voting_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'voting_title') ?>"><strong><?= Lang::get('user', 'voting') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="novotelinks" id="novotelinks" <?= !empty($SiteOptions['NoVoteLinks']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="novotelinks"><?= Lang::get('user', 'voting_disable') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="tor_dltext_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'dltext_title') ?>"><strong><?= Lang::get('user', 'dltext') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="downloadalt" id="downloadalt" <?= $DownloadAlt ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="downloadalt"><?= Lang::get('user', 'dltext_tr') ?></label>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="Form-rowList" variant="header" id="community_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= Lang::get('user', 'st_community') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_ppp_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'ppp_title') ?>"><strong><?= Lang::get('user', 'ppp') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="postsperpage" id="postsperpage">
                                <option class="Select-option" value="25" <?= $SiteOptions['PostsPerPage'] == 25 ? ' selected="selected"' : '' ?>>25 (<?= Lang::get('user', 'default') ?>)</option>
                                <option class="Select-option" value="50" <?= $SiteOptions['PostsPerPage'] == 50 ? ' selected="selected"' : '' ?>>50</option>
                                <option class="Select-option" value="100" <?= $SiteOptions['PostsPerPage'] == 100 ? ' selected="selected"' : '' ?>>100</option>
                            </select>
                            <?= Lang::get('user', 'ppp_number') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_inbsort_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'inbsort_title') ?>"><strong><?= Lang::get('user', 'inbsort') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="list_unread_pms_first" id="list_unread_pms_first" <?= !empty($SiteOptions['ListUnreadPMsFirst']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="list_unread_pms_first"><?= Lang::get('user', 'inbsort_un') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_emot_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'emot_title') ?>"><strong><?= Lang::get('user', 'emot') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="disablesmileys" id="disablesmileys" <?= !empty($SiteOptions['DisableSmileys']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="disablesmileys"><?= Lang::get('user', 'emot_disable') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_mature_tr">
                        <td class="Form-label" data-tooltip-interactive="<?= Lang::get('user', 'mature_title') ?>"><strong><?= Lang::get('user', 'mature') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="enablematurecontent" id="enablematurecontent" <?= !empty($SiteOptions['EnableMatureContent']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="enablematurecontent"><?= Lang::get('user', 'mature_show') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="comm_avatars_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'avatars_title') ?>"><strong><?= Lang::get('user', 'avatars') ?></strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="disableavatars" id="disableavatars" onchange="ToggleIdenticons();">
                                <option class="Select-option" value="1" <?= $SiteOptions['DisableAvatars'] == 1 ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'disabled') ?></option>
                                <option class="Select-option" value="0" <?= $SiteOptions['DisableAvatars'] == 0 ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'avatars_0') ?></option>
                                <option class="Select-option" value="2" <?= $SiteOptions['DisableAvatars'] == 2 ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'avatars_2') ?></option>
                                <option class="Select-option" value="3" <?= $SiteOptions['DisableAvatars'] == 3 ? ' selected="selected"' : '' ?>><?= Lang::get('user', 'avatars_3') ?></option>
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
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'autosave_title') ?>"><strong><?= Lang::get('user', 'autosave') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="disableautosave" id="disableautosave" <?= !empty($SiteOptions['DisableAutoSave']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="disableautosave"><?= Lang::get('user', 'disabled') ?></label>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="Form-rowList" variant="header" id="notification_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= Lang::get('user', 'st_notification') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_autosubscribe_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'autosubscribe_title') ?>"><strong><?= Lang::get('user', 'autosubscribe') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="autosubscribe" id="autosubscribe" <?= !empty($SiteOptions['AutoSubscribe']) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="autosubscribe"><?= Lang::get('user', 'enabled') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_requests_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'autosubscribe_your_request_title') ?>"><strong><?= Lang::get('user', 'autosubscribe_your_request') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="requestsalerts" id="requestsalerts" <?= checked($RequestsAlerts) ?> />
                                <label class="Checkbox-label" for="requestsalerts"><?= Lang::get('user', 'enabled') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_notifyondeleteseeding_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'notifyondeleteseeding_title') ?>"><strong><?= Lang::get('user', 'notifyondeleteseeding') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="notifyondeleteseeding" id="notifyondeleteseeding" <?= !empty($NotifyOnDeleteSeeding) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="notifyondeleteseeding"><?= Lang::get('user', 'notifyondeleteseeding_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_notifyondeletesnatched_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'notifyondeletesnatched_title') ?>"><strong><?= Lang::get('user', 'notifyondeletesnatched') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="notifyondeletesnatched" id="notifyondeletesnatched" <?= !empty($NotifyOnDeleteSnatched) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="notifyondeletesnatched"><?= Lang::get('user', 'notifyondeletesnatched_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_notifyondeletedownloaded_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'notifyondeletedownloaded_title') ?>"><strong><?= Lang::get('user', 'notifyondeletedownloaded') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="notifyondeletedownloaded" id="notifyondeletedownloaded" <?= !empty($NotifyOnDeleteDownloaded) ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="notifyondeletedownloaded"><?= Lang::get('user', 'notifyondeletedownloaded_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_unseeded_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'unseeded_title') ?>"><strong><?= Lang::get('user', 'unseeded') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="unseededalerts" id="unseededalerts" <?= checked($UnseededAlerts) ?> />
                                <label class="Checkbox-label" for="unseededalerts"><?= Lang::get('user', 'unseeded_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="notif_reported_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'reported_title') ?>"><strong><?= Lang::get('user', 'reported') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="reportedalerts" id="reportedalerts" <?= checked($ReportedAlerts) ?> />
                                <label class="Checkbox-label" for="reportedalerts"><?= Lang::get('user', 'reported_checked') ?></label>
                            </div>
                        </td>
                    </tr>
                    <? NotificationsManagerView::render_settings(NotificationsManager::get_settings($UserID)); ?>
                </table>
                <table class="Form-rowList" variant="header" id="personal_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= Lang::get('user', 'st_personal') ?>
                        </td>
                    </tr>
                    <script>
                        function avatar_upload(url) {
                            $("#avatar").val(url)
                        }

                        function avatar_2_upload(url) {
                            $("#second_avatar").val(url)
                        }
                    </script>
                    <tr class="Form-row" id="pers_avatar_tr">
                        <td class="Form-label" data-tooltip-interactive="<?= Lang::get('user', 'st_avatar_title') ?>"><strong><?= Lang::get('user', 'st_avatar') ?></strong></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" size="50" name="avatar" id="avatar" value="<?= display_str($Avatar) ?>" readonly />
                            <input class="Button" type="button" onclick="globalapp.UploadImage(false, avatar_upload)" value="上传">
                        </td>
                    </tr>
                    <? if ($HasSecondAvatar) { ?>
                        <tr class="Form-row" id="pers_avatar2_tr">
                            <td class="Form-label" data-tooltip-interactive="<?= Lang::get('user', 'st_avatar_2_title') ?>"><strong><?= Lang::get('user', 'st_avatar_2') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="second_avatar" id="second_avatar" value="<?= $Rewards['SecondAvatar'] ?>" readonly />
                                <input class="Button" type="button" onclick="globalapp.UploadImage(false, avatar_2_upload)" value="上传">
                            </td>
                        </tr>
                    <?  }
                    if ($HasAvatarMouseOverText) { ?>
                        <tr class="Form-row" id="pers_avatarhover_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_avatarhover_title') ?>"><strong><?= Lang::get('user', 'st_avatarhover') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="avatar_mouse_over_text" id="avatar_mouse_over_text" value="<?= $Rewards['AvatarMouseOverText'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasDonorIconMouseOverText) { ?>
                        <tr class="Form-row" id="pers_donorhover_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_donorhover_title') ?>"><strong><?= Lang::get('user', 'st_donorhover') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="donor_icon_mouse_over_text" id="donor_icon_mouse_over_text" value="<?= $Rewards['IconMouseOverText'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasDonorIconLink) { ?>
                        <tr class="Form-row" id="pers_donorlink_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_donorlink_title') ?>"><strong><?= Lang::get('user', 'st_donorlink') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="donor_icon_link" id="donor_icon_link" value="<?= $Rewards['CustomIconLink'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasCustomDonorIcon) { ?>
                        <tr class="Form-row" id="pers_donoricon_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_donoricon_title') ?>"><strong><?= Lang::get('user', 'st_donoricon') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="donor_icon_custom_url" id="donor_icon_custom_url" value="<?= $Rewards['CustomIcon'] ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasDonorForum) { ?>
                        <tr class="Form-row" id="pers_donorforum_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_donorforum_title') ?>"><strong><?= Lang::get('user', 'st_donorforum') ?></strong></td>
                            <td class="Form-items">
                                <div>
                                    <label>
                                        <strong><?= Lang::get('user', 'donorforum_1') ?>:</strong>
                                        <input class="Input" type="text" id="input-donor_title_prefix" size="30" maxlength="30" name="donor_title_prefix" id="donor_title_prefix" value="<?= $DonorTitles['Prefix'] ?>" /></label>
                                </div>
                                <div>
                                    <label for="input-donor_title_suffix"><strong><?= Lang::get('user', 'donorforum_2') ?>:</strong>
                                        <input class="Input" type="text" id="input-donor_title_suffix" size="30" maxlength="30" name="donor_title_suffix" id="donor_title_suffix" value="<?= $DonorTitles['Suffix'] ?>" /></label>
                                </div>
                                <div>
                                    <label for="input-donor_title_comma"><strong><?= Lang::get('user', 'donorforum_3') ?>:</strong>
                                        <input id="input-donor_title_comma" type="checkbox" id="donor_title_comma" name="donor_title_comma" <?= !$DonorTitles['UseComma'] ? ' checked="checked"' : '' ?> /></label>
                                </div>
                                <div>
                                    <strong><?= Lang::get('user', 'donorforum_4') ?>:</strong> <span id="donor_title_prefix_preview"></span><?= $Username ?><span id="donor_title_comma_preview">, </span><span id="donor_title_suffix_preview"></span>
                                </div>
                            </td>
                        </tr>
                    <?  } ?>

                    <tr class="Form-row" id="pers_proftitle_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_proftitle1_title') ?>"><strong><?= Lang::get('user', 'st_proftitle1') ?></strong></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" size="50" name="profile_title" id="profile_title" value="<?= display_str($InfoTitle) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row" id="pers_profinfo_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_profinfo1_title') ?>"><strong><?= Lang::get('user', 'st_profinfo1') ?></strong></td>
                        <td class="Form-items">
                            <?php $textarea = new TEXTAREA_PREVIEW('info', 'info', display_str($Info), 40, 8); ?>
                        </td>
                    </tr>
                    <!-- Excuse this numbering confusion, we start numbering our profile info/titles at 1 in the donor_rewards table -->
                    <? if ($HasProfileInfo1) { ?>
                        <tr class="Form-row" id="pers_proftitle2_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_proftitle2_title') ?>"><strong><?= Lang::get('user', 'st_proftitle2') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="profile_title_1" id="profile_title_1" value="<?= display_str($ProfileRewards['ProfileInfoTitle1']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo2_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_profinfo2_title') ?>"><strong><?= Lang::get('user', 'st_profinfo2') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_1', 'profile_info_1', display_str($ProfileRewards['ProfileInfo1']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasProfileInfo2) { ?>
                        <tr class="Form-row" id="pers_proftitle3_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_proftitle3_title') ?>"><strong><?= Lang::get('user', 'st_proftitle3') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="profile_title_2" id="profile_title_2" value="<?= display_str($ProfileRewards['ProfileInfoTitle2']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo3_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_profinfo3_title') ?>"><strong><?= Lang::get('user', 'st_profinfo3') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_2', 'profile_info_2', display_str($ProfileRewards['ProfileInfo2']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasProfileInfo3) { ?>
                        <tr class="Form-row" id="pers_proftitle4_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_proftitle4_title') ?>"><strong><?= Lang::get('user', 'st_proftitle4') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" size="50" name="profile_title_3" id="profile_title_3" value="<?= display_str($ProfileRewards['ProfileInfoTitle3']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo4_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_profinfo4_title') ?>"><strong><?= Lang::get('user', 'st_profinfo4') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_3', 'profile_info_3', display_str($ProfileRewards['ProfileInfo3']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasProfileInfo4) { ?>
                        <tr class="Form-row" id="pers_proftitle5_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_proftitle5_title') ?>"><strong><?= Lang::get('user', 'st_proftitle5') ?></strong></td>
                            <td class="Form-inputs"><input class="Input" type="text" size="50" name="profile_title_4" id="profile_title_4" value="<?= display_str($ProfileRewards['ProfileInfoTitle4']) ?>" />
                            </td>
                        </tr>
                        <tr class="Form-row" id="pers_profinfo5_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_profinfo5_title') ?>"><strong><?= Lang::get('user', 'st_profinfo5') ?></strong></td>
                            <td class="Form-items">
                                <?php $textarea = new TEXTAREA_PREVIEW('profile_info_4', 'profile_info_4', display_str($ProfileRewards['ProfileInfo4']), 40, 8); ?>
                            </td>
                        </tr>
                    <?  }
                    if ($HasUnlimitedColor) { ?>
                        <tr class="Form-row" id="pers_unlimitedcolor_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'unlimitedcolor_title') ?>"><strong><?= Lang::get('user', 'unlimitedcolor') ?></strong></td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" onkeyup="previewColorUsername()" size="50" name="unlimitedcolor" placeholder="<?= Lang::get('user', 'unlimitedcolor_placeholder') ?>" id="unlimitedcolor" value="<?= display_str($Rewards['ColorUsername']) ?>" />
                            </td>
                        </tr>
                    <?  } else if ($HasLimitedColorName) {
                        $LimitedColors = [
                            "#ed5a65" => Lang::get('user', 'limitedcolor_red'),
                            "#2474b5" => Lang::get('user', 'limitedcolor_blue'),
                            "#428675" => Lang::get('user', 'limitedcolor_green'),
                            "#f2ce2b" => Lang::get('user', 'limitedcolor_yellow'),
                            "#fb8b05" => Lang::get('user', 'limitedcolor_orange'),
                            "#8b2671" => Lang::get('user', 'limitedcolor_purple')
                        ];
                    ?>
                        <tr class="Form-row" id="pers_limitedcolorname_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'limitedcolor_title') ?>"><strong><?= Lang::get('user', 'limitedcolor') ?></strong></td>
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
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'gradientscolor_title') ?>"><strong><?= Lang::get('user', 'gradientscolor') ?></strong></td>
                            <td class="Form-inputs"><input class="Input" type="text" onkeyup="previewColorUsername()" size="50" name="gradientscolor" placeholder="<?= Lang::get('user', 'gradientscolor_placeholder') ?>" id="gradientscolor" value="<?= display_str($Rewards['GradientsColor']) ?>" />
                            </td>
                        </tr>
                    <?  }
                    if ($HasGradientsColor || $HasLimitedColorName || $HasUnlimitedColor) { ?>
                        <tr class="Form-row" id="pers_colornamepreview_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'colornamepreview_title') ?>"><strong><?= Lang::get('user', 'colornamepreview') ?></strong></td>
                            <td class="Form-inputs"><a class="Link" id="preview_color_username" href="user.php?id=<?= $UserID ?>"><?= $Username ?></a></td>
                        </tr>
                    <?  } ?>
                </table>
                <table class="Form-rowList" variant="header" id="paranoia_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= Lang::get('user', 'st_paranoia') ?>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"></td>
                        <td class="Form-items">
                            <div>
                                <?= Lang::get('user', 'st_paranoia_note') ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_lastseen_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_lastseen_title') ?>"><strong><?= Lang::get('user', 'st_lastactivity') ?></strong></td>
                        <td class="Form-inputs">
                            <input id="input-p_lastseen" type="checkbox" name="p_lastseen" <?= checked(!in_array('lastseen', $Paranoia)) ?> />
                            <label for="input-p_lastseen"> <?= Lang::get('user', 'st_lastseen') ?></label>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_presets_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'st_presets') ?></strong></td>
                        <td class="Form-inputs">
                            <input class="Button" type="button" onclick="ParanoiaResetOff();" value="<?= Lang::get('user', 'st_presets_0') ?>" />
                            <input class="Button" type="button" onclick="ParanoiaResetStats();" value="<?= Lang::get('user', 'st_presets_1') ?>" />
                            <input class="Button" type="button" onclick="ParanoiaResetOn();" value="<?= Lang::get('user', 'st_presets_2') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_donations_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'st_donations') ?></strong></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="p_donor_stats" name="p_donor_stats" onchange="AlterParanoia();" <?= $DonorIsVisible ? ' checked="checked"' : '' ?> />
                                <label class="Checkbox-label" for="p_donor_stats"><?= Lang::get('user', 'st_donations_0') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="p_donor_heart" name="p_donor_heart" onchange="AlterParanoia();" <?= checked(!in_array('hide_donor_heart', $Paranoia)) ?> />
                                <label class="Checkbox-label" for="p_donor_heart"><?= Lang::get('user', 'st_donations_1') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_stats_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'para_stats_title') ?>"><strong><?= Lang::get('user', 'para_stats') ?></strong></td>
                        <td class="Form-inputs">
                            <?
                            $UploadChecked = checked(!in_array('uploaded', $Paranoia));
                            $DownloadChecked = checked(!in_array('downloaded', $Paranoia));
                            $RatioChecked = checked(!in_array('ratio', $Paranoia));
                            $BonusCheched = checked(!in_array('bonuspoints', $Paranoia));
                            ?>
                            <input id="input-p_uploaded" type="checkbox" name="p_uploaded" onchange="AlterParanoia();" <?= $UploadChecked ?> />
                            <label for="input-p_uploaded"> <?= Lang::get('user', 'para_uploaded') ?></label>
                            <input id="input-p_downloaded" type="checkbox" name="p_downloaded" onchange="AlterParanoia();" <?= $DownloadChecked ?> />
                            <label for="input-p_downloaded"> <?= Lang::get('user', 'para_downloaded') ?></label>
                            <input id="input-p_ratio" type="checkbox" name="p_ratio" onchange="AlterParanoia();" <?= $RatioChecked ?> />
                            <label for="input-p_ratio"> <?= Lang::get('user', 'para_ratio') ?></label>
                            <input id="input-p_bonuspoints" type="checkbox" name="p_bonuspoints" <?= $BonusCheched ?> />
                            <label for="input-p_bonuspoints"> <?= Lang::get('user', 'para_bonus') ?></label>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_reqratio_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_reratio') ?></strong></td>
                        <td class="Form-inputs">
                            <input id="input-p_requiredratio" type="checkbox" name="p_requiredratio" <?= checked(!in_array('requiredratio', $Paranoia)) ?> />
                            <label for="input-p_requiredratio"> <?= Lang::get('user', 'para_reratio') ?></label>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_comments_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_comments') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('torrentcomments'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_collstart_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_collstart') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('collages'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_collcontr_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_collcontr') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('collagecontribs'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_reqfill_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_reqfill') ?></strong></td>
                        <td class="Form-inputs">
                            <?
                            $RequestsFilledCountChecked = checked(!in_array('requestsfilled_count', $Paranoia));
                            $RequestsFilledBountyChecked = checked(!in_array('requestsfilled_bounty', $Paranoia));
                            $RequestsFilledListChecked = checked(!in_array('requestsfilled_list', $Paranoia));
                            ?>
                            <input id="input-p_requestsfilled_count" type="checkbox" name="p_requestsfilled_count" onchange="AlterParanoia();" <?= $RequestsFilledCountChecked ?> />
                            <label for="input-p_requestsfilled_count"> <?= Lang::get('user', 'show_count') ?></label>
                            <input id="input-p_requestsfilled_bounty" type="checkbox" name="p_requestsfilled_bounty" onchange="AlterParanoia();" <?= $RequestsFilledBountyChecked ?> />
                            <label for="input-p_requestsfilled_bounty"> <?= Lang::get('user', 'show_bounty') ?></label>
                            <input id="input-p_requestsfilled_list" type="checkbox" name="p_requestsfilled_list" onchange="AlterParanoia();" <?= $RequestsFilledListChecked ?> />
                            <label for="input-p_requestsfilled_list"> <?= Lang::get('user', 'show_list') ?></label>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_reqvote_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_reqvote') ?></strong></td>
                        <td class="Form-inputs">
                            <?
                            $RequestsVotedCountChecked = checked(!in_array('requestsvoted_count', $Paranoia));
                            $RequestsVotedBountyChecked = checked(!in_array('requestsvoted_bounty', $Paranoia));
                            $RequestsVotedListChecked = checked(!in_array('requestsvoted_list', $Paranoia));
                            ?>
                            <input id="input-p_requestsvoted_count" type="checkbox" name="p_requestsvoted_count" onchange="AlterParanoia();" <?= $RequestsVotedCountChecked ?> />
                            <label for="input-p_requestsvoted_count"> <?= Lang::get('user', 'show_count') ?></label>
                            <input id="input-p_requestsvoted_bounty" type="checkbox" name="p_requestsvoted_bounty" onchange="AlterParanoia();" <?= $RequestsVotedBountyChecked ?> />
                            <label for="input-p_requestsvoted_bounty"> <?= Lang::get('user', 'show_bounty') ?></label>
                            <input id="input-p_requestsvoted_list" type="checkbox" name="p_requestsvoted_list" onchange="AlterParanoia();" <?= $RequestsVotedListChecked ?> />
                            <label for="input-p_requestsvoted_list"> <?= Lang::get('user', 'show_list') ?></label>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_upltor_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_upltor') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('uploads'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_original_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_original') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('originals'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_uplunique_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_uplunique') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('uniquegroups'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_torseed_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_torseed') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('seeding'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_torleech_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_torleech') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('leeching'); ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="para_torsnatch_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'para_torsnatch') ?></strong></td>
                        <td class="Form-inputs">
                            <? display_paranoia('snatched'); ?>
                        </td>
                    </tr>
                    <!--
            <tr class="Form-row" id="para_torsubscr_tr">
                <td class="Form-label" data-tooltip="This option allows other users to subscribe to your torrent uploads."><strong><?= Lang::get('user', 'para_torsubscr') ?></strong></td>
                <td class="Form-inputs">
                    <input id="input-p_notifications" type="checkbox" name="p_notifications"<?= checked(!in_array('notifications', $Paranoia)) ?> />
                    <label for="input-p_notifications"> <?= Lang::get('user', 'para_torsubscr_note') ?></label>
                </td>
            </tr>
            -->
                    <tr class="Form-row" id="para_emailshowtotc_tr">
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'para_emailshowtotc_title') ?>"><strong><?= Lang::get('user', 'para_emailshowtotc') ?></strong></td>
                        <td class="Form-inputs">
                            <input id="input-p_emailshowtotc" type="checkbox" name="p_emailshowtotc" <?= checked(in_array('emailshowtotc', $Paranoia)) ?> />
                            <label for="input-p_emailshowtotc"> <?= Lang::get('user', 'para_emailshowtotc_label') ?></label>
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
                        <td class="Form-label" data-tooltip="This option controls the display of your <?= CONFIG['SITE_NAME'] ?> invitees."><strong><?= Lang::get('user', 'para_invited') ?></strong></td>
                        <td class="Form-inputs">
                            <input id="input-p_invitedcount" type="checkbox" name="p_invitedcount" <?= checked(!in_array('invitedcount', $Paranoia)) ?> />
                            <label for="input-p_invitedcount"> <?= Lang::get('user', 'show_count') ?></label>
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
                        <td class="Form-label" data-tooltip="<?= Lang::get('user', 'para_artistsadded_title') ?>"><strong><?= Lang::get('user', 'para_artistsadded') ?></strong></td>
                        <td class="Form-inputs">
                            <input id="input-p_artistsadded" type="checkbox" name="p_artistsadded" <?= checked(!in_array('artistsadded', $Paranoia)) ?> />
                            <label for="input-p_artistsadded"> <?= Lang::get('user', 'show_count') ?></label>
                        </td>
                    </tr>
                    <?
                    if (CONFIG['ENABLE_BADGE']) {
                    ?>
                        <tr class="Form-row" id="para_badgedisplay_tr">
                            <td class="Form-label" data-tooltip="para_badgedisplay_title"><strong><?= Lang::get('user', 'para_badgedisplay') ?></strong></td>
                            <td class="Form-inputs">
                                <input id="input-p_badgedisplay" type="checkbox" name="p_badgedisplay" <?= checked(!in_array('badgedisplay', $Paranoia)) ?> />
                                <label for="input-p_badgedisplay"> <?= Lang::get('user', 'para_badgedisplay_label') ?></label>
                            </td>
                        </tr>
                    <?
                    }
                    ?>
                    <tr class="Form-row" id="para_preview_tr">
                        <td class="Form-inputs"></td>
                        <td class="Form-inputs"><a class="Link" href="#" id="preview_paranoia"><?= Lang::get('user', 'para_preview') ?></a></td>
                    </tr>
                </table>
                <table class="Form-rowList" variant="header" id="access_settings">
                    <tr class="Form-rowHeader">
                        <td class="Form-title" colspan="2">
                            <?= Lang::get('user', 'st_access') ?>
                        </td>
                    </tr>
                    <tr class="Form-row" id="acc_resetpk_tr">
                        <td class="Form-label" data-tooltip-interactive="<?= Lang::get('user', 'resetpk_title') ?>"><strong><?= Lang::get('user', 'resetpk') ?></strong></td>
                        <td class="Form-inputs">
                            <div>
                                <input id="input-resetpasskey" type="checkbox" name="resetpasskey" id="resetpasskey" />
                                <label for="input-resetpasskey"><?= Lang::get('user', 'resetpk_note') ?></label>
                            </div>

                        </td>
                    </tr>
                    <tr class="Form-row" id="acc_irckey_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'irckey') ?></strong></td>
                        <td class="Form-items">
                            <div class="Form-inputs">
                                <input class="Input" type="text" size="50" name="irckey" id="irckey" value="<?= display_str($IRCKey) ?>" />
                                <input class="Button" type="button" onclick="RandomIRCKey();" value="<?= Lang::get('user', 'irckey_title') ?>" />
                            </div>
                            <div>
                                <?= Lang::get('user', 'irckey_note_1') ?> <?= CONFIG['BOT_NICK'] ?> <?= Lang::get('user', 'irckey_note_2') ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row" id="acc_tg_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', 'tg_binding') ?></strong></td>
                        <td class="Form-items">
                            <div>
                                <span><?= Lang::get('user', 'tg_binding_span') ?></span>
                                <ul class="postlist">
                                    <li><?= Lang::get('user', 'tg_binding_key') ?><code><?= $Right8Passkey ?></code><?= Lang::get('user', 'tg_binding_right8') ?></li>
                                    <li><?= Lang::get('user', 'tg_binding_status') ?><span id="tg_bind"><?= $TGID ? Lang::get('user', 'tg_binding_binded') : Lang::get('user', 'tg_binding_unbind') ?></span> <input id="tg_unbind_button" type="button" onclick="Unbind_tg(<?= $UserID ?>);" value="解绑" style="<?= $TGID ? "" : "display: none;" ?>" /></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?
                    if (check_perms('users_edit_profiles', $Class)) {
                    ?>
                        <tr class="Form-row" id="acc_email_tr">
                            <td class="Form-label" data-tooltip="<?= Lang::get('user', 'st_email_title') ?>"><strong><?= Lang::get('user', 'st_email') ?></strong></td>
                            <td class="Form-items">
                                <div>
                                    <input class="Input" type="email" size="50" name="email" id="email" value="<?= display_str($Email) ?>" />
                                </div>
                                <div><?= Lang::get('user', 'st_email_note') ?></div>
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
                        <td class="Form-label"><strong><?= Lang::get('user', 'st_password') ?></strong></td>
                        <td class="Form-items">
                            <div>
                                <label>
                                    <?= Lang::get('user', 'st_password_old') ?>:
                                    <input class="Input" type="password" size="40" name="cur_pass" id="cur_pass" value="" />
                                </label>
                            </div>
                            <div>
                                <label>
                                    <?= Lang::get('user', 'st_password_new') ?>:
                                    <input class="Input" type="password" size="40" name="new_pass_1" id="new_pass_1" value="" /> <strong id="pass_strength"></strong>
                                </label>
                            </div>
                            <div>
                                <label><?= Lang::get('user', 'st_password_re') ?>:
                                    <input class="Input" type="password" size="40" name="new_pass_2" id="new_pass_2" value="" /> <strong id="pass_match"></strong>
                                </label>
                            </div>
                            <div class="setting_description">
                                <?= Lang::get('user', 'st_password_note') ?>
                            </div>
                        </td>
                    </tr>

                    <tr class="Form-row" id="acc_2fa_tr">
                        <td class="Form-label"><strong><?= Lang::get('user', '2fa') ?></strong></td>
                        <td class="Form-items">
                            <div>
                                <?= Lang::get('user', 'st_2fa_note1') ?> <strong class="<?= $TwoFAKey ? 'u-colorSuccess' : 'u-colorWarning'; ?>"><?= $TwoFAKey ? Lang::get('user', 'st_2fa_enabled') : Lang::get('user', 'st_2fa_disabled'); ?></strong>
                            </div>
                            <div>
                                <a class="Link" href="user.php?action=2fa&do=<?= $TwoFAKey ? 'disable' : 'enable'; ?>&userid=<?= G::$LoggedUser['ID'] ?>"><?= Lang::get('user', 'st_2fa_note3') . ($TwoFAKey ? Lang::get('user', 'st_2fa_disable') : Lang::get('user', 'st_2fa_enable')) . Lang::get('user', 'st_2fa_after') ?></a>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
</div>
<? View::show_footer([], 'userEdit.js'); ?>