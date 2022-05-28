<?
//Include the header
View::show_header(Lang::get('rules', 'chat_title'), '', 'PageRuleChat');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="header">
        <h2 id="general"><?= Lang::get('rules', 'chat_general') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <?= Lang::get('rules', 'chat_general_rules') ?>
    </div>
    <!-- <br /> -->
    <!-- Forum Rules -->
    <div class="header">
        <h2 id="forums"><?= Lang::get('rules', 'chat_forums') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <? Rules::display_forum_rules() ?>
    </div>
    <!-- END Forum Rules -->

    <!-- IRC Rules -->
    <div class="header">
        <h2 id="irc"><?= Lang::get('rules', 'chat_groups') ?></h2>
    </div>
    <div class="BoxBody HtmlText rule_summary">
        <? Rules::display_irc_chat_rules() ?>
    </div>
</div>
<?
View::show_footer();
?>