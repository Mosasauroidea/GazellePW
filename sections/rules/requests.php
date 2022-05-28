<?
//Include the header
View::show_header(Lang::get('rules', 'requests_title'), '', 'PageRuleRequest');
?>
<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="header">
        <h2 class="general"><?= Lang::get('rules', 'requests_title') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <ul>
            <?= Lang::get('rules', 'requests_summary') ?>
        </ul>
    </div>

</div>
<?
View::show_footer();
?>