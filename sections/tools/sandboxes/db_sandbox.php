<?php

if (!check_perms('admin_site_debug')) {
    error(403);
}

if (!empty($_POST['query'])) {
    $_POST['query'] = trim($_POST['query']);
    if (preg_match('/^select[\s]+([^--]*)[\s]+from/i', $_POST['query']) !== 1) {
        error('Invalid query');
    }
}

function print_row($Row, $Class) {
    echo "<tr class='{$Class}'>" . implode("\n", array_map(function ($Value) {
        return "<td>" . (($Value === null) ? "NULL" : $Value) . "</td>";
    }, $Row)) . "</tr>";
}

$Title = 'DB Sandbox';
View::show_header($Title);

?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= $Title ?></h2>
</div>
<div class="thin pad box">
    <form action="tools.php?action=db_sandbox" method='POST'>
        <textarea class="Input" type="textare" name="query" cols="90" rows="8"><?= $_POST['query'] ?></textarea><br /><br />
        <input class="Button" type="submit" value="Query" />
    </form>
</div>
<?php

if (!empty($_POST['query'])) {
    try {
        $success = true;
        $DB->prepared_query($_POST['query']);
    } catch (DB_MYSQL_Exception $e) {
        $success = false;
?>
        <div class="thin BoxBody">
            <h3 style="display:inline">Query error</h3>
            <div>Mysql error: <?= display_str($e->getMessage()) ?></div>
        </div>
    <?php
    }
    if ($success) {
    ?>
        <div class="LayoutBody" style="overflow-x: scroll">
            <div>
                <h3 style="display:inline">Query Results</h3>
            </div>
            <table>
                <?php

                $Record = $DB->fetch_record();
                $Headers = [];
                $Row = [];
                foreach ($Record as $Key => $Value) {
                    if (!is_int($Key)) {
                        $Headers[] = $Key;
                        $Row[] = $Value;
                    }
                }

                print_row($Headers, 'colhead');
                print_row($Row, 'rowb');
                $Cnt = 0;
                while ($Record = $DB->fetch_record()) {
                    $Row = [];
                    foreach ($Record as $Key => $Value) {
                        if (!is_int($Key)) {
                            $Row[] = $Value;
                        }
                    }
                    print_row($Row, ($Cnt++ % 2) ? 'rowa' : 'rowb');
                }
                ?>
            </table>
        </div>

<?php
    }
}
View::show_footer();
