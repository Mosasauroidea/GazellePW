<?

/********************************************************************************
 ************ Permissions form ********************** user.php and tools.php ****
 ********************************************************************************
 ** This function is used to create both the class permissions form, and the   **
 ** user custom permissions form.                                             **
 ********************************************************************************/

$PermissionsArray = [
    'site_leech',
    'site_upload',
    'site_vote',
    'site_submit_requests',
    'site_advanced_search',
    'site_top10',
    'site_torrents_notify',
    'site_collages_create',
    'site_collages_manage',
    'site_collages_delete',
    'site_collages_subscribe',
    'site_collages_personal',
    'site_collages_renamepersonal',
    'site_advanced_top10',
    'site_album_votes',
    'site_make_bookmarks',
    'site_edit_wiki',
    'site_can_invite_always',
    'site_can_invite',
    'site_send_unlimited_invites',
    'site_moderate_requests',
    'site_delete_artist',
    'forums_polls_create',
    'forums_polls_moderate',
    'site_moderate_forums',
    'site_admin_forums',
    'site_view_flow',
    'site_view_full_log',
    'site_view_torrent_snatchlist',
    'site_recommend_own',
    'site_manage_recommendations',
    'site_delete_tag',
    'site_disable_ip_history',
    'zip_downloader',
    'site_debug',
    'site_proxy_images',
    'site_search_many',
    'site_collages_recover',
    'project_team',
    'site_tag_aliases_read',
    'forums_see_hidden',
    'show_admin_team',
    'show_staff_username',
    'users_edit_usernames',
    'users_edit_ratio',
    'users_edit_own_ratio',
    'users_edit_titles',
    'users_edit_avatars',
    'users_edit_invites',
    'users_edit_watch_hours',
    'users_edit_reset_keys',
    'users_edit_profiles',
    'users_view_friends',
    'users_reset_own_keys',
    'users_edit_password',
    'users_promote_below',
    'users_promote_to',
    'users_give_donor',
    'users_warn',
    'users_disable_users',
    'users_disable_posts',
    'users_disable_any',
    'users_delete_users',
    'users_view_invites',
    'users_view_seedleech',
    'users_view_uploaded',
    'users_view_keys',
    'users_view_ips',
    'users_view_email',
    'users_invite_notes',
    'users_override_paranoia',
    'users_make_invisible',
    'users_logout',
    'users_mod',
    'staff_award',
    'users_view_disabled',
    'torrents_edit',
    'torrents_check',
    'self_torrents_check',
    'torrents_check_log',
    'torrents_delete',
    'torrents_delete_fast',
    'torrents_freeleech',
    'torrents_search_fast',
    'torrents_add_artist',
    'edit_unknowns',
    'torrents_hide_dnu',
    'torrents_fix_ghosts',
    'torrents_trumpable',
    'torrents_slot_edit',
    'admin_manage_news',
    'admin_manage_blog',
    'admin_manage_polls',
    'admin_manage_forums',
    'admin_manage_fls',
    'admin_manage_user_fls',
    'admin_manage_badges',
    'admin_manage_applicants',
    'admin_send_bonus',
    'admin_reports',
    'admin_bp_history',
    'admin_advanced_user_search',
    'admin_create_users',
    'admin_donor_log',
    'admin_manage_stylesheets',
    'admin_manage_ipbans',
    'admin_dnu',
    'admin_clear_cache',
    'admin_whitelist',
    'admin_manage_permissions',
    'admin_schedule',
    'admin_login_watch',
    'admin_manage_wiki',
    'admin_update_geoip',
    'admin_interviewer',
    'events_reward_tokens',
    'events_reward_bonus',
    'events_reward_invites',
    'events_reward_badges',
    'events_reward_history',
];

function permissions_form() {
?>

    <tr class="Form-row">
        <td class="Form-label"><?= t('server.permissions.site') ?></td>
        <td class="Form-items">
            <div class="PermissionCheckbox-list ">
                <?
                display_perm('site_leech');
                display_perm('site_upload');
                display_perm('site_vote');
                display_perm('site_submit_requests');
                display_perm('site_advanced_search');
                display_perm('site_top10');
                display_perm('site_torrents_notify');
                display_perm('site_collages_create');
                display_perm('site_collages_manage');
                display_perm('site_collages_delete');
                display_perm('site_collages_subscribe');
                display_perm('site_collages_personal');
                display_perm('site_collages_renamepersonal');
                display_perm('site_advanced_top10');
                display_perm('site_album_votes');
                display_perm('site_make_bookmarks');
                display_perm('site_edit_wiki');
                display_perm('site_can_invite_always');
                display_perm('site_can_invite');
                display_perm('site_send_unlimited_invites');
                display_perm('site_moderate_requests');
                display_perm('site_delete_artist');
                display_perm('forums_polls_create');
                display_perm('forums_polls_moderate');
                display_perm('site_moderate_forums');
                display_perm('site_admin_forums');
                display_perm('site_view_flow');
                display_perm('site_view_full_log');
                display_perm('site_view_torrent_snatchlist');
                display_perm('site_recommend_own');
                display_perm('site_manage_recommendations');
                display_perm('site_delete_tag');
                display_perm('site_disable_ip_history');
                display_perm('zip_downloader');
                display_perm('site_debug');
                display_perm('site_proxy_images');
                display_perm('site_search_many');
                display_perm('site_collages_recover');
                display_perm('project_team');
                display_perm('site_tag_aliases_read');
                display_perm('forums_see_hidden');
                display_perm('show_admin_team');
                display_perm('show_staff_username');
                ?>
            </div>
        </td>
    </tr>

    <tr class="Form-row">
        <td class="Form-label"><?= t('server.permissions.users') ?></td>
        <td class="Form-items">
            <div class="PermissionCheckbox-list ">
                <?
                display_perm('users_edit_usernames');
                display_perm('users_edit_ratio');
                display_perm('users_edit_own_ratio');
                display_perm('users_edit_titles');
                display_perm('users_edit_avatars');
                display_perm('users_edit_invites');
                display_perm('users_edit_watch_hours');
                display_perm('users_edit_reset_keys');
                display_perm('users_edit_profiles');
                display_perm('users_view_friends');
                display_perm('users_reset_own_keys');
                display_perm('users_edit_password');
                display_perm('users_promote_below');
                display_perm('users_promote_to');
                display_perm('users_give_donor');
                display_perm('users_warn');
                display_perm('users_disable_users');
                display_perm('users_disable_posts');
                display_perm('users_disable_any');
                display_perm('users_delete_users');
                display_perm('users_view_invites');
                display_perm('users_view_seedleech');
                display_perm('users_view_uploaded');
                display_perm('users_view_keys');
                display_perm('users_view_ips');
                display_perm('users_view_email');
                display_perm('users_invite_notes');
                display_perm('users_override_paranoia');
                display_perm('users_make_invisible');
                display_perm('users_logout');
                display_perm('users_mod');
                display_perm('staff_award');
                display_perm('users_view_disabled');
                ?>
            </div>
        </td>
    </tr>
    <tr class="Form-row">
        <td class="Form-label"><?= t('server.permissions.torrents') ?></td>
        <td class="Form-items">
            <div class="PermissionCheckbox-list ">
                <?
                display_perm('torrents_edit');
                display_perm('torrents_check');
                display_perm('self_torrents_check');
                display_perm('torrents_check_log');
                display_perm('torrents_delete');
                display_perm('torrents_delete_fast');
                display_perm('torrents_freeleech');
                display_perm('torrents_search_fast');
                display_perm('torrents_add_artist');
                display_perm('edit_unknowns');
                display_perm('torrents_hide_dnu');
                display_perm('torrents_fix_ghosts');
                display_perm('torrents_trumpable');
                display_perm('torrents_slot_edit');
                ?>
            </div>
        </td>
    </tr>
    <tr class="Form-row">
        <td class="Form-label"><?= t('server.permissions.administrative') ?></td>
        <td class="Form-items">
            <div class="PermissionCheckbox-list ">
                <?
                display_perm('admin_manage_news');
                display_perm('admin_manage_blog');
                display_perm('admin_manage_polls');
                display_perm('admin_manage_forums');
                display_perm('admin_manage_fls');
                display_perm('admin_manage_user_fls');
                display_perm('admin_manage_badges');
                display_perm('admin_manage_applicants');
                display_perm('admin_send_bonus');
                display_perm('admin_reports');
                display_perm('admin_bp_history');
                display_perm('admin_advanced_user_search');
                display_perm('admin_create_users');
                display_perm('admin_donor_log');
                display_perm('admin_manage_stylesheets');
                display_perm('admin_manage_ipbans');
                display_perm('admin_dnu');
                display_perm('admin_clear_cache');
                display_perm('admin_whitelist');
                display_perm('admin_manage_permissions');
                display_perm('admin_schedule');
                display_perm('admin_login_watch');
                display_perm('admin_manage_wiki');
                display_perm('admin_update_geoip');
                display_perm('admin_interviewer');
                display_perm('events_reward_tokens');
                display_perm('events_reward_bonus');
                display_perm('events_reward_invites');
                display_perm('events_reward_badges');
                display_perm('events_reward_history');
                ?>
            </div>
        </td>
    <tr class="Form-row">
        <td>
            <i><?= t('server.permissions.only_applicable_to_lower_class') ?></i>
        </td>
    </tr>
    <tr class="Form-row">
        <td>
            <input class="Button" type="submit" name="submit" value="<?= t('server.common.submit') ?>" />
        </td>
    </tr>
    </div>
<?
}
