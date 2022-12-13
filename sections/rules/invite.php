<?
View::show_header(t('server.rules.invite_title'), '', 'PageRuleInvite');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Invite-mdx" mdx></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Invite.mdx')
    })
</script>

<?
View::show_footer();
?>