<?php

namespace Gazelle;

class Top10 {
    public static function renderLinkbox(string $selected) {
?>
        <div class="BodyNavLinks">
            <a href="top10.php?type=torrents" class="brackets"><?= self::selectedLink("Torrents", $selected == "torrents") ?></a>
            <a href="top10.php?type=users" class="brackets"><?= self::selectedLink("Users", $selected == "users") ?></a>
            <a href="top10.php?type=tags" class="brackets"><?= self::selectedLink("Tags", $selected == "tags") ?></a>
            <?
            if (CONFIG['ENABLE_VOTES']) {
            ?>
                <a href="top10.php?type=votes" class="brackets"><?= self::selectedLink("Favorites", $selected == "votes") ?></a>
            <? } ?>
            <a href="top10.php?type=donors" class="brackets"><?= self::selectedLink("Donors", $selected == "donors") ?></a>
        </div>
        <?php
    }

    private static function selectedLink($string, $selected) {
        if ($selected) {
            return "<strong>$string</strong>";
        } else {
            return $string;
        }
    }

    public static function renderArtistTile($artist, $category) {
        if (self::isValidArtist($artist)) {
            switch ($category) {
                case 'weekly':
                case 'hyped':
                    self::renderTile("artist.php?artistname=", $artist['name'], $artist['image'][3]['#text']);
                    break;
                default:
                    break;
            }
        }
    }

    private static function renderTile($url, $name, $image) {
        if (!empty($image)) {
            $name = display_str($name);
        ?>
            <li>
                <a href="<?= $url ?><?= $name ?>">
                    <img class="large_tile" alt="<?= $name ?>" data-tooltip="<?= $name ?>" src="<?= \ImageTools::process($image) ?>" />
                </a>
            </li>
        <?php
        }
    }


    public static function renderArtistList($artist, $category) {
        if (self::isValidArtist($artist)) {
            switch ($category) {

                case 'weekly':
                case 'hyped':
                    self::renderList("artist.php?artistname=", $artist['name'], $artist['image'][3]['#text']);
                    break;
                default:
                    break;
            }
        }
    }

    private static function renderList($url, $name, $image) {
        if (!empty($image)) {
            $image = \ImageTools::process($image);
            $tooltip = "data-tooltip-image=\"&lt;img class=&quot;large_tile&quot; src=&quot;$image&quot; alt=&quot;&quot; /&rsaquo;\"";
            $name = display_str($name);
        ?>
            <li>
                <a data-title-plain="<?= $name ?>" <?= $tooltip ?> href="<?= $url ?><?= $name ?>"><?= $name ?></a>
            </li>
<?php
        }
    }

    private static function isValidArtist($artist) {
        return $artist['name'] != '[unknown]';
    }
}
