<?
//Include the header
View::show_header(Lang::get('rules', 'collages_title'), '', 'PageRuleCollage');
?>
<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="header">
        <h2 class="general"><?= Lang::get('rules', 'collages_title') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <ul>
            <?= Lang::get('rules', 'collages_summary') ?>
        </ul>
    </div>
</div>
<?
View::show_footer();
?>