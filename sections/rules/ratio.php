<?
//Include the header
View::show_header(Lang::get('rules', 'ratio_title'), '', 'PageRuleRatio');
?>
<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="header">
        <h2 class="general"><?= Lang::get('rules', 'ratio') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <br />
        <strong><?= Lang::get('rules', 'ratio_used') ?></strong>
        <br />
        <ul>
            <li><?= Lang::get('rules', 'ratio_summary_a') ?>
            </li>
            <li><?= Lang::get('rules', 'ratio_summary_b') ?>
            </li>
            <li><?= Lang::get('rules', 'ratio_summary_c') ?>
            </li>
            <li><?= Lang::get('rules', 'ratio_summary_d') ?>
            </li>
        </ul>
        <br />
        <br />
        <strong><?= Lang::get('rules', 'ratio_used_a') ?></strong>
        <br />
        <ul>
            <li><?= Lang::get('rules', 'ratio_summary_a_a') ?>
            </li>
            <li><?= Lang::get('rules', 'ratio_summary_b_b') ?>
            </li>
            <li><?= Lang::get('rules', 'ratio_summary_c_c') ?>
            </li>
            <li><?= Lang::get('rules', 'ratio_summary_d_d') ?>
            </li>
        </ul>
        <br />
        <br />
        <div style="text-align: center;">
            <strong><?= Lang::get('rules', 'ratio_table') ?></strong>
            <br />
            <br />
            <div class="TableContainer">
                <table class="TableRuleRatio Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" data-tooltip="<?= Lang::get('rules', 'ratio_dl_title') ?>"><?= Lang::get('rules', 'ratio_dl') ?></td>
                        <td class="Table-cell"><?= Lang::get('rules', 'ratio_re_0') ?></td>
                        <td class="Table-cell"><?= Lang::get('rules', 'ratio_re_100') ?></td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] < 5 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">0&ndash;5 GB</td>
                        <td class="Table-cell">0.00</td>
                        <td class="Table-cell">0.00</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 5 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 10 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">5&ndash;10 GB</td>
                        <td class="Table-cell">0.15</td>
                        <td class="Table-cell">0.00</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 10 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 20 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">10&ndash;20 GB</td>
                        <td class="Table-cell">0.20</td>
                        <td class="Table-cell">0.00</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 20 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 30 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">20&ndash;30 GB</td>
                        <td class="Table-cell">0.30</td>
                        <td class="Table-cell">0.05</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 30 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 40 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">30&ndash;40 GB</td>
                        <td class="Table-cell">0.40</td>
                        <td class="Table-cell">0.10</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 40 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 50 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">40&ndash;50 GB</td>
                        <td class="Table-cell">0.50</td>
                        <td class="Table-cell">0.20</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 50 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 60 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">50&ndash;60 GB</td>
                        <td class="Table-cell">0.60</td>
                        <td class="Table-cell">0.30</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 60 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 80 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">60&ndash;80 GB</td>
                        <td class="Table-cell">0.60</td>
                        <td class="Table-cell">0.40</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 80 * 1024 * 1024 * 1024 && $LoggedUser['BytesDownloaded'] < 100 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">80&ndash;100 GB</td>
                        <td class="Table-cell">0.60</td>
                        <td class="Table-cell">0.50</td>
                    </tr>
                    <tr class="Table-row <?= ($LoggedUser['BytesDownloaded'] >= 100 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
                        <td class="Table-cell">100+ GB</td>
                        <td class="Table-cell">0.60</td>
                        <td class="Table-cell">0.60</td>
                    </tr>
                </table>
            </div>
        </div>
        <br />
        <br />
        <strong><?= Lang::get('rules', 'ratio_sum') ?></strong>
        <br />
        <ul>
            <li>
                <?= Lang::get('rules', 'ratio_1') ?>
            </li>
            <li>
                <?= Lang::get('rules', 'ratio_2') ?> <br />
                <br />
                <div style="text-align: center;">
                    <img style="vertical-align: middle;" src="static/styles/public/images/chart.png" alt="required ratio = (maximum required ratio) * (1 - (seeding / snatched))" />
                </div>
                <br />
                <br />
                <ul>
                    <?= Lang::get('rules', 'ratio_show') ?>
                </ul>
            </li>
            <li><?= Lang::get('rules', 'ratio_3') ?>
            </li>
        </ul>
        <br />
        <br />
        <strong><?= Lang::get('rules', 'ratio_summary_1') ?></strong>
        <br />
        <ul>
            <?= Lang::get('rules', 'ratio_summary_1_con') ?>
        </ul>
        <br />
        <br />
        <strong><?= Lang::get('rules', 'ratio_summary_2') ?></strong>
        <br />
        <ul>
            <?= Lang::get('rules', 'ratio_summary_2_con') ?>
        </ul>
        <br />
        <br />
        <strong><?= Lang::get('rules', 'ratio_summary_3') ?></strong>
        <br />
        <ul>
            <?= Lang::get('rules', 'ratio_summary_3_con') ?>
        </ul>
        <br />
        <br />
        <strong><?= Lang::get('rules', 'ratio_summary_4') ?></strong>
        <br />
        <ul>
            <?= Lang::get('rules', 'ratio_summary_4_con') ?>
        </ul>
        <br />
        <br />
    </div>
    <div class="header">
        <h2 class="general"><?= Lang::get('rules', 'hnr') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <?= Lang::get('rules', 'hnr_rules_body') ?>
    </div>
</div>
<?
View::show_footer();
?>