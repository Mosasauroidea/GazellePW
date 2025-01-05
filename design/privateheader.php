<?

use Gazelle\Manager\Donation;

define('FOOTER_FILE', CONFIG['SERVER_ROOT'] . '/design/privatefooter.php');

global $LoggedUser;
$donation = new Donation();
$CurrentLang = Lang::getCurrentLangStandard();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html data-theme="<?= G::$LoggedUser['StyleTheme'] ?>" data-lang="<?= $CurrentLang  ?>" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title><?= display_str($PageTitle) ?></title>
    <meta http-equiv="X-UA-Compatible" content="chrome=1;IE=edge" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="msapplication-config" content="none" />
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="favicon.ico" />
    <link rel="stylesheet" type="text/css" media="screen" href="/deps/tooltipster.bundle.min.css" />
    <link rel="search" type="application/opensearchdescription+xml" title="<?= CONFIG['SITE_NAME'] ?> Torrents" href="opensearch.php?type=torrents" />
    <link rel="search" type="application/opensearchdescription+xml" title="<?= CONFIG['SITE_NAME'] ?> Artists" href="opensearch.php?type=artists" />
    <link rel="search" type="application/opensearchdescription+xml" title="<?= CONFIG['SITE_NAME'] ?> Requests" href="opensearch.php?type=requests" />
    <link rel="search" type="application/opensearchdescription+xml" title="<?= CONFIG['SITE_NAME'] ?> Forums" href="opensearch.php?type=forums" />
    <link rel="search" type="application/opensearchdescription+xml" title="<?= CONFIG['SITE_NAME'] ?> Log" href="opensearch.php?type=log" />
    <link rel="search" type="application/opensearchdescription+xml" title="<?= CONFIG['SITE_NAME'] ?> Users" href="opensearch.php?type=users" />
    <link rel="search" type="application/opensearchdescription+xml" title="<?= CONFIG['SITE_NAME'] ?> Wiki" href="opensearch.php?type=wiki" />
    <link rel="alternate" type="application/rss+xml" title="<?= CONFIG['SITE_NAME'] ?> - News" href="feeds.php?feed=feed_news&amp;user=<?= G::$LoggedUser['ID'] ?>&amp;auth=<?= G::$LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= G::$LoggedUser['torrent_pass'] ?>&amp;authkey=<?= G::$LoggedUser['AuthKey'] ?>" />
    <link rel="alternate" type="application/rss+xml" title="<?= CONFIG['SITE_NAME'] ?> - Blog" href="feeds.php?feed=feed_blog&amp;user=<?= G::$LoggedUser['ID'] ?>&amp;auth=<?= G::$LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= G::$LoggedUser['torrent_pass'] ?>&amp;authkey=<?= G::$LoggedUser['AuthKey'] ?>" />
    <link rel="alternate" type="application/rss+xml" title="<?= CONFIG['SITE_NAME'] ?> - Changelog" href="feeds.php?feed=feed_changelog&amp;user=<?= G::$LoggedUser['ID'] ?>&amp;auth=<?= G::$LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= G::$LoggedUser['torrent_pass'] ?>&amp;authkey=<?= G::$LoggedUser['AuthKey'] ?>" />
    <link rel="alternate" type="application/rss+xml" title="<?= CONFIG['SITE_NAME'] ?> - P.T.N." href="feeds.php?feed=torrents_notify_<?= G::$LoggedUser['torrent_pass'] ?>&amp;user=<?= G::$LoggedUser['ID'] ?>&amp;auth=<?= G::$LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= G::$LoggedUser['torrent_pass'] ?>&amp;authkey=<?= G::$LoggedUser['AuthKey'] ?>" />

    <?
    if (isset(G::$LoggedUser['Notify'])) {
        foreach (G::$LoggedUser['Notify'] as $Filter) {
            list($FilterID, $FilterName) = $Filter;
    ?>
            <link rel="alternate" type="application/rss+xml" title="<?= CONFIG['SITE_NAME'] ?> - <?= display_str($FilterName) ?>" href="feeds.php?feed=torrents_notify_<?= $FilterID ?>_<?= G::$LoggedUser['torrent_pass'] ?>&amp;user=<?= G::$LoggedUser['ID'] ?>&amp;auth=<?= G::$LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= G::$LoggedUser['torrent_pass'] ?>&amp;authkey=<?= G::$LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode($FilterName) ?>" />
        <? } ?>
    <? } ?>
    <link rel="alternate" type="application/rss+xml" title="<?= CONFIG['SITE_NAME'] ?> - All Torrents" href="feeds.php?feed=torrents_all&amp;user=<?= G::$LoggedUser['ID'] ?>&amp;auth=<?= G::$LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= G::$LoggedUser['torrent_pass'] ?>&amp;authkey=<?= G::$LoggedUser['AuthKey'] ?>" />
    <link rel="alternate" type="application/rss+xml" title="<?= CONFIG['SITE_NAME'] ?> - Movie Torrents" href="feeds.php?feed=torrents_movie&amp;user=<?= G::$LoggedUser['ID'] ?>&amp;auth=<?= G::$LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= G::$LoggedUser['torrent_pass'] ?>&amp;authkey=<?= G::$LoggedUser['AuthKey'] ?>" />
    <meta name="viewport" content="width=device-width" />
    <?
    if (G::$LoggedUser['StyleURL']) {
        $StyleURLInfo = parse_url(G::$LoggedUser['StyleURL']);
        if (
            substr(G::$LoggedUser['StyleURL'], -4) == '.css'
            && $StyleURLInfo['query'] . $StyleURLInfo['fragment'] == ''
            && in_array($StyleURLInfo['host'], array(CONFIG['SITE_HOST']))
            && file_exists(CONFIG['SERVER_ROOT'] . $StyleURLInfo['path'])
        ) {
            $StyleURL = G::$LoggedUser['StyleURL'] . '?v=' . filemtime(CONFIG['SERVER_ROOT'] . '/public' . $StyleURLInfo['path']);
        } else {
            $StyleURL = G::$LoggedUser['StyleURL'];
        }
    ?>
        <link rel="stylesheet" type="text/css" title="External CSS" media="screen" href="<?= $StyleURL ?>" />
    <? } else { ?>
        <? if (CONFIG['IS_DEV']) { ?>
            <link rel="stylesheet" type="text/css" media="screen" href="/src/css/default/<?= G::$LoggedUser['StyleName'] ?>/index.css" />
        <? } else { ?>
            <link rel="stylesheet" type="text/css" media="screen" href="/app/themes/<?= G::$LoggedUser['StyleName'] ?>/index.css?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/app/themes/' . G::$LoggedUser['StyleName'] . '/index.css') ?>" />
        <? } ?>
    <? } ?>
    <script type="text/javascript">
        window.globalapp = {}
        window.DATA = {
            CONFIG: <?= json_encode($GLOBALS['WINDOW_CONFIG']) ?>
        }
        var authkey = "<?= G::$LoggedUser['AuthKey'] ?>"
        var userid = <?= G::$LoggedUser['ID'] ?>
    </script>
    <?
    $Scripts = array_merge(array('jquery', 'script_start', 'ajax.class', 'cookie.class', 'global', 'jquery.autocomplete', 'autocomplete', 'jquery.countdown.min', 'bbcode', 'news_ajax'), explode(',', $JSIncludes));
    foreach ($Scripts as $Script) {
        if (trim($Script) == '') {
            continue;
        }
    ?>
        <script src="<?= CONFIG['STATIC_SERVER'] ?>functions/<?= $Script ?>.js?v=<?= filemtime(CONFIG['SERVER_ROOT'] . '/public/static/functions/' . $Script . '.js') ?>" type="text/javascript"></script>
    <?
    }

    global $ClassLevels;
    // Get notifications early to change menu items if needed
    global $NotificationSpans;
    $NotificationsManager = new NotificationsManager(G::$LoggedUser['ID']);
    $Notifications = $NotificationsManager->get_notifications();
    $UseNoty = $NotificationsManager->use_noty();
    $NewSubscriptions = false;
    $NotificationSpans = array();
    foreach ($Notifications as $Type => $Notification) {
        if ($Type === NotificationsManager::SUBSCRIPTIONS) {
            $NewSubscriptions = true;
        }
        if ($UseNoty) {
            $NotificationSpans[] = "<span class=\"noty-notification\" style=\"display: none;\" data-noty-type=\"$Type\" data-noty-id=\"$Notification[id]\" data-noty-importance=\"$Notification[importance]\" data-noty-url=\"$Notification[url]\">$Notification[message]</span>";
        }
    }
    if ($UseNoty && !empty($NotificationSpans)) {
        NotificationsManagerView::load_js();
    }
    if ($NotificationsManager->is_skipped(NotificationsManager::SUBSCRIPTIONS)) {
        $NewSubscriptions = Subscriptions::has_new_subscriptions();
    }
    ?>
</head>

<?
//Start handling alert bars
$Alerts = array();
$ModBar = array();
// Important banner
if (isset(CONFIG['BANNER_URL']) && !empty(CONFIG['BANNER_URL'])) {
    $Alerts[] = "<a class='HeaderAnnounceItem-link' href='" . CONFIG['BANNER_URL'] . "'>" . CONFIG['BANNER_TEXT'][$CurrentLang]  . "</a>";
}

// Staff blog
if (check_perms('users_mod')) {
    global $SBlogReadTime, $LatestSBlogTime;
    if (!$SBlogReadTime && ($SBlogReadTime = G::$Cache->get_value('staff_blog_read_' . G::$LoggedUser['ID'])) === false) {
        G::$DB->query("
			SELECT Time
			FROM staff_blog_visits
			WHERE UserID = " . G::$LoggedUser['ID']);
        if (list($SBlogReadTime) = G::$DB->next_record()) {
            $SBlogReadTime = strtotime($SBlogReadTime);
        } else {
            $SBlogReadTime = 0;
        }
        G::$Cache->cache_value('staff_blog_read_' . G::$LoggedUser['ID'], $SBlogReadTime, 1209600);
    }
    if (!$LatestSBlogTime && ($LatestSBlogTime = G::$Cache->get_value('staff_blog_latest_time')) === false) {
        G::$DB->query("
			SELECT MAX(Time)
			FROM staff_blog");
        list($LatestSBlogTime) = G::$DB->next_record();
        if ($LatestSBlogTime) {
            $LatestSBlogTime = strtotime($LatestSBlogTime);
        } else {
            $LatestSBlogTime = 0;
        }
        G::$Cache->cache_value('staff_blog_latest_time', $LatestSBlogTime, 1209600);
    }
    if ($SBlogReadTime < $LatestSBlogTime) {
        $Alerts[] = '<a class="Button ButtonHeader" href="staffblog.php">New staff blog post!</a>';
    }
}

// Inbox
if ($NotificationsManager->is_traditional(NotificationsManager::INBOX)) {
    $NotificationsManager->load_inbox();
    $NewMessages = $NotificationsManager->get_notifications();
    if (isset($NewMessages[NotificationsManager::INBOX])) {
        $Alerts[] = NotificationsManagerView::format_traditional($NewMessages[NotificationsManager::INBOX]);
    }
    $NotificationsManager->clear_notifications_array();
}

if (G::$LoggedUser['RatioWatch']) {
    $Alerts[] = t('server.pub.ratio_watch_you_have', ['Values' => [time_diff(G::$LoggedUser['RatioWatchEnds'], 3)]]);
} elseif (G::$LoggedUser['CanLeech'] != 1) {
    $Alerts[] = t('server.pub.ratio_watch_your_dl_privileges');
}

// Torrents
if ($NotificationsManager->is_traditional(NotificationsManager::TORRENTS)) {
    $NotificationsManager->load_torrent_notifications();
    $NewTorrents = $NotificationsManager->get_notifications();
    if (isset($NewTorrents[NotificationsManager::TORRENTS])) {
        $Alerts[] = NotificationsManagerView::format_traditional($NewTorrents[NotificationsManager::TORRENTS]);
    }
    $NotificationsManager->clear_notifications_array();
}

if (check_perms('users_mod')) {
    $ModBar[] = '<a class="Button ButtonHeader"  href="tools.php">' . t('server.pub.toolbox') . '</a>';
}
if (check_perms('users_give_donor')) {
    $Count = $donation->getPendingDonationCount();
    if ($Count > 0) {
        $ModBar[] = "<a class='Button ButtonHeader' href='tools.php?action=prepaid_card'>" . $Count . t('server.donate.has_pending_donation') . "</a>";
    }
}
if (
    check_perms('users_mod')
    || G::$LoggedUser['PermissionID'] == CONFIG['USER_CLASS']['FORUM_MOD']
    || G::$LoggedUser['PermissionID'] == CONFIG['USER_CLASS']['TORRENT_MOD']
    || isset(G::$LoggedUser['ExtraClasses'][CONFIG['USER_CLASS']['FLS_TEAM']])
) {
    $NumStaffPMsArray = G::$Cache->get_value('num_staff_pms_' . G::$LoggedUser['ID']);
    if ($NumStaffPMsArray === false) {
        if (check_perms('users_mod')) {
            $LevelCap = 1000;
            G::$DB->query("
                            SELECT COUNT(ID)
                            FROM staff_pm_conversations
                            WHERE Status = 'Unanswered'
                            AND (AssignedToUser = " . G::$LoggedUser['ID'] . "
                                OR (LEAST('$LevelCap', Level) <= '" . G::$LoggedUser['EffectiveClass'] . "'))");
            list($NumStaffPMs) = G::$DB->next_record();
            G::$DB->query("
                        SELECT COUNT(ID)
                        FROM staff_pm_conversations
                        WHERE Status = 'Unanswered'
                        AND (AssignedToUser = " . G::$LoggedUser['ID'] . "
                            OR (LEAST('$LevelCap', Level) <= '" . G::$LoggedUser['EffectiveClass'] . "' AND Level >= " . $Classes[CONFIG['USER_CLASS']['FLS_TEAM']]['Level'] . "))");
            list($NumMyStaffPMs) = G::$DB->next_record();
            $NumStaffPMsArray = array($NumStaffPMs, $NumMyStaffPMs);
        }
        if (isset(G::$LoggedUser['ExtraClasses'][CONFIG['USER_CLASS']['FLS_TEAM']])) {
            G::$DB->query("
                            SELECT COUNT(ID)
                            FROM staff_pm_conversations
                            WHERE Status='Unanswered'
                                AND (AssignedToUser = " . G::$LoggedUser['ID'] . "
                                    OR Level = 0)");

            list($NumStaffPMs) = G::$DB->next_record();
            $NumStaffPMsArray = array($NumStaffPMs);
        }
        if (G::$LoggedUser['PermissionID'] == CONFIG['USER_CLASS']['FORUM_MOD'] || G::$LoggedUser['PermissionID'] == CONFIG['USER_CLASS']['TORRENT_MOD']) {
            G::$DB->query("
                            SELECT COUNT(ID)
                            FROM staff_pm_conversations
                            WHERE Status='Unanswered'
                                AND (AssignedToUser = " . G::$LoggedUser['ID'] . "
                                    OR Level <= '" . $Classes[G::$LoggedUser['PermissionID']]['Level'] . "')");

            list($NumStaffPMs) = G::$DB->next_record();
            G::$DB->query("
                        SELECT COUNT(ID)
                        FROM staff_pm_conversations
                        WHERE Status='Unanswered'
                            AND (AssignedToUser = " . G::$LoggedUser['ID'] . "
                                OR (Level <= '" . $Classes[G::$LoggedUser['PermissionID']]['Level'] . "' and level >= " . $Classes[CONFIG['USER_CLASS']['FORUM_MOD']]['Level'] . "))");

            list($NumMyStaffPMs) = G::$DB->next_record();
            $NumStaffPMsArray = array($NumStaffPMs, $NumMyStaffPMs);
        }
        G::$Cache->cache_value('num_staff_pms_' . G::$LoggedUser['ID'], $NumStaffPMsArray, 1000);
    }

    if ($NumStaffPMsArray[0] > 0) {
        if (isset($NumStaffPMsArray[1])) {
            $ModBar[] = '<a class="Button ButtonHeader"  href="staffpm.php">' . $NumStaffPMsArray[1] . "/" . $NumStaffPMsArray[0] . ' Staff PMs</a>';
        } else {
            $ModBar[] = '<a class="Button ButtonHeader"  href="staffpm.php">' . $NumStaffPMsArray[0] . ' Staff PMs</a>';
        }
    }
}
if (check_perms('admin_reports')) {
    // Torrent reports code
    $NumTorrentReports = G::$Cache->get_value('num_torrent_reportsv2');
    if ($NumTorrentReports === false) {
        G::$DB->query("
			SELECT COUNT(ID)
			FROM reportsv2
			WHERE Status = 'New'");
        list($NumTorrentReports) = G::$DB->next_record();
        G::$Cache->cache_value('num_torrent_reportsv2', $NumTorrentReports, 0);
    }

    $ModBar[] = '<a class="Button ButtonHeader"  href="reportsv2.php">' . $NumTorrentReports . t('server.pub.report') . '</a>';

    // Other reports code
    $NumOtherReports = G::$Cache->get_value('num_other_reports');
    if ($NumOtherReports === false) {
        G::$DB->query("
			SELECT COUNT(ID)
			FROM reports
			WHERE Status = 'New'");
        list($NumOtherReports) = G::$DB->next_record();
        G::$Cache->cache_value('num_other_reports', $NumOtherReports, 0);
    }

    if ($NumOtherReports > 0) {
        $ModBar[] = '<a class="Button ButtonHeader"  href="reports.php">' . $NumOtherReports . (($NumTorrentReports == 1) ? t('server.pub.other_report') : t('server.pub.other_reports')) . '</a>';
    }
} elseif (check_perms('project_team')) {
    $NumUpdateReports = G::$Cache->get_value('num_update_reports');
    if ($NumUpdateReports === false) {
        G::$DB->query("
			SELECT COUNT(ID)
			FROM reports
			WHERE Status = 'New'
				AND Type = 'request_update'");
        list($NumUpdateReports) = G::$DB->next_record();
        G::$Cache->cache_value('num_update_reports', $NumUpdateReports, 0);
    }

    if ($NumUpdateReports > 0) {
        $ModBar[] = '<a class="Button ButtonHeader"  href="reports.php">Request update reports</a>';
    }
} elseif (check_perms('site_moderate_forums')) {
    $NumForumReports = G::$Cache->get_value('num_forum_reports');
    if ($NumForumReports === false) {
        G::$DB->query("
			SELECT COUNT(ID)
			FROM reports
			WHERE Status = 'New'
				AND Type IN('artist_comment', 'collages_comment', 'post', 'requests_comment', 'thread', 'torrents_comment')");
        list($NumForumReports) = G::$DB->next_record();
        G::$Cache->cache_value('num_forum_reports', $NumForumReports, 0);
    }

    if ($NumForumReports > 0) {
        $ModBar[] = '<a class="Button ButtonHeader"  href="reports.php">' . $NumForumReports . (($NumForumReports == 1) ? ' Forum report' : ' Forum reports') . '</a>';
    }
}

if (check_perms('admin_manage_applicants')) {
    $NumNewApplicants = Applicant::new_applicant_count();
    if ($NumNewApplicants > 0) {
        $ModBar[] = '<a class="Button ButtonHeader" href="apply.php?action=view">'
            . t('server.apply.new_applicant', ['Count' => $NumNewApplicants, 'Values' => [$NumNewApplicants]])
            . '</a>';
    }

    $NumNewReplies = Applicant::new_reply_count();
    if ($NumNewReplies > 0) {
        $ModBar[] =
            '<a class="Button ButtonHeader" href="apply.php?action=view">'
            .  t('server.apply.new_applicant_reply', ['Count' => $NumNewReplies, 'Values' => [$NumNewReplies]])
            . '</a>';
    }
}

if (check_perms('users_mod') && CONFIG['FEATURE_EMAIL_REENABLE']) {
    $NumEnableRequests = G::$Cache->get_value(AutoEnable::CACHE_KEY_NAME);
    if ($NumEnableRequests === false) {
        G::$DB->query("SELECT COUNT(1) FROM users_enable_requests WHERE Outcome IS NULL");
        list($NumEnableRequests) = G::$DB->next_record();
        G::$Cache->cache_value(AutoEnable::CACHE_KEY_NAME, $NumEnableRequests);
    }

    if ($NumEnableRequests > 0) {
        $ModBar[] = '<a class="Button ButtonHeader"  href="tools.php?action=enable_requests">' . $NumEnableRequests . '&nbsp' . t('server.common.enable_requests') . "</a>";
    }
}
?>

<?
$BodyClass = 'browse';
if ($_REQUEST['action']) {
    $BodyClass = $_REQUEST['action'];
} elseif ($_REQUEST['type']) {
    $BodyClass = $_REQUEST['type'];
} elseif ($_REQUEST['id']) {
    $BodyClass = 'details';
}
?>

<body id="<?= $Document == 'collages' ? 'collage' : $Document ?>" class="<?= $BodyClass ?>">
    <button class="BackToTop" onclick="globalapp.backToTop()">
        <?= icon('back-to-top') ?>
    </button>
    <input id="extracb1" class="hidden" type="checkbox">
    <input id="extracb2" class="hidden" type="checkbox">
    <input id="extracb3" class="hidden" type="checkbox">
    <input id="extracb4" class="hidden" type="checkbox">
    <input id="extracb5" class="hidden" type="checkbox">
    <?
    $Avatar = G::$LoggedUser['Avatar'] ?: CONFIG['STATIC_SERVER'] . 'common/avatars/default.png';
    ?>
    <div class="LayoutPage <?= $PageClass ?>">
        <header class="LayoutPage-header Header <?= (!empty($Alerts) || !empty($ModBar)) ? 'is-hasAlerts' : '' ?>">
            <div class="HeaderInfo">
                <ul class="HeaderDonate HeaderInfo-left">
                    <li class="HeaderDonate-content HeaderInfo-item brackets <?= Format::add_class($PageID, array('donate'), 'active', false) ?>">
                        <a class="HeaderDonate-link LinkHeader Link" href="donate.php" data-tooltip="<?= t('server.donate.progress', ['Values' => [$donation->getYearProgress()]]) ?>">
                            <div class="HeaderDonate-progressBarBorder">
                                <div class="HeaderDonate-progressBar" style="width: <?= $donation->getYearProgress() . '%' ?>"></div>
                            </div>
                            <div class="HeaderDonate-percent"><?= t('server.donate.donate') ?></div>
                        </a>
                    </li>
                </ul>
                <ul class="HeaderStat HeaderInfo-middle">
                    <li class="HeaderStat-item is-seeding" data-tooltip="<?= t('server.common.uploaded') ?>">
                        <a class="HeaderStat-link LinkHeader Link" href="torrents.php?type=seeding&amp;userid=<?= G::$LoggedUser['ID'] ?>">
                            <?= icon('uploaded') ?>
                            <span class="HeaderStat-value is-uploaded" id="header-uploaded-value" data-value="<?= G::$LoggedUser['BytesUploaded'] ?>">
                                <?= Format::get_size(G::$LoggedUser['BytesUploaded']) ?>
                            </span>
                        </a>
                    </li>
                    <li class="HeaderStat-item is-leeching" data-tooltip="<?= t('server.common.downloaded') ?>">
                        <a class="HeaderStat-link LinkHeader Link" href="torrents.php?type=leeching&amp;userid=<?= G::$LoggedUser['ID'] ?>">
                            <?= icon('downloaded') ?>
                            <span class="HeaderStat-value is-downloaded" id="header-downloaded-value" data-value="<?= G::$LoggedUser['BytesDownloaded'] ?>">
                                <?= Format::get_size(G::$LoggedUser['BytesDownloaded']) ?>
                            </span>
                        </a>
                    </li>

                    <li class="HeaderStat-item is-ratio" data-tooltip="<?= t('server.common.ratio') ?>">
                        <a class="HeaderStat-link LinkHeader Link">
                            <?= icon('ratio') ?>
                            <span class="HeaderStat-value is-ratio" id="header-ratio-value" data-value="<?= Format::get_ratio(G::$LoggedUser['BytesUploaded'], G::$LoggedUser['BytesDownloaded']) ?>">
                                <?= Format::get_ratio_html(G::$LoggedUser['BytesUploaded'], G::$LoggedUser['BytesDownloaded'], true, false) ?>
                            </span>
                        </a>
                    </li>
                    <? if (((int) G::$LoggedUser['RequiredRatio']) !== 0) { ?>
                        <li class="HeaderStat-item is-requiredRatio" data-tooltip="<?= t('server.common.required_ratio') ?>">
                            <a class="HeaderStat-link LinkHeader Link" href="rules.php?p=ratio">
                                <?= icon('required-ratio') ?>
                                <span class="HeaderStat-value is-required-ratio" id="header-required-ratio-value" data-value="<?= G::$LoggedUser['RequiredRatio'] ?>">
                                    <?= number_format(G::$LoggedUser['RequiredRatio'], 2) ?>
                                </span>
                            </a>
                        </li>
                    <? } ?>
                    <li class="HeaderStat-item is-bp" data-tooltip="<?= t('server.common.bonus') ?>">
                        <a class="HeaderStat-link LinkHeader Link" href="/bonus.php?action=bprates">
                            <?= icon('bonus-active') ?>
                            <span class="HeaderStat-value is-bp" id="header-bp-value" data-value="<?= G::$LoggedUser['BonusPoints'] ?>">
                                <?= number_format(G::$LoggedUser['BonusPoints']) ?>
                            </span>
                        </a>
                    </li>
                    <?
                    if (G::$LoggedUser['FLTokens'] > 0) { ?>
                        <li class="HeaderStat-item is-bp" data-tooltip="<?= t('server.common.fltoken') ?>">
                            <a class="HeaderStat-link LinkHeader Link" href="userhistory.php?action=token_history&amp;userid=<?= G::$LoggedUser['ID'] ?>">
                                <?= icon('token') ?>
                                <span class="HeaderStat-value is-bp" id="header-bp-value" data-value="<?= G::$LoggedUser['TimedTokens'] + G::$LoggedUser['FLTokens'] ?>">
                                    <?
                                    $Tokens = G::$LoggedUser['TimedTokens'] == 0 ? G::$LoggedUser['FLTokens'] : (G::$LoggedUser['FLTokens'] - G::$LoggedUser['TimedTokens']) . '+' . G::$LoggedUser['TimedTokens'];
                                    echo $Tokens; ?>
                                </span>
                            </a>
                        </li>
                    <?    } ?>
                    <? if (CONFIG['ENABLE_HNR']) { ?>
                        <li class="HeaderStat-item isHnr">
                            <a class="HeaderStat-link LinkHeader Link" href="rules.php?p=ratio">
                                <i data-tooltip="<?= t('server.torrents.hit_and_run') ?>">
                                    <?= icon("User/hnr") ?>
                                </i>
                            </a>
                            <span>: </span>
                            <a class="HeaderStat-value is-hnrCount" id="header-hnr-count-value" data-value="<?= $Users::get_hnr_count(G::$LoggedUser['ID']) ?>" href="torrents.php?type=downloaded&userid=<?= G::$LoggedUser['ID'] ?>&view=1">
                                <?= Users::get_hnr_count(G::$LoggedUser['ID']) ?>
                            </a>
                        </li>
                    <? } ?>
                </ul>

                <?
                if (check_perms('site_send_unlimited_invites')) {
                    $Invites = ' (∞)';
                } elseif (G::$LoggedUser['Invites'] > 0) {
                    $Invites = G::$LoggedUser['TimedInvites'] == 0 ? ' (' . G::$LoggedUser['Invites'] . ')' : ' (' . (G::$LoggedUser['Invites'] - G::$LoggedUser['TimedInvites']) . '+' . G::$LoggedUser['TimedInvites'] . ')';
                } else {
                    $Invites = '';
                }
                ?>
                <ul class="HeaderQuickAction HeaderInfo-right">
                    <li class="HeaderQuickAction-item is-upload brackets <?= Format::add_class($PageID, array('upload'), 'active', false) ?>">
                        <a class="HeaderQuickAction-iconLink LinkHeader Link u-center u-heightFull" href="upload.php" data-tooltip="<?= t('server.common.menu_upload_title') ?>">
                            <?= icon('upload') ?>
                        </a>
                    </li>
                    <li class="HeaderQuickAction-item is-invite brackets <?= Format::add_class($PageID, array('user', 'invite'), 'active', false) ?>">
                        <a class='HeaderQuickAction-iconLink LinkHeader Link u-center u-heightFull' href="user.php?action=invite" data-tooltip="<?= t('server.common.invite') ?><?= $Invites ?>">
                            <?= icon('invite') ?>
                        </a>
                    </li>
                    <li class="HeaderQuickAction-item is-imageHost brackets">
                        <a class="HeaderQuickAction-iconLink LinkHeader Link u-center u-heightFull" href="upload.php?action=image" data-tooltip="<?= t('server.common.image_host') ?>">
                            <?= icon('image-host') ?>
                        </a>
                    </li>
                    <li class="HeaderQuickAction-item is-language brackets Dropdown Dropdown-trigger" data-tooltip="<?= t('server.common.language') ?>">
                        <a class="HeaderQuickAction-iconLink LinkHeader Link u-center u-heightFull">
                            <?= icon('Common/language') ?>
                        </a>
                        <form action="/" method="post">
                            <input type="hidden" name="action" value="change_language" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <div class="DropdownMenu Overlay">
                                <? foreach (Lang::LANGS as $Lang) { ?>
                                    <input class="DropdownMenu-item is-lang<?= $Lang ?>" type="submit" name="language" value="<?= t("server.common.lang_$Lang") ?>" />
                                <? } ?>
                            </div>
                        </form>
                    </li>
                    <li class="HeaderQuickAction-item is-profile brackets Dropdown">
                        <div class="HeaderProfile">
                            <a class="HeaderProfile-nameLink LinkHeader Link u-center u-heightFull" id="header-username-value" data-value="<?= G::$LoggedUser['Username'] ?>" href="user.php?id=<?= G::$LoggedUser['ID'] ?>">
                                <?= G::$LoggedUser['Username'] ?>
                            </a>
                            <span class="HeaderProfile-avatarContainer Dropdown-trigger u-center u-heightFull">
                                <img class="HeaderProfile-avatar" src="<?= $Avatar ?>" />
                                <?= icon('menuExtend', 'HeaderProfile-menuExtendIcon') ?>
                            </span>
                        </div>
                        <div class="DropdownMenu Overlay">
                            <a class="DropdownMenu-item is-profile" href="user.php?id=<?= G::$LoggedUser['ID'] ?>"><?= t('server.common.profile') ?></a>
                            <a class="DropdownMenu-item is-settings" href="user.php?action=edit&amp;userid=<?= G::$LoggedUser['ID'] ?>"><?= t('server.common.setting') ?></a>
                            <a class="DropdownMenu-item is-inbox" href="<?= Inbox::get_inbox_link(); ?>"> <?= t('server.common.inbox') ?></a>
                            <a class="DropdownMenu-item is-staffpm" href="staffpm.php"> <?= t('server.common.staffpm') ?></a>
                            <?
                            if (CONFIG['ENABLE_BADGE']) {
                            ?>
                                <a class="DropdownMenu-item is-badges" href="badges.php"> <?= t('server.common.my_badges') ?></a>
                            <?
                            }
                            ?>
                            <a class="DropdownMenu-item is-uploaded" href="torrents.php?type=uploaded&amp;userid=<?= G::$LoggedUser['ID'] ?>"> <?= t('server.common.my_uploaded') ?></a>
                            <a class="DropdownMenu-item is-bookmarks" href="bookmarks.php?type=torrents"> <?= t('server.common.my_bookmarks') ?></a>
                            <? if (check_perms('site_torrents_notify')) { ?> <a class="DropdownMenu-item is-notify" href="user.php?action=notify"> <?= t('server.common.my_notify') ?></a> <?    } ?>
                            <?
                            $ClassNames = $NewSubscriptions ? 'new-subscriptions' : '';
                            $ClassNames = trim($ClassNames . Format::add_class($PageID, array('userhistory', 'subscriptions'), 'active', false));
                            ?>
                            <a class="DropdownMenu-item is-subscriptions <?= $ClassNames ?>" href="userhistory.php?action=subscriptions"> <?= t('server.common.my_subscriptions') ?></a>
                            <a class="DropdownMenu-item is-comments" href="comments.php"> <?= t('server.common.my_comments') ?></a>
                            <a class="DropdownMenu-item is-friends" href="friends.php"> <?= t('server.common.my_friends') ?></a>
                            <a class="DropdownMenu-item is-missing" href="torrents.php?type=missing"> <?= t('server.common.missing') ?></a>
                            <a class="DropdownMenu-item is-logout" href="logout.php?auth=<?= G::$LoggedUser['AuthKey'] ?>"> <?= t('server.common.logout') ?></a>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="HeaderLogo">
                <a class="HeaderLogo-link Logo" href="index.php"></a>
            </div>

            <?
            if (!empty($Alerts) || !empty($ModBar)) { ?>
                <div class="HeaderAnnounce">
                    <? foreach ($Alerts as $Alert) { ?>
                        <div class="Header-announce"><?= $Alert ?></div>
                    <?
                    }
                    if (!empty($ModBar)) { ?>
                        <div class="HeaderAdmin">
                            <?= implode(' | ', $ModBar);
                            echo "\n" ?>
                        </div>
                    <?    } ?>
                </div>
            <?  } ?>

            <?
            if (isset(G::$LoggedUser['SearchType']) && G::$LoggedUser['SearchType']) { // Advanced search
                $UseAdvancedSearch = true;
            } else {
                $UseAdvancedSearch = false;
            }
            ?>
            <div class="HeaderSearch">
                <ul class="HeaderSearchList">
                    <li class="HeaderSearchList-item" id="searchbar_torrents">
                        <span class="hidden">Torrents: </span>
                        <form class="HeaderSearch-form" name="torrents" action="torrents.php" method="get">
                            <? if ($UseAdvancedSearch) { ?>
                                <input type="hidden" name="action" value="advanced" />
                            <?    } ?>
                            <input class="Input InputHeader" type="text" id="torrentssearch" autocomplete="off" <?= Users::has_autocomplete_enabled('search');
                                                                                                                ?> accesskey="t" spellcheck="false" placeholder="<?= t('server.index.moviegroups') ?>" name="<?= $UseAdvancedSearch ? 'groupname' : 'searchstr' ?>" size="17" />
                        </form>
                    </li>
                    <li class="HeaderSearchList-item" id="searchbar_artists">
                        <span class="hidden">Artist: </span>
                        <form class="HeaderSearch-form" name="artists" action="artist.php" method="get">
                            <input class="Input InputHeader" id="artistsearch" autocomplete="off" <?= Users::has_autocomplete_enabled('search');
                                                                                                    ?> accesskey="a" spellcheck="false" placeholder="<?= t('server.common.artists') ?>" type="text" name="artistname" size="17" />
                        </form>
                    </li>
                    <li class="HeaderSearchList-item" id="searchbar_requests">
                        <span class="hidden">Requests: </span>
                        <form class="HeaderSearch-form" name="requests" action="requests.php" method="get">
                            <input class="Input InputHeader" type="text" id="requestssearch" spellcheck="false" accesskey="r" placeholder="<?= t('server.common.requests') ?>" name="search" size="17" />
                        </form>
                    </li>
                    <li class="HeaderSearchList-item" id="searchbar_forums">
                        <span class="hidden">Forums: </span>
                        <form class="HeaderSearch-form" name="forums" action="forums.php" method="get">
                            <input value="search" type="hidden" name="action" />
                            <input class="Input InputHeader" type="text" id="forumssearch" accesskey="f" placeholder="<?= t('server.common.forums') ?>" name="search" size="17" />
                        </form>
                    </li>
                    <li class="HeaderSearchList-item" id="searchbar_log">
                        <span class="hidden">Log: </span>
                        <form class="HeaderSearch-form" name="log" action="log.php" method="get">
                            <input class="Input InputHeader" type="text" id="logsearch" accesskey="l" placeholder="<?= t('server.common.log') ?>" name="search" size="17" />
                        </form>
                    </li>
                    <li class="HeaderSearchList-item" id="searchbar_users">
                        <span class="hidden">Users: </span>
                        <form class="HeaderSearch-form" name="users" action="user.php" method="get">
                            <input type="hidden" name="action" value="search" />
                            <input class="Input InputHeader" type="text" id="userssearch" accesskey="u" placeholder="<?= t('server.common.users') ?>" name="search" size="20" />
                        </form>
                    </li>
                </ul>
            </div>


            <div class="HeaderNav">
                <ul class="HeaderNavList">
                    <li class="HeaderNavList-item" id="nav_torrents" <?=
                                                                        Format::add_class($PageID, array('torrents', false, false), 'active', true) ?>>
                        <a class="HeaderNav-link LinkHeader Link" href="torrents.php">
                            <?= t('server.index.moviegroups') ?></a>
                    </li>
                    <?
                    if (CONFIG['ENABLE_COLLAGES']) {
                    ?>
                        <li class="HeaderNavList-item" id="nav_collages" <?=
                                                                            Format::add_class($PageID, array('collages'), 'active', true) ?>>
                            <a class="HeaderNav-link LinkHeader Link" href="collages.php">
                                <?= t('server.common.collages') ?></a>
                        </li>
                    <?
                    }
                    ?>
                    <li class="HeaderNavList-item" id="nav_requests" <?=
                                                                        Format::add_class($PageID, array('requests'), 'active', true) ?>>
                        <a class="HeaderNav-link LinkHeader Link" href="requests.php">
                            <?= t('server.common.requests') ?></a>
                    </li>
                    <li class="HeaderNavList-item" id="nav_forums" <?=
                                                                    Format::add_class($PageID, array('forums'), 'active', true) ?>>
                        <a class="HeaderNav-link LinkHeader Link" href="forums.php">
                            <?= t('server.common.forums') ?></a>
                    </li>
                    <li class="HeaderNavList-item" id="nav_top10" <?=
                                                                    Format::add_class($PageID, array('top10'), 'active', true) ?>>
                        <a class="HeaderNav-link LinkHeader Link" href="top10.php">
                            <?= t('server.common.top_10') ?></a>
                    </li>
                    <li class="HeaderNavList-item" id="nav_rules" <?=
                                                                    Format::add_class($PageID, array('rules'), 'active', true) ?>>
                        <a class="HeaderNav-link LinkHeader Link" href="rules.php">
                            <?= t('server.common.rules') ?></a>
                    </li>
                    <li class="HeaderNavList-item" id="nav_wiki" <?=
                                                                    Format::add_class($PageID, array('wiki'), 'active', true) ?>>
                        <a class="HeaderNav-link LinkHeader Link" href="wiki.php">
                            <?= t('server.common.wiki') ?></a>
                    </li>
                    <li class="HeaderNavList-item" id="nav_staff" <?=
                                                                    Format::add_class($PageID, array('staff'), 'active', true) ?>>
                        <a class="HeaderNav-link LinkHeader Link" href="staff.php">
                            <?= t('server.common.staff') ?></a>
                    </li>
                </ul>
            </div>
        </header>

        <div class="LayoutPage-body Body">