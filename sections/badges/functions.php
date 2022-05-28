<?

//总统计下载量（G）
function badge_downloaded($Count) {
    $Size = $Count * 1024 * 1024 * 1024;
    G::$DB->query(
        "SELECT ID 
        from users_main um 
        where `Downloaded` + (select sum(`Downloaded`) from users_freeleeches uf where um.ID = uf.UserID) >= $Size"
    );
    return G::$DB->collect("ID");
}

function badge_count_downloaded($UserID) {
    G::$DB->query(
        "SELECT (`Downloaded` + (select sum(`Downloaded`) from users_freeleeches uf where um.ID = uf.UserID)) / 1024 / 1024 / 1024
        from users_main um 
        where id = $UserID"
    );
    list($Count) = G::$DB->next_record();
    return $Count;
}

//总统计上传量（G）
function badge_uploaded($Count) {
    $Size = $Count * 1024 * 1024 * 1024;
    G::$DB->query(
        "SELECT ID 
        from users_main um 
        where `Uploaded` >= $Size"
    );
    return G::$DB->collect("ID");
}

function badge_count_uploaded($UserID) {
    G::$DB->query(
        "SELECT `Uploaded` / 1024 / 1024 / 1024
        from users_main um 
        where id = $UserID"
    );
    list($Count) = G::$DB->next_record();
    return $Count;
}

//总上传数
function badge_uploaded_count($Count) {
    G::$DB->query(
        "SELECT `UserID` 
        FROM `torrents` 
        GROUP BY `UserID` 
        HAVING count(`ID`) >= $Count"
    );
    return G::$DB->collect("UserID");
}

function badge_count_uploaded_count($UserID) {
    G::$DB->query(
        "SELECT count(`ID`)
        from `torrents` 
        where UserID = $UserID"
    );
    list($Count) = G::$DB->next_record();
    return $Count;
}

//总下载数
function badge_downloaded_count($Count) {
    G::$DB->query(
        "SELECT uid ID
        FROM `xbt_snatched`
        GROUP BY `uid` 
        HAVING COUNT(DISTINCT fid) >= $Count"
    );
    return G::$DB->collect("ID");
}

function badge_count_downloaded_count($UserID) {
    G::$DB->query(
        "SELECT 
            COUNT(DISTINCT fid)
        from xbt_snatched
        where uid = $UserID"
    );
    list($Count) = G::$DB->next_record();
    return $Count;
}

//站内论坛帖子数（包括发帖）
function badge_post_thread_count($Count) {
    G::$DB->query(
        "SELECT userid ID from (SELECT fp.id id, fp.authorid userid from forums_posts fp LEFT join forums_topics ft on fp.topicid=ft.id where ForumID != 34
        UNION select id id, authorid userid from forums_topics where ForumID != 34) tp group by userid HAVING count(id) >= $Count"
    );
    return G::$DB->collect("ID");
}

function badge_count_post_thread_count($UserID) {
    G::$DB->query(
        "SELECT count(id) from (SELECT fp.id id, fp.authorid userid from forums_posts fp LEFT join forums_topics ft on fp.topicid=ft.id where ForumID != 34 and fp.authorid = $UserID UNION select id id, authorid userid from forums_topics ft where ForumID != 34 and ft.authorid = $UserID) tp"
    );
    list($Count) = G::$DB->next_record();
    return $Count;
}

function badge_donor($Count) {
    G::$DB->query(
        "SELECT UserID ID from users_donor_ranks where TotalRank >= $Count"
    );
    return G::$DB->collect("ID");
}

function badge_count_donor($UserID) {
    G::$DB->query(
        "SELECT TotalRank from users_donor_ranks where UserID = $UserID"
    );
    list($Count) = G::$DB->next_record();
    return $Count;
}
