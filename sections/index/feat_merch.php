<?
/*
    $FeaturedMerchURL = '';

    $FeaturedMerch = $Cache->get_value('featured_merch');
    if ($FeaturedMerch === false) {
        $DB->query('
            SELECT ProductID, Title, Image, ArtistID
            FROM featured_merch
            WHERE Ended = 0');
        $FeaturedMerch = $DB->next_record(MYSQLI_ASSOC);
        $Cache->cache_value('featured_merch', $FeaturedMerch, 0);
    }

    if ($FeaturedMerch != null) {
?>
<div id="merchbox" class="box">
    <div class="head colhead_dark">
        <strong><?=t('server.index.fproduct')?></strong>
    </div>
    <div class="center pad">
        <a href="http://anonym.to/?<?=$FeaturedMerchURL . $FeaturedMerch['ProductID']?>"><img src="<?=ImageTools::process($FeaturedMerch['Image'])?>" width="100%" alt="<?=t('server.index.fproduct_title')?>" /></a>
    </div>
    <div class="center pad">
        <a href="http://anonym.to/?<?=$FeaturedMerchURL . $FeaturedMerch['ProductID']?>"><em><?=t('server.index.product_page')?></em></a>
<?      if ($FeaturedMerch['ArtistID'] > 0) {
            $UserInfo = Users::user_info($FeaturedMerch['ArtistID']);
?>      - Artist: <a href="user.php?id=<?=$FeaturedMerch['ArtistID']?>"><?=$UserInfo['Username']?></a>
<?      } ?>
    </div>
</div>
<?  } else { ?>
<div class="box">
    <div class="head colhead_dark">
        <strong><?=t('server.index.mystery')?></strong>
    </div>
    <div class="center pad">
        <?=t('server.index.product_note')?>
    </div>
</div>
<?
    }
*/
