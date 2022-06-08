<?

View::show_header('Challenge', '', 'PageContestIntro');
$Contest = Contest::get_current_contest();

if ($Contest !== false and strlen($Contest['Banner'])) {
?>
    <div class="pad">
        <img border="0" src="<?= $Contest['Banner'] ?>" alt="<?= $Contest['Name'] ?>" width="640" height="125" style="display: block; margin-left: auto; margin-right: auto;" />
    </div>
<?  } ?>
<div class="BodyNavLinks">
    <a href="contest.php?action=leaderboard" class="brackets">Leaderboard</a>
    <?= (check_perms('users_mod')) ? '<a href="contest.php?action=admin" class="brackets">Admin</a>' : '' ?>
</div>

<div class="LayoutBody Box">
    <? if ($Contest === false) { ?>
        <div class="Box-body">
            <p>There is no contest at the moment.</p>
        </div>
    <?  } else { ?>
        <div class="Box-body HtmlText">
            <?= Text::full_format($Contest['WikiText']) ?>
        </div>
    <? } ?>
</div>

<? View::show_footer();
