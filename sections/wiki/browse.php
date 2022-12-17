<?
Text::$TOC = true;

$ArticleID = false;
if (!empty($_GET['id']) && is_number($_GET['id'])) { //Visiting article via ID
    $ArticleID = (int)$_GET['id'];
} elseif ($_GET['name'] != '') { //Retrieve article ID via alias.
    $ArticleID = Wiki::alias_to_id($_GET['name']);
}
if ($ArticleID) {
    $Article = Wiki::get_article($ArticleID, false);
}

if (!$ArticleID || !$Article) { //No article found
    View::show_header(t('server.wiki.no_article_found'), '', 'PageWikiHome');
?>
    <div class="LayoutBody">
        <div class="BodyHeader">
            <h2 class="BodyHeader-nav"><?= t('server.wiki.no_article_found') ?></h2>
        </div>
        <div class="BoxBody">
            <?= t('server.wiki.no_article_matching_the_name') ?>
            <ul>
                <li><a href="wiki.php?action=search&amp;search=<?= display_str($_GET['name']) ?>"><?= t('server.wiki.search') ?></a><?= t('server.wiki.for_an_similar_article') ?></li>
                <li><a href="wiki.php?action=create&amp;alias=<?= display_str(Wiki::normalize_alias($_GET['name'])) ?>"><?= t('server.wiki.create') ?></a><?= t('server.wiki.replace_article') ?></li>
            </ul>
        </div>
    </div>
<?
    View::show_footer();
    die();
}

list($Revision, $Title, $Body, $Read, $Edit, $Date, $AuthorID, $AuthorName, $Aliases, $UserIDs,, $FatherID) = array_shift($Article);
if ($Read > $LoggedUser['EffectiveClass']) {
    error(t('server.wiki.you_must_be_a_higher_user_class_to_view'));
}

$TextBody = Text::full_format($Body, false);
$TOC = Text::parse_toc(0);

View::show_header($Title, 'wiki,bbcode', 'PageWikiHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
        <div class="BodyNavLinks">
            <a class="Link" href="wiki.php?action=create"><?= t('server.wiki.article_create') ?></a>
            <? if ($Edit <= $LoggedUser['EffectiveClass']) { ?>
                <a class="Link" href="wiki.php?action=edit&amp;id=<?= $ArticleID ?>"><?= t('server.wiki.article_edit') ?></a>
                <a class="Link" href="wiki.php?action=revisions&amp;id=<?= $ArticleID ?>"><?= t('server.wiki.article_history') ?></a>
                <? if (check_perms('admin_manage_wiki') && $_GET['id'] != CONFIG['INDEX_ARTICLE']) { ?>
                    <a class="Link" href="wiki.php?action=delete&amp;id=<?= $ArticleID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" onclick="return confirm('<?= t('server.wiki.article_delete_confirm1') ?>\n<?= t('server.wiki.article_delete_confirm2') ?>\n<?= t('server.wiki.article_delete_confirm3') ?>')"><?= t('server.wiki.article_delete') ?></a>
                <? } ?>
            <? } ?>
        </div>
    </div>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <div class="SidebarItemWikiSearch SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <?= t('server.wiki.search') ?></div>
                <div class="SidebarItem-body Box-body">
                    <form class="FormOneLine SearchSidebarArticle" name="articles" action="wiki.php" method="get">
                        <input type="hidden" name="action" value="search" />
                        <input class="Input" type="text" placeholder="<?= t('server.wiki.search_articles') ?>" name="search" size="20" />
                        <input value="Search" type="submit" class="hidden" />
                    </form>
                </div>
            </div>
            <?
            if ($FatherID) {
                $DB->query("select ID,Title,Lang from wiki_articles where id=$FatherID or father=$FatherID and id != $ArticleID");
            } else {
                $DB->query("select ID,Title,Lang from wiki_articles where Father=$ArticleID");
            }
            $Atcs = $DB->to_array();
            if (isset($Atcs) && count($Atcs) > 0) {
            ?>
                <div class="SidebarItemWikiLanguages SidebarItem Box" id="other_language_box">
                    <div class="SidebarItem-header Box-header">
                        <?= t('server.wiki.other_languages') ?></div>
                    <div class="SidebarItem-body Box-body">
                        <?

                        echo "<ul>";
                        foreach ($Atcs as $Atc) {
                            echo "<li><a href=\"wiki.php?action=article&id=$Atc[0]\">" . ($Atc[2] == 'chs' ? '简体中文' : 'English') . "</a></li>";
                        }
                        echo "</ul>";
                        ?>
                    </div>
                </div>
            <?
            }
            ?>
            <div class="SidebarItemWikiToc SidebarItem Box">
                <div class="SidebarItem-header Box-header"><?= t('server.wiki.toc') ?></div>
                <div class="SidebarItem-body Box-body SidebarList">
                    <?= $TOC ?>
                </div>
            </div>
            <div class="SidebarItemWikiInfo SidebarItem Box">
                <ul>
                    <li>
                        <strong><?= t('server.wiki.permissions') ?></strong>
                        <ul>
                            <li><?= t('server.wiki.read') ?>: <?= $ClassLevels[$Read]['Name'] ?></li>
                            <li><?= t('server.common.edit') ?>: <?= $ClassLevels[$Edit]['Name'] ?></li>
                        </ul>
                    </li>
                    <li>
                        <strong><?= t('server.wiki.details') ?></strong>
                        <ul>
                            <li><?= t('server.wiki.version') ?>: <?= $Revision ?></li>
                            <li><?= t('server.wiki.last_edited') ?>: <?= Users::format_username($AuthorID, false, false, false) ?></li>
                            <li><?= t('server.wiki.last_upload') ?>: <?= time_diff($Date) ?></li>
                        </ul>
                    </li>
                    <li>
                        <strong><?= t('server.wiki.aliases') ?></strong>
                        <ul>
                            <?
                            if ($Aliases != $Title) {
                                $AliasArray = explode(',', $Aliases);
                                $UserArray = explode(',', $UserIDs);
                                $i = 0;
                                foreach ($AliasArray as $AliasItem) {
                            ?>
                                    <li id="alias_<?= $AliasItem ?>"><a href="wiki.php?action=article&amp;name=<?= $AliasItem ?>"><?= Format::cut_string($AliasItem, 80, 1) ?></a><? if (check_perms('admin_manage_wiki')) { ?> <a href="#" onclick="Remove_Alias('<?= $AliasItem ?>'); return false;" class="brackets" data-tooltip="<?= t('server.wiki.delete_aliases') ?>">X</a> <a href="user.php?id=<?= $UserArray[$i] ?>" class="brackets" data-tooltip="<?= t('server.wiki.view_user') ?>">U</a><? } ?></li>
                            <? $i++;
                                }
                            }
                            ?>
                        </ul>
                    </li>
                </ul>
            </div>
            <? if ($Edit <= $LoggedUser['EffectiveClass']) { ?>
                <div class="SidebarItemWikiAddAlias SidebarItem Box">
                    <div class="SidebarItem-header Box-header"><?= t('server.wiki.add_alias') ?></div>
                    <div class="SidebarItem-body Box-body">
                        <form class="FormOneLine add_form" name="aliases" action="wiki.php" method="post">
                            <input type="hidden" name="action" value="add_alias" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="article" value="<?= $ArticleID ?>" />
                            <input class="Input" type="text" onfocus="if (this.value == 'Add alias') this.value='';" onblur="if (this.value == '') this.value='Add alias';" name="alias" size="20" />
                            <input class="Button" type="submit" value="+" />
                    </div>
                    </form>
                </div>
            <? } ?>
        </div>
        <div class="LayoutMainSidebar-main">
            <div class="Post wiki_article">
                <div class="Post-body  HtmlText PostArticle">
                    <?= $TextBody ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?
View::show_footer();
