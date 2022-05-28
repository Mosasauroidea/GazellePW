<?
if (!list($Labels, $InFlow, $OutFlow, $NetFlow, $Max) = $Cache->get_value('torrents_timeline')) {
    $DB->query("
		SELECT DATE_FORMAT(Time,'%b \'%y') AS Month, COUNT(ID)
		FROM log
		WHERE Message LIKE 'Torrent % was uploaded by %'
		GROUP BY Month
		ORDER BY Time DESC
		LIMIT 1, 12");
    $TimelineIn = array_reverse($DB->to_array());
    $DB->query("
		SELECT DATE_FORMAT(Time,'%b \'%y') AS Month, COUNT(ID)
		FROM log
		WHERE Message LIKE 'Torrent % was deleted %'
		GROUP BY Month
		ORDER BY Time DESC
		LIMIT 1, 12");
    $TimelineOut = array_reverse($DB->to_array());
    $DB->query("
		SELECT DATE_FORMAT(Time,'%b \'%y') AS Month, COUNT(ID)
		FROM torrents
		GROUP BY Month
		ORDER BY Time DESC
		LIMIT 1, 12");
    $TimelineNet = array_reverse($DB->to_array());

    foreach ($TimelineIn as $Month) {
        list($Label, $Amount) = $Month;
        if ($Amount > $Max) {
            $Max = $Amount;
        }
    }
    foreach ($TimelineOut as $Month) {
        list($Label, $Amount) = $Month;
        if ($Amount > $Max) {
            $Max = $Amount;
        }
    }
    foreach ($TimelineNet as $Month) {
        list($Label, $Amount) = $Month;
        if ($Amount > $Max) {
            $Max = $Amount;
        }
    }
    foreach ($TimelineIn as $Month) {
        list($Label, $Amount) = $Month;
        $Labels[] = $Label;
        $InFlow[] = number_format(($Amount / $Max) * 100, 4);
    }
    foreach ($TimelineOut as $Month) {
        list($Label, $Amount) = $Month;
        $OutFlow[] = number_format(($Amount / $Max) * 100, 4);
    }
    foreach ($TimelineNet as $Month) {
        list($Label, $Amount) = $Month;
        $NetFlow[] = number_format(($Amount / $Max) * 100, 4);
    }
    $Cache->cache_value('torrents_timeline', array($Labels, $InFlow, $OutFlow, $NetFlow, $Max), mktime(0, 0, 0, date('n') + 1, 2)); //Tested: fine for dec -> jan
}

include_once(SERVER_ROOT . '/classes/charts.class.php');
$DB->query("
	SELECT tg.CategoryID, COUNT(t.ID) AS Torrents
	FROM torrents AS t
		JOIN torrents_group AS tg ON tg.ID = t.GroupID
	GROUP BY tg.CategoryID
	ORDER BY Torrents DESC");
$Groups = $DB->to_array();
$Pie = new PIE_CHART(750, 400, array('Other' => 1, 'Percentage' => 1));
foreach ($Groups as $Group) {
    list($CategoryID, $Torrents) = $Group;
    $CategoryName = $Categories[$CategoryID - 1];
    $Pie->add($CategoryName, $Torrents);
}
$Pie->transparent();
$Pie->color('FF33CC');
$Pie->generate();
$Categories = $Pie->url();

View::show_header(Lang::get('stats', 'detailed_torrent_statistics'), '', 'PageStatTorrent');
?>
<div class="BodyNavLinks">
    <a href="stats.php?action=users" class="brackets"><?= Lang::get('stats', 'user_stats') ?></a>
</div>
<h1 id="Torrent_Upload"><a href="#Torrent_Upload"><?= Lang::get('stats', 'uploads_by_month') ?></a></h1>
<div class="BoxBody center">
    <img src="https://chart.googleapis.com/chart?cht=lc&amp;chs=880x160&amp;chco=000D99,99000D,00990D&amp;chg=0,-1,1,1&amp;chxt=y,x&amp;chxs=0,h&amp;chxl=1:|<?= implode('|', $Labels) ?>&amp;chxr=0,0,<?= $Max ?>&amp;chd=t:<?= implode(',', $InFlow) ?>|<?= implode(',', $OutFlow) ?>|<?= implode(',', $NetFlow) ?>&amp;chls=2,4,0&amp;chdl=Uploads|Deletions|Remaining&amp;chf=bg,s,FFFFFF00" alt="<?= Lang::get('stats', 'torrent_flow_chart') ?>" />
</div>
<!-- <h1 id="Torrent_Category"><a href="#Torrent_Category"><?= Lang::get('stats', 'torrents_by_category') ?></a></h1>这是按大类分的，就是音乐种、电子书、软件这样的分类，对我们来说这个统计无用。
<div class="BoxBody center">
    <img src="<?= $Categories ?>" alt="" />
</div> -->
<?
View::show_footer();
