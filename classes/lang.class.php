<?

class Lang {
    const EN = 'en';
    const CHS = 'chs';
    const PT = 'pt';
    const LANGS = [self::EN, self::CHS, self::PT];
    const DEFAULT_LANG = self::CHS;
    const MAIN_LANG = self::EN;
    const SUB_LANG = self::CHS;

    static $Locales = [];

    public static function get($Key, $Options = []) {
        $Options = array_merge([
            'DefaultValue' => null,
            'Lang' => null,
            'Values' => [],
            'Count' => null,
        ], $Options);
        $Lang = self::getLang($Options['Lang']);
        $Value = self::getWithLang($Key, $Lang, $Options);

        if (empty($Value)) {
            $Value = self::getWithLang($Key, self::MAIN_LANG, $Options);
        }
        if (empty($Value)) {
            $Value = $Key;
        }
        return $Value;
    }

    public static function getWithLang($Key, $Lang, $Options = []) {

        $DefaultValue = $Options['DefaultValue'];
        $Values = $Options['Values'];
        $Count = $Options['Count'];
        $Locale = self::get_locale($Lang);
        if ($Count !== null) {
            $Suffix = ($Count === 1) ? '_one' : '_other';
            $Key = "${Key}${Suffix}";
        }

        $Value = $Locale[$Key];
        if (!isset($Locale[$Key]) && !empty($DefaultValue)) {
            $Value = $DefaultValue;
        }

        if (!empty($Value) && is_string($Value)) {
            $Value = sprintf($Value, ...$Values);
        }

        return $Value;
    }

    public static function choose_content($Content, $SubContent) {
        $UserID = G::$LoggedUser['ID'];
        $Lang = self::getUserLang($UserID);
        if ($Lang == self::MAIN_LANG) {
            if (!empty($Content)) {
                return $Content;
            }
            return $SubContent;
        } else if ($Lang == self::SUB_LANG) {
            if (!empty($SubContent)) {
                return $SubContent;
            }
        } else {
            if (!empty($Content)) {
                return $Content;
            }
            return $SubContent;
        }
        return $Content;
    }

    private static function get_locale($Lang) {
        $NewLang = $Lang == 'chs' ? 'zh-Hans' : $Lang;
        $Data = self::$Locales[$Lang];
        if (!empty($Data)) {
            return $Data;
        }
        global $WINDOW_CONFIG;
        $YamlText = G::$Twig->render("$NewLang/$NewLang.yaml", ['CONFIG' => $WINDOW_CONFIG]);
        $Locale = yaml_parse($YamlText);
        self::$Locales[$Lang] = $Locale;
        return $Locale;
    }

    // TODO by qwerty temp solution
    public static function get_key($Prefix, $Value = false) {
        foreach (self::LANGS as $Lang) {
            $Locale = self::get_locale($Lang);
            $Result =
                array_filter($Locale, function ($element) use ($Value) {
                    return isset($element) && strtolower($element) == strtolower($Value);
                });
            foreach ($Result as $K => $V) {
                if (str_starts_with($K, $Prefix)) {
                    return  $K;
                }
            }
        }
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
        if (!in_array($Lang, self::LANGS)) {
            $Lang = self::DEFAULT_LANG;
        }
        return $Lang;
    }
}

function t(...$args) {
    return Lang::get(...$args);
}
