<?
// Main feeds page
// The feeds don't use script_start.php, their code resides entirely in feeds.php in the document root
// Bear this in mind when you try to use script_start functions.

if (
    empty($_GET['feed'])
    || empty($_GET['authkey'])
    || empty($_GET['auth'])
    || empty($_GET['passkey'])
    || empty($_GET['user'])
    || !is_number($_GET['user'])
    || strlen($_GET['authkey']) !== 32
    || strlen($_GET['passkey']) !== 32
    || strlen($_GET['auth']) !== 32
) {
    $Feed->open_feed();
    $Feed->channel('Blocked', 'RSS feed.');
    $Feed->close_feed();
    die();
}

$User = (int)$_GET['user'];

if (!$Enabled = $Cache->get_value("enabled_$User")) {
    $DB->query("
		SELECT Enabled
		FROM users_main
		WHERE ID = '$User'");
    list($Enabled) = $DB->next_record();
    $Cache->cache_value("enabled_$User", $Enabled, 0);
}

if (md5($User . CONFIG['RSS_HASH'] . $_GET['passkey']) !== $_GET['auth'] || $Enabled != 1) {
    $Feed->open_feed();
    $Feed->channel('Blocked', 'RSS feed.');
    $Feed->close_feed();
    die();
}
require(CONFIG['SERVER_ROOT'] . '/classes/text.class.php');
$Feed->open_feed();
switch ($_GET['feed']) {
    case 'feed_news':
        $Feed->channel('News', 'RSS feed for site news.');
        if (!$News = $Cache->get_value('news')) {
            $DB->query("
				SELECT
					ID,
					Title,
					Body,
					Time
				FROM news
				ORDER BY Time DESC
				LIMIT 10");
            $News = $DB->to_array(false, MYSQLI_NUM, false);
            $Cache->cache_value('news', $News, 1209600);
        }
        $Count = 0;
        foreach ($News as $NewsItem) {
            list($NewsID, $Title, $Body, $NewsTime) = $NewsItem;
            if (strtotime($NewsTime) >= time()) {
                continue;
            }
            echo $Feed->item($Title, Text::full_format($Body), "index.php#news$NewsID", CONFIG['SITE_NAME'] . ' Staff', '', '', $NewsTime);
            if (++$Count > 4) {
                break;
            }
        }
        break;
    case 'feed_blog':
        $Feed->channel('Blog', 'RSS feed for site blog.');
        if (!$Blog = $Cache->get_value('blog')) {
            $DB->query("
				SELECT
					b.ID,
					um.Username,
					b.UserID,
					b.Title,
					b.Body,
					b.Time,
					b.ThreadID
				FROM blog AS b
					LEFT JOIN users_main AS um ON b.UserID = um.ID
				ORDER BY Time DESC
				LIMIT 20");
            $Blog = $DB->to_array(false, MYSQLI_BOTH, false);
            $Cache->cache_value('blog', $Blog, 1209600);
        }
        foreach ($Blog as $BlogItem) {
            list($BlogID, $Author, $AuthorID, $Title, $Body, $BlogTime, $ThreadID) = $BlogItem;
            if ($ThreadID) {
                echo $Feed->item($Title, Text::full_format($Body), "forums.php?action=viewthread&amp;threadid=$ThreadID", CONFIG['SITE_NAME'] . ' Staff', '', '', $BlogTime);
            } else {
                echo $Feed->item($Title, Text::full_format($Body), "blog.php#blog$BlogID", CONFIG['SITE_NAME'] . ' Staff', '', '', $BlogTime);
            }
        }
        break;
    case 'feed_changelog':
        $Feed->channel('Gazelle Change Log', 'RSS feed for Gazelle\'s changelog.');
        if (!$Changelog = $Cache->get_value('changelog')) {
            $DB->query("
				SELECT Message, Author, Date(Time)
				FROM changelog
				ORDER BY Time DESC
				LIMIT 20");
            $Changelog = $DB->to_array(false, MYSQLI_BOTH, false);
            $Cache->cache_value('changelog', $Changelog, 3600);
        }
        foreach ($Changelog as $Change) {
            list($Message, $Author, $Date) = $Change;
            echo $Feed->item("$Date by $Author", Text::full_format($Message), 'tools.php?action=change_log', CONFIG['SITE_NAME'] . ' Staff', '', '', $Date);
        }
        break;
    case 'torrents_all':
        $Feed->channel('All Torrents', 'RSS feed for all new torrent uploads.');
        $Feed->retrieve('torrents_all', $_GET['authkey'], $_GET['passkey']);
        break;
    case 'torrents_movie':
        $Feed->channel('Movie Torrents', 'RSS feed for all new movie torrents.');
        $Feed->retrieve('torrents_movie', $_GET['authkey'], $_GET['passkey']);
        break;
    case 'torrents_free':
        $Feed->channel('Free Torrents', 'RSS feed for all new free torrent.');
        $Feed->retrieve('torrents_free', $_GET['authkey'], $_GET['passkey']);
        break;
    default:
        // Personalized torrents
        if (empty($_GET['name']) && substr($_GET['feed'], 0, 16) == 'torrents_notify_') {
            // All personalized torrent notifications
            $Feed->channel('Personalized torrent notifications', 'RSS feed for personalized torrent notifications.');
            $Feed->retrieve($_GET['feed'], $_GET['authkey'], $_GET['passkey']);
        } elseif (!empty($_GET['name']) && substr($_GET['feed'], 0, 16) == 'torrents_notify_') {
            // Specific personalized torrent notification channel
            $Feed->channel(display_str($_GET['name']), 'Personal RSS feed: ' . display_str($_GET['name']));
            $Feed->retrieve($_GET['feed'], $_GET['authkey'], $_GET['passkey']);
        } elseif (substr($_GET['feed'], 0, 21) == 'torrents_bookmarks_t_') {
            // Bookmarks
            $Feed->channel('Bookmarked torrent notifications', 'RSS feed for bookmarked torrents.');
            $Feed->retrieve($_GET['feed'], $_GET['authkey'], $_GET['passkey']);
        } else {
            $Feed->channel('All Torrents', 'RSS feed for all new torrent uploads.');
            $Feed->retrieve('torrents_all', $_GET['authkey'], $_GET['passkey']);
        }
}
$Feed->close_feed();
