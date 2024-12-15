<?

use Gazelle\Manager\Reward;
use Gazelle\Action\RewardInfo;

include(CONFIG['SERVER_ROOT'] . '/sections/badges/functions.php');
class Badges {
    static $Limit = array(
        CONFIG['USER_CLASS']['USER'] => 0,
        CONFIG['USER_CLASS']['MEMBER'] => 0,
        CONFIG['USER_CLASS']['POWER'] => 1,
        CONFIG['USER_CLASS']['ELITE'] => 2,
        CONFIG['USER_CLASS']['TORRENT_MASTER'] => 3,
        CONFIG['USER_CLASS']['POWER_TM'] => 4,
        CONFIG['USER_CLASS']['ELITE_TM'] => 5,
        CONFIG['USER_CLASS']['GURU'] => 5,
    );
    static $Lang = false;
    public static function get_text($Label, $Type) {
        return t("server.badges.{$Label}_$Type", [
            'DefaultValue' =>
            "{$Label}_$Type"
        ]);
    }
    public static function get_badge_labels() {
        if (($Badges = G::$Cache->get_value("badge_labels")) === false) {
            G::$DB->query("select * from badges_label order by rank");
            $Badges = G::$DB->to_array('Label');
            G::$Cache->cache_value("badge_labels", $Badges);
        }
        return $Badges;
    }
    public static function get_badges_by_id($BadgeID = false) {
        if (($Badges = G::$Cache->get_value("badges_by_id")) === false) {
            G::$DB->query("select * from badges_item order by label, level");
            $Badges = G::$DB->to_array('ID');
            G::$Cache->cache_value("badges_by_id", $Badges);
        }
        return $BadgeID === false ? $Badges : $Badges[$BadgeID];
    }
    public static function get_badges_by_label($BadgeLabel = false) {
        if (($Badges = G::$Cache->get_value("badges_by_label")) === false) {
            $ret = array();
            G::$DB->query("select * from badges_item order by label, level");
            $Badges = G::$DB->to_array();
            foreach ($Badges as $Badge) {
                if (isset($ret[$Badge['Label']])) {
                    $ret[$Badge['Label']][] = $Badge;
                } else {
                    $ret[$Badge['Label']] = array($Badge);
                }
            }
            $Badges = $ret;
            G::$Cache->cache_value("badges_by_label", $Badges);
        }
        return $BadgeLabel === false ? $Badges : $Badges[$BadgeLabel];
    }
    public static function get_badge_labels_by_type() {
        if (($Badges = G::$Cache->get_value("badge_labels_by_type")) === false) {
            $ret = array();
            G::$DB->query("select * from badges_label order by rank");
            $Badges = G::$DB->to_array();
            foreach ($Badges as $Badge) {
                if (isset($ret[$Badge['Type']])) {
                    $ret[$Badge['Type']][] = $Badge;
                } else {
                    $ret[$Badge['Type']] = array($Badge);
                }
            }
            $Badges = $ret;
            G::$Cache->cache_value("badge_labels_by_type", $Badges);
        }
        return $Badges;
    }
    public static function get_badges_by_userid($UserID) {
        if (($Badges = G::$Cache->get_value("badges_by_" . $UserID)) === false) {
            G::$DB->query("select BadgeID, Profile, Username, Time from badges where userid=" . intval($UserID) . " order by time desc");
            $Badges = G::$DB->to_array('BadgeID');
            G::$Cache->cache_value("badges_by_" . $UserID, $Badges);
        }
        return $Badges;
    }
    public static function get_wear_badges($UserID) {
        $BadgesByUserID = Badges::get_badges_by_userid($UserID);
        $Wear = array('Username' => array(), 'Profile' => array());
        $Biggest = array();
        foreach ($BadgesByUserID as $BadgeID => $BadgeWear) {
            $Badge = Badges::get_badges_by_id($BadgeID);
            $Label = $Badge['Label'];
            $Level = $Badge['Level'];
            if ($BadgeWear['Profile']) {
                $Wear['Profile'][] = array('Label' => $Label, 'Rank' => $BadgeWear['Profile']);
            }
            if ($BadgeWear['Username']) {
                $Wear['Username'][] = array('Label' => $Label, 'Rank' => $BadgeWear['Username']);
            }
            if (isset($Biggest[$Label])) {
                if ($Biggest[$Label]['Level'] < $Level) {
                    $Biggest[$Label]['Level'] = $Level;
                    $Biggest[$Label]['ID'] = $BadgeID;
                }
            } else {
                $Biggest[$Label] = array('Level' => $Level, 'ID' => $BadgeID);
            }
        }
        $ret = array('Username' => array(), 'Profile' => array());
        foreach ($Wear['Username'] as $Badge) {
            $ret['Username'][$Badge['Rank']] = $Biggest[$Badge['Label']]['ID'];
        }
        foreach ($Wear['Profile'] as $Badge) {
            $ret['Profile'][$Badge['Rank']] = $Biggest[$Badge['Label']]['ID'];
        }
        ksort($ret['Profile']);
        ksort($ret['Username']);
        return $ret;
    }
    public static function clear_cache() {
        G::$Cache->delete_value("badges_by_id");
        G::$Cache->delete_value("badges_by_label");
        G::$Cache->delete_value("badge_labels_by_type");
        G::$Cache->delete_value("badge_labels");
    }
    public static function addLabel($Label, $DisImage, $Type, $Auto, $Father, $Progress, $Rank, $Remark) {
        G::$DB->query("
        insert into badges_label 
        (Label, DisImage, Type, Auto, Father, Progress, Rank, Remark) 
        values 
        ('" . db_string($Label) . "','" . db_string($DisImage) . "','" . db_string($Type) . "'," . intval($Auto) . "," . intval($Father) . "," . intval($Progress) . "," . intval($Rank) . ",'" . db_string($Remark) . "')");
        Badges::clear_cache();
    }
    public static function editLabel($Label, $DisImage, $Type, $Auto, $Father, $Progress, $Rank, $Remark) {
        G::$DB->query("
        update badges_label set
        DisImage='" . db_string($DisImage) . "', 
        Type='" . db_string($Type) . "', 
        Auto=" . intval($Auto) . ", 
        Father=" . intval($Father) . ",
        Progress=" . intval($Progress) . ",
        Rank=" . intval($Rank) . ",
        Remark='" . db_string($Remark) . "' 
        where Label='" . db_string($Label) . "'");
        Badges::clear_cache();
    }
    public static function deleteLabel($Label) {
        G::$DB->query("select count(1) from badges_item where Label='" . db_string($Label) . "'");
        list($Count) = G::$DB->next_record();
        if ($Count) {
            return false;
        } else {
            G::$DB->query("delete from badges_label where Label='" . db_string($Label) . "'");
            Badges::clear_cache();
            return true;
        }
    }
    public static function addItem($Label, $BigImage, $SmallImage, $Level, $Count) {
        G::$DB->query("
        insert into badges_item 
        (Label, BigImage, SmallImage, Level, Count) 
        values 
        ('" . db_string($Label) . "','" . db_string($BigImage) . "','" . db_string($SmallImage) . "'," . intval($Level) . "," . intval($Count) . ")");
        Badges::clear_cache();
    }
    public static function editItem($ID, $Label, $BigImage, $SmallImage, $Level, $Count) {
        G::$DB->query("
        update badges_item set 
        Label='" . db_string($Label) . "', 
        BigImage='" . db_string($BigImage) . "', 
        SmallImage='" . db_string($SmallImage) . "', 
        Level=" . intval($Level) . ", 
        Count=" . intval($Count) . "
        where ID=" . intval($ID));
        Badges::clear_cache();
    }
    public static function deleteItem($ID) {
        G::$DB->query("select count(1) from badges where BadgeID=" . intval($ID));
        list($Count) = G::$DB->next_record();
        if ($Count) {
            return false;
        } else {
            G::$DB->query("delete from badges_item where ID=" . intval($ID));
            Badges::clear_cache();
            return true;
        }
    }
    public static function gave($UserID, $BadgeID, $SendPM = true) {
        $Badge = Badges::get_badges_by_id($BadgeID);
        $rewardManager = new Reward;
        $reward = new RewardInfo;
        $reward->badgeID = $BadgeID;
        $rewardManager->sendReward($reward, [$UserID], "System", false, true);
        if ($SendPM) {
            Misc::send_pm_with_tpl($UserID, 'give_badges', ['BadgeName' => Badges::get_text($Badge['Label'], 'badge_name'), 'BadgeLevel' => $Badge['Level']]);
        }
    }
    public static function buy($UserID, $BadgeID, $Price) {
        if (G::$LoggedUser['ID'] != $UserID) return 0;
        if (G::$LoggedUser['BonusPoints'] < $Price) return 0;
        if (Badges::gave($UserID, $BadgeID)) {
            G::$DB->query("update users_main set BonusPoints = BonusPoints - $Price where id=$UserID");
            G::$Cache->delete_value('user_stats_' . $UserID);
            return 1;
        } else {
            return 0;
        }
    }
    public static function take($UserID, $BadgeID) {
        G::$DB->query("delete from badges where UserID=" . intval($UserID) . " and BadgeID=" . intval($BadgeID));
        G::$Cache->delete_value("badges_by_" . $UserID);
    }
    public static function wearProfile($UserID, $Label) {
        $WearOrDisplay = Badges::get_wear_badges($UserID);
        $Limit = 8;
        if (count($WearOrDisplay['Profile']) >= $Limit) {
            return 0;
        }
        $Badges = Badges::get_badges_by_label($Label);
        $BadgeIDs = array();
        foreach ($Badges as $Badge) {
            $BadgeIDs[] = $Badge['ID'];
        }
        G::$DB->query("SELECT max(`Profile`)+1 FROM `badges` WHERE `UserID`=" . intval($UserID));
        list($Rank) = G::$DB->next_record();
        G::$DB->query("update badges set Profile=$Rank where UserID=" . intval($UserID) . " and BadgeID in (" . implode(",", $BadgeIDs) . ")");
        G::$Cache->delete_value("badges_by_$UserID");
        return G::$DB->affected_rows();
    }
    public static function unWearProfile($UserID, $Label) {
        $Badges = Badges::get_badges_by_label($Label);
        $BadgeIDs = array();
        foreach ($Badges as $Badge) {
            $BadgeIDs[] = $Badge['ID'];
        }
        G::$Cache->delete_value("badges_by_$UserID");
        G::$DB->query("update badges set Profile=0 where UserID=" . intval($UserID) . " and BadgeID in (" . implode(",", $BadgeIDs) . ")");
    }
    public static function wearUsername($UserID, $Label) {
        $WearOrDisplay = Badges::get_wear_badges($UserID);
        $Limit = isset(Badges::$Limit[G::$LoggedUser['PermissionID']]) ? Badges::$Limit[G::$LoggedUser['PermissionID']] : 6;
        if (count($WearOrDisplay['Username']) >= $Limit) {
            return 0;
        }
        $Badges = Badges::get_badges_by_label($Label);
        $BadgeIDs = array();
        foreach ($Badges as $Badge) {
            $BadgeIDs[] = $Badge['ID'];
        }
        G::$DB->query("SELECT max(`Username`)+1 FROM `badges` WHERE `UserID`=" . intval($UserID));
        list($Rank) = G::$DB->next_record();
        G::$DB->query("update badges set Username=$Rank where UserID=" . intval($UserID) . " and BadgeID in (" . implode(",", $BadgeIDs) . ")");
        G::$Cache->delete_value("badges_by_$UserID");
        return G::$DB->affected_rows();
    }
    public static function unWearUsername($UserID, $Label) {
        $Badges = Badges::get_badges_by_label($Label);
        $BadgeIDs = array();
        foreach ($Badges as $Badge) {
            $BadgeIDs[] = $Badge['ID'];
        }
        G::$Cache->delete_value("badges_by_$UserID");
        G::$DB->query("update badges set Username=0 where UserID=" . intval($UserID) . " and BadgeID in (" . implode(",", $BadgeIDs) . ")");
    }
    public static function checkBadgeFunc($Label) {
        return function_exists("badge_$Label");
    }
    public static function checkBadgeCountFunc($Label) {
        return function_exists("badge_count_$Label");
    }
    public static function badgeFunc($Label, $Count) {
        return call_user_func("badge_$Label", $Count);
    }
    public static function badgeCountFunc($Label, $UserID) {
        return call_user_func("badge_count_$Label", $UserID);
    }
    public static function get_badges_count($UserID) {
        $Counts = [];
        $Badges = Badges::get_badge_labels();
        foreach ($Badges as $Label => $Badge) {
            if (Badges::checkBadgeCountFunc($Label)) {
                $Count = Badges::badgeCountFunc($Label, $UserID);
                $Counts[$Label] = $Count ? $Count : 0;
            } else {
                $Counts[$Label] = 0;
            }
        }
        return $Counts;
    }
}
