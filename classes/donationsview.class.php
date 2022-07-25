<?

use Gazelle\Manager\Donation;

class DonationsView {
    public static function render_mod_donations($Rank, $TotalRank) {
?>
        <table class="TableDonateBox Table Form-rowList">
            <tr class="Form-rowHeader">
                <td colspan="2"><?= t('server.user.donor_system_add_points') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.value') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" name="donation_value" onkeypress="return isNumberKey(event);" />
                    <select class="Input" name="donation_currency">
                        <option class="Select-option" value="CNY"><?= t('server.user.cny') ?></option>
                        <option class="Select-option" value="BTC"><?= t('server.user.btc') ?></option>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.reason') ?>:</td>
                <td class="Form-inputs"><input class="Input wide_input_text" type="text" name="donation_reason" /></td>
            </tr>
            <tr class="Form-row">
                <td align="right" colspan="2">
                    <input class="Button" type="submit" name="donor_points_submit" value="Add donor points" />
                </td>
            </tr>
        </table>

        <table class="TableDonorPoints Table Form-rowList" id="donor_points_box">
            <tr class="Form-rowHeader">
                <td colspan="3" data-tooltip='<?= t('server.user.donor_system_modify_values_title') ?>'><?= t('server.user.donor_system_modify_values') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label" data-tooltip="<?= t('server.user.active_points_title') ?>"><?= t('server.user.active_points') ?>:</td>
                <td class="Form-inputs"><input class="Input is-small" type="text" name="donor_rank" onkeypress="return isNumberKey(event);" value="<?= $Rank ?>" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label" data-tooltip="<?= t('server.user.total_points_title') ?>"><?= t('server.user.total_points') ?>:</td>
                <td class="Form-inputs"><input class="Input is-small" type="text" name="total_donor_rank" onkeypress="return isNumberKey(event);" value="<?= $TotalRank ?>" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.reason') ?>:</td>
                <td class="Form-inputs"><input class="Input wide_input_text" type="text" name="reason" /></td>
            </tr>
            <tr class="Form-row">
                <td align="right" colspan="2">
                    <input class="Button" type="submit" name="donor_values_submit" value="Change point values" />
                </td>
            </tr>
        </table>
        <?
    }

    public static function render_donor_stats($OwnProfile, $DonationInfo, $leadboardRank, $Visible, $IsDonor) {
        if (check_perms("users_mod") || $OwnProfile || $Visible) {
        ?>
            <div class="SidebarItemUserDonorStats SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <?= t('server.user.donor_statistics') ?></div>
                <ul class="SidebarList SidebarItem-body Box-body">
                    <?
                    if ($IsDonor) {
                        if (check_perms('users_mod') || $OwnProfile) {
                    ?>
                            <li class="SidebarList-item">
                                <?= t('server.user.total_donor_points') ?>: <?= $DonationInfo['TotRank'] ?>
                            </li class="SidebarList-item">
                        <?              } ?>
                        <li class="SidebarList-item">
                            <?= t('server.user.current_donor_rank') ?>: <?= self::render_rank($DonationInfo['Rank'], $DonationInfo['SRank']) ?>
                        </li>
                        <li class="SidebarList-item">
                            <?= t('server.user.current_special_donor_rank') ?>: <?= $DonationInfo['SRank'] ?>
                        </li>

                        <li class="SidebarList-item">
                            <?= t('server.user.leaderboard_position') ?>: <?= $leadboardRank ?>
                        </li>
                        <li class="SidebarList-item">
                            <?= t('server.user.last_donated') ?>: <?= time_diff($DonationInfo['Time']) ?>
                        </li>
                        <li class="SidebarList-item">
                            <?= t('server.user.rank_expires') ?>: <?= ($DonationInfo['ExpireTime']) ?>
                        </li>
                    <?          } else { ?>
                        <li class="SidebarList-item">
                            <?= t('server.user.rank_expires') ?>
                        </li>
                    <?          } ?>
                </ul>
            </div>
            <?
        }
    }

    public static function render_profile_rewards($EnabledRewards, $ProfileRewards) {
        for ($i = 1; $i <= 4; $i++) {
            if ($EnabledRewards['HasProfileInfo' . $i] && $ProfileRewards['ProfileInfo' . $i]) {
            ?>
                <div class="Box">
                    <div class="Box-header">
                        <span><?= !empty($ProfileRewards['ProfileInfoTitle' . $i]) ? display_str($ProfileRewards['ProfileInfoTitle' . $i]) : "Extra Profile " . ($i + 1) ?></span>
                        <span style="float: right;"><a href="#" onclick="$('#profilediv_<?= $i ?>').gtoggle(); this.innerHTML = (this.innerHTML == '<?= t('server.global.hide') ?>' ? '<?= t('server.global.show') ?>' : '<?= t('server.global.hide') ?>'); return false;" class="brackets"><?= t('server.global.hide') ?></a></span>
                    </div>
                    <div class="Box-body HtmlText PostArticle profileinfo" id="profilediv_<?= $i ?>">
                        <?= Text::full_format($ProfileRewards['ProfileInfo' . $i]) ?>
                    </div>
                </div>
        <?
            }
        }
    }

    public static function render_donation_history($DonationHistory) {
        if (empty($DonationHistory)) {
            return;
        }
        ?>
        <div class="Box" id="donation_history_box">
            <div class="Box-header">
                <?= t('server.user.donation_history') ?> <a href="#" onclick="$('#donation_history').gtoggle(); return false;" class="brackets"><?= t('server.user.view') ?></a>
            </div>
            <? $Row = 'b'; ?>
            <div class="Box-body TableContainer hidden" id="donation_history">
                <table class="Table">
                    <tbody>
                        <tr class="Table-rowHeader">
                            <td class="Table-cell">
                                <strong><?= t('server.user.source') ?></strong>
                            </td>
                            <td class="Table-cell">
                                <strong><?= t('server.user.date') ?></strong>
                            </td>
                            <td class="Table-cell">
                                <strong><?= t('server.user.amount_cny') ?></strong>
                            </td>
                            <td class="Table-cell">
                                <strong><?= t('server.user.added_points') ?></strong>
                            </td>
                            <td class="Table-cell">
                                <strong><?= t('server.user.total_points') ?></strong>
                            </td>
                            <td class="Table-cell">
                                <strong><?= t('server.user.email') ?></strong>
                            </td>
                            <td class="Table-cell" style="width: 30%;">
                                <strong><?= t('server.user.reason') ?></strong>
                            </td>
                        </tr>
                        <? foreach ($DonationHistory as $Donation) { ?>
                            <tr class="row<?= $Row ?>">
                                <td>
                                    <?= display_str($Donation['Source']) ?> (<?= Users::format_username($Donation['AddedBy']) ?>)
                                </td>
                                <td>
                                    <?= $Donation['Time'] ?>
                                </td>
                                <td>
                                    <?= $Donation['Amount'] ?>
                                </td>
                                <td>
                                    <?= $Donation['Rank'] ?>
                                </td>
                                <td>
                                    <?= $Donation['TotalRank'] ?>
                                </td>
                                <td>
                                    <?= display_str($Donation['Email']) ?>
                                </td>
                                <td>
                                    <?= display_str($Donation['Reason']) ?>
                                </td>
                            </tr>
                        <?
                            $Row = $Row === 'b' ? 'a' : 'b';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
<?
    }

    public static function render_rank($rank, $specialRank, $ShowOverflow = true) {
        echo Donation::rankLabel($rank, $specialRank, $ShowOverflow);
    }
}
