<?
class Lang {
    static $Lang = [];
    const DEFAULT_LANG = 'chs';
    const EN = 'en';
    const CHS = 'chs';
    const LANGS = [self::EN, self::CHS];
    public static function get($Page, $Label = false, $Lang = false, $Option = [], ...$Interpolations) {
        $Option = array_merge(['DefaultValue' => null], $Option);
        if (!isset(self::$Lang[$Lang . $Page])) {
            include(self::getLangfilePath($Page, $Lang));
            $LangArray = "lang_$Page";
            self::$Lang[$Lang . $Page] = $$LangArray;
        }
        if ($Label === false) {
            return self::$Lang[$Lang . $Page];
        }
        if (!isset(self::$Lang[$Lang . $Page][$Label])) {
            return $Option['DefaultValue'] ?: '$lang_' . $Page . "['$Label']";
        }
        if (!empty($Interpolations)) {
            $Tmp = sprintf(self::$Lang[$Lang . $Page][$Label], ...$Interpolations);
            if ($Tmp === false) {
                return '$lang_' . $Page . "['$Label'] is bad!";
            }
            return $Tmp;
        }
        return self::$Lang[$Lang . $Page][$Label];
    }

    public static function get_key($Page, $Label = false, $Lang = false, ...$Interpolations) {
        if (!isset(self::$Lang[$Lang . $Page])) {
            include(self::getLangfilePath($Page, $Lang));
            $LangArray = "lang_$Page";
            self::$Lang[$Lang . $Page] = $$LangArray;
        }
        if ($Label === false) {
            return self::$Lang[$Lang . $Page];
        }
        if (!empty($Interpolations)) {
            $Tmp = sprintf(self::$Lang[$Lang . $Page][$Label], ...$Interpolations);
            if ($Tmp === false) {
                return '$lang_' . $Page . "['$Label'] is bad!";
            }
            return $Tmp;
        }
        return array_search($Label, self::$Lang[$Lang . $Page]);
    }

    public static function getUserLang($UserID) {
        if (empty($UserID)) {
            return 'chs';
        }
        $Lang = G::$Cache->get_value("lang_$UserID");
        if ($Lang === false) {
            G::$DB->query("SELECT Lang
                FROM users_info
                WHERE UserID = $UserID");
            if (G::$DB->has_results()) {
                list($Lang) = G::$DB->next_record();
            } else {
                $Lang = '';
            }
            G::$Cache->cache_value("lang_$UserID", $Lang);
        }
        return $Lang;
    }
    public static function getLangfilePath($Page, $Lang = false) {
        if (!$Lang) {
            if (class_exists('G')) {
                $UserID = false;
                $UserID = G::$LoggedUser['ID'];
                if ($UserID) {
                    $Lang = self::getUserLang($UserID);
                }
            }
            if (!$Lang) {
                if (!empty($_COOKIE['lang'])) {
                    $Lang = $_COOKIE['lang'];
                }
            }
            if (!$Lang) {
                $Lang = self::DEFAULT_LANG;
            }
            if ($_COOKIE['lang'] != $Lang) {
                setcookie('lang', $Lang, time() + 60 * 60 * 24 * 365, '/');
            }
        }
        if (!in_array($Lang, array('chs', 'en'))) {
            $Lang = self::DEFAULT_LANG;
        }
        return SERVER_ROOT . "/lang/$Lang/lang_$Page.php";
    }
}
