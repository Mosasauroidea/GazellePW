<?

if (!check_perms('admin_donor_log')) {
    error(403);
}

include(CONFIG['SERVER_ROOT'] . '/sections/donate/config.php');
include(CONFIG['SERVER_ROOT'] . '/sections/donate/const.php');

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
	LEFT JOIN users_main AS m ON m.ID = d.UserID ";

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

View::show_header('Donation log', '', 'PageToolBitcoinLog');
if (empty($_GET['email']) && empty($_GET['source']) && empty($_GET['username']) && !isset($_GET['page'])) {
?>
    <div class="BoxBody">
        <img src="<?= $DonationTimeline ?>" alt="Donation timeline. The &quot;y&quot; axis is donation amount." />
    </div>
    <br />
<? } ?>
<div class="BodyContent">
    <form class="Form SearchPage Box SearchBtc" name="donation_log" action="" method="get">
        <input type="hidden" name="action" value="donation_log" />
        <table class="Form-list">
            <tr class="Form-row">
                <td class="Form-label"><strong>Username:</strong></td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="username" size="60" value="<? if (!empty($_GET['username'])) {
                                                                                            echo display_str($_GET['username']);
                                                                                        } ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong>Email:</strong></td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="email" size="60" value="<? if (!empty($_GET['email'])) {
                                                                                        echo display_str($_GET['email']);
                                                                                    } ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong>Source:</strong></td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="source" size="60" value="<? if (!empty($_GET['source'])) {
                                                                                        echo display_str($_GET['source']);
                                                                                    } ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong>Date Range:</strong></td>
                <td class="Form-inputs">
                    <input class="Input" type="date" name="after_date" />
                    <input class="Input" type="date" name="before_date" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-submit">
                    <input class="Button" type="submit" value="Search donation log" />
                </td>
            </tr>
        </table>
    </form>
</div>
<br />
<div class="BodyNavLinks">
    <?
    $Pages = Format::get_pages($Page, $Results, CONFIG['DONATIONS_PER_PAGE'], 11);
    echo $Pages;
    ?>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">User</td>
        <td class="Table-cell">Amount</td>
        <td class="Table-cell">Email</td>
        <td class="Table-cell">Source</td>
        <td class="Table-cell">Reason</td>
        <td class="Table-cell">Time</td>
    </tr>
    <?
    $PageTotal = 0;
    foreach ($Donations as $Donation) {
        $PageTotal += $Donation['Amount']; ?>
        <tr class="Table-row">
            <td class="Table-cell"><?= Users::format_username($Donation['UserID'], true) ?> (<?= Users::format_username($Donation['AddedBy']) ?>)</td>
            <td class="Table-cell"><?= display_str($Donation['Amount']) ?></td>
            <td class="Table-cell"><?= display_str($Donation['Email']) ?></td>
            <td class="Table-cell"><?= display_str($Donation['Source']) ?></td>
            <td class="Table-cell"><?= display_str($Donation['Reason']) ?></td>
            <td class="Table-cell"><?= time_diff($Donation['Time']) ?></td>
        </tr>
    <?  } ?>
    <tr class="Table-rowHeader">
        <td class="Table-cell">Page Total</td>
        <td class="Table-cell"><?= $PageTotal ?></td>
        <td class="Table-cell">Total</td>
        <td class="Table-cell" colspan="3"><?= $Total ?></td>
    </tr>
</table>
<div class="BodyNavLinks">
    <?= $Pages ?>
</div>
<? View::show_footer(); ?>