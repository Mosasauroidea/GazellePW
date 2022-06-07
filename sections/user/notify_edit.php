<?

use Aws\NetworkFirewall\NetworkFirewallClient;

if (!check_perms('site_torrents_notify')) {
    error(403);
}
View::show_header(Lang::get('user', 'manage_notifications'), 'jquery.validate,form_validate', 'PageUserNotifyEdit');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('user', 'notify_me_of_all_new_torrents_with') ?></h2>
        <div class="BodyNavLinks">
            <a href="torrents.php?action=notify" class="brackets"><?= Lang::get('user', 'view_notifications') ?></a>
        </div>
    </div>
    <script>
        function alwaysOn(event) {
            if (event.data.id == 0) event.data.id = ""
            if ($("#good_" + event.data.id).prop('checked')) {
                $(this).prop('checked', true);
            }
        }

        function alwaysOff(event) {
            if (event.data.id == 0) event.data.id = ""
            if ($("#good_" + event.data.id).prop('checked')) {
                $(this).prop('checked', false);
            }
        }

        function smallerThen(first, second, error) {
            console.log(first, second, error)
            var firstval = $("#" + first).val(),
                secondval = $("#" + second).val()
            if (firstval && secondval) {
                if (parseInt(firstval) > parseInt(secondval)) {

                    $("#" + error).show()

                } else {
                    $("#" + error).hide()
                }
            }
        }
    </script>
    <?
    $DB->query("
	SELECT
		ID,
		Label,
		Artists,
		ExcludeVA,
		NewGroupsOnly,
		Tags,
		NotTags,
		ReleaseTypes,
		Categories,
        Codecs, 
        Sources, 
        Containers, 
        Resolutions,
        Processings,
        FreeTorrents,
		FromYear,
		ToYear,
		FromSize,
		ToSize,
		Users,
		NotUsers,
        FromIMDBRating, 
        Regions, 
        Languages, 
        RemasterTitles
	FROM users_notify_filters
	WHERE UserID=$LoggedUser[ID]");

    $NumFilters = $DB->record_count();

    $Notifications = $DB->to_array();
    $Notifications[] = array(
        'ID' => false,
        'Label' => '',
        'Artists' => '',
        'ExcludeVA' => false,
        'NewGroupsOnly' => false,
        'Tags' => '',
        'NotTags' => '',
        'ReleaseTypes' => '',
        'Categories' => '',
        'Codecs' => '',
        'Sources' => '',
        'Resolutions' => '',
        'Containers' => '',
        'FreeTorrents' => '',
        'Processings' => '',
        'FromYear' => 0,
        'ToYear' => 0,
        'FromSize' => 0,
        'ToSize' => 0,
        'Users' => '',
        'NotUsers' => '',
        'FromIMDBRating' => 0,
        'Regions' => '',
        'Languages' => '',
        'RemasterTitles' => '',
    );

    $i = 0;
    ?>
    <h3><b><?= Lang::get('user', 'current_filter') ?></b></h3>
    <?
    foreach ($Notifications as $Idx => $N) { // $N stands for Notifications
        $i++;
        $NewFilter = $N['ID'] === false;
        $N['Artists']       = implode(', ', explode('|', substr($N['Artists'], 1, -1)));
        $N['Tags']          = implode(', ', explode('|', substr($N['Tags'], 1, -1)));
        $N['NotTags']       = implode(', ', explode('|', substr($N['NotTags'], 1, -1)));
        $N['Regions']       = implode(', ', explode('|', substr($N['Regions'], 1, -1)));
        $N['Languages']       = implode(', ', explode('|', substr($N['Languages'], 1, -1)));
        $N['RemasterTitles']       = implode(', ', explode('|', substr($N['RemasterTitles'], 1, -1)));
        $N['ReleaseTypes']  = explode('|', substr($N['ReleaseTypes'], 1, -1));
        $N['Categories']    = explode('|', substr($N['Categories'], 1, -1));
        $N['Codecs']       = explode('|', substr($N['Codecs'], 1, -1));
        $N['Sources']     = explode('|', substr($N['Sources'], 1, -1));
        $N['Resolutions']         = explode('|', substr($N['Resolutions'], 1, -1));
        $N['Containers']         = explode('|', substr($N['Containers'], 1, -1));
        $N['Processings']         = explode('|', substr($N['Processings'], 1, -1));
        $N['FreeTorrents']         = explode('|', substr($N['FreeTorrents'], 1, -1));
        $N['Users']         = explode('|', substr($N['Users'], 1, -1));
        $N['NotUsers']      = explode('|', substr($N['NotUsers'], 1, -1));

        $Usernames = '';
        foreach ($N['Users'] as $UserID) {
            $UserInfo = Users::user_info($UserID);
            $Usernames .= $UserInfo['Username'] . ', ';
        }
        $Usernames = rtrim($Usernames, ', ');

        $NotUsernames = '';
        foreach ($N['NotUsers'] as $UserID) {
            $UserInfo = Users::user_info($UserID);
            $NotUsernames .= $UserInfo['Username'] . ', ';
        }
        $NotUsernames = rtrim($NotUsernames, ', ');
        if ($N['FromYear'] + $N['ToYear'] == 0) {
            $N['FromYear'] = '';
            $N['ToYear'] = '';
        }
        if ($N['FromIMDBRating'] == 0) {
            $N['FromIMDBRating'] = '';
        }
        $N['FromSize'] /= 1024 * 1024 * 1024;
        $N['ToSize'] /= 1024 * 1024 * 1024;
        if ($N['FromSize'] + $N['ToSize'] == 0) {
            $N['FromSize'] = '';
            $N['ToSize'] = '';
        }
        if ($N['ToSize'] == 0) {
            $N['ToSize'] = '';
        }
        if ($NewFilter) {
    ?>
            <hr>
            <h3><b><?= Lang::get('user', 'create_a_new_notification_filter') ?></b></h3>
        <?  } elseif ($NumFilters > 0) { ?>
            <b>
                <label><?= $Idx + 1 ?>.</label>
                <?= display_str($N['Label']) ?>
                <a href="#" onclick="$('#filter_form_<?= $N['ID'] ?>').gtoggle(); return false;" class="brackets"><?= Lang::get('global', 'show') ?></a>
                <a href="user.php?action=notify_delete&amp;id=<?= $N['ID'] ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" onclick="return confirm('<?= Lang::get('user', 'are_you_sure_delete_notification_filter') ?>')" class="brackets"><?= Lang::get('global', 'delete') ?></a>
                <a <a class="brackets" href="feeds.php?feed=torrents_notify_<?= $N['ID'] ?>_<?= $LoggedUser['torrent_pass'] ?>&amp;user=<?= $LoggedUser['ID'] ?>&amp;auth=<?= $LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= $LoggedUser['torrent_pass'] ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode($N['Label']) ?>"><?= Lang::get('user', 'rss_address') ?></a>
                <a href="feeds.php?feed=torrents_notify_<?= $N['ID'] ?>_<?= $LoggedUser['torrent_pass'] ?>&amp;user=<?= $LoggedUser['ID'] ?>&amp;auth=<?= $LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= $LoggedUser['torrent_pass'] ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode($N['Label']) ?>"><img src="<?= STATIC_SERVER ?>/common/symbols/rss.png" alt="RSS feed" /></a>

            </b>
        <?
        }
        ?>
        <form class="TorrentNotifyForm <?= ($NewFilter ? 'create_form' : 'edit_form hidden') ?>" id="<?= 'filter_form_' . $N['ID'] ?>" name="notification" action="user.php" method="post">
            <input type="hidden" name="formid" value="<?= $i ?>" />
            <input type="hidden" name="action" value="notify_handle" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <? if (!$NewFilter) { ?>
                <input type="hidden" name="id<?= $i ?>" value="<?= $N['ID'] ?>" />
            <?  } ?>
            <table class="Table Form-rowList" <?= (!$NewFilter ? 'id="filter_' . $N['ID'] . '"' : '') ?>>
                <? if ($NewFilter) { ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= Lang::get('user', 'notification_filter_name') ?>:</td>
                        <td class="Form-inputs">
                            <input class="Input required" type="text" name="label<?= $i ?>" placeholder="<?= Lang::get('user', 'notification_filter_name_note') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td colspan="2" class="center">
                            <span><?= Lang::get('user', 'all_fields_below_here_are_optional') ?></span>
                        </td>
                    </tr>
                <?  } ?>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'one_of_these_artists') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="artists<?= $i ?>" rows="5" placeholder="<?= Lang::get('user', 'comma_seperated_artists_list') ?>" value="<?= display_str($N['Artists']) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'one_of_these_users') ?>:</td>
                    <td class="Form-inputs">
                        <input type="text" class="Input" name="users<?= $i ?>" rows="5" placeholder="<?= Lang::get('user', 'comma_seperated_usernames_list') ?>" value="<?= display_str($Usernames) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'none_of_these_users') ?>:
                    </td>
                    <td class="Form-inputs">
                        <input type="text" class="Input" name="notusers<?= $i ?>" rows="2" placeholder="<?= Lang::get('user', 'comma_seperated_usernames_list') ?>" value="<?= display_str($NotUsernames) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'at_least_one_of_these_tags') ?>:</td>
                    <td class="Form-inputs">
                        <input type="text" class="Input" name="tags<?= $i ?>" rows="2" placeholder="<?= Lang::get('user', 'comma_seperated_tags_list') ?>" value="<?= display_str($N['Tags']) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'none_of_these_tags') ?>:</td>
                    <td class="Form-inputs">
                        <input type="text" class="Input" name="nottags<?= $i ?>" rows="2" placeholder="<?= Lang::get('user', 'comma_seperated_tags_list') ?>" value="<?= display_str($N['NotTags']) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_countries') ?>:
                    </td>
                    <td class="Form-inputs">
                        <input type="text" class="Input" name="regions<?= $i ?>" rows="2" placeholder="<?= Lang::get('user', 'comma_seperated_countries_list') ?>" value="<?= display_str($N['Regions']) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_languages') ?>:
                    </td>
                    <td class="Form-inputs">
                        <input type="text" class="Input" name="languages<?= $i ?>" rows="2" placeholder="<?= Lang::get('user', 'comma_seperated_languages_list') ?>" value="<?= display_str($N['Languages']) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_editions') ?>:</td>
                    <td class="Form-inputs">
                        <input type="text" class="Input" name="remastertitles<?= $i ?>" rows="2" placeholder="<?= Lang::get('user', 'comma_seperated_editions_list') ?>" value="<?= display_str($N['RemasterTitles']) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_categories') ?>:</td>
                    <td class="Form-inputs">
                        <? foreach ($ReleaseTypes as $key) { ?>
                            <input type="checkbox" name="releasetypes<?= $i ?>[]" id="<?= $key ?>_<?= $N['ID'] ?>" value="<?= $key ?>" <? if (in_array($key, $N['ReleaseTypes'])) {
                                                                                                                                            echo ' checked="checked"';
                                                                                                                                        } ?> />
                            <label for="<?= $key ?>_<?= $N['ID'] ?>"><?= Lang::get('torrents', 'release_types')[$key] ?></label>
                        <?  } ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'between_the_years') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input is-small" type="number" min="1888" max="9999" name="fromyear<?= $i ?>" value="<?= $N['FromYear'] ?>" id="fromyear_<?= $N['ID'] ?>" placeholder="<?= Lang::get('user', 'min_1888') ?>" onchange='smallerThen("fromyear_<?= $N['ID'] ?>", "toyear_<?= $N['ID'] ?>", "yearerror_<?= $N['ID'] ?>")' />
                        -
                        <input class="Input is-small" type="number" min="0" max="9999" name="toyear<?= $i ?>" value="<?= $N['ToYear'] ?>" id="toyear_<?= $N['ID'] ?>" onchange='smallerThen("fromyear_<?= $N['ID'] ?>", "toyear_<?= $N['ID'] ?>", "yearerror_<?= $N['ID'] ?>")' />
                        <?= Lang::get('user', 'year') ?>
                        <span>&ensp;<?= Lang::get('user', 'leaving_blank_means_you_allow_all_years') ?></span>
                        <label id="yearerror_<?= $N['ID'] ?>" class="u-colorWarning" style="display: none;"><?= Lang::get('user', 'please_enter_correct_numbers') ?></label>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'minimum_imdb_rating') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="number" min="0" max="10" name="fromimdbrating<?= $i ?>" value="<?= $N['FromIMDBRating'] ?>" id="fromimdbrating_<?= $N['ID'] ?>" onchange='smallerThen("fromimdbrating_<?= $N['ID'] ?>", "toimdbrating_<?= $N['ID'] ?>", "imdbratingerror_<?= $N['ID'] ?>")' />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_sources') ?>:</td>
                    <td class="Form-inputs">
                        <? foreach ($Sources as $Source) { ?>
                            <div class="Checkbox">
                                <input class="Input m_<?= $N['ID'] ?>" type="checkbox" name="sources<?= $i ?>[]" id="<?= $Source ?>_<?= $N['ID'] ?>" value="<?= $Source ?>" <? if (in_array($Source, $N['Sources'])) {
                                                                                                                                                                                echo ' checked="checked"';
                                                                                                                                                                            } ?> />
                                <label class="Checkbox-label" for="<?= $Source ?>_<?= $N['ID'] ?>"><?= $Source ?></label>
                            </div>
                        <?  } ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_processings') ?>:</td>
                    <td class="Form-inputs">
                        <? foreach ($Processings as $Processing) {
                            if ($Processing == '---') {
                                continue;
                            }
                        ?>
                            <div class="Checkbox">
                                <input class="Input m_<?= $N['ID'] ?>" type="checkbox" name="processings<?= $i ?>[]" id="<?= $Processing ?>_<?= $N['ID'] ?>" value="<?= $Processing ?>" <? if (in_array($Processing, $N['Processings'])) {
                                                                                                                                                                                            echo ' checked="checked"';
                                                                                                                                                                                        } ?> />
                                <label class="Checkbox-label" for="<?= $Processing ?>_<?= $N['ID'] ?>"><?= $Processing == 'Encode' ? 'Encode/---' : $Processing ?></label>
                            </div>
                        <?  } ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_codecs') ?>:</td>
                    <td class="Form-inputs">
                        <? foreach ($Codecs as $Codec) { ?>
                            <div class="Checkbox">
                                <input class="Input m_<?= $N['ID'] ?>" type="checkbox" name="codecs<?= $i ?>[]" id="<?= $Codec ?>_<?= $N['ID'] ?>" value="<?= $Codec ?>" <? if (in_array($Codec, $N['Codecs'])) {
                                                                                                                                                                                echo ' checked="checked"';
                                                                                                                                                                            } ?> />
                                <label class="Checkbox-label" for="<?= $Codec ?>_<?= $N['ID'] ?>"><?= $Codec ?></label>
                            </div>
                        <?  } ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_resolutions') ?>:</td>
                    <td class="Form-inputs">
                        <? foreach ($Resolutions as $Resolution) { ?>
                            <div class="Checkbox">
                                <input class="Input m_<?= $N['ID'] ?>" type="checkbox" name="resolutions<?= $i ?>[]" id="<?= $Resolution ?>_<?= $N['ID'] ?>" value="<?= $Resolution ?>" <? if (in_array($Resolution, $N['Resolutions'])) {
                                                                                                                                                                                            echo ' checked="checked"';
                                                                                                                                                                                        } ?> />
                                <label class="Checkbox-label" for="<?= $Resolution ?>_<?= $N['ID'] ?>"><?= $Resolution ?></label>
                            </div>
                        <?  } ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_containers') ?>:</td>
                    <td class="Form-inputs">
                        <? foreach ($Containers as $Container) {
                        ?>
                            <div class="Checkbox">
                                <input class="Input m_<?= $N['ID'] ?>" type="checkbox" name="containers<?= $i ?>[]" id="<?= $Container ?>_<?= $N['ID'] ?>" value="<?= $Container ?>" <? if (in_array($Container, $N['Containers'])) {
                                                                                                                                                                                            echo ' checked="checked"';
                                                                                                                                                                                        } ?> />
                                <label class="Checkbox-label" for="<?= $Container ?>_<?= $N['ID'] ?>"><?= $Container ?></label>
                            </div>
                        <?  } ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'only_these_discounts') ?>:</td>
                    <td class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" name="frees<?= $i ?>[]" id="free_leech" type="checkbox" value="<?= 1 ?>" <? if (in_array(1, $N['FreeTorrents'])) {
                                                                                                                                echo ' checked="checked"';
                                                                                                                            } ?>" />
                            <label for="Checkbox-label free_leech"><?= Lang::get('tools', 'free_leech') ?></label>
                        </div>
                        <div class="Checkbox">
                            <input class="Input" name="frees<?= $i ?>[]" id="75_percent_off" type="checkbox" value="<?= 13 ?>" <? if (in_array(13, $N['FreeTorrents'])) {
                                                                                                                                    echo ' checked="checked"';
                                                                                                                                } ?> />
                            <label class="Checkbox-label" for="75_percent_off"><?= Lang::get('tools', '75_percent_off') ?></label>
                        </div>
                        <div class="Checkbox">
                            <input class="Input" name="frees<?= $i ?>[]" id="50_percent_off" type="checkbox" value="<?= 12 ?>" <? if (in_array(12, $N['FreeTorrents'])) {
                                                                                                                                    echo ' checked="checked"';
                                                                                                                                } ?> />
                            <label class="Checkbox-label" for="50_percent_off"><?= Lang::get('tools', '50_percent_off') ?></label>
                        </div>
                        <div class="Checkbox">
                            <input class="Input" name="frees<?= $i ?>[]" id="25_percent_off" type="checkbox" value="<?= 11 ?>" <? if (in_array(11, $N['FreeTorrents'])) {
                                                                                                                                    echo ' checked="checked"';
                                                                                                                                } ?> />
                            <label class="Checkbox-label" for="25_percent_off"><?= Lang::get('tools', '25_percent_off') ?></label>
                        </div>
                        <div class="Checkbox">
                            <input class="Input" name="frees<?= $i ?>[]" id="neutral_leech" type="checkbox" value="<?= 2 ?>" / <? if (in_array(2, $N['FreeTorrents'])) {
                                                                                                                                    echo ' checked="checked"';
                                                                                                                                } ?>>
                            <label class="Checkbox-label" for="neutral_leech"><?= Lang::get('tools', 'neutral_leech') ?></label>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('user', 'between_the_sizes') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input is-small" type="number" min="1" name="fromsize<?= $i ?>" value="<?= $N['FromSize'] ? $N['FromSize'] : 1 ?>" id="fromsize_<?= $N['ID'] ?>" placeholder="<?= Lang::get('user', 'min_1') ?>" oninput="if(value<1)value=1" onchange='smallerThen("fromsize_<?= $N['ID'] ?>", "tosize_<?= $N['ID'] ?>", "sizeerror_<?= $N['ID'] ?>")' />
                        -
                        <input class="Input is-small" type="number" min="0" name="tosize<?= $i ?>" value="<?= $N['ToSize'] ?>" id="tosize_<?= $N['ID'] ?>" onchange='smallerThen("fromsize_<?= $N['ID'] ?>", "tosize_<?= $N['ID'] ?>", "sizeerror_<?= $N['ID'] ?>")' />
                        GB
                        <span>&ensp;<?= Lang::get('user', 'leaving_blank_means_you_allow_all_sizes') ?></span>
                        <label id="sizeerror_<?= $N['ID'] ?>" class="u-colorWarning" style="display: none;"><?= Lang::get('user', 'please_enter_correct_numbers') ?></label>
                    </td>
                </tr>
                <tr class="Form-row">
                    <? $NewGroupsOnlyChecked = $N['NewGroupsOnly'] == '1' ? 'checked="checked"' : ''  ?>
                    <td class="Form-label">
                        <?= Lang::get('user', 'only_new_releases') ?>:
                    </td>
                    <td class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" name="newgroupsonly<?= $i ?>" id="newgroupsonly_<?= $N['ID'] ?>" <?= $NewGroupsOnlyChecked  ?> />
                            <label class="Checkbox-label" for="newgroupsonly_<?= $N['ID'] ?>">
                                <?= Lang::get('user', 'only_new_releases_label') ?>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td colspan="2" class="Form-submit center">
                        <input class="Button" type="submit" value="<?= ($NewFilter ? Lang::get('user', 'create_filter') : Lang::get('user', 'update_filter')) ?>" />
                    </td>
                </tr>
            </table>
        </form>
    <? } ?>
</div>
<? View::show_footer(); ?>