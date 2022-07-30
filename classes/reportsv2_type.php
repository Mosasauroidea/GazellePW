<?
/*
 * The $Types array is the backbone of the reports system and is stored here so it can
 * be included on the pages that need it without clogging up the pages that don't.
 * Important thing to note about the array:
 *   1. You need to ensure that the top level of the
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
 *   5. Modified by ZexWoo for matching a movie tracker.
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
            'title' => t('server.reportsv2.types.master.dupe.title'),
            'report_messages' => array(
                t('server.reportsv2.types.master.dupe.report_messages.1')
            ),
            'report_fields' => array(
                'sitelink' => '1'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '1',
                'pm' => t('server.reportsv2.types.master.dupe.resolve_options.pm')
            )
        ),
        'banned' => array(
            'priority' => '230',
            'reason' => '14',
            'title' => t('server.reportsv2.types.master.banned.title'),
            'report_messages' => array(
                t('server.reportsv2.types.master.banned.report_messages.1')
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '4',
                'delete' => '1',
                'pm' => t('server.reportsv2.types.master.banned.resolve_options.pm')
            )
        ),
        'urgent' => array(
            'priority' => '280',
            'reason' => '-1',
            'title' => t('server.reportsv2.types.master.urgent.title'),
            'report_messages' => array(
                t('server.reportsv2.types.master.urgent.report_messages.1'),
                t('server.reportsv2.types.master.urgent.report_messages.2'),
                t('server.reportsv2.types.master.urgent.report_messages.3'),
            ),
            'report_fields' => array(
                'sitelink' => '0',
                // 'track' => '0',
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
            'priority' => '250',
            'reason' => '-1',
            'title' => t('server.reportsv2.types.master.other.title'),
            'report_messages' => array(
                t('server.reportsv2.types.master.other.report_messages.1')
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
            'title' => t('server.reportsv2.types.master.trump.title'),
            'report_messages' => array(
                t('server.reportsv2.types.master.trump.report_messages.1'),
                t('server.reportsv2.types.master.trump.report_messages.2'),
            ),
            'report_fields' => array(
                'sitelink' => '1'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '1',
                'pm' => t('server.reportsv2.types.master.trump.resolve_options.pm')
            )
        )
    ),
    '1' => array( //Movie Resolves
        'transcode' => array(
            'priority' => '100',
            'reason' => '9',
            'title' => t('server.reportsv2.types.movie.transcode.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.transcode.report_messages.1'),
                t('server.reportsv2.types.movie.transcode.report_messages.2'),
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '2',
                'delete' => '1',
                'pm' => t('server.reportsv2.types.movie.transcode.resolve_options.pm'),
            )
        ),
        'low' => array(
            'priority' => '90',
            'reason' => '8',
            'title' => t('server.reportsv2.types.movie.low.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.low.report_messages.1'),
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => t('server.reportsv2.types.movie.low.resolve_options.pm'),
            )
        ),

        'names_bad' => array(
            'priority' => '30',
            'reason' => '2',
            'title' => t('server.reportsv2.types.movie.names_bad.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.names_bad.report_messages.1'),
                t('server.reportsv2.types.movie.names_bad.report_messages.2'),
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => t('server.reportsv2.types.movie.names_bad.resolve_options.pm'),
            )
        ),
        'video_track_bad' => array(
            'priority' => '40',
            'reason' => '3',
            'title' => t('server.reportsv2.types.movie.video_track_bad.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.video_track_bad.report_messages.1'),
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' =>  t('server.reportsv2.types.movie.video_track.resolve_options.pm'),
            )
        ),
        'audio_track_bad' => array(
            'priority' => '50',
            'reason' => '4',
            'title' => t('server.reportsv2.types.movie.audio_track_bad.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.audio_track_bad.report_messages.1'),
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => t('server.reportsv2.types.movie.audio_track.resolve_options.pm'),
            )
        ),
        'subtitle_track_bad' => array(
            'priority' => '60',
            'reason' => '5',
            'title' => t('server.reportsv2.types.movie.subtitle_track_bad.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.subtitle_track_bad.report_messages.1'),
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => t('server.reportsv2.types.movie.subtitle_track.resolve_options.pm'),
            )
        ),
        'torrent_description_bad' => array(
            'priority' => '80',
            'reason' => '7',
            'title' => t('server.reportsv2.types.movie.torrent_description_bad.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.torrent_description_bad.report_messages.1'),
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => t('server.reportsv2.types.movie.torrent_description_bad.resolve_options.pm'),
            )
        ),
        'format' => array(
            'priority' => '70',
            'reason' => '6',
            'title' => t('server.reportsv2.types.movie.format.title'),
            'report_messages' => array(
                t('server.reportsv2.types.movie.format.report_messages.1'),
                t('server.reportsv2.types.movie.format.report_messages.2'),
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => t('server.reportsv2.types.movie.format.resolve_options.pm'),
            )
        )
    ),
);
