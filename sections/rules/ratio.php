<?
View::show_header(t('server.rules.ratio_title'), '', 'PageRuleRatio');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Ratio-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Ratio.mdx')
    })
</script>


<?
View::show_footer();
?>