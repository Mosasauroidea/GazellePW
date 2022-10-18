<?
View::show_header(t('server.blog.staff_blog'), 'bbcode', 'PageStaffBlogHome');

enforce_login();

if (!check_perms('users_mod')) {
    error(403);
}

$DB->query("
	INSERT INTO staff_blog_visits
		(UserID, Time)
	VALUES
		(" . $LoggedUser['ID'] . ", NOW())
	ON DUPLICATE KEY UPDATE
		Time = NOW()");
$Cache->delete_value('staff_blog_read_' . $LoggedUser['ID']);


if (check_perms('admin_manage_blog')) {
    if (!empty($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
            case 'takeeditblog':
                authorize();
                if (empty($_POST['title'])) {
                    error(t('server.blog.please_enter_a_title'));
                }
                if (is_number($_POST['blogid'])) {
                    $DB->query("
						UPDATE staff_blog
						SET Title = '" . db_string($_POST['title']) . "', 
                        Body = '" . db_string($_POST['body']) . "'
						WHERE ID = '" . db_string($_POST['blogid']) . "'");
                    $Cache->delete_value('staff_blog');
                    $Cache->delete_value('staff_feed_blog');
                }
                header('Location: staffblog.php');
                break;
            case 'editblog':
                if (is_number($_GET['id'])) {
                    $BlogID = $_GET['id'];
                    $DB->query("
						SELECT Title, Body, ThreadID
						FROM staff_blog
						WHERE ID = $BlogID");
                    list($Title, $Body, $ThreadID) = $DB->next_record();
                }
                break;
            case 'deleteblog':
                if (is_number($_GET['id'])) {
                    authorize();
                    $DB->query("
						DELETE FROM staff_blog
						WHERE ID = '" . db_string($_GET['id']) . "'");
                    $Cache->delete_value('staff_blog');
                    $Cache->delete_value('staff_feed_blog');
                }
                header('Location: staffblog.php');
                break;

            case 'takenewblog':
                authorize();
                $ThreadID = Misc::create_thread(CONFIG['STAFF_BLOG_FORUM'], G::$LoggedUser['ID'], $_POST['title'], $_POST['body']);
                if ($ThreadID < 1) {
                    error(0);
                }

                if (empty($_POST['title'])) {
                    error(t('server.blog.please_enter_a_title'));
                }
                $Title = db_string($_POST['title']);
                $Body = db_string($_POST['body']);

                $DB->query("
					INSERT INTO staff_blog
						(UserID, Title, Body, Time, ThreadID)
					VALUES
						('$LoggedUser[ID]', '" . db_string($_POST['title']) . "', '" . db_string($_POST['body']) . "', NOW(), $ThreadID)");
                $Cache->delete_value('staff_blog');
                $Cache->delete_value('staff_blog_latest_time');

                send_irc("PRIVMSG " . CONFIG['ADMIN_CHAN'] . " :!mod New staff blog: " . $_POST['title'] . " - " . site_url() . "/staffblog.php#blog" . $DB->inserted_id());
                header('Location: staffblog.php');
                break;
        }
    }
}
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.blog.staff_blog') ?>
        </div>
    </div>

    <form class="<?= ((empty($_GET['action'])) ? 'create_form' : 'edit_form') ?>" id="blog_post" name="blog_post" action="staffblog.php" method="post">
        <input type="hidden" name="action" value="<?= ((empty($_GET['action'])) ? 'takenewblog' : 'takeeditblog') ?>" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <? if (!empty($_GET['action']) && $_GET['action'] == 'editblog') { ?>
            <input type="hidden" name="blogid" value="<?= $BlogID; ?>" />
        <?
        } ?>
        <div id="postform" variant="header" class="Form-rowList pad">
            <div class="Form-rowHeader">
                <?= ((empty($_GET['action'])) ? t('server.blog.create_staff_blog_post') : t('server.blog.edit_staff_blog_post')) ?>
            </div>

            <div class="Form-row">
                <div class="Form-label"><?= t('server.blog.title') ?></div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="title" size="95" <? if (!empty($Title)) {
                                                                                echo ' value="' . display_str($Title) . '"';
                                                                            } ?> />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.blog.body') ?></div>
                <div class="Form-items">
                    <?php new TEXTAREA_PREVIEW('body', 'quickpost', display_str($Body), 60, 8, true, true, false); ?>
                </div>
            </div>
            <div class="Form-row">
                <input class="Button" type="submit" value="<?= ((!isset($_GET['action'])) ? t('server.blog.create_a_blog_post') : t('server.blog.edit_blog_post')) ?>" />
            </div>
        </div>
    </form>
    <?
    if (($Blog = $Cache->get_value('staff_blog')) === false) {
        $DB->query("
		SELECT
			b.ID,
			um.Username,
			b.Title,
			b.Body,
			b.Time,
            b.ThreadID
		FROM staff_blog AS b
			LEFT JOIN users_main AS um ON b.UserID = um.ID
		ORDER BY Time DESC");
        $Blog = $DB->to_array(false, MYSQLI_NUM);
        $Cache->cache_value('staff_blog', $Blog, 1209600);
    }
    ?>
    <div class="PostList PostListStaffBlog LayoutBody">
        <?
        foreach ($Blog as $BlogItem) {
            list($BlogID, $Author, $Title, $Body, $BlogTime, $ThreadID) = $BlogItem;
            $BlogTime = strtotime($BlogTime);
        ?>
            <div class="Post" id="blog<?= $BlogID ?>">
                <div class="Post-header">
                    <div class="Post-headerLeft">
                        <span class="Post-headerTitle"><?= $Title ?></span>
                        - <?= time_diff($BlogTime); ?>
                    </div>
                    <div class="Post-headerActions">
                        <a href="forums.php?action=viewthread&amp;threadid=<?= $ThreadID ?>"><?= t('server.index.discuss') ?></a>
                        <? if (check_perms('admin_manage_blog')) { ?>
                            - <a href="staffblog.php?action=editblog&amp;id=<?= $BlogID ?>" class="brackets"><?= t('server.common.edit') ?></a>
                            - <a href="staffblog.php?action=deleteblog&amp;id=<?= $BlogID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" onclick="return confirm('<?= t('server.blog.do_you_want_to_delete_this') ?>');" class="brackets"><?= t('server.common.delete') ?></a>
                        <? } ?>
                    </div>
                </div>
                <div class="Post-body Box-body HtmlText">
                    <?= Text::full_format($Body) ?>
                </div>
            </div>
        <?  } ?>
    </div>

</div>
<?
View::show_footer();
