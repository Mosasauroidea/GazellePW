<?

namespace  Gazelle\Action;

class BasicReward {
    public static function getReward($action): RewardInfo {
        $rewardInfo = new RewardInfo;
        $config = CONFIG['BaseRewardConfig'][$action];
        $rewardInfo->tokenCount = !empty($config['Token']) ? $config['Token'] : 0;
        if (!empty($config['TokenValidDay']) && $config['TokenValidDay'] > 0) {
            $rewardInfo->tokenExpireTime = add_day(date('Y-m-d'), $config['TokenValidDay']);
        }
        $rewardInfo->inviteCount = !empty($config['Invite']) ? $config['Invite'] : 0;
        if (!empty($config['InviteValidDay']) && $config['InviteValidDay'] > 1) {
            $rewardInfo->invteExpireTime = add_day(date('Y-m-d'), $config['InviteValidDay']);
        }
        $rewardInfo->bonus = !empty($config['Bonus']) ? $config['Bonus'] : 0;
        $rewardInfo->badgeID = !empty($config['Badge_id']) ? $config['Badge_id'] : 0;
        return $rewardInfo;
    }
}
