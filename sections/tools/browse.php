<?

/***********************************************
 * This file displays the list of available tools in the staff toolbox.
 *
 * Because there are various subcontainers and various permissions, it
 * is possible to have empty subcontainers. The $ToolsHTML variable is
 * used to display only non-empty subcontainers.
 *
 ***********************************************
 */

if (!check_perms('users_mod')) {
    error(403);
}

/**
 * Used for rendering a single table row in the staff toolbox. The
 * $ToolsHTML variable is incrementally expanded with each function call
 * in a given subcontainer and gets reset at the beginning of each new
 * subcontainer.
 *
 * @param string $Title - the displayed name of the tool
 * @param string $URL - the relative URL of the tool
 * @param bool $HasPermission - whether the user has permission to view/use the tool
 * @param string $Tooltip - optional tooltip
 *
 */
function create_row($Title, $URL, $HasPermission = false, $Tooltip = false) {
    if ($HasPermission) {
        global $ToolsHTML;
        $TooltipHTML = $Tooltip !== false ? " data-tooltip=\"$Tooltip\"" : "";
        $ToolsHTML .= "\t\t\t\t<tr class='Table-row'><td class='Table-cell'>";
        if (!empty($URL)) {
            $ToolsHTML .= "<a class='Link' href=\"$URL\"$TooltipHTML>$Title</a></td></tr>\n";
        } else {
            $ToolsHTML .= "$Title</td></tr>\n";
        }
    }
}

View::show_header(t('server.tools.staff_tools'), '', 'PageToolHome');
?>
<div class="Permissions">
    <div class="permission_container">
        <!-- begin left column -->
        <?
        // begin Administration category
        $ToolsHTML = "";
        create_row(t('server.tools.create_user'), "tools.php?action=create_user", check_perms("admin_create_users"));
        create_row(t('server.tools.permissions_manager'), "tools.php?action=permissions", check_perms("admin_manage_permissions"));
        create_row(t('server.tools.staff_page_group_manager'), "tools.php?action=staff_groups", check_perms("admin_manage_permissions"));
        create_row(t('server.tools.special_users'), "tools.php?action=special_users", check_perms("admin_manage_permissions"));
        create_row(t('server.tools.auto_enable_requests'), "tools.php?action=enable_requests", check_perms("users_mod"));
        create_row(t('server.tools.login_watch'), "tools.php?action=login_watch", check_perms("admin_login_watch"));
        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.administration') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Announcements category
        $ToolsHTML = "";
        create_row('<s>' . t('server.tools.calendar') . '</s>', /*"tools.php?action=calendar"*/ "", Calendar::can_view());
        create_row('<s>' . t('server.tools.change_log') . '</s>', /*"tools.php?action=change_log*/ "", check_perms("users_mod"));
        create_row(t('server.tools.global_notification'), "tools.php?action=global_notification", check_perms("users_mod"));
        create_row(t('server.tools.mass_pm'), "tools.php?action=mass_pm", check_perms("users_mod"));
        create_row(t('server.tools.news_post'), "forums.php?action=viewforum&forumid=" . $CONFIG['NEWS_FORUM_ID'], check_perms("admin_manage_news"));
        create_row(t('server.tools.featured_movie'), "tools.php?action=featuremovie", check_perms("users_mod"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.announcements') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Community category
        $ToolsHTML = "";
        create_row(t('server.tools.forum_management'), "tools.php?action=categories", check_perms("admin_manage_forums"));
        create_row('<s>' . t('server.tools.irc_manager') . '</s>', /*"tools.php?action=irc"*/ "", check_perms("admin_manage_forums"));
        create_row('<s>' . t('server.tools.navigation_manager') . '</s>', /*"tools.php?action=navigation"*/ "", check_perms("admin_manage_forums"));
        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.community') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Finances category
        $ToolsHTML = "";
        create_row('<s>' . t('server.tools.bitcoin_donations') . '</s>', /*"tools.php?action=bitcoin_balance"*/ "", check_perms("admin_donor_log"));
        create_row(t('server.tools.prepaid_card_donor'), "tools.php?action=prepaid_card", check_perms("admin_donor_log"));
        create_row(t('server.tools.donation_log'), "tools.php?action=donation_log", check_perms("admin_donor_log"));
        create_row(t('server.tools.donor_rewards'), "tools.php?action=donor_rewards", check_perms("admin_donor_log"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.finances') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?  } ?>
        <!-- end left column -->
    </div>
    <div class="permission_container">
        <!-- begin middle column -->
        <?
        // begin Managers category
        $ToolsHTML = "";
        create_row(t('server.tools.client_whitelist'), "tools.php?action=whitelist", check_perms("admin_whitelist"));
        create_row(t('server.tools.stylesheets'), "tools.php?action=stylesheets", check_perms("admin_manage_stylesheets"));
        create_row(t('server.tools.email_blacklist'), "tools.php?action=email_blacklist", check_perms("users_view_email"));
        create_row(t('server.tools.ip_address_bans'), "tools.php?action=ip_ban", check_perms("admin_manage_ipbans"));
        create_row(t('server.tools.duplicate_ip_addresses'), "tools.php?action=dupe_ips", check_perms("users_view_ips"));
        create_row(t('server.tools.manipulate_invite_tree'), "tools.php?action=manipulate_tree", check_perms("users_mod"));
        if (CONFIG['ENABLE_BADGE']) {
            create_row('<s>' . t('server.tools.manage_badges') . '</s>',/* "tools.php?action=badges"*/ "", check_perms("admin_manage_badges"));
            create_row('<s>' . t('server.tools.badges_giving') . '</s>',/* "tools.php?action=badges_gave"*/ "", check_perms("admin_manage_badges"));
        }
        create_row(t('server.tools.events_reward'), "tools.php?action=events_reward", check_perms("events_reward_tokens") || check_perms("events_reward_bonus") || check_perms("events_reward_invites") || check_perms("events_reward_badges"));
        create_row(t('server.tools.invite_pool'), "tools.php?action=invite_pool", check_perms("users_view_invites"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.managers') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }
        // begin Torrents category
        $ToolsHTML = "";
        if (CONFIG['ENABLE_COLLAGES']) {
            create_row(t('server.tools.collage_recovery'), "collages.php?action=recover", check_perms("site_collages_recover"));
        }
        create_row(t('server.tools.dnu_list'), "tools.php?action=dnu", check_perms("admin_dnu"));
        create_row('<s>' . t('server.tools.multiple_freeleech') . '</s>', /*"tools.php?action=multiple_freeleech"*/ "", check_perms("users_mod"));
        create_row(t('server.tools.tags_manager'), "tools.php?action=manage_tags", check_perms("users_mod"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.torrents') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }
        ?>

    </div>
    <div class="permission_container">
        <!-- begin right column -->
        <?
        // begin Site Information category
        $ToolsHTML = "";
        create_row(t('server.tools.economic_stats'), "tools.php?action=economic_stats", check_perms("site_view_flow"));
        create_row(t('server.tools.torrent_stats'), "tools.php?action=torrent_stats", check_perms("site_view_flow"));
        create_row(t('server.tools.os_and_browser_usage'), "tools.php?action=platform_usage", check_perms('site_view_flow'));
        create_row(t('server.tools.user_flow'), "tools.php?action=user_flow", check_perms("site_view_flow"));
        create_row(t('server.tools.registration_log'), "tools.php?action=registration_log", check_perms("users_view_ips") && check_perms("users_view_email"));
        create_row(t('server.tools.upscale_pool'), "tools.php?action=upscale_pool", check_perms("site_view_flow"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.site_information') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Development category
        $ToolsHTML = "";
        create_row(t('server.tools.clear_view_a_cache_key'), "tools.php?action=clear_cache", check_perms("users_mod"));
        create_row(t('server.tools.php_processes'), "tools.php?action=process_info", check_perms("site_debug"));
        create_row('<s>' . t('server.tools.rerender_stylesheet_gallery_images') . '</s>', /*"tools.php?action=rerender_gallery"*/ "", check_perms("site_debug") || check_perms("users_mod"));
        create_row(t('server.tools.schedule'), "schedule.php?auth=$LoggedUser[AuthKey]", check_perms("site_debug"));
        create_row(t('server.tools.service_stats'), "tools.php?action=service_stats", check_perms("site_debug"));
        // create_row("Database specifics", "tools.php?action=database_specifics", check_perms("site_database_specifics"));
        create_row(t('server.tools.site_info'), "tools.php?action=site_info", check_perms('site_debug'));
        create_row(t('server.tools.site_options'), "tools.php?action=site_options", check_perms('users_mod'));
        create_row(t('server.tools.tracker_info'), "tools.php?action=ocelot_info", check_perms("users_mod"));
        create_row(t('server.tools.update_geoip'), "tools.php?action=update_geoip", check_perms("admin_update_geoip"));
        create_row(t('server.tools.bbcode_sandbox'), "tools.php?action=bbcode_sandbox", check_perms("users_mod"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.development') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?  } ?>
        <!-- end right column -->
    </div>
</div>
<? View::show_footer(); ?>