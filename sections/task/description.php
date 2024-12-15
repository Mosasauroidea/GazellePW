<?php
View::show_header(Lang::get('better', 'artists_with_no_descriptions'));

$DB->query("
SELECT COUNT(*) as count FROM artists_group AS a
LEFT JOIN wiki_artists AS wiki ON wiki.RevisionID = a.RevisionID
WHERE wiki.Body is NULL OR wiki.Body = ''");
$row = $DB->next_record();
$total = $row['count'];
$total_str = number_format($total);
$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
$page = max(1, $page);
$limit = TORRENTS_PER_PAGE;
$offset = TORRENTS_PER_PAGE * ($page - 1);
$DB->query("
SELECT
    a.ArtistID,
    a.Name
FROM artists_group AS a
    LEFT JOIN wiki_artists AS wiki ON wiki.RevisionID = a.RevisionID
WHERE wiki.Body is NULL OR wiki.Body = ''
ORDER BY a.Name
LIMIT {$limit} OFFSET {$offset}");
$artists = $DB->to_array('ArtistID', MYSQLI_ASSOC);
$pages = Format::get_pages($page, $total, TORRENTS_PER_PAGE);
?>
<div class="header">
    <h2><?= Lang::get('better', 'artists_that_are_missing_descriptions') ?></h2>

    <div class="linkbox">
        <a href="better.php" class="brackets"><?= Lang::get('better', 'back_to_better_php_list') ?></a>
    </div>
    <div class="linkbox"><?= $pages ?></div>
</div>

<div class="thin box pad">
    <h3><?= Lang::get('better', 'there_are_artists_remaining_before') ?> <?= $total_str ?> <?= Lang::get('better', 'there_are_artists_remaining_after') ?></h3>
    <div class="table_container border">
        <table class="torrent_table">
            <?

            foreach ($artists as $id => $artist) {
            ?>
                <tr class="torrent torrent_row">
                    <td><a href='artist.php?id=<?= $id ?>' target='_blank'><?= $artist['Name'] ?></a></td>
                </tr>
            <?
            }
            ?>
        </table>
    </div>
</div>
<?

View::show_footer();
