<?

namespace Gazelle\Torrent;

use Lang;

class EditionType {
    const Edition = 1;
    const Feature = 2;
    const Collection = 3;
    const Remaster = 4;
    const ThreeD = 5;
    const AdvanceVideoFeature = 6;
    const AdvanceAudioFeature = 7;
}

class EditionInfo {

    const edition_10_bit = '10_bit';
    const edition_hdr10plus = 'hdr10plus';
    const edition_dolby_vision = 'dolby_vision';
    const edition_hdr10 = 'hdr10';
    const edition_dolby_atmos = 'dolby_atmos';
    const edition_dts_x = 'dts_x';

    const collections = ['masters_of_cinema', 'the_criterion_collection', 'warner_archive_collection'];
    const editions = ['director_s_cut', 'extended_edition', 'rifftrax', 'theatrical_cut', 'uncut', 'unrated'];
    const features = ['2d_3d_edition', '3d_anaglyph', '3d_full_sbs', '3d_half_ou', '3d_half_sbs', '2_disc_set', '2_in_1', '4k_restoration', '4k_remaster', 'remaster',  'dual_audio', 'english_dub', 'extras',  'with_commentary'];
    const remasters = ['remaster', '4k_remaster', '4k_restoration', 'warner_archive_collection', 'masters_of_cinema', 'the_criterion_collection'];
    const threeD = ['3d_anaglyph', '3d_full_sbs', '3d_half_ou', '3d_half_sbs'];
    const advance_video_feature = [self::edition_10_bit, self::edition_hdr10, self::edition_hdr10plus, self::edition_dolby_vision,];
    const advance_audio_feature = [self::edition_dolby_atmos, self::edition_dts_x];

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
            case EditionType::AdvanceAudioFeature:
                return self::advance_video_feature;
            case EditionInfo::advance_audio_feature:
                return self::advance_audio_feature;
        }
        return array_merge(self::collections, self::editions, self::features, self::advance_audio_feature, self::advance_video_feature);
    }

    public static function filter_basic_remaster_title($RemasterTitle): ?string {
        $Editions = !empty($RemasterTitle) ? explode(' / ', $RemasterTitle) : [];
        return implode(
            ' / ',
            array_filter(
                $Editions,
                fn ($m) => array_search($m, array_merge(self::collections, self::editions, self::features)) !== false
            )
        );
    }

    public static function checkEditionInfo($RemasterTitle, $Edition): ?bool {
        $Editions = !empty($RemasterTitle) ? explode(' / ', $RemasterTitle) : [];
        return array_search($Edition, $Editions) !== false;
    }

    public static function mergeAdvanceFeature($RemasterTitle, $Params): ?string {
        $Editions = !empty($RemasterTitle) ? explode(' / ', $RemasterTitle) : [];

        if (isset($Params[self::edition_dts_x])) {
            $Editions[] = self::edition_dts_x;
        } else {
            $Editions = array_filter($Editions, fn ($m) => $m != self::edition_dts_x);
        }

        if (isset($Params[self::edition_dolby_atmos])) {
            $Editions[] = self::edition_dolby_atmos;
        } else {
            $Editions = array_filter($Editions, fn ($m) => $m != self::edition_dolby_atmos);
        }

        if (isset($Params[self::edition_10_bit])) {
            $Editions[] = self::edition_10_bit;
        } else {
            $Editions = array_filter($Editions, fn ($m) => $m != self::edition_10_bit);
        }

        if (isset($Params[self::edition_hdr10])) {
            $Editions[] = self::edition_hdr10;
        } else {
            $Editions = array_filter($Editions, fn ($m) => $m != self::edition_hdr10);
        }

        if (isset($Params[self::edition_hdr10plus])) {
            $Editions[] = self::edition_hdr10plus;
        } else {
            $Editions = array_filter($Editions, fn ($m) => $m != self::edition_hdr10plus);
        }

        if (isset($Params[self::edition_dolby_vision])) {
            $Editions[] = self::edition_dolby_vision;
        } else {
            $Editions = array_filter($Editions, fn ($m) => $m != self::edition_dolby_vision);
        }
        $Editions = array_unique($Editions);
        return implode(' / ', $Editions);
    }

    public static function text(string $key, $Lang = null): ?string {
        return t("server.editioninfo.$key", ['Lang' => $Lang]);
    }

    public static function icon(string $key): ?string {
        return icon("Torrent/$key", "", ['ReturnEmptyString' => true]) ?: self::text($key);
    }

    public static function key(string $text): ?string {
        $key = Lang::get_key('server.editioninfo', $text);
        if (!empty($key)) {
            return str_ireplace('server.editioninfo.', '', $key);
        }
        return "invalid";
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
