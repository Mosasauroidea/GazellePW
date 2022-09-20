<?
View::show_header(t('server.upload.image_host'), '', 'PageImageHost');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.upload.image_host') ?></h2>
    </div>
    <div class="BodyContent ImageHost" id="image_uploader">
        <div class="ImageHost-dropArea" id="image_uploader_drop_area">


            <div id="image_uploader_preview" class="ImageHost-imageContainer">
            </div>
            <div id="image_host_result" class="ImageHost-imageContainer hidden">
            </div>
            <div class="ImageHost-actions">
                <div>
                    <?= t('server.upload.drop_area') . ' ' . t('server.artist.or')  . '&nbsp;&nbsp;'  ?>
                    <input class="hidden Input" type="file" id="imageupload" name="images[]" size="60" accept="image/gif,image/jpeg,image/jpg,image/png" multiple>
                    <button id="image_upload_choose_file" class="Button"><?= t('server.upload.select_file') ?></button>
                </div>
                |
                <input type="button" variant="primary" value="<?= t('server.upload.upload_img') ?>" id='image_uploader_upload' class="Button" disabled />
                <input type="button" value="<?= t('server.user.reset') ?>" id='image_uploader_cancel' class="Button" />
                <div id="image_host_text" class="ImageHost-text"></div>
            </div>
            <div class="hidden ImageHost-progress">
                <div class="ImageHost-progressBarBorder">
                    <div class="ImageHost-progressBar" style="width: 0%;"></div>
                </div>
            </div>

        </div>

        <div class="hidden ImageHost-body">
            <div class="Box" id="image_host_body_bbcode">
                <div class="Box-header">
                    <div class="Box-headerLeft">
                        <div class="Box-headerTitle">BBCode</div>
                    </div>
                    <div class="Box-headerActions">
                        <a href="#">
                            <?= t('server.upload.copy') ?>
                        </a>
                    </div>
                </div>
                <div class="Box-body">
                    <textarea readonly class="Input ImageHost-linkText"></textarea>
                </div>
            </div>
            <div class="Box" id="image_host_body_link">
                <div class="Box-header">
                    <div class="Box-headerLeft">
                        <div class="Box-headerTitle"><?= t('server.reportsv2.image_s') ?></div>
                    </div>
                    <div class="Box-headerActions">
                        <a href="#">
                            <?= t('server.upload.copy') ?>
                        </a>
                    </div>
                </div>
                <div class="Box-body">
                    <textarea readonly class="Input ImageHost-linkText"></textarea>
                </div>
            </div>
        </div>

    </div>
</div>
<?
View::show_footer([], 'imgupload/index.js');
