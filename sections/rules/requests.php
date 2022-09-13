<?
View::show_header(t('server.rules.requests_title'), '', 'PageRuleRequest');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Requests-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Requests.mdx')
    })
</script>

<?
View::show_footer();
?>