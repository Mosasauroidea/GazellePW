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
        $TooltipHTML = $Tooltip !== false ? " class=\"tooltip\" title=\"$Tooltip\"" : "";
        $ToolsHTML .= "\t\t\t\t<tr class='Table-row'><td class='Table-cell'><a class='Link' href=\"$URL\"$TooltipHTML>$Title</a></td></tr>\n";
    }
}

View::show_header(Lang::get('tools', 'staff_tools'), '', 'PageToolHome');
?>
<div class="Permissions">
    <div class="permission_container">
        <!-- begin left column -->
        <?
        // begin Administration category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'client_whitelist'), "tools.php?action=whitelist", check_perms("admin_whitelist"));
        create_row(Lang::get('tools', 'create_user'), "tools.php?action=create_user", check_perms("admin_create_users"));
        create_row(Lang::get('tools', 'permissions_manager'), "tools.php?action=permissions", check_perms("admin_manage_permissions"));
        create_row(Lang::get('tools', 'staff_page_group_manager'), "tools.php?action=staff_groups", check_perms("admin_manage_permissions"));
        create_row(Lang::get('tools', 'special_users'), "tools.php?action=special_users", check_perms("admin_manage_permissions"));
        create_row(Lang::get('tools', 'application_manager'), "tools.php?action=apply_list", check_perms("admin_interviewer"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'administration') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Announcements category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'calendar'), "tools.php?action=calendar", Calendar::can_view());
        create_row(Lang::get('tools', 'change_log'), "tools.php?action=change_log", check_perms("users_mod"));
        create_row(Lang::get('tools', 'global_notification'), "tools.php?action=global_notification", check_perms("users_mod"));
        create_row(Lang::get('tools', 'mass_pm'), "tools.php?action=mass_pm", check_perms("users_mod"));
        create_row(Lang::get('tools', 'news_post'), "forums.php?action=viewforum&forumid=12", check_perms("admin_manage_news"));
        create_row(Lang::get('tools', 'album_of_the_month'), "tools.php?action=monthalbum", check_perms("users_mod"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'announcements') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Community category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'category_manager'), "tools.php?action=categories", check_perms("admin_manage_forums"));
        create_row(Lang::get('tools', 'forum_manager'), "tools.php?action=forum", check_perms("admin_manage_forums"));
        create_row(Lang::get('tools', 'irc_manager'), "tools.php?action=irc", check_perms("admin_manage_forums"));
        create_row(Lang::get('tools', 'navigation_manager'), "tools.php?action=navigation", check_perms("admin_manage_navigation"));
        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'community') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Finances category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'btc_donations_balance'), "tools.php?action=bitcoin_balance", check_perms("admin_donor_log"));
        create_row(Lang::get('tools', 'btc_donations_unprocessed'), "tools.php?action=bitcoin_unproc", check_perms("admin_donor_log"));
        create_row(Lang::get('tools', 'prepaid_card_donor'), "tools.php?action=prepaid_card", check_perms("users_give_donor"));
        //create_row("Donation log", "tools.php?action=donation_log", check_perms("admin_donor_log"));
        create_row(Lang::get('tools', 'donor_rewards'), "tools.php?action=donor_rewards", check_perms("users_mod"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'finances') ?></td>
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
        // begin Queue category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'auto_enable_requests'), "tools.php?action=enable_requests", check_perms("users_mod"));
        create_row(Lang::get('tools', 'login_watch'), "tools.php?action=login_watch", check_perms("admin_login_watch"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'queue') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Managers category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'stylesheets'), "tools.php?action=stylesheets", check_perms("admin_manage_stylesheets"));
        create_row(Lang::get('tools', 'email_blacklist'), "tools.php?action=email_blacklist", check_perms("users_view_email"));
        create_row(Lang::get('tools', 'ip_address_bans'), "tools.php?action=ip_ban", check_perms("admin_manage_ipbans"));
        create_row(Lang::get('tools', 'duplicate_ip_addresses'), "tools.php?action=dupe_ips", check_perms("users_view_ips"));
        create_row(Lang::get('tools', 'manipulate_invite_tree'), "tools.php?action=manipulate_tree", check_perms("users_mod"));
        if (ENABLE_BADGE) {
            create_row(Lang::get('tools', 'manage_badges'), "tools.php?action=badges", check_perms("admin_manage_badges"));
        }
        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'managers') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        $ToolsHTML = "";
        create_row(Lang::get('tools', 'bonus_points_giving'), "tools.php?action=bonus_points", check_perms("users_mod"));
        create_row(Lang::get('tools', 'fl_tokens_giving'), "tools.php?action=tokens", check_perms("users_mod"));
        create_row(Lang::get('tools', 'invites_giving'), "tools.php?action=invite", check_perms("users_edit_invites"));
        if (ENABLE_BADGE) {
            create_row(Lang::get('tools', 'badges_giving'), "tools.php?action=badges_gave", check_perms("admin_manage_badges"));
        }
        create_row(Lang::get('tools', 'events_reward'), "tools.php?action=events_reward", check_perms("events_reward_tokens") || check_perms("events_reward_bonus") || check_perms("events_reward_invites") || check_perms("events_reward_badges"));
        create_row(Lang::get('tools', 'events_reward_history'), "tools.php?action=events_reward_history", check_perms("events_reward_history"));
        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'rewards') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Developer Sandboxes category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'bbcode_sandbox'), "tools.php?action=bbcode_sandbox", check_perms("users_mod"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'developer_sandboxes') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?  } ?>
        <!-- end middle column -->
    </div>
    <div class="permission_container">
        <!-- begin right column -->
        <?
        // begin Site Information category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'economic_stats'), "tools.php?action=economic_stats", check_perms("site_view_flow"));
        create_row(Lang::get('tools', 'invite_pool'), "tools.php?action=invite_pool", check_perms("users_view_invites"));
        create_row(Lang::get('tools', 'registration_log'), "tools.php?action=registration_log", check_perms("users_view_ips") && check_perms("users_view_email"));
        create_row(Lang::get('tools', 'torrent_stats'), "tools.php?action=torrent_stats", check_perms("site_view_flow"));
        create_row(Lang::get('tools', 'upscale_pool'), "tools.php?action=upscale_pool", check_perms("site_view_flow"));
        create_row(Lang::get('tools', 'user_flow'), "tools.php?action=user_flow", check_perms("site_view_flow"));
        create_row(Lang::get('tools', 'os_and_browser_usage'), "tools.php?action=platform_usage", check_perms('site_view_flow'));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'site_information') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Torrents category
        $ToolsHTML = "";
        if (ENABLE_COLLAGES) {
            create_row(Lang::get('tools', 'collage_recovery'), "collages.php?action=recover", check_perms("site_collages_recover"));
        }
        create_row(Lang::get('tools', 'dnu_list'), "tools.php?action=dnu", check_perms("admin_dnu"));
        create_row(Lang::get('tools', 'multiple_freeleech'), "tools.php?action=multiple_freeleech", check_perms("users_mod"));
        create_row(Lang::get('tools', 'tag_aliases'), "tools.php?action=tag_aliases", check_perms("users_mod"));
        create_row(Lang::get('tools', 'batch_tag_editor'), "tools.php?action=edit_tags", check_perms("users_mod"));
        create_row(Lang::get('tools', 'official_tags_manager'), "tools.php?action=official_tags", check_perms("users_mod"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'torrents') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?
        }

        // begin Development category
        $ToolsHTML = "";
        create_row(Lang::get('tools', 'clear_view_a_cache_key'), "tools.php?action=clear_cache", check_perms("users_mod"));
        create_row(Lang::get('tools', 'php_processes'), "tools.php?action=process_info", check_perms("site_debug"));
        create_row(Lang::get('tools', 'rerender_stylesheet_gallery_images'), "tools.php?action=rerender_gallery", check_perms("site_debug") || check_perms("users_mod"));
        create_row(Lang::get('tools', 'schedule'), "schedule.php?auth=$LoggedUser[AuthKey]", check_perms("site_debug"));
        create_row(Lang::get('tools', 'service_stats'), "tools.php?action=service_stats", check_perms("site_debug"));
        // create_row("Database specifics", "tools.php?action=database_specifics", check_perms("site_database_specifics"));
        create_row(Lang::get('tools', 'site_info'), "tools.php?action=site_info", check_perms('site_debug'));
        create_row(Lang::get('tools', 'site_options'), "tools.php?action=site_options", check_perms('users_mod'));
        create_row(Lang::get('tools', 'tracker_info'), "tools.php?action=ocelot_info", check_perms("users_mod"));
        create_row(Lang::get('tools', 'update_geoip'), "tools.php?action=update_geoip", check_perms("admin_update_geoip"));

        if ($ToolsHTML) {
        ?>
            <div class="permission_subcontainer">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= Lang::get('tools', 'development') ?></td>
                    </tr>
                    <?= $ToolsHTML ?>
                </table>
            </div>
        <?  } ?>
        <!-- end right column -->
    </div>
</div>
<? View::show_footer(); ?>