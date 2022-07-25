<?

global $Locales, $Twig, $WINDOW_CONFIG;
$Files = glob(CONFIG['SERVER_ROOT'] . '/src/locales/*/*.yaml');
$Locales = [];
foreach ($Files as $File) {
    $Lang = basename(dirname($File));
    $YamlText = $Twig->render("$Lang/$Lang.yaml", ['CONFIG' => $WINDOW_CONFIG]);
    $Locale = yaml_parse($YamlText);
    $NewLang = $Lang == 'zh-Hans' ? 'chs' : $Lang;
    $Locales[$NewLang] = $Locale;
}

class Lang {
    static $Lang = [];
    const DEFAULT_LANG = 'chs';
    const EN = 'en';
    const CHS = 'chs';
    const LANGS = [self::EN, self::CHS];

    public static function get($Key, $Options = []) {
        global $Locales;
        $Options = array_merge([
            'DefaultValue' => null,
            'Lang' => null,
            'Values' => [],
        ], $Options);
        $DefaultValue = $Options['DefaultValue'] ?: $Key;
        $Lang = self::getLang($Options['Lang']);
        $Values = $Options['Values'];
        $Locale = $Locales[$Lang];
        $Value = $Locale[$Key];
        if (!isset($Locale[$Key])) {
            $Value = $DefaultValue;
        }
        $Value = sprintf($Value, ...$Values);
        if ($Value === false) {
            $Value = $Key;
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

function t(...$args) {
    return Lang::get(...$args);
}
