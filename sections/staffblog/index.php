<? View::show_header(Lang::get('blog', 'staff_blog'), 'bbcode', 'PageStaffBlogHome'); ?>
<div class="LayoutBody">
    <?
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

    define('ANNOUNCEMENT_FORUM_ID', 50);

    if (check_perms('admin_manage_blog')) {
        if (!empty($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case 'takeeditblog':
                    authorize();
                    if (empty($_POST['title'])) {
                        error(Lang::get('blog', 'please_enter_a_title'));
                    }
                    if (is_number($_POST['blogid'])) {
                        $DB->query("
						UPDATE staff_blog
						SET Title = '" . db_string($_POST['title']) . "', Body = '" . db_string($_POST['body']) . "'
						WHERE ID = '" . db_string($_POST['blogid']) . "'");
                        $Cache->delete_value('staff_blog');
                        $Cache->delete_value('staff_feed_blog');
                        Misc::create_thread(ANNOUNCEMENT_FORUM_ID, G::$LoggedUser['ID'], $_POST['title'], $_POST['body']);
                    }
                    header('Location: staffblog.php');
                    break;
                case 'editblog':
                    if (is_number($_GET['id'])) {
                        $BlogID = $_GET['id'];
                        $DB->query("
						SELECT Title, Body
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
                    if (empty($_POST['title'])) {
                        error(Lang::get('blog', 'please_enter_a_title'));
                    }
                    $Title = db_string($_POST['title']);
                    $Body = db_string($_POST['body']);

                    $DB->query("
					INSERT INTO staff_blog
						(UserID, Title, Body, Time)
					VALUES
						('$LoggedUser[ID]', '" . db_string($_POST['title']) . "', '" . db_string($_POST['body']) . "', NOW())");
                    $Cache->delete_value('staff_blog');
                    $Cache->delete_value('staff_blog_latest_time');

                    send_irc("PRIVMSG " . ADMIN_CHAN . " :!mod New staff blog: " . $_POST['title'] . " - " . site_url() . "/staffblog.php#blog" . $DB->inserted_id());

                    Misc::create_thread(ANNOUNCEMENT_FORUM_ID, G::$LoggedUser['ID'], $_POST['title'], $_POST['body']);

                    header('Location: staffblog.php');
                    break;
            }
        }
    ?>
        <script>
            function Quick_Preview() {
                if ($("#preview_button")[0].value == "Preview") {
                    ajax.post("ajax.php?action=preview", 'blog_post', function(response) {
                        $('#quickpost').ghide();
                        $('#preview').raw().innerHTML = response;
                        $('#preview').gshow();
                        $("#preview_button")[0].value = "Edit"
                    });
                } else {
                    $("#preview_button")[0].value = "Preview"
                    $('#preview').ghide();
                    $('#quickpost').gshow();
                }
            }
        </script>
        <div class="LayoutBody">
            <div class="BodyHeader">
                <div class="head">
                    <?= ((empty($_GET['action'])) ? Lang::get('blog', 'create_staff_blog_post') : Lang::get('blog', 'edit_staff_blog_post')) ?>
                    <span style="float: right;">
                        <a href="#" onclick="$('#postform').gtoggle(); this.innerHTML = (this.innerHTML == '<?= Lang::get('global', 'hide') ?>' ? '<?= Lang::get('global', 'show') ?>' : '<?= Lang::get('global', 'hide') ?>'); return false;" class="brackets"><?= ((!isset($_REQUEST['action']) || $_REQUEST['action'] != 'editblog') ? Lang::get('global', 'show') : Lang::get('global', 'hide')) ?></a>
                    </span>
                </div>
                <form class="<?= ((empty($_GET['action'])) ? 'create_form' : 'edit_form') ?>" id="blog_post" name="blog_post" action="staffblog.php" method="post">
                    <div id="postform" class="pad<?= (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'editblog') ? ' hidden' : '' ?>">
                        <input type="hidden" name="action" value="<?= ((empty($_GET['action'])) ? 'takenewblog' : 'takeeditblog') ?>" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <? if (!empty($_GET['action']) && $_GET['action'] == 'editblog') { ?>
                            <input type="hidden" name="blogid" value="<?= $BlogID; ?>" />
                        <?      } ?>
                        <div class="field_div">
                            <h3><?= Lang::get('blog', 'title') ?></h3>
                            <input class="Input" type="text" name="title" size="95" <? if (!empty($Title)) {
                                                                                        echo ' value="' . display_str($Title) . '"';
                                                                                    } ?> />
                        </div>
                        <div class="field_div">
                            <h3><?= Lang::get('blog', 'body') ?></h3>

                            <textarea class="Input" id="quickpost" name="body" cols="95" rows="15"><? if (!empty($Body)) {
                                                                                                        echo display_str($Body);
                                                                                                    } ?></textarea> <br />
                            <div id="preview"></div>
                        </div>
                        <div class="submit_div center">
                            <input id="preview_button" type="button" value="Preview" onclick="Quick_Preview();">
                            <input class="Button" type="submit" value="<?= ((!isset($_GET['action'])) ? Lang::get('blog', 'create_a_blog_post') : Lang::get('blog', 'edit_blog_post')) ?>" />
                        </div>
                    </div>
                </form>
            </div>
        <?  } ?>
        <?
        if (($Blog = $Cache->get_value('staff_blog')) === false) {
            $DB->query("
		SELECT
			b.ID,
			um.Username,
			b.Title,
			b.Body,
			b.Time
		FROM staff_blog AS b
			LEFT JOIN users_main AS um ON b.UserID = um.ID
		ORDER BY Time DESC");
            $Blog = $DB->to_array(false, MYSQLI_NUM);
            $Cache->cache_value('staff_blog', $Blog, 1209600);
        }
        ?>

        <div class="PostList PostListStaffBlog">
            <?
            foreach ($Blog as $BlogItem) {
                list($BlogID, $Author, $Title, $Body, $BlogTime) = $BlogItem;
                $BlogTime = strtotime($BlogTime);
            ?>
                <div class="Post Box" id="blog<?= $BlogID ?>">
                    <div class="Post-header Box-header">
                        <div class="Post-headerLeft">
                            <span class="Post-headerTitle"><?= $Title ?></span> -
                            <span><?= Lang::get('blog', 'posted') ?></span>
                            <?= time_diff($BlogTime); ?>
                            <?= Lang::get('blog', 'by') ?> <a href="user.php?name=<?= $Author ?>"><?= $Author ?></a>
                        </div>
                        <div class="Post-headerActions">
                            <? if (check_perms('admin_manage_blog')) { ?>
                                - <a href="staffblog.php?action=editblog&amp;id=<?= $BlogID ?>" class="brackets"><?= Lang::get('global', 'edit') ?></a>
                                <a href="staffblog.php?action=deleteblog&amp;id=<?= $BlogID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" onclick="return confirm('<?= Lang::get('blog', 'do_you_want_to_delete_this') ?>');" class="brackets"><?= Lang::get('global', 'delete') ?></a>
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
        ?>