<?
View::show_header(t('server.rules.collages_title'), '', 'PageRuleCollage');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="HtmlText BoxBody" id="Rules-Collages-mdx" mdx></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Collages.mdx')
    })
</script>

<?
View::show_footer();
?>