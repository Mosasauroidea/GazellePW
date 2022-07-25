<?
View::show_header(t('server.register.register'), '', 'PageResigerCode');
?>
<div style="width: 500px;">
    <form class="auth_form" name="invite" method="get" action="register.php">
        Please enter your invite code into the box below.<br /><br />
        <table class="layout" cellpadding="2" cellspacing="1" border="0" align="center">
            <tr valign="top">
                <td align="right">Invite&nbsp;</td>
                <td align="left"><input class="Input" type="text" name="invite" id="invite" /></td>
            </tr>
            <tr>
                <td colspan="2" align="right"><input class="Button" type="submit" name="submit" value="Begin!" /></td>
            </tr>
        </table>
    </form>
</div>
<?
View::show_footer();
?>