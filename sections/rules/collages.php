<?
View::show_header(t('server.rules.collages_title'), '', 'PageRuleCollage');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Collages-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Collages.mdx')
    })
</script>

<?
View::show_footer();
?>