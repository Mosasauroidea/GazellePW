<?php

/**
 * This class outputs a table that can be used to sort torrents through a drag/drop
 * interface, an automatic column sorter, or manual imput.
 *
 * It places checkboxes to delete items.
 *
 * (It creates a div#thin.)
 *
 * It can be used for Bookmarks, Collages, or anywhere where torrents are managed.
 */
class MASS_USER_TORRENTS_TABLE_VIEW {
    /**
     * Used to set text the page heading (h2 tag)
     * @var string $Heading
     */
    private $Heading = 'Manage Torrents';

    /**
     * Sets the value of the input name="type"
     * Later to be used as $_POST['type'] in a form processor
     * @var string $EditType
     */
    private $EditType;

    /**
     * Flag for empty $TorrentList
     * @var bool $HasTorrentList
     */
    private $HasTorrents;

    /**
     * Internal reference to the TorrentList
     * @var array $TorrentList
     */
    private $TorrentList;

    /**
     * Ref. to $CollageDataList
     * @var array $CollageDataList
     */
    private $CollageDataList;

    /**
     * Counter for number of groups
     * @var int $NumGroups
     */
    private $NumGroups = 0;

    /**
     * When creating a new instance of this class, TorrentList and
     * CollageDataList must be passed. Additionally, a heading can be added.
     *
     * @param array $TorrentList
     * @param array $CollageDataList
     * @param string $EditType
     * @param string $Heading
     */
    public function __construct(array &$TorrentList, array &$CollageDataList, $EditType, $Heading = null) {
        $this->set_heading($Heading);
        $this->set_edit_type($EditType);

        $this->TorrentList = $TorrentList;
        $this->CollageDataList = $CollageDataList;

        $this->HasTorrents = !empty($TorrentList);
        if (!$this->HasTorrents) {
            $this->no_torrents();
        }
    }

    private function no_torrents() {
?>
        <div class="LayoutBody">
            <div class="BodyHeader">
                <h2 class="BodyHeader-nav"><?= t('server.bookmarks.no_torrents_found') ?></h2>
            </div>
            <div class="BodyContent">
                <div class="Box">
                    <div class="Box-body" align="center">
                        <p><?= t('server.bookmarks.add_some_torrents_and_come_back_later') ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renders a complete page and table
     */
    public function render_all() {
        $this->header();
        $this->body();
        $this->footer();
    }

    /**
     * Renders a comptele page/table header: div#thin, h2, scripts, notes,
     * form, table, etc.
     */
    public function header() {
        if ($this->HasTorrents) {
        ?>

            <div class="LayoutBody">
                <div class="BodyHeader">
                    <h2 class="BodyHeader-nav"><?= display_str($this->Heading) ?></h2>
                </div>
                <form class="BodyContent" action="bookmarks.php" method="post" id="drag_drop_collage_form">
                    <div class="Box" id="drag_drop_textnote">
                        <div class="Box-header">
                            <div class="Box-headerTitle"><?= t('server.bookmarks.sorting') ?></div>
                            <div class="Box-headerActions" id="sorting_head"></div>
                        </div>
                        <div class="Box-body">
                            <td> <?= t('server.bookmarks.drag_drop_textnote_torrents') ?></td>
                        </div>
                    </div>

                    <?php $this->buttons(); ?>

                    <table class="TableManageCollage Table" id="manage_collage_table">
                        <thead>
                            <tr class="Table-rowHeader">
                                <th width=50px class="Table-cell Table-cellLeft" data-sorter="false"><?= t('server.bookmarks.order') ?></th>
                                <th width=50px class="Table-cell Table-cellLeft"><span><abbr data-tooltip="<?= t('server.bookmarks.current_order') ?>">#</abbr></span></th>
                                <th class="Table-cell Table-cellLeft"><span><?= t('server.bookmarks.year') ?></span></th>
                                <th class="Table-cell Table-cellLeft" data-sorter="ignoreArticles"><span><?= t('server.common.torrent') ?></span></th>
                                <th class="Table-cell Table-cellLeft" data-sorter="relativeTime"><span><?= t('server.bookmarks.bookmarked') ?></span></th>
                                <th class="Table-cell" id="check_all" data-sorter="false"><span><?= t('server.bookmarks.remove') ?></span></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                    }
                }

                /**
                 * Closes header code
                 */
                public function footer() {
                    if ($this->HasTorrents) {
                        ?>

                        </tbody>
                    </table>


                    <?php $this->buttons(); ?>
                    <div>
                        <input type="hidden" name="action" value="mass_edit" />
                        <input type="hidden" name="type" value="<?= display_str($this->EditType) ?>" />
                        <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
                    </div>
                </form>
            </div>

        <?php
                    }
                }

                /**
                 * Formats data for use in row
                 *
                 */
                public function body() {
                    if ($this->HasTorrents)
                        foreach ($this->TorrentList as $GroupID => $Group) {
                            extract($Group);
                            extract($this->CollageDataList[$GroupID]);

                            $this->NumGroups++;
                            $TorrentLink = Torrents::group_name($Group);
                            $Year = $Year > 0 ? $Year : '';
                            $DateAdded = date($Time);

                            $this->row($Sort, $GroupID, $Year, $DisplayName, $TorrentLink, $DateAdded);
                        }
                }

                /**
                 * Outputs a single row
                 *
                 * @param string|int $Sort
                 * @param string|int $GroupID
                 * @param string|int $GroupYear
                 * @param string $DisplayName
                 * @param string $TorrentLink
                 */
                public function row($Sort, $GroupID, $GroupYear, $DisplayName, $TorrentLink, $DateAdded) {
                    $CSS = $this->NumGroups % 2 === 0 ? 'rowa' : 'rowb';
        ?>

        <tr class="drag <?= $CSS ?>" id="li_<?= $GroupID ?>">
            <td>
                <input class="Input sort_numbers" type="text" name="sort[<?= $GroupID ?>]" value="<?= $Sort ?>" id="sort_<?= $GroupID ?>" size="4" />
            </td>
            <td><?= $this->NumGroups ?></td>
            <td><?= $GroupYear ? trim($GroupYear) : ' ' ?></td>
            <td><?= $TorrentLink ? trim($TorrentLink) : ' ' ?></td>
            <td class="nobr" data-tooltip="<?= $DateAdded ?>"><?= $DateAdded ? time_diff($DateAdded) : ' ' ?></td>
            <td class="center"><input type="checkbox" name="remove[<?= $GroupID ?>]" value="" /></td>
        </tr>
    <?php
                }

                /**
                 * Parses a simple display name
                 *
                 * @param array $Artists
                 * @param array $Artists
                 * @return string $DisplayName
                 */
                public static function display_name(array &$Artists) {
                    $DisplayName = Artists::display_artists($Artists, true, false);
                    return $DisplayName;
                }

                /**
                 * Renders buttons used at the top and bottom of the table
                 */
                public function buttons() {
    ?>
        <div class="drag_drop_save">
            <button class="Button" type="submit" name="update" value="Update ranking" data-tooltip="Save your rank"><?= t('client.common.save') ?></button>
            <button class="Button" type="submit" name="delete" value="Delete checked" data-tooltip="Remove items"><?= t('server.bookmarks.delete_checked') ?></button>
        </div>
<?php
                }


                /**
                 * @param string $EditType
                 */
                public function set_edit_type($EditType) {
                    $this->EditType = $EditType;
                }

                /**
                 * Set's the current page's heading
                 * @param string $Heading
                 */
                public function set_heading($Heading) {
                    $this->Heading = $Heading;
                }
            }
