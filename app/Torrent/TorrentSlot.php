<?php

namespace Gazelle\Torrent;

use Torrents;

class TorrentSlot {
    // Slot resolution
    public const TorrentSlotResolutionNone = 0;
    public const TorrentSlotResolutionSD = 1;
    public const TorrentSlotResolutionHD720P = 2;
    public const TorrentSlotResolutionHD1080P = 3;
    public const TorrentSlotResolutionUHD = 4;

    // Slot group
    public const TorrentSlotGroupSDEncode = 1;
    public const TorrentSlotGroupSDUntouched = 2;
    public const TorrentSlotGroupHDEncode = 3;
    public const TorrentSlotGroupHDUntouched = 4;
    public const TorrentSlotGroupUHDEncode = 5;
    public const TorrentSlotGroupUHDUntouched = 6;

    // Slot status
    public const TorrentSlotStatusFull = 1;
    public const TorrentSlotStatusFree = 2;
    public const TorrentSlotStatusEmpty = 3;


    // Slot type
    public const TorrentSlotTypeNone = 0;
    public const TorrentSlotTypeQuality = 1;
    public const TorrentSlotTypeNTSCUntouched = 2;
    public const TorrentSlotTypePALUntouched = 3;
    public const TorrentSlotTypeRetention = 4;
    public const TorrentSlotTypeFeature = 5;
    public const TorrentSlotTypeChineseQuality = 6;
    public const TorrentSlotTypeEnglishQuality = 7;
    public const TorrentSlotTypeX265ChineseQuality = 8;
    public const TorrentSlotTypeX265EnglishQuality = 9;
    public const TorrentSlotTypeRemux = 10;
    public const TorrentSlotTypeDIY = 11;
    public const TorrentSlotTypeUntouched = 12;
    public const TorrentSlotTypeWebSDR = 13;
    public const TorrentSlotTypeWebDovi = 14;
    public const TorrentSlotTypeHi10p = 15;
    public const TorrentSlotTypeRemuxFeature = 16;


    const SDEncodeSlots = [
        self::TorrentSlotTypeQuality,
        self::TorrentSlotTypeChineseQuality,
    ];
    const SDUntouchedSlots = [
        self::TorrentSlotTypeNTSCUntouched,
        self::TorrentSlotTypePALUntouched,
    ];
    const HD720PEncodeSlots = [
        self::TorrentSlotTypeRetention,
        self::TorrentSlotTypeFeature,
        self::TorrentSlotTypeChineseQuality,
        self::TorrentSlotTypeEnglishQuality,
    ];
    const HD1080PEncodeSlots = [
        self::TorrentSlotTypeRetention,
        self::TorrentSlotTypeFeature,
        self::TorrentSlotTypeChineseQuality,
        self::TorrentSlotTypeEnglishQuality,
        self::TorrentSlotTypeX265ChineseQuality,
        self::TorrentSlotTypeX265EnglishQuality,
        self::TorrentSlotTypeWebDovi,
    ];
    const HD720PUntouchedSlots = [
        self::TorrentSlotTypeRemux,
        self::TorrentSlotTypeUntouched,
    ];

    const HD1080PUntouchedSlots = [
        self::TorrentSlotTypeRemux,
        self::TorrentSlotTypeRemuxFeature,
        self::TorrentSlotTypeUntouched,
        self::TorrentSlotTypeDIY,
    ];
    const UHDEncodeSlots = [
        self::TorrentSlotTypeRetention,
        self::TorrentSlotTypeFeature,
        self::TorrentSlotTypeChineseQuality,
        self::TorrentSlotTypeEnglishQuality,
        self::TorrentSlotTypeWebSDR,
        self::TorrentSlotTypeWebDovi,
    ];
    const UHDUntouchedSlots = [
        self::TorrentSlotTypeRemux,
        self::TorrentSlotTypeRemuxFeature,
        self::TorrentSlotTypeDIY,
        self::TorrentSlotTypeUntouched,
    ];

    const SDSlots = [
        self::TorrentSlotTypeNone,
        self::TorrentSlotTypeQuality,
        self::TorrentSlotTypeChineseQuality,
        self::TorrentSlotTypeNTSCUntouched,
        self::TorrentSlotTypePALUntouched,
    ];
    const HD720PSlots = [
        self::TorrentSlotTypeNone,
        self::TorrentSlotTypeRetention,
        self::TorrentSlotTypeFeature,
        self::TorrentSlotTypeChineseQuality,
        self::TorrentSlotTypeEnglishQuality,
        self::TorrentSlotTypeHi10p,
        self::TorrentSlotTypeRemux,
        self::TorrentSlotTypeUntouched,
    ];
    const HD1080PSlots = [
        self::TorrentSlotTypeNone,
        self::TorrentSlotTypeRetention,
        self::TorrentSlotTypeFeature,
        self::TorrentSlotTypeWebDovi,
        self::TorrentSlotTypeChineseQuality,
        self::TorrentSlotTypeEnglishQuality,
        self::TorrentSlotTypeX265ChineseQuality,
        self::TorrentSlotTypeX265EnglishQuality,
        self::TorrentSlotTypeHi10p,
        self::TorrentSlotTypeRemux,
        self::TorrentSlotTypeRemuxFeature,
        self::TorrentSlotTypeDIY,
        self::TorrentSlotTypeUntouched,
    ];
    const UHDSlots = [
        self::TorrentSlotTypeNone,
        self::TorrentSlotTypeRetention,
        self::TorrentSlotTypeWebSDR,
        self::TorrentSlotTypeWebDovi,
        self::TorrentSlotTypeFeature,
        self::TorrentSlotTypeX265ChineseQuality,
        self::TorrentSlotTypeX265EnglishQuality,
        self::TorrentSlotTypeRemux,
        self::TorrentSlotTypeRemuxFeature,
        self::TorrentSlotTypeDIY,
        self::TorrentSlotTypeUntouched,
    ];

    const Slots = [
        self::TorrentSlotResolutionSD => self::SDSlots,
        self::TorrentSlotResolutionHD720P => self::HD720PSlots,
        self::TorrentSlotResolutionHD1080P => self::HD1080PSlots,
        self::TorrentSlotResolutionUHD => self::UHDSlots,
    ];

    const MaxSlotCount = [self::TorrentSlotTypeQuality => 2];

    public static function get_slot_resolution($Resolution) {
        global $StandardDefinition, $UltraDefinition;
        if (in_array($Resolution, $StandardDefinition)) {
            return self::TorrentSlotResolutionSD;
        } else if ($Resolution == '720p') {
            return self::TorrentSlotResolutionHD720P;
        } else if (in_array($Resolution, ['1080i', '1080p'])) {
            return self::TorrentSlotResolutionHD1080P;
        } else if (in_array($Resolution, $UltraDefinition)) {
            return self::TorrentSlotResolutionUHD;
        } else if (empty($Resolution)) {
            return self::TorrentSlotResolutionNone;
        }
        return self::TorrentSlotResolutionSD;
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
            if ($Resolution == self::TorrentSlotResolutionSD) {
                if (isset($SDSlotTorrents[$Torrent['Slot']])) {
                    $SDSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $SDSlotTorrents[$Torrent['Slot']] = 1;
                }
            } else if (in_array($Resolution, [self::TorrentSlotResolutionHD720P])) {
                if (isset($HD720PSlotTorrents[$Torrent['Slot']])) {
                    $HD720PSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $HD720PSlotTorrents[$Torrent['Slot']] = 1;
                }
            } else if (in_array($Resolution, [self::TorrentSlotResolutionHD1080P])) {
                if (isset($HD1080PSlotTorrents[$Torrent['Slot']])) {
                    $HD1080PSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $HD1080PSlotTorrents[$Torrent['Slot']] = 1;
                }
            } else if ($Resolution == self::TorrentSlotResolutionUHD) {
                if (isset($UHDSlotTorrents[$Torrent['Slot']])) {
                    $UHDSlotTorrents[$Torrent['Slot']]++;
                } else {
                    $UHDSlotTorrents[$Torrent['Slot']] = 1;
                }
            }
        }
        list($HD720PEncodeStatus, $HD720PEncodeMissSlots) = self::check_slot_status($HD720PSlotTorrents, self::HD720PEncodeSlots);
        list($HD1080PEncodeStatus, $HD10800PEncodeMissSlots) = self::check_slot_status($HD1080PSlotTorrents, self::HD1080PEncodeSlots);
        $HDEncodeStatus = self::TorrentSlotStatusFree;
        if ($HD720PEncodeStatus == self::TorrentSlotStatusFull && $HD1080PEncodeStatus == self::TorrentSlotStatusFull) {
            $HDEncodeStatus = self::TorrentSlotStatusFull;
        }
        if ($HD720PEncodeStatus == self::TorrentSlotStatusEmpty && $HD1080PEncodeStatus == self::TorrentSlotStatusEmpty) {
            $HDEncodeStatus = self::TorrentSlotStatusEmpty;
        }
        $HDEncodeMissSlots = $HD720PEncodeMissSlots;
        foreach ($HD10800PEncodeMissSlots as $MissSlot) {
            if (!in_array($MissSlot, $HDEncodeMissSlots)) {
                $HDEncodeMissSlots[] = $MissSlot;
            }
        }

        list($HD720PUntouchedStatus, $HD720PUntouchedMissSlots) = self::check_slot_status($HD720PSlotTorrents, self::HD720PUntouchedSlots);
        list($HD1080PUntouchedStatus, $HD1080PUntouchedMissSlots) = self::check_slot_status($HD1080PSlotTorrents, self::HD1080PUntouchedSlots);
        $HDUntouchedStatus = self::TorrentSlotStatusFree;
        if ($HD720PUntouchedStatus == self::TorrentSlotStatusFull && $HD1080PUntouchedStatus == self::TorrentSlotStatusFull) {
            $HDUntouchedStatus = self::TorrentSlotStatusFull;
        }
        if ($HD720PEncodeStatus == self::TorrentSlotStatusEmpty && $HD1080PUntouchedStatus == self::TorrentSlotStatusEmpty) {
            $HDUntouchedStatus = self::TorrentSlotStatusEmpty;
        }
        $HDUntouchedMissSlots = $HD720PUntouchedMissSlots;
        foreach ($HD1080PUntouchedMissSlots as $MissSlot) {
            if (!in_array($MissSlot, $HDEncodeMissSlots)) {
                $HDUntouchedMissSlots[] = $MissSlot;
            }
        }

        return [
            self::TorrentSlotGroupSDEncode => self::check_slot_status($SDSlotTorrents, self::SDEncodeSlots),
            self::TorrentSlotGroupSDUntouched => self::check_slot_status($SDSlotTorrents, self::SDUntouchedSlots),
            self::TorrentSlotGroupHDEncode => [$HDEncodeStatus, $HDEncodeMissSlots],
            self::TorrentSlotGroupHDUntouched => [$HDUntouchedStatus, array_unique($HDUntouchedMissSlots)],
            self::TorrentSlotGroupUHDEncode => self::check_slot_status($UHDSlotTorrents, self::UHDEncodeSlots),
            self::TorrentSlotGroupUHDUntouched => self::check_slot_status($UHDSlotTorrents, self::UHDUntouchedSlots),
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
            return [self::TorrentSlotStatusEmpty, $MissSlots];
        }
        if (!$allfull) {
            return [self::TorrentSlotStatusFree, $MissSlots];
        }
        return [self::TorrentSlotStatusFull, $MissSlots];
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
                case self::TorrentSlotResolutionSD:
                    $SDTorrents[$Slot][] = $Torrent;
                    break;
                case self::TorrentSlotResolutionHD720P:
                    $HD720PTorrents[$Slot][] = $Torrent;
                    break;
                case self::TorrentSlotResolutionHD1080P:
                    $HD1080PTorrents[$Slot][] = $Torrent;
                    break;
                case self::TorrentSlotResolutionUHD:
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
            if (!isset($T['Missing']) && in_array($T['Slot'], [self::TorrentSlotTypeChineseQuality, self::TorrentSlotTypeEnglishQuality])) {
                $Has720PQualitySlot = true;
            }
        }
        $TS = self::filter_slot_torrent(self::SDSlots, $SDTorrents, 'NTSC');
        foreach ($TS[0] as $T) {
            if ($T['Slot'] == self::TorrentSlotTypeQuality && $Has720PQualitySlot && !isset($T['ExtraSlot'])) {
                $T['Dupe'] = true;
            }
            $Ret[] = $T;
        }
        foreach ($HD720TS[0] as $T) {
            $Ret[] = $T;
        }
        $Missing[self::TorrentSlotResolutionSD] = $TS[1];
        $Missing[self::TorrentSlotResolutionHD720P] = $HD720TS[1];
        $TS = self::filter_slot_torrent(self::HD1080PSlots, $HD1080PTorrents, '1080p');
        foreach ($TS[0] as $T) {
            $Ret[] = $T;
        }
        $Missing[self::TorrentSlotResolutionHD1080P] = $TS[1];
        $TS = self::filter_slot_torrent(self::UHDSlots, $UHDTorrents, '2160p');
        foreach ($TS[0] as $T) {
            $Ret[] = $T;
        }
        $Missing[self::TorrentSlotResolutionUHD] = $TS[1];
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
                if ($Slot == self::TorrentSlotTypeNone) {
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
            return self::TorrentSlotTypeNone;
        }
        $Codec = $Torrent['Codec'];
        $SpecialSub = isset($Torrent['SpecialSub']) && !empty($Torrent['SpecialSub']);
        $ChineseDubbed = isset($Torrent['ChineseDubbed']) && !empty($Torrent['ChineseDubbed']);
        $ChineseSubtitle = isset($Torrent['Subtitles']) && strstr($Torrent['Subtitles'], 'chinese');
        $Source = $Torrent['Source'];
        $RemasterTitle = $Torrent['RemasterTitle'];
        switch (self::get_slot_resolution($Resolution)) {
            case self::TorrentSlotResolutionSD:
                if ($Processing == 'Encode') {
                    if ($ChineseSubtitle) {
                        return self::TorrentSlotTypeChineseQuality;
                    }
                    return self::TorrentSlotTypeQuality;
                }
                if ($Processing == 'Untouched') {
                    if ($Resolution == 'NTSC') {
                        return self::TorrentSlotTypeNTSCUntouched;
                    } else if ($Resolution = 'PAL') {
                        return self::TorrentSlotTypePALUntouched;
                    }
                }
                return self::TorrentSlotTypeNone;
            case self::TorrentSlotResolutionHD720P:
                if ($Processing == 'Untouched') {
                    return self::TorrentSlotTypeUntouched;
                } else if ($Processing == 'Remux') {
                    return self::TorrentSlotTypeRemux;
                } else {
                    if ($SpecialSub || $ChineseDubbed) {
                        return self::TorrentSlotTypeFeature;
                    }
                    if ($ChineseSubtitle) {
                        return self::TorrentSlotTypeChineseQuality;
                    } else {
                        return self::TorrentSlotTypeEnglishQuality;
                    }
                }
                return self::TorrentSlotTypeNone;
            case self::TorrentSlotResolutionHD1080P:
                if ($Source == 'Web' && $Codec == 'H.265') {
                    if (EditionInfo::checkEditionInfo($RemasterTitle, EditionInfo::edition_dolby_vision)) {
                        return self::TorrentSlotTypeWebDovi;
                    }
                }
                if ($Processing == 'Untouched') {
                    return self::TorrentSlotTypeUntouched;
                } else if ($Processing == 'Remux') {
                    if ($SpecialSub || $ChineseDubbed) {
                        return self::TorrentSlotTypeRemuxFeature;
                    }
                    return self::TorrentSlotTypeRemux;
                } else if ($Processing == 'DIY') {
                    return self::TorrentSlotTypeDIY;
                } else {
                    if ($Codec == 'x265' || $Codec == 'H.265') {
                        if ($ChineseSubtitle) {
                            return self::TorrentSlotTypeX265ChineseQuality;
                        } else {
                            return self::TorrentSlotTypeX265EnglishQuality;
                        }
                    } else if ($Codec == 'x264' || $Codec == 'H.264') {
                        if ($SpecialSub || $ChineseDubbed) {
                            return self::TorrentSlotTypeFeature;
                        }
                        if ($ChineseSubtitle) {
                            return self::TorrentSlotTypeChineseQuality;
                        } else {
                            return self::TorrentSlotTypeEnglishQuality;
                        }
                    }
                }
                return self::TorrentSlotTypeNone;
            case self::TorrentSlotResolutionUHD:
                if ($Source == 'Web' && $Codec == 'H.265') {
                    if (EditionInfo::checkEditionInfo($RemasterTitle, EditionInfo::edition_dolby_vision)) {
                        return self::TorrentSlotTypeWebDovi;
                    }
                    return self::TorrentSlotTypeWebSDR;
                }
                if ($Processing == 'Untouched') {
                    return self::TorrentSlotTypeUntouched;
                } else if ($Processing == 'Remux') {
                    if ($SpecialSub || $ChineseDubbed) {
                        return self::TorrentSlotTypeRemuxFeature;
                    }
                    return self::TorrentSlotTypeRemux;
                } else if ($Processing == 'DIY') {
                    return self::TorrentSlotTypeDIY;
                } else {
                    if ($SpecialSub || $ChineseDubbed) {
                        return self::TorrentSlotTypeFeature;
                    }
                    if ($ChineseSubtitle) {
                        return self::TorrentSlotTypeX265ChineseQuality;
                    } else {
                        return self::TorrentSlotTypeX265EnglishQuality;
                    }
                }
                return self::TorrentSlotTypeNone;
        }
        return self::TorrentSlotTypeNone;
    }


    public static function empty_slot_title($SlotResolution) {
        switch ($SlotResolution) {
            case self::TorrentSlotResolutionSD:
                return "empty_slots";
            case self::TorrentSlotResolutionHD720P:
                return "720p_empty_slots";
            case self::TorrentSlotResolutionHD1080P:
                return "1080p_empty_slots";
            case self::TorrentSlotResolutionUHD:
                return "empty_slots";
        }
    }


    public static function empty_slot_tooltip($Slot) {
        $str = '';
        switch ($Slot) {
            case self::TorrentSlotTypeQuality:
                $str = 'quality_slot_requirements';
                break;
            case self::TorrentSlotTypeNTSCUntouched:
                $str = 'untouched_slot_requirements';
                break;
            case self::TorrentSlotTypePALUntouched:
                $str = 'untouched_slot_requirements';
                break;
            case self::TorrentSlotTypeX265ChineseQuality:
                $str = 'cn_quality_slot_requirements';
                break;
            case self::TorrentSlotTypeX265EnglishQuality:
                $str = 'en_quality_slot_requirements';
                break;
            case self::TorrentSlotTypeChineseQuality:
                $str = 'cn_quality_slot_requirements';
                break;
            case self::TorrentSlotTypeEnglishQuality:
                $str =  'en_quality_slot_requirements';
                break;
            case self::TorrentSlotTypeRetention:
                $str =  'retention_slot_requirements';
                break;
            case self::TorrentSlotTypeFeature:
                $str =  'feature_slot_requirements';
                break;
            case self::TorrentSlotTypeRemux:
                $str =  'remux_slot_requirements';
                break;
            case self::TorrentSlotTypeUntouched:
                $str =  'untouched_slot_requirements';
                break;
            case self::TorrentSlotTypeDIY:
                $str =  'diy_slot_requirements';
                break;
            case self::TorrentSlotTypeHi10p:
                $str =  'hi10p_slot_requirements';
                break;
            case self::TorrentSlotTypeRemuxFeature:
                $str =  'remux_feature_slot_requirements';
                break;
            case self::TorrentSlotTypeWebDovi:
                $str =  'dovi_slot_requirements';
                break;
            case self::TorrentSlotTypeWebSDR:
                $str =  'sdr_slot_requirements';
                break;
            default:
                return '';
        }
        return t("server.torrents.$str");
    }

    public static function slot_option_lang($Slot) {
        switch ($Slot) {
            case self::TorrentSlotTypeQuality:
                return 'quality_slot';
            case self::TorrentSlotTypeNTSCUntouched:
                return 'untouched_slot_ntsc';
            case self::TorrentSlotTypePALUntouched:
                return 'untouched_slot_pal';
            case self::TorrentSlotTypeX265ChineseQuality:
                return 'cn_quality_slot_x265';
            case self::TorrentSlotTypeX265EnglishQuality:
                return 'en_quality_slot_x265';
            case self::TorrentSlotTypeChineseQuality:
                return 'cn_quality_slot';
            case self::TorrentSlotTypeEnglishQuality:
                return 'en_quality_slot';
            case self::TorrentSlotTypeRetention:
                return 'retention_slot';
            case self::TorrentSlotTypeFeature:
                return 'feature_slot';
            case self::TorrentSlotTypeRemux:
                return 'remux_slot';
            case self::TorrentSlotTypeUntouched:
                return 'untouched_slot';
            case self::TorrentSlotTypeDIY:
                return 'diy_slot';
            case self::TorrentSlotTypeWebSDR:
                return 'sdr_slot';
            case self::TorrentSlotTypeWebDovi:
                return 'dovi_slot';
            case self::TorrentSlotTypeHi10p:
                return 'hi10p_slot';
            case self::TorrentSlotTypeRemuxFeature:
                return 'remux_feature_slot';
            case self::TorrentSlotTypeNone:
                return "empty";
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

    public static function slot_filter_name($Slot) {
        switch ($Slot) {
            case self::TorrentSlotTypeNone:
                return "empty";
            case self::TorrentSlotTypeChineseQuality:
            case self::TorrentSlotTypeX265ChineseQuality:
                return 'cn_quality';
            case self::TorrentSlotTypeQuality:
            case self::TorrentSlotTypeHi10p:
            case self::TorrentSlotTypeWebSDR:
            case self::TorrentSlotTypeWebDovi:
                return 'quality';
            case self::TorrentSlotTypeEnglishQuality:
            case self::TorrentSlotTypeX265EnglishQuality:
                return 'en_quality';
            case self::TorrentSlotTypeRetention:
                return 'retention';
            case self::TorrentSlotTypeFeature:
                return 'feature';
            case self::TorrentSlotTypeDIY:
                return 'diy';
            case self::TorrentSlotTypeRemux:
            case self::TorrentSlotTypeRemuxFeature:
                return 'remux';
            case self::TorrentSlotTypeUntouched:
            case self::TorrentSlotTypeNTSCUntouched:
            case self::TorrentSlotTypePALUntouched:
                return 'untouched';
        }
        return '';
    }
}
