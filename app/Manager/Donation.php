<?php

namespace Gazelle\Manager;

use Misc;
use Lang;
use DB_MYSQL_DuplicateKeyException;
use Gazelle\Action\RewardInfo;

class DonationSource {
    const PrepaidCard = "Prepaid Card";
    const AddPoint = "Add Points";
    const StaffPM = "Staff PM";
    const ModifyValue = "Modify Values";
}

class PrepaidCardStatus {
    const Pending = '1';
    const Passed = '2';
    const Reject = '3';
}


class DonationCurrency {
    const CNY = "CNY";
    const BTC = "BTC";
}
class Donation extends \Gazelle\Base {
    private Reward $rewardManager;
    private static $ForumDescriptions = array(
        "I want only two houses, rather than seven... I feel like letting go of things",
        "A billion here, a billion there, sooner or later it adds up to real money.",
        "I've cut back, because I'm buying a house in the West Village.",
        "Some girls are just born with glitter in their veins.",
        "I get half a million just to show up at parties. My life is, like, really, really fun.",
        "Some people change when they think they're a star or something",
        "I'd rather not talk about money. It’s kind of gross.",
        "I have not been to my house in Bermuda for two or three years, and the same goes for my house in Portofino. How long do I have to keep leading this life of sacrifice?",
        "When I see someone who is making anywhere from $300,000 to $750,000 a year, that's middle class.",
        "Money doesn't make you happy. I now have $50 million but I was just as happy when I had $48 million.",
        "I'd rather smoke crack than eat cheese from a tin.",
        "I am who I am. I can’t pretend to be somebody who makes $25,000 a year.",
        "A girl never knows when she might need a couple of diamonds at ten 'o' clock in the morning.",
        "I wouldn't run for president. I wouldn't want to move to a smaller house.",
        "I have the stardom glow.",
        "What's Walmart? Do they like, sell wall stuff?",
        "Whenever I watch TV and see those poor starving kids all over the world, I can't help but cry. I mean I'd love to be skinny like that, but not with all those flies and death and stuff.",
        "Too much money ain't enough money.",
        "What's a soup kitchen?",
        "I work very hard and I’m worth every cent!",
        "To all my Barbies out there who date Benjamin Franklin, George Washington, Abraham Lincoln, you'll be better off in life. Get that money."
    );
    public function __construct() {
        parent::__construct();
        $this->rewardManager = new Reward;
    }

    public function moderatorAdjust(int $UserID, int $Rank, int $TotalRank, string $Reason, int $who) {
        $this->donate($UserID, [
            'Manipulation' => 'Direct',
            "Source" => "Modify Values",
            "Rank" => (int)$Rank,
            "TotalRank" => (int)$TotalRank,
            "SendPM" => false,
            "Reason" => $Reason,
            "AddedBy"    => $who,
        ]);
    }

    public function moderatorDonate(int $UserID, string $amount, string $Currency, string $Reason, string $source, int $who) {
        $this->donate($UserID, [
            "Source" => $source,
            "Amount" => $amount,
            "Currency" => $Currency,
            "SendPM" => true,
            "Reason" => $Reason,
            "AddedBy"    => $who,
        ]);
    }

    public function prepaidCardDonate(int $PrepaidCardID, $Who) {
        $prepaidCardInfo = $this->prepaidCard($PrepaidCardID);
        if (empty($prepaidCardInfo)) {
            return t('server.donate.donate_error');
        }
        $UserID = $prepaidCardInfo['user_id'];
        $this->db->prepared_query(
            '
            	UPDATE donations_prepaid_card SET STATUS = ? WHERE id = ?',
            PrepaidCardStatus::Passed,
            $PrepaidCardID
        );

        $text = $this->donate($UserID, [
            "Source" => DonationSource::PrepaidCard,
            "Currency" => DonationCurrency::CNY,
            "SendPM" => true,
            "AddedBy"    => $Who,
            'Amount' => $prepaidCardInfo['face_value'],
            'Reason' => $PrepaidCardID,
        ]);

        $this->cache->delete_value("user_donations_prepaid_card_$UserID");
        return $text;
    }

    public function regularDonate(int $UserID, string $DonationAmount, string $Source, string $Reason, $Currency = "CNY") {
        $this->donate($UserID, [
            "Source" => $Source,
            "Amount" => $DonationAmount,
            "Currency" => $Currency,
            "SendPM" => true,
            "Reason" => $Reason,
            "AddedBy"    => $UserID,
        ]);
    }

    function currencyExchange($Amount, $Currency) {
        switch ($Currency) {
            case 'BTC':
                $XBT = new \Gazelle\Manager\XBT;
                $forexRate = $XBT->latestRate('CNY');

                $Amount = $Amount * $forexRate;
                break;
            default:
                break;
        }
        return round($Amount, 2);
    }

    public function prepaidCard(int $id) {
        $this->db->prepared_query(
            "SELECT * FROM donations_prepaid_card WHERE id = ?",
            $id
        );
        $Result = $this->db->next_record();
        return $Result;
    }

    public function getAllPrepaidCardDonations($Limit) {
        $this->db->prepared_query(
            "SELECT SQL_CALC_FOUND_ROWS * FROM donations_prepaid_card ORDER BY create_time desc limit $Limit "
        );
        $Result = $this->db->to_array();
        $this->db->query('SELECT FOUND_ROWS()');
        list($NumResults) = $this->db->next_record();
        return [$NumResults, $Result];
    }

    public function getPrepaidCardDonations($UserID) {
        if (!$Value = $this->cache->get_value("user_donations_prepaid_card_$UserID")) {
            $this->db->prepared_query(
                'SELECT * FROM donations_prepaid_card WHERE user_id = ? ORDER BY create_time desc',
                $UserID
            );
            $Value = $this->db->to_array(false, MYSQLI_NUM);
            $this->cache->cache_value("user_donations_prepaid_card_$UserID", $Value);
        }
        return $Value;
    }

    public function preDonatePrepaidCard($UserID, $CardNum, $CardSecret, $FaceValue) {
        $Date = sqltime();
        try {
            $this->db->prepared_query('INSERT INTO donations_prepaid_card (user_id, create_time, card_num, card_secret, face_value)
                VALUES (?, ?, ?, ?, ?)', $UserID, $Date, $CardNum, $CardSecret, $FaceValue);
        } catch (DB_MYSQL_DuplicateKeyException $e) {
            return t('server.donate.duplicated_card');
        }

        $this->cache->delete_value("user_donations_prepaid_card_$UserID");
        $this->cache->delete_value("donations_pending_count");
        return '';
    }

    public function getPendingDonationCount() {
        if ($Count = $this->cache->get_value("donations_pending_count") === false) {
            $this->db->prepared_query('SELECT count(*) FROM donations_prepaid_card where status = ?', PrepaidCardStatus::Pending);
            list($Count) = $this->db->next_record();
            $this->cache->cache_value("donations_pending_count", $Count);
        }
        return $Count;
    }


    public function getYearProgress() {
        if (($YearSum = $this->cache->get_value("donations_year_sum")) === false) {
            $this->db->query(
                "SELECT sum(rank) from donations 
	            where time >= '" . date("Y-01-01") . "'"
            );
            list($YearSum) = $this->db->next_record();
            $this->cache->cache_value("donations_year_sum", $YearSum, 86400);
        }
        if (empty($YearSum)) {
            return 0;
        }
        return number_format(($YearSum * 50 / (CONFIG['DONATE_MONTH_GOAL'] * 12) * 100));
    }

    public function rejectPrepaidCard($PrepaidCardID) {
        $prepaidCardInfo = $this->prepaidCard($PrepaidCardID);
        if (empty($prepaidCardInfo)) {
            return t('server.donate.donate_error');
        }
        $UserID = $prepaidCardInfo['user_id'];

        $this->db->prepared_query(
            '
            	UPDATE donations_prepaid_card SET STATUS = ? WHERE id = ?',
            PrepaidCardStatus::Reject,
            $PrepaidCardID
        );
        Misc::send_pm_with_tpl($UserID, 'reject_prepaid_card', ['SiteURL' => CONFIG['SITE_URL']]);
        $this->cache->delete_value("user_donations_prepaid_card_$UserID");
        $this->cache->delete_value("donations_pending_count");
        return "";
    }

    private function donate(int $UserID, array $Args) {
        $UserID = (int)$UserID;
        $QueryID = $this->db->get_query_id();

        $this->db->query("
			SELECT 1
			FROM users_main
			WHERE ID = '$UserID'
			LIMIT 1");
        if (!$this->db->has_results()) {
            return t('server.donate.donate_error');
        }

        $this->cache->InternalCache = false;
        foreach ($Args as &$Arg) {
            $Arg = db_string($Arg);
        }
        extract($Args);
        if (empty($Date)) {
            $Date = sqltime();
        }

        // Legacy donor, should remove at some point
        $this->db->query("
        	UPDATE users_info
				SET Donor = '1'
				WHERE UserID = '$UserID'");
        // Give them the extra invite
        $ExtraInvite = $this->db->affected_rows();

        // A staff member is directly manipulating donor points
        if (isset($Manipulation) && $Manipulation === "Direct") {
            $DonorPoints = $Rank;
            $AdjustedRank = $Rank >= MAX_RANK ? MAX_RANK : $Rank;
            $this->db->query("
				INSERT INTO users_donor_ranks
						(UserID, Rank, TotalRank, DonationTime, RankExpirationTime)
					VALUES
						('$UserID', '$AdjustedRank', '$TotalRank', '$Date', NOW())
					ON DUPLICATE KEY UPDATE
						Rank = '$AdjustedRank',
						TotalRank = '$TotalRank',
						DonationTime = '$Date',
						RankExpirationTime = NOW()");
        } else {
            // Donations from the store get donor points directly, no need to calculate them
            $ConvertedPrice = $this->currencyExchange($Amount, $Currency);
            // 计算捐赠点数
            $DonorPoints = $ConvertedPrice / 50;
            $IncreaseRank = $DonorPoints;

            // Rank is the same thing as DonorPoints
            $CurrentRank = $this->rank($UserID);
            // A user's donor rank can never exceed MAX_RANK
            // If the amount they donated causes it to overflow, chnage it to MAX_RANK
            // The total rank isn't affected by this, so their original donor point value is added to it
            if (($CurrentRank + $DonorPoints) >= MAX_RANK) {
                $AdjustedRank = MAX_RANK;
            } else {
                $AdjustedRank = $CurrentRank + $DonorPoints;
            }
            $this->db->query("
				INSERT INTO users_donor_ranks
						(UserID, Rank, TotalRank, DonationTime, RankExpirationTime)
					VALUES
						('$UserID', '$AdjustedRank', '$DonorPoints', '$Date', NOW())
					ON DUPLICATE KEY UPDATE
						Rank = '$AdjustedRank',
						TotalRank = TotalRank + '$DonorPoints',
						DonationTime = '$Date',
						RankExpirationTime = NOW()");
        }
        // Donor cache key is outdated
        $this->cache->delete_value("donor_info_$UserID");

        // Get their rank
        $Rank = $this->rank($UserID);
        $TotalRank = $this->totalRank($UserID);

        // Now that their rank and total rank has been set, we can calculate their special rank
        $this->calculateSpecialRank($UserID);

        // Lastly, add this donation to our history
        $this->db->query("
                INSERT INTO donations
					(UserID, Amount, Source, Reason, Currency, Email, Time, AddedBy, Rank, TotalRank)
				VALUES
					('$UserID', '$ConvertedPrice', '$Source', '$Reason', '$Currency', '', '$Date', '$AddedBy', '$DonorPoints', '$TotalRank' )");


        // Send them a thank you PM
        if ($SendPM) {
            Misc::send_pm_with_tpl(
                $UserID,
                'donation_received',
                [
                    'DonationAmount' => $Amount . ' ' . $Currency,
                    'ReceivedRank' => $IncreaseRank,
                    'CurrentRank' => $this->rankLabel($Rank, $SpecialRank),
                    'SiteName' => CONFIG['SITE_NAME'],
                    'SiteURL' => CONFIG['SITE_URL'],
                ]
            );
        }

        // Clear their user cache keys because the users_info values has been modified
        $this->cache->delete_value("user_info_$UserID");
        $this->cache->delete_value("user_info_heavy_$UserID");
        $this->cache->delete_value("donor_info_$UserID");
        $this->cache->delete_value("donations_pending_count");
        $this->cache->delete_value("donations_year_sum");
        $this->db->set_query_id($QueryID);
        return '';
    }

    public static function rankLabel($rank, $specialRank, $ShowOverflow = true) {
        if ($specialRank == MAX_SPECIAL_RANK) {
            return '∞ (' . t('server.donate.diamond_rank') . ')';
        }
        $label = $rank >= MAX_RANK ? MAX_RANK : $rank;
        $overflow = $rank - $label;
        if ($ShowOverflow && $overflow) {
            $label .= " (+$overflow)";
        }
        if ($rank >= 6) {
            $label .= ' (' . t('server.donate.gold_rank') . ')';
        } elseif ($rank >= 4) {
            $label .= ' (' . t('server.donate.silver_rank') . ')';
        } elseif ($rank >= 3) {
            $label .= ' (' . t('server.donate.bronze_rank') . ')';
        } elseif ($rank >= 2) {
            $label .= ' (' . t('server.donate.copper_rank') . ')';
        } elseif ($rank >= 1) {
            $label .= ' (' . t('server.donate.red_rank') . ')';
        }
        return $label;
    }

    public function forumDescription() {
        return self::$ForumDescriptions[rand(0, count(self::$ForumDescriptions) - 1)];
    }

    protected function calculateSpecialRank(int $UserID) {
        $UserID = (int)$UserID;
        $UserLang = Lang::getUserLang($UserID);
        $QueryID = $this->db->get_query_id();
        // Are they are special?
        $TotalRank = $this->totalRank($UserID);
        $SpecialRank = $this->specialRank($UserID);
        $Invite = 0;
        // Adjust their special rank depending on the total rank.
        if ($TotalRank < 2) {
            $SpecialRank = 0;
        }
        if ($SpecialRank < 1 && $TotalRank >= 2) {
            $Invite += 2;
            $SpecialRank = 1;
        }
        if ($SpecialRank < 2 && $TotalRank >= 6) {
            $Invite += 2;
            $SpecialRank = 2;
        }
        if ($SpecialRank < 3 && $TotalRank >= 12) {
            $Invite += 2;
            $SpecialRank = 3;
        }
        if ($SpecialRank < 4 && $TotalRank >= 24) {
            $Invite += 4;
            $SpecialRank = 4;
        }
        if ($SpecialRank < 5 && $TotalRank >= 50) {
            $Invite += 6;
            $SpecialRank = 5;
        }

        $this->db->query("
				UPDATE users_donor_ranks
				SET SpecialRank = '$SpecialRank'
				WHERE UserID = '$UserID'");
        if ($Invite > 0) {
            $rewardInfo = new RewardInfo;
            $rewardInfo->inviteCount = $Invite;
            $this->rewardManager->sendReward($rewardInfo, [$UserID], "Donor reward.", false, true);
        }
        $this->cache->delete_value("donor_info_$UserID");
        $this->db->set_query_id($QueryID);
    }

    protected function addDonorStatus(int $UserID): int {
        if (($class = $this->db->scalar('SELECT ID FROM permissions WHERE Name = ?', 'Donor')) !== null) {
            $this->db->prepared_query(
                '
                INSERT IGNORE INTO users_levels
                       (UserID, PermissionID)
                VALUES (?,      ?)
                ',
                $UserID,
                $class
            );
            return $this->db->affected_rows();
        }
        return 0;
    }

    protected function removeDonorStatus(int $UserID): int {
        $class = $this->db->scalar('SELECT ID FROM permissions WHERE Name = ?', 'Donor');
        if ($class) {
            $this->db->prepared_query(
                '
                DELETE FROM users_levels
                WHERE UserID = ?
                    AND PermissionID = ?
                ',
                $UserID,
                $class
            );
        }
        $this->db->prepared_query(
            '
            UPDATE users_donor_ranks SET
                SpecialRank = 0
            WHERE UserID = ?
            ',
            $UserID
        );
        return $this->db->affected_rows();
    }

    protected function toggleHidden(int $userId, string $state): int {
        $this->db->prepared_query(
            "
            INSERT INTO users_donor_ranks
                   (UserID, Hidden)
            VALUES (?,      ?)
            ON DUPLICATE KEY UPDATE
                Hidden = ?
            ",
            $userId,
            $state,
            $state
        );
        return $this->db->affected_rows();
    }

    public function hide(int $userId): int {
        return $this->toggleHidden($userId, '1');
    }

    public function show(int $userId): int {
        return $this->toggleHidden($userId, '0');
    }

    public function hasForumAccess($UserID) {
        return $this->rank($UserID) >= CONFIG['DONOR_FORUM_RANK'] || $this->specialRank($UserID) >= MAX_SPECIAL_RANK;
    }

    /**
     * Put all the common donor info in the same cache key to save some cache calls
     */
    public function info($UserID) {
        // Our cache class should prevent identical memcached requests
        $DonorInfo = $this->cache->get_value("donor_info_$UserID");
        if ($DonorInfo === false) {
            $QueryID = $this->db->get_query_id();
            $this->db->prepared_query(
                '
                SELECT
                    Rank,
                    SpecialRank,
                    TotalRank,
                    DonationTime,
                    RankExpirationTime + INTERVAL 766 HOUR,
                    InvitesRecievedRank 
                FROM users_donor_ranks
                WHERE UserID = ?
                ',
                $UserID
            );
            // 2 hours less than 32 days to account for schedule run times
            if ($this->db->has_results()) {
                [$Rank, $SpecialRank, $TotalRank, $DonationTime, $ExpireTime, $InvitesRecievedRank]
                    = $this->db->next_record(MYSQLI_NUM, false);
                if ($DonationTime === null) {
                    $DonationTime = 0;
                }
                if ($ExpireTime === null) {
                    $ExpireTime = 0;
                }
            } else {
                $Rank = $SpecialRank = $TotalRank = $DonationTime = $ExpireTime = $InvitesRecievedRank = 0;
            }
            if (\Permissions::is_mod($UserID)) {
                $Rank = MAX_RANK;
                $SpecialRank = MAX_SPECIAL_RANK;
            }
            $this->db->prepared_query(
                '
                SELECT
                    IconMouseOverText,
                    AvatarMouseOverText,
                    CustomIcon,
                    CustomIconLink,
                    SecondAvatar,
                    ColorUsername,
					GradientsColor
                FROM donor_rewards
                WHERE UserID = ?
                ',
                $UserID
            );
            $Rewards = $this->db->next_record(MYSQLI_ASSOC);
            $this->db->set_query_id($QueryID);

            $DonorInfo = [
                'Rank' => (int)$Rank,
                'SRank' => (int)$SpecialRank,
                'TotRank' => (int)$TotalRank,
                'Time' => $DonationTime,
                'ExpireTime' => $ExpireTime,
                'Rewards' => $Rewards,
                'IRank' => $InvitesRecievedRank,
            ];
            $this->cache->cache_value("donor_info_$UserID", $DonorInfo, 86400);
        }
        return $DonorInfo;
    }

    public function rank($UserID) {
        return $this->info($UserID)['Rank'];
    }

    public function specialRank($UserID) {
        return $this->info($UserID)['SRank'];
    }
    private function invitesReceivedRank($UserID) {
        return $this->info($UserID)['IRank'];
    }

    public function totalRank($UserID) {
        return $this->info($UserID)['TotRank'];
    }

    public function lastDonation($UserID) {
        return $this->info($UserID)['Time'];
    }

    public function personalCollages($UserID) {
        $DonorInfo = $this->info($UserID);
        if ($DonorInfo['SRank'] == MAX_SPECIAL_RANK) {
            $Collages = 5;
        } else {
            $Collages = min($DonorInfo['Rank'], 5); // One extra collage per donor rank up to 5
        }
        return $Collages;
    }

    public function titles($UserID) {
        $Results = $this->cache->get_value("donor_title_$UserID");
        if ($Results === false) {
            $QueryID = $this->db->get_query_id();
            $this->db->prepared_query(
                '
                SELECT Prefix, Suffix, UseComma
                FROM donor_forum_usernames
                WHERE UserID = ?
                ',
                $UserID
            );
            $Results = $this->db->next_record();
            $this->db->set_query_id($QueryID);
            $this->cache->cache_value("donor_title_$UserID", $Results, 0);
        }
        return $Results;
    }

    public function enabledRewards($UserID) {
        $Rewards = [];
        $Rank = $this->rank($UserID);
        $SpecialRank = $this->specialRank($UserID);
        $HasAll = $SpecialRank == MAX_SPECIAL_RANK;
        $Rewards = array(
            'HasAvatarMouseOverText' => false,
            'HasCustomDonorIcon' => false,
            'HasDonorForum' => false,
            'HasDonorIconLink' => false,
            'HasDonorIconMouseOverText' => false,
            'HasProfileInfo1' => false,
            'HasProfileInfo2' => false,
            'HasProfileInfo3' => false,
            'HasProfileInfo4' => false,
            'HasSecondAvatar' => false,
            'HasLimitedColorName' => false,
            'HasUnlimitedColor' => false,
            'HasGradientsColor' => false,
        );

        if ($Rank >= 2 || $HasAll) {
            $Rewards["HasDonorIconMouseOverText"] = true;
        }
        if ($Rank >= 3 || $HasAll) {
            $Rewards["HasAvatarMouseOverText"] = true;
        }
        if ($Rank >= 4 || $HasAll) {
            $Rewards["HasDonorIconLink"] = true;
        }
        if ($Rank >= MAX_RANK || $HasAll) {
            $Rewards["HasDonorForum"] = true;
            $Rewards["HasSecondAvatar"] = true;
        }

        if ($SpecialRank >= 1 || $HasAll) {
            $Rewards['HasProfileInfo1'] = true;
        }
        if ($SpecialRank >= 2 || $HasAll) {
            $Rewards['HasProfileInfo2'] = true;
        }
        if ($SpecialRank >= 3 || $HasAll) {
            $Rewards['HasProfileInfo3'] = true;
            $Rewards['HasCustomDonorIcon'] = true;
        }
        if ($SpecialRank >= 4 || $HasAll) {
            $Rewards['HasProfileInfo4'] = true;
            $Rewards["HasLimitedColorName"] = true;
        }
        if ($SpecialRank >= MAX_SPECIAL_RANK || $HasAll) {
            $Rewards['HasUnlimitedColor'] = true;
            $Rewards['HasGradientsColor'] = true;
        }
        return $Rewards;
    }

    public function rewards($UserID) {
        return $this->info($UserID)['Rewards'];
    }

    public function profileRewards($UserID) {
        $Results = $this->cache->get_value("donor_profile_rewards_$UserID");
        if ($Results === false) {
            $QueryID = $this->db->get_query_id();
            $this->db->prepared_query(
                '
                SELECT
                    ProfileInfo1,
                    ProfileInfoTitle1,
                    ProfileInfo2,
                    ProfileInfoTitle2,
                    ProfileInfo3,
                    ProfileInfoTitle3,
                    ProfileInfo4,
                    ProfileInfoTitle4
                FROM donor_rewards
                WHERE UserID = ?
                ',
                $UserID
            );
            $Results = $this->db->next_record();
            $this->db->set_query_id($QueryID);
            $this->cache->cache_value("donor_profile_rewards_$UserID", $Results, 0);
        }
        return $Results;
    }

    public function updateReward($UserID) {
        // TODO: could this be rewritten to avoid accessing $_POST directly?
        $Rank = $this->rank($UserID);
        $SpecialRank = $this->specialRank($UserID);
        $HasAll = $SpecialRank == MAX_SPECIAL_RANK;
        $Counter = 0;
        $Insert = array();
        $Values = array();
        $Update = array();

        $Insert[] = "UserID";
        $Values[] = "'$UserID'";
        if ($Rank >= 1 || $HasAll) {
        }
        if ($Rank >= 2 || $HasAll) {
            if (isset($_POST['donor_icon_mouse_over_text'])) {
                $IconMouseOverText = db_string($_POST['donor_icon_mouse_over_text']);
                $Insert[] = "IconMouseOverText";
                $Values[] = "'$IconMouseOverText'";
                $Update[] = "IconMouseOverText = '$IconMouseOverText'";
            }
            $Counter++;
        }
        if ($Rank >= 3 || $HasAll) {
            if (isset($_POST['avatar_mouse_over_text'])) {
                $AvatarMouseOverText = db_string($_POST['avatar_mouse_over_text']);
                $Insert[] = "AvatarMouseOverText";
                $Values[] = "'$AvatarMouseOverText'";
                $Update[] = "AvatarMouseOverText = '$AvatarMouseOverText'";
            }
            $Counter++;
        }
        if ($Rank >= 4 || $HasAll) {
            if (isset($_POST['donor_icon_link'])) {
                $CustomIconLink = db_string($_POST['donor_icon_link']);
                if (!Misc::is_valid_url($CustomIconLink)) {
                    $CustomIconLink = '';
                }
                $Insert[] = "CustomIconLink";
                $Values[] = "'$CustomIconLink'";
                $Update[] = "CustomIconLink = '$CustomIconLink'";
            }
            $Counter++;
        }

        for ($i = 1; $i <= $Counter; $i++) {
            $this->addProfileInfoReward($i, $Insert, $Values, $Update);
        }
        if ($Rank >= MAX_RANK || $HasAll) {
            if (isset($_POST['donor_icon_custom_url'])) {
                $CustomIcon = db_string($_POST['donor_icon_custom_url']);
                if (!Misc::is_valid_url($CustomIcon)) {
                    $CustomIcon = '';
                }
                $Insert[] = "CustomIcon";
                $Values[] = "'$CustomIcon'";
                $Update[] = "CustomIcon = '$CustomIcon'";
            }
            $this->updateTitle($UserID, $_POST['donor_title_prefix'], $_POST['donor_title_suffix'], $_POST['donor_title_comma']);
            $Counter++;
        }
        if ($SpecialRank >= 4) {
            if (isset($_POST['second_avatar'])) {
                $SecondAvatar = db_string($_POST['second_avatar']);
                if (!Misc::is_valid_url($SecondAvatar)) {
                    $SecondAvatar = '';
                }
                $Insert[] = "SecondAvatar";
                $Values[] = "'$SecondAvatar'";
                $Update[] = "SecondAvatar = '$SecondAvatar'";
            }
            if (isset($_POST['limitedcolor']) && (preg_match('/^#[a-fA-F0-9]{6}$/', $_POST['limitedcolor']) || $_POST['limitedcolor'] == '')) {
                $ColorUsername = db_string($_POST['limitedcolor']);
                $Insert[] = "ColorUsername";
                $Values[] = "'$ColorUsername'";
                $Update[] = "ColorUsername = '$ColorUsername'";
            }
        }
        if ($SpecialRank >= 5) {
            if (isset($_POST['unlimitedcolor']) && (preg_match('/^#[a-fA-F0-9]{6}$/', $_POST['unlimitedcolor']) || $_POST['unlimitedcolor'] == '')) {
                $ColorUsername = db_string($_POST['unlimitedcolor']);
                $Insert[] = "ColorUsername";
                $Values[] = "'$ColorUsername'";
                $Update[] = "ColorUsername = '$ColorUsername'";
            }
            if (isset($_POST['gradientscolor']) && (preg_match('/^#[a-fA-F0-9]{6}(,#[a-fA-F0-9]{6}){1,2}$/', $_POST['gradientscolor']) || $_POST['gradientscolor'] == '')) {
                $GradientsColor = db_string($_POST['gradientscolor']);
                $Insert[] = "GradientsColor";
                $Values[] = "'$GradientsColor'";
                $Update[] = "GradientsColor = '$GradientsColor'";
            }
        }
        $Insert = implode(', ', $Insert);
        $Values = implode(', ', $Values);
        $Update = implode(', ', $Update);
        if ($Counter > 0) {
            $QueryID = $this->db->get_query_id();
            $this->db->query("
				INSERT INTO donor_rewards
					($Insert)
				VALUES
					($Values)
				ON DUPLICATE KEY UPDATE
					$Update");
            $this->db->set_query_id($QueryID);
        }
        $this->cache->delete_value("donor_profile_rewards_$UserID");
        $this->cache->delete_value("donor_info_$UserID");
    }

    private function addProfileInfoReward($Counter, &$Insert, &$Values, &$Update) {
        if (isset($_POST["profile_title_" . $Counter]) && isset($_POST["profile_info_" . $Counter])) {
            $ProfileTitle = db_string($_POST["profile_title_" . $Counter]);
            $ProfileInfo = db_string($_POST["profile_info_" . $Counter]);
            $ProfileInfoTitleSQL = "ProfileInfoTitle" . $Counter;
            $ProfileInfoSQL = "ProfileInfo" . $Counter;
            $Insert[] = "$ProfileInfoTitleSQL";
            $Values[] = "'$ProfileTitle'";
            $Update[] = "$ProfileInfoTitleSQL = '$ProfileTitle'";
            $Insert[] = "$ProfileInfoSQL";
            $Values[] = "'$ProfileInfo'";
            $Update[] = "$ProfileInfoSQL = '$ProfileInfo'";
        }
    }

    // TODO: make $UseComma more sane
    public function updateTitle($UserID, $Prefix, $Suffix, $UseComma) {
        $QueryID = $this->db->get_query_id();
        $Prefix = trim($Prefix);
        $Suffix = trim($Suffix);
        $UseComma = empty($UseComma) ? true : false;
        $this->db->prepared_query(
            '
            INSERT INTO donor_forum_usernames
                   (UserID, Prefix, Suffix, UseComma)
            VALUES (?,      ?,      ?,      ?)
            ON DUPLICATE KEY UPDATE
                Prefix = ?, Suffix = ?, UseComma = ?
            ',
            $UserID,
            $Prefix,
            $Suffix,
            $UseComma ? 1 : 0,
            $Prefix,
            $Suffix,
            $UseComma ? 1 : 0
        );
        $this->cache->delete_value("donor_title_$UserID");
        $this->db->set_query_id($QueryID);
    }

    public function history(int $UserID) {
        if ($UserID < 1) {
            error(404);
        }
        $QueryID = $this->db->get_query_id();
        $this->db->prepared_query(
            '
            SELECT Amount, Time, Currency, Reason, Source, AddedBy, Rank, TotalRank
            FROM donations
            WHERE UserID = ?
            ORDER BY Time DESC
            ',
            $UserID
        );
        $DonationHistory = $this->db->to_array(false, MYSQLI_ASSOC, false);
        $this->db->set_query_id($QueryID);
        return $DonationHistory;
    }

    public function rankExpiry($UserID) {
        $DonorInfo = $this->info($UserID);
        if ($DonorInfo['SRank'] == MAX_SPECIAL_RANK || $DonorInfo['Rank'] == 1) {
            $Return = 'Never';
        } elseif ($DonorInfo['ExpireTime']) {
            $ExpireTime = strtotime($DonorInfo['ExpireTime']);
            if ($ExpireTime - time() < 60) {
                $Return = 'Soon';
            } else {
                $Expiration = time_diff($ExpireTime); // 32 days
                $Return = "in $Expiration";
            }
        } else {
            $Return = '';
        }
        return $Return;
    }

    public function leaderboardRank(int $UserID): int {
        $this->db->prepared_query("SET @RowNum := 0");
        $Position = $this->db->scalar(
            "
            SELECT Position
            FROM (
                SELECT d.UserID, @RowNum := @RowNum + 1 AS Position
                FROM users_donor_ranks AS d
                ORDER BY TotalRank DESC
            ) l
            WHERE UserID = ?
            ",
            $UserID
        );
        return $Position ?? 0;
    }

    public function isDonor(int $userId) {
        return $this->rank($userId) > 0;
    }

    public function isVisible(int $userId): int {
        return is_null($this->db->scalar(
            "
            SELECT Hidden
            FROM users_donor_ranks
            WHERE Hidden = '1'
                AND UserID = ?
            ",
            $userId
        ));
    }

    public function totalMonth(int $month) {
        if (($donations = $this->cache->get_value("donations_month_$month")) === false) {
            $donations = $this->db->scalar(
                "
                SELECT sum(xbt)
                FROM donations
                WHERE time >= CAST(DATE_FORMAT(NOW() ,'%Y-%m-01') as DATE) - INTERVAL ? MONTH
                ",
                $month - 1
            );
            $this->cache->cache_value("donations_month_$month", $donations, 3600 * 36);
        }
        return $donations;
    }

    public function expireRanks(): int {
        $this->db->prepared_query("
            SELECT UserID
            FROM users_donor_ranks
            WHERE Rank > 1
                AND SpecialRank < ?
                AND RankExpirationTime < NOW() - INTERVAL 1440 HOUR
        ", MAX_SPECIAL_RANK); // 2 hours less than 32 days to account for schedule run times
        $userIds = [];
        while ([$id] = $this->db->next_record()) {
            $this->cache->delete_value("donor_info_$id");
            $this->cache->delete_value("donor_title_$id");
            $this->cache->delete_value("donor_profile_rewards_$id");
            $userIds[] = $id;
        }
        if ($userIds) {
            $this->db->prepared_query(
                "
                UPDATE users_donor_ranks SET
                    Rank = Rank - 1,
                    RankExpirationTime = now()
                WHERE Rank > 1
                    AND UserID IN (" . placeholders($userIds) . ")
                ",
                ...$userIds
            );
        }
        return count($userIds);
    }
}
