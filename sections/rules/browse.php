<?
View::show_header(t('server.rules.rules'), '', 'PageRuleHome');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-GoldenRules-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/GoldenRules.mdx')
    })
</script>

<?
View::show_footer();
?>