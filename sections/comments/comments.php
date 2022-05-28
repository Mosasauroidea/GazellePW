<?
/*
 * $_REQUEST['action'] is artist, collages, requests or torrents (default torrents)
 * $_REQUEST['type'] depends on the page:
 *     collages:
 *        created = comments left on one's collages
 *        contributed = comments left on collages one contributed to
 *     requests:
 *        created = comments left on one's requests
 *        voted = comments left on requests one voted on
 *     torrents:
 *        uploaded = comments left on one's uploads
 *     If missing or invalid, this defaults to the comments one made
 */

// User ID
if (isset($_GET['id']) && is_number($_GET['id'])) {
    $UserID = (int)$_GET['id'];

    $UserInfo = Users::user_info($UserID);

    $Username = $UserInfo['Username'];
    if ($LoggedUser['ID'] == $UserID) {
        $Self = true;
    } else {
        $Self = false;
    }
    $Perms = Permissions::get_permissions($UserInfo['PermissionID']);
    $UserClass = $Perms['Class'];
    if (!check_paranoia('torrentcomments', $UserInfo['Paranoia'], $UserClass, $UserID)) {
        error(403);
    }
} else {
    $UserID = $LoggedUser['ID'];
    $Username = $LoggedUser['Username'];
    $Self = true;
}

// Posts per page limit stuff
if (isset($LoggedUser['PostsPerPage'])) {
    $PerPage = $LoggedUser['PostsPerPage'];
} else {
    $PerPage = POSTS_PER_PAGE;
}
list($Page, $Limit) = Format::page_limit($PerPage);

if (!isset($_REQUEST['action'])) {
    $Action = 'torrents';
} else {
    $Action = $_REQUEST['action'];
}
if (!isset($_REQUEST['type'])) {
    $Type = 'default';
} else {
    $Type = $_REQUEST['type'];
}

// Construct the SQL query
$Conditions = $Join = array();
switch ($Action) {
    case 'artist':
        $Field1 = 'artists_group.ArtistID';
        $Field2 = 'artists_group.Name';
        $Table = 'artists_group';
        $Title = Lang::get('comments', 'artist_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . $Username . Lang::get('comments', 'username_space')) . Lang::get('comments', 'artist_comments_left_by_user_after');
        $Header = Lang::get('comments', 'artist_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'username_space')) . Lang::get('comments', 'artist_comments_left_by_user_after');
        $Conditions[] = "comments.AuthorID = $UserID";
        break;
    case 'collages':
        $Field1 = 'collages.ID';
        $Field2 = 'collages.Name';
        $Table = 'collages';
        $Conditions[] = "collages.Deleted = '0'";
        if ($Type == 'created') {
            $Conditions[] = "collages.UserID = $UserID";
            $Conditions[] = "comments.AuthorID != $UserID";
            $Title = Lang::get('comments', 'comments_left_on_collages_user_created_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . $Username . Lang::get('comments', 'username_space')) . Lang::get('comments', 'comments_left_on_collages_user_created_after');
            $Header = Lang::get('comments', 'comments_left_on_collages_user_created_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'username_space')) . Lang::get('comments', 'comments_left_on_collages_user_created_after');
        } elseif ($Type == 'contributed') {
            $Conditions[] = "comments.AuthorID != $UserID";
            $Join[] = "LEFT JOIN collages_torrents ON collages_torrents.CollageID = collages.ID AND collages_torrents.UserID = $UserID";
            $Join[] = "LEFT JOIN collages_artists ON collages_artists.CollageID = collages.ID AND collages_artists.UserID = $UserID";
            $Title = Lang::get('comments', 'comments_left_on_collages_user_has_contributed_to_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'comments_left_on_collages_user_has_contributed_to_after');
            $Header = Lang::get('comments', 'comments_left_on_collages_user_has_contributed_to_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'has_after')) . Lang::get('comments', 'comments_left_on_collages_user_has_contributed_to_after');
        } else {
            $Type = 'default';
            $Conditions[] = "comments.AuthorID = $UserID";
            $Title = Lang::get('comments', 'collage_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . $Username . Lang::get('comments', 'username_space')) . Lang::get('comments', 'collage_comments_left_by_user_after');
            $Header = Lang::get('comments', 'collage_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'username_space')) . Lang::get('comments', 'collage_comments_left_by_user_after');
        }
        break;
    case 'requests':
        $Field1 = 'requests.ID';
        $Field2 = 'requests.Title';
        $Table = 'requests';
        if ($Type == 'created') {
            $Conditions[] = "requests.UserID = $UserID";
            $Conditions[] = "comments.AuthorID != $UserID";
            $Title = Lang::get('comments', 'collage_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . $Username . Lang::get('comments', 'username_space')) . Lang::get('comments', 'comments_left_on_requests_user_created_after');
            $Header = Lang::get('comments', 'collage_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'username_space')) . Lang::get('comments', 'comments_left_on_requests_user_created_after');
        } elseif ($Type == 'voted') {
            $Conditions[] = "requests_votes.UserID = $UserID";
            $Conditions[] = "comments.AuthorID != $UserID";
            $Join[] = 'JOIN requests_votes ON requests_votes.RequestID = requests.ID';
            $Title = Lang::get('comments', 'comments_left_on_requests_user_has_voted_on_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'comments_left_on_requests_user_has_voted_on_after');
            $Header = Lang::get('comments', 'comments_left_on_requests_user_has_voted_on_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'has_after')) . Lang::get('comments', 'comments_left_on_requests_user_has_voted_on_after');
        } else {
            $Type = 'default';
            $Conditions[] = "comments.AuthorID = $UserID";
            $Title = Lang::get('comments', 'request_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . $Username . Lang::get('comments', 'username_space')) . Lang::get('comments', 'request_comments_left_by_user_after');
            $Header = Lang::get('comments', 'request_comments_left_by_user_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'username_space')) . Lang::get('comments', 'request_comments_left_by_user_after');
        }
        break;
    case 'torrents':
    default:
        $Action = 'torrents';
        $Field1 = 'torrents.GroupID';
        $Field2 = 'torrents_group.Name';
        $Table = 'torrents';
        $Join[] = 'JOIN torrents_group ON torrents.GroupID = torrents_group.ID';
        if ($Type == 'uploaded') {
            $Conditions[] = "torrents.UserID = $UserID";
            $Conditions[] = 'comments.AddedTime > torrents.Time';
            $Conditions[] = "comments.AuthorID != $UserID";
            $Title = Lang::get('comments', 'comments_left_on_torrents_user_has_uploaded_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'comments_left_on_torrents_user_has_uploaded_after');
            $Header = Lang::get('comments', 'comments_left_on_torrents_user_has_uploaded_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'has_after')) . Lang::get('comments', 'comments_left_on_torrents_user_has_uploaded_after');
        } else {
            $Type = 'default';
            $Conditions[] = "comments.AuthorID = $UserID";
            $Title = Lang::get('comments', 'torrent_comments_left_by_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . $Username . Lang::get('comments', 'username_space')) . Lang::get('comments', 'torrent_comments_left_by_after');
            $Header = Lang::get('comments', 'torrent_comments_left_by_before') . ($Self ? Lang::get('comments', 'you') : Lang::get('comments', 'space_username') . Users::format_username($UserID, false, false, false) . Lang::get('comments', 'username_space')) . Lang::get('comments', 'torrent_comments_left_by_after');
        }
        break;
}
$Join[] = "JOIN comments ON comments.Page = '$Action' AND comments.PageID = $Field1";
$Join = implode("\n\t\t", $Join);
$Conditions = implode(" AND ", $Conditions);
$Conditions = ($Conditions ? 'WHERE ' . $Conditions : '');

$SQL = "
	SELECT
		SQL_CALC_FOUND_ROWS
		comments.AuthorID,
		comments.Page,
		comments.PageID,
		$Field2,
		comments.ID,
		comments.Body,
		comments.AddedTime,
		comments.EditedTime,
		comments.EditedUserID
	FROM $Table
		$Join
	$Conditions
	GROUP BY comments.ID
	ORDER BY comments.ID DESC
	LIMIT $Limit";

$Comments = $DB->query($SQL);
$Count = $DB->record_count();

$DB->query("SELECT FOUND_ROWS()");
list($Results) = $DB->next_record();
$Pages = Format::get_pages($Page, $Results, $PerPage, 11);

$DB->set_query_id($Comments);
if ($Action == 'requests') {
    $RequestIDs = array_flip(array_flip($DB->collect('PageID')));
    $Artists = array();
    foreach ($RequestIDs as $RequestID) {
        $Artists[$RequestID] = Requests::get_artists($RequestID);
    }
    $DB->set_query_id($Comments);
} elseif ($Action == 'torrents') {
    $GroupIDs = array_flip(array_flip($DB->collect('PageID')));
    $Artists = Artists::get_artists($GroupIDs);
    $DB->set_query_id($Comments);
}

$LinkID = (!$Self ? '&amp;id=' . $UserID : '');
$ActionLinks = $TypeLinks = array();
if ($Action != 'artist') {
    $ActionLinks[] = '<a href="comments.php?action=artist' . $LinkID . '" class="brackets">' . Lang::get('comments', 'artist_comments') . '</a>';
}
if ($Action != 'collages') {
    $ActionLinks[] = '<a href="comments.php?action=collages' . $LinkID . '" class="brackets">' . Lang::get('comments', 'collage_comments') . '</a>';
}
if ($Action != 'requests') {
    $ActionLinks[] = '<a href="comments.php?action=requests' . $LinkID . '" class="brackets">' . Lang::get('comments', 'request_comments') . '</a>';
}
if ($Action != 'torrents') {
    $ActionLinks[] = '<a href="comments.php?action=torrents' . $LinkID . '" class="brackets">' . Lang::get('comments', 'torrent_comments') . '</a>';
}
switch ($Action) {
    case 'collages':
        $BaseLink = 'comments.php?action=collages' . $LinkID;
        if ($Type != 'default') {
            $TypeLinks[] = '<a href="' . $BaseLink . '" class="brackets">' . Lang::get('comments', 'display_comments_left_on_collages_user_has_made_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'display_comments_left_on_collages_user_has_made_after') . '</a>';
        }
        if ($Type != 'created') {
            $TypeLinks[] = '<a href="' . $BaseLink . '&amp;type=created" class="brackets">' . Lang::get('comments', 'display_comments_left_on_users_collages_before') . ($Self ? Lang::get('comments', 'your_collages') : Lang::get('comments', 'collages_created_by_before') . $Username . Lang::get('comments', 'collages_created_by_after')) . Lang::get('comments', 'display_comments_left_on_users_collages_after') . '</a>';
        }
        if ($Type != 'contributed') {
            $TypeLinks[] = '<a href="' . $BaseLink . '&amp;type=contributed" class="brackets">' . Lang::get('comments', 'display_comments_left_on_collages_user_has_contributed_to_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'display_comments_left_on_collages_user_has_contributed_to_after') . '</a>';
        }
        break;
    case 'requests':
        $BaseLink = 'comments.php?action=requests' . $LinkID;
        if ($Type != 'default') {
            $TypeLinks[] = '<a href="' . $BaseLink . '" class="brackets">' . Lang::get('comments', 'display_comments_left_on_requests_user_has_made_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'display_comments_left_on_requests_user_has_made_after') . '</a>';
        }
        if ($Type != 'created') {
            //
            //your requests
            $TypeLinks[] = '<a href="' . $BaseLink . '&amp;type=created" class="brackets">' . Lang::get('comments', 'display_comments_left_on_requests_user_created_before') . ($Self ? Lang::get('comments', 'you') :  Lang::get('comments', 'space_username') . $Username . Lang::get('comments', 'username_space')) . Lang::get('comments', 'display_comments_left_on_requests_user_created_after') . '</a>';
        }
        if ($Type != 'voted') {
            $TypeLinks[] = '<a href="' . $BaseLink . '&amp;type=voted" class="brackets">' . Lang::get('comments', 'display_comments_left_on_requests_user_has_voted_on_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'display_comments_left_on_requests_user_has_voted_on_after') . '</a>';
        }
        break;
    case 'torrents':
        if ($Type != 'default') {
            $TypeLinks[] = '<a href="comments.php?action=torrents' . $LinkID . '" class="brackets">' . Lang::get('comments', 'display_comments_left_on_torrents_user_has_made_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'display_comments_left_on_torrents_user_has_made_after') . '</a>';
        }
        if ($Type != 'uploaded') {
            $TypeLinks[] = '<a href="comments.php?action=torrents' . $LinkID . '&amp;type=uploaded" class="brackets">' . Lang::get('comments', 'display_comments_left_on_torrents_user_has_uploaded_before') . ($Self ? Lang::get('comments', 'you_ve') : Lang::get('comments', 'has_before') . $Username . Lang::get('comments', 'has_after')) . Lang::get('comments', 'display_comments_left_on_torrents_user_has_uploaded_after') . '</a>';
        }
        break;
}
$Links = implode(' ', $ActionLinks) . (count($TypeLinks) ? '<br />' . implode(' ', $TypeLinks) : '');

View::show_header($Title, 'bbcode,comments', 'PageCommentHome');
?><div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Header ?></h2>
        <? if ($Links !== '') { ?>
            <div class="BodyNavLinks">
                <?= $Links ?>
            </div>
        <? } ?>
    </div>
    <div class="BodyNavLinks">
        <?= $Pages ?>
    </div>
    <?
    if ($Count > 0) {
        $DB->set_query_id($Comments);
        while (list($AuthorID, $Page, $PageID, $Name, $PostID, $Body, $AddedTime, $EditedTime, $EditedUserID) = $DB->next_record()) {
            $Link = Comments::get_url($Page, $PageID, $PostID);
            switch ($Page) {
                case 'artist':
                    $Header = Lang::get('comments', 'space_on_space') . "<a href=\"artist.php?id=$PageID\">$Name</a>";
                    break;
                case 'collages':
                    $Header = Lang::get('comments', 'space_on_space') . "<a href=\"collages.php?id=$PageID\">$Name</a>";
                    break;
                case 'requests':
                    $Header = Lang::get('comments', 'space_on_space') . Artists::display_artists($Artists[$PageID]) . " <a href=\"requests.php?action=view&id=$PageID\">$Name</a>";
                    break;
                case 'torrents':
                    $Header = Lang::get('comments', 'space_on_space') . Artists::display_artists($Artists[$PageID]) . " <a href=\"torrents.php?id=$PageID\">$Name</a>";
                    break;
            }
            CommentsView::render_comment($AuthorID, $PostID, $Body, $AddedTime, $EditedUserID, $EditedTime, $Link, false, $Header, false);
        }
    } else { ?>
        <div class="center"><?= Lang::get('comments', 'no_results') ?></div>
    <? } ?>
    <div class="BodyNavLinks">
        <?= $Pages ?>
    </div>
</div>
<?
View::show_footer();
