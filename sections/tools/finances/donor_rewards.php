<?
if (!check_perms('users_mod')) {
    error(403);
}

define('USERS_PER_PAGE', 50);
list($Page, $Limit) = Format::page_limit(USERS_PER_PAGE);

$SearchQuery = '';
if ($_GET['username']) {
    $SearchString = db_string($_GET['username']);
    $SearchQuery = " WHERE Username LIKE '%$SearchString%' ";
}

$Title = t('server.tools.donor_rewards');

$DB->query("
	SELECT
		SQL_CALC_FOUND_ROWS
		u.Username,
		d.UserID,
		d.Rank,
		d.Hidden,
		d.DonationTime,
		r.IconMouseOverText,
		r.AvatarMouseOverText,
		r.CustomIcon,
		r.SecondAvatar,
		r.CustomIconLink
	FROM users_donor_ranks AS d
		LEFT JOIN users_main AS u ON u.ID = d.UserID
		LEFT JOIN donor_rewards AS r ON r.UserID = d.UserID
	$SearchQuery
	ORDER BY d.Rank DESC
	LIMIT $Limit");

$Users = $DB->to_array();
$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();
$Pages = Format::get_pages($Page, $Results, USERS_PER_PAGE, 9);

View::show_header($Title, '', 'PageToolDonorReward');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>

    </div>

    <form class="Form Box SearchPage" action="" method="get">
        <input type="hidden" name="action" value="donor_rewards" />
        <div class="SearchPageBody">
            <div class="Form-rowList">
                <div class="Form-row">
                    <div class="Form-label">
                        <?= t('server.tools.username_search') ?>:
                    </div>
                    <div class="Form-inputs">
                        <input class="Input" type="text" name="username" />
                    </div>
                </div>
            </div>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-actions">
                <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
            </div>
        </div>
    </form>
    <? View::pages($Pages); ?>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.tools.username') ?></td>
            <td class="Table-cell"><?= t('server.tools.rank') ?></td>
            <td class="Table-cell"><?= t('server.tools.hidden') ?></td>
            <td class="Table-cell"><?= t('server.tools.last_donated') ?></td>
            <td class="Table-cell"><?= t('server.tools.icon_text') ?></td>
            <td class="Table-cell"><?= t('server.tools.icon') ?></td>
            <td class="Table-cell"><?= t('server.tools.icon_link') ?></td>
            <td class="Table-cell"><?= t('server.tools.avatar_text') ?></td>
            <td class="Table-cell"><?= t('server.tools.second_avatar') ?></td>
        </tr>
        <?
        foreach ($Users as $User) {
            $UserInfo = Users::user_info($User['UserID']);
            $Username = $UserInfo['Username'];
        ?>
            <tr class="Table-row">
                <td class="Table-cell"><?= Users::format_username($User['UserID'], false, true, true, false, false, true) ?></td>
                <td class="Table-cell"><?= $User['Rank'] ?></td>
                <td class="Table-cell"><?= $User['Hidden'] ? t('server.tools.yes') : t('server.tools.no') ?></td>
                <td class="Table-cell"><?= time_diff($User['DonationTime']) ?></td>
                <td class="Table-cell" style="word-wrap: break-word;">
                    <?= $User['IconMouseOverText'] ?>
                </td>
                <td class="Table-cell" style="word-wrap: break-word;">
                    <? if (!empty($User['CustomIcon'])) { ?>
                        <img src="<?= ImageTools::process($User['CustomIcon'], false, 'donoricon', $User['UserID']) ?>" width="15" height="13" alt="" />
                    <?      } ?>
                </td>
                <td class="Table-cell" style="word-wrap: break-word;">
                    <?= $User['CustomIconLink'] ?>
                </td>
                <td class="Table-cell" style="word-wrap: break-word;">
                    <?= $User['AvatarMouseOverText'] ?>
                </td>
                <td class="Table-cell" style="word-wrap: break-word;">
                    <?= $User['SecondAvatar'] ?>
                </td>
            </tr>
        <?
            $Row = $Row === 'b' ? 'a' : 'b';
        } // foreach
        ?>
    </table>
    <? View::pages($Pages); ?>
</div>
<?
View::show_footer();
