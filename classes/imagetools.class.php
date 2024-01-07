<?php

interface ImageStorage {
    public function upload($Name, $Content);
    public function multi_upload($Datas);
}

/**
 * ImageTools Class
 * Thumbnail aide, mostly
 */
class ImageTools {
    /**
     * @var ImageStorage $Provider
     */
    public static $Provider;
    /**
     * Store processed links to avoid repetition
     * @var array 'URL' => 'Parsed URL'
     */
    private static $Storage = array();

    /**
     * We use true as an extra property to make the domain an array key
     * @var array $Hosts Array of image hosts
     */
    private static $Hosts = array(
        'whatimg.com' => true,
        'imgur.com' => true
    );

    /**
     * Blacklisted sites
     * @var array $Blacklist Array of blacklisted hosts
     */
    private static $Blacklist = CONFIG['IMAGE_HOST_BLACKLIST'];

    private static $Whitelist = CONFIG['IMAGE_HOST_WHITELIST'];

    /**
     * Array of image hosts that provide thumbnailing
     * @var array $Thumbs
     */
    private static $Thumbs = array(
        'i.imgur.com' => true,
        'whatimg.com' => true
    );

    /**
     * Array of extensions
     * @var array $Extensions
     */
    private static $Extensions = array(
        'jpg' => true,
        'jpeg' => true,
        'png' => true,
        'gif' => true
    );

    /**
     * Array of user IDs whose avatars have been checked for size
     * @var array $CheckedAvatars
     */
    private static $CheckedAvatars = array();
    private static $CheckedAvatars2 = array();

    /**
     * Array of user IDs whose donor icons have been checked for size
     * @var array $CheckedDonorIcons
     */
    private static $CheckedDonorIcons = array();

    /**
     * Checks from our list of valid hosts
     * @param string $Host Domain/host to check
     * @return boolean
     */
    public static function valid_host($Host) {
        return !empty(self::$Hosts[$Host]) && self::$Hosts[$Host] === true;
    }

    public static function init($Provider = null) {
        switch ($Provider) {
            case 'minio':
                self::$Provider = new Minio();
                break;
            case 'local':
                self::$Provider = new LocalImage();
                break;
            default:
                self::$Provider = new LocalImage();
                break;
        }
    }

    /**
     * Checks if a link's host is (not) good, otherwise displays an error.
     * @param string $Url Link to an image
     * @return boolean
     */
    public static function blacklisted($Url, $ShowError = true) {
        foreach (self::$Blacklist as &$Value) {
            $Blacklisted = stripos($Url, $Value);
            if ($Blacklisted !== false) {
                $ParsedUrl = parse_url($Url);
                if ($ShowError) {
                    error($ParsedUrl['host'] . ' is not an allowed image host. Please use a different host.');
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a link's host is (not) good, otherwise displays an error.
     * @param string $Url Link to an image
     * @return boolean
     */
    public static function whitelisted($Url, $ShowError = true) {
        foreach (self::$Whitelist as &$Value) {
            $Whitelisted = stripos($Url, $Value);
            if ($Whitelisted == true) {
                return true;
            }
        }
        $ParsedUrl = parse_url($Url);
        if ($ShowError) {
            error($ParsedUrl['host'] . ' is not an allowed image host. Please use a different host.');
        }
        return false;
    }

    /**
     * Checks to see if a link has a thumbnail
     * @param string $Url Link to an image
     * @return string|false Matched host or false
     */
    private static function thumbnailable($Url) {
        $ParsedUrl = parse_url($Url);
        return !empty(self::$Thumbs[$ParsedUrl['host']]);
    }

    public static function match_minetype($Ext, $MineType) {
        switch ($Ext) {
            case 'jpg':
                return in_array($MineType, ['image/jpeg', 'image/jpg']);
            case 'jpeg':
                return in_array($MineType, ['image/jpeg', 'image/jpg']);
            case 'gif':
                return in_array($MineType, ['image/gif']);
            case 'png':
                return in_array($MineType, ['image/png']);
        }
        return false;
    }

    /**
     * Checks an extension
     * @param string $Ext Extension to check
     * @return boolean
     */
    public static function valid_extension($Ext) {
        $Ext = strtolower($Ext);
        return !empty(self::$Extensions[$Ext]) && (self::$Extensions[$Ext] === true);
    }

    /**
     * Stores a link with a (thumbnail) link
     * @param type $Link
     * @param type $Processed
     */
    private static function store($Link, $Processed) {
        self::$Storage[$Link] = $Processed;
    }

    /**
     * Retrieves an entry from our storage
     * @param type $Link
     * @return boolean|string Returns false if no match
     */
    private static function get_stored($Link) {
        if (isset(self::$Storage[$Link])) {
            return self::$Storage[$Link];
        }
        return false;
    }

    /**
     * Checks if URL points to a whatimg thumbnail.
     */
    private static function has_whatimg_thumb($Url) {
        return (strpos($Url, '_thumb') !== false);
    }

    /**
     * Cleans up imgur URL if it already has a modifier attached to the end of it.
     */
    private static function clean_imgur_url($Url) {
        $Extension = pathinfo($Url, PATHINFO_EXTENSION);
        $Full = preg_replace('/\.[^.]*$/', '', $Url);
        $Base = substr($Full, 0, strrpos($Full, '/'));
        $Path = substr($Full, strrpos($Full, '/') + 1);
        if (strlen($Path) == 6) {
            $Last = $Path[strlen($Path) - 1];
            if ($Last == 'm' || $Last == 'l' || $Last == 's' || $Last == 'h' || $Last == 'b') {
                $Path = substr($Path, 0, -1);
            }
        }
        return "$Base/$Path.$Extension";
    }

    /**
     * Replaces the extension.
     */
    private static function replace_extension($String, $Extension) {
        return preg_replace('/\.[^.]*$/', $Extension, $String);
    }

    /**
     * Create image proxy URL
     * @param string $Url image URL
     * @param bool/string $CheckSize - accepts one of false, "avatar", "avatar2", or "donoricon"
     * @param bool/string/number $UserID - user ID for avatars and donor icons
     * @return image proxy URL
     */
    public static function proxy_url($Url, $CheckSize, $UserID, &$ExtraInfo) {
        global $SSL;

        if ($UserID) {
            $ExtraInfo = "&amp;userid=$UserID";
            if ($CheckSize === 'avatar' && !isset(self::$CheckedAvatars[$UserID])) {
                $ExtraInfo .= "&amp;type=$CheckSize";
                self::$CheckedAvatars[$UserID] = true;
            } elseif ($CheckSize === 'avatar2' && !isset(self::$CheckedAvatars2[$UserID])) {
                $ExtraInfo .= "&amp;type=$CheckSize";
                self::$CheckedAvatars2[$UserID] = true;
            } elseif ($CheckSize === 'donoricon' && !isset(self::$CheckedDonorIcons[$UserID])) {
                $ExtraInfo .= "&amp;type=$CheckSize";
                self::$CheckedDonorIcons[$UserID] = true;
            }
        }

        return CONFIG['SITE_URL'] . "/image.php?c=1&amp;i=" . urlencode($Url);
    }

    /**
     * Determine the image URL. This takes care of the image proxy and thumbnailing.
     * @param string $Url
     * @param bool $Thumb
     * @param bool/string $CheckSize - accepts one of false, "avatar", "avatar2", or "donoricon"
     * @param bool/string/number $UserID - user ID for avatars and donor icons
     * @return string
     */
    public static function process($Url, $Thumb = false, $CheckSize = false, $UserID = false) {
        if (empty($Url)) {
            return '';
        }

        if ($Found = self::get_stored($Url . ($Thumb ? '_thumb' : ''))) {
            return $Found;
        }

        $ProcessedUrl = $Url;
        if ($Thumb) {
            $Extension = pathinfo($Url, PATHINFO_EXTENSION);
            if (self::thumbnailable($Url) && self::valid_extension($Extension)) {
                if (strpos($Url, 'whatimg') !== false && !self::has_whatimg_thumb($Url)) {
                    $ProcessedUrl = self::replace_extension($Url, '_thumb.' . $Extension);
                } elseif (strpos($Url, 'imgur') !== false) {
                    $ProcessedUrl = self::replace_extension(self::clean_imgur_url($Url), 'm.' . $Extension);
                }
            }
        }

        $ExtraInfo = '';
        if (check_perms('site_proxy_images')) {
            $ProcessedUrl = self::proxy_url($ProcessedUrl, $CheckSize, $UserID, $ExtraInfo);
        }
        self::store($Url . ($Thumb ? '_thumb' : ''), $ProcessedUrl);
        return $ProcessedUrl . $ExtraInfo;
    }

    /**
     * Cover art thumbnail in browse, on artist pages etc.
     * @global array $CategoryIcons
     * @param string $Url
     * @param int $CategoryID
     */
    public static function cover_thumb($Url, $CategoryID) {
        global $CategoryIcons;
        if ($Url) {
            $Src = self::process($Url, true);
        } else {
            $Src = CONFIG['STATIC_SERVER'] . 'common/noartwork/' . $CategoryIcons[$CategoryID - 1];
        }
?>
        <img src="<?= $Src ?>" width="94" height="137" alt="Cover" onclick="lightbox.init(this, 100)" />
<?php
    }

    public static function fetch_upload($Name, $Url) {
        try {
            $data = file_get_contents($Url);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return "";
        }
        return self::$Provider->upload($Name, $data);
    }

    public static function multi_fetch_upload($Datas) {
        $ContentDatas = [];
        foreach ($Datas as $Data) {
            $data = file_get_contents($Data['Url']);
            $file_info = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $file_info->buffer($data);
            if (!self::match_minetype($Data['Ext'], $mime_type)) {
                throw new Exception("ext not match: $mime_type");
            }

            $ContentDatas[] = ['Content' => $data, 'Name' => $Data['Name'], "MimeType" => $mime_type];
        }
        return self::$Provider->multi_upload($ContentDatas);
    }
}
