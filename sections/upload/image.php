<?
View::show_header(Lang::get('upload', 'image_host'), '', 'PageUploadImage');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('upload', 'image_host') ?></h2>
    </div>
    <div class="u-vstack" id="image_uploader">
        <input class="Input" type="text" placeholder="<?= Lang::get('upload', 'image_placeholder') ?>" id="image" name="image" size="60" ondrop="globalapp.imgDrop(event)" ondragover="globalapp.imgAllowDrop(event)" />
        <div>
            <input class="Button" type="button" onclick="globalapp.imgUpload()" value="上传" accept="image/gif,image/jpeg,image/jpg,image/png,image/svg" />
            <input class="Button" type="button" onclick="globalapp.imgCopy()" value="复制"> <span id="imgUploadPer"></span>
        </div>
    </div>
    <div id="uploaded_img_container">
        <img id="uploaded_img">
    </div>
</div>
<?
View::show_footer();
