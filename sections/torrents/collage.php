<script>
    var collages

    function search(s) {
        var title = $('#collage_title').val()
        $.ajax({
            url: "collages.php",
            data: {
                "action": "ajaxsearch",
                "name": title,
                "type": 'torrent',
                "self": s
            },
            type: "POST",
            success: data => {
                $("#select_collage").empty();
                collages = data
                for (var collageid in collages) {
                    $("#select_collage").append('<option class="Select-option" value="' + collageid + '">' + collages[collageid].name + '</option>')
                };
                selectCollage()
            },
            dataType: "json",
        })
    }

    function addCollage(groupid) {
        var collageid = $('#select_collage').val()
        if (collageid) {
            $.ajax({
                url: "collages.php",
                data: {
                    "action": "add_torrent",
                    "auth": "<?= $LoggedUser['AuthKey'] ?>",
                    "collageid": collageid,
                    "groupid": groupid
                },
                type: "POST",
                success: data => {
                    if (data.state) {
                        alert("<?= t('server.collages.collage_add_success') ?>")
                    } else {
                        alert("<?= t('server.collages.collage_add_error') ?>")
                    }
                },
                dataType: "json",
            })
        }
    }

    function openCollage() {
        var collageid = $('#select_collage').val()
        if (collageid) {
            window.location.replace("collages.php?id=" + collageid);
        }
    }

    function selectCollage() {
        var collageid = $('#select_collage').val()
        if (collageid) {
            $('#s_c_span_category')[0].innerHTML = '<a href="wiki.php?action=article&id=243" target="_blank">' + collages[collageid].category + '</a>'
            $('#s_c_span_author')[0].innerHTML = '<a href="user.php?id=' + collages[collageid].userid + '" target="_blank">' + collages[collageid].username + '</a>'
        } else {
            $('#s_c_span_category')[0].innerHTML = ''
            $('#s_c_span_author')[0].innerHTML = ''
        }
    }
    $(() => {
        search(true)
    });
</script>
<div class="SidebarItemAddCollage SidebarItem Box hidden" id="add_collage_form">
    <div class="SidebarItem-header Box-header">
        <span><?= t('server.collages.add_to_collage') ?></span>
    </div>
    <div class="SidebarItem-body Box-body">
        <div class="Form-row FormOneLine FormCollageSearch">
            <input class="Input" type="text" id="collage_title" name="title" placeholder="<?= t('server.collages.collage_search') ?>" />
            <input class="Button" type="button" value="<?= t('server.collages.search') ?>" onclick="search(false)" />
        </div>
        <div class="Form-row FormCollageRow">
            <select class="Input" id="select_collage" onchange="selectCollage()"></select>
        </div>
        <div class="Form-row FormCollageRow" id="selected_collage_category">
            <span><?= t('server.collages.selected_collage_category') ?>: </span>
            <span id="s_c_span_category"></span>
        </div>
        <div class="Form-row FormCollageRow" id="selected_collage_author">
            <span><?= t('server.collages.selected_collage_author') ?>: </span>
            <span id="s_c_span_author"></span>
        </div>
        <div class="Form-row ButtonGroup center">
            <input class="Button" type="button" value="<?= t('server.collages.open_collage') ?>" onclick="openCollage()" />
            <input class="Button" type="button" value="<?= t('server.collages.add_to_collage') ?>" onclick="addCollage(<?= $GroupID ?>)" />
        </div>
    </div>
</div>