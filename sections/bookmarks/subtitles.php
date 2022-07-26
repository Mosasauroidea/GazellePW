<?
//ini_set('max_file_uploads', 1);
View::show_header(t('server.subtitles.h2_subtitles'), 'validate_subtitles', 'PageBookmarkSubtitle');
?>




<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.subtitles.h2_subtitles') ?></h2>
    </div>
    <div class="BodyNavLinks">
        <a href="subtitles.php?action=new" class="brackets"><?= t('server.subtitles.new_subtitle') ?></a>
        <a href="subtitles.php?action=new" class="brackets"><?= t('server.subtitles.my_subtitles') ?></a>
        <a href="subtitles.php?action=new" class="brackets"><?= t('server.subtitles.bookmarked_subtitles') ?></a>
    </div>
    <div id="subtitle_search_box">
        <input class="Input" type="text" id="subtitle_search_title" placeholder="<?= t('server.subtitles.title_or_imdb_link') ?>">
        <input class="Input" type="text" id="subtitle_search_year" placeholder="<?= t('server.subtitles.year_optional') ?>">
        <select class="Input" id="subtitle_search_language" name="TargetLanguageId" class="form__input">
            <option class="Select-option" value="14">简中</option>
            <option class="Select-option" value="14">繁中</option>
            <option class="Select-option" value="3">English</option>
            <option class="Select-option" value="14">日语 japanese</option>
            <option class="Select-option" value="19">韩语 korean</option>
            <option class="Select-option" value="" selected="selected">---</option>
            <option class="Select-option" value="22">Arabic</option>
            <option class="Select-option" value="49">Brazilian Portuguese</option>
            <option class="Select-option" value="29">Bulgarian</option>
            <option class="Select-option" value="14">Chinese</option>
            <option class="Select-option" value="23">Croatian</option>
            <option class="Select-option" value="30">Czech</option>
            <option class="Select-option" value="10">Danish</option>
            <option class="Select-option" value="9">Dutch</option>
            <option class="Select-option" value="38">Estonian</option>
            <option class="Select-option" value="15">Finnish</option>
            <option class="Select-option" value="6">German</option>
            <option class="Select-option" value="26">Greek</option>
            <option class="Select-option" value="40">Hebrew</option>
            <option class="Select-option" value="41">Hindi</option>
            <option class="Select-option" value="24">Hungarian</option>
            <option class="Select-option" value="28">Icelandic</option>
            <option class="Select-option" value="47">Indonesian</option>
            <option class="Select-option" value="16">Italian</option>
            </option>
            <option class="Select-option" value="37">Latvian</option>
            <option class="Select-option" value="39">Lithuanian</option>
            <option class="Select-option" value="12">Norwegian</option>
            <option class="Select-option" value="52">Persian</option>
            <option class="Select-option" value="17">Polish</option>
            <option class="Select-option" value="21">Portuguese</option>
            <option class="Select-option" value="13">Romanian</option>
            <option class="Select-option" value="7">Russian</option>
            <option class="Select-option" value="31">Serbian</option>
            <option class="Select-option" value="42">Slovak</option>
            <option class="Select-option" value="43">Slovenian</option>
            <option class="Select-option" value="11">Swedish</option>
            <option class="Select-option" value="20">Thai</option>
            <option class="Select-option" value="18">Turkish</option>
            <option class="Select-option" value="34">Ukrainian</option>
            <option class="Select-option" value="25">Vietnamese</option>
        </select>
        <button class="Button"><?= t('server.subtitles.search') ?></button>
    </div>
    <div id="subtitle_browser">
        <div class="thead subtitle_language"><?= t('server.common.language') ?></div>
        <div class="thead movie_title"><?= t('server.subtitles.movie_title') ?></div>
        <div class="thead subtitle_language"><?= t('server.common.language') ?></div>
        <div class="thead movie_title"><?= t('server.subtitles.movie_title') ?></div>
        <!-- 如果是多语字幕，下边就亮联合国旗 -->
        <div class="tbody subtitle_language">国旗</div>
        <div class="tbody movie_title"><a href="subtitles.php?action=detail">[电影中文名] 电影英文名 (年) by 导演名</a><span class="floatright">[ <a>DL</a> ]</span></div>
        <div class="tbody subtitle_language">国旗</div>
        <div class="tbody movie_title"><a href="subtitles.php?action=detail">[电影中文名] 电影英文名 (年) by 导演名</a><span class="floatright">[ <a>DL</a> ]</span></div>
    </div>
</div>



<?
View::show_footer();
