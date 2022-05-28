<?php View::show_header('Site stats', '', 'pageStatHome'); ?>

<div class="LayoutBody">
    <h3 id="general">Pursuit of Perfection</h3>
    <div class="BoxBody" style="padding: 10px 10px 10px 20px;">
        <ul>
            <li><a href='stats.php?action=users'>User Stats</a>
            <li><a href='stats.php?action=torrents'>Torrent Stats</a>
    </div>
</div>
<?php
View::show_footer();
