<?

class Top10View {

    public static function render_linkbox($Selected, $Class) {
?>
        <div class="<?= $Class ?>">

            <a href="top10.php?type=torrents" class="brackets"><?= self::get_selected_link(Lang::get('top10', 'torrents'), $Selected == "torrents") ?></a>
            <a href="top10.php?type=original" class="brackets"><?= self::get_selected_link(Lang::get('top10', 'original'), $Selected == "original") ?></a>
            <a href="top10.php?type=users" class="brackets"><?= self::get_selected_link(Lang::get('top10', 'users'), $Selected == "users") ?></a>
            <a href="top10.php?type=tags" class="brackets"><?= self::get_selected_link(Lang::get('top10', 'tags'), $Selected == "tags") ?></a>
            <?
            if (ENABLE_VOTES) {
            ?>
                <a href="top10.php?type=votes" class="brackets"><?= self::get_selected_link(Lang::get('top10', 'favorites'), $Selected == "votes") ?></a>
            <? } ?>
            <a href="top10.php?type=donors" class="brackets"><?= self::get_selected_link(Lang::get('top10', 'donors'), $Selected == "donors") ?></a>
        </div>
        <?
    }

    private static function get_selected_link($String, $Selected) {
        if ($Selected) {
            return "<strong>$String</strong>";
        } else {
            return $String;
        }
    }

    public static function render_artist_tile($Artist, $Category) {
        if (self::is_valid_artist($Artist)) {
            switch ($Category) {
                case 'weekly':
                case 'hyped':
                    self::render_tile("artist.php?artistname=", $Artist['name'], $Artist['image'][3]['#text']);
                    break;
                default:
                    break;
            }
        }
    }

    private static function render_tile($Url, $Name, $Image) {
        if (!empty($Image)) {
            $Name = display_str($Name);
        ?>
            <li>
                <a href="<?= $Url ?><?= $Name ?>">
                    <img class="large_tile" alt="<?= $Name ?>" data-tooltip="<?= $Name ?>" src="<?= ImageTools::process($Image) ?>" />
                </a>
            </li>
        <?
        }
    }


    public static function render_artist_list($Artist, $Category) {
        if (self::is_valid_artist($Artist)) {
            switch ($Category) {

                case 'weekly':
                case 'hyped':
                    self::render_list("artist.php?artistname=", $Artist['name'], $Artist['image'][3]['#text']);
                    break;
                default:
                    break;
            }
        }
    }

    private static function render_list($Url, $Name, $Image) {
        if (!empty($Image)) {
            $UseTooltipster = !isset(G::$LoggedUser['Tooltipster']) || G::$LoggedUser['Tooltipster'];
            $Image = ImageTools::process($Image);
            $Tooltip = "data-tooltip-image=\"&lt;img class=&quot;large_tile&quot; src=&quot;$Image&quot; alt=&quot;&quot; /&gt;\"";
            $Name = display_str($Name);
        ?>
            <li>
                <a data-title-plain="<?= $Name ?>" <?= $Tooltip ?> href="<?= $Url ?><?= $Name ?>"><?= $Name ?></a>
            </li>
<?
        }
    }

    private static function is_valid_artist($Artist) {
        return $Artist['name'] != '[unknown]';
    }
}
