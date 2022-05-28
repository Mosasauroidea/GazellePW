<?php
$ComicsExtensions = array_fill_keys(array('cbr', 'cbz', 'gif', 'jpeg', 'jpg', 'pdf', 'png'), true);

$BadExtensions = array_fill_keys(array(
    'torrent'
), true);
$Keywords = array(
    'ahashare.com', 'demonoid.com', 'demonoid.me', 'djtunes.com', 'h33t', 'housexclusive.net',
    'limetorrents.com', 'mixesdb.com', 'mixfiend.blogstop', 'mixtapetorrent.blogspot',
    'plixid.com', 'reggaeme.com', 'scc.nfo', 'thepiratebay.org', 'torrentday'
);

function check_file($Type, $Name) {
    check_name($Name);
    check_extensions($Type, $Name);
}

function check_name($Name) {
    global $Keywords;
    $NameLC = strtolower($Name);
    foreach ($Keywords as &$Value) {
        if (strpos($NameLC, $Value) !== false) {
            forbidden_error($Name);
        }
    }
    if (preg_match('/INCOMPLETE~\*/i', $Name)) {
        forbidden_error($Name);
    }

    /*
     * These characters are invalid in NTFS on Windows systems:
     *      : ? / < > \ * | "
     *
     * TODO: Add "/" to the blacklist. Adding "/" to the blacklist causes problems with nested dirs, apparently.
     *
     * Only the following characters need to be escaped (see the link below):
     *      \ - ^ ]
     *
     * http://www.php.net/manual/en/regexp.reference.character-classes.php
     */
    $AllBlockedChars = ' : ? < > \ * | " ';
    if (preg_match('/[\\:?<>*|"]/', $Name, $Matches)) {
        character_error($Matches[0], $AllBlockedChars);
    }
}

function check_extensions($Type, $Name) {
    global $ComicsExtensions, $BadExtensions;

    $extension = get_file_extension($Name);
    if ($Type == 'Comics') {
        if (!isset($ComicsExtensions[$extension])) {
            invalid_error($Name);
        }
    } else {
        if (isset($BadExtensions[$extension])) {
            forbidden_error($Name);
        }
    }
}

function get_file_extension($FileName) {
    return strtolower(substr(strrchr($FileName, '.'), 1));
}

function invalid_error($Name) {
    global $Err;
    $Err = 'Torrent包含一个或多个无效文件(' . display_str($Name) . ')';
}

function forbidden_error($Name) {
    global $Err;
    $Err = 'Torrent包含一个或多个禁止使用的文件(' . display_str($Name) . ')';
}

function character_error($Character, $AllBlockedChars) {
    global $Err;
    $Err = "中的一个或多个文件或文件夹的名称包含禁止使用的字符<br /><br />\n下面显示了不允许使用的完整字符列表：<br />\n\t\t$AllBlockedChars";
}
