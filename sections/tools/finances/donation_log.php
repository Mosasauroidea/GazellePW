<?

if (!check_perms('admin_donor_log')) {
    error(403);
}

list($Page, $Limit) = Format::page_limit(CONFIG['DONATIONS_PER_PAGE']);

$AfterDate = $_GET['after_date'];
$BeforeDate = $_GET['before_date'];
$DateSearch = false;
if (!empty($AfterDate) && !empty($BeforeDate)) {
    list($Y, $M, $D) = explode('-', $AfterDate);
    if (!checkdate($M, $D, $Y)) {
        error('Incorrect "after" date format');
    }
    list($Y, $M, $D) = explode('-', $BeforeDate);
    if (!checkdate($M, $D, $Y)) {
        error('Incorrect "before" date format');
    }
    $AfterDate = db_string($AfterDate);
    $BeforeDate = db_string($BeforeDate);
    $DateSearch = true;
}

$Operator = "WHERE";
$SQL = "
	SELECT
		SQL_CALC_FOUND_ROWS
		d.UserID,
		d.Amount,
		d.Currency,
		d.Email,
		d.Time,
		d.Source,
		m.Username,
		d.AddedBy,
		d.Reason
	FROM donations AS d
	LEFT JOIN users_main AS m ON m.ID =1 ";

if (!empty($_GET['email'])) {
    $SQL .= "
	$Operator d.Email LIKE '%" . db_string($_GET['email']) . "%' ";
    $Operator = "AND";
}
if (!empty($_GET['username'])) {
    $SQL .= "
	$Operator m.Username LIKE '%" . db_string($_GET['username']) . "%' ";
    $Operator = "AND";
}
if ($DateSearch) {
    $SQL .= "$Operator d.Time BETWEEN '$AfterDate' AND '$BeforeDate' ";
    $Operator = "AND";
}
$SQL .= "
	ORDER BY d.Time DESC
	LIMIT $Limit";
$DB->query($SQL);
$Donations = $DB->to_array();

$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();

$DB->query("SELECT SUM(Amount) FROM donations");
list($Total) = $DB->next_record();

if (empty($_GET['email']) && empty($_GET['username']) && empty($_GET['source']) && !isset($_GET['page']) && !$DonationTimeline = $Cache->get_value('donation_timeline')) {
    include(CONFIG['SERVER_ROOT'] . '/classes/charts.class.php');
    $DB->query("
		SELECT DATE_FORMAT(Time,'%b \'%y') AS Month, SUM(Amount)
		FROM donations
		GROUP BY Month
		ORDER BY Time DESC
		LIMIT 1, 18");
    $Timeline = array_reverse($DB->to_array());
    $Area = new AREA_GRAPH(880, 160, array('Break' => 1));
    foreach ($Timeline as $Entry) {
        list($Label, $Amount) = $Entry;
        $Area->add($Label, $Amount);
    }
    $Area->transparent();
    $Area->grid_lines();
    $Area->color('3d7930');
    $Area->lines(2);
    $Area->generate();
    $DonationTimeline = $Area->url();
    $Cache->cache_value('donation_timeline', $DonationTimeline, mktime(0, 0, 0, date('n') + 1, 2));
}

$Pages = Format::get_pages($Page, $Results, CONFIG['DONATIONS_PER_PAGE'], 11);
View::show_header(t('server.tools.donation_log'), '', 'PageToolDonationLog');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.tools.donation_log') ?>
        </div>
    </div>
    <?
    if (empty($_GET['email']) && empty($_GET['source']) && empty($_GET['username']) && !isset($_GET['page'])) {
    ?>
        <div class="BoxBody">
            <img src="<?= $DonationTimeline ?>" alt="Donation timeline. The &quot;y&quot; axis is donation amount." />
        </div>
    <? } ?>
    <form class="Form Box SearchPage SearchDonate" name="donation_log" action="" method="get">
        <input type="hidden" name="action" value="donation_log" />
        <div class="SearchPageBody">
            <table class="Form-rowList">
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.tools.username') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="username" size="60" value="<? if (!empty($_GET['username'])) {
                                                                                                echo display_str($_GET['username']);
                                                                                            } ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.tools.donation_source') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="source" size="60" value="<? if (!empty($_GET['source'])) {
                                                                                            echo display_str($_GET['source']);
                                                                                        } ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.common.date_range') ?></td>
                    <td class="Form-inputs">
                        <input class="Input" type="date" name="after_date" />
                        <input class="Input" type="date" name="before_date" />
                    </td>
                </tr>
            </table>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-actions">
                <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
            </div>
        </div>
    </form>
    <? if ($Pages) { ?>
        <div class="BodyNavLinks">
            <?
            echo $Pages;
            ?>
        </div>
    <? } ?>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.common.user') ?></td>
            <td class="Table-cell"><?= t('server.tools.amount') ?></td>
            <td class="Table-cell"><?= t('server.tools.donation_source') ?></td>
            <td class="Table-cell"><?= t('server.common.reason') ?></td>
            <td class="Table-cell"><?= t('server.common.time') ?></td>
        </tr>
        <?
        $PageTotal = 0;
        foreach ($Donations as $Donation) {
            $PageTotal += $Donation['Amount']; ?>
            <tr class="Table-row">
                <td class="Table-cell"><?= Users::format_username($Donation['UserID'], true) ?> (<?= Users::format_username($Donation['AddedBy']) ?>)</td>
                <td class="Table-cell"><?= display_str($Donation['Amount']) ?></td>
                <td class="Table-cell"><?= t('server.tools.donation_' . str_replace(' ', '_', strtolower(display_str($Donation['Source'])))) ?></td>
                <td class="Table-cell"><?= display_str($Donation['Reason']) ?></td>
                <td class="Table-cell"><?= time_diff($Donation['Time']) ?></td>
            </tr>
        <?  } ?>

    </table>
    <div>
        <?= t('server.tools.page_total') ?>: <?= $PageTotal ?> &nbsp;<?= t('server.tools.amount') ?>: <?= $Total ?>
    </div>
    <? if ($Pages) { ?>
        <div class="BodyNavLinks">
            <?
            echo $Pages;
            ?>
        </div>
    <? } ?>
</div>
<? View::show_footer(); ?>