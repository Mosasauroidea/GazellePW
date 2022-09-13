<?
View::show_header(t('server.rules.upload_rules'), '', 'PageRules is-upload');
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Upload-mdx" mdx></div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Upload.mdx')
    })
</script>

<? /*
<!-- Uploading Rules -->
<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.rules.upload_rules') ?></h2>
    </div>
    <!-- Uploading Rules Index Links -->
    <form class="Form" name="rules" onsubmit="return false" action="">
        <div>
            <input class="Input" type="text" id="search_string" value="<?= t('server.rules.upload_search') ?>" />
        </div>
        <div id="Index"><?= t('server.rules.upload_search_note') ?></div>
    </form>
    <div class="Box before_rules">
        <div class="RuleText">
            <ul>
                <li id="Introk"><a href="#Intro"><strong><?= t('server.rules.upload_introk') ?></strong></a></li>
                <li id="h1k"><a href="#h1">1. <strong><?= t('server.rules.upload_h1k') ?></strong></a>
                    <ul>
                        <li id="h1.1k"><a href="#h1.1">1.1. <strong><?= t('server.rules.upload_h11k') ?></strong></a></li>
                        <li id="h1.2k"><a href="#h1.2">1.2. <strong><?= t('server.rules.upload_h12k') ?></strong></a></li>
                    </ul>
                </li>
                <li id="h2k"><a href="#h2">2. <strong><?= t('server.rules.upload_h2k') ?></strong></a>
                    <ul>
                        <li id="h2.1k"><a href="#h2.1">2.1. <strong><?= t('server.rules.upload_h21k') ?></strong></a></li>
                        <li id="h2.2k"><a href="#h2.2">2.2. <strong><?= t('server.rules.upload_h22k_t') ?></strong></a>
                        </li>
                        <li id="h2.3k"><a href="#h2.3">2.3. <strong><?= t('server.rules.upload_h23k_t') ?></strong></a></li>
                        <li id="h2.4k"><a href="#h2.4">2.4. <strong><?= t('server.rules.upload_h24k_t') ?></strong></a></li>
                        <!-- <li id="h2.5k"><a href="#h2.5">2.5. <strong><?= t('server.rules.upload_h25k_t') ?></strong></a></li> -->
                    </ul>
                </li>
                <li id="h3k"><a href="#h3">3. <strong><?= t('server.rules.upload_h3k') ?></strong></a>
                    <ul>
                        <li id="h3.1k"><a href="#h3.1">3.1. <strong><?= t('server.rules.upload_h31k') ?></strong></a></li>
                        <li id="h3.2k"><a href="#h3.2">3.2. <strong><?= t('server.rules.upload_h32k') ?></strong></a>
                        </li>
                        <li id="h3.3k"><a href="#h3.3">3.3. <strong><?= t('server.rules.upload_h33k') ?></strong></a></li>
                        <li id="h3.4k"><a href="#h3.4">3.4. <strong><?= t('server.rules.upload_h34k') ?></strong></a></li>
                        <li id="h3.5k"><a href="#h3.5">3.5. <strong><?= t('server.rules.upload_h35k') ?></strong></a></li>
                        <li id="h3.5k"><a href="#h3.5">3.6. <strong><?= t('server.rules.upload_h36k') ?></strong></a></li>
                    </ul>
                </li>
                <li id="h4k"><a href="#h4">4. <strong><?= t('server.rules.upload_h4k') ?></strong></a>
                    <ul>
                        <li id="h4.0k"><a href="#h4.0">4.0. <strong><?= t('server.rules.upload_h40k') ?></strong></a></li>
                        <li id="h4.1k"><a href="#h4.1">4.1. <strong><?= t('server.rules.upload_h41k') ?></strong></a></li>
                        <li id="h4.2k"><a href="#h4.2">4.2. <strong><?= t('server.rules.upload_h42k') ?></strong></a>
                        </li>
                        <li id="h4.3k"><a href="#h4.3">4.3. <strong><?= t('server.rules.upload_h43k') ?></strong></a></li>
                        <li id="h4.4k"><a href="#h4.4">4.4. <strong><?= t('server.rules.upload_h44k') ?></strong></a></li>
                        <li id="h4.5k"><a href="#h4.5">4.5. <strong><?= t('server.rules.upload_h45k') ?></strong></a></li>
                        <li id="h4.6k"><a href="#h4.6">4.6. <strong><?= t('server.rules.upload_h46k') ?></strong></a></li>
                    </ul>
                </li>
                <li id="h5k"><a href="#h5">5. <strong><?= t('server.rules.upload_h5k') ?></strong></a>
                    <ul>
                        <li id="h5.1k"><a href="#h5.1">5.1. <strong><?= t('server.rules.upload_h51k') ?></strong></a></li>
                        <li id="h5.2k"><a href="#h5.2">5.2. <strong><?= t('server.rules.upload_h52k') ?></strong></a>
                        </li>
                        <li id="h5.3k"><a href="#h5.3">5.3. <strong><?= t('server.rules.upload_h53k') ?></strong></a></li>
                        <li id="h5.4k"><a href="#h5.4">5.4. <strong><?= t('server.rules.upload_h54k_t') ?></strong></a></li>
                    </ul>
                </li>
                <li id="h6k"><a href="#h6">6. <strong><?= t('server.rules.upload_h6k') ?></strong></a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Actual Uploading Rules -->
    <div class="Box" id="actual_rules">
        <div class="Box-body">
            <div class="before_rules">
                <h4 id="Intro"><a href="#Introk"><strong>&uarr;</strong></a> <?= t('server.rules.upload_introk') ?></h4>
                <div class="RuleText">
                    <?= t('server.rules.upload_introk_note') ?>
                </div>
            </div>
            <h4 id="h1"><a href="#h1k"><strong>&uarr;</strong></a> <a href="#h1">1.</a> <?= t('server.rules.upload_h1k') ?></h4>

            <h5 id="h1.1"><a href="#h1.1k"><strong>&uarr;</strong></a> <a href="#h1.1">1.1.</a> <?= t('server.rules.upload_h11k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h11k_note') ?>
            </div>
            <h5 id="h1.2"><a href="#h1.2k"><strong>&uarr;</strong></a> <a href="#h1.2">1.2.</a> <?= t('server.rules.upload_h12k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h12k_note') ?>
            </div>

            <h4 id="h2"><a href="#h2k"><strong>&uarr;</strong></a> <a href="#h2">2.</a> <?= t('server.rules.upload_h2k') ?></h4>

            <h5 id="h2.1"><a href="#h2.1k"><strong>&uarr;</strong></a> <a href="#h2.1">2.1.</a> <?= t('server.rules.upload_h21k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h21k_note') ?>
            </div>
            <h5 id="h2.2"><a href="#h2.2k"><strong>&uarr;</strong></a> <a href="#h2.2">2.2.</a> <?= t('server.rules.upload_h22k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h22k_note') ?>
            </div>
            <h5 id="h2.3"><a href="#h2.3k"><strong>&uarr;</strong></a> <a href="#h2.3">2.3.</a> <?= t('server.rules.upload_h23k') ?> </h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h23k_note') ?>
            </div>
            <h5 id="h2.4"><a href="#h2.4k"><strong>&uarr;</strong></a> <a href="#h2.4">2.4. </a> <?= t('server.rules.upload_h24k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h24k_note') ?>
            </div>
            <!-- <h5 id="h2.5"><a href="#h2.5k"><strong>&uarr;</strong></a> <a href="#h2.5">2.5.</a> <?= t('server.rules.upload_h25k') ?></h5>
        <div class="RuleText">
            <?= t('server.rules.upload_h25k_note') ?>
        </div> -->


            <h4 id="h3"><a href="#h3k"><strong>&uarr;</strong></a> <a href="#h3">3.</a> <?= t('server.rules.upload_h3k') ?></h4>

            <h5 id="h3.1"><a href="#h3.1k"><strong>&uarr;</strong></a> <a href="#h3.1">3.1.</a> <?= t('server.rules.upload_h31k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h31k_note') ?>
            </div>
            <h5 id="h3.2"><a href="#h3.2k"><strong>&uarr;</strong></a> <a href="#h3.2">3.2.</a> <?= t('server.rules.upload_h32k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h32k_note') ?>
            </div>
            <h5 id="h3.3"><a href="#h3.3k"><strong>&uarr;</strong></a> <a href="#h3.3">3.3.</a> <?= t('server.rules.upload_h33k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h33k_note') ?>
            </div>
            <h5 id="h3.4"><a href="#h3.4k"><strong>&uarr;</strong></a> <a href="#h3.4">3.4.</a> <?= t('server.rules.upload_h34k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h34k_note') ?>
            </div>
            <h5 id="h3.5"><a href="#h3.5k"><strong>&uarr;</strong></a> <a href="#h3.5">3.5.</a> <?= t('server.rules.upload_h35k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h35k_note') ?>
            </div>
            <h5 id="h3.6"><a href="#h3.6k"><strong>&uarr;</strong></a> <a href="#h3.6">3.6.</a> <?= t('server.rules.upload_h36k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h36k_note') ?>
            </div>

            <h4 id="h4"><a href="#h4k"><strong>&uarr;</strong></a> <a href="#h4">4.</a> <?= t('server.rules.upload_h4k') ?></h4>

            <h5 id="h4.0"><a href="#h4.0k"><strong>&uarr;</strong></a> <a href="#h4.0">4.0.</a> <?= t('server.rules.upload_h40k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h40k_note') ?>
            </div>
            <h5 id="h4.1"><a href="#h4.1k"><strong>&uarr;</strong></a> <a href="#h4.1">4.1.</a> <?= t('server.rules.upload_h41k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h41k_note') ?>
            </div>
            <h5 id="h4.2"><a href="#h4.2k"><strong>&uarr;</strong></a> <a href="#h4.2">4.2.</a> <?= t('server.rules.upload_h42k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h42k_note') ?>
            </div>
            <h5 id="h4.3"><a href="#h4.3k"><strong>&uarr;</strong></a> <a href="#h4.3">4.3.</a> <?= t('server.rules.upload_h43k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h43k_note') ?>
            </div>
            <h5 id="h4.4"><a href="#h4.4k"><strong>&uarr;</strong></a> <a href="#h4.4">4.4.</a> <?= t('server.rules.upload_h44k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h44k_note') ?>
            </div>
            <h5 id="h4.5"><a href="#h4.5k"><strong>&uarr;</strong></a> <a href="#h4.5">4.5.</a> <?= t('server.rules.upload_h45k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h45k_note') ?>
            </div>
            <h5 id="h4.6"><a href="#h4.6k"><strong>&uarr;</strong></a> <a href="#h4.6">4.6.</a> <?= t('server.rules.upload_h46k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h46k_note') ?>
            </div>

            <h4 id="h5"><a href="#h5k"><strong>&uarr;</strong></a> <a href="#h5">5.</a> <?= t('server.rules.upload_h5k') ?></h4>

            <h5 id="h5.1"><a href="#h5.1k"><strong>&uarr;</strong></a> <a href="#h5.1">5.1.</a> <?= t('server.rules.upload_h51k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h51k_note') ?>
            </div>
            <h5 id="h5.2"><a href="#h5.2k"><strong>&uarr;</strong></a> <a href="#h5.2">5.2.</a> <?= t('server.rules.upload_h52k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h52k_note') ?>
            </div>
            <h5 id="h5.3"><a href="#h5.3k"><strong>&uarr;</strong></a> <a href="#h5.3">5.3.</a> <?= t('server.rules.upload_h53k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h53k_note') ?>
            </div>
            <h5 id="h5.4"><a href="#h5.4k"><strong>&uarr;</strong></a> <a href="#h5.4">5.4.</a> <?= t('server.rules.upload_h54k') ?></h5>
            <div class="RuleText">
                <?= t('server.rules.upload_h54k_note') ?>
            </div>

            <h4 id="h6"><a href="#h6k"><strong>&uarr;</strong></a> <a href="#h6">6.</a> <?= t('server.rules.upload_h6k') ?></h4>
            <div class="RuleText">
                <h5 id="h6.1"><a href="#h6.1k"><strong>&uarr;</strong></a> <a href="#h6.1">6.1.</a> <?= t('server.rules.upload_h61k') ?></h5>

                <h5 id="h6.2"><a href="#h6.2k"><strong>&uarr;</strong></a> <a href="#h6.2">6.2.</a> <?= t('server.rules.upload_h62k') ?></h5>

                <h5 id="h6.3"><a href="#h6.3k"><strong>&uarr;</strong></a> <a href="#h6.3">6.3.</a> <?= t('server.rules.upload_h63k') ?></h5>

                <h5 id="h6.4"><a href="#h6.4k"><strong>&uarr;</strong></a> <a href="#h6.4">6.4.</a> <?= t('server.rules.upload_h64k') ?></h5>
            </div>
        </div>
    </div>

</div>
*/ ?>

<?
View::show_footer();
?>