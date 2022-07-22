<?
View::show_header(Lang::get('upload.image_host'), '', 'PageImageHost');
?>
<div class="LayoutBody" ondrop="globalapp.imgDrop(event)" ondragover="globalapp.imgAllowDrop(event)">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('upload.image_host') ?></h2>
    </div>
    <div class="ImageHost u-vstack" id="image_uploader">
        <div class="ImageHost-dropArea">
            <?= Lang::get('upload.drop_area') ?>
        </div>
        <button class="Button" onclick="globalapp.imgUpload()"><?= Lang::get('upload.select_file') ?></button>
        <div class="ImageHost-Result">
            <input class="Input" type="text" id="image" name="image" size="60" disabled />
            <span onclick="globalapp.imgCopy()"><?= icon('Common/copy') ?></button>
        </div>
        <div id="uploaded_img_container">
            <img id="uploaded_img">
        </div>
    </div>
</div>
<?
View::show_footer();
