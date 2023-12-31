<?
View::show_header(t('server.rules.slots_rules'), '', 'PageRuleSlots');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Slots-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Slots.mdx')
    })
</script>

<?
View::show_footer();
?>