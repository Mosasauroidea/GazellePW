<?
class RevisionHistoryView {
    /**
     * Render the revision history
     * @param array $RevisionHistory see RevisionHistory::get_revision_history
     * @param string $BaseURL
     */
    public static function render_revision_history($RevisionHistory, $BaseURL) {
?>
        <div class="TableContainer">
            <table class="TableRevision Table" id="revision_table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= Lang::get('global.revision') ?></td>
                    <td class="Table-cell"><?= Lang::get('global.date') ?></td>
                    <td class="Table-cell"><?= Lang::get('global.user') ?></td>
                    <td class="Table-cell"><?= Lang::get('global.summary') ?></td>
                </tr>
                <?
                $Row = 'a';
                foreach ($RevisionHistory as $Entry) {
                    list($RevisionID, $Summary, $Time, $UserID) = $Entry;
                    $Row = (($Row == 'a') ? 'b' : 'a');
                ?>
                    <tr class="row<?= $Row ?>">
                        <td>
                            <?= "<a href=\"$BaseURL&amp;revisionid=$RevisionID\">#$RevisionID</a>" ?>
                        </td>
                        <td>
                            <?= $Time ?>
                        </td>
                        <td>
                            <?= Users::format_username($UserID, false, false, false) ?>
                        </td>
                        <td>
                            <?= ($Summary ? $Summary : '(empty)') ?>
                        </td>
                    </tr>
                <?      } ?>
            </table>
        </div>
<?
    }
}
