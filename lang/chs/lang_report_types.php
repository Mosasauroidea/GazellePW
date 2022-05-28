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
            'title' => '重复',
            'report_messages' => array(
                '请附上既有种子的链接。'
            ),
            'report_fields' => array(
                'sitelink' => '1'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '1',
                'pm' => '[rule]h2.2[/rule]。你的种子已被报告，因为它与站点既有种子重复。'
            )
        ),
        'banned' => array(
            'priority' => '230',
            'reason' => '14',
            'title' => '特别禁止内容',
            'report_messages' => array(
                '请明确指出其违反了 “禁止发布” 列表中的哪一项。'
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '4',
                'delete' => '1',
                'pm' => '[rule]h1.2[/rule]。你上传了本站目前禁止的资源。列于禁止发布列表（位于 [url=upload.php]发布页面[/url] 顶部）以及上传规则中 [url=rules.php?p=upload#h1.2]特别禁止[/url] 部分的资源不能被上传到本站。除非你的种子符合禁止发布列表注释中指定的条件，否则请勿发布。
                你的种子已被报告，因为它包含了来自禁止发布列表或上传规则中特别禁止部分的资源。'
            )
        ),
        'urgent' => array(
            'priority' => '280',
            'reason' => '-1',
            'title' => '紧急',
            'report_messages' => array(
                '该类目仅适用于紧急情况，一般是因为在种子中泄露了个人信息。',
                '滥用 “紧急” 类目会导致警告或更严重的惩罚。',
                '由于该类目不能方便地告知管理员问题所在，所以请在说明中详细描述种子的问题。'
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
            'title' => '其他',
            'report_messages' => array(
                '请在说明中尽可能详细地描述问题。'
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
            'title' => '替代',
            'report_messages' => array(
                '请列出新种子能够替代原有种子的具体原因。',
                '请确保你正在报告的，是<strong class="u-colorWarning">将被替代</strong>且应被删除的种子，而不是你认为应保留的种子。'
            ),
            'report_fields' => array(
                'sitelink' => '1'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '1',
                'pm' => '[rule]h2.2[/rule]。你的种子已被报告，因为它将被新的种子替代。'
            )
        )
    ),
    '1' => array( //Movie Resolves
        'transcode' => array(
            'priority' => '100',
            'reason' => '9',
            'title' => '劣质转码',
            'report_messages' => array(
                "请写明你检查的音视频轨以及用以确认它为劣质转码的方法。",
                "如有可能，请附上至少一张用以佐证的分析图，分析图多多益善。"
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '2',
                'delete' => '1',
                'pm' => '[rule]5.4.21[/rule]。我们不接受有损来源的转码或重编码。
                你的种子已被报告，因为它包含了劣质转码的音轨。'
            )
        ),
        'low' => array(
            'priority' => '90',
            'reason' => '8',
            'title' => '劣质源',
            'report_messages' => array(
                "请向我们提供 PNG 格式的影片原始分辨率截图。"
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => '[rule]5.4.10[/rule]、[rule]5.4.11[/rule]、[rule]5.4.21[/rule]、[rule]5.4.24[/rule]。编码自质量低下的、存在错误的、低清的源的作品会被标记为 “可替代”。'
            )
        ),

        'names_bad' => array(
            'priority' => '30',
            'reason' => '2',
            'title' => '问题文件（夹）名',
            'report_messages' => array(
                "请指出有问题的文件（夹）名。",
                "理想情况下，你可以发布修复了文件（夹）名问题之后的种子以替代该种子。"
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]2.1.1[/rule]。文件（夹）名必须使用电影原始语种名称或官方英文名（推荐）。（如海报所示等的官方英文名，其优先级高于 IMDb。）
                
                [rule]2.1.2[/rule]。压制组发行（来自 P2P 组或 Scene 组）不应重命名，除非它们不满足规则 [rule]2.1.1[/rule] 或我们的文件名要求。

                [rule]2.1.4[/rule]。DVD 和 BD 文件目录结构不允许改动，仅顶层文件夹允许重命名。
                
                你的种子被标记为问题文件（夹）名且可被替代。当然你也可自行修复这个种子，补充或修正文件（夹）名然后重新发布种子。然后以 “替代” 为由报告（RP）旧种，在报告说明中指出你已修复的命名问题，同时请确保提供用以替代的新种永久链接（PL）。"
            )
        ),
        'video_track_bad' => array(
            'priority' => '40',
            'reason' => '3',
            'title' => '问题视频轨',
            'report_messages' => array(
                "请指出视频轨存在的具体问题，并向我们提供 PNG 格式的影片原始分辨率截图。"
            ),
            'report_fields' => array(
                'image' => '0'
            ),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]5.4.1[/rule]、[rule]5.4.2[/rule]。种子的纵横比因编码错误等原因与原始的、影院上映的电影不同。
                
                [rule]5.4.5[/rule]、[rule]5.4.6[/rule]。种子被错误地反交错，或以错误的帧率播放。

                [rule]5.4.18[/rule]。种子明显裁边过多或过少。

                [rule]5.4.22[/rule]。种子含有明显的水印。
                
                你的种子被标记为 “问题视频轨” 且可被替代。"
            )
        ),
        'audio_track_bad' => array(
            'priority' => '50',
            'reason' => '4',
            'title' => '问题音频轨',
            'report_messages' => array(
                "请指出音频轨存在的具体问题。"
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]5.4.3[/rule]。种子内含的音频轨相对其分辨率而言过于庞大。

                [rule]5.4.4[/rule]。种子含有冗余的音频轨。
                
                [rule]5.4.16[/rule]。既没有原始语种音频也没有国语配音，只有非国语配音的种子会被标记为 “可替代”。
                
                [rule]5.4.17[/rule]。种子中包含的音轨有效，但不同步。
                
                你的种子被标记为 “问题音频轨” 且可被替代。"
            )
        ),
        'subtitle_track_bad' => array(
            'priority' => '60',
            'reason' => '5',
            'title' => '问题字幕轨',
            'report_messages' => array(
                "请指出字幕轨存在的具体问题。"
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]5.4.7[/rule]。种子中包含的字幕有效，但不同步。
                
                [rule]5.4.14[/rule]。无中字槽位的种子如果是不包含英文字幕（通过字幕管理器内挂或外挂）的非英语电影，会被标为 “可替代”。

                [rule]5.4.15[/rule]。无中字槽位的种子，其重要非英语对白不包含单独的英文字幕。

                [rule]5.4.19[/rule]。包含字幕质量很差，且不是电影准确翻译的种子会被标为 “可替代”。

                [rule]5.4.20[/rule]。字幕被硬编码在视频轨中的种子会被标为 “可替代”。
                
                你的种子被标记为 “问题字幕轨” 且可被替代。"
            )
        ),
        'torrent_description_bad' => array(
            'priority' => '80',
            'reason' => '7',
            'title' => '问题种子描述',
            'report_messages' => array(
                "请指出种子描述存在的具体问题。"
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => "[rule]2.2[/rule]。当前填写的种子描述未符合规则要求，可能是 MediaInfo 不完整、有错误；或截图不符合规则。"
            )
        ),
        'format' => array(
            'priority' => '70',
            'reason' => '6',
            'title' => '规格不当',
            'report_messages' => array(
                "在规则容许规格范围外的种子都可以使用本项，如 SDR x265 1080p Encode、RMVB 720p Encode 等。",
                "请指明是容器不被规则允许，还是分辨率存在问题。"
            ),
            'report_fields' => array(),
            'resolve_options' => array(
                'upload' => '0',
                'warn' => '0',
                'delete' => '0',
                'pm' => '[rule]5.4.8[/rule]。种子应符合我们 [rule]h3[/rule] 推荐格式的要求。
                
                [rule]5.4.9[/rule]。种子应符合我们 [rule]h3[/rule] 推荐分辨率的要求。
                
                你的种子已被报告，因为其格式或分辨率与规则不符。'
            )
        )


        // 'upload_contest' => array(
        //     'priority' => '163',
        //     'reason' => '-1',
        //     'title' => '请求批准上传比赛',
        //     'report_messages' => array(
        //         '请附上带有写着你用户名小纸条的实体贴片照片。',
        //         '<strong class="u-colorWarning">任何你提交用作证据的图片只有管理组成员可见。</strong>'
        //     ),
        //     'report_fields' => array(
        //         'proofimages' => '2'
        //     ),
        //     'resolve_options' => array(
        //         'upload' => '0',
        //         'warn' => '0',
        //         'delete' => '0'
        //     )
        // )
    ),
);
