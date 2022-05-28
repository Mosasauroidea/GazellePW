<?

$PostID = (int)$_POST['postid'];

if (empty($PostID)) {
	ajax_json_error("empty postid");
}

$DB->query("
	SELECT t.ForumID, p.Body
	FROM forums_posts AS p
		JOIN forums_topics AS t ON p.TopicID = t.ID
	WHERE p.ID = '$PostID'");

if (!$DB->has_results()) {
	ajax_json_error("no results");
}

list($ForumID, $Body) = $DB->next_record();
if (!Forums::check_forumperm($ForumID)) {
	ajax_json_error("assholes");
}

ajax_json_success(array("body" => nl2br($Body)));
