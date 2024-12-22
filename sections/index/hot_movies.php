<?php
require(CONFIG['SERVER_ROOT'] . '/classes/recommend_groups.class.php');

$ShowData = [];
if ($LoggedUser['ShowHotMovieOnHomePage']) {
    $Top10Movies                = new Top10Movies();
    $ShowData['popular_movies'] = $Top10Movies->getData(
        'active_week',
        [
            'Limit' => 10,
        ]
    );

    $ShowData['user_recommend'] = RecommendGroups::get_recommend_group_for_index();
    $ShowData = array_filter($ShowData);
}
?>

<div class="u-tab">
    <?php $firstKey = array_key_first($ShowData); ?>
    <?php $lastKey = array_key_last($ShowData); ?>
    <?php foreach ($ShowData as $K => $Items) { ?>
        <?php if ($K == $firstKey) { ?>
            <div class="IndexTop10Movie Group u-tabItem u-tabItem<?= ucfirst($K) ?>">
            <?php } else { ?>
                <div class="IndexTop10Movie Group u-tabItem u-tabItem<?= ucfirst($K) ?>" style="display: none">
                <?php } ?>
                <div class="Group-header">
                    <?php foreach ($ShowData as $TabK => $TabItem) { ?>
                        <div class="Group-headerTitle">
                            <?php if ($K !== $TabK) { ?>
                                <a href="#" onclick='globalapp.toggleTab(event, ".u-tabItem<?= ucfirst($TabK) ?>")'>
                                <?php } ?>
                                <?= t('server.index.' . $TabK) ?></a>
                        </div>
                        <?php if ($TabK !== $lastKey) { ?>
                            &nbsp;<span class="Group-headerTitle"> | </span>&nbsp;
                        <?php } ?>
                    <?php } ?>
                </div>

                <div class="Group-body">
                    <?php
                    $tableRender = new TorrentGroupCoverTableView($Items);
                    $tableRender->render(['Variant' => 'OneLine']);
                    ?>
                </div>
                </div>
            <?php } ?>
            </div>