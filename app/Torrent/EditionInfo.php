<?

namespace Gazelle\Torrent;

use Lang;

class EditionType {
    const Edition = 1;
    const Feature = 2;
    const Collection = 3;
    const Remaster = 4;
    const ThreeD = 5;
}

class EditionInfo {
    const collections = ['masters_of_cinema', 'the_criterion_collection', 'warner_archive_collection'];
    const editions = ['director_s_cut', 'extended_edition', 'rifftrax', 'theatrical_cut', 'uncut', 'unrated'];
    const features = ['2_disc_set', '2_in_1', '2d_3d_edition', '3d_anaglyph', '3d_full_sbs', '3d_half_ou', '3d_half_sbs', '4k_restoration', '4k_remaster', 'remaster', '10_bit', 'dts_x', 'dolby_atmos', 'dolby_vision', 'dual_audio', 'english_dub', 'extras', 'hdr10', 'hdr10plus', 'with_commentary'];
    const remasters = ['remaster', '4k_remaster', '4k_restoration', 'warner_archive_collection', 'masters_of_cinema', 'the_criterion_collection'];
    const threeD = ['3d_anaglyph', '3d_full_sbs', '3d_half_ou', '3d_half_sbs'];

    public static function allEditionKey($type = null): ?array {
        switch ($type) {
            case EditionType::Collection:
                return self::collections;
            case EditionType::Edition:
                return self::editions;
            case EditionType::Feature:
                return self::features;
            case EditionType::Remaster:
                return self::remasters;
            case EditionType::ThreeD:
                return self::threeD;
        }
        return array_merge(self::collections, self::editions, self::features);
    }

    public static function text(string $key): ?string {
        return t("server.editioninfo.$key");
    }

    public static function icon(string $key): ?string {
        return icon("Torrent/$key", "", ['ReturnEmptyString' => true]) ?: self::text($key);
    }

    public static function key(string $text): ?string {
        $key = Lang::get_key('server.editioninfo', $text);
        if (!empty($key)) {
            return $key;
        }
        return "";
    }

    public static function validate($Value) {
        if (!$Value) {
            return true;
        }
        $RemasterTitles = explode(' / ', $Value);
        $AllTitles = EditionInfo::allEditionKey();
        foreach ($RemasterTitles as $Title) {
            if (!in_array($Title, $AllTitles)) {
                return false;
            }
        }
        return true;
    }
}
