<?php

/**********************************************************************
 *>>>>>>>>>>>>>>>>>>>>>>>>>>> User search <<<<<<<<<<<<<<<<<<<<<<<<<<<<*
 **********************************************************************/

if (!empty($_GET['search'])) {

    $_GET['username'] = $_GET['search'];
}

define('USERS_PER_PAGE', 30);

if (isset($_GET['username'])) {

    $_GET['username'] = trim($_GET['username']);
    // form submitted
    $Val->SetFields('username', '1', 'username', 'Please enter a username.');
    $Err = $Val->ValidateForm($_GET);

    if (!$Err) {
        // Passed validation. Let's rock.
        list($Page, $Limit) = Format::page_limit(USERS_PER_PAGE);
        if ($Page > 10) {
            $Page = 10;
            $Limit = sprintf("%d, %d", ($Page - 1) * USERS_PER_PAGE, USERS_PER_PAGE);
        }
        $DB->prepared_query("
			SELECT
				SQL_CALC_FOUND_ROWS
				ID,
				Username,
				Enabled,
				PermissionID,
				Donor,
				Warned
			FROM users_main AS um
				JOIN users_info AS ui ON ui.UserID = um.ID
			WHERE Username = ?
			ORDER BY Username
			LIMIT $Limit", $_GET['username']);
        $Results = $DB->to_array();
        $DB->query('SELECT FOUND_ROWS()');
        list($NumResults) = $DB->next_record();
        if ($NumResults > 300) {
            $NumResults = 300;
        }
    }
}

View::show_header(t('server.user.user_search'), '', 'PageUserSearch');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h3 class="BodyHeader-nav"><?= t('server.user.search_results') ?></h3>
    </div>
    <? $Pages = Format::get_pages($Page, $NumResults, USERS_PER_PAGE, 9);
    if ($Pages) { ?>
        <div class="BodyNavLinks pager"><?= ($Pages) ?></div>
    <?  } ?>
    <form class="Form SearchPage Box SearchUser" name="users" action="user.php" method="get">
        <input type="hidden" name="action" value="search" />
        <div class="SearchPageBody">
            <table class="Form-rowList">
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.user.username') ?>:</td>
                    <td class="Form-inputs">
                        <input placeholder="<?= t('server.user.search_type_strict') ?>" class="Input" type="text" name="username" size="60" value="<?= display_str($_GET['username']) ?>" />
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
    <? if (count($Results) > 0) { ?>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
        <div class="TableContainer">
            <table class="Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell" width="50%"><?= t('server.user.username') ?></td>
                    <td class="Table-cell"><?= t('server.user.primary_class') ?></td>
                </tr>
                <?
                foreach ($Results as $Result) {
                    list($UserID, $Username, $Enabled, $PermissionID, $Donor, $Warned) = $Result;
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= Users::format_username($UserID, true, true, true, true); ?></td>
                        <td class="Table-cell"><?= Users::make_class_string($PermissionID); ?></td>
                    </tr>
                <?  } ?>
            </table>
        </div>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
    <? } else {
        if (!empty($_GET['username'])) {
            View::line(t('server.common.no_results'));
        }
    } ?>
</div>
<? View::show_footer(); ?>