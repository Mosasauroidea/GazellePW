<?

namespace Gazelle\Torrent;

use Lang;

class Language {
    private const Item = [
        'english',
        'japanese',
        'korean',
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
        'vietnamese',
        'mandarin',
        'cantonese',
        'min_nan',
        'japanese_sign_language',
        'chinese',
        'catalan',
    ];

    public static function allItem() {
        return self::Item;
    }

    public static function text($Item) {
        return t('server.upload.' .  str_replace(' ', '_', trim(strtolower($Item))),  ['DefaultValue' => $Item]);
    }

    public static function sphinxKey($Text) {
        $key = Lang::get_key('server.upload', $Text);
        if (!empty($key)) {
            return Lang::getWithLang($key, Lang::EN);
        }
        return 'invalid';
    }
}
