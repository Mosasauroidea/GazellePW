<?
View::show_header(t('server.rules.bonus_title'), '', 'PageRuleBonus');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Bonus-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Bonus.mdx')
    })
</script>

<?
View::show_footer();
?>