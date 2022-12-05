<?
if (!check_perms('users_mod')) {
    error(403);
}

View::show_header(t('server.tools.global_torrents_sales_promotion_management'));

if (isset($_POST['torrents'])) {
    $GroupIDs = array();
    $Elements = explode("\r\n", $_POST['torrents']);
    foreach ($Elements as $Element) {
        // Get all of the torrent IDs
        if (strpos($Element, "torrents.php") !== false) {
            $Data = explode("id=", $Element);
            if (!empty($Data[1])) {
                $GroupIDs[] = (int) $Data[1];
            }
        } else if (strpos($Element, "collages.php") !== false) {
            $Data = explode("id=", $Element);
            if (!empty($Data[1])) {
                $CollageID = (int) $Data[1];
                $DB->query("
                    SELECT GroupID
                    FROM collages_torrents
                    WHERE CollageID = '$CollageID'");
                while (list($GroupID) = $DB->next_record()) {
                    $GroupIDs[] = (int) $GroupID;
                }
            }
        }
    }

    if (sizeof($GroupIDs) == 0) {
        $Err = t('server.tools.please_enter_properly_formatted_urls');
    } else {
        $FreeLeechType = (int) $_POST['freeleechtype'];
        $FreeLeechReason = (int) $_POST['freeleechreason'];

        if (!in_array($FreeLeechType, array(0, 1, 2)) || !in_array($FreeLeechReason, array(0, 1, 2, 3))) {
            $Err = t('server.tools.invalid_freeleech_type_or_freeleech_reason');
        } else {
            // Get the torrent IDs
            $DB->query("
                SELECT ID
                FROM torrents
                WHERE GroupID IN (" . implode(', ', $GroupIDs) . ")");
            $TorrentIDs = $DB->collect('ID');

            if (sizeof($TorrentIDs) == 0) {
                $Err = t('server.tools.invalid_group_ids');
            } else {
                if (isset($_POST['NLOver']) && $FreeLeechType == 1) {
                    // Only use this checkbox if freeleech is selected
                    $Size = (int) $_POST['size'];
                    $Units = db_string($_POST['scale']);

                    if (empty($Size) || !in_array($Units, array('k', 'm', 'g'))) {
                        $Err = t('server.tools.invalid_size_or_units');
                    } else {
                        $Bytes = Format::get_bytes($Size . $Units);

                        $DB->query("
                            SELECT ID
                            FROM torrents
                            WHERE ID IN (" . implode(', ', $TorrentIDs) . ")
                              AND Size > '$Bytes'");
                        $LargeTorrents = $DB->collect('ID');
                        $TorrentIDs = array_diff($TorrentIDs, $LargeTorrents);
                    }
                }

                if (sizeof($TorrentIDs) > 0) {
                    Torrents::freeleech_torrents($TorrentIDs, $FreeLeechType, $FreeLeechReason);
                }

                if (isset($LargeTorrents) && sizeof($LargeTorrents) > 0) {
                    Torrents::freeleech_torrents($LargeTorrents, 2, $FreeLeechReason);
                }

                $Err = 'Done!';
            }
        }
    }
}
?>
<div class="LayoutBody">
    <h2><?= t('server.tools.h2_global_torrents_sales_promotion_management') ?></h2>
    <? /*
    <div class="TableContainer pad box" id="torrent_sale_management">
        <table id="torrent_sale_management_table">
            <tr>
                <td class="label"><?= t('server.tools.sales_promotion_range') ?>:</td>
                <td>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.tools.all_torrents_include_new') ?></option>
                        <option class="Select-option"><?= t('server.tools.all_current_torrents') ?></option>
                        <option class="Select-option"><?= t('server.tools.all_new_torrents') ?></option>
                        <!-- <option class="Select-option"><?= t('server.tools.internal_torrents') ?></option> -->
                        <option class="Select-option"><?= t('server.tools.certain_torrent_groups') ?></option>
                        <option class="Select-option"><?= t('server.tools.certain_torrents') ?></option>
                        <!-- <option class="Select-option"><?= t('server.tools.certain_size_torrents') ?></option> -->
                    </select>
                </td>
            </tr>
            <tr class="hidden">
                <td colspan="2">
                    <textarea>
                <!-- 如果选了「指定种子组」，就显示这个模块，要求填写种子组 ID，用英文逗号隔开 -->
            </textarea>
                </td>
            </tr>
            <tr class="hidden">
                <td colspan="2">
                    <textarea>
                <!-- 如果选了「指定种子」，就显示这个模块，要求填写种子 PL ID，用英文逗号隔开 -->
            </textarea>
                </td>
            </tr>
            <tr>
                <td class="label"><?= t('server.tools.specifications') ?>:</td>
                <td>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.common.type') ?></option>
                        <option class="Select-option"><?= t('server.common.feature_film') ?></option>
                        <option class="Select-option"><?= t('server.common.short_film') ?></option>
                        <option class="Select-option"><?= t('server.common.miniseries') ?></option>
                        <option class="Select-option"><?= t('server.common.stand_up_comedy') ?></option>
                        <option class="Select-option"><?= t('server.common.live_performance') ?></option>
                        <option class="Select-option"><?= t('server.common.movie_collection') ?></option>
                    </select>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.tools.resolution') ?></option>
                        <option class="Select-option"><?= t('server.tools.standard_definition') ?></option>
                        <option class="Select-option">720p</option>
                        <option class="Select-option">1080p</option>
                        <option class="Select-option">2160p</option>
                    </select>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.tools.processing') ?></option>
                        <option class="Select-option">Encode</option>
                        <option class="Select-option">Remux</option>
                        <option class="Select-option">DIY</option>
                        <option class="Select-option">Untouched</option>
                    </select>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.tools.source') ?></option>
                        <option class="Select-option">DVD</option>
                        <option class="Select-option">WEB</option>
                        <option class="Select-option">Blu-ray</option>
                        <option class="Select-option">HD-DVD</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><?= t('server.tools.misc') ?>:</td>
                <td>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.tools.original_options') ?></option>
                        <option class="Select-option"><?= t('server.tools.both') ?></option>
                        <option class="Select-option"><?= t('server.upload.self_rip') ?></option>
                        <option class="Select-option"><?= t('server.upload.self_purchase') ?></option>
                    </select>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.tools.internal_torrent') ?></option>
                        <option class="Select-option"><?= t('server.tools.yes') ?></option>
                        <option class="Select-option"><?= t('server.tools.no') ?></option>
                    </select>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.tools.feature_torrent') ?></option>
                        <option class="Select-option"><?= t('server.tools.both') ?></option>
                        <option class="Select-option"><?= t('server.tools.chinese_dubbed') ?></option>
                        <option class="Select-option"><?= t('server.tools.special_effects_subtitles') ?></option>
                        <option class="Select-option"><?= t('server.tools.no') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><?= t('server.tools.size_limitation') ?>:</td>
                <td>
                    <span><?= t('server.tools.above') ?></span>
                    <input class="Input" type="number" style="width: 80px" min="0">
                    <span><?= t('server.tools.below') ?></span>
                    <input class="Input" type="number" style="width: 80px" min="0">
                    <span><?= t('server.tools.equal') ?></span>
                    <input class="Input" type="number" style="width: 80px" min="0">
                    <span>GB</span>
                    <p><?= t('server.tools.size_limitation_note') ?></p>
                </td>
            </tr>
            <tr>
                <td class="label"><?= t('server.tools.sales_promotion_plan') ?>:</td>
                <td>
                    <select class="Input">
                        <option class="Select-option"><?= t('server.torrents.freeleech') ?></option>
                        <option class="Select-option"><?= t('server.torrents.off25') ?></option>
                        <option class="Select-option"><?= t('server.torrents.off50') ?></option>
                        <option class="Select-option"><?= t('server.torrents.off75') ?></option>
                        <option class="Select-option"><?= t('server.torrents.neutral_leech') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><?= t('server.tools.sales_promotion_period') ?>:</td>
                <td>
                    <input class="Input" type="number" min="1" max="">
                    <span><?= t('server.tools.hour_s') ?></span>
                    <input type="checkbox" id="permanent">
                    <label for="permanent"><?= t('server.tools.permanent') ?></label>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="center">
                    <button class="Button"> <?= t('server.tools.add_rule') ?> </button>
                </td>
            </tr>
        </table>
    </div>

    <h3><?= t('server.tools.current_sales_promotion_rules') ?></h3>
    <div id="current_sale_rules">
        <table class="TableMultipleFreeleech Table">
            <tr class="Tablep-rowHeader">
                <td class="Table-cell"><?= t('server.tools.sales_promotion_range') ?></td>
                <td class="Table-cell"><?= t('server.tools.sales_promotion_plan') ?></td>
                <td class="Table-cell"><?= t('server.tools.sales_promotion_period') ?></td>
                <td class="Table-cell"><?= t('server.tools.added_on') ?></td>
                <td class="Table-cell"><?= t('server.tools.operator') ?></td>
                <td class="Table-cell"><?= t('server.tools.operation') ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><a><?= t('server.tools.click_to_see') ?></a></td><!-- 这样设计是因为考虑到自定义一批种子或种子组，需要另行显示；如果是全局的批量，就直接显示文字就可以。 -->
                <td class="Table-cell">比方说免费</td>
                <td class="Table-cell">比方说 3 天</td>
                <td class="Table-cell">2021-05-13</td>
                <td class="Table-cell">Username</td>
                <td class="Table-cell">
                    <a><?= t('server.common.delete') ?></a><!-- 如果要调整促销规则，删掉旧的新增就可以 -->
                </td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell">所有种子</td>
                <td class="Table-cell">比方说免费</td>
                <td class="Table-cell">比方说 3 天</td>
                <td class="Table-cell">2021-05-14</td>
                <td class="Table-cell">Username</td>
                <td class="Table-cell">
                    <a><?= t('server.common.delete') ?></a>
                </td>
            </tr>
        </table>
    </div>

    <h3><?= t('server.tools.sales_promotion_rules_history') ?></h3>
    <div id="sale_rules_history">
        <table class="TableMultipleFreeleech Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.sales_promotion_range') ?></td>
                <td class="Table-cell"><?= t('server.tools.sales_promotion_plan') ?></td>
                <td class="Table-cell"><?= t('server.tools.sales_promotion_period') ?></td>
                <td class="Table-cell"><?= t('server.tools.added_on') ?></td>
                <td class="Table-cell"><?= t('server.tools.deleted_on') ?></td>
                <td class="Table-cell"><?= t('server.tools.operator') ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><a><?= t('server.tools.click_to_see') ?></a></td><!-- 这样设计是因为考虑到自定义一批种子或种子组，需要另行显示；如果是全局的批量，就直接显示文字就可以。 -->
                <td class="Table-cell">比方说免费</td>
                <td class="Table-cell">比方说 3 天</td>
                <td class="Table-cell">2021-05-13</td>
                <td class="Table-cell">2021-05-14</td>
                <td class="Table-cell">Username</td>
            </tr>
        </table>
    </div>
*/ ?>






    <div class="BoxBody box2">
        <? if (isset($Err)) { ?>
            <strong class="u-colorWarning"><?= $Err ?></strong><br />
        <?  } ?>
        <?= t('server.tools.paste_a_list_of_collage_or_torrent_group_urls') ?>
    </div>
    <div class="BoxBody">
        <form class="send_form center" action="" method="post">
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <textarea class="Input" name="torrents" style="width: 95%; height: 200px;"><?= $_POST['torrents'] ?></textarea><br /><br />
            <?= t('server.tools.mark_torrents_as') ?>:&nbsp;
            <select class="Input" name="freeleechtype">
                <option class="Select-option" value="1" <?= $_POST['freeleechtype'] == '1' ? 'selected' : '' ?>><?= t('server.tools.fl') ?></option>
                <option class="Select-option" value="2" <?= $_POST['freeleechtype'] == '2' ? 'selected' : '' ?>><?= t('server.tools.nl') ?></option>
                <option class="Select-option" value="0" <?= $_POST['freeleechtype'] == '0' ? 'selected' : '' ?>><?= t('server.tools.normal') ?></option>
            </select>&nbsp;<?= t('server.tools.for_reason') ?>&nbsp;<select class="Input" name="freeleechreason">
                <? $FL = array('N/A', 'Staff Pick', 'Perma-FL');
                foreach ($FL as $Key => $FLType) { ?>
                    <option class="Select-option" value="<?= $Key ?>" <?= $_POST['freeleechreason'] == $Key ? 'selected' : '' ?>><?= $FLType ?></option>
                <?      } ?>
            </select><br /><br />
            <input type="checkbox" name="NLOver" checked />&nbsp;<?= t('server.tools.nl_torrents_over') ?>
            <input class="Input" type="text" name="size" value="<?= isset($_POST['size']) ? $_POST['size'] : '1' ?>" size=1 />
            <select class="Input" name="scale">
                <option class="Select-option" value="k" <?= $_POST['scale'] == 'k' ? 'selected' : '' ?>>KB</option>
                <option class="Select-option" value="m" <?= $_POST['scale'] == 'm' ? 'selected' : '' ?>>MB</option>
                <option class="Select-option" value="g" <?= !isset($_POST['scale']) || $_POST['scale'] == 'g' ? 'selected' : '' ?>>GB</option>
            </select><?= t('server.tools.nl_torrents_over_after') ?><br /><br />
            <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
        </form>
    </div>
</div>
<?
View::show_footer();
