<?
View::show_header(Lang::get('rules', 'chat_title'), '', 'PageRuleChat');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="HtmlText BoxBody" id="Rules-Chat-mdx" mdx></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Chat.mdx')
    })
</script>

<?
View::show_footer();
?>