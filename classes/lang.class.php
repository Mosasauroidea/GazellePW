<?

global $Locales;
$Files = glob(CONFIG['SERVER_ROOT'] . '/src/locales/*/server.yaml');
$Locales = [];
foreach ($Files as $File) {
    $Locale = yaml_parse_file($File);
    $Lang = basename(dirname($File));
    $Lang = $Lang == 'zh-CN' ? 'chs' : $Lang;
    $Locales[$Lang] = $Locale;
}

class Lang {
    static $Lang = [];
    const DEFAULT_LANG = 'chs';
    const EN = 'en';
    const CHS = 'chs';
    const LANGS = [self::EN, self::CHS];

    public static function get($Page, $Label = false, $Lang = false, $Option = [], ...$Interpolations) {
        $Option = array_merge(['DefaultValue' => null], $Option);
        global $Locales;
        $Lang = self::getLang($Lang);
        $Locale = $Locales[$Lang][$Page];
        if (!$Label) {
            return $Locale;
        }
        if (!isset($Locale[$Label])) {
            return $Option['DefaultValue'] ?: "$Page.$Label";
        }
        $Value = $Locale[$Label];
        if (is_string($Value)) {
            $Value = str_replace(["${CONFIG['SITE_NAME']}", "${CONFIG['TG_GROUP']}", "${CONFIG['TG_DISBALE_CHANNEL']}", "${CONFIG['MAIL_HOST']}"], [CONFIG['SITE_NAME'], CONFIG['TG_GROUP'], CONFIG['TG_DISBALE_CHANNEL'], CONFIG['MAIL_HOST']], $Value);
        }
        if (!empty($Interpolations)) {
            $Value = sprintf($Value, ...$Interpolations);
            if ($Value === false) {
                $Value = "$Page.$Label is bad";
            }
        }
        return $Value;
    }

    public static function get_key($Page, $Label = false, $Lang = false) {
        $Locale = self::get($Page, false, $Lang);
        return array_search($Label, $Locale);
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
        $Lang = self::getLang($Lang);
        return CONFIG['SERVER_ROOT'] . "/lang/$Lang/lang_$Page.php";
    }

    public static function getLang($Lang = false) {
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
        return $Lang;
    }
}
