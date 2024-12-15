<?

use Gazelle\Manager\Donation;

class DonationsView {
    public static function render_mod_donations($Rank, $TotalRank) {
?>
        <tr class="Form-rowSubHeader">
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
                        <?
                        } ?>
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
                    <?
                    } else {
                    ?>
                        <li class="SidebarList-item">
                            <?= t('server.common.no_results') ?>
                        </li>
                    <?
                    } ?>
                </ul>
            </div>
            <?
        }
    }

    public static function render_profile_rewards($EnabledRewards, $ProfileRewards) {
        for ($i = 1; $i <= 4; $i++) {
            if ($EnabledRewards['HasProfileInfo' . $i] && $ProfileRewards['ProfileInfo' . $i]) {
            ?>
                <div class="Post">
                    <div class="Post-header">
                        <div class="Post-headerLeft">
                            <div class="Post-headerTitle">
                                <span><?= !empty($ProfileRewards['ProfileInfoTitle' . $i]) ? display_str($ProfileRewards['ProfileInfoTitle' . $i]) : "Extra Profile " . ($i + 1) ?></span>
                            </div>
                        </div>
                        <div class="Post-headerActions">
                            <a href="#" onclick="globalapp.toggleAny(event, '#profilediv_<?= $i ?>');return false;">
                                <span class="u-toggleAny-show u-hidden"><?= t('server.common.show') ?></span>
                                <span class="u-toggleAny-hide"><?= t('server.common.hide') ?></span>
                            </a>
                        </div>
                    </div>
                    <div class="Post-body HtmlText PostArticle profileinfo" id="profilediv_<?= $i ?>">
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
        <div class="Group" id="donation_history_box">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.user.donation_history') ?>
                </div>

                <div class="Group-headerActions">
                    <a href="#" onclick="globalapp.toggleAny(event, '#donation_history');return false;">
                        <span class="u-toggleAny-show"><?= t('server.common.show') ?></span>
                        <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                    </a>
                </div>
            </div>
            <div class="Group-body TableContainer u-hidden" id="donation_history">
                <table class="Table">
                    <tbody>
                        <tr class="Table-rowHeader">
                            <td class="Table-cell">
                                <strong><?= t('server.user.source') ?></strong>
                            </td>
                            <td class="Table-cell ">
                                <strong><?= t('server.user.date') ?></strong>
                            </td>
                            <td class="Table-cell">
                                <strong><?= t('server.user.amount_cny') ?></strong>
                            </td>
                            <td class="Table-cell Table-cellRight">
                                <strong><?= t('server.user.added_points') ?></strong>
                            </td>
                            <td class="Table-cell Table-cellRight">
                                <strong><?= t('server.user.total_points') ?></strong>
                            </td>
                            <td class="Table-cell Table-cellRight" style="width: 30%;">
                                <strong><?= t('server.user.reason') ?></strong>
                            </td>
                        </tr>
                        <? foreach ($DonationHistory as $Donation) { ?>
                            <tr class="Table-row">
                                <td class="Table-cell">
                                    <?= display_str($Donation['Source']) ?> (<?= Users::format_username($Donation['AddedBy']) ?>)
                                </td>
                                <td class="Table-cell">
                                    <?= $Donation['Time'] ?>
                                </td>
                                <td class="Table-cell">
                                    <?= $Donation['Amount'] ?>
                                </td>
                                <td class="Table-cell Table-cellRight">
                                    <?= $Donation['Rank'] ?>
                                </td>
                                <td class="Table-cell Table-cellRight">
                                    <?= $Donation['TotalRank'] ?>
                                </td>
                                <td class="Table-cell Table-cellRight">
                                    <?= display_str($Donation['Reason']) ?>
                                </td>
                            </tr>
                        <?
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
<?
    }

    public static function render_rank($rank, $specialRank, $ShowOverflow = true) {
        $donate = new Donation;
        echo $donate->rankLabel($rank, $specialRank, $ShowOverflow);
    }
}
