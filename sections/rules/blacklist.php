<?
View::show_header(t('server.rules.blacklist_title'), '', 'PageRuleBlacklist');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Blacklist-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Blacklist.mdx')
    })
</script>

<?
View::show_footer();
?>