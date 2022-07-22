<?
include(CONFIG['SERVER_ROOT'] . '/sections/reports/array.php');

if (empty($_GET['type']) || empty($_GET['id']) || !is_number($_GET['id'])) {
    error(404);
}

if (!array_key_exists($_GET['type'], $Types)) {
    error(403);
}
$Short = $_GET['type'];
$Type = $Types[$Short];

$ID = $_GET['id'];

switch ($Short) {
    case 'user':
        $DB->query("
			SELECT Username
			FROM users_main
			WHERE ID = $ID");
        if (!$DB->has_results()) {
            error(404);
        }
        list($Username) = $DB->next_record();
        break;

    case 'request_update':
        $NoReason = true;
        $DB->query("
			SELECT Title, Description, TorrentID, CategoryID, Year
			FROM requests
			WHERE ID = $ID");
        if (!$DB->has_results()) {
            error(404);
        }
        list($Name, $Desc, $Filled, $CategoryID, $Year) = $DB->next_record();
        if ($Filled || ($CategoryID != 0)) {
            error(403);
        }
        break;

    case 'request':
        $DB->query("
			SELECT Title, Description, TorrentID
			FROM requests
			WHERE ID = $ID");
        if (!$DB->has_results()) {
            error(404);
        }
        list($Name, $Desc, $Filled) = $DB->next_record();
        break;

    case 'collage':
        $DB->query("
			SELECT Name, Description
			FROM collages
			WHERE ID = $ID");
        if (!$DB->has_results()) {
            error(404);
        }
        list($Name, $Desc) = $DB->next_record();
        break;

    case 'thread':
        $DB->query("
			SELECT ft.Title, ft.ForumID, um.Username
			FROM forums_topics AS ft
				JOIN users_main AS um ON um.ID = ft.AuthorID
			WHERE ft.ID = $ID");
        if (!$DB->has_results()) {
            error(404);
        }
        list($Title, $ForumID, $Username) = $DB->next_record();
        $DB->query("
			SELECT MinClassRead
			FROM forums
			WHERE ID = $ForumID");
        list($MinClassRead) = $DB->next_record();
        if (
            !empty($LoggedUser['DisableForums'])
            || ($MinClassRead > $LoggedUser['EffectiveClass'] && (!isset($LoggedUser['CustomForums'][$ForumID]) || $LoggedUser['CustomForums'][$ForumID] == 0))
            || (isset($LoggedUser['CustomForums'][$ForumID]) && $LoggedUser['CustomForums'][$ForumID] == 0)
        ) {
            error(403);
        }
        break;

    case 'post':
        $DB->query("
			SELECT fp.Body, fp.TopicID, um.Username
			FROM forums_posts AS fp
				JOIN users_main AS um ON um.ID = fp.AuthorID
			WHERE fp.ID = $ID");
        if (!$DB->has_results()) {
            error(404);
        }
        list($Body, $TopicID, $Username) = $DB->next_record();
        $DB->query("
			SELECT ForumID
			FROM forums_topics
			WHERE ID = $TopicID");
        list($ForumID) = $DB->next_record();
        $DB->query("
			SELECT MinClassRead
			FROM forums
			WHERE ID = $ForumID");
        list($MinClassRead) = $DB->next_record();
        if (
            !empty($LoggedUser['DisableForums'])
            || ($MinClassRead > $LoggedUser['EffectiveClass'] && (!isset($LoggedUser['CustomForums'][$ForumID]) || $LoggedUser['CustomForums'][$ForumID] == 0))
            || (isset($LoggedUser['CustomForums'][$ForumID]) && $LoggedUser['CustomForums'][$ForumID] == 0)
        ) {
            error(403);
        }
        break;

    case 'comment':
        $DB->query("
			SELECT c.Body, um.Username
			FROM comments AS c
				JOIN users_main AS um ON um.ID = c.AuthorID
			WHERE c.ID = $ID");
        if (!$DB->has_results()) {
            error(404);
        }
        list($Body, $Username) = $DB->next_record();
        break;
}

View::show_header(Lang::get('reports.report_a') . $Type['title'], 'bbcode,jquery.validate,form_validate', 'PageReportShow');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('reports.h2_report') ?><?= $Type['title'] ?></h2>
    </div>
    <div class="Box">
        <div class="Box-header"><?= Lang::get('reports.reporting_guidelines') ?></div>
        <div class="Box-body">
            <p><?= Lang::get('reports.reporting_guidelines_intro') ?></p>
            <ul class="Postlist">
                <?
                foreach ($Type['guidelines'] as $Guideline) { ?>
                    <li><?= $Guideline ?></li>
                <?  } ?>
            </ul>
            <p><?= Lang::get('reports.reporting_guidelines_summary') ?></p>
        </div>
    </div>
    <div class="Form-rowList" variant="header">
        <?

        switch ($Short) {
            case 'user':
        ?>
                <div class="Form-rowHeader"><?= Lang::get('reports.reporting_the_user') ?> > <?= display_str($Username) ?></div>
            <?
                break;
            case 'request_update':
            ?>
                <p><?= Lang::get('reports.reporting_the_request') ?>:</p>
                <table class=" TableReportDetail">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('reports.title') ?></td>
                        <td class="Table-cell"><?= Lang::get('reports.description') ?></td>
                        <td class="Table-cell"><?= Lang::get('reports.filled') ?>?</td>
                    </tr>
                    <tr class="Table-row">
                        <td class="Table-cell">
                            <?= display_str($Name) ?>
                        </td>
                        <td class="Table-cell">
                            <div class="HtmlText">
                                <?= Text::full_format($Desc) ?>
                            </div>
                        </td>
                        <td class="Table-cell">
                            <strong><?= ($Filled == 0 ? Lang::get('reports.no') : Lang::get('reports.yes')) ?></strong>
                        </td>
                    </tr>
                </table>
                <br />

                <div class="BoxBody center">
                    <p><strong><?= Lang::get('reports.way_to_increase_turnover_rate') ?></strong></p>
                    <form class="create_form" id="report_form" name="report" action="" method="post">
                        <input type="hidden" name="action" value="takereport" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <input type="hidden" name="id" value="<?= $ID ?>" />
                        <input type="hidden" name="type" value="<?= $Short ?>" />
                        <table class="layout">
                            <tr>
                                <td class="label"><?= Lang::get('reports.year_required') ?></td>
                                <td>
                                    <input class="Input required" type="text" size="4" name="year" />
                                </td>
                            </tr>
                            <tr>
                                <td class="label"><?= Lang::get('reports.release_type') ?></td>
                                <td>
                                    <select class="Input" id="releasetype" name="releasetype">
                                        <option class="Select-option" value="0">---</option>
                                        <? foreach ($ReleaseTypes as $Key => $Val) { ?>
                                            <option class="Select-option" value="<?= $Key ?>" <?= (!empty($ReleaseType) ? ($Key == $ReleaseType ? ' selected="selected"' : '') : '') ?>><?= $Val ?></option>
                                        <?      } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label"><?= Lang::get('reports.comment') ?></td>
                                <td>
                                    <textarea class="Input" rows="8" cols="80" name="comment" class="required"></textarea>
                                </td>
                            </tr>
                        </table>
                        <br />
                        <br />
                        <input class="Button" type="submit" value="Submit report" />
                    </form>
                </div>
            <?
                break;
            case 'request':
            ?>
                <div class="Form-rowHeader"><?= Lang::get('reports.h2_report') ?><?= $Type['title'] ?> </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.title') ?>:</div>
                    <div class="Form-inputs"><?= display_str($Name) ?></div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.description') ?>:</div>
                    <div class="Form-inputs HtmlText PostArticle"><?= Text::full_format($Desc) ?></div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.filled') ?>:</div>
                    <div class="Form-inputs HtmlText PostArticle"><strong><?= ($Filled == 0 ? Lang::get('reports.no') : Lang::get('reports.yes')) ?></strong></div>
                </div>

            <?
                break;
            case 'collage':
            ?>
                <div class="Form-rowHeader"><?= Lang::get('reports.h2_report') ?><?= $Type['title'] ?> ><?= display_str($Name) ?></div>
            <?
                break;
            case 'thread':
            ?>
                <div class="Form-rowHeader"><?= Lang::get('reports.h2_report') ?><?= $Type['title'] ?> </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.username') ?>:</div>
                    <div class="Form-inputs"><?= display_str($Username) ?></div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.title') ?>:</div>
                    <div class="Form-inputs HtmlText PostArticle"><?= Text::full_format($Title) ?></div>
                </div>

            <?
                break;
            case 'post':
            ?>
                <div class="Form-rowHeader"><?= Lang::get('reports.h2_report') ?><?= $Type['title'] ?> </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.username') ?>:</div>
                    <div class="Form-inputs"><?= display_str($Username) ?></div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.body') ?>:</div>
                    <div class="Form-inputs HtmlText PostArticle"><?= Text::full_format($Body) ?></div>
                </div>
            <?
                break;
            case 'comment':
            ?>
                <div class="Form-rowHeader"><?= Lang::get('reports.h2_report') ?><?= $Type['title'] ?> </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.username') ?>:</div>
                    <div class="Form-inputs"><?= display_str($Username) ?></div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= Lang::get('reports.body') ?>:</div>
                    <div class="Form-inputs HtmlText PostArticle"><?= Text::full_format($Body) ?></div>
                </div>

            <?
                break;
        }
        if (empty($NoReason)) {
            ?>
            <form class="create_form" name="report" id="report_form" action="" method="post">
                <input type="hidden" name="action" value="takereport" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="id" value="<?= $ID ?>" />
                <input type="hidden" name="type" value="<?= $Short ?>" />
                <div class="Form-row">
                    <div class="Form-items">
                        <? new TEXTAREA_PREVIEW('reason', 'reason', '', 60, 8, true, true, false, ['placeholder:"' . Lang::get('reports.reason') . '"']); ?>
                    </div>
                </div>
                <div class="Form-row">
                    <input class="Button" type="submit" value="Submit report" />
                </div>
            </form>
    </div>
<?

        } /* close <div class="LayoutBody"> */ ?>
</div>
<?
View::show_footer();
?>