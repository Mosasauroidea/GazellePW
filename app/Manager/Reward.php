<?

namespace Gazelle\Manager;

use Gazelle\Action\RewardInfo;
use Badges;
use Misc;
use Gazelle\Exception\InvalidParamException;

class Reward extends \Gazelle\Base {
    public function sendReward(RewardInfo $rewardInfo, array $toUserIDs, string $remark, bool $pm = false, bool $system = false) {
        // check badgeID valid
        if (count($toUserIDs) <= 0) {
            return;
        }
        $send = false;
        $BadgeName = '';
        if (CONFIG['ENABLE_BADGE'] && $rewardInfo->badgeID > 0) {
            $Badge = Badges::get_badges_by_id($rewardInfo->badgeID);
            $BadgeName =  $Badge['Label'];
            if (empty($BadgeName)) {
                throw new InvalidParamException('badge id');
            }
            $this->addBadge($toUserIDs, $rewardInfo->badgeID);
            $send = true;
        }
        $this->db->begin_transaction();
        try {
            if ($rewardInfo->tokenCount > 0) {
                $this->addTokens($toUserIDs, $rewardInfo->tokenCount, $rewardInfo->tokenExpireTime);
                $send = true;
            }
            if ($rewardInfo->inviteCount > 0) {
                $this->addInvites($toUserIDs, $rewardInfo->inviteCount, $rewardInfo->invteExpireTime);
                $send = true;
            }
            if ($rewardInfo->bonus > 0) {
                $this->addBonus($toUserIDs, $rewardInfo->bonus);
                $send = true;
            }
            $this->recordRewardLog($rewardInfo, $toUserIDs, $remark, $system);
        } catch (\Exception $e) {
            $this->db->rollback();
        }
        if (!$send) {
            $this->db->rollback();
            return;
        }
        $this->db->commit();

        // clear cache
        if ($rewardInfo->tokenCount > 0 || $rewardInfo->inviteCount > 0) {
            $this->clearCache("user_info_heavy_", $toUserIDs);
        }
        if ($rewardInfo->bonus > 0) {
            $this->clearCache("user_stats_", $toUserIDs);
        }
        if ($rewardInfo->badgeID > 0) {
            $this->clearCache("badges_by_", $toUserIDs);
        }
        if (!$pm) {
            return;
        }

        // send pm
        foreach ($toUserIDs as $userID) {
            Misc::send_pm_with_tpl(
                $userID,
                'send_reward',
                [
                    'Invites' => $rewardInfo->inviteCount,
                    'InvitesTime' => $rewardInfo->invteExpireTime,
                    'Tokens' => $rewardInfo->tokenCount,
                    'TokensTime' => $rewardInfo->tokenExpireTime,
                    'Bonus' => $rewardInfo->bonus,
                    'BadgeName' => $BadgeName,
                ]
            );
        }
    }

    private function addBadge($toUserIDs, $badgeID) {
        foreach ($toUserIDs as $userID) {
            $this->db->prepared_query("insert ignore into badges (UserID, BadgeID) values (" . intval($userID) . ", " . intval($badgeID) . ")");
        }
    }

    private function addTokens($toUserIDs, $count, $expireTime) {
        $Where = " where id in (" . implode(",", $toUserIDs) . ")";
        $this->db->prepared_query("UPDATE users_main SET FLTokens = FLTokens + $count$Where");
        if ($expireTime) {
            $SQL = "INSERT INTO tokens_typed (`EndTime`, `Type`, `UserID`) VALUES";
            $Values = [];
            foreach ($toUserIDs as $UserID) {
                for ($i = 0; $i < $count; $i++) {
                    $Values[] = "('$expireTime', 'time', $UserID)";
                }
            }
            $SQL .= implode(',', $Values);
            $this->db->prepared_query($SQL);
        }
    }

    private function clearCache($keyPrefix, $userIDs) {
        foreach ($userIDs as $userID) {
            $this->cache->delete_value($keyPrefix . $userID);
        }
    }

    private function addInvites($userIDs, $count, $expireTime) {
        $Where = " where id in (" . implode(",", $userIDs) . ")";
        $this->db->prepared_query("UPDATE users_main SET Invites = Invites + $count$Where");
        if ($expireTime) {
            $SQL = "insert into invites_typed (`EndTime`, `Type`, `UserID`) values ";
            $Values = [];
            foreach ($userIDs as $UserID) {
                for ($i = 0; $i < $count; $i++) {
                    $Values[] = "('$expireTime', 'time', $UserID)";
                }
            }
            $SQL .= implode(',', $Values);
            $this->db->prepared_query($SQL);
        }
    }

    private function addBonus($userIDs, $count) {
        $Where = " where id in (" . implode(",", $userIDs) . ")";
        $this->db->prepared_query("UPDATE users_main SET BonusPoints = BonusPoints + $count$Where");
    }

    private function recordRewardLog(RewardInfo $rewardInfo, array $toUserIDs, string $remark, bool $system) {
        $fromUserID = 0;
        if (!$system) {
            $fromUserID = $this->user['ID'];
        }
        $Columns = "";
        $Values = "";
        if ($rewardInfo->inviteCount > 0) {
            $Columns .= ", Invites";
            $Values .= ", $rewardInfo->inviteCount";
            if ($rewardInfo->invteExpireTime) {
                $Columns .= ", InvitesTime";
                $Values .= ", '$rewardInfo->invteExpireTime'";
            }
        }
        if ($rewardInfo->tokenCount > 0) {
            $Columns .= ", Tokens";
            $Values .= ", $rewardInfo->tokenCount";
            if ($rewardInfo->tokenExpireTime) {
                $Columns .= ", TokensTime";
                $Values .= ", '$rewardInfo->tokenExpireTime'";
            }
        }
        if ($rewardInfo->bonus > 0) {
            $Columns .= ", Bonus";
            $Values .= ", $rewardInfo->bonus";
        }
        if ($rewardInfo->badgeID > 0) {
            $Columns .= ", Badge";
            $Values .= ", $rewardInfo->badgeID";
        }
        $this->db->prepared_query("INSERT INTO events_reward_log (UserIDs, ByUserID, Remark$Columns) VALUES ('" . implode(",", $toUserIDs) . "', " . $fromUserID . ", '$remark'$Values)");
    }
}
