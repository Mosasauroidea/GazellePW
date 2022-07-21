<?
View::show_header(Lang::get('rules', 'ratio_title'), '', 'PageRuleRatio');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="HtmlText BoxBody" id="Rules-Ratio-mdx" mdx></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Ratio.mdx')
    })
</script>


<?
View::show_footer();
?>