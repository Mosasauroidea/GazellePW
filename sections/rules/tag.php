<?
//Include the header
View::show_header(Lang::get('rules', 'tags_title'), '', 'PageRuleTag');
?>
<!-- General Rules -->
<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="header">
        <h2 id="general"><?= Lang::get('rules', 'tags_title') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <? Rules::display_site_tag_rules(false) ?>
    </div>
    <!-- END General Rules -->
</div>
<?
View::show_footer();
?>