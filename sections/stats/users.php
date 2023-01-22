<?
if (!list($Countries, $Rank, $CountryUsers, $CountryMax, $CountryMin, $LogIncrements) = $Cache->get_value('geodistribution')) {
    $DB->query('
		SELECT Code, Users
		FROM users_geodistribution');
    $Data = $DB->to_array();
    $Count = $DB->record_count() - 1;

    if ($Count < 30) {
        $CountryMinThreshold = $Count;
    } else {
        $CountryMinThreshold = 30;
    }

    $CountryMax = ceil(log(Max(1, $Data[0][1])) / log(2)) + 1;
    $CountryMin = floor(log(Max(1, $Data[$CountryMinThreshold][1])) / log(2));

    $CountryRegions = array('RS' => array('RS-KM')); // Count Kosovo as Serbia as it doesn't have a TLD
    foreach ($Data as $Key => $Item) {
        list($Country, $UserCount) = $Item;
        $Countries[] = $Country;
        $CountryUsers[] = number_format((((log($UserCount) / log(2)) - $CountryMin) / ($CountryMax - $CountryMin)) * 100, 2);
        $Rank[] = round((1 - ($Key / $Count)) * 100);

        if (isset($CountryRegions[$Country])) {
            foreach ($CountryRegions[$Country] as $Region) {
                $Countries[] = $Region;
                $Rank[] = end($Rank);
            }
        }
    }
    reset($Rank);

    for ($i = $CountryMin; $i <= $CountryMax; $i++) {
        $LogIncrements[] = Format::human_format(pow(2, $i));
    }
    $Cache->cache_value('geodistribution', array($Countries, $Rank, $CountryUsers, $CountryMax, $CountryMin, $LogIncrements), 0);
}


View::show_header(t('server.stats.stats'), '', 'PageStatUser');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.stats.stats') ?>
        </div>
        <div class="BodyHeader-subNav">
            <?= t('server.top10.users') ?>
        </div>
        <div class="BodyNavLinks">
            <a href="stats.php?action=torrents" class="brackets"><?= t('server.top10.torrents') ?></a>
            <a href="stats.php?action=peers" class="brackets">Peers</a>
        </div>
    </div>
    <div class="ChartRoot">
        <div id="chart_user_timeline"> </div>
        <div id="chart_user_day_active"> </div>
        <div id="chart_user_day_torrent"> </div>
        <div id="chart_user_home" class="ChartPieContainer"></div>
    </div>
    <? if (false) { ?>
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <div id="Geo_Dist_Map"><a href="#Geo_Dist_Map"><?= t('server.stats.geographical_distribution_map') ?></a></div>
                </div>
            </div>
            <div class="Group-body">
                <div class=" center">
                    <img src="https://chart.googleapis.com/chart?cht=map:fixed=-55,-180,73,180&amp;chs=440x220&amp;chd=t:<?= implode(',', $Rank) ?>&amp;chco=FFFFFF,EDEDED,1F0066&amp;chld=<?= implode('|', $Countries) ?>&amp;chf=bg,s,CCD6FF" alt="<?= t('server.stats.geographical_distribution_map') ?>" />
                    <img src="https://chart.googleapis.com/chart?cht=map:fixed=37,-26,65,67&amp;chs=440x220&amp;chs=440x220&amp;chd=t:<?= implode(',', $Rank) ?>&amp;chco=FFFFFF,EDEDED,1F0066&amp;chld=<?= implode('|', $Countries) ?>&amp;chf=bg,s,CCD6FF" alt="<?= t('server.stats.geographical_distribution_map_europe') ?>" />
                    <br />
                    <img src="https://chart.googleapis.com/chart?cht=map:fixed=-46,-132,24,21.5&amp;chs=440x220&amp;chd=t:<?= implode(',', $Rank) ?>&amp;chco=FFFFFF,EDEDED,1F0066&amp;chld=<?= implode('|', $Countries) ?>&amp;chf=bg,s,CCD6FF" alt="<?= t('server.stats.geographical_distribution_map_south_america') ?>" />
                    <img src="https://chart.googleapis.com/chart?cht=map:fixed=-11,22,50,160&amp;chs=440x220&amp;chd=t:<?= implode(',', $Rank) ?>&amp;chco=FFFFFF,EDEDED,1F0066&amp;chld=<?= implode('|', $Countries) ?>&amp;chf=bg,s,CCD6FF" alt="<?= t('server.stats.geographical_distribution_map_asia') ?>" />
                    <br />
                    <img src="https://chart.googleapis.com/chart?cht=map:fixed=-36,-57,37,100&amp;chs=440x220&amp;chd=t:<?= implode(',', $Rank) ?>&amp;chco=FFFFFF,EDEDED,1F0066&amp;chld=<?= implode('|', $Countries) ?>&amp;chf=bg,s,CCD6FF" alt="<?= t('server.stats.geographical_distribution_map_africa') ?>" />
                    <img src="https://chart.googleapis.com/chart?cht=map:fixed=14.8,15,45,86&amp;chs=440x220&amp;chd=t:<?= implode(',', $Rank) ?>&amp;chco=FFFFFF,EDEDED,1F0066&amp;chld=<?= implode('|', $Countries) ?>&amp;chf=bg,s,CCD6FF" alt="<?= t('server.stats.geographical_distribution_map_middle_east') ?>" />
                    <br />
                    <img src="https://chart.googleapis.com/chart?chxt=y,x&amp;chg=0,-1,1,1&amp;chxs=0,h&amp;cht=bvs&amp;chco=76A4FB&amp;chs=880x300&amp;chd=t:<?= implode(',', array_slice($CountryUsers, 0, 31)) ?>&amp;chxl=1:|<?= implode('|', array_slice($Countries, 0, 31)) ?>|0:|<?= implode('|', $LogIncrements) ?>&amp;chf=bg,s,FFFFFF00" alt="<?= t('server.stats.number_of_users_by_country') ?>" />
                </div>
            </div>
        </div>
    <? } ?>
</div>
<?
Stats::userTimeLine();
Stats::uv();
Stats::userClasses();
Stats::userPlatforms();
Stats::userBrowsers();
Stats::torrentByDayUser();
View::show_footer([], 'stats/index.jsx');
