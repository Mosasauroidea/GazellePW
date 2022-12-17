<?

class SiteHistoryView {
    public static function render_linkbox() {
        if (check_perms('users_mod')) {
?>
            <div class="BodyNavLinks">
                <a href="sitehistory.php?action=edit" class="brackets"><?= t('server.sitehistory.create_new_event') ?></a>
            </div>
        <?
        }
    }

    public static function render_events($Events) {
        $Categories = SiteHistory::get_categories();
        $SubCategories = SiteHistory::get_sub_categories();
        $CanEdit = check_perms('users_mod');
        ?>
        <div class="PostList PostListSiteHistory">
            <?
            foreach ($Events as $Event) {
            ?>
                <div class="Post Box">
                    <div class="Post-header Box-header">
                        <div class="Post-headerRow">
                            <div class="Post-headerLeft">
                                <span class="Post-headerDate"><?= date('F d, Y', strtotime($Event['Date'])); ?> -</span>
                                <? if (!empty($Event['Url'])) { ?>
                                    <span class="Post-headerTitle"><a href="<?= $Event['Url'] ?>"><?= $Event['Title'] ?></a></span>
                                <? } else { ?>
                                    <span class="Post-headerTitle"><?= $Event['Title'] ?></span>
                                <? } ?>
                            </div>
                            <div class="Post-headerActions">
                                <a href="sitehistory.php?action=search&amp;category=<?= $Event['Category'] ?>" class="brackets"><?= $Categories[$Event['Category']] ?></a>
                                <a href="sitehistory.php?action=search&amp;subcategory=<?= $Event['SubCategory'] ?>" class="brackets"><?= $SubCategories[$Event['SubCategory']] ?></a>
                                <? if ($CanEdit) { ?>
                                    <a class="brackets" href="sitehistory.php?action=edit&amp;id=<?= $Event['ID'] ?>"><?= t('server.common.edit') ?></a>
                                <? } ?>
                            </div>
                        </div>
                        <div class="Post-headerTags Post-headerRow">
                            <? self::render_tags($Event['Tags']) ?>
                        </div>
                    </div>
                    <? if (!empty($Event['Body'])) { ?>
                        <div class="Post-body Box-body HtmlText PostArticle">
                            <?= Text::full_format($Event['Body']) ?>
                        </div>
                    <? } ?>
                </div>
            <? } ?>
        </div>
    <?
    }

    private static function render_tags($Tags) {
        $Tags = explode(',', $Tags);
        natcasesort($Tags);
        $FormattedTags = '';
        foreach ($Tags as $Tag) {
            $FormattedTags .= "<a href=\"sitehistory.php?action=search&amp;tags=$Tag\">$Tag" . "</a>, ";
        }
        echo rtrim($FormattedTags, ', ');
    }

    public static function render_months($Months) { ?>
        <div class="SidebarItem Box">
            <div class="SidebarItem-header Box-header"><?= t('server.sitehistory.calendar') ?></div>
            <div class="SidebarItem-body Box-body">
                <?
                $Year = "";
                foreach ($Months as $Month) {
                    if ($Month['Year'] != $Year) {
                        $Year = $Month['Year'];
                        echo "<h2>$Year</h2>";
                    }
                ?>
                    <a style="margin-left: 5px;" href="sitehistory.php?month=<?= $Month['Month'] ?>&amp;year=<?= $Month['Year'] ?>"><?= $Month['MonthName'] ?></a>
                <?      } ?>
            </div>
        </div>
    <?
    }

    public static function render_search() { ?>
        <div class="SidebarItem Box">
            <div class="SidebarItem-header Box-header"><?= t('server.sitehistory.search') ?></div>
            <div class="SidebarItem-body Box-body">
                <form class="Form FormSiteHistorySearch" action="sitehistory.php" method="get">
                    <div class="Form-rowList" variant="alignLeft">
                        <input type="hidden" name="action" value="search" />
                        <div class="Form-row">
                            <input class="Input" type="text" id="title" name="title" size="20" placeholder="<?= t('server.sitehistory.title') ?>" />
                        </div>
                        <div class="Form-row">
                            <input class="Input" type="text" id="tags" name="tags" size="20" placeholder="<?= t('server.sitehistory.comma_separated_tags') ?>" />
                        </div>
                        <div class="Form-row">
                            <select class="Input" name="category" id="category">
                                <option class="Select-option" value="0"><?= t('server.sitehistory.choose_a_category') ?></option>
                                <?
                                $Categories = SiteHistory::get_categories();
                                foreach ($Categories as $Key => $Value) {
                                ?>
                                    <option<?= $Key == $Event['Category'] ? ' selected="selected"' : '' ?> value="<?= $Key ?>"><?= $Value ?></option>
                                    <? } ?>
                            </select>
                        </div>
                        <div class="Form-row">
                            <select class="Input" name="subcategory">
                                <option class="Select-option" value="0"><?= t('server.sitehistory.choose_a_subcategory') ?></option>
                                <?
                                $SubCategories = SiteHistory::get_sub_categories();
                                foreach ($SubCategories as $Key => $Value) {
                                ?>
                                    <option<?= $Key == $Event['SubCategory'] ? ' selected="selected"' : '' ?> value="<?= $Key ?>"><?= $Value ?></option>
                                    <? } ?>
                            </select>
                        </div>
                        <div class="Form-row">
                            <input class="Button" value="Search" type="submit" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?  }

    public static function render_edit_form($Event) { ?>
        <form id="Form FormSiteHistoryCreate" method="post" action="">
            <? if ($Event) { ?>
                <input type="hidden" name="action" value="take_edit" />
                <input type="hidden" name="id" value="<?= $Event['ID'] ?>" />
            <?      } else { ?>
                <input type="hidden" name="action" value="take_create" />
            <?      } ?>
            <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
            <table class="Form-rowList" variant="header">
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.sitehistory.title') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input required" type="text" id="title" name="title" size="50" value="<?= $Event['Title'] ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.sitehistory.link') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" id="url" name="url" size="50" value="<?= $Event['Url'] ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.sitehistory.date') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input required" type="date" id="date" name="date" <?= $Event ? ' value="' . date('Y-m-d', strtotime($Event['Date'])) . '"' : '' ?> />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.sitehistory.category') ?>:</td>
                    <td class="Form-inputs">
                        <select class="Input" id="category" name="category" class="required">
                            <option class="Select-option" value="0"><?= t('server.sitehistory.choose_a_category') ?></option>
                            <?
                            $Categories = SiteHistory::get_categories();
                            foreach ($Categories as $Key => $Value) {
                            ?>
                                <option<?= $Key == $Event['Category'] ? ' selected="selected"' : '' ?> value="<?= $Key ?>"><?= $Value ?></option>
                                <?      } ?>
                        </select>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.sitehistory.subcategory') ?>:</td>
                    <td class="Form-inputs">
                        <select class="Input" id="category" name="sub_category" class="required">
                            <option class="Select-option" value="0"><?= t('server.sitehistory.choose_a_subcategory') ?></option>
                            <? $SubCategories = SiteHistory::get_sub_categories();
                            foreach ($SubCategories as $Key => $Value) { ?>
                                <option<?= $Key == $Event['SubCategory'] ? ' selected="selected"' : '' ?> value="<?= $Key ?>"><?= $Value ?></option>
                                <?      } ?>
                        </select>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.sitehistory.tags') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" id="tags" name="tags" placeholder="<?= t('server.sitehistory.tags_placeholder') ?>" size="50" value="<?= $Event['Tags'] ?>" />
                        <select class="Input" id="tag_list">
                            <option class="Select-option"><?= t('server.sitehistory.choose_tags') ?></option>
                            <?
                            $Tags = SiteHistory::get_tags();
                            foreach ($Tags as $Tag) {
                            ?>
                                <option class="Select-option"><?= $Tag ?></option>
                            <?      } ?>
                        </select>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.sitehistory.body') ?>:</td>
                    <td class="Form-items">
                        <? new TEXTAREA_PREVIEW('body', 'site_history', $Event['Body'], 60, 8, true, true, false); ?>
                    </td>
                </tr>
            </table>
            <div style="text-align: center;">
                <input class="Button" type="submit" name="submit" value="<?= t('server.common.submit') ?>" />
                <? if ($Event) { ?>
                    <input class="Button" type="submit" name="delete" value="Delete" />
                <?      } ?>
            </div>
        </form>
    <?
    }

    public static function render_recent_sidebar($Events) { ?>
        <div class="SidebarItemSiteHistory SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <span><a href="sitehistory.php"><?= t('server.sitehistory.latest_site_history') ?></a></span>
            </div>
            <ul class="SidebarItem-body Box-body SidebarList is-ordered">
                <?
                $Categories = SiteHistory::get_categories();
                $i = 0;
                foreach ($Events as $Event) {
                ?>
                    <li class="SidebarList-item">
                        <? if (!empty($Event['Url'])) { ?>
                            <a href="<?= $Event['Url'] ?>"><?= $Event['Title'] ?></a>
                        <? } else { ?>
                            <?= $Event['Title'] ?>
                        <? } ?>
                    </li>
                <?      } ?>
            </ul>
        </div>
<?
    }
}
