<?
View::show_header(t('server.rules.chat_title'), '', 'PageRuleChat');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Chat-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Chat.mdx')
    })
</script>

<?
View::show_footer();
?>