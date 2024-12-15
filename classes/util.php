<?
// This is a file of miscellaneous functions that are called so damn often
// that it'd just be annoying to stick them in namespaces.

use Gazelle\Util\{Type, Time};

/**
 * Return true if the given string is an integer. The original Gazelle developers
 * must have thought the only numbers out there were integers when naming this function.
 *
 * @param mixed $Str
 * @return bool
 */
if (PHP_INT_SIZE === 4) {
    function is_number($Str) {
        if ($Str === null || $Str === '') {
            return false;
        }
        if (is_int($Str)) {
            return true;
        }
        if ($Str[0] == '-' || $Str[0] == '+') { // Leading plus/minus signs are ok
            $Str[0] = 0;
        }
        return ltrim($Str, "0..9") === '';
    }
} else {
    function is_number($Str) {
        return Type::isInteger($Str);
    }
}

function is_date($Date) {
    return Time::isValidDate($Date);
}

/**
 * Check that some given variables (usually in _GET or _POST) are numbers
 *
 * @param array $Base array that's supposed to contain all keys to check
 * @param array $Keys list of keys to check
 * @param mixed $Error error code or string to pass to the error() function if a key isn't numeric
 */
function assert_numbers(&$Base, $Keys, $Error = 0) {
    // make sure both arguments are arrays
    if (!is_array($Base) || !is_array($Keys)) {
        return;
    }
    foreach ($Keys as $Key) {
        if (!isset($Base[$Key]) || !is_number($Base[$Key])) {
            error($Error);
        }
    }
}

/**
 * Return true, false or null, depending on the input value's "truthiness" or "non-truthiness"
 *
 * @param $Value the input value to check for truthiness
 * @return true if $Value is "truthy", false if it is "non-truthy" or null if $Value was not
 *         a bool-like value
 */
function is_bool_value($Value) {
    return Type::isBoolValue($Value);
}

/**
 * HTML-escape a string for output.
 *
 * @param string $Str
 * @return string escaped string.
 */
function display_str($Str) {
    if ($Str === null || $Str === false || is_array($Str)) {
        return '';
    }
    return htmlspecialchars($Str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8',  false);
}


/**
 * Send a message to an IRC bot listening on CONFIG['SOCKET_LISTEN_PORT']
 *
 * @param string $Raw An IRC protocol snippet to send.
 */
function send_irc($Raw) {
    if (CONFIG['DISABLE_IRC'] === true) {
        return;
    }
    $IRCSocket = fsockopen(CONFIG['SOCKET_LISTEN_ADDRESS'], CONFIG['SOCKET_LISTEN_PORT']);
    $Raw = str_replace(array("\n", "\r"), '', $Raw);
    fwrite($IRCSocket, $Raw);
    fclose($IRCSocket);
}


/**
 * Display a critical error and kills the page.
 *
 * @param string $Error Error type. Automatically supported:
 *  403, 404, 0 (invalid input), -1 (invalid request)
 *  If you use your own string for Error, it becomes the error description.
 * @param boolean $NoHTML If true, the header/footer won't be shown, just the description.
 * @param string $Log If true, the user is given a link to search $Log in the site log.
 */
function error($Error, $NoHTML = false, $Log = false, $Title = false) {
    require(CONFIG['SERVER_ROOT'] . '/sections/error/index.php');
    G::$Debug->profile();
    die();
}


/**
 * Convenience function for check_perms within Permissions class.
 *
 * @see Permissions::check_perms()
 *
 * @param string $PermissionName
 * @param int $MinClass
 * @return bool
 */
function check_perms($PermissionName, $MinClass = 0) {
    return Permissions::check_perms($PermissionName, $MinClass);
}


function static_prefix() {
    if (CONFIG['IS_DEV']) {
        return "/src";
    }
    return "/public";
}

function ajax_json_success($Message, $Flags = JSON_UNESCAPED_UNICODE) {
    print json_encode(array('status' => "success", 'response' => $Message), $Flags);
    die();
}

function ajax_json_error($Message = "") {
    print json_encode(array('status' => "failure", 'error' => $Message));
    die();
}

function ajax_json_error_with_code($Code, $Message = "") {
    print json_encode(array('status' => "failure", 'code' => $Code, 'error' => $Message));
    die();
}

function json_error($Message) {
    echo json_encode(add_json_info(['status' => 'failure', 'erro' => $Message, 'response' => []]));
    die();
}

function add_json_info($Json) {
    if (!isset($Json['info'])) {
        $Json = array_merge($Json, [
            'info' => [
                'source' => CONFIG['SITE_NAME'],
                'version' => 1,
            ],
        ]);
    }
    if (!isset($Json['debug']) && G::$LoggedUser && check_perms('site_debug')) {
        /** @var DEBUG $Debug */
        global $Debug;
        $Json = array_merge($Json, [
            'debug' => [
                'queries' => $Debug->get_queries(),
                'searches' => $Debug->get_sphinxql_queries()
            ],
        ]);
    }
    return $Json;
}


/**
 * Print the site's URL including the appropriate URI scheme, including the trailing slash
 *
 * @return string for site
 */
function site_url($Slash = true) {
    if ($Slash) {
        return CONFIG['SITE_URL'] . '/';
    }
    return CONFIG['SITE_URL'];
}

/**
 * The text of the pop-up confirmation when burning an FL token.
 *
 * @param integer $seeders - number of seeders for the torrent
 * @return string Warns if there are no seeders on the torrent
 */
function FL_confirmation_msg($seeders, $Size) {
    /* Coder Beware: this text is emitted as part of a Javascript single quoted string.
     * Any apostrophes should be avoided or escaped appropriately (with \\').
     */
    $TokenUses = 1;
    $FTStr = t('server.common.n_fl_token', ['Count' => $TokenUses, 'Values' => [$TokenUses]]);
    return ($seeders == 0)
        ? t('server.common.not_seeded_sure_use_fl', ['Values' => [$FTStr]])
        : t('server.common.sure_use_fl', ['Values' => [$FTStr]]);
}

/**
 * Utility function that unserializes an array, and then if the unserialization fails,
 * it'll then return an empty array instead of a null or false which will break downstream
 * things that require an incoming array
 *
 * @param string $array
 * @return array
 */
function unserialize_array($array) {
    $array = empty($array) ? array() : unserialize($array);
    return (empty($array)) ? array() : $array;
}

/**
 * Utility function for determining if checkbox should be checked if some $value is set or not
 * @param $value
 * @return string
 */
function isset_array_checked($array, $value) {
    return (isset($array[$value])) ? "checked" : "";
}

/**
 * Helper function to return an string of N elements from an array.
 *
 * (e.g. [2, 4, 6] into a list of query placeholders (e.g. '?,?,?')
 * By default '?' is used, but a custom placeholder may be specified,
 * such as '(?)' or '(?, now(), 100)', for use in a bulk insert.
 *
 * @param array $list The list of elements
 * @param string $placeholder ('?' by default).
 * @return string The resulting placeholder string.
 */
function placeholders(array $list, $placeholder = '?') {
    return implode(',', array_fill(0, count($list), $placeholder));
}

/**
 * Awful anglo-centric hack for handling plurals ;-)
 *
 * @param int $n the number
 * @return string '' if 1, otherwise 's'
 */
function plural(int $n) {
    return $n == 1 ? '' : 's';
}

function open_registration($Email = null) {
    if (CONFIG['OPEN_REGISTRATION']) {
        return true;
    }
    if (!empty(CONFIG['OPEN_REGISTRATION_EMAIL'])) {
        if (!empty($Email)) {
            $EmailBox = explode('@', $Email);
            if (in_array($EmailBox[1], CONFIG['OPEN_REGISTRATION_EMAIL'])) {
                return true;
            }
        } else {
            return true;
        }
    }
    if (!isset(CONFIG['OPEN_REGISTRATION_TO']) || !isset(CONFIG['OPEN_REGISTRATION_FROM'])) {
        return false;
    }
    $t = time();
    if ($t >= strtotime(CONFIG['OPEN_REGISTRATION_FROM']) && $t < strtotime(CONFIG['OPEN_REGISTRATION_TO'])) {
        return true;
    }
    return false;
}

function is_limit_email_registration() {
    if (CONFIG['OPEN_REGISTRATION']) {
        return false;
    }
    if (!isset(CONFIG['OPEN_REGISTRATION_TO']) || !isset(CONFIG['OPEN_REGISTRATION_FROM'])) {
        return true;
    }
    $t = time();
    if ($t >= strtotime(CONFIG['OPEN_REGISTRATION_FROM']) && $t < strtotime(CONFIG['OPEN_REGISTRATION_TO'])) {
        return false;
    }
    return true;
}

function icon($name, $class = '', $Option = []) {
    $Option = array_merge(['ReturnEmptyString' => false], $Option);
    $icon = file_get_contents(CONFIG['SERVER_ROOT'] . "/src/icons/$name.svg");
    if ($icon) {
        // random id for flag icon
        if (str_starts_with($name, "flag/")) {
            $icon = str_replace('%s', rand(), $icon);
        }
        $icon = str_replace('class="', "class=\"$class ", $icon);
        return $icon;
    } else {
        return $Option['ReturnEmptyString'] ? '' : $name;
    }
}

/**
 * Generate a random string drawn from alphanumeric characters
 * but omitting lowercase l, uppercase I and O (to avoid confusion).
 *
 * @param  int    $len
 * @return string random alphanumeric string
 */
function randomString($len = 32) {
    $alphabet = str_split('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789');
    $max = count($alphabet);
    $mask = (int)pow(2, ceil(log($len, 2))) - 1;
    $out = '';
    while (strlen($out) < $len) {
        $n = ord(openssl_random_pseudo_bytes(1)) & $mask;
        if ($n < $max) {
            $out .= $alphabet[$n];
        }
    }
    return $out;
}

function make_utf8($Str) {
    if ($Str != '') {
        if (is_utf8($Str)) {
            $Encoding = 'UTF-8';
        }
        if (empty($Encoding)) {
            $Encoding = mb_detect_encoding($Str, 'UTF-8, ISO-8859-1');
        }
        if (empty($Encoding)) {
            $Encoding = 'ISO-8859-1';
        }
        if ($Encoding == 'UTF-8') {
            return $Str;
        } else {
            return @mb_convert_encoding($Str, 'UTF-8', $Encoding);
        }
    }
}

function display_array($Array, $Escape = array()) {
    foreach ($Array as $Key => $Val) {
        if ((!is_array($Escape) && $Escape == true) || !in_array($Key, $Escape)) {
            $Array[$Key] = display_str($Val);
        }
    }
    return $Array;
}


function page_title_conn($Titles) {
    return implode(" > ", $Titles);
}

function get_by_path($Object, $Path, $DefaultValue = null) {
    $Keys = explode('.', $Path);
    $Value = $Object;
    foreach ($Keys as $Key) {
        if (!isset($Value[$Key])) {
            return $DefaultValue;
        }
        $Value = $Value[$Key];
    }
    return $Value;
}

function add_day($date, $day) {
    $timestamp = strtotime($date);
    $timestamp = strtotime("+$day days", $timestamp);
    $newDate = date('Y-m-d', $timestamp);
    return $newDate;
}
