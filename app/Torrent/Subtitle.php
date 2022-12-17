<?

namespace Gazelle\Torrent;

use Lang;

class Subtitle {
    const MainItem = 1;
    const ExtraItem = 2;
    const AllItem = 3;
    private const Main = [
        'chinese_simplified',
        'chinese_traditional',
        'english',
        'japanese',
        'korean'
    ];
    public const NoSubtitleitem = 'no_subtitles';
    private const Extra = [
        'arabic',
        'brazilian_port',
        'bulgarian',
        'croatian',
        'czech',
        'danish',
        'dutch',
        'estonian',
        'finnish',
        'french',
        'german',
        'greek',
        'hebrew',
        'hindi',
        'hungarian',
        'icelandic',
        'indonesian',
        'italian',
        'latvian',
        'lithuanian',
        'norwegian',
        'persian',
        'polish',
        'portuguese',
        'romanian',
        'russian',
        'serbian',
        'slovak',
        'slovenian',
        'spanish',
        'swedish',
        'thai',
        'turkish',
        'ukrainian',
        'vietnamese'
    ];

    public static function allItem($Type = null) {
        switch ($Type) {
            case self::MainItem:
                return self::Main;
            case self::ExtraItem:
                return self::Extra;
            case self::AllItem:
            default:
                return array_merge(self::Main, self::Extra);
        }
    }

    public static function text($Item) {
        return t("server.upload.$Item");
    }

    public static function icon($Item) {
        return  icon("flag/$Item");
    }

    public static function sphinxKey($Text) {
        $key = Lang::get_key('server.upload', $Text);
        if (!empty($key)) {
            return str_ireplace('server.upload.', '', $key);
        }
        return 'invalid';
    }
}
