<?
View::show_header(Lang::get('rules', 'rules'), '', 'PageRuleHome');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="HtmlText BoxBody" id="Rules-GoldenRules-mdx" mdx></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/GoldenRules.mdx')
    })
</script>

<?
View::show_footer();
?>