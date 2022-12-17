<?php

namespace Gazelle\Torrent;

use Torrents;
use Lang;

class TorrentSlotGroupStatus {
    const Full = 1; // 满
    const Free = 2; // 有空闲
    const Empty = 3; // 空
}

class TorrentSlotResolution {
    const None = 0;
    const SD = 1;
    const HD720P = 2;
    const HD1080P = 3;
    const UHD = 4;
}

class TorrentSlotType {
    const None = 0;
    const Quality = 1;
    const NTSCUntouched = 2;
    const PALUntouched = 3;
    const Retention = 4;
    const Feature = 5;
    const ChineseQuality = 6;
    const EnglishQuality = 7;
    const X265ChineseQuality = 8;
    const X265EnglishQuality = 9;
    const Remux = 10;
    const DIY = 11;
    const Untouched = 12;
}

class TorrentSlotGroup {
    const SDEncode = 1;
    const SDUntouched = 2;
    const HDEncode = 3;
    const HDUntouched = 4;
    const UHDEncode = 5;
    const UHDUntouched = 6;
}

class TorrentSlot {
    const SDEncodeSlots = [
        TorrentSlotType::Quality,
        TorrentSlotType::ChineseQuality,
    ];
    const SDUntouchedSlots = [
        TorrentSlotType::NTSCUntouched,
        TorrentSlotType::PALUntouched,
    ];
    const HD720PEncodeSlots = [
        TorrentSlotType::Retention,
        TorrentSlotType::Feature,
        TorrentSlotType::ChineseQuality,
        TorrentSlotType::EnglishQuality,
    ];
    const HD1080PEncodeSlots = [
        TorrentSlotType::Retention,
        TorrentSlotType::Feature,
        TorrentSlotType::ChineseQuality,
        TorrentSlotType::EnglishQuality,
        TorrentSlotType::X265ChineseQuality,
        TorrentSlotType::X265EnglishQuality,
    ];
    const HD720PUntouchedSlots = [
        TorrentSlotType::Remux,
        TorrentSlotType::Untouched,
    ];

    const HD1080PUntouchedSlots = [
        TorrentSlotType::Remux,
        TorrentSlotType::Untouched,
        TorrentSlotType::DIY,
    ];
    const UHDEncodeSlots = [
        TorrentSlotType::Retention,
        TorrentSlotType::Feature,
        TorrentSlotType::ChineseQuality,
        TorrentSlotType::EnglishQuality,
    ];
    const UHDUntouchedSlots = [
        TorrentSlotType::Remux,
        TorrentSlotType::DIY,
        TorrentSlotType::Untouched,
    ];



    const SDSlots = [
        TorrentSlotType::None,
        TorrentSlotType::Quality,
        TorrentSlotType::ChineseQuality,
        TorrentSlotType::NTSCUntouched,
        TorrentSlotType::PALUntouched,
    ];
    const HD720PSlots = [
        TorrentSlotType::None,
        TorrentSlotType::Retention,
        TorrentSlotType::Feature,
        TorrentSlotType::ChineseQuality,
        TorrentSlotType::EnglishQuality,
        TorrentSlotType::Remux,
        TorrentSlotType::Untouched,
    ];
    const HD1080PSlots = [
        TorrentSlotType::None,
        TorrentSlotType::Retention,
        TorrentSlotType::Feature,
        TorrentSlotType::ChineseQuality,
        TorrentSlotType::EnglishQuality,
        TorrentSlotType::X265ChineseQuality,
        TorrentSlotType::X265EnglishQuality,
        TorrentSlotType::Remux,
        TorrentSlotType::DIY,
        TorrentSlotType::Untouched,
    ];
    const UHDSlots = [
        TorrentSlotType::None,
        TorrentSlotType::Retention,
        TorrentSlotType::Feature,
        TorrentSlotType::ChineseQuality,
        TorrentSlotType::EnglishQuality,
        TorrentSlotType::Remux,
        TorrentSlotType::DIY,
        TorrentSlotType::Untouched,
    ];

    const Slots = [
        TorrentSlotResolution::SD => self::SDSlots,
        TorrentSlotResolution::HD720P => self::HD720PSlots,
        TorrentSlotResolution::HD1080P => self::HD1080PSlots,
        TorrentSlotResolution::UHD => self::UHDSlots,
    ];

    const MaxSlotCount = [TorrentSlotType::Quality => 2];

    public static function get_slot_resolution($Resolution) {
        global $StandardDefinition, $UltraDefinition;
        if (in_array($Resolution, $StandardDefinition)) {
            return TorrentSlotResolution::SD;
        } else if ($Resolution == '720p') {
            return TorrentSlotResolution::HD720P;
        } else if (in_array($Resolution, ['1080i', '1080p'])) {
            return TorrentSlotResolution::HD1080P;
        } else if (in_array($Resolution, $UltraDefinition)) {
            return TorrentSlotResolution::UHD;
        } else if (empty($Resolution)) {
            return TorrentSlotResolution::None;
        }
        return TorrentSlotResolution::SD;
    }

    public static function get_resolution_slots($Resolution) {
        global $StandardDefinition, $UltraDefinition;
        if (in_array($Resolution, $StandardDefinition)) {
            return self::SDSlots;
        } else if ($Resolution == '720p') {
            return self::HD720PSlots;
        } else if (in_array($Resolution, ['1080i', '1080p'])) {
            return self::HD1080PSlots;
        } else if (in_array($Resolution, $UltraDefinition)) {
            return self::UHDSlots;
        }
        return self::SDSlots;
    }
    public static function get_slot_group_status($Torrents) {
        $SDSlotTorrents = [];
        $HD720PSlotTorrents = [];
        $HD1080PSlotTorrents = [];
        $UHDSlotTorrents = [];
        foreach ($Torrents as $Torrent) {
            $Resolution = self::get_slot_resolution($Torrent['Resolution']);
            if ($Resolution == TorrentSlotResolution::SD) {
                if (isset($SDSlotTorrents[$Torrent['Slot']])) {
                    $SDSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $SDSlotTorrents[$Torrent['Slot']] = 1;
                }
            } else if (in_array($Resolution, [TorrentSlotResolution::HD720P])) {
                if (isset($HD720PSlotTorrents[$Torrent['Slot']])) {
                    $HD720PSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $HD720PSlotTorrents[$Torrent['Slot']] = 1;
                }
            } else if (in_array($Resolution, [TorrentSlotResolution::HD1080P])) {
                if (isset($HD1080PSlotTorrents[$Torrent['Slot']])) {
                    $HD1080PSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $HD1080PSlotTorrents[$Torrent['Slot']] = 1;
                }
            } else if ($Resolution == TorrentSlotResolution::UHD) {
                if (isset($UHDSlotTorrents[$Torrent['Slot']])) {
                    $UHDSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $UHDSlotTorrents[$Torrent['Slot']] = 1;
                }
            }
        }
        list($HD720PEncodeStatus, $HD720PEncodeMissSlots) = self::check_slot_status($HD720PSlotTorrents, self::HD720PEncodeSlots);
        list($HD1080PEncodeStatus, $HD10800PEncodeMissSlots) = self::check_slot_status($HD1080PSlotTorrents, self::HD1080PEncodeSlots);
        $HDEncodeStatus = TorrentSlotGroupStatus::Free;
        if ($HD720PEncodeStatus == TorrentSlotGroupStatus::Full && $HD1080PEncodeStatus == TorrentSlotGroupStatus::Full) {
            $HDEncodeStatus = TorrentSlotGroupStatus::Full;
        }
        if ($HD720PEncodeStatus == TorrentSlotGroupStatus::Empty && $HD1080PEncodeStatus == TorrentSlotGroupStatus::Empty) {
            $HDEncodeStatus = TorrentSlotGroupStatus::Empty;
        }
        $HDEncodeMissSlots = $HD720PEncodeMissSlots;
        foreach ($HD10800PEncodeMissSlots as $MissSlot) {
            if (!in_array($MissSlot, $HDEncodeMissSlots)) {
                $HDEncodeMissSlots[] = $MissSlot;
            }
        }

        list($HD720PUntouchedStatus, $HD720PUntouchedMissSlots) = self::check_slot_status($HD720PSlotTorrents, self::HD720PUntouchedSlots);
        list($HD1080PUntouchedStatus, $HD1080PUntouchedMissSlots) = self::check_slot_status($HD1080PSlotTorrents, self::HD1080PUntouchedSlots);
        $HDUntouchedStatus = TorrentSlotGroupStatus::Free;
        if ($HD720PUntouchedStatus == TorrentSlotGroupStatus::Full && $HD1080PUntouchedStatus == TorrentSlotGroupStatus::Full) {
            $HDUntouchedStatus = TorrentSlotGroupStatus::Full;
        }
        if ($HD720PEncodeStatus == TorrentSlotGroupStatus::Empty && $HD1080PUntouchedStatus == TorrentSlotGroupStatus::Empty) {
            $HDUntouchedStatus = TorrentSlotGroupStatus::Empty;
        }
        $HDUntouchedMissSlots = $HD720PUntouchedMissSlots;
        foreach ($HD1080PUntouchedMissSlots as $MissSlot) {
            if (!in_array($MissSlot, $HDEncodeMissSlots)) {
                $HDuntouchedMissSlots[] = $MissSlot;
            }
        }

        return [
            TorrentSlotGroup::SDEncode => self::check_slot_status($SDSlotTorrents, self::SDEncodeSlots),
            TorrentSlotGroup::SDUntouched => self::check_slot_status($SDSlotTorrents, self::SDUntouchedSlots),
            TorrentSlotGroup::HDEncode => [$HDEncodeStatus, $HDEncodeMissSlots],
            TorrentSlotGroup::HDUntouched => [$HDUntouchedStatus, $HDUntouchedMissSlots],
            TorrentSlotGroup::UHDEncode => self::check_slot_status($UHDSlotTorrents, self::UHDEncodeSlots),
            TorrentSlotGroup::UHDUntouched => self::check_slot_status($UHDSlotTorrents, self::UHDUntouchedSlots),
        ];
    }
    private static function check_slot_status($SlotTorrents, $SlotGroup) {
        $allempty = true;
        $allfull = true;
        $MissSlots = [];
        foreach ($SlotGroup as $Slot) {
            $free = false;
            $count = isset($SlotTorrents[$Slot]) ? $SlotTorrents[$Slot] : 0;
            if ($count > 0) {
                $allempty = false;
            }
            if ((isset(self::MaxSlotCount[$Slot]) && $count < self::MaxSlotCount[$Slot]) || $count < 1) {
                $free = true;
                $allfull = false;
            }
            if ($free) {
                $MissSlots[] = $Slot;
            }
        }
        if ($allempty) {
            return [TorrentSlotGroupStatus::Empty, $MissSlots];
        }
        if (!$allfull) {
            return [TorrentSlotGroupStatus::Free, $MissSlots];
        }
        return [TorrentSlotGroupStatus::Full, $MissSlots];
    }

    public static function convert_slot_torrents($Torrents) {
        $SDTorrents = [];
        $HD720PTorrents = [];
        $HD1080PTorrents = [];
        $UHDTorrents = [];
        foreach ($Torrents as $Torrent) {
            $RemasterTitle = $Torrent['RemasterTitle'];
            $RemasterCustomTitle = $Torrent['RemasterCustomTitle'];
            $Resolution = $Torrent['Resolution'];
            $NotMainMovie = $Torrent['NotMainMovie'];
            $Slot = $Torrent['Slot'];
            $IsExtraSlot = $Torrent['IsExtraSlot'];
            if ($IsExtraSlot) {
                $Slot = $Slot . '*';
            }

            if (in_array(Torrents::get_edition($Resolution, $RemasterTitle, $RemasterCustomTitle, $NotMainMovie), ['extra_definition', '3d'])) {
                continue;
            }
            switch (self::get_slot_resolution($Torrent['Resolution'])) {
                case TorrentSlotResolution::SD:
                    $SDTorrents[$Slot][] = $Torrent;
                    break;
                case TorrentSlotResolution::HD720P:
                    $HD720PTorrents[$Slot][] = $Torrent;
                    break;
                case TorrentSlotResolution::HD1080P:
                    $HD1080PTorrents[$Slot][] = $Torrent;
                    break;
                case TorrentSlotResolution::UHD:
                    $UHDTorrents[$Slot][] = $Torrent;
                    break;
            }
        }
        $Ret = [];
        $Missing = [];
        // 720P的任意质量槽如果存在，那么和SD的质量槽就会冲突Dupe
        $Has720PQualitySlot = false;

        $HD720TS = self::filter_slot_torrent(self::HD720PSlots, $HD720PTorrents, '720p');
        foreach ($HD720TS[0] as $T) {
            if (!isset($T['Missing']) && in_array($T['Slot'], [TorrentSlotType::ChineseQuality, TorrentSlotType::EnglishQuality])) {
                $Has720PQualitySlot = true;
            }
        }
        $TS = self::filter_slot_torrent(self::SDSlots, $SDTorrents, 'NTSC');
        foreach ($TS[0] as $T) {
            if ($T['Slot'] == TorrentSlotType::Quality && $Has720PQualitySlot && !isset($T['ExtraSlot'])) {
                $T['Dupe'] = true;
            }
            $Ret[] = $T;
        }
        foreach ($HD720TS[0] as $T) {
            $Ret[] = $T;
        }
        $Missing[TorrentSlotResolution::SD] = $TS[1];
        $Missing[TorrentSlotResolution::HD720P] = $HD720TS[1];
        $TS = self::filter_slot_torrent(self::HD1080PSlots, $HD1080PTorrents, '1080p');
        foreach ($TS[0] as $T) {
            $Ret[] = $T;
        }
        $Missing[TorrentSlotResolution::HD1080P] = $TS[1];
        $TS = self::filter_slot_torrent(self::UHDSlots, $UHDTorrents, '2160p');
        foreach ($TS[0] as $T) {
            $Ret[] = $T;
        }
        $Missing[TorrentSlotResolution::UHD] = $TS[1];
        return [$Ret, $Missing];
    }
    private static function filter_slot_torrent($Slots, $Torrents, $Resolution) {
        $MissingSlot = [];
        foreach ($Slots as $Slot) {
            $SlotTorrents = isset($Torrents[$Slot]) ? $Torrents[$Slot] : [];
            $ExtraSlotTorents = isset($Torrents[$Slot . '*']) ? $Torrents[$Slot . '*'] : [];
            $count = count($SlotTorrents);
            if ($count > 1) {
                if (isset(self::MaxSlotCount[$Slot]) && $count <= self::MaxSlotCount[$Slot]) {
                    foreach ($SlotTorrents as $SlotTorrent) {
                        $Ret[] = $SlotTorrent;
                    }
                } else {
                    foreach ($SlotTorrents as $SlotTorrent) {
                        $SlotTorrent['Dupe'] = true;
                        $Ret[] = $SlotTorrent;
                    }
                }
            } else if ($count <= 0) {
                if ($Slot == TorrentSlotType::None) {
                    continue;
                }
                $MissingSlot[] = $Slot;
                $Ret[] = ['Missing' => true, 'Slot' => $Slot, 'Resolution' => $Resolution];
            } else {
                $Ret[] = $SlotTorrents[0];
            }
            foreach ($ExtraSlotTorents as $ExtraSlotTorrent) {
                $ExtraSlotTorrent['ExtraSlot'] = true;
                $Ret[] = $ExtraSlotTorrent;
            }
        }
        return [$Ret, $MissingSlot];
    }
    public static function CalSlot($Torrent) {
        $Processing = Torrents::processing_value($Torrent);
        $Resolution = $Torrent['Resolution'];
        if (in_array(Torrents::resolution_level($Torrent), [SUBGROUP_3D, SUBGROUP_Extra])) {
            return TorrentSlotType::None;
        }
        $Codec = $Torrent['Codec'];
        $SpecialSub = isset($Torrent['SpecialSub']) && !empty($Torrent['SpecialSub']);
        $ChineseDubbed = isset($Torrent['ChineseDubbed']) && !empty($Torrent['ChineseDubbed']);
        $ChineseSubtitle = isset($Torrent['Subtitles']) && strstr($Torrent['Subtitles'], 'chinese');
        foreach (explode(',', $Torrent['Subtitles']) as $Subtitle) {
            if (!in_array($Subtitle, ['chinese_simplified', 'chinese_traditional', 'english'])) {
                $ChineseSubtitle = false;
                break;
            }
        }
        switch (self::get_slot_resolution($Resolution)) {
            case TorrentSlotResolution::SD:
                if ($Processing == 'Encode') {
                    if ($ChineseSubtitle) {
                        return TorrentSlotType::ChineseQuality;
                    }
                    return TorrentSlotType::Quality;
                }
                if ($Processing == 'Untouched') {
                    if ($Resolution == 'NTSC') {
                        return TorrentSlotType::NTSCUntouched;
                    } else if ($Resolution = 'PAL') {
                        return TorrentSlotType::PALUntouched;
                    }
                }
                return TorrentSlotType::None;
            case TorrentSlotResolution::HD720P:
                if ($Processing == 'Untouched') {
                    return TorrentSlotType::Untouched;
                } else if ($Processing == 'Remux') {
                    return TorrentSlotType::Remux;
                } else {
                    if ($SpecialSub || $ChineseDubbed) {
                        return TorrentSlotType::Feature;
                    }
                    if ($ChineseSubtitle) {
                        return TorrentSlotType::ChineseQuality;
                    } else {
                        return TorrentSlotType::EnglishQuality;
                    }
                }
                return TorrentSlotType::None;
            case TorrentSlotResolution::HD1080P:
                if ($Processing == 'Untouched') {
                    return TorrentSlotType::Untouched;
                } else if ($Processing == 'Remux') {
                    return TorrentSlotType::Remux;
                } else if ($Processing == 'DIY') {
                    return TorrentSlotType::DIY;
                } else {
                    if ($Codec == 'x265' || $Codec == 'H.265') {
                        if ($ChineseSubtitle) {
                            return TorrentSlotType::X265ChineseQuality;
                        } else {
                            return TorrentSlotType::X265EnglishQuality;
                        }
                    } else if ($Codec == 'x264' || $Codec == 'H.264') {
                        if ($SpecialSub || $ChineseDubbed) {
                            return TorrentSlotType::Feature;
                        }
                        if ($ChineseSubtitle) {
                            return TorrentSlotType::ChineseQuality;
                        } else {
                            return TorrentSlotType::EnglishQuality;
                        }
                    }
                }
                return TorrentSlotType::None;
            case TorrentSlotResolution::UHD:
                if ($Processing == 'Untouched') {
                    return TorrentSlotType::Untouched;
                } else if ($Processing == 'Remux') {
                    return TorrentSlotType::Remux;
                } else if ($Processing == 'DIY') {
                    return TorrentSlotType::DIY;
                } else {
                    if ($SpecialSub || $ChineseDubbed) {
                        return TorrentSlotType::Feature;
                    }
                    if ($ChineseSubtitle) {
                        return TorrentSlotType::ChineseQuality;
                    } else {
                        return TorrentSlotType::EnglishQuality;
                    }
                }
                return TorrentSlotType::None;
        }
        return TorrentSlotType::None;
    }


    public static function empty_slot_title($SlotResolution) {
        switch ($SlotResolution) {
            case TorrentSlotResolution::SD:
                return "empty_slots";
            case TorrentSlotResolution::HD720P:
                return "720p_empty_slots";
            case TorrentSlotResolution::HD1080P:
                return "1080p_empty_slots";
            case TorrentSlotResolution::UHD:
                return "empty_slots";
        }
    }


    public static function empty_slot_tooltip($Slot) {
        $str = '';
        switch ($Slot) {
            case TorrentSlotType::Quality:
                $str = 'quality_slot_requirements';
                break;
            case TorrentSlotType::NTSCUntouched:
                $str = 'untouched_slot_requirements';
                break;
            case TorrentSlotType::PALUntouched:
                $str = 'untouched_slot_requirements';
                break;
            case TorrentSlotType::X265ChineseQuality:
                $str = 'cn_quality_slot_requirements';
                break;
            case TorrentSlotType::X265EnglishQuality:
                $str = 'en_quality_slot_requirements';
                break;
            case TorrentSlotType::ChineseQuality:
                $str = 'cn_quality_slot_requirements';
                break;
            case TorrentSlotType::EnglishQuality:
                $str =  'en_quality_slot_requirements';
                break;
            case TorrentSlotType::Retention:
                $str =  'retention_slot_requirements';
                break;
            case TorrentSlotType::Feature:
                $str =  'feature_slot_requirements';
                break;
            case TorrentSlotType::Remux:
                $str =  'remux_slot_requirements';
                break;
            case TorrentSlotType::Untouched:
                $str =  'untouched_slot_requirements';
                break;
            case TorrentSlotType::DIY:
                $str =  'diy_slot_requirements';
                break;
            default:
                return '';
        }
        return t("server.torrents.$str");
    }

    public static function slot_option_lang($Slot) {
        switch ($Slot) {
            case TorrentSlotType::Quality:
                return 'quality_slot';
            case TorrentSlotType::NTSCUntouched:
                return 'untouched_slot_ntsc';
            case TorrentSlotType::PALUntouched:
                return 'untouched_slot_pal';
            case TorrentSlotType::X265ChineseQuality:
                return 'cn_quality_slot_x265';
            case TorrentSlotType::X265EnglishQuality:
                return 'en_quality_slot_x265';
            case TorrentSlotType::ChineseQuality:
                return 'cn_quality_slot';
            case TorrentSlotType::EnglishQuality:
                return 'en_quality_slot';
            case TorrentSlotType::Retention:
                return 'retention_slot';
            case TorrentSlotType::Feature:
                return 'feature_slot';
            case TorrentSlotType::Remux:
                return 'remux_slot';
            case TorrentSlotType::Untouched:
                return 'untouched_slot';
            case TorrentSlotType::DIY:
                return 'diy_slot';
        }
        return '';
    }

    public static function slot_option($Slot, $IsExtra, $TorrentSlot, $TorrentIsExtra) {
        $Selected = '';
        if ($Slot == $TorrentSlot && $IsExtra == $TorrentIsExtra) {
            $Selected = 'selected';
        }
        if (empty($Slot)) {
            $text = '---';
        } else {
            $text = t('server.torrents.' . self::slot_option_lang($Slot));
            if ($IsExtra) {
                $text .= '*';
                $Slot .= '*';
            }
        }

        $Ret = "<option class='Select-option' $Selected value='$Slot'>$text</option>";
        return $Ret;
    }

    public static function slot_name($Slot) {
        switch ($Slot) {
            case TorrentSlotType::None:
                return "empty";
            case TorrentSlotType::ChineseQuality:
            case TorrentSlotType::X265ChineseQuality:
                return 'cn_quality';
            case TorrentSlotType::Quality:
                return 'quality';
            case TorrentSlotType::EnglishQuality:
            case TorrentSlotType::X265EnglishQuality:
                return 'en_quality';
            case TorrentSlotType::Retention:
                return 'retention';
            case TorrentSlotType::Feature:
                return 'feature';
            case TorrentSlotType::DIY:
                return 'diy';
            case TorrentSlotType::Remux:
                return 'remux';
            case TorrentSlotType::Untouched:
            case TorrentSlotType::NTSCUntouched:
            case TorrentSlotType::PALUntouched:
                return 'untouched';
        }
        return '';
    }
}
