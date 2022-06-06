<?php
$lang_permissions = array(
    'site' => 'Site',
    'users' => 'Users',
    'only_applicable_to_lower_class' => '*Everything is only applicable to users with the same or lower class level',
    'torrents' => 'Torrents',
    'administrative' => 'Administrative',




    'site_leech' => 'Can leech',
    'site_upload' => 'Can upload',
    'site_vote' => 'Can vote on requests',
    'site_submit_requests' => 'Can submit requests',
    'site_advanced_search' => 'Can use advanced search',
    'site_top10' => 'Can access top 10',
    'site_torrents_notify' => 'Can access torrents notifications system',
    'site_collages_create' => 'Can create collages',
    'site_collages_manage' => 'Can manage collages (add torrents, sorting)',
    'site_collages_delete' => 'Can delete collages',
    'site_collages_subscribe' => 'Can access collage subscriptions',
    'site_collages_personal' => 'Can have a personal collage',
    'site_collages_renamepersonal' => 'Can rename own personal collages',
    'site_advanced_top10' => 'Can access advanced top 10',
    'site_album_votes' => 'Can vote for favorite torrents',
    'site_make_bookmarks' => 'Can make bookmarks',
    'site_edit_wiki' => 'Can edit wiki pages',
    'site_can_invite_always' => 'Can invite users even when invites are closed',
    'site_can_invite' => 'Can invite users',
    'site_send_unlimited_invites' => 'Can send unlimited invites',
    'site_moderate_requests' => 'Can moderate any request',
    'site_delete_artist' => 'Can delete artists (must be able to delete torrents+requests)',
    'forums_polls_create' => 'Can create polls in the forums',
    'forums_polls_moderate' => 'Can feature and close polls',
    'site_moderate_forums' => 'Can moderate the forums',
    'site_admin_forums' => 'Can administrate the forums',
    'site_view_flow' => 'Can view site stats and data pools',
    'site_view_full_log' => 'Can view the full site log',
    'site_view_torrent_snatchlist' => 'Can view torrent snatch lists',
    'site_recommend_own' => 'Can add own torrents to recommendations list',
    'site_manage_recommendations' => 'Can edit recommendations list',
    'site_delete_tag' => 'Can delete tags',
    'site_disable_ip_history' => 'Disable IP history',
    'zip_downloader' => 'Download multiple torrents at once',
    'site_debug' => 'View site debug tables',
    'site_proxy_images' => 'Proxy images through the server',
    'site_search_many' => 'Can go past low limit of search results',
    'site_collages_recover' => 'Can recover \'deleted\' collages',
    'project_team' => 'Part of the project team',
    'site_tag_aliases_read' => 'Can view the list of tag aliases',
    'forums_see_hidden' => 'Can see hidden posts',
    'show_admin_team' => 'Show admin team',
    'show_staff_username' => 'Show staff username',
    'users_edit_usernames' => 'Can edit usernames',
    'users_edit_ratio' => 'Can edit anyone\'s upload/download amounts',
    'users_edit_own_ratio' => 'Can edit own upload/download amounts',
    'users_edit_titles' => 'Can edit titles',
    'users_edit_avatars' => 'Can edit avatars',
    'users_edit_invites' => 'Can edit invite numbers and cancel sent invites',
    'users_edit_watch_hours' => 'Can edit contrib watch hours',
    'users_edit_reset_keys' => 'Can reset any passkey/authkey',
    'users_edit_profiles' => 'Can edit anyone\'s profile',
    'users_view_friends' => 'Can view anyone\'s friends',
    'users_reset_own_keys' => 'Can reset own passkey/authkey',
    'users_edit_password' => 'Can change password',
    'users_promote_below' => 'Can promote users to below current level',
    'users_promote_to' => 'Can promote users up to current level',
    'users_give_donor' => 'Can give donor access',
    'users_warn' => 'Can warn users',
    'users_disable_users' => 'Can disable users',
    'users_disable_posts' => 'Can disable users\' posting privileges',
    'users_disable_any' => 'Can disable any users\' rights',
    'users_delete_users' => 'Can delete anyone\'s account',
    'users_view_invites' => 'Can view who user has invited',
    'users_view_seedleech' => 'Can view what a user is seeding or leeching',
    'users_view_uploaded' => 'Can view a user\'s uploads, regardless of privacy level',
    'users_view_keys' => 'Can view passkeys',
    'users_view_ips' => 'Can view IP addresses',
    'users_view_email' => 'Can view email addresses',
    'users_invite_notes' => 'Can add a note when inviting someone',
    'users_override_paranoia' => 'Can override paranoia',
    'users_make_invisible' => 'Can make users invisible',
    'users_logout' => 'Can log users out',
    'users_mod' => 'Can access basic moderator tools (Admin comment)',
    'staff_award' => 'Can view award',
    'users_view_disabled' => 'Can view disabled privilege',
    'torrents_edit' => 'Can edit any torrent',
    'torrents_check' => 'Can check torrents',
    'self_torrents_check' => 'Can check self torrents',
    'torrents_check_log' => 'Can view check log',
    'torrents_delete' => 'Can delete torrents',
    'torrents_delete_fast' => 'Can delete more than 3 torrents at a time',
    'torrents_freeleech' => 'Can make torrents freeleech',
    'torrents_search_fast' => 'Unlimit search frequency (for scripts)',
    'torrents_add_artist' => 'Can add artists to any group',
    'edit_unknowns' => 'Can edit unknown release information',
    'torrents_hide_dnu' => 'Hide the Do Not Upload list by default',
    'torrents_fix_ghosts' => 'Can fix ghost groups on artist pages',
    'torrents_trumpable' => 'Can manage trumpable marks of torrents',
    'torrents_slot_edit' => 'Can manage which slot should a torrent stay in',
    'admin_manage_news' => 'Can manage site news',
    'admin_manage_blog' => 'Can manage the site blog',
    'admin_manage_polls' => 'Can manage polls',
    'admin_manage_forums' => 'Can manage forums (add/edit/delete)',
    'admin_manage_fls' => 'Can manage First Line Support (FLS) crew',
    'admin_manage_user_fls' => 'Can manage user FL tokens',
    'admin_manage_badges' => 'Can manage badges',
    'admin_manage_applicants' => 'Can manage job roles and user applications',
    'admin_send_bonus' => 'Can give points in the forum, but not deduct own points',
    'admin_reports' => 'Can access reports system',
    'admin_bp_history' => 'Can view bonus points spent by other users',
    'admin_advanced_user_search' => 'Can access advanced user search',
    'admin_create_users' => 'Can create users through an administrative form',
    'admin_donor_log' => 'Can view the donor log',
    'admin_manage_stylesheets' => 'Can manage stylesheets',
    'admin_manage_ipbans' => 'Can manage IP bans',
    'admin_dnu' => 'Can manage do not upload list',
    'admin_clear_cache' => 'Can clear cached pages',
    'admin_whitelist' => 'Can manage the list of allowed clients',
    'admin_manage_permissions' => 'Can edit permission classes/user permissions',
    'admin_schedule' => 'Can run the site schedule',
    'admin_login_watch' => 'Can manage login watch',
    'admin_manage_wiki' => 'Can manage wiki access',
    'admin_update_geoip' => 'Can update geoIP data',
    'admin_interviewer' => 'Can manage user application',
    'events_reward_tokens' => 'Events reward tokens',
    'events_reward_bonus' => 'Events reward bonus',
    'events_reward_invites' => 'Events reward invites',
    'events_reward_badges' => 'Events reward badges',
    'events_reward_history' => 'Events reward history',
);