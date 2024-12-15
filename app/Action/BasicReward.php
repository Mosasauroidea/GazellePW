<?

namespace  Gazelle\Action;

class BasicReward {
    public static function getReward($action): RewardInfo {
        $rewardInfo = new RewardInfo;
        global $BaseRewardConfig;
        $config = $BaseRewardConfig[$action];
        $rewardInfo->tokenCount = !empty($config['token']) ? $config['token'] : 0;
        if (!empty($config['token_valid_day']) && $config['token_valid_day'] > 0) {
            $rewardInfo->tokenExpireTime = add_day(date('Y-m-d'), $config['token_valid_day']);
        }
        $rewardInfo->inviteCount = !empty($config['invite']) ? $config['invite'] : 0;
        if (!empty($config['invite_valid_day']) && $config['invite_valid_day'] > 0) {
            $rewardInfo->invteExpireTime = add_day(date('Y-m-d'), $config['invite_valid_day']);
        }
        $rewardInfo->bonus = !empty($config['bonus']) ? $config['bonus'] : 0;
        $rewardInfo->badgeID = !empty($config['badge_id']) ? $config['badge_id'] : 0;
        return $rewardInfo;
    }
}
