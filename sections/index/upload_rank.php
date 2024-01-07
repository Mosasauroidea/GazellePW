<?

if (!isset(CONFIG['UPLOAD_RANK_START_TIME']) || !isset(CONFIG['UPLOAD_RANK_SHOW_END_TIME'])) {
    return;
}
$t = time();
if ($t < strtotime(CONFIG['UPLOAD_RANK_START_TIME']) || $t >= strtotime(CONFIG['UPLOAD_RANK_SHOW_END_TIME'])) {
    return;
}

$CacheKey = 'upload_rank_' . strtotime(CONFIG['UPLOAD_RANK_START_TIME']) . '_' . strtotime(CONFIG['UPLOAD_RANK_END_TIME']);
if (!$RankList = G::$Cache->get_value($CacheKey)) {
    G::$DB->prepared_query("SELECT UserID, Count(*) as Count FROM torrents Where TIme > ? and Time < ? GROUP BY UserID ORDER BY Count DESC LIMIT 50", CONFIG['UPLOAD_RANK_START_TIME'], CONFIG['UPLOAD_RANK_END_TIME']);
    $RankList = G::$DB->to_array(false, MYSQLI_ASSOC, false);
    G::$Cache->cache_value($CacheKey, $RankList, 1800);
}
?>

<div class="SidebarItemPoster SidebarItem Box is-limitHeight">
    <div class="SidebarItem-header Box-header">
        <div class="SidebarItem-headerTitle">
            <?= t('server.index.upload_rank') . " (" . date('m/d', strtotime(CONFIG['UPLOAD_RANK_START_TIME'])) . '-' . date('m/d', strtotime(CONFIG['UPLOAD_RANK_END_TIME'])) . ')' ?>
        </div>
    </div>
    <ul class=" SidebarItem-body Box-body SidebarList is-ordered">
        <?
        $Rank = 1;
        foreach ($RankList as $RankItem) {
            $UserID = $RankItem['UserID'];
            $Count = $RankItem['Count'];

        ?>
            <li class="SidebarList-item">
                <div class="SidebarList-actions">
                    <?= Users::format_username($UserID) ?>
                    <div class="SidebarList-actions">
                        <?= $Count ?>
                    </div>
                </div>
            </li>

        <?
            $Rank++;
        }
        ?>
    </ul>
</div>