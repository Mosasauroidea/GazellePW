<?
//Include the header
View::show_header(Lang::get('rules', 'rules'), '', 'PageRuleHome');
?>
<!-- General Rules -->
<div class="PageBody">
    <? include('jump.php'); ?>
    <div class="header">
        <h2 id="general"><?= Lang::get('rules', 'golden_rules') ?></h2>
        <p><?= Lang::get('rules', 'golden_rules_used') ?></p>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <? Rules::display_golden_rules(); ?>
    </div>
    <!-- END General Rules -->

</div>
<?
View::show_footer();
?>