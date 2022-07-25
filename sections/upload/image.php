<?
View::show_header(t('server.upload.image_host'), '', 'PageImageHost');
?>
<div class="LayoutBody" ondrop="globalapp.imgDrop(event)" ondragover="globalapp.imgAllowDrop(event)">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.upload.image_host') ?></h2>
    </div>
    <div class="ImageHost u-vstack" id="image_uploader">
        <div class="ImageHost-dropArea">
            <?= t('server.upload.drop_area') ?>
        </div>
        <button class="Button" onclick="globalapp.imgUpload()"><?= t('server.upload.select_file') ?></button>
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
