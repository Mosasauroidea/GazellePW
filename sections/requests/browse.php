<?php
$GenreTags = Tags::get_genre_tag();
$SphQL = new SphinxqlQuery();
$SphQL->select('id, votes, bounty')->from('requests, requests_delta');

$SortOrders = array(
    'votes' => 'votes',
    'bounty' => 'bounty',
    'lastvote' => 'lastvote',
    'filled' => 'timefilled',
    'year' => 'year',
    'created' => 'timeadded',
    'random' => false
);

$RequestType = $_GET['request_type'];
if (!empty($RequestType)) {
    $SphQL->where("requesttype", $RequestType);
}

if (empty($_GET['order']) || !isset($SortOrders[$_GET['order']])) {
    $_GET['order'] = 'created';
}
$OrderBy = $_GET['order'];

if (!empty($_GET['sort']) && $_GET['sort'] === 'asc') {
    $OrderWay = 'asc';
} else {
    $_GET['sort'] = 'desc';
    $OrderWay = 'desc';
}
$NewSort = $_GET['sort'] === 'asc' ? 'desc' : 'asc';

if ($OrderBy === 'random') {
    $SphQL->order_by('RAND()', '');
    unset($_GET['page']);
} else {
    $SphQL->order_by($SortOrders[$OrderBy], $OrderWay);
}

$Submitted = !empty($_GET['submit']);

//Paranoia
if (!empty($_GET['userid'])) {
    if (!is_number($_GET['userid'])) {
        error('User ID must be an integer');
    }
    $UserInfo = Users::user_info($_GET['userid']);
    if (empty($UserInfo)) {
        error('That user does not exist');
    }
    $Perms = Permissions::get_permissions($UserInfo['PermissionID']);
    $UserClass = $Perms['Class'];
}
$BookmarkView = false;

if (empty($_GET['type'])) {
    $Title = t('server.requests.requests');
} else {
    switch ($_GET['type']) {
        case 'created':
            if (!empty($UserInfo)) {
                if (!check_paranoia('requestsvoted_list', $UserInfo['Paranoia'], $Perms['Class'], $UserInfo['ID'])) {
                    error(403);
                }
                $Title = t('server.requests.requests_created_by', ['Values' => [$UserInfo['Username']]]);
                $SphQL->where('userid', $UserInfo['ID']);
            } else {
                $Title = t('server.requests.my_requests');
                $SphQL->where('userid', $LoggedUser['ID']);
            }
            break;
        case 'voted':
            if (!empty($UserInfo)) {
                if (!check_paranoia('requestsvoted_list', $UserInfo['Paranoia'], $Perms['Class'], $UserInfo['ID'])) {
                    error(403);
                }
                $Title = t('server.requests.requests_voted_for_by', ['Values' => [$UserInfo['Username']]]);
                $SphQL->where('voter', $UserInfo['ID']);
            } else {
                $Title = t('server.requests.requests_i_have_voted_on');
                $SphQL->where('voter', $LoggedUser['ID']);
            }
            break;
        case 'filled':
            if (!empty($UserInfo)) {
                if (!check_paranoia('requestsfilled_list', $UserInfo['Paranoia'], $Perms['Class'], $UserInfo['ID'])) {
                    error(403);
                }
                $Title = t('server.requests.requests_filled_by', ['Values' => [$UserInfo['Username']]]);
                $SphQL->where('fillerid', $UserInfo['ID']);
            } else {
                $Title = t('server.requests.requests_i_have_filled');
                $SphQL->where('fillerid', $LoggedUser['ID']);
            }
            break;
        case 'bookmarks':
            $Title = t('server.requests.bookmarks');
            $BookmarkView = true;
            $SphQL->where('bookmarker', $LoggedUser['ID']);
            break;
        default:
            error(404);
    }
}

if (empty($_GET['show_filled'])) {
    $SphQL->where('torrentid', 0);
}

$EnableNegation = false; // Sphinx needs at least one positive search condition to support the NOT operator

if (!empty($_GET['source'])) {
    $SourceArray = $_GET['source'];
    if (count($SourceArray) !== count($Sources)) {
        $SourceNameArray = array();
        foreach ($SourceArray as $Index => $MasterIndex) {
            if (isset($Sources[$MasterIndex])) {
                $SourceNameArray[$Index] = '"' . strtr(Sphinxql::sph_escape_string($Sources[$MasterIndex]), '-.', '  ') . '"';
            }
        }
        if (count($SourceNameArray) >= 1) {
            $EnableNegation = true;
            if (!empty($_GET['source_strict'])) {
                $SearchString = '(' . implode(' | ', $SourceNameArray) . ')';
            } else {
                $SearchString = '(any | ' . implode(' | ', $SourceNameArray) . ')';
            }
            $SphQL->where_match($SearchString, 'sourcelist', false);
        }
    }
}

if (!empty($_GET['codec'])) {
    $CodecArray = $_GET['codec'];
    if (count($CodecArray) !== count($Codecs)) {
        $CodecNameArray = array();
        foreach ($CodecArray as $Index => $MasterIndex) {
            if (isset($Codecs[$MasterIndex])) {
                $CodecNameArray[$Index] = '"' . strtr(Sphinxql::sph_escape_string($Codecs[$MasterIndex]), '-.', '  ') . '"';
            }
        }

        if (count($CodecNameArray) >= 1) {
            $EnableNegation = true;
            if (!empty($_GET['codec_strict'])) {
                $SearchString = '(' . implode(' | ', $CodecNameArray) . ')';
            } else {
                $SearchString = '(any | ' . implode(' | ', $CodecNameArray) . ')';
            }
            $SphQL->where_match($SearchString, 'codeclist', false);
        }
    }
}

if (!empty($_GET['container'])) {
    $ContainerArray = $_GET['container'];
    if (count($ContainerArray) !== count($Containers)) {
        $ContainerNameArray = array();
        foreach ($ContainerArray as $Index => $MasterIndex) {
            if (isset($Containers[$MasterIndex])) {
                $ContainerNameArray[$Index] = '"' . strtr(Sphinxql::sph_escape_string($Containers[$MasterIndex]), '-.', '  ') . '"';
            }
        }

        if (count($ContainerNameArray) >= 1) {
            $EnableNegation = true;
            if (!empty($_GET['container_strict'])) {
                $SearchString = '(' . implode(' | ', $ContainerNameArray) . ')';
            } else {
                $SearchString = '(any | ' . implode(' | ', $ContainerNameArray) . ')';
            }
            $SphQL->where_match($SearchString, 'containerlist', false);
        }
    }
}

if (!empty($_GET['resolution'])) {
    $ResolutionArray = $_GET['resolution'];
    if (count($ResolutionArray) !== count($Resolutions)) {
        $ResolutionNameArray = array();
        foreach ($ResolutionArray as $Index => $MasterIndex) {
            if (isset($Resolutions[$MasterIndex])) {
                $ResolutionNameArray[$Index] = '"' . strtr(Sphinxql::sph_escape_string($Resolutions[$MasterIndex]), '-.', '  ') . '"';
            }
        }

        if (count($ResolutionNameArray) >= 1) {
            $EnableNegation = true;
            if (!empty($_GET['container_strict'])) {
                $SearchString = '(' . implode(' | ', $ResolutionNameArray) . ')';
            } else {
                $SearchString = '(any | ' . implode(' | ', $ResolutionNameArray) . ')';
            }
            $SphQL->where_match($SearchString, 'resolutionlist', false);
        }
    }
}

if (!empty($_GET['search'])) {
    $SearchString = trim($_GET['search']);

    if ($SearchString !== '') {
        $SearchWords = array('include' => array(), 'exclude' => array());
        $Words = explode(' ', $SearchString);
        foreach ($Words as $Word) {
            $Word = trim($Word);
            // Skip isolated hyphens to enable "Artist - Title" searches
            if ($Word === '-') {
                continue;
            }
            if ($Word[0] === '!' && strlen($Word) >= 2) {
                if (strpos($Word, '!', 1) === false) {
                    $SearchWords['exclude'][] = $Word;
                } else {
                    $SearchWords['include'][] = $Word;
                    $EnableNegation = true;
                }
            } elseif ($Word !== '') {
                $SearchWords['include'][] = $Word;
                $EnableNegation = true;
            }
        }
    }
}

if (!isset($_GET['tags_type']) || $_GET['tags_type'] === '1') {
    $TagType = 1;
    $_GET['tags_type'] = '1';
} else {
    $TagType = 0;
    $_GET['tags_type'] = '0';
}

if (!empty($_GET['tags'])) {
    $SearchTags = array('include' => array(), 'exclude' => array());
    $Tags = explode(',', str_replace('.', '_', $_GET['tags']));
    foreach ($Tags as $Tag) {
        $Tag = trim($Tag);
        if ($Tag[0] === '!' && strlen($Tag) >= 2) {
            if (strpos($Tag, '!', 1) === false) {
                $SearchTags['exclude'][] = $Tag;
            } else {
                $SearchTags['include'][] = $Tag;
                $EnableNegation = true;
            }
        } elseif ($Tag !== '') {
            $SearchTags['include'][] = $Tag;
            $EnableNegation = true;
        }
    }

    $TagFilter = Tags::tag_filter_sph($SearchTags, $EnableNegation, $TagType);

    if (!empty($TagFilter['predicate'])) {
        $SphQL->where_match($TagFilter['predicate'], 'taglist', false);
    }
} elseif (!isset($_GET['tags_type']) || $_GET['tags_type'] !== '0') {
    $_GET['tags_type'] = 1;
} else {
    $_GET['tags_type'] = 0;
}
$TagNames = $_GET['tags'];

if (isset($SearchWords)) {
    $QueryParts = array();
    if (!$EnableNegation && !empty($SearchWords['exclude'])) {
        $SearchWords['include'] = array_merge($SearchWords['include'], $SearchWords['exclude']);
        unset($SearchWords['exclude']);
    }
    foreach ($SearchWords['include'] as $Word) {
        $QueryParts[] = Sphinxql::sph_escape_string($Word);
    }
    if (!empty($SearchWords['exclude'])) {
        foreach ($SearchWords['exclude'] as $Word) {
            $QueryParts[] = '!' . Sphinxql::sph_escape_string(substr($Word, 1));
        }
    }
    if (!empty($QueryParts)) {
        $SearchString = implode(' ', $QueryParts);
        $SphQL->where_match($SearchString, '*', false);
    }
}

if (!empty($_GET['filter_cat'])) {
    $CategoryArray = array_keys($_GET['filter_cat']);
    if (count($CategoryArray) !== count($Categories)) {
        foreach ($CategoryArray as $Key => $Index) {
            if (!isset($Categories[$Index - 1])) {
                unset($CategoryArray[$Key]);
            }
        }
        if (count($CategoryArray) >= 1) {
            $SphQL->where('categoryid', $CategoryArray);
        }
    }
}

if (!empty($_GET['releases'])) {
    $ReleaseArray = $_GET['releases'];
    if (count($ReleaseArray) !== count($ReleaseTypes)) {
        foreach ($ReleaseArray as $Index => $Value) {
            if (!isset($ReleaseTypes[$Value])) {
                unset($ReleaseArray[$Index]);
            }
        }
        if (count($ReleaseArray) >= 1) {
            $SphQL->where('releasetype', $ReleaseArray);
        }
    }
}

if (!empty($_GET['requestor'])) {
    if (is_number($_GET['requestor'])) {
        $SphQL->where('userid', $_GET['requestor']);
    } else {
        error(404);
    }
}

if (isset($_GET['year'])) {
    if (is_number($_GET['year']) || $_GET['year'] === '0') {
        $SphQL->where('year', $_GET['year']);
    } else {
        error(404);
    }
}

if (!empty($_GET['page']) && is_number($_GET['page']) && $_GET['page'] > 0) {
    $Page = $_GET['page'];
    $Offset = ($Page - 1) * CONFIG['REQUESTS_PER_PAGE'];
    $SphQL->limit($Offset, CONFIG['REQUESTS_PER_PAGE'], $Offset + CONFIG['REQUESTS_PER_PAGE']);
} else {
    $Page = 1;
    $SphQL->limit(0, CONFIG['REQUESTS_PER_PAGE'], CONFIG['REQUESTS_PER_PAGE']);
}

$SphQLResult = $SphQL->query();
$NumResults = (int)$SphQLResult->get_meta('total_found');
if ($NumResults > 0) {
    $SphRequests = $SphQLResult->to_array('id');
    if ($OrderBy === 'random') {
        $NumResults = count($SphRequests);
    }
    if ($NumResults > CONFIG['REQUESTS_PER_PAGE']) {
        if (($Page - 1) * CONFIG['REQUESTS_PER_PAGE'] > $NumResults) {
            $Page = 0;
        }
        $PageLinks = Format::get_pages($Page, $NumResults, CONFIG['REQUESTS_PER_PAGE']);
    }
}

$CurrentURL = Format::get_url(array('order', 'sort', 'page'));
View::show_header($Title, '', 'PageRequestHome');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= $Title ?>
        </div>
        <div class="BodyNavLinks">
            <? if (!$BookmarkView) {
                if (check_perms('site_submit_requests')) { ?>
                    <a class="Link" href="requests.php?action=new"><?= t('server.requests.new_request') ?></a>
                    <a class="Link" href="requests.php?type=created&show_filled=on"><?= t('server.requests.my_requests') ?></a>
                <? } ?>
                <? if (check_perms('site_vote')) { ?>
                    <a class="Link" href="requests.php?type=voted"><?= t('server.requests.vote_requests') ?></a>
                <? } ?>
                <a class="Link" href="bookmarks.php?type=requests"><?= t('server.requests.bookmarked_requests') ?></a>
            <?  } else { ?>
                <a class="Link" href="bookmarks.php?type=torrents"><?= t('server.index.moviegroups') ?></a>
                <a class="Link" href="bookmarks.php?type=artists"><?= t('server.common.artists') ?></a>
                <? if (CONFIG['ENABLE_COLLAGES']) { ?>
                    <a class="Link" href="bookmarks.php?type=collages"><?= t('server.requests.collages') ?></a>
                <? } ?>
                <a class="Link" href="bookmarks.php?type=requests"><?= t('server.common.requests') ?></a>
            <?  } ?>
        </div>
    </div>
    <?
    if ($BookmarkView && $NumResults === 0) {
    ?>
        <div class="center">
            <div><?= t('server.requests.you_have_not_bookmarked_any_request') ?></div>
        </div>
    <?
    } else { ?>
        <form class="Form SearchPage Box SearchReqeust" name="requests" action="" method="get">
            <div class="SearchPageBody">
                <? if ($BookmarkView) { ?>
                    <input type="hidden" name="action" value="view" />
                    <input type="hidden" name="type" value="requests" />
                <?      } elseif (isset($_GET['type'])) { ?>
                    <input type="hidden" name="type" value="<?= $_GET['type'] ?>" />
                <?      } ?>
                <input type="hidden" name="submit" value="true" />
                <? if (!empty($_GET['userid']) && is_number($_GET['userid'])) { ?>
                    <input type="hidden" name="userid" value="<?= $_GET['userid'] ?>" />
                <?      } ?>
                <table class="Form-rowList">
                    <tr class="Form-row is-searchStr">
                        <td class="Form-label"><?= t('server.requests.search_terms') ?>:</td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" name="search" size="75" value="<? if (isset($_GET['search'])) {
                                                                                                echo display_str($_GET['search']);
                                                                                            } ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-searchStr">
                        <td class="Form-label"><?= t('server.requests.request_type') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="request_type[]" value="1" id="new_torrent" <?= empty($RequestType) || in_array(1, $RequestType) ? ' checked="checked" ' : '' ?> />
                                <label class="Checkbox-label" for="new_torrent"><?= t('server.requests.new_torrent') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="request_type[]" value="2" id="seed_torrent" <?= empty($RequestType) || in_array(2, $RequestType) ? ' checked="checked" ' : '' ?> />
                                <label class="Checkbox-label" for="new_torrent"><?= t('server.requests.seed_torrent') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row is-includeFilled">
                        <td class="Form-label">
                            <label for="include_filled_box"><?= t('server.requests.include_filled') ?>:</label>
                        </td>
                        <td class="Form-inputs">
                            <input class="Input" type="checkbox" id="include_filled_box" name="show_filled" <? if (!empty($_GET['show_filled']) || (!empty($_GET['type']) && $_GET['type'] === 'filled')) { ?> checked="checked" <? } ?> />
                        </td>
                    </tr>
                </table>
                <table class="Form-rowList">
                    <tr class="Form-row is-release">
                        <td class="Form-label"><?= t('server.requests.release_list') ?></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="toggle_releases" onchange="globalapp.allToggle('releases', 0);" <?= (!$Submitted || !empty($ReleaseArray) && count($ReleaseArray) === count($ReleaseTypes) ? ' checked="checked"' : '') ?> />
                                <label for="toggle_releases"><?= t('server.forums.check_all') ?></label>
                            </div>
                            <?
                            foreach ($ReleaseTypes as $Key) {
                            ?>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="releases[]" value="<?= $Key ?>" id="release_<?= $Key ?>" <?= (!$Submitted || (!empty($ReleaseArray) && in_array($Key, $ReleaseArray)) ? ' checked="checked" ' : '') ?> />
                                    <label class="Checkbox-label" for="release_<?= $Key ?>"><?= t('server.torrents.release_types')[$Key] ?></label>
                                </div>
                            <?
                            }
                            ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-source">
                        <td class="Form-label"><?= t('server.requests.source_list') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="toggle_source" onchange="globalapp.allToggle('source', 0);" <?= (!$Submitted || !empty($SourceArray) && count($SourceArray) === count($Sources) ? ' checked="checked"' : '') ?> />
                                <label class="Checkbox-label" for="toggle_source"><?= t('server.forums.check_all') ?></label>
                            </div>
                            <?
                            foreach ($Sources as $Key => $Val) {
                            ?>
                                <div class="Checkbox">
                                    <input class=Input" type="checkbox" name="source[]" value="<?= $Key ?>" id="source_<?= $Key ?>" <?= (!$Submitted || (!empty($SourceArray) && in_array($Key, $SourceArray)) ? ' checked="checked" ' : '') ?> />
                                    <label class="Checkbox-label" for="source_<?= $Key ?>"><?= $Val ?></label>
                                </div>
                            <?      } ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-codec">
                        <td class="Form-label"><?= t('server.requests.codec_list') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="toggle_codec" onchange="globalapp.allToggle('codec', 0);" <?= (!$Submitted || !empty($CodecArray) && count($CodecArray) === count($Codecs) ? ' checked="checked"' : '') ?> />
                                <label class="Checkbox-label" for="toggle_codec"><?= t('server.forums.check_all') ?></label>
                            </div>
                            <?
                            foreach ($Codecs as $Key => $Val) {
                            ?>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="codec[]" value="<?= $Key ?>" id="codec_<?= $Key ?>" <?= (!$Submitted || (!empty($CodecArray) && in_array($Key, $CodecArray)) ? ' checked="checked" ' : '') ?> />
                                    <label class="Checkbox-label" for="codec_<?= $Key ?>"><?= $Val ?></label>
                                </div>
                            <?      } ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-container">
                        <td class="Form-label"><?= t('server.requests.container_list') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="toggle_container" onchange="globalapp.allToggle('container', 0);" <?= (!$Submitted || !empty($ContainerArray) && count($ContainerArray) === count($Containers) ? ' checked="checked"' : '') ?> />
                                <label class="Checkbox-label" for="toggle_container"><?= t('server.forums.check_all') ?></label>
                            </div>
                            <?
                            foreach ($Containers as $Key => $Val) {
                            ?>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="container[]" value="<?= $Key ?>" id="container_<?= $Key ?>" <?= (!$Submitted || (!empty($ContainerArray) && in_array($Key, $ContainerArray)) ? ' checked="checked" ' : '') ?> />
                                    <label class="Checkbox-label" for="container_<?= $Key ?>"><?= $Val ?></label>
                                </div>
                            <?
                            }
                            ?>
                        </td>
                    </tr>
                    <tr class="Form-row is-resolution">
                        <td class="Form-label"><?= t('server.requests.resolution_list') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" id="toggle_resolution" onchange="globalapp.allToggle('resolution', 0);" <?= (!$Submitted || !empty($ResolutionArray) && count($ResolutionArray) === count($Resolutions) ? ' checked="checked"' : '') ?> />
                                <label class="Checkbox-label" for="toggle_resolution"><?= t('server.forums.check_all') ?></label>
                            </div>
                            <?
                            foreach ($Resolutions as $Key => $Val) {
                            ?>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="resolution[]" value="<?= $Key ?>" id="resolution_<?= $Key ?>" <?= (!$Submitted || (!empty($ResolutionArray) && in_array($Key, $ResolutionArray)) ? ' checked="checked" ' : '') ?> />
                                    <label class="Checkbox-label" for="resolution_<?= $Key ?>"><?= $Val ?></label>
                                </div>
                            <?
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="SearchPageFooter">
                <div class="SearchPageFooter-actions">
                    <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
                </div>
            </div>
        </form>
        <?
        if (isset($PageLinks)) {
        ?>
            <div class="BodyNavLinks">
                <?= $PageLinks ?>
            </div>
        <?
        } ?>
        <div class="TableContainer">
            <table class="TableRequest Table" id="request_table" cellpadding="6" cellspacing="1" border="0" width="100%">
                <tr class="Table-rowHeader">
                    <td class="Table-cell" style="width: 38%;">
                        <?= t('server.requests.name') ?> / <a href="?order=year&amp;sort=<?= ($OrderBy === 'year' ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.requests.year') ?></a>
                    </td>
                    <td class="Table-cell">
                        <?= t('server.requests.request_type') ?>
                    </td>
                    <td class="Table-cell">
                        <a href="?order=votes&amp;sort=<?= ($OrderBy === 'votes' ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.requests.quick_vote') ?></a>
                    </td>
                    <td class="Table-cell">
                        <a href="?order=bounty&amp;sort=<?= ($OrderBy === 'bounty' ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.requests.bounty') ?></a>
                    </td>
                    <td class="Table-cell">
                        <a href="?order=filled&amp;sort=<?= ($OrderBy === 'filled' ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.requests.filled') ?></a>
                    </td>
                    <td class="Table-cell">
                        <?= t('server.requests.filled_by') ?>
                    </td>
                    <td class="Table-cell">
                        <?= t('server.requests.add_by') ?>
                    </td>
                    <td class="Table-cell">
                        <a href="?order=created&amp;sort=<?= ($OrderBy === 'created' ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.requests.created') ?></a>
                    </td>
                    <td class="Table-cell">
                        <a href="?order=lastvote&amp;sort=<?= ($OrderBy === 'lastvote' ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.requests.lastvote') ?></a>
                    </td>
                </tr>
                <?
                if ($NumResults === 0) {
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell" colspan="8">
                            Nothing found!
                        </td>
                    </tr>
                <?      } elseif ($Page === 0) { ?>
                    <tr class="Table-row">
                        <td class="Table-cell" colspan="8">
                            The requested page contains no matches!
                        </td>
                    </tr>
                    <?
                } else {
                    $TimeCompare = 1267643718; // Requests v2 was implemented 2010-03-03 20:15:18
                    $Requests = Requests::get_requests(array_keys($SphRequests));
                    foreach ($Requests as $RequestID => $Request) {
                        $SphRequest = $SphRequests[$RequestID];
                        $Bounty = $SphRequest['bounty'] * 1024 * 1024; // Sphinx stores bounty in MB
                        $VoteCount = $SphRequest['votes'];

                        if ($Request['CategoryID'] == 0) {
                            $CategoryName = 'Unknown';
                        } else {
                            $CategoryName = $Categories[$Request['CategoryID'] - 1];
                        }
                        $RequestType = $Request['RequestType'];

                        if ($Request['TorrentID'] != 0) {
                            $IsFilled = true;
                            $FillerInfo = Users::user_info($Request['FillerID']);
                        } else {
                            $IsFilled = false;
                        }

                        $ArtistForm = Requests::get_artists($RequestID);
                        $RequestName = Torrents::group_name($Request, false);
                        $FullName = "<a href=\"requests.php?action=view&amp;id=$RequestID\">$RequestName</a>";
                        $Tags = $Request['Tags'];
                    ?>
                        <tr class="TableRequest-row Table-row">
                            <td class="TableRequest-cellName Table-cell">
                                <?= $FullName ?>
                                <div class="torrent_info">
                                    <?
                                    if ($RequestType == 2) {
                                    ?>
                                        <a href="<?= $Request['SourceTorrent'] ?>"><?= str_replace('|', ', ', $Request['CodecList']) . ' / ' . str_replace('|', ', ', $Request['SourceList']) . ' / ' . str_replace('|', ', ', $Request['ResolutionList']) . ' / ' . str_replace('|', ', ', $Request['ContainerList']) ?></a>
                                    <?
                                    } else {
                                    ?>
                                        <?= str_replace('|', ', ', $Request['CodecList']) . ' / ' . str_replace('|', ', ', $Request['SourceList']) . ' / ' . str_replace('|', ', ', $Request['ResolutionList']) . ' / ' . str_replace('|', ', ', $Request['ContainerList']) ?>
                                    <?
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="TableRequest-cellType Table-cell">
                                <?= $RequestType  == 2 ? t('server.requests.seed_torrent') : t('server.requests.new_torrent') ?>
                            </td>
                            <td class="TableRequest-cellVotes Table-cell">
                                <span id="vote_count_<?= $RequestID ?>"><?= number_format($VoteCount) ?></span>
                                <? if (!$IsFilled && check_perms('site_vote')) { ?>
                                    <a href="javascript:globalapp.requestVote(0, <?= $RequestID ?>)" class="brackets">+</a>
                                <?      } ?>
                            </td>
                            <td class="TableRequest-cellSize Table-cell">
                                <?= Format::get_size($Bounty) ?>
                            </td>
                            <td class="TableRquest-cellFilled Table-cell">
                                <? if ($IsFilled) { ?>
                                    <a href="torrents.php?<?= (strtotime($Request['TimeFilled']) < $TimeCompare ? 'id=' : 'torrentid=') . $Request['TorrentID'] ?>"><?= time_diff($Request['TimeFilled'], 1) ?></a>
                                <?      } else { ?>
                                    No
                                <?      } ?>
                            </td>
                            <td class="TableRequest-cellFilledBy Table-cell">
                                <?
                                if ($IsFilled) {
                                ?>
                                    <a href="user.php?id=<?= $FillerInfo['ID'] ?>"><?= $FillerInfo['Username'] ?></a>
                                <?
                                } else { ?>
                                    --
                                <?
                                } ?>
                            </td>
                            <td class="TableRequest-cellRequestedBy Table-cell">
                                <?= Users::format_username($Request['UserID'], false, false, false) ?>
                            </td>
                            <td class="TableRequest-cellCreatedAt TableRequest-cellTime Table-cell">
                                <?= time_diff($Request['TimeAdded'], 1) ?>
                            </td>
                            <td class="TableRequest-cellModifiedAt Table-cell">
                                <?= time_diff($Request['LastVote'], 1) ?>
                            </td>
                        </tr>
                <?
                    } // foreach
                } // else
                ?>
            </table>
            <?
            ?>
        </div>
    <?
    } // if ($BookmarkView && $NumResults < 1)
    if (isset($PageLinks)) { ?>
        <div class="BodyNavLinks">
            <?= $PageLinks ?>
        </div>
    <?
    }
    ?>
</div>
<? View::show_footer(); ?>