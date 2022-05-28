<?
View::show_header(Lang::get('donate', 'donate'), '', 'PageDonateStep1');

$SiteName = SITE_NAME;
?>
<div class="LayoutBody" id="donate_information">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('donate', 'donate') ?></h2>
    </div>
    <div class="Box donation_info">
        <div class="Box-header">
            <strong class="donation_info_title"><?= Lang::get('donate', 'why_donate') ?></strong>
        </div>
        <div class="Box-body HtmlText">
            <?= Lang::get('donate', 'donation_info_1') ?>
            <!-- 
            <br><br>
            <?= Lang::get('donate', 'donation_info_2') ?>
            <br><br>
            <?= Lang::get('donate', 'donation_info_3') ?>
            <br><br>
            <?= Lang::get('donate', 'donation_info_4') ?>
            -->
        </div>
    </div>

    <div class="Box donation_info">
        <div class="Box-header"><strong class="donation_info_title"><?= Lang::get('donate', 'what_will_receive') ?></strong></div>
        <div class="Box-body HtmlText">
            <?= Lang::get('donate', 'donation_info_8') ?>
            <?= Lang::get('donate', 'donation_info_11') ?>
        </div>
        <!-- <div id="donor_ranks_container">
            <table id="donor_ranks_table">
                <tr>
                    <td class="th"><?= Lang::get('donate', 'donor_ranks') ?></td>
                    <td>1 (<img src="/static/common/symbols/donor.png"></img>)</td>
                    <td>2 (<img src="/static/common/symbols/donor_2.png"></img>)</td>
                    <td>3 (<img src="/static/common/symbols/donor_3.png"></img>)</td>
                    <td>4 (<img src="/static/common/symbols/donor_4.png"></img>)</td>
                    <td>5 (<img src="/static/common/symbols/donor_5.png"></img>)</td>
                </tr>
                <tr>
                    <td class="th"><?= Lang::get('donate', 'invites') ?></td>
                    <td>0</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>6</td>
                </tr>
                <tr>
                    <td class="th"><?= Lang::get('donate', 'personal_collages') ?></td>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>5</td>
                </tr>
                <tr>
                    <td class="th"><?= Lang::get('donate', 'profile_info') ?></td>
                    <td>0</td>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                </tr>
                <tr>
                    <td class="th"><?= Lang::get('donate', 'unlockable_reward') ?></td>
                    <td><?= Lang::get('donate', 'unlockable_reward_1') ?></td>
                    <td><?= Lang::get('donate', 'unlockable_reward_2') ?></td>
                    <td><?= Lang::get('donate', 'unlockable_reward_3') ?></td>
                    <td><?= Lang::get('donate', 'unlockable_reward_4') ?></td>
                    <td><?= Lang::get('donate', 'unlockable_reward_5') ?></td>
                </tr>
            </table> -->
        <!-- <div class="donate_rank">
                <h4 id="rank_1_header"><?= Lang::get('donate', 'rank_1_header') ?></h4>
                <div id="rank_1_body"><?= Lang::get('donate', 'rank_1_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_2_header"><?= Lang::get('donate', 'rank_2_header') ?></h4>
                <div id="rank_2_body"><?= Lang::get('donate', 'rank_2_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_3_header"><?= Lang::get('donate', 'rank_3_header') ?></h4>
                <div id="rank_3_body"><?= Lang::get('donate', 'rank_3_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_4_header"><?= Lang::get('donate', 'rank_4_header') ?></h4>
                <div id="rank_4_body"><?= Lang::get('donate', 'rank_4_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_5_header"><?= Lang::get('donate', 'rank_5_header') ?></h4>
                <div id="rank_5_body"><?= Lang::get('donate', 'rank_5_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_6_header"><?= Lang::get('donate', 'rank_6_header') ?></h4>
                <div id="rank_6_body"><?= Lang::get('donate', 'rank_6_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_7_header"><?= Lang::get('donate', 'rank_7_header') ?></h4>
                <div id="rank_7_body"><?= Lang::get('donate', 'rank_7_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_8_header"><?= Lang::get('donate', 'rank_8_header') ?></h4>
                <div id="rank_8_body"><?= Lang::get('donate', 'rank_8_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_20_header"><?= Lang::get('donate', 'rank_20_header') ?></h4>
                <div id="rank_20_body"><?= Lang::get('donate', 'rank_20_body') ?></div>
            </div>
            <div class="donate_rank">
                <h4 id="rank_50_header"><?= Lang::get('donate', 'rank_50_header') ?></h4>
                <div id="rank_50_body"><?= Lang::get('donate', 'rank_50_body') ?></div>
            </div> -->
        <!-- </div> -->
        <!-- <br><br>
        <?= Lang::get('donate', 'donation_info_9') ?> -->
        <!-- <br> -->
        <!-- <?= Lang::get('donate', 'donation_info_10') ?>
        <br> -->
        <!-- <br> -->
        <!-- <br> -->
        <!-- <div style="text-align: center;">[<a href="/wiki.php?action=article&id=277"><?= Lang::get('donate', 'view_donor_system_faq') ?></a>]</div><br> -->
        <!-- <br>
        [<a rel="noreferrer" target="_blank" href="static/common/banners/donorinfographic.jpg"><?= Lang::get('donate', 'view_donor_perks_img') ?></a>]<br>
        (Last Updated: July 2013)</div><br>
        <br> -->


    </div>


    <div class="Box donation_info">
        <div class="Box-header">
            <strong class="donation_info_title"><?= Lang::get('donate', 'what_wont_receive') ?></strong>
        </div>
        <div class="Box-body"><?= Lang::get('donate', 'donation_info_12') ?></div>
    </div>

    <div class="Box donation_info">
        <div class="Box-header">
            <strong class="donation_info_title"><?= Lang::get('donate', 'how_to_donate') ?></strong>
        </div>
        <div class="Box-body"><?= Lang::get('donate', 'donation_info_5') ?>
            <br /><br />
            <a href="donate.php?action=step2"><button class="Button"><?= Lang::get('donate', 'want_donate') ?></button></a>
        </div>
    </div>

</div>


<? View::show_footer();
