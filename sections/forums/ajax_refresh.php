<?
if (!check_perms('admin_manage_news')) {
    error(403);
}
if (isset($_GET['news_flush'])) {
    authorize();
    G::$Cache->delete_value('news');
    G::$Cache->delete_value('news_latest_id');
    G::$Cache->delete_value('news_latest_title');
    NotificationsManager::send_push(NotificationsManager::get_push_enabled_users(), site_url() . 'index.php', NotificationsManager::NEWS);
}
if (isset($_GET['news_flush_lite'])) {
    authorize();
    $Cache->delete_value('news');
    $Cache->delete_value('feed_news');
}
echo '{}';
