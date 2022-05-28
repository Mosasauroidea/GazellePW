<?
class IPLock {
    public static function getIPLock($UserID = false) {
        if ($UserID) {
            $List = self::getIPLock();
            return isset($List[$UserID]) ? $List[$UserID] : false;
        } else {
            $List = G::$Cache->get_value("ip_lock_list");
            if ($List === false) {
                $List = [];
                G::$DB->query("select * from ip_lock order by userid");
                $Records = G::$DB->to_array();
                foreach ($Records as $Record) {
                    $List[$Record['UserID']] = $Record['IPs'];
                }
                G::$Cache->cache_value("ip_lock_list", $List, 1800);
            }
            return $List;
        }
    }
    public static function add($UserID, $IPs) {
        if (self::getIPLock($UserID)) {
            return false;
        } else {
            G::$DB->query("
            insert into ip_lock 
            (userid, ips) 
            values 
            (" . intval($UserID) . ",'" . db_string($IPs) . "')");
            self::clear_cache();
            return true;
        }
    }
    public static function edit($UserID, $IPs) {
        G::$DB->query("
        update ip_lock set 
        ips='" . db_string($IPs) . "'
        where userid=" . intval($UserID));
        self::clear_cache();
    }
    public static function delete($UserID) {
        G::$DB->query("delete from ip_lock where UserID=" . intval($UserID));
        self::clear_cache();
    }
    public static function clear_cache() {
        G::$Cache->delete_value("ip_lock_list");
    }
    public static function canLogin($UserID, $IP) {
        $IPLockList = self::getIPLock($UserID);
        return $IPLockList === false || stripos($IPLockList, $IP) !== false;
    }
}
