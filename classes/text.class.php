<?
class Text {
    /**
     * Array of valid tags; tag => max number of attributes
     * @var array $ValidTags
     */
    private static $ValidTags = array(
        'b' => 0,
        'u' => 0,
        'i' => 0,
        's' => 0,
        '*' => 0,
        '#' => 0,
        'artist' => 0,
        'user' => 0,
        'n' => 0,
        'inlineurl' => 0,
        'inlinesize' => 1,
        'headline' => 1,
        'align' => 1,
        'color' => 1,
        'colour' => 1,
        'size' => 1,
        'url' => 1,
        'img' => 1,
        'quote' => 1,
        'pre' => 1,
        'code' => 1,
        'tex' => 0,
        'hide' => 1,
        'spoiler' => 1,
        'plain' => 0,
        'important' => 0,
        'torrent' => 0,
        'rule' => 0,
        'mature' => 1,
        'table' => 1,
        'tr' => 1,
        'td' => 1,
        'lang' => 1,
        'mediainfo' => 0,
        'bdinfo' => 0,
        'comparison' => 10
    );

    /**
     * Array of smilies; code => image file in CONFIG['STATIC_SERVER']/common/smileys
     * @var array $Smileys
     */
    private static $Smileys = array(
        ':angry:'       => '😡',
        ':-D'           => '😆',
        ':D'            => '😆',
        ':|'            => '😑',
        ':-|'           => '😑',
        ':blush:'       => '😊',
        ':cool:'        => '😎',
        ':&#39;('       => '😭',
        ':crying:'      => '😭',
        '&gt;.&gt;'     => '😏',
        ':frown:'       => '😞',
        '&lt;3'         => '❤️',
        ':unsure:'      => '🤔',
        ':whatlove:'    => '😻',
        ':lol:'         => '😂',
        ':loveflac:'    => '😻',
        ':flaclove:'    => '😻',
        ':ninja:'       => '🥷',
        ':no:'          => '🙅',
        ':nod:'         => '🫡',
        ':ohno:'        => '😞',
        ':ohnoes:'      => '😞',
        ':omg:'         => '😱',
        ':o'            => '😱',
        ':O'            => '😱',
        ':paddle:'      => '🚣',
        ':('            => '😥',
        ':-('           => '😥',
        ':shifty:'      => '😉',
        ':sick:'        => '🤒',
        ':)'            => '😀',
        ':-)'           => '😀',
        ':sorry:'       => '🙇',
        ':thanks:'      => '🙏',
        ':P'            => '😛',
        ':p'            => '😛',
        ':-P'           => '😛',
        ':-p'           => '😛',
        ':wave:'        => '👋',
        ';-)'           => '😉',
        ':wink:'        => '😉',
        ':creepy:'      => '🧟',
        ':worried:'     => '😟',
        ':wtf:'         => '🤦',
        ':wub:'         => '🥰',
    );

    /**
     * Processed version of the $Smileys array, see {@link smileys}
     * @var array $ProcessedSmileys
     */
    private static $ProcessedSmileys = array();

    /**
     * Whether or not to turn images into URLs (used inside [quote] tags).
     * This is an integer reflecting the number of levels we're doing that
     * transition, i.e. images will only be displayed as images if $NoImg <= 0.
     * By setting this variable to a negative number you can delay the
     * transition to a deeper level of quotes.
     * @var int $NoImg
     */
    private static $NoImg = 0;

    /**
     * Internal counter for the level of recursion in to_html
     * @var int $Levels
     */
    private static $Levels = 0;

    /**
     * The maximum amount of nesting allowed (exclusive)
     * In reality n-1 nests are shown.
     * @var int $MaximumNests
     */
    private static $MaximumNests = 10;

    /**
     * Used to detect and disable parsing (e.g. TOC) within quotes
     * @var int $InQuotes
     */
    private static $InQuotes = 0;

    /**
     * Used to [hide] quote trains starting with the specified depth (inclusive)
     * @var int $NestsBeforeHide
     *
     * This defaulted to 5 but was raised to 10 to effectively "disable" it until
     * an optimal number of nested [quote] tags is chosen. The variable $MaximumNests
     * effectively overrides this variable, if $MaximumNests is less than the value
     * of $NestsBeforeHide.
     */
    private static $NestsBeforeHide = 10;

    /**
     * Array of headlines for Table Of Contents (TOC)
     * @var array $HeadLines
     */
    private static $Headlines;

    /**
     * Counter for making headline URLs unique
     * @var int $HeadLines
     */
    private static $HeadlineID = 0;

    /**
     * Depth
     * @var array $HeadlineLevels
     */
    private static $HeadlineLevels = array('1', '2', '3', '4');

    /**
     * TOC enabler
     * @var bool $TOC
     */
    public static $TOC = true;

    /**
     * Output BBCode as XHTML
     * @param string $Str BBCode text
     * @param bool $OutputTOC Ouput TOC near (above) text
     * @param int $Min See {@link parse_toc}
     * @return string
     */
    public static function full_format($Str, $OutputTOC = true, $Min = 10) {
        global $Debug;
        $Debug->set_flag('BBCode start');
        $Str = display_str($Str);
        self::$Headlines = array();

        //Inline links
        $URLPrefix = '(\[url\]|\[url\=|\[img\=|\[img\])';
        $Str = preg_replace('/' . $URLPrefix . '\s+/i', '$1', $Str);
        $Str = preg_replace('/(?<!' . $URLPrefix . ')http(s)?:\/\//i', '$1[inlineurl]http$2://', $Str);
        // For anonym.to and archive.org links, remove any [inlineurl] in the middle of the link

        $Str = preg_replace_callback(
            '/(?<=\[inlineurl\]|' . $URLPrefix . ')(\S*\[inlineurl\]\S*)/m',
            function ($matches) {
                return str_replace("[inlineurl]", "", $matches[0]);
            },
            $Str
        );

        if (self::$TOC) {
            $Str = preg_replace('/(\={5})([^=].*)\1/i', '[headline=4]$2[/headline]', $Str);
            $Str = preg_replace('/(\={4})([^=].*)\1/i', '[headline=3]$2[/headline]', $Str);
            $Str = preg_replace('/(\={3})([^=].*)\1/i', '[headline=2]$2[/headline]', $Str);
            $Str = preg_replace('/(\={2})([^=].*)\1/i', '[headline=1]$2[/headline]', $Str);
        } else {
            $Str = preg_replace('/(\={4})([^=].*)\1/i', '[inlinesize=3]$2[/inlinesize]', $Str);
            $Str = preg_replace('/(\={3})([^=].*)\1/i', '[inlinesize=5]$2[/inlinesize]', $Str);
            $Str = preg_replace('/(\={2})([^=].*)\1/i', '[inlinesize=7]$2[/inlinesize]', $Str);
        }

        $HTML = str_replace(array("\r\n", "\r", "\n"), '<br />', self::to_html(self::parse($Str)));

        if (self::$TOC && $OutputTOC) {
            $HTML = self::parse_toc($Min) . $HTML;
        }


        $Debug->set_flag('BBCode end');
        return $HTML;
    }

    public static function strip_bbcode($Str) {
        $Str = display_str($Str);

        //Inline links
        $Str = preg_replace('/(?<!(\[url\]|\[url\=|\[img\=|\[img\]))http(s)?:\/\//i', '$1[inlineurl]http$2://', $Str);

        return str_replace(array("\r\n", "\r", "\n"), '<br />', self::raw_text(self::parse($Str)));
    }


    private static function valid_url($Str, $Extension = '', $Inline = false) {
        $Regex = '/^';
        $Regex .= '(https?|ftps?|irc):\/\/'; // protocol
        $Regex .= '(\w+(:\w+)?@)?'; // user:pass@
        $Regex .= '(';
        $Regex .= '(([0-9]{1,3}\.){3}[0-9]{1,3})|'; // IP or...
        $Regex .= '(localhost(\:[0-9]{1,5})?)|'; // locahost or...
        $Regex .= '(([a-z0-9\-\_]+\.)+\w{2,6})'; // sub.sub.sub.host.com
        $Regex .= ')';
        $Regex .= '(:[0-9]{1,5})?'; // port
        $Regex .= '\/?'; // slash?
        $Regex .= '(\/?[0-9a-z\-_.,&=@~%\/:;()+|!#]+)*'; // /file
        if (!empty($Extension)) {
            $Regex .= $Extension;
        }

        // query string
        if ($Inline) {
            $Regex .= '(\?([0-9a-z\-_.,%\/\@~&=:;()+*\^$!#|?]|\[\d*\])*)?';
        } else {
            $Regex .= '(\?[0-9a-z\-_.,%\/\@[\]~&=:;()+*\^$!#|?]*)?';
        }

        $Regex .= '(#[a-z0-9\-_.,%\/\@[\]~&=:;()+*\^$!]*)?'; // #anchor
        $Regex .= '$/i';

        return preg_match($Regex, $Str, $Matches);
    }

    public static function local_url($Str) {
        $URLInfo = parse_url($Str);
        if (!$URLInfo) {
            return false;
        }
        $Host = $URLInfo['host'];
        // If for some reason your site does not require subdomains or contains a directory in the CONFIG['SITE_URL'], revert to the line below.
        if ($Host === CONFIG['SITE_HOST']) {
            $URL = '';
            if (!empty($URLInfo['path'])) {
                $URL .= ltrim($URLInfo['path'], '/'); // Things break if the path starts with '//'
            }
            if (!empty($URLInfo['query'])) {
                $URL .= "?$URLInfo[query]";
            }
            if (!empty($URLInfo['fragment'])) {
                $URL .= "#$URLInfo[fragment]";
            }
            return $URL ? "/$URL" : false;
        } else {
            return false;
        }
    }


    /*
    How parsing works

    Parsing takes $Str, breaks it into blocks, and builds it into $Array.
    Blocks start at the beginning of $Str, when the parser encounters a [, and after a tag has been closed.
    This is all done in a loop.

    EXPLANATION OF PARSER LOGIC

    1) Find the next tag (regex)
        1a) If there aren't any tags left, write everything remaining to a block and return (done parsing)
        1b) If the next tag isn't where the pointer is, write everything up to there to a text block.
    2) See if it's a [[wiki-link]] or an ordinary tag, and get the tag name
    3) If it's not a wiki link:
        3a) check it against the self::$ValidTags array to see if it's actually a tag and not [bullshit]
            If it's [not a tag], just leave it as plaintext and move on
        3b) Get the attribute, if it exists [name=attribute]
    4) Move the pointer past the end of the tag
    5) Find out where the tag closes (beginning of [/tag])
        5a) Different for different types of tag. Some tags don't close, others are weird like [*]
        5b) If it's a normal tag, it may have versions of itself nested inside - e.g.:
            [quote=bob]*
                [quote=joe]I am a redneck!**[/quote]
                Me too!
            ***[/quote]
        If we're at the position *, the first [/quote] tag is denoted by **.
        However, our quote tag doesn't actually close there. We must perform
        a loop which checks the number of opening [quote] tags, and make sure
        they are all closed before we find our final [/quote] tag (***).

        5c) Get the contents between [open] and [/close] and call it the block.
        In many cases, this will be parsed itself later on, in a new parse() call.
        5d) Move the pointer past the end of the [/close] tag.
    6) Depending on what type of tag we're dealing with, create an array with the attribute and block.
        In many cases, the block may be parsed here itself. Stick them in the $Array.
    7) Increment array pointer, start again (past the end of the [/close] tag)

    */
    private static function parse($Str) {
        $i = 0; // Pointer to keep track of where we are in $Str
        $Len = strlen($Str);
        $Array = array();
        $ArrayPos = 0;
        $StrLC = strtolower($Str);

        while ($i < $Len) {
            $Block = '';

            // 1) Find the next tag (regex)
            // [name(=attribute)?]|[[wiki-link]]
            $IsTag = preg_match("/((\[[a-zA-Z*#]+)(=(?:[^\n'\"\[\]]|\[\d*\])+)?\])|(\[\[[^\n\"'\[\]]+\]\])/", $Str, $Tag, PREG_OFFSET_CAPTURE, $i);

            // 1a) If there aren't any tags left, write everything remaining to a block
            if (!$IsTag) {
                // No more tags
                $Array[$ArrayPos] = substr($Str, $i);
                break;
            }

            // 1b) If the next tag isn't where the pointer is, write everything up to there to a text block.
            $TagPos = $Tag[0][1];
            if ($TagPos > $i) {
                $Array[$ArrayPos] = substr($Str, $i, $TagPos - $i);
                ++$ArrayPos;
                $i = $TagPos;
            }

            // 2) See if it's a [[wiki-link]] or an ordinary tag, and get the tag name
            if (!empty($Tag[4][0])) { // Wiki-link
                $WikiLink = true;
                $TagName = Wiki::unicode_decode(substr($Tag[4][0], 2, -2));
                //file_put_contents('/var/www/log', Wiki::unicode_decode($TagName)."\n", FILE_APPEND);
                $Attrib = '';
            } else { // 3) If it's not a wiki link:
                $WikiLink = false;
                $TagName = strtolower(substr($Tag[2][0], 1));

                //3a) check it against the self::$ValidTags array to see if it's actually a tag and not [bullshit]
                if (!isset(self::$ValidTags[$TagName])) {
                    $Array[$ArrayPos] = substr($Str, $i, ($TagPos - $i) + strlen($Tag[0][0]));
                    $i = $TagPos + strlen($Tag[0][0]);
                    ++$ArrayPos;
                    continue;
                }

                $MaxAttribs = self::$ValidTags[$TagName];

                // 3b) Get the attribute, if it exists [name=attribute]
                if (!empty($Tag[3][0])) {
                    $Attrib = substr($Tag[3][0], 1);
                } else {
                    $Attrib = '';
                }
            }

            // 4) Move the pointer past the end of the tag
            $i = $TagPos + strlen($Tag[0][0]);

            // 5) Find out where the tag closes (beginning of [/tag])

            // Unfortunately, BBCode doesn't have nice standards like XHTML
            // [*], [img=...], and http:// follow different formats
            // Thus, we have to handle these before we handle the majority of tags


            //5a) Different for different types of tag. Some tags don't close, others are weird like [*]
            if ($TagName == 'img' && !empty($Tag[3][0]) && self::valid_url(substr($Tag[3][0], 1))) { //[img=...]
                $Block = ''; // Nothing inside this tag
                // Don't need to touch $i
            } elseif ($TagName == 'inlineurl') { // We did a big replace early on to turn http:// into [inlineurl]http://

                // Let's say the block can stop at a newline or a space
                $CloseTag = strcspn($Str, " \n\r", $i);
                if ($CloseTag === false) { // block finishes with URL
                    $CloseTag = $Len;
                }
                if (preg_match('/[!,.?:]+$/', substr($Str, $i, $CloseTag), $Match)) {
                    $CloseTag -= strlen($Match[0]);
                }
                $URL = substr($Str, $i, $CloseTag);
                if (substr($URL, -1) == ')' && substr_count($URL, '(') < substr_count($URL, ')')) {
                    $CloseTag--;
                    $URL = substr($URL, 0, -1);
                }
                $Block = $URL; // Get the URL

                // strcspn returns the number of characters after the offset $i, not after the beginning of the string
                // Therefore, we use += instead of the = everywhere else
                $i += $CloseTag; // 5d) Move the pointer past the end of the [/close] tag.
            } elseif ($WikiLink == true || $TagName == 'n') {
                // Don't need to do anything - empty tag with no closing
            } elseif ($TagName === '*' || $TagName === '#') {
                // We're in a list. Find where it ends
                $NewLine = $i;
                do { // Look for \n[*]
                    $NewLine = strpos($Str, "\n", $NewLine + 1);
                } while ($NewLine !== false && substr($Str, $NewLine + 1, 3) == "[$TagName]");

                $CloseTag = $NewLine;
                if ($CloseTag === false) { // block finishes with list
                    $CloseTag = $Len;
                }
                $Block = substr($Str, $i, $CloseTag - $i); // Get the list
                $i = $CloseTag; // 5d) Move the pointer past the end of the [/close] tag.
            } else {
                //5b) If it's a normal tag, it may have versions of itself nested inside
                $CloseTag = $i - 1;
                $InTagPos = $i - 1;
                $NumInOpens = 0;
                $NumInCloses = -1;

                $InOpenRegex = '/\[(' . $TagName . ')';
                if ($MaxAttribs > 0) {
                    $InOpenRegex .= "(=[^\n'\"\[\]]+)?";
                }
                $InOpenRegex .= '\]/i';


                // Every time we find an internal open tag of the same type, search for the next close tag
                // (as the first close tag won't do - it's been opened again)
                do {
                    $CloseTag = strpos($StrLC, "[/$TagName]", $CloseTag + 1);
                    if ($CloseTag === false) {
                        $CloseTag = $Len;
                        break;
                    } else {
                        $NumInCloses++; // Majority of cases
                    }

                    // Is there another open tag inside this one?
                    $OpenTag = preg_match($InOpenRegex, $Str, $InTag, PREG_OFFSET_CAPTURE, $InTagPos + 1);
                    if (!$OpenTag || $InTag[0][1] > $CloseTag) {
                        break;
                    } else {
                        $InTagPos = $InTag[0][1];
                        $NumInOpens++;
                    }
                } while ($NumInOpens > $NumInCloses);


                // Find the internal block inside the tag
                $Block = substr($Str, $i, $CloseTag - $i); // 5c) Get the contents between [open] and [/close] and call it the block.

                $i = $CloseTag + strlen($TagName) + 3; // 5d) Move the pointer past the end of the [/close] tag.

            }

            // 6) Depending on what type of tag we're dealing with, create an array with the attribute and block.
            switch ($TagName) {
                case 'inlineurl':
                    $Array[$ArrayPos] = array('Type' => 'inlineurl', 'Attr' => $Block, 'Val' => '');
                    break;
                case 'url':
                    $Array[$ArrayPos] = array('Type' => 'img', 'Attr' => $Attrib, 'Val' => $Block);
                    if (empty($Attrib)) { // [url]http://...[/url] - always set URL to attribute
                        $Array[$ArrayPos] = array('Type' => 'url', 'Attr' => $Block, 'Val' => '');
                    } else {
                        $Array[$ArrayPos] = array('Type' => 'url', 'Attr' => $Attrib, 'Val' => self::parse($Block));
                    }
                    break;
                case 'quote':
                    $Array[$ArrayPos] = array('Type' => 'quote', 'Attr' => self::parse($Attrib), 'Val' => self::parse($Block));
                    break;
                case 'img':
                case 'image':
                    if (empty($Block)) {
                        $Elements = explode(',', $Attrib);
                        $Block = end($Elements);
                        $Attrib = preg_replace('/,?' . preg_quote($Block, '/') . '/i', '', $Attrib);
                    }
                    $Array[$ArrayPos] = array('Type' => 'img', 'Attr' => $Attrib,  'Val' => $Block);
                    break;
                case 'aud':
                case 'mp3':
                case 'audio':
                    if (empty($Block)) {
                        $Block = $Attrib;
                    }
                    $Array[$ArrayPos] = array('Type' => 'aud', 'Val' => $Block);
                    break;
                case 'user':
                    $Array[$ArrayPos] = array('Type' => 'user', 'Val' => $Block);
                    break;
                case 'artist':
                    $Array[$ArrayPos] = array('Type' => 'artist', 'Val' => $Block);
                    break;
                case 'torrent':
                    $Array[$ArrayPos] = array('Type' => 'torrent', 'Val' => $Block);
                    break;
                case 'tex':
                    $Array[$ArrayPos] = array('Type' => 'tex', 'Val' => $Block);
                    break;
                case 'rule':
                    $Array[$ArrayPos] = array('Type' => 'rule', 'Val' => $Block);
                    break;
                case 'pre':
                case 'code':
                case 'plain':
                    $Block = strtr($Block, array('[inlineurl]' => ''));

                    $Callback = function ($matches) {
                        $n = $matches[2];
                        $text = '';
                        if ($n < 5 && $n > 0) {
                            $e = str_repeat('=', $matches[2] + 1);
                            $text = $e . $matches[3] . $e;
                        }
                        return $text;
                    };
                    $Block = preg_replace_callback('/\[(headline)\=(\d)\](.*?)\[\/\1\]/i', $Callback, $Block);

                    $Block = preg_replace('/\[inlinesize\=3\](.*?)\[\/inlinesize\]/i', '====$1====', $Block);
                    $Block = preg_replace('/\[inlinesize\=5\](.*?)\[\/inlinesize\]/i', '===$1===', $Block);
                    $Block = preg_replace('/\[inlinesize\=7\](.*?)\[\/inlinesize\]/i', '==$1==', $Block);


                    $Array[$ArrayPos] = array('Type' => $TagName, 'Val' => $Block);
                    break;
                case 'spoiler':
                case 'hide':
                    $Array[$ArrayPos] = array('Type' => 'hide', 'Attr' => $Attrib, 'Val' => self::parse($Block));
                    break;
                case 'mature':
                    $Array[$ArrayPos] = array('Type' => 'mature', 'Attr' => $Attrib, 'Val' => self::parse($Block));
                    break;
                case '#':
                case '*':
                    $Array[$ArrayPos] = array('Type' => 'list');
                    $Array[$ArrayPos]['Val'] = explode("[$TagName]", $Block);
                    $Array[$ArrayPos]['ListType'] = $TagName === '*' ? 'ul' : 'ol';
                    $Array[$ArrayPos]['Tag'] = $TagName;
                    foreach ($Array[$ArrayPos]['Val'] as $Key => $Val) {
                        $Array[$ArrayPos]['Val'][$Key] = self::parse(trim($Val));
                    }
                    break;
                case 'n':
                    $ArrayPos--;
                    break; // n serves only to disrupt bbcode (backwards compatibility - use [pre])
                case 'mediainfo':
                    if (strstr(strtolower($Block), 'disc size')) {
                        $Array[$ArrayPos] = array('Type' => 'bdinfo', 'Val' => $Block);
                    } else {
                        $Array[$ArrayPos] = array('Type' => 'mediainfo', 'Val' =>  $Block);
                    }
                    break;
                case 'bdinfo':
                    $Array[$ArrayPos] = array('Type' => 'bdinfo', 'Val' => $Block);
                    break;

                case 'comparison':
                    $Array[$ArrayPos] = array('Type' => $TagName, 'Val' => self::parse($Block));
                    if (!empty($Attrib) && $MaxAttribs > 0) {
                        $Array[$ArrayPos]['Attr'] = $Attrib;
                    }
                    break;
                default:
                    if ($WikiLink == true) {
                        $Array[$ArrayPos] = array('Type' => 'wiki', 'Val' => $TagName);
                    } else {

                        // Basic tags, like [b] or [size=5]

                        $Array[$ArrayPos] = array('Type' => $TagName, 'Val' => self::parse($Block));
                        if (!empty($Attrib) && $MaxAttribs > 0) {
                            $Array[$ArrayPos]['Attr'] = strtolower($Attrib);
                        }
                    }
            }

            $ArrayPos++; // 7) Increment array pointer, start again (past the end of the [/close] tag)
        }
        return $Array;
    }


    private static function reduceHeadlinesLevel($l, $r) {
        /*
          1 3 3 3 3 1 2 4 4 4 2 4 4 4 2
          1 3 3 3 3  1 2 4 4 4 2 4 4 4 2
          1 2 2 2 2
                       2 4 4 4  2 4 4 4 2
                       2 3 3 3
                               2 4 4 4  2
                               2 3 3 3
                                        2
          1 2 2 2 2 1 2 3 3 3 2 3 3 3 2
        */
        if ($l >= $r) {
            // 1
            return;
        }
        for ($i = $l + 1; $i <= $r; $i++) {
            if (self::$Headlines[$i][0] == self::$Headlines[$l][0]) {
                // 13331444 => 1333 1444
                self::reduceHeadlinesLevel($l, $i - 1);
                self::reduceHeadlinesLevel($i, $r);
                return;
            }
        }
        if (self::$Headlines[$l][0] + 1 < self::$Headlines[$l + 1][0]) {
            // 1333 => 1222
            $sub = self::$Headlines[$l + 1][0] - self::$Headlines[$l][0] - 1;
            for ($i = $l + 1; $i <= $r; $i++) {
                self::$Headlines[$i][0] -= $sub;
            }
        }
        // 1222 => 222
        self::reduceHeadlinesLevel($l + 1, $r);
    }
    /**
     * Generates a navigation list for TOC
     * @param int $Min Minimum number of headlines required for a TOC list
     */
    public static function parse_toc($Min = 3) {
        self::reduceHeadlinesLevel(0, count(self::$Headlines) - 1);
        if (count(self::$Headlines) > $Min) {
            $list = '<ol class="navigation_list">';
            $i = 0;
            $level = 0;
            $off = 0;

            $only13 = true;
            foreach (self::$Headlines as $t) {
                if ($t[0] == 2) {
                    $only13 = false;
                    break;
                }
            }
            foreach (self::$Headlines as $t) {
                $n = (int)$t[0];
                if ($only13 && $n == 3) {
                    $n = 2;
                }
                if ($i === 0 && $n > 1) {
                    $off = $n - $level;
                }
                self::headline_level($n, $level, $list, $i, $off);
                $list .= sprintf('<li><a href="#%2$s">%1$s</a>', $t[1], $t[2]);
                $level = $n;
                $off = 0;
                $i++;
            }

            $list .= str_repeat('</li></ol>', $level);
            $list .= "\n\n";
            return $list;
        }
    }

    /**
     * Generates the list items and proper depth
     *
     * First check if the item should be higher than the current level
     * - Close the list and previous lists
     *
     * Then check if the item should go lower than the current level
     * - If the list doesn't open on level one, use the Offset
     * - Open appropriate sub lists
     *
     * Otherwise the item is on the same as level as the previous item
     *
     * @param int $ItemLevel Current item level
     * @param int $Level Current list level
     * @param str $List reference to an XHTML string
     * @param int $i Iterator digit
     * @param int $Offset If the list doesn't start at level 1
     */
    private static function headline_level(&$ItemLevel, &$Level, &$List, $i, &$Offset) {
        if ($ItemLevel < $Level) {
            $diff = $Level - $ItemLevel;
            $List .= '</li>' . str_repeat('</ol></li>', $diff);
        } elseif ($ItemLevel > $Level) {
            $diff = $ItemLevel - $Level;
            if ($Offset > 0) $List .= str_repeat('<li><ol>', $Offset - 2);

            if ($ItemLevel > 1) {
                $List .= $i === 0 ? '<li>' : '';
                $List .= "\n<ol>\n";
            }
        } else {
            $List .= $i > 0 ? '</li>' : '<li>';
        }
    }
    private static function getval($str, $key) {
        if (preg_match("/^\s*$key\s*:\s*(.+)$/mi", $str, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }
    private static function getallval($str, $key) {
        if (preg_match_all("/^\s*$key\s*:\s*(.+)$/mi", $str, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }
    private static function removeAllFalse($a) {
        return array_filter($a, function ($aa) {
            if (is_array($aa)) {
                foreach ($aa as $v) {
                    if ($v) return true;
                }
                return false;
            } else {
                return $aa !== false;
            }
        });
    }
    private static function onlyDigit($str) {
        return implode(array_filter(str_split($str), function ($ch) {
            return is_number($ch);
        }));
    }
    private static function genTable($title, $data) {
        $Str = '';
        $Str .= "<table class='$title'><caption>$title</caption><tr>";
        if (count($data) == 1) {
            if (is_array($data[0])) {
                foreach ($data[0] as $k => $v) {
                    if (!empty($v)) {
                        $Str .= "<tr class='row'><td class='key'>$k:</td><td class='value'>$v</td></tr>";
                    }
                }
            } else {
                $Str .=  "<tr><td>$data[0]</td></tr>";
            }
        } else {
            if ($title == "Video") {
                $Index = 1;
                $Str .= implode(array_map(function ($a) use (&$Index) {
                    $s = "";
                    if ($Index != 1) {
                        $s .= "<tr><td>&nbsp; <td></tr>";
                    }
                    $s .= "<tr class='row'><td class='key'>#$Index</td></tr>";
                    foreach ($a as $k => $v) {
                        if (!empty($v)) {
                            $s .= "<tr class='row'><td class='key'>$k:</td><td class='value'>$v</td></tr>";
                        }
                    }
                    $Index++;
                    return $s;
                }, $data));
            } else if ($title == "Audio") {
                $Index = 1;
                $Str .= implode(array_map(function ($a) use (&$Index) {
                    $s = "";
                    $s = "<tr class='row'><td class='key audio_track_number'>#$Index: </td><td class='value'>$a</td></tr>";
                    $Index++;
                    return $s;
                }, $data));
            } else {
                $Str .= implode(array_map(function ($a) {
                    $s = "";
                    if (is_array($a)) {
                        foreach ($a as $k => $v) {
                            if (!empty($v)) {
                                $s .= "<tr class='row'><td class='key'>$k:</td><td class='value'>$v</td></tr>";
                            }
                        }
                    }

                    return $s;
                }, $data));
            }
        }
        $Str .= "</tr></table>";
        return $Str;
    }
    private static function getTextTableColStartIndexes($table) {
        if (preg_match("/^\s*([- ]+)\s*$/mi", $table, $match)) {
            $Indexs = [0];
            $LastIndex = 0;
            while ($LastIndex !== false) {
                $LastIndex = strpos($match[1], ' -', $LastIndex + 1);
                if ($LastIndex !== false) {
                    $Indexs[] = $LastIndex + 1;
                }
            }
            return $Indexs;
        } else {
            return false;
        }
    }

    private static function parseBDInfoVideo($VideoTable) {
        $Ret = [];
        $Rows = explode("\n", $VideoTable);
        $len = count($Rows);
        for ($i = 0; $i < $len; $i++) {
            $v = $Rows[$i];
            unset($Rows[$i]);
            if (str_starts_with($v, '-')) {
                break;
            }
        }
        foreach ($Rows as $key => $Row) {
            preg_match('/(.*Video (?:\([0-9,]\))?) *([0-9.\(\),]* kbps) *(.*)/mi', $Row, $matches);
            list(, $Codec, $Bitrate, $Desc) =  $matches;
            $Ret[] = array_slice($matches, 1);
        }
        return  $Ret;
    }

    private static function parseBDInfoAudio($AudioTable) {
        $Rows = explode("\n", $AudioTable);
        $len = count($Rows);
        for ($i = 0; $i < $len; $i++) {
            $v = $Rows[$i];
            unset($Rows[$i]);
            if (str_starts_with($v, '-')) {
                break;
            }
        }
        foreach ($Rows as $key => $Row) {
            preg_match('/(.*(?:Audio|Atmos)) *([a-zA-Z ]*[a-zA-Z]) *([0-9.]* kbps) *(.*)/', $Row, $matches);
            $Ret[] = array_slice($matches, 1);
        }
        return $Ret;
    }

    private static function getColValueInTextTable($table) {
        $Rows = explode("\n", $table);
        $len = count($Rows);
        for ($i = 0; $i < $len; $i++) {
            $v = $Rows[$i];
            unset($Rows[$i]);
            if (str_starts_with($v, '-')) {
                break;
            }
        }
        foreach ($Rows as $key => $Row) {
            $Cols = [];
            foreach (explode('   ', $Row) as $V) {
                if (trim($V)) {
                    $Cols[] = trim($V);
                }
            }
            $Rows[$key] = $Cols;
        }
        return array_values($Rows);
    }

    private static function getAudioChannelsInBDInfo($str) {
        if (preg_match("/(\d\.\d)/mi", $str, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }
    private static function to_html($Array) {
        global $SSL, $Debug;
        self::$Levels++;
        /*
         * Hax prevention
         * That's the original comment on this.
         * Most likely this was implemented to avoid anyone nesting enough
         * elements to reach PHP's memory limit as nested elements are
         * solved recursively.
         * Original value of 10, it is now replaced in favor of
         * $MaximumNests.
         * If this line is ever executed then something is, infact
         * being haxed as the if before the block type switch for different
         * tags should always be limiting ahead of this line.
         * (Larger than vs. smaller than.)
         */
        if (self::$Levels > self::$MaximumNests) {
            return $Block['Val']; // Hax prevention, breaks upon exceeding nests.
        }
        $Str = '';
        foreach ($Array as $Block) {
            if (is_string($Block)) {
                $Block = str_replace('[hr]', '<hr class="bbcode_hr">', $Block);
                $Str .= self::smileys($Block);
                continue;
            }
            if (self::$Levels < self::$MaximumNests) {
                switch ($Block['Type']) {
                    case 'b':
                        $Str .= '<strong>' . self::to_html($Block['Val']) . '</strong>';
                        break;
                    case 'u':
                        $Str .= '<span style="text-decoration: underline;">' . self::to_html($Block['Val']) . '</span>';
                        break;
                    case 'i':
                        $Str .= '<span style="font-style: italic;">' . self::to_html($Block['Val']) . "</span>";
                        break;
                    case 's':
                        $Str .= '<span style="text-decoration: line-through;">' . self::to_html($Block['Val']) . '</span>';
                        break;
                    case 'important':
                        $Str .= '<strong class="u-colorWarning">' . self::to_html($Block['Val']) . '</strong>';
                        break;
                    case 'user':
                        $Str .= '<a href="user.php?action=search&amp;search=' . urlencode($Block['Val']) . '">' . $Block['Val'] . '</a>';
                        break;
                    case 'artist':
                        $Str .= '<a href="artist.php?artistname=' . urlencode($Block['Val']) . '">' . $Block['Val'] . '</a>';
                        break;
                    case 'rule':
                        $Rule = trim(strtolower($Block['Val']));
                        $Page = '';
                        $RV = explode('#', $Rule);
                        if (count($RV) == 1) {
                            $Num = $RV[0];
                            $Str .= '<a href="rules.php?p=upload#' . urlencode(Format::undisplay_str($Num)) . '">' . 'upload#' . $Num . '</a>';
                        } else {
                            $Page = $RV[0];
                            $Num = $RV[1];
                            $Str .= '<a href="rules.php?p=' . $Page . '#' . urlencode(Format::undisplay_str($Num)) . '">' . $Rule . '</a>';
                        }

                        break;
                    case 'torrent':
                        $Pattern = '/(' . SITELINK_REGEX . '\/torrents\.php.*[\?&]id=)?(\d+)($|&|\#).*/i';
                        $Matches = array();
                        if (preg_match($Pattern, $Block['Val'], $Matches)) {
                            if (isset($Matches[2])) {
                                $GroupID = $Matches[2];
                                $Group = Torrents::get_group($GroupID, true, 0, true, true);
                                if ($Group) {
                                    $Str .= Torrents::group_name($Group);
                                } else {
                                    $Str .= '[torrent]' . str_replace('[inlineurl]', '', $Block['Val']) . '[/torrent]';
                                }
                            }
                        } else {
                            $Str .= '[torrent]' . str_replace('[inlineurl]', '', $Block['Val']) . '[/torrent]';
                        }
                        break;
                    case 'wiki':
                        $Str .= '<a href="wiki.php?action=article&amp;name=' . urlencode($Block['Val']) . '">' . $Block['Val'] . '</a>';
                        break;
                    case 'tex':
                        $Str .= '<img style="vertical-align: middle;" src="' . CONFIG['STATIC_SERVER'] . 'blank.gif" onload="if (this.src.substr(this.src.length - 9, this.src.length) == \'blank.gif\') { this.src = \'https://chart.googleapis.com/chart?cht=tx&amp;chf=bg,s,FFFFFF00&amp;chl=' . urlencode(mb_convert_encoding($Block['Val'], 'UTF-8', 'HTML-ENTITIES')) . '&amp;chco=\' + hexify(getComputedStyle(this.parentNode, null).color); }" alt="' . $Block['Val'] . '" />';
                        break;
                    case 'plain':
                        $Str .= $Block['Val'];
                        break;
                    case 'pre':
                        $Str .= '<pre>' . $Block['Val'] . '</pre>';
                        break;
                    case 'code':
                        $Str .= '<code>' . $Block['Val'] . '</code>';
                        break;
                    case 'list':
                        $Str .= "<$Block[ListType] class=\"postlist\">";
                        foreach ($Block['Val'] as $Line) {

                            $Str .= '<li>' . self::to_html($Line) . '</li>';
                        }
                        $Str .= '</' . $Block['ListType'] . '>';
                        break;
                    case 'align':
                        $ValidAttribs = array('left', 'center', 'right');
                        if (!in_array($Block['Attr'], $ValidAttribs)) {
                            $Str .= '[align=' . $Block['Attr'] . ']' . self::to_html($Block['Val']) . '[/align]';
                        } else {
                            $Str .= '<div style="text-align: ' . $Block['Attr'] . ';">' . self::to_html($Block['Val']) . '</div>';
                        }
                        break;
                    case 'color':
                    case 'colour':
                        $ValidAttribs = array('aqua', 'black', 'blue', 'fuchsia', 'green', 'grey', 'lime', 'maroon', 'navy', 'olive', 'purple', 'red', 'silver', 'teal', 'white', 'yellow');
                        if (!in_array($Block['Attr'], $ValidAttribs) && !preg_match('/^#[0-9a-f]{6}$/', $Block['Attr'])) {
                            $Str .= '[color=' . $Block['Attr'] . ']' . self::to_html($Block['Val']) . '[/color]';
                        } else {
                            $Str .= '<span style="color: ' . $Block['Attr'] . ';">' . self::to_html($Block['Val']) . '</span>';
                        }
                        break;
                    case 'headline':
                        $text = self::to_html($Block['Val']);
                        $raw = self::raw_text($Block['Val']);
                        if (!in_array($Block['Attr'], self::$HeadlineLevels)) {
                            $Str .= sprintf('%1$s%2$s%1$s', str_repeat('=', $Block['Attr'] + 1), $text);
                        } else {
                            $id = '_' . crc32($raw . self::$HeadlineID);
                            if (self::$InQuotes === 0) {
                                self::$Headlines[] = array($Block['Attr'], $raw, $id);
                            }

                            $Str .= sprintf('<h%1$d id="%3$s">%2$s</h%1$d>', ($Block['Attr'] + 2), $text, $id);
                            self::$HeadlineID++;
                        }
                        break;
                    case 'inlinesize':
                    case 'size':
                        $ValidAttribs = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10');
                        if (!in_array($Block['Attr'], $ValidAttribs)) {
                            $Str .= '[size=' . $Block['Attr'] . ']' . self::to_html($Block['Val']) . '[/size]';
                        } else {
                            $Str .= '<span class="size' . $Block['Attr'] . '">' . self::to_html($Block['Val']) . '</span>';
                        }
                        break;
                    case 'quote':
                        self::$NoImg++; // No images inside quote tags
                        self::$InQuotes++;
                        if (self::$InQuotes == self::$NestsBeforeHide) { //Put quotes that are nested beyond the specified limit in [hide] tags.
                            $Str .= '<strong>Older quotes</strong>: <a href="javascript:void(0);" onclick="BBCode.spoiler(this);">Show</a>';
                            $Str .= '<blockquote class="hidden spoiler">';
                        }
                        if (!empty($Block['Attr'])) {
                            $Exploded = explode('|', self::to_html($Block['Attr']));
                            if (isset($Exploded[1]) && (is_numeric($Exploded[1]) || (in_array($Exploded[1][0], array('a', 't', 'c', 'r')) && is_numeric(substr($Exploded[1], 1))))) {
                                // the part after | is either a number or starts with a, t, c or r, followed by a number (forum post, artist comment, torrent comment, collage comment or request comment, respectively)
                                $PostID = trim($Exploded[1]);
                                $Str .= '<a href="#" onclick="QuoteJump(event, \'' . $PostID . '\'); return false;"><strong class="quoteheader">' . $Exploded[0] . '</strong> wrote: </a>';
                            } else {
                                $Str .= '<strong class="quoteheader">' . $Exploded[0] . '</strong> wrote: ';
                            }
                        }
                        $Str .= '<blockquote>' . self::to_html($Block['Val']) . '</blockquote>';
                        if (self::$InQuotes == self::$NestsBeforeHide) { //Close quote the deeply nested quote [hide].
                            $Str .= '</blockquote><br />'; // Ensure new line after quote train hiding
                        }
                        self::$NoImg--;
                        self::$InQuotes--;
                        break;
                    case 'hide':
                        $Str .= '<strong>' . (($Block['Attr']) ? $Block['Attr'] : t('server.user.hidden_text')) . '</strong>: <a href="javascript:void(0);" onclick="BBCode.spoiler(this);">Show</a>';
                        $Str .= '<blockquote class="hidden spoiler">' . self::to_html($Block['Val']) . '</blockquote>';
                        break;
                    case 'mature':
                        if (G::$LoggedUser['EnableMatureContent']) {
                            if (!empty($Block['Attr'])) {
                                $Str .= '<strong class="mature" style="font-size: 1.2em;">Mature content:</strong><strong> ' . $Block['Attr'] . '</strong><br /> <a href="javascript:void(0);" onclick="BBCode.spoiler(this);">Show</a>';
                                $Str .= '<blockquote class="hidden spoiler">' . self::to_html($Block['Val']) . '</blockquote>';
                            } else {
                                $Str .= '<strong>Use of the [mature] tag requires a description.</strong> The correct format is as follows: <strong>[mature=description] ...content... [/mature]</strong>, where "description" is a mandatory description of the post. Misleading descriptions will be penalized. For further information on our mature content policies, please refer to this <a href="wiki.php?action=article&amp;id=1063">wiki</a>.';
                            }
                        } else {
                            $Str .= '<span class="mature_blocked" style="font-style: italic;"><a href="wiki.php?action=article&amp;id=1063">Mature content</a> has been blocked. You can choose to view mature content by editing your <a href="user.php?action=edit&amp;userid=' . G::$LoggedUser['ID'] . '">settings</a>.</span>';
                        }
                        break;
                    case 'img':
                        $resize = '';
                        if (!empty($Block['Attr'])) {
                            $Elements = explode('x', $Block['Attr']);
                            // Width
                            if (!empty($Elements[0]))
                                $resize .= 'width="' . intval($Elements[0]) . '" ';
                            // Height
                            if (!empty($Elements[1]))
                                $resize .= 'height="' . intval($Elements[1]) . '" ';
                        }
                        if (self::$NoImg > 0 && self::valid_url($Block['Val'])) {
                            $Str .= '<a rel="noreferrer" target="_blank" href="' . $Block['Val'] . '">' . $Block['Val'] . '</a> (image)';
                            break;
                        }
                        if (!self::valid_url($Block['Val'], '\.(jpe?g|gif|png|bmp|tiff)')) {
                            $Str .= '[img]' . $Block['Val'] . '[/img]';
                        } else {
                            //$LocalURL = self::local_url($Block['Val']);
                            $LocalURL = false;
                            if ($LocalURL) {
                                $Str .= '<img loading="lazy" class="scale_image" onclick="lightbox.init(this, $(this).width());" alt="' . $Block['Val'] . '" src="' . $LocalURL . '" ' . $resize . '/>';
                            } else {
                                $Str .= '<img loading="lazy" class="scale_image" onclick="lightbox.init(this, $(this).width());" alt="' . $Block['Val'] . '" src="' . ImageTools::process($Block['Val']) . '" ' . $resize . '/>';
                            }
                        }
                        break;

                    case 'aud':
                        if (self::$NoImg > 0 && self::valid_url($Block['Val'])) {
                            $Str .= '<a rel="noreferrer" target="_blank" href="' . $Block['Val'] . '">' . $Block['Val'] . '</a> (audio)';
                            break;
                        }
                        if (!self::valid_url($Block['Val'], '\.(mp3|ogg|wav)')) {
                            $Str .= '[aud]' . $Block['Val'] . '[/aud]';
                        } else {
                            //TODO: Proxy this for staff?
                            $Str .= '<audio controls="controls" src="' . $Block['Val'] . '"><a rel="noreferrer" target="_blank" href="' . $Block['Val'] . '">' . $Block['Val'] . '</a></audio>';
                        }
                        break;

                    case 'url':
                        // Make sure the URL has a label
                        if (empty($Block['Val'])) {
                            $Block['Val'] = $Block['Attr'];
                            $NoName = true; // If there isn't a Val for this
                        } else {
                            $Block['Val'] = self::to_html($Block['Val']);
                            $NoName = false;
                        }

                        if (!self::valid_url($Block['Attr'])) {
                            $Str .= '<a rel="noreferrer" target="_blank" href="' . $Block['Attr'] . '">' . $Block['Val'] . '</a>';
                        } else {
                            $LocalURL = self::local_url($Block['Attr']);
                            if ($LocalURL) {
                                if ($NoName) {
                                    $Block['Val'] = substr($LocalURL, 1);
                                }
                                $Str .= '<a href="' . $LocalURL . '">' . $Block['Val'] . '</a>';
                            } else {
                                $Str .= '<a rel="noreferrer" target="_blank" href="' . $Block['Attr'] . '">' . $Block['Val'] . '</a>';
                            }
                        }
                        break;

                    case 'inlineurl':
                        if (!self::valid_url($Block['Attr'], '', true)) {
                            $Array = self::parse($Block['Attr']);
                            $Block['Attr'] = $Array;
                            $Str .= self::to_html($Block['Attr']);
                        } else {
                            $LocalURL = self::local_url($Block['Attr']);
                            if ($LocalURL) {
                                $Str .= '<a href="' . $LocalURL . '">' . substr($LocalURL, 1) . '</a>';
                            } else {
                                $Str .= '<a rel="noreferrer" target="_blank" href="' . $Block['Attr'] . '">' . $Block['Attr'] . '</a>';
                            }
                        }

                        break;
                    case 'table':
                        $TableStyle = '';
                        if (
                            isset($Block['Attr']) &&
                            is_number($Block['Attr']) &&
                            $Block['Attr'] > 0 &&
                            $Block['Attr'] <= 100
                        ) {
                            $TableStyle = ' style="width: ' . intval($Block['Attr']) . '%;"';
                        }
                        $Str .= "<div class=\"TalbeContainer\"><table class=\"Table\" $TableStyle>";
                        foreach ($Block['Val'] as $tr) {
                            if (is_string($tr)) {
                                $tr = trim($tr);
                                if ($tr == '') {
                                    continue;
                                }
                                $tr = str_replace('\\|', '&#124;', $tr);
                                $tr = str_replace('\\n', '<br>', $tr);
                                $Trs = explode("\n", $tr);
                                foreach ($Trs as $Tr) {
                                    $Tr = trim($Tr);
                                    if ($Tr == '') {
                                        continue;
                                    }
                                    $Str .= '<tr class="Table-row">';
                                    $Tds = explode('|', $Tr);
                                    foreach ($Tds as $Td) {
                                        $Td = trim($Td);
                                        if ($Td == '') {
                                            continue;
                                        }
                                        $Str .= "<td class=\"Table-cell\">$Td</td>";
                                    }
                                    $Str .= '</tr>';
                                }
                            } else {
                                $Str .=  self::to_html([$tr]);
                            }
                        }
                        $Str .= '</table></div>';
                        break;
                    case 'tr':
                        $TrStyle = '';
                        $ValidAttribs = array('aqua', 'black', 'blue', 'fuchsia', 'green', 'grey', 'lime', 'maroon', 'navy', 'olive', 'purple', 'red', 'silver', 'teal', 'white', 'yellow');
                        if (isset($Block['Attr']) && (in_array($Block['Attr'], $ValidAttribs) || preg_match('/^#[0-9a-f]{6}$/', $Block['Attr']))) {
                            $TrStyle = ' style="background-color: ' . $Block['Attr'] . ';"';
                        }
                        $Str .= "<tr class=\"Table-row\" $TrStyle>" . self::to_html($Block['Val']) . '</tr>';
                        break;
                    case 'td':
                        $TdStyle = '';
                        $TdAttr = false;
                        if (isset($Block['Attr'])) {
                            if (is_number($Block['Attr']) && $Block['Attr'] > 0 && $Block['Attr'] <= 100) {
                                $TdStyle = ' width="' . $Block['Attr'] . '%"';
                            } else if (preg_match('/^(\d+),(\d+),(\d+)$/', $Block['Attr'], $TdAttr)) {
                                if ($TdAttr[1] = intval($TdAttr[1])) {
                                    $TdStyle .= ' rowspan="' . $TdAttr[1] . '"';
                                }
                                if ($TdAttr[2] = intval($TdAttr[2])) {
                                    $TdStyle .= ' colspan="' . $TdAttr[2] . '"';
                                }
                                if (($TdAttr[3] = intval($TdAttr[3])) && $TdAttr[3] < 100) {
                                    $TdStyle .= ' width="' . $TdAttr[3] . '%"';
                                }
                            }
                        }
                        $Str .= "<td class=\"Table-cell\" $TdStyle>" . self::to_html($Block['Val']) . "</td>";
                        break;
                    case 'lang':
                        if (isset($Block['Attr'])) {
                            if (stripos($Block['Attr'], Lang::getUserLang(G::$LoggedUser['ID'])) !== false) {
                                $Str .= self::to_html($Block['Val']);
                            }
                        } else {
                            $Str .= self::to_html($Block['Val']);
                        }
                        break;

                    case 'bdinfo':
                        $Block['Val'] = str_replace("\r\n", "\n", $Block['Val']);
                        $Block['Val'] = str_replace("\r", "\n", $Block['Val']);
                        $Title = self::getval($Block['Val'], "Disc Title");
                        if (!$Title) {
                            $Title = self::getval($Block['Val'], "Disc Label");
                        }
                        if (!$Title) {
                            $Title = 'BDInfo';
                        }
                        $BDInfo = [
                            "General" => [],
                            "Video" => [],
                            "Audio" => [],
                            "Text" => [],
                        ];
                        $BDInfo["General"][] = [
                            "Container" => "BDAV",
                            "Runtime" => trim(self::getval($Block['Val'], "Length")),
                            "Size" => Format::get_size(str_replace(',', '', trim(self::getval($Block['Val'], "Size"), 'bytes'))),
                        ];
                        $Str .= "<div>";
                        $Str .= "<a data-action='toggle-mediainfo' href='#'>";
                        $Str .= t('server.index.details');
                        $Str .= "</a> | " . $Title . "</div><div class='hidden'><pre class='MediaInfoText' variant='bdinfo'>" . $Block['Val'] . "</pre></div>";
                        $VideoAudioSubTitle = stristr($Block['Val'], "VIDEO:");
                        $VideoText = rtrim(substr($VideoAudioSubTitle, 0, stripos($VideoAudioSubTitle, "AUDIO:")));
                        $TableValues = self::parseBDInfoVideo($VideoText);
                        $VideoInfo = [
                            "Codec" => "",
                            "Resolution" => "",
                            "Bit rate" => "",
                            "Bit depth" => "",
                            "Frame rate" => "",
                            "Aspect ratio" => "",
                            //"BPP" => self::getval($Section, "Bits\/\(Pixel\*Frame\)"),
                        ];
                        if ($TableValues) {
                            foreach ($TableValues as $TableValue) {
                                list($Resolution, $FPS, $AspectRatio,,, $Bits,, $HDRFormat) = explode('/', $TableValue[2]);
                                $VideoInfo["Codec"] = ltrim($TableValue[0], '* ');
                                $VideoInfo["Resolution"] = trim($Resolution);
                                $VideoInfo["Bit rate"] = $TableValue[1];
                                $VideoInfo["Bit depth"] = trim($Bits);
                                $VideoInfo["Frame rate"] = trim($FPS);
                                $VideoInfo["Aspect ratio"] = trim($AspectRatio);
                                $BDInfo["Video"][] = $VideoInfo;
                            }
                        } else {
                            $VideoSummary = self::getallval($Block['Val'], "Video");
                            foreach ($VideoSummary as $VS) {
                                $VideoSummaryArray = explode('/', $VS);
                                $VideoInfo["Codec"] = $VideoSummaryArray[0];
                                $VideoInfo["Resolution"] = $VideoSummaryArray[2];
                                $VideoInfo["Aspect ratio"] = $VideoSummaryArray[4];
                                $VideoInfo["Frame rate"] = $VideoSummaryArray[3];
                                $VideoInfo["Bit rate"] = $VideoSummaryArray[1];
                                $VideoInfo["Bit depth"] = $VideoSummaryArray[6];
                                $BDInfo["Video"][] = $VideoInfo;
                            }
                        }

                        $AudioText = stristr($VideoAudioSubTitle, "AUDIO:");
                        if (stripos($AudioText, "SUBTITLES:")) {
                            $AudioText = rtrim(substr($AudioText, 0, stripos($AudioText, "SUBTITLES:")));
                        }
                        if (stripos($AudioText, "FILES:")) {
                            $AudioText = rtrim(substr($AudioText, 0, stripos($AudioText, "FILES:")));
                        }
                        $TableValues = self::parseBDInfoAudio($AudioText);
                        if ($TableValues) {
                            foreach ($TableValues as $Row) {
                                list($channel,,, $bitDepth) = explode('/', $Row[3]);
                                $AudioInfo = [
                                    "Language" => $Row[1],
                                    "Format" => $Row[0],
                                    "Channels" => $channel,
                                    "Bit rate" => $Row[2],
                                    "Bit depth" => $bitDepth,
                                ];
                                $AudioText = '';
                                if ($AudioInfo['Language']) {
                                    $AudioText .= $AudioInfo['Language'] . " ";
                                }
                                if ($AudioInfo['Channels']) {

                                    $AudioText .= $AudioInfo['Channels'] . " ";
                                }
                                if ($AudioInfo['Format']) {
                                    $AudioText .= $AudioInfo['Format'] . " ";
                                }
                                if ($AudioInfo['Bit rate']) {
                                    $AudioText .= '@ ' . $AudioInfo['Bit rate'];
                                }
                                $BDInfo["Audio"][] = $AudioText;
                            }
                        } else {
                            $AudioSummarys = self::getallval($Block['Val'], "Audio");
                            foreach ($AudioSummarys as $AS) {
                                $AudioSummaryArray = explode('/', $AS);
                                $AudioInfo = [
                                    "Language" => $AudioSummaryArray[0],
                                    //"Bit depth" => self::getval($Section, "Bit depth"),
                                    "Format" => $AudioSummaryArray[1],
                                    "Channels" => $AudioSummaryArray[2],
                                    "Bit rate" => $AudioSummaryArray[4],
                                    //"Aspect ratio" => self::getval($Section, "Display aspect ratio"),
                                    //"BPP" => self::getval($Section, "Bits\/\(Pixel\*Frame\)"),
                                ];
                                $AudioText = '';
                                if ($AudioInfo['Language']) {
                                    $AudioText .= $AudioInfo['Language'] . " ";
                                }
                                if ($AudioInfo['Channels']) {

                                    $AudioText .= $AudioInfo['Channels'] . " ";
                                }
                                if ($AudioInfo['Format']) {
                                    $AudioText .= $AudioInfo['Format'] . " ";
                                }
                                if ($AudioInfo['Bit rate']) {
                                    $AudioText .= '@ ' . $AudioInfo['Bit rate'];
                                }
                                $BDInfo["Audio"][] = $AudioText;
                            }
                        }
                        //$SubTitleText = rtrim(stristr($VideoAudioSubTitle, "SUBTITLES:"));

                        //$Str .= $AudioText;
                        //print_r(self::getColValueInTextTable($AudioText));
                        foreach ($BDInfo as $key => $data) {
                            //$data = self::removeAllFalse($data);
                            if (count($data)) {
                                $BDInfo[$key] = self::genTable($key, $data);
                            } else {
                                $BDInfo[$key] = '';
                            }
                        }
                        $Str .= "<table class='TableMediaInfo' variant='bdinfo'><tr><td>" . $BDInfo['General'] . "</td><td>" . $BDInfo['Video'] . "</td><td>" . $BDInfo['Audio'] /*. $MediaInfo['Text']*/ . "</td></tr></table>";

                        break;
                    case 'mediainfo':
                        $Block['Val'] = str_replace("\r\n", "\n", $Block['Val']);
                        $Block['Val'] = str_replace("\r", "\n", $Block['Val']);
                        //$Debug->log_var($Block['Val']);
                        $Sections = preg_split("/^\s*$/m", $Block['Val']);
                        //$Debug->log_var($Sections);
                        $MediaInfo = [
                            "General" => [],
                            "Video" => [],
                            "Audio" => [],
                            "Text" => [],
                        ];
                        foreach ($Sections as $Section) {
                            $Section = ltrim($Section);
                            switch (substr($Section, 0, 1)) {
                                case "G":
                                    $MediaInfo["General"][] = [
                                        "Complete name" => self::getval($Section, "Complete name"),
                                        "Container" => self::getval($Section, "Format"),
                                        "Runtime" => self::getval($Section, "Duration"),
                                        "Size" => self::getval($Section, "File size"),
                                    ];
                                    break;
                                case "V":
                                    $VideoInfo = [
                                        "Codec" => self::getval($Section, "Format"),
                                        'Resolution' => '',
                                        "Bit rate" => self::getval($Section, "Bit rate"),
                                        "Bit rate (N)" => self::getval($Section, "Nominal bit rate"),
                                        "Bit depth" => self::getval($Section, "Bit depth"),
                                        "Frame rate" => self::getval($Section, "Frame rate"),
                                        "Aspect ratio" => self::getval($Section, "Display aspect ratio"),
                                        "Width" => self::getval($Section, "Width"),
                                        "Height" => self::getval($Section, "Height"),
                                        //"BPP" => self::getval($Section, "Bits\/\(Pixel\*Frame\)"),
                                    ];
                                    if ($VideoInfo['Width'] && $VideoInfo['Height']) {
                                        $VideoInfo['Resolution'] = self::onlyDigit($VideoInfo['Width']) . " × " . self::onlyDigit($VideoInfo['Height']);
                                    }
                                    unset($VideoInfo['Width'], $VideoInfo['Height']);
                                    if ($VideoInfo['Codec'] == "AVC") {
                                        if (!empty(self::getval($Section, "Encoding settings")) || str_contains(self::getval($Section, "Writing library"), 'x264')) {
                                            $VideoInfo['Codec'] = "x264";
                                        } else {
                                            $VideoInfo['Codec'] = "H.264";
                                        }
                                    } else if ($VideoInfo['Codec'] == "HEVC") {
                                        if (!empty(self::getval($Section, "Encoding settings")) || str_contains(self::getval($Section, "Writing library"), 'x265')) {
                                            $VideoInfo['Codec'] = "x265";
                                        } else {
                                            $VideoInfo['Codec'] = "H.265";
                                        }
                                    } else {
                                        $VideoInfo['Codec'] = $VideoInfo['Codec'];
                                    }
                                    $MediaInfo["Video"][] = $VideoInfo;
                                    break;
                                case "A":
                                    $AudioInfo = [
                                        "Language" => self::getval($Section, "Language"),
                                        "Format" => self::getval($Section, "Format"),
                                        "Channels" => self::getval($Section, "Channel\(s\)"),
                                        "Bit rate" => self::getval($Section, "Bit rate"),
                                        "Title" => self::getval($Section, "Title"),
                                        "Bit depth" => self::getval($Section, "Bit depth"),
                                    ];
                                    $AudioText = '';
                                    if ($AudioInfo['Language']) {
                                        $AudioText .= $AudioInfo['Language'] . " ";
                                    }
                                    switch (self::onlyDigit($AudioInfo['Channels'])) {
                                        case "2":
                                            $AudioText .= "2.0ch ";
                                            break;
                                        case "3":
                                            $AudioText .= "2.1ch ";
                                            break;
                                        case "6":
                                            $AudioText .= "5.1ch ";
                                            break;
                                        case "8":
                                            $AudioText .= "7.1ch ";
                                            break;
                                        default:
                                            $AudioText .= $AudioInfo['Channels'] . " ";
                                            break;
                                    }
                                    if ($AudioInfo['Format']) {
                                        $AudioText .= $AudioInfo['Format'] . " ";
                                    }
                                    if ($AudioInfo['Bit rate']) {
                                        $AudioText .= '@ ' . $AudioInfo['Bit rate'];
                                    }
                                    if ($AudioInfo['Title']) {
                                        $AudioText .= ' (' . $AudioInfo['Title'] . ')';
                                    }
                                    $MediaInfo["Audio"][] = $AudioText;
                                    break;
                                case "T":
                                    $MediaInfo["Text"][] = self::getval($Section, "Title");
                                    break;
                            }
                        }
                        $Str .= "<div><a data-action='toggle-mediainfo' href='#'>";
                        $Str .= t('server.index.details') . "</a> | ";
                        if (isset($MediaInfo['General'][0]) && $MediaInfo['General'][0]['Complete name']) {
                            $slash = strrpos($MediaInfo['General'][0]['Complete name'], '\\');
                            if (!$slash) {
                                $slash = strrpos($MediaInfo['General'][0]['Complete name'], '/');
                            }
                            $CM = ltrim(substr($MediaInfo['General'][0]['Complete name'], $slash), '\\');
                            $CM = ltrim($CM, '/');
                            $Str .= $CM;
                            $Block['Val'] = str_replace($MediaInfo['General'][0]['Complete name'], $CM, $Block['Val']);
                            unset($MediaInfo['General'][0]['Complete name']);
                        } else {
                            $Str .= "mediainfo";
                        }
                        $Str .= "</div><div class='hidden'><pre class='MediaInfoText' variant='mediainfo'>" . $Block['Val'] . "</pre></div>";
                        //$Debug->log_var($MediaInfo);
                        foreach ($MediaInfo as $key => $data) {
                            $data = self::removeAllFalse($data);
                            if (count($data)) {
                                $MediaInfo[$key] = self::genTable($key, $data);
                            } else {
                                $MediaInfo[$key] = '';
                            }
                        }
                        $Str .= "<table class='TableMediaInfo' variant='mediainfo'><tr><td>" . $MediaInfo['General'] . "</td><td>" . $MediaInfo['Video'] . "</td><td>" . $MediaInfo['Audio'] /*. $MediaInfo['Text']*/ . "</td></tr></table>";
                        $Str .= "";
                        break;
                    case 'comparison':
                        $Block['Val'] = str_replace("\r\n", "\n", $Block['Val']);
                        $Block['Val'] = str_replace("\r", "\n", $Block['Val']);
                        $Images = [];
                        foreach ($Block['Val'] as $value) {
                            if (is_array($value) && !empty($value['Attr'])) {
                                $Images[] = "'" . $value['Attr'] . "'";
                            } else if (is_array($value) && !empty($value['Val'])) {
                                $Images[] = "'" . $value['Val'] . "'";
                            }
                        }
                        if (count($Images) < 2) {
                            break;
                        }
                        $ImageStr = '[' . implode(',', $Images) . ']';
                        $Attrs = explode(',', $Block['Attr']);
                        $AttrArray = [];
                        foreach ($Attrs as $Attr) {
                            $AttrArray[] = "'" . $Attr . "'";
                        }
                        $AttrStr = '[' . implode(',', $AttrArray) . ']';
                        $Str .= '<div class="comparison"><span class="title"><b>' . $Block['Attr'] . ': </b></span><a href="#" onclick="screenshotCompare(' . $AttrStr . ',' . $ImageStr . ' ); return false;">Show comparison</a></div>';
                        break;
                }
            }
        }
        self::$Levels--;
        return $Str;
    }

    private static function raw_text($Array) {
        $Str = '';
        foreach ($Array as $Block) {
            if (is_string($Block)) {
                $Str .= $Block;
                continue;
            }
            switch ($Block['Type']) {
                case 'headline':
                    break;
                case 'b':
                case 'u':
                case 'i':
                case 's':
                case 'color':
                case 'size':
                case 'quote':
                case 'align':

                    $Str .= self::raw_text($Block['Val']);
                    break;
                case 'tex': //since this will never strip cleanly, just remove it
                    break;
                case 'artist':
                case 'user':
                case 'wiki':
                case 'pre':
                case 'code':
                case 'aud':
                case 'img':
                    $Str .= $Block['Val'];
                    break;
                case 'list':
                    foreach ($Block['Val'] as $Line) {
                        $Str .= $Block['Tag'] . self::raw_text($Line);
                    }
                    break;

                case 'url':
                    // Make sure the URL has a label
                    if (empty($Block['Val'])) {
                        $Block['Val'] = $Block['Attr'];
                    } else {
                        $Block['Val'] = self::raw_text($Block['Val']);
                    }

                    $Str .= $Block['Val'];
                    break;

                case 'inlineurl':
                    if (!self::valid_url($Block['Attr'], '', true)) {
                        $Array = self::parse($Block['Attr']);
                        $Block['Attr'] = $Array;
                        $Str .= self::raw_text($Block['Attr']);
                    } else {
                        $Str .= $Block['Attr'];
                    }

                    break;
            }
        }
        return $Str;
    }

    private static function smileys($Str) {
        if (!empty(G::$LoggedUser['DisableSmileys'])) {
            return $Str;
        }
        if (count(self::$ProcessedSmileys) == 0 && count(self::$Smileys) > 0) {
            foreach (self::$Smileys as $Key => $Val) {
                if (str_ends_with($Val, '.gif')) {
                    self::$ProcessedSmileys[$Key] = '<img border="0" src="' . CONFIG['STATIC_SERVER'] . 'common/smileys/' . $Val . '" alt="" />';
                } else {
                    self::$ProcessedSmileys[$Key] = $Val;
                }
            }
            reset(self::$ProcessedSmileys);
        }
        $Str = strtr($Str, self::$ProcessedSmileys);
        return $Str;
    }

    /**
     * Given a String that is composed of HTML, attempt to convert it back
     * into BBCode. Useful when we're trying to deal with the output from
     * some other site's metadata
     *
     * @param String $Html
     * @return String
     */
    public static function parse_html($Html) {
        $Document = new DOMDocument();
        $Document->loadHtml(stripslashes($Html));

        // For any manipulation that we do on the DOM tree, always go in reverse order or
        // else you end up with broken array pointers and missed elements
        $CopyNode = function ($OriginalElement, $NewElement) {
            for ($i = count($OriginalElement->childNodes) - 1; $i >= 0; $i--) {
                if (count($NewElement->childNodes) > 0) {
                    $NewElement->insertBefore($OriginalElement->childNodes[$i], $NewElement->childNodes[0]);
                } else {
                    $NewElement->appendChild($OriginalElement->childNodes[$i]);
                }
            }
        };

        $Elements = $Document->getElementsByTagName('div');
        for ($i = $Elements->length - 1; $i >= 0; $i--) {
            $Element = $Elements->item($i);
            if (strpos($Element->getAttribute('style'), 'text-align') !== false) {
                $NewElement = $Document->createElement('align');
                $CopyNode($Element, $NewElement);
                $NewElement->setAttribute('align', str_replace('text-align: ', '', $Element->getAttribute('style')));
                $Element->parentNode->replaceChild($NewElement, $Element);
            }
        }

        $Elements = $Document->getElementsByTagName('span');
        for ($i = $Elements->length - 1; $i >= 0; $i--) {
            $Element = $Elements->item($i);
            if (strpos($Element->getAttribute('class'), 'size') !== false) {
                $NewElement = $Document->createElement('size');
                $CopyNode($Element, $NewElement);
                $NewElement->setAttribute('size', str_replace('size', '', $Element->getAttribute('class')));
                $Element->parentNode->replaceChild($NewElement, $Element);
            } elseif (strpos($Element->getAttribute('style'), 'font-style: italic') !== false) {
                $NewElement = $Document->createElement('italic');
                $CopyNode($Element, $NewElement);
                $Element->parentNode->replaceChild($NewElement, $Element);
            } elseif (strpos($Element->getAttribute('style'), 'text-decoration: underline') !== false) {
                $NewElement = $Document->createElement('underline');
                $CopyNode($Element, $NewElement);
                $Element->parentNode->replaceChild($NewElement, $Element);
            } elseif (strpos($Element->getAttribute('style'), 'color: ') !== false) {
                $NewElement = $Document->createElement('color');
                $CopyNode($Element, $NewElement);
                $NewElement->setAttribute('color', str_replace(['color: ', ';'], '', $Element->getAttribute('style')));
                $Element->parentNode->replaceChild($NewElement, $Element);
            }
        }

        $Elements = $Document->getElementsByTagName('ul');
        for ($i = 0; $i < $Elements->length; $i++) {
            $InnerElements = $Elements->item($i)->getElementsByTagName('li');
            for ($j = $InnerElements->length - 1; $j >= 0; $j--) {
                $Element = $InnerElements->item($j);
                $NewElement = $Document->createElement('bullet');
                $CopyNode($Element, $NewElement);
                $Element->parentNode->replaceChild($NewElement, $Element);
            }
        }

        $Elements = $Document->getElementsByTagName('ol');
        for ($i = 0; $i < $Elements->length; $i++) {
            $InnerElements = $Elements->item($i)->getElementsByTagName('li');
            for ($j = $InnerElements->length - 1; $j >= 0; $j--) {
                $Element = $InnerElements->item($j);
                $NewElement = $Document->createElement('number');
                $CopyNode($Element, $NewElement);
                $Element->parentNode->replaceChild($NewElement, $Element);
            }
        }

        $Elements = $Document->getElementsByTagName('strong');
        for ($i = $Elements->length - 1; $i >= 0; $i--) {
            $Element = $Elements->item($i);
            if ($Element->hasAttribute('class') === 'u-colorWarning') {
                $NewElement = $Document->createElement('important');
                $CopyNode($Element, $NewElement);
                $Element->parentNode->replaceChild($NewElement, $Element);
            }
        }

        $Elements = $Document->getElementsByTagName('a');
        for ($i = $Elements->length - 1; $i >= 0; $i--) {
            $Element = $Elements->item($i);
            if ($Element->hasAttribute('href')) {
                $Element->removeAttribute('rel');
                $Element->removeAttribute('target');
                if ($Element->getAttribute('href') === $Element->nodeValue) {
                    $Element->removeAttribute('href');
                } elseif (
                    $Element->getAttribute('href') === 'javascript:void(0);'
                    && $Element->getAttribute('onclick') === 'BBCode.spoiler(this);'
                ) {
                    $Spoilers = $Document->getElementsByTagName('blockquote');
                    for ($j = $Spoilers->length - 1; $j >= 0; $j--) {
                        $Spoiler = $Spoilers->item($j);
                        if ($Spoiler->hasAttribute('class') && $Spoiler->getAttribute('class') === 'hidden spoiler') {
                            $NewElement = $Document->createElement('spoiler');
                            $CopyNode($Spoiler, $NewElement);
                            $Element->parentNode->replaceChild($NewElement, $Element);
                            $Spoiler->parentNode->removeChild($Spoiler);
                            break;
                        }
                    }
                } elseif (substr($Element->getAttribute('href'), 0, 22) === 'artist.php?artistname=') {
                    $NewElement = $Document->createElement('artist');
                    $CopyNode($Element, $NewElement);
                    $Element->parentNode->replaceChild($NewElement, $Element);
                } elseif (substr($Element->getAttribute('href'), 0, 30) === 'user.php?action=search&search=') {
                    $NewElement = $Document->createElement('user');
                    $CopyNode($Element, $NewElement);
                    $Element->parentNode->replaceChild($NewElement, $Element);
                }
            }
        }

        $Str = str_replace(["<body>\n", "\n</body>", "<body>", "</body>"], "", $Document->saveHTML($Document->getElementsByTagName('body')->item(0)));
        $Str = str_replace(["\r\n", "\n"], "", $Str);
        $Str = preg_replace("/\<strong\>([a-zA-Z0-9 ]+)\<\/strong\>\: \<spoiler\>/", "[spoiler=\\1]", $Str);
        $Str = str_replace("</spoiler>", "[/spoiler]", $Str);
        $Str = preg_replace("/\<strong class=\"quoteheader\"\>(.*)\<\/strong\>(.*)wrote\:(.*)\<blockquote\>/", "[quote=\\1]", $Str);
        $Str = preg_replace("/\<(\/*)blockquote\>/", "[\\1quote]", $Str);
        $Str = preg_replace("/\<(\/*)strong\>/", "[\\1b]", $Str);
        $Str = preg_replace("/\<(\/*)italic\>/", "[\\1i]", $Str);
        $Str = preg_replace("/\<(\/*)underline\>/", "[\\1u]", $Str);
        $Str = preg_replace("/\<(\/*)important\>/", "[\\1important]", $Str);
        $Str = preg_replace("/\<(\/*)code\>/", "[\\1code]", $Str);
        $Str = preg_replace("/\<(\/*)pre\>/", "[\\1pre]", $Str);
        $Str = preg_replace("/\<color color=\"(.*)\"\>/", "[color=\\1]", $Str);
        $Str = str_replace("</color>", "[/color]", $Str);
        $Str = str_replace(['<number>', '<bullet>'], ['[#]', '[*]'], $Str);
        $Str = str_replace(['</number>', '</bullet>'], '<br />', $Str);
        $Str = str_replace(['<ul class="postlist">', '<ol class="postlist">', '</ul>', '</ol>'], '', $Str);
        $Str = preg_replace("/\<align align=\"([a-z]+);\">/", "[align=\\1]", $Str);
        $Str = str_replace("</align>", "[/align]", $Str);
        $Str = preg_replace("/\<size size=\"([0-9]+)\"\>/", "[size=\\1]", $Str);
        $Str = str_replace("</size>", "[/size]", $Str);
        //$Str = preg_replace("/\<a href=\"rules.php\?(.*)#(.*)\"\>(.*)\<\/a\>/", "[rule]\\3[/rule]", $Str);
        //$Str = preg_replace("/\<a href=\"wiki.php\?action=article&name=(.*)\"\>(.*)\<\/a>/", "[[\\1]]", $Str);
        $Str = preg_replace('#/torrents.php\?taglist="?(?:[^"]*)#', CONFIG['SITE_URL'] . '\\0', $Str);
        $Str = preg_replace("/\<(\/*)artist\>/", "[\\1artist]", $Str);
        $Str = preg_replace("/\((\/*)user\>/", "[\\1user]", $Str);
        $Str = preg_replace("/\<a href=\"([^\"]*)\">/", "[url=\\1]", $Str);
        $Str = preg_replace("/\<(\/*)a\>/", "[\\1url]", $Str);
        $Str = preg_replace("/\<img(.*)src=\"(.*)\"(.*)\>/", '[img]\\2[/img]', $Str);
        $Str = str_replace('<p>', '', $Str);
        $Str = str_replace('</p>', '<br />', $Str);
        return str_replace(["<br />", "<br>"], "\n", $Str);
    }
}
