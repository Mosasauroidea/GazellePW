<?
ini_set('memory_limit', '5G');
set_time_limit(0);

// Data is published on the first Tuesday of every month
$Dir = CONFIG['TMPDIR'];
$HaveData = false;
$FileNameLocation = "$Dir/GeoLite2-City-CSV/GeoLite2-City-Locations-en.csv";
$FileNameIPv4Blocks = "$Dir/GeoLite2-City-CSV/GeoLite2-City-Blocks-IPv4.csv";
$FileNameIPv6Blocks = "$Dir/GeoLite2-City-CSV/GeoLite2-City-Blocks-IPv6.csv";
if (file_exists($Dir)) {
	if (file_exists($FileNameLocation) && file_exists($FileNameIPv4Blocks) && file_exists($FileNameIPv6Blocks)) {
		$HaveData = true;
	}
}

if (!$HaveData) {
	// configuration
	$client = new \tronovav\GeoIP2Update\Client(array(
		'license_key' => CONFIG['GEOIP_LICENSE_KEY'],
		'dir' => $Dir,
		'editions' => array('GeoLite2-City-CSV'),
	));
	// run update
	$client->run();
}

if (!file_exists($FileNameLocation) || !file_exists($FileNameIPv4Blocks) ||  !file_exists($FileNameIPv6Blocks)) {
	// TODO by qwerty i18N
	error('Download or extraction of maxmind database failed');
}

View::show_header(t('server.tools.update_geoip'), '', 'PageToolUpdateGeoIP');
?>
<div class="LayoutBody">
	<div class="BodyHeader">
		<h2 class="BodyHeader-nav"><?= t('server.tools.update_geoip') ?></h2>
	</div>
	<div>
		<?

		$DB->query("TRUNCATE TABLE geoip_country");

		$DB->prepared_query("
CREATE TEMPORARY TABLE temp_geoip_locations (
	`ID` int(10) NOT NULL PRIMARY KEY,
	`Country` varchar(2) NOT NULL,
	`City` varchar(3) NOT NULL
)");

		// Note: you cannot use a prepared query here for this
		$DB->query("
LOAD DATA LOCAL INFILE '{$FileNameLocation}' INTO TABLE temp_geoip_locations
FIELDS TERMINATED BY ',' 
OPTIONALLY ENCLOSED BY '\"' 
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@ID,  @dummy,  @dummy,  @dummy,  @Country, @dummy, @City, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy)
SET `ID`=@ID, `Country`=@Country, `City`=@City;");


		$DB->prepared_query("
CREATE TEMPORARY TABLE temp_geoip_blocks (
	`Network` varchar(32) DEFAULT NULL,
	`LocID` INT(10) NOT NULL
)");

		// Note: you cannot use a prepared query here for this
		$DB->query("
LOAD DATA LOCAL INFILE '{$FileNameIPv4Blocks}' INTO TABLE temp_geoip_blocks
FIELDS TERMINATED BY ',' 
OPTIONALLY ENCLOSED BY '\"' 
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@Network,  @LocID,  @dummy,  @dummy,  @dummy,  @dummy, @dummy, @dummy, @dummy, @dummy)
SET `Network`=@Network, `LocID`=@LocID;");

		$DB->prepared_query("
INSERT INTO geoip_country (StartIP, EndIP, Code) 
	SELECT 
    INET_ATON( SUBSTRING_INDEX(Network, '/', 1)) & 0xffffffff ^ ((0x1 << ( 32 - SUBSTRING_INDEX(Network, '/', -1))  ) -1 ) as StartIP,
    INET_ATON( SUBSTRING_INDEX(Network, '/', 1)) | ((0x100000000 >> SUBSTRING_INDEX(Network, '/', -1) ) -1 ) as EndIP,
	Country
	FROM temp_geoip_blocks AS tgb
	LEFT JOIN temp_geoip_locations AS tgl ON tgb.LocID = tgl.ID
");

		print "{$DB->affected_rows()} locations inserted";

		$DB->query("INSERT INTO users_geodistribution
	(Code, Users)
SELECT g.Code, COUNT(u.ID) AS Users
FROM geoip_country AS g
	JOIN users_main AS u ON INET_ATON(u.IP) BETWEEN g.StartIP AND g.EndIP
WHERE u.Enabled = '1'
GROUP BY g.Code
ORDER BY Users DESC");

		print "{$DB->affected_rows()} users updated";

		?>
	</div>
</div>
<?
View::show_footer();
