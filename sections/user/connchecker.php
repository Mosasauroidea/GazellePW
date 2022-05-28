<?
View::show_header('Connectability Checker', '', 'PageUserConnChecker');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><a href="user.php?id=<?= $LoggedUser['ID'] ?>"><?= $LoggedUser['Username'] ?></a> &gt; Connectability Checker</h2>
    </div>
    <div class="BodyNavLinks"></div>
    <div class="BoxBody">This page has been disabled because the results have been inaccurate. Try a smarter and more reliable service, like <a href="http://www.canyouseeme.org">http://www.canyouseeme.org</a>.</div>
</div>
<? View::show_footer(); ?>