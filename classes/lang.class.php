<?

class Lang {
    static $Lang = [];
    const DEFAULT_LANG = 'chs';
    const EN = 'en';
    const CHS = 'chs';
    const LANGS = [self::EN, self::CHS];
    static $Locales = [];

    public static function init() {
        global $WINDOW_CONFIG;
        $Files = glob(CONFIG['SERVER_ROOT'] . '/src/locales/*/*.yaml');
        foreach ($Files as $File) {
            $Lang = basename(dirname($File));
            $YamlText = G::$Twig->render("$Lang/$Lang.yaml", ['CONFIG' => $WINDOW_CONFIG]);
            $Locale = yaml_parse($YamlText);
            $NewLang = $Lang == 'zh-Hans' ? 'chs' : $Lang;
            self::$Locales[$NewLang] = $Locale;
        }
    }

    public static function is_default() {
        return Lang::getUserLang(G::$LoggedUser['ID']) == self::DEFAULT_LANG;
    }

    public static function get($Key, $Options = []) {
        $Options = array_merge([
            'DefaultValue' => null,
            'Lang' => null,
            'Values' => [],
            'Count' => null,
        ], $Options);
        $Lang = self::getLang($Options['Lang']);
        $Value = self::_get($Key, $Lang, $Options);
        if ($Value === false) {
            $Value = self::_get($Key, self::DEFAULT_LANG, $Options);
        }
        if ($Value == false) {
            $Value = $Key;
        }
        return $Value;
    }

    private static function _get($Key, $Lang, $Options = []) {
        $DefaultValue = $Options['DefaultValue'] ?: $Key;
        $Values = $Options['Values'];
        $Count = $Options['Count'];
        $Locale = self::$Locales[$Lang];
        if ($Count !== null) {
            $Suffix = ($Count === 1) ? '_one' : '_other';
            $Key = "${Key}${Suffix}";
        }
        $Value = $Locale[$Key];

        if (!isset($Locale[$Key])) {
            $Value = $DefaultValue;
        }
        if (is_string($Value)) {
            $Value = sprintf($Value, ...$Values);
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

    public static function getCurrentLangStandard() {
        global $LoggedUser;
        $Lang = self::getUserLang($LoggedUser['ID']);
        return $Lang == 'chs' ? 'zh-Hans' : $Lang;
    }

    private static function getLang($Lang = false) {
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

function t(...$args) {
    return Lang::get(...$args);
}
