<?
/*
 * The $Types array is the backbone of the reports system and is stored here so it can
 * be included on the pages that need it without clogging up the pages that don't.
 * Important thing to note about the array:
 *   1. When coding for a non music site, you need to ensure that the top level of the
 * array lines up with the $Categories array in your config.php.
 *   2. The first sub array contains resolves that are present on every report type
 * regardless of category.
 *   3. The only part that shouldn't be self-explanatory is that for the tracks field in
 * the report_fields arrays, 0 means not shown, 1 means required, 2 means required but
 * you can't select the 'All' box.
 *   4. The current report_fields that are set up are tracks, sitelink, link and image. If
 * you wanted to add a new one, you'd need to add a field to the reportsv2 table, elements
 * to the relevant report_fields arrays here, add the HTML in ajax_report and add security
 * in takereport.
 */

$ReportCategories = [
    'master' => 'General',
    '1' => 'Movie',
];

$Types = array(
    'master' => array(
        'dupe' => array(
            'priority' => '10',
            'reason' => '0',
            'title' => 'Dupe',
            'report_messages' => array(
                'Please specify a link to the original torrent.'
            ),
            'report_fields' => array(
                'sitelink' => '1'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '1',
                'pm' => '[rule]h2.2[/rule]. Your torrent was reported because it was a duplicate of another torrent.'
            )
        ),
        'banned' => array(
            'priority' => '230',
            'reason' => '14',
            'title' => 'Specifically Banned',
            'report_messages' => array(
                'Please specify exactly which entry on the Do Not Upload list this is violating.'
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '4',
                'delete' => '1',
                'pm' => '[rule]h1.2[/rule]. You have uploaded material that is currently forbidden. Items on the Do Not Upload (DNU) list (at the top of the [url=upload.php]upload page[/url]) and in the [url=rules.php?p=upload#1.2]Specifically Banned[/url] portion of the uploading rules cannot be uploaded to the site. Do not upload them unless your torrent meets a condition specified in the comments of the DNU list.
                Your torrent was reported because it contained material from the DNU list or from the Specifically Banned section of the rules.'
            )
        ),
        'urgent' => array(
            'priority' => '280',
            'reason' => '-1',
            'title' => 'Urgent',
            'report_messages' => array(
                'This report type is only for very urgent reports, usually for personal information being found within a torrent.',
                'Abusing the "Urgent" report type could result in a warning or worse.',
                'As this report type gives the staff absolutely no information about the problem, please be as clear as possible in your comments about what the problem is.'
            ),
            'report_fields' => array(
                'sitelink' => '0',
                'track' => '0',
                'link' => '0',
                'image' => '0',
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => ''
            )
        ),
        'other' => array(
            'priority' => '200',
            'reason' => '-1',
            'title' => 'Other',
            'report_messages' => array(
                'Please include as much information as possible to verify the report.'
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => ''
            )
        ),
        'trump' => array(
            'priority' => '20',
            'reason' => '1',
            'title' => 'Trump',
            'report_messages' => array(
                'Please list the specific reason(s) the newer torrent trumps the older one.',
                'Please make sure you are reporting the torrent <strong class="u-colorWarning">which has been trumped</strong> and should be deleted, not the torrent that you think should remain on site.'
            ),
            'report_fields' => array(
                'sitelink' => '1'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '1',
                'pm' => '[rule]h2.2[/rule]. Your torrent was reported because it was trumped by another torrent.'
            )
        )
    ),

    '1' => array( //Music Resolves
        'transcode' => array(
            'priority' => '100',
            'reason' => '9',
            'title' => 'Transcode',
            'report_messages' => array(
                "Please tell us how you checked the video and the audio tracks and confirm that they are transcodes.",
                "Please provide at least 1 picture as evidence if possible."
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '2',
                'delete' => '1',
                'pm' => '[rule]5.4.22[/rule]. Your torrent was reported, because it contains a transcoded audio track.'
            )
        ),
        'low' => array(
            'priority' => '90',
            'reason' => '8',
            'title' => 'Inferior Source',
            'report_messages' => array(
                "Please provide us PNG original screenshots."
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => '[rule]5.4.10[/rule]、[rule]5.4.11[/rule]、[rule]5.4.21[/rule]、[rule]5.4.24[/rule]。Encodes from inferior, mistaken, low definition sources are trumpable.'
            )
        ),
        'names_bad' => array(
            'priority' => '30',
            'reason' => '2',
            'title' => 'Bad File/Folder Names',
            'report_messages' => array(
                "Please list the file/folder name and what is wrong with it.",
                "Ideally you will replace this torrent with one with fixed file/folder names and report this with the reason \"Bad File/Folder Name Trump\"."
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]2.1.1[/rule]. File/Folder name should use the original title or official English title of the movie. (e.g. the English title on the poster, which is prior to IMDb.)
                
                [rule]2.1.2[/rule]. Renaming group releases (by P2P groups or Scene) is not allowed unless they disagree with rule [rule]2.1.1[/rule] or our file/folder name requirements.

                [rule]2.1.4[/rule]. DVD/BD structures should not be modified, only the top-level folder can be renamed.
                
                Your torrent has been marked as trumpable because of the bad file/folder name. You may fix this by yourself and re-upload a new torrent. Then, you need to report the old one by \"Trump\" with the permalink of new torrent."
            )
        ),

        'video_track_bad' => array(
            'priority' => '40',
            'reason' => '3',
            'title' => 'Bad Video Track',
            'report_messages' => array(
                "Please provide us PNG original screenshots and specify the problems of the video track."
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]5.4.1[/rule], [rule]5.4.2[/rule]. This upload has an aspect ratio different than the original, theatrically presented movie. Once a release with proper aspect ratio is available, no non-OAR upload may coexist in the same resolution group.
                
                [rule]5.4.5[/rule], [rule]5.4.6[/rule]. This upload has been improperly deinterlaced or plays at a framerate different than the native, proper framerate.

                [rule]5.4.18[/rule]. This upload was significantly overcropped or undercropped.

                [rule]5.4.22[/rule]. This upload is watermarked in a significant way.
                
                Your torrent has been marked as \"Bad Video Track\" and trumpable."
            )
        ),
        'audio_track_bad' => array(
            'priority' => '50',
            'reason' => '4',
            'title' => 'Bad Audio Track',
            'report_messages' => array(
                "Please specify the problems of the audio track."
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]5.4.3[/rule]. The video or audio bitrate of this upload is too high.

                [rule]5.4.4[/rule]. This upload includes superfluous audio tracks such as non-English dubs, or redundant versions of the same track.
                
                [rule]5.4.16[/rule]. Non-Chinese Language Dub: only for CN Quality Slots. This upload includes neither the original audio nor an Chinese dub, only a non-Chinese dub. Non-English Language Dub: only for EN Quality Slots. This upload includes neither the original audio nor an English dub, only a non-English dub.
                
                [rule]5.4.17[/rule]. Audio contained with this upload is usable, but not properly synchronized.
                
                Your torrent has been marked as \"Bad Audio Track\" and trumpable."
            )
        ),
        'subtitle_track_bad' => array(
            'priority' => '60',
            'reason' => '5',
            'title' => 'Bad Subtitle Track',
            'report_messages' => array(
                "Please specify the problems of the subtitle track."
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]5.4.7[/rule]. Subtitles contained with this upload are usable, but not properly synchronized.
                
                [rule]5.4.14[/rule]. Only for EN Quality Slots. This upload of a non-English movie does not contain English subtitles (internal or external).

                [rule]5.4.15[/rule]. Only for EN Quality Slots. This upload does not include separate English subtitles for significant non-English dialogue.

                [rule]5.4.19[/rule]. Subtitles included with this upload are poor quality and not an accurate translation of the movie.

                [rule]5.4.20[/rule]. Subtitles have been hardcoded in the video track of this upload. Hardcoded forced subtitles are not targeted by this mark.
                
                Your torrent has been marked as \"Bad Subtitle Track\" and trumpable."
            )
        ),
        'torrent_description_bad' => array(
            'priority' => '80',
            'reason' => '7',
            'title' => 'Bad Torrent Description',
            'report_messages' => array(
                "Please specify the problems of the torrent description."
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]2.2[/rule]. The torrent description doesn't meet the requirement of the rules. It may be the MediaInfo mistakes or wrong screenshots."
            )
        ),
        'format' => array(
            'priority' => '70',
            'reason' => '6',
            'title' => 'Improper Specifications',
            'report_messages' => array(
                "Any torrent out of allow specifications may use this type. e.g. SDR x265 1080p Encode、RMVB 720p Encode etc..",
                "Please specify whether the container or the resolution is not right."
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => '[rule]5.4.8[/rule]. This upload does not conform to our preferred formats.
                
                [rule]5.4.9[/rule]. This upload does not conform to our preferred resolutions.
                
                Your torrent was reported, because it used the format or the resolution that we do not prefer.'
            )
        )
    ),
);
