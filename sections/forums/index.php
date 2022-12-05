<?
enforce_login();

if (!empty($LoggedUser['DisableForums'])) {
    error(403);
}

$Forums = Forums::get_forums();
$ForumCats = Forums::get_forum_categories();

G::$Router->addGet('', CONFIG['SERVER_ROOT'] . '/sections/forums/browse.php');

G::$Router->addPost('reply', CONFIG['SERVER_ROOT'] . '/sections/forums/take_reply.php');
G::$Router->addPost('new', CONFIG['SERVER_ROOT'] . '/sections/forums/take_new_thread.php');
G::$Router->addPost('mod_thread', CONFIG['SERVER_ROOT'] . '/sections/forums/mod_thread.php');
G::$Router->addPost('poll_mod', CONFIG['SERVER_ROOT'] . '/sections/forums/poll_mod.php');
G::$Router->addPost('add_poll_option', CONFIG['SERVER_ROOT'] . '/sections/forums/add_poll_option.php');
G::$Router->addPost('warn', CONFIG['SERVER_ROOT'] . '/sections/forums/warn.php');
G::$Router->addPost('take_warn', CONFIG['SERVER_ROOT'] . '/sections/forums/take_warn.php');
G::$Router->addPost('take_topic_notes', CONFIG['SERVER_ROOT'] . '/sections/forums/take_topic_notes.php');
G::$Router->addPost('takeedit', CONFIG['SERVER_ROOT'] . '/sections/forums/takeedit.php');
G::$Router->addPost('edit_rules', CONFIG['SERVER_ROOT'] . '/sections/forums/edit_rules.php');

G::$Router->addGet('viewforum', CONFIG['SERVER_ROOT'] . '/sections/forums/forum.php');
G::$Router->addGet('viewthread', CONFIG['SERVER_ROOT'] . '/sections/forums/thread.php');
G::$Router->addGet('viewtopic', CONFIG['SERVER_ROOT'] . '/sections/forums/thread.php');
G::$Router->addGet('ajax_get_edit', CONFIG['SERVER_ROOT'] . '/sections/forums/ajax_get_edit.php');
G::$Router->addGet('ajax_get_jf', CONFIG['SERVER_ROOT'] . '/sections/forums/ajax_get_jf.php');
G::$Router->addGet('ajax_refresh', CONFIG['SERVER_ROOT'] . '/sections/forums/ajax_refresh.php');
G::$Router->addGet('new', CONFIG['SERVER_ROOT'] . '/sections/forums/newthread.php');
G::$Router->addGet('takeedit', CONFIG['SERVER_ROOT'] . '/sections/forums/takeedit.php');
G::$Router->addGet('get_post', CONFIG['SERVER_ROOT'] . '/sections/forums/get_post.php');
G::$Router->addGet('delete', CONFIG['SERVER_ROOT'] . '/sections/forums/delete.php');
G::$Router->addGet('catchup', CONFIG['SERVER_ROOT'] . '/sections/forums/catchup.php');
G::$Router->addGet('search', CONFIG['SERVER_ROOT'] . '/sections/forums/search.php');
G::$Router->addGet('change_vote', CONFIG['SERVER_ROOT'] . '/sections/forums/change_vote.php');
G::$Router->addGet('delete_poll_option', CONFIG['SERVER_ROOT'] . '/sections/forums/delete_poll_option.php');
G::$Router->addGet('sticky_post', CONFIG['SERVER_ROOT'] . '/sections/forums/sticky_post.php');
G::$Router->addGet('edit_rules', CONFIG['SERVER_ROOT'] . '/sections/forums/edit_rules.php');
//G::$Router->addGet('thread_subscribe', '');
G::$Router->addGet('warn', CONFIG['SERVER_ROOT'] . '/sections/forums/warn.php');
