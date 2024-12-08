<?
function link_users($UserID, $TargetID) {
    global $DB, $LoggedUser;

    authorize();
    if (!check_perms('users_mod')) {
        error(403);
    }

    if (!is_number($UserID) || !is_number($TargetID)) {
        error(403);
    }
    if ($UserID == $TargetID) {
        return;
    }

    $DB->query("
		SELECT 1
		FROM users_main
		WHERE ID IN ($UserID, $TargetID)");
    if ($DB->record_count() !== 2) {
        error(403);
    }

    $DB->query("
		SELECT GroupID
		FROM users_dupes
		WHERE UserID = $TargetID");
    list($TargetGroupID) = $DB->next_record();
    $DB->query("
		SELECT u.GroupID, d.Comments
		FROM users_dupes AS u
			JOIN dupe_groups AS d ON d.ID = u.GroupID
		WHERE UserID = $UserID");
    list($UserGroupID, $Comments) = $DB->next_record();

    $UserInfo = Users::user_info($UserID);
    $TargetInfo = Users::user_info($TargetID);
    if (!$UserInfo || !$TargetInfo) {
        return;
    }

    if ($TargetGroupID) {
        if ($TargetGroupID == $UserGroupID) {
            return;
        }
        if ($UserGroupID) {
            $DB->query("
				UPDATE users_dupes
				SET GroupID = $TargetGroupID
				WHERE GroupID = $UserGroupID");
            $DB->query("
				UPDATE dupe_groups
				SET Comments = CONCAT('" . db_string($Comments) . "\n\n',Comments)
				WHERE ID = $TargetGroupID");
            $DB->query("DELETE FROM dupe_groups WHERE ID = $UserGroupID");
            $GroupID = $UserGroupID;
        } else {
            $DB->query("INSERT INTO users_dupes (UserID, GroupID) VALUES ($UserID, $TargetGroupID)");
            $GroupID = $TargetGroupID;
        }
    } elseif ($UserGroupID) {
        $DB->query("INSERT INTO users_dupes (UserID, GroupID) VALUES ($TargetID, $UserGroupID)");
        $GroupID = $UserGroupID;
    } else {
        $DB->query("INSERT INTO dupe_groups () VALUES ()");
        $GroupID = $DB->inserted_id();
        $DB->query("INSERT INTO users_dupes (UserID, GroupID) VALUES ($TargetID, $GroupID)");
        $DB->query("INSERT INTO users_dupes (UserID, GroupID) VALUES ($UserID, $GroupID)");
    }

    $AdminComment = sqltime() . " - Linked accounts updated: [user]" . $UserInfo['Username'] . "[/user] and [user]" . $TargetInfo['Username'] . "[/user] linked by " . $LoggedUser['Username'];
    $DB->query("
			UPDATE users_info AS i
				JOIN users_dupes AS d ON d.UserID = i.UserID
			SET i.AdminComment = CONCAT('" . db_string($AdminComment) . "\n\n', i.AdminComment)
			WHERE d.GroupID = $GroupID");
}

function unlink_user($UserID) {
    global $DB, $LoggedUser;

    authorize();
    if (!check_perms('users_mod')) {
        error(403);
    }

    if (!is_number($UserID)) {
        error(403);
    }
    $UserInfo = Users::user_info($UserID);
    if ($UserInfo === false) {
        return;
    }
    $AdminComment = sqltime() . " - Linked accounts updated: [user]" . $UserInfo['Username'] . "[/user] unlinked by " . $LoggedUser['Username'];
    $DB->query("
		UPDATE users_info AS i
			JOIN users_dupes AS d1 ON d1.UserID = i.UserID
			JOIN users_dupes AS d2 ON d2.GroupID = d1.GroupID
		SET i.AdminComment = CONCAT('" . db_string($AdminComment) . "\n\n', i.AdminComment)
		WHERE d2.UserID = $UserID");
    $DB->query("DELETE FROM users_dupes WHERE UserID = '$UserID'");
    $DB->query("
		DELETE g.*
		FROM dupe_groups AS g
			LEFT JOIN users_dupes AS u ON u.GroupID = g.ID
		WHERE u.GroupID IS NULL");
}

function delete_dupegroup($GroupID) {
    global $DB;

    authorize();
    if (!check_perms('users_mod')) {
        error(403);
    }

    if (!is_number($GroupID)) {
        error(403);
    }

    $DB->query("DELETE FROM dupe_groups WHERE ID = '$GroupID'");
}

function dupe_comments($GroupID, $Comments) {
    global $DB, $LoggedUser;

    authorize();
    if (!check_perms('users_mod')) {
        error(403);
    }

    if (!is_number($GroupID)) {
        error(403);
    }

    $DB->query("
		SELECT SHA1(Comments) AS CommentHash
		FROM dupe_groups
		WHERE ID = $GroupID");
    list($OldCommentHash) = $DB->next_record();
    if ($OldCommentHash != sha1($Comments)) {
        $AdminComment = sqltime() . " - Linked accounts updated: Comments updated by " . $LoggedUser['Username'];
        if ($_POST['form_comment_hash'] == $OldCommentHash) {
            $DB->query("
				UPDATE dupe_groups
				SET Comments = '" . db_string($Comments) . "'
				WHERE ID = '$GroupID'");
        } else {
            $DB->query("
				UPDATE dupe_groups
				SET Comments = CONCAT('" . db_string($Comments) . "\n\n',Comments)
				WHERE ID = '$GroupID'");
        }

        $DB->query("
				UPDATE users_info AS i
					JOIN users_dupes AS d ON d.UserID = i.UserID
				SET i.AdminComment = CONCAT('" . db_string($AdminComment) . "\n\n', i.AdminComment)
				WHERE d.GroupID = $GroupID");
    }
}
