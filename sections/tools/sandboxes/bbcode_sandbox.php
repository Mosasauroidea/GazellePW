<?
if (!check_perms('users_mod')) {
    error(403);
}
$Title = Lang::get('tools', 'bbcode_sandbox');
View::show_header($Title, 'bbcode_sandbox');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= $Title ?></h2>
</div>
<div class="LayoutBody">
    <div id="bbcode_sandbox_container">
        <textarea class="Input" id="sandbox" class="wbbarea" onkeyup="resize('sandbox');" name="body" cols="90" rows="8"></textarea>
        <br />
        <br />
        <div id="bbcode_sandbox_preview_container">
            <table class="forum_post wrap_overflow box vertical_margin">
                <tr>
                    <td class="body" id="preview">
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<?
View::show_footer();
