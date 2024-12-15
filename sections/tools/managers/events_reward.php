<?

use Gazelle\Manager\Reward;
use Gazelle\Action\RewardInfo;
use Gazelle\Exception\InvalidParamException;

function getUserIDs($UsernamesString) {
    $Usernames = preg_split("/[\s,]+/", $UsernamesString);
    G::$DB->query("select ID from users_main where username in ('" . implode("','", $Usernames) . "')");
    $UserIDs = G::$DB->collect('ID');
    if (count($UserIDs) != count($Usernames)) {
        return false;
    } else {
        return $UserIDs;
    }
}

if (!check_perms('users_mod')) {
    error(403);
}
if (isset($_POST['action'])) {
    switch ($_POST['add_all']) {
        case '-1':
            error(t('server.common.invalid_param'));
            break;
        case '0':
            $Usernames = db_string($_POST['usernames']);
            if (!$Usernames) {
                error(t('server.common.invalid_param'));
            }
            if (!($UserIDs = getUserIDs($Usernames))) {
                error(t('server.common.invalid_param'));
            }
            break;
        case '1':
            if (isset($_POST['add_disabled'])) {
                $Where = "";
            } else {
                $Where = " where Enabled='1'";
            }
            G::$DB->query("select ID from users_main$Where");
            $UserIDs = G::$DB->collect("ID");
            break;
    }
    $Send = false;
    if (!empty($_POST['tokens_numbers'])) {
        if (!check_perms('events_reward_tokens')) error(403);
        if ($_POST['tokens_permanent'] === "" || empty($_POST['tokens_numbers'])) {
            error(t('server.common.invalid_param'));
        }
        $TokensPermanent = $_POST['tokens_permanent'] ? true : false;
        if (!$TokensPermanent) {
            $TokensTime = db_string($_POST['tokens_time']);
            if (empty($TokensTime)) {
                error(t('server.common.invalid_param'));
            }
        } else {
            $TokensTime = false;
        }
        $TokensNumbers = intval($_POST['tokens_numbers']);
        if ($TokensNumbers <= 0) {
            error(t('server.common.invalid_param'));
        }
        $SendToLeechDisabled = isset($_POST['leechdisabled']);
        $Send = true;
    }
    if (!empty($_POST['invites_numbers'])) {
        if (!check_perms('events_reward_invites')) error(403);
        if ($_POST['invites_permanent'] === "" || empty($_POST['invites_numbers'])) {
            error(t('server.common.invalid_param'));
        }
        $InvitesPermanent = $_POST['invites_permanent'] ? true : false;
        if (!$InvitesPermanent) {
            $InvitesTime = db_string($_POST['invites_time']);
            if (empty($InvitesTime)) {
                error(t('server.common.invalid_param'));
            }
        } else {
            $InvitesTime = false;
        }
        $InvitesNumbers = intval($_POST['invites_numbers']);
        if ($InvitesNumbers <= 0) {
            error(t('server.common.invalid_param'));
        }
        $Send = true;
    }
    if (!empty($_POST['bonus_numbers'])) {
        if (!check_perms('events_reward_bonus')) {
            error(t('server.common.invalid_param'));
        }
        if (empty($_POST['bonus_numbers'])) {
            error(t('server.common.invalid_param'));
        }
        $BonusNumbers = intval($_POST['bonus_numbers']);
        if ($BonusNumbers <= 0) {
            error(t('server.common.invalid_param'));
        }
        $Send = true;
    }
    if (!empty($_POST['badgeid'])) {
        if (!check_perms('events_reward_badges')) error(403);
        if (empty($_POST['badgeid'])) {
            error(t('server.common.invalid_param'));
        }
        $BadgeID = intval($_POST['badgeid']);
        $Send = true;
    }
    if (!$Send) {
        error(t('server.common.invalid_param'));
    }
    $PM = db_string($_POST['pm']);
    $RewardInfo = new RewardInfo;
    $RewardInfo->tokenCount = $TokensNumbers ? $TokensNumbers : 0;
    $RewardInfo->tokenExpireTime = $TokensTime;
    $RewardInfo->inviteCount = $InvitesNumbers ? $InvitesNumbers : 0;
    $RewardInfo->invteExpireTime = $InvitesTime;
    $RewardInfo->badgeID = $BadgeID ? $BadgeID : 0;
    $RewardInfo->bonus = $BonusNumbers ? $BonusNumbers : 0;
    $RewardManager = new Reward;
    try {
        $RewardManager->sendReward($RewardInfo, $UserIDs, $PM, true);
    } catch (InvalidParamException $e) {
        error($e->getMessage());
    } catch (Exception $e) {
        error_log($e->getMessage());
        error("internal error");
    }
    header("Location: tools.php?action=events_reward_history");
    exit();
}
View::show_header(t('server.tools.events_reward'));

$Badges = Badges::get_badges_by_id();
$BadgeLabels = Badges::get_badge_labels();
?>

<div class="LayoutBody">
    <script>
        function check() {
            var str = '发放用户：'
            if ($('#add_all_1').prop('checked')) {
                if ($('#add_disabled').prop('checked')) {
                    str += '全体用户'
                } else {
                    str += '全体启用用户'
                }
            } else if ($('#add_all_0').prop('checked')) {
                str += $('#usernames').val() + ' (' + $('#usernames').val().split(/[\s,]+/).length + ')'

            } else {
                return false;
            }
            if ($('#someone_tokens_input').prop('checked')) {
                str += '\n\n'
                if ($('#tokens_permanent_1').prop('checked')) {
                    str += '发放永久令牌'
                } else if ($('#tokens_permanent_0').prop('checked')) {
                    str += '发放临时令牌 ('
                    str += $('#tokens_time').val() + ')'
                } else {
                    return false;
                }
                if ($('#leechdisabled').prop('checked')) {
                    str += ' (包括禁止下载用户)'
                } else {
                    str += ' (不包括禁止下载用户)'
                }
                str += '：' + $('#tokens_numbers').val()
            }
            if ($('#someone_invites_input').prop('checked')) {
                str += '\n\n'
                if ($('#invites_permanent_1').prop('checked')) {
                    str += '发放永久邀请'
                } else if ($('#invites_permanent_0').prop('checked')) {
                    str += '发放临时邀请 ('
                    str += $('#invites_time').val() + ')'
                } else {
                    return false;
                }
                str += '：' + $('#invites_numbers').val()
            }
            if ($('#someone_bonus_input').prop('checked')) {
                str += '\n\n'
                str += '发放积分：' + $('#bonus_numbers').val()
            }
            if ($('#someone_badges_input').prop('checked')) {
                str += '\n\n'
                str += '发放印记：' + $("#badgeid").find("option:selected").text()
            }
            str += '\n\n理由：' + $("#pm").val()
            return confirm(str);
        }
    </script>
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.events_reward') ?></h2>
        <div class="BodyNavLinks">
            <div><a href="tools.php?action=events_reward_history" class="brackets"><?= t('server.tools.events_reward_history') ?></a></div>
        </div>
    </div>
    <form class="Form" method="post" onsubmit="return check()">
        <input type="hidden" name="action" value="events_reward" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.tools.events_reward') ?>
            </div>
            <div class="Form-row" id="add_to">
                <div class="Form-label"><?= t('server.tools.add_to') ?>:</div>
                <div class="Form-inputs">
                    <div class="RadioGroup">
                        <div class="Radio">
                            <input class="Input" id="add_all_1" type="radio" name="add_all" value="1" onclick="$('.add_disabled').show();$('#usernames').hide()" /><label class="Radio-label" for="add_all_1"><?= t('server.tools.add_all') ?></label>
                        </div>
                        <div class="Radio">
                            <input class="Input" id="add_all_0" type="radio" name="add_all" value="0" onclick="$('.add_disabled').hide();$('#usernames').show()" /><label class="Radio-label" for="add_all_0"><?= t('server.tools.add_part') ?></label>
                        </div>
                    </div>
                    <div class="Checkbox">
                        <input class="Input add_disabled" id="add_disabled" name="add_disabled" style="display: none;" type="checkbox" />
                        <label style="display: none;" class="Checkbox-label  add_disabled" for="add_disabled"><?= t('server.tools.add_to_disabled_users') ?></label>
                    </div>
                    <textarea class="Input" id="usernames" name="usernames" style="display: none;" placeholder="<?= t('server.tools.add_to_placeholder') ?>"></textarea>
                </div>
            </div>
            <? if (check_perms('events_reward_tokens')) { ?>
                <div class="Form-rowSubHeader">
                    <?= t('server.tools.someone_tokens') ?>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.token_type') ?>:</div>
                    <div class="Form-inputs">
                        <div class="RadioGroup">
                            <div class="Radio">
                                <input class="Input" id="tokens_permanent_1" type="radio" name="tokens_permanent" value="1" onclick="$('#tokens_time').hide()" />
                                <label class="Radio-label" for="tokens_permanent_1"><?= t('server.tools.permanent') ?></label>
                            </div>
                            <div class="Radio">
                                <input class="Input" id="tokens_permanent_0" type="radio" name="tokens_permanent" value="0" onclick="$('#tokens_time').show()" />
                                <label class="Radio-label" for="tokens_permanent_0"><?= t('server.tools.temporary') ?></label>
                            </div>
                        </div>
                        <input id="tokens_time" type="date" name="tokens_time" style="display: none;" />
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.token_number') ?>:</div>
                    <div class="Form-inputs">
                        <input class="Input is-small" type="number" id="tokens_numbers" name="tokens_numbers">
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label">
                    </div>
                    <div class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" id="leechdisabled" name="leechdisabled" type="checkbox">
                            <label class="Checkbox-label" for="leechdisabled"><?= t('server.tools.leechdisabled') ?></label>
                        </div>
                    </div>
                </div>
            <? }
            if (check_perms('events_reward_invites')) { ?>
                <div class="Form-rowSubHeader">
                    <?= t('server.tools.someone_invites') ?>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.invites_type') ?>:</div>
                    <div class="Form-inputs">
                        <div class="RadioGroup">
                            <div class="Radio">
                                <input class="Input" id="invites_permanent_1" type="radio" name="invites_permanent" value="1" onclick="$('#invites_time').hide()" />
                                <label class="Radio-label" for="invites_permanent_1"><?= t('server.tools.permanent') ?></label>
                            </div>
                            <div class="Radio">
                                <input class="Input" id="invites_permanent_0" type="radio" name="invites_permanent" value="0" onclick="$('#invites_time').show()" />
                                <label for="invites_permanent_0" class="Radio-label"><?= t('server.tools.temporary') ?></label>
                            </div>
                        </div>
                        <input id="invites_time" type="date" name="invites_time" style="display: none;" />
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.invites_number') ?>:</div>
                    <div class="Form-inputs">
                        <input class="Input is-small" type="number" id="invites_numbers" name="invites_numbers" />
                    </div>
                </div>
            <? }
            if (check_perms('events_reward_bonus')) { ?>
                <div class="Form-rowSubHeader">
                    <?= t('server.tools.someone_bonus') ?>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.bonus_number') ?>:</div>
                    <div class="Form-inputs">
                        <input class="Input is-small" type="number" id="bonus_numbers" name="bonus_numbers" />
                    </div>
                </div>
            <? }
            if (check_perms('events_reward_badges') && CONFIG['ENABLE_BADGE']) { ?>
                <div class="Form-rowSubHeader">
                    <?= t('server.tools.badge_send') ?>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.badge_send') ?>:</div>
                    <div class="Form-inputs">
                        <select class="Input" id="badgeid" name='badgeid'>
                            <?
                            foreach ($Badges as $Badge) {
                                echo "<option  class='Select-option' value='" . $Badge['ID'] . "'>" . $Badge['Level'] . "-" . $BadgeLabels[$Badge['Label']]['Remark'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <? } ?>
            <div class="Form-rowSubHeader">
                <?= t('server.tools.reason') ?>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.tools.reason') ?>:</div>
                <div class="Form-inputs">
                    <input class="Input" type="text" id="pm" name="pm" placeholder="<?= t('server.tools.reason_placeholder') ?>" size="80" />
                </div>
            </div>
            <div class="Form-row">
                <div><input class="Button" type="submit" value="<?= t('server.tools.send_rewards') ?>"></div>
            </div>
        </div>
    </form>
</div>
<?
View::show_footer()
?>