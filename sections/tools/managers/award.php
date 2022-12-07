<?
include(CONFIG['SERVER_ROOT'] . '/sections/tools/managers/award_functions.php');
View::show_header(t('server.pub.statistics'), '', 'PageToolAward');
$Year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$Quarter = isset($_GET['quarter']) ? intval($_GET['quarter']) : 0;
$Month = isset($_GET['month']) ? intval($_GET['month']) : 0;

if (!$Year && !$Quarter && !$Month) {
    $Month = date('n');
}
if (!$Year) {
    $Year = date('Y');
}
if ($Quarter && $Month) {
    $Month = 0;
}
$QUARTER = array("", t('server.tools.quarter_1'), t('server.tools.quarter_2'), t('server.tools.quarter_3'), t('server.tools.quarter_4'));
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.pub.statistics') ?></h2>
    </div>

    <h2 id="award-title" style="text-align: center;"><?= t('server.tools.year', ['Values' => [$Year]]) . ($Month ? t('server.tools.month', ['Values' => [$Month]]) : "") . ($Quarter ? "$QUARTER[$Quarter]" : "") ?></h2>

    <div class="BoxBody">
        <table id="table-select">
            <?
            printYearTR(2021, ($Month || $Quarter) ? 0 : $Year);
            printQuarterTR($Year, $Quarter);
            printMonthTR($Year, $Month);
            ?>
        </table>
    </div>

    <?
    $Time = timeConvert($Year, $Quarter, $Month);
    $AwardDatas = [
        getAwardData("Sysop", $Time),
        getAwardData("Administrator", $Time),
        getAwardData("Developer", $Time),
        getAwardData("Senior Moderator", $Time),
        getAwardData("Moderator", $Time),
        getAwardData("Torrent Moderator", $Time),
        getAwardData("Forum Moderator", $Time),
        getAwardData("Torrent Inspector", $Time),
        getAwardData("First Line Support", $Time),
        getAwardData("Interviewer", $Time),
        getAwardData("Translators", $Time),
    ];
    $MaxValue = [
        'DownloadCount' => 0,
        'UploadCount' => 0,
        'CheckCount' => 0,
        'RSReportCount' => 0,
        'RPReportCount' => 0,
        'EditCount' => 0,
        'PostCount' => 0,
        'SendJF' => 0,
        'Point' => 0,
        'ApplyCount' => 0,
    ];
    makePoint($AwardDatas, $Bases, $PointRadios, $MaxValue);
    //print_r ($AwardDatas);
    ?>
    <!--
<script>
$(document).ready(function() {
    var top = $('#table-head-top').offset().top - parseFloat($('#table-head-top').css('marginTop').replace(/auto/, 0))
    $(window).scroll(function (event) {
        var y = $(this).scrollTop();
        if (y >= top) {
            $('#table-head-top').addClass('fixed');
        } else {
            $('#table-head-top').removeClass('fixed');
        }
    });
})
</script>
-->
    <div id="salary-table">
        <div id="table-head-left">
            <div class="row1 th-span1 row-span-2"><?= t('server.tools.group') ?></div>
            <div class="row1 th-span1 row-span-2"><?= t('server.tools.id') ?></div>
            <?
            $row = 2;
            $PutoutUsersIDs = [];
            foreach ($AwardDatas as $data) {
                $first = true;
                $headLeftUsers = "";
                $rowspan = 0;
                foreach ($data['Users'] as $User) {
                    if (!in_array($User['UserID'], $PutoutUsersIDs)) {
                        if ($first) {
                            $first = false;
                        }
                        $row++;
                        $DivClassName = "row" . ($row % 2 ? "even" : "odd");
                        $PutoutUsersIDs[] = $User['UserID'];
                        $rowspan++;
                        $headLeftUsers .= "<div class=\"$DivClassName div-username\">" . Users::format_username($User['UserID'], false, false, false) . "</div>";
                    }
                }
                if (!$first) {
                    $GroupNameMap = ['Moderator' => 'Mod', 'Torrent Moderator' => 'TM', 'Forum Moderator' => 'FM', 'Torrent Inspector' => 'TI', 'First Line Support' => 'FLS', 'Interviewer' => 'IN', 'Translators' => 'TL', 'Senior Moderator' => 'SM', 'Developer' => 'Dev', 'Administrator' => 'AD'];
                    if (isset($GroupNameMap[$data['GroupName']])) {
                        echo "<div class=\"group-name\" style=\"grid-row-start: span $rowspan;\" data-tooltip=\"" . $data['GroupName'] . "\">" . $GroupNameMap[$data['GroupName']] . "</div>";
                    } else {
                        echo "<div class=\"group-name\" style=\"grid-row-start: span $rowspan;\">" . $data['GroupName'] . "</div>";
                    }
                    echo $headLeftUsers;
                }
            }
            ?>
        </div>
        <div id="table-right-scroll">
            <div id="table-head-top" style="grid-template-columns: repeat(15, 1fr);">
                <div class="row1 th-span2"><?= t('server.tools.data') ?></div>
                <div class="row1 th-span4"><?= t('server.tools.torrent_management') ?></div>
                <div class="row1 th-span4"><?= t('server.tools.site_activities') ?></div>
                <div class="row1 th-span1"><?= t('server.tools.others') ?></div>
                <div class="row1 col-tail th-span4"><?= t('server.tools.wage_statistics') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.snatches') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.uploads') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.checks') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.reports_submitted') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.reports_handled') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.edit_requests_handled') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.posts') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.rewarded_times') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.qq_group') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.tg_group') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.examined') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.total_points') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.floating_wage') ?></div>
                <div class="row2 th-span1"><?= t('server.tools.base_salary') ?></div>
                <div class="row2 th-span1 col-tail"><?= t('server.tools.total_wages') ?></div>

            </div>
            <div id="table-content" style="grid-template-columns: repeat(15, 1fr);">
                <?
                $row = 2;
                $PutoutUsersIDs = [];
                $Gear = ["", t('server.tools.grade_1'), t('server.tools.grade_2'), t('server.tools.grade_3'), t('server.tools.grade_4'), t('server.tools.grade_5')];
                foreach ($AwardDatas as $data) {
                    foreach ($data['Users'] as $User) {
                        if (!in_array($User['UserID'], $PutoutUsersIDs)) {
                            $row++;
                            $DivClassName = "row$row row" . ($row % 2 ? "even" : "odd");
                            $PutoutUsersIDs[] = $User['UserID'];
                ?>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['DownloadCount'] && $MaxValue['DownloadCount'] == $User['DownloadCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['DownloadCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['UploadCount'] && $MaxValue['UploadCount'] == $User['UploadCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['UploadCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['CheckCount'] && $MaxValue['CheckCount'] == $User['CheckCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['CheckCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['RPReportCount'] && $MaxValue['RPReportCount'] == $User['RPReportCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['RPReportCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['RSReportCount'] && $MaxValue['RSReportCount'] == $User['RSReportCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['RSReportCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['EditCount'] && $MaxValue['EditCount'] == $User['EditCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['EditCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['PostCount'] && $MaxValue['PostCount'] == $User['PostCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['PostCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['SendJF'] && $MaxValue['SendJF'] == $User['SendJF'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['SendJF'] ?></div>
                            <div class="<?= $DivClassName ?>"><?= $User['QQCount'] ?></div>
                            <div class="<?= $DivClassName ?>"><?= $User['TGCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['ApplyCount'] && $MaxValue['ApplyCount'] == $User['ApplyCount'] ? " style=\"color: #d39911;\"" : "" ?>><?= $User['ApplyCount'] ?></div>
                            <div class="<?= $DivClassName ?>" <?= $MaxValue['Point'] && $MaxValue['Point'] == $User['Point'] ? " style=\"color: #d39911;\"" : "" ?>><strong><?= $User['Point'] ?></strong></div>
                            <div class="<?= $DivClassName ?>"><?= $User['Brokerage'] ?></div>
                            <div class="<?= $DivClassName ?>"><?= $User['Base'] ?></div>
                            <div class="<?= $DivClassName ?> col-tail" data-tooltip="<?= $Gear[$User['Gear']] ?>"><strong><?= $User['Salary'] ?></strong></div>
                <?
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <div class="floatright"><?= t('server.tools.explanation_thread') ?></div>
</div>
<script>
    function setCookie(cname, cvalue, exdays = 0) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + (exdays ? (";" + expires) : "") + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    $(document).ready(() => {
        var lastScroll = getCookie("lastScroll")
        if (lastScroll) {
            $(document).scrollTop(lastScroll)
        }
        $(document).scroll(() => {
            setCookie("lastScroll", $(document).scrollTop())
        })
    })
</script>
<? View::show_footer(); ?>