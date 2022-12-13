<?

use Aws\NetworkFirewall\NetworkFirewallClient;

if (!check_perms('site_torrents_notify')) {
    error(403);
}
$ID = $_GET['id'];
$NewFilter = false;
if (empty($ID)) {
    $NewFilter = true;
}
View::show_header(t('server.user.manage_notifications'), 'jquery.validate,form_validate', 'PageUserNotifyEdit');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.user.notify_me_of_all_new_torrents_with') ?></h2>
        <div class="BodyNavLinks">
            <a href="user.php?action=notify_edit" class="brackets"><?= t('server.user.create_new_torrent_notify') ?></a>
            <a href="user.php?action=notify" class="brackets"><?= t('server.user.new_torrent_notify_list') ?></a>
            <a href="torrents.php?action=notify" class="brackets"><?= t('server.user.view_notifications') ?></a>
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
    if (!$NewFilter) {
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
	WHERE ID=$ID");
        $N = G::$DB->next_record(MYSQLI_ASSOC);

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
    }
    ?>
    <form variant="header" class="Form-rowList TorrentNotifyForm <?= ($NewFilter ? 'create_form' : 'edit_form') ?>" id="<?= 'filter_form_' . $N['ID'] ?>" name="notification" action="user.php" method="post">
        <input type="hidden" name="formid" value="<?= $i ?>" />
        <input type="hidden" name="action" value="notify_handle" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <? if (!$NewFilter) { ?>
            <input type="hidden" name="id<?= $i ?>" value="<?= $N['ID'] ?>" />
        <?  } ?>
        <table <?= (!$NewFilter ? 'id="filter_' . $N['ID'] . '"' : '') ?>>
            <tr class="Form-rowHeader">
                <td>
                    <?
                    if ($NewFilter) {
                    ?>
                        <?= t('server.user.create_new_torrent_notify') ?>
                    <?
                    } else {
                        echo page_title_conn([t('server.torrents.edit_notification_filters'), $N['Label']]);
                    }
                    ?>
                </td>
            </tr>
            <? if ($NewFilter) { ?>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.user.notification_filter_name') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input required" type="text" name="label<?= $i ?>" placeholder="<?= t('server.user.notification_filter_name_note') ?>" />
                    </td>
                </tr>
                <tr class="Form-rowSubHeader">
                    <td colspan="2" class="center">
                        <span><?= t('server.user.all_fields_below_here_are_optional') ?></span>
                    </td>
                </tr>
            <?  } ?>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.one_of_these_artists') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="artists<?= $i ?>" rows="5" placeholder="<?= t('server.user.comma_seperated_artists_list') ?>" value="<?= display_str($N['Artists']) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.one_of_these_users') ?>:</td>
                <td class="Form-inputs">
                    <input type="text" class="Input" name="users<?= $i ?>" rows="5" placeholder="<?= t('server.user.comma_seperated_usernames_list') ?>" value="<?= display_str($Usernames) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.none_of_these_users') ?>:
                </td>
                <td class="Form-inputs">
                    <input type="text" class="Input" name="notusers<?= $i ?>" rows="2" placeholder="<?= t('server.user.comma_seperated_usernames_list') ?>" value="<?= display_str($NotUsernames) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.at_least_one_of_these_tags') ?>:</td>
                <td class="Form-inputs">
                    <input type="text" class="Input" name="tags<?= $i ?>" rows="2" placeholder="<?= t('server.user.comma_seperated_tags_list') ?>" value="<?= display_str($N['Tags']) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.none_of_these_tags') ?>:</td>
                <td class="Form-inputs">
                    <input type="text" class="Input" name="nottags<?= $i ?>" rows="2" placeholder="<?= t('server.user.comma_seperated_tags_list') ?>" value="<?= display_str($N['NotTags']) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.only_these_countries') ?>:
                </td>
                <td class="Form-inputs">
                    <input type="text" class="Input" name="regions<?= $i ?>" rows="2" placeholder="<?= t('server.user.comma_seperated_countries_list') ?>" value="<?= display_str($N['Regions']) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.only_these_languages') ?>:
                </td>
                <td class="Form-inputs">
                    <input type="text" class="Input" name="languages<?= $i ?>" rows="2" placeholder="<?= t('server.user.comma_seperated_languages_list') ?>" value="<?= display_str($N['Languages']) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.only_these_editions') ?>:</td>
                <td class="Form-inputs">
                    <input type="text" class="Input" name="remastertitles<?= $i ?>" rows="2" placeholder="<?= t('server.user.comma_seperated_editions_list') ?>" value="<?= display_str($N['RemasterTitles']) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.only_these_categories') ?>:</td>
                <td class="Form-inputs">
                    <? foreach ($ReleaseTypes as $key) { ?>
                        <input type="checkbox" name="releasetypes<?= $i ?>[]" id="<?= $key ?>_<?= $N['ID'] ?>" value="<?= $key ?>" <? if (in_array($key, $N['ReleaseTypes'])) {
                                                                                                                                        echo ' checked="checked"';
                                                                                                                                    } ?> />
                        <label for="<?= $key ?>_<?= $N['ID'] ?>"><?= t('server.torrents.release_types')[$key] ?></label>
                    <?  } ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.between_the_years') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="number" min="1888" max="9999" name="fromyear<?= $i ?>" value="<?= $N['FromYear'] ?>" id="fromyear_<?= $N['ID'] ?>" placeholder="<?= t('server.user.min_1888') ?>" onchange='smallerThen("fromyear_<?= $N['ID'] ?>", "toyear_<?= $N['ID'] ?>", "yearerror_<?= $N['ID'] ?>")' />
                    -
                    <input class="Input is-small" type="number" min="0" max="9999" name="toyear<?= $i ?>" value="<?= $N['ToYear'] ?>" id="toyear_<?= $N['ID'] ?>" onchange='smallerThen("fromyear_<?= $N['ID'] ?>", "toyear_<?= $N['ID'] ?>", "yearerror_<?= $N['ID'] ?>")' />
                    <?= t('server.user.year') ?>
                    <span>&ensp;<?= t('server.user.leaving_blank_means_you_allow_all_years') ?></span>
                    <label id="yearerror_<?= $N['ID'] ?>" class="u-colorWarning" style="display: none;"><?= t('server.user.please_enter_correct_numbers') ?></label>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.minimum_imdb_rating') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input" type="number" min="0" max="10" name="fromimdbrating<?= $i ?>" value="<?= $N['FromIMDBRating'] ?>" id="fromimdbrating_<?= $N['ID'] ?>" onchange='smallerThen("fromimdbrating_<?= $N['ID'] ?>", "toimdbrating_<?= $N['ID'] ?>", "imdbratingerror_<?= $N['ID'] ?>")' />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.only_these_sources') ?>:</td>
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
                <td class="Form-label"><?= t('server.user.only_these_processings') ?>:</td>
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
                <td class="Form-label"><?= t('server.user.only_these_codecs') ?>:</td>
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
                <td class="Form-label"><?= t('server.user.only_these_resolutions') ?>:</td>
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
                <td class="Form-label"><?= t('server.user.only_these_containers') ?>:</td>
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
                <td class="Form-label"><?= t('server.user.only_these_discounts') ?>:</td>
                <td class="Form-inputs">
                    <div class="Checkbox">
                        <input class="Input" name="frees<?= $i ?>[]" id="free_leech" type="checkbox" value="<?= 1 ?>" <? if (in_array(1, $N['FreeTorrents'])) {
                                                                                                                            echo ' checked="checked"';
                                                                                                                        } ?>" />
                        <label for="Checkbox-label free_leech"><?= t('server.torrents.freeleech') ?></label>
                    </div>
                    <div class="Checkbox">
                        <input class="Input" name="frees<?= $i ?>[]" id="75_percent_off" type="checkbox" value="<?= 13 ?>" <? if (in_array(13, $N['FreeTorrents'])) {
                                                                                                                                echo ' checked="checked"';
                                                                                                                            } ?> />
                        <label class="Checkbox-label" for="75_percent_off"><?= t('server.torrents.off75') ?></label>
                    </div>
                    <div class="Checkbox">
                        <input class="Input" name="frees<?= $i ?>[]" id="50_percent_off" type="checkbox" value="<?= 12 ?>" <? if (in_array(12, $N['FreeTorrents'])) {
                                                                                                                                echo ' checked="checked"';
                                                                                                                            } ?> />
                        <label class="Checkbox-label" for="50_percent_off"><?= t('server.torrents.off50') ?></label>
                    </div>
                    <div class="Checkbox">
                        <input class="Input" name="frees<?= $i ?>[]" id="25_percent_off" type="checkbox" value="<?= 11 ?>" <? if (in_array(11, $N['FreeTorrents'])) {
                                                                                                                                echo ' checked="checked"';
                                                                                                                            } ?> />
                        <label class="Checkbox-label" for="25_percent_off"><?= t('server.torrents.off25') ?></label>
                    </div>
                    <div class="Checkbox">
                        <input class="Input" name="frees<?= $i ?>[]" id="neutral_leech" type="checkbox" value="<?= 2 ?>" / <? if (in_array(2, $N['FreeTorrents'])) {
                                                                                                                                echo ' checked="checked"';
                                                                                                                            } ?>>
                        <label class="Checkbox-label" for="neutral_leech"><?= t('server.torrents.neutral_leech') ?></label>
                    </div>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.between_the_sizes') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="number" min="1" name="fromsize<?= $i ?>" value="<?= $N['FromSize'] ? $N['FromSize'] : 1 ?>" id="fromsize_<?= $N['ID'] ?>" placeholder="<?= t('server.user.min_1') ?>" oninput="if(value<1)value=1" onchange='smallerThen("fromsize_<?= $N['ID'] ?>", "tosize_<?= $N['ID'] ?>", "sizeerror_<?= $N['ID'] ?>")' />
                    -
                    <input class="Input is-small" type="number" min="0" name="tosize<?= $i ?>" value="<?= $N['ToSize'] ?>" id="tosize_<?= $N['ID'] ?>" onchange='smallerThen("fromsize_<?= $N['ID'] ?>", "tosize_<?= $N['ID'] ?>", "sizeerror_<?= $N['ID'] ?>")' />
                    GB
                    <span>&ensp;<?= t('server.user.leaving_blank_means_you_allow_all_sizes') ?></span>
                    <label id="sizeerror_<?= $N['ID'] ?>" class="u-colorWarning" style="display: none;"><?= t('server.user.please_enter_correct_numbers') ?></label>
                </td>
            </tr>
            <tr class="Form-row">
                <? $NewGroupsOnlyChecked = $N['NewGroupsOnly'] == '1' ? 'checked="checked"' : ''  ?>
                <td class="Form-label">
                    <?= t('server.user.only_new_releases') ?>:
                </td>
                <td class="Form-inputs">
                    <div class="Checkbox">
                        <input class="Input" type="checkbox" name="newgroupsonly<?= $i ?>" id="newgroupsonly_<?= $N['ID'] ?>" <?= $NewGroupsOnlyChecked  ?> />
                        <label class="Checkbox-label" for="newgroupsonly_<?= $N['ID'] ?>">
                            <?= t('server.user.only_new_releases_label') ?>
                        </label>
                    </div>
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2" class="Form-submit center">
                    <input class="Button" type="submit" value="<?= ($NewFilter ? t('server.user.create_filter') : t('server.user.update_filter')) ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>
<? View::show_footer(); ?>