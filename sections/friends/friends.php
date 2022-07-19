<?php

/************************************************************************
//------------// Main friends page //----------------------------------//
This page lists a user's friends.

There's no real point in caching this page. I doubt users load it that
much.
 ************************************************************************/

// Number of users per page
define('FRIENDS_PER_PAGE', '20');
include_once(CONFIG['SERVER_ROOT'] . '/classes/paranoia.class.php');



View::show_header(Lang::get('friends', 'friends'), 'comments', 'PageFriendHome');


$UserID = $LoggedUser['ID'];


$Select = "f.FriendID";
$Where = "f.UserID = '$UserID'";
$Join1 = "f.FriendID = m.ID";
$Join2 = "f.FriendID = i.UserID";



list($Page, $Limit) = Format::page_limit(FRIENDS_PER_PAGE);

// Main query
$DB->query("
	SELECT
		SQL_CALC_FOUND_ROWS
		$Select,
		f.Comment,
		m.Username,
		m.Uploaded,
		m.Downloaded,
		m.PermissionID,
		m.Paranoia,
		m.LastAccess,
		i.Avatar
	FROM friends AS f
		JOIN users_main AS m ON $Join1
		JOIN users_info AS i ON $Join2
	WHERE $Where
	ORDER BY Username
	LIMIT $Limit");
$Friends = $DB->to_array(false, MYSQLI_BOTH, array(6, 'Paranoia'));

// Number of results (for pagination)
$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();

// Start printing stuff
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('friends', 'friends_list') ?></h2>
    </div>
    <div class="BodyNavLinks">
        <?
        // Pagination
        $Pages = Format::get_pages($Page, $Results, FRIENDS_PER_PAGE, 9);
        echo $Pages;
        ?>
    </div>
    <div class="Box">
        <div class="Box-body">
            <?
            if ($Results == 0) {
                echo '<p>' . Lang::get('friends', 'you_have_no_friends') . '</p>';
            }
            // Start printing out friends
            foreach ($Friends as $Friend) {
                list($FriendID, $Comment, $Username, $Uploaded, $Downloaded, $Class, $Paranoia, $LastAccess, $Avatar) = $Friend;
            ?>
                <form class="manage_form" name="friends" action="friends.php" method="post">
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <div class="TableContainer">
                        <table class="TableUserFriend Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell" colspan="<?= (Users::has_avatars_enabled() ? 3 : 2) ?>">
                                    <span style="float: left;"><?= Users::format_username($FriendID, true, true, true, true) ?>
                                        <? if (check_paranoia('ratio', $Paranoia, $Class, $FriendID)) { ?>
                                            &nbsp;<?= Lang::get('friends', 'ratio') ?>: <strong><?= Format::get_ratio_html($Uploaded, $Downloaded) ?></strong>
                                        <?
                                        }
                                        if (check_paranoia('uploaded', $Paranoia, $Class, $FriendID)) {
                                        ?>
                                            &nbsp;<?= Lang::get('friends', 'up') ?>: <strong><?= Format::get_size($Uploaded) ?></strong>
                                        <?
                                        }
                                        if (check_paranoia('downloaded', $Paranoia, $Class, $FriendID)) {
                                        ?>
                                            &nbsp;<?= Lang::get('friends', 'down') ?>: <strong><?= Format::get_size($Downloaded) ?></strong>
                                        <?  } ?>
                                    </span>
                                    <? if (check_paranoia('lastseen', $Paranoia, $Class, $FriendID)) { ?>
                                        <span style="float: right;"><?= time_diff($LastAccess) ?></span>
                                    <?  } ?>
                                </td>
                            </tr>
                            <tr>
                                <? if (Users::has_avatars_enabled()) { ?>
                                    <td class="col_avatar avatar" width="160px" valign="top">
                                        <?= Users::show_avatar($Avatar, $FriendID, $Username, $HeavyInfo['DisableAvatars']) ?>
                                    </td>
                                <?  } ?>
                                <td valign="top">
                                    <input type="hidden" name="friendid" value="<?= $FriendID ?>" />

                                    <textarea class="Input" name="comment" rows="4" cols="65"><?= $Comment ?></textarea>
                                </td>
                                <td class="left" valign="top">
                                    <input class="Button" type="submit" name="action" value="Update" /><br />
                                    <input class="Button" type="submit" name="action" value="Remove friend" /><br />
                                    <input class="Button" type="submit" name="action" value="Contact" /><br />
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
            <?  }  ?>
        </div>
    </div>
    <div class="BodyNavLinks">
        <?= $Pages ?>
    </div>
</div>
<?
View::show_footer();
?>