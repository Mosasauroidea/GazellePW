<?
View::show_header(Lang::get('rules.tags_title'), '', 'PageRuleTag');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="HtmlText BoxBody" id="Rules-Tags-mdx" mdx></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Tags.mdx')
    })
</script>

<?
View::show_footer();
?>