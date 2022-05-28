<?
class Activity {
    public static function getText() {
        $Activities = self::getActivities();
        if (isset($Activities[0]) && $Activities[0]['Display']) return $Activities[0]['Text'];
        else return "";
    }
    public static function getActivities() {
        if (($Activities = G::$Cache->get_value("activities")) === false) {
            G::$DB->query("select * from activity order by display desc, time desc");
            $Activities = G::$DB->to_array();
            G::$Cache->cache_value("activities", $Activities);
        }
        return $Activities;
    }
    public static function edit($ID, $Text) {
        G::$DB->query("update activity set Text='" . db_string($Text) . "' where id=" . intval($ID));
        self::clear();
    }
    public static function add($Text) {
        G::$DB->query("insert into activity (Text) values ('" . db_string($Text) . "')");
        self::clear();
    }
    public static function delete($ID) {
        G::$DB->query("delete from activity where id=" . intval($ID));
        self::clear();
    }
    public static function show($ID) {
        G::$DB->query("update activity set display=0 where display=1");
        G::$DB->query("update activity set display=1 where id=" . intval($ID));
        self::clear();
    }
    public static function hide($ID) {
        G::$DB->query("update activity set display=0 where id=" . intval($ID));
        self::clear();
    }
    public static function clear() {
        G::$Cache->delete_value("activities");
    }
}
