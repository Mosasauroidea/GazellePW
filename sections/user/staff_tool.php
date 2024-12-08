<?
$UserID = (int)$_GET['id'];
G::$DB->query("SELECT p.Level as Class From users_main as m left join permissions as p on p.ID = m.PermissionID where m.ID = $UserID");
if (!$DB->has_results()) { // If user doesn't exist
    error(404);
}
list($Class) =  $DB->next_record(MYSQLI_NUM);
if (!check_perms('users_mod', $Class)) {
    error(403);
}

$DB->query("
		SELECT
			m.Username,
			m.Email,
			m.LastAccess,
			m.IP,
			p.Level AS Class,
			m.Uploaded,
			m.Downloaded,
			m.BonusPoints,
			m.RequiredRatio,
			m.Title,
			m.torrent_pass,
			m.Enabled,
			m.Paranoia,
			m.Invites,
			m.can_leech,
			m.Visible,
			i.JoinDate,
			i.Info,
			i.Avatar,
			i.AdminComment,
			i.Donor,
			i.Found,
			i.Artist,
			i.Warned,
			i.SupportFor,
			i.RestrictedForums,
			i.PermittedForums,
			i.Inviter,
			inviter.Username,
			COUNT(DISTINCT posts.id) AS ForumPosts,
			i.RatioWatchEnds,
			i.RatioWatchDownload,
			i.DisableAvatar,
			i.DisableInvites,
			i.DisablePosting,
			i.DisablePoints,
			i.DisableForums,
			i.DisableTagging,
			i.DisableUpload,
			i.DisableWiki,
			i.DisablePM,
			i.DisableIRC,
			i.DisableRequests," . "
			m.FLTokens,
			m.2FA_Key,
			SHA1(i.AdminComment),
			i.InfoTitle,
			la.Type AS LockedAccount,
			i.DisableCheckAll,
			i.DisableCheckSelf,
			m.TotalUploads,
            m.BonusUploaded,
            COUNT(DISTINCT t.ID) AS Uploads
		FROM users_main AS m
			JOIN users_info AS i ON i.UserID = m.ID
			LEFT JOIN users_main AS inviter ON i.Inviter = inviter.ID
			LEFT JOIN permissions AS p ON p.ID = m.PermissionID
			LEFT JOIN forums_posts AS posts ON posts.AuthorID = m.ID
			LEFT JOIN locked_accounts AS la ON la.UserID = m.ID
            LEFT JOIN torrents AS t ON t.UserID = m.ID
		WHERE m.ID = '$UserID'
		GROUP BY AuthorID");

if (!$DB->has_results()) { // If user doesn't exist
    header("Location: log.php?search=User+$UserID");
}

list($Username, $Email, $LastAccess, $IP, $Class, $Uploaded, $Downloaded, $BonusPoints, $RequiredRatio, $CustomTitle, $torrent_pass, $Enabled, $Paranoia, $Invites, $DisableLeech, $Visible, $JoinDate, $Info, $Avatar, $AdminComment, $Donor, $Found, $Artist, $Warned, $SupportFor, $RestrictedForums, $PermittedForums, $InviterID, $InviterName, $ForumPosts, $RatioWatchEnds, $RatioWatchDownload, $DisableAvatar, $DisableInvites, $DisablePosting, $DisablePoints, $DisableForums, $DisableTagging, $DisableUpload, $DisableWiki, $DisablePM, $DisableIRC, $DisableRequests, $FLTokens, $FA_Key, $CommentHash, $InfoTitle, $LockedAccount, $DisableCheckAll, $DisableCheckSelf, $TotalUploads, $BonusUploaded, $Uploads) = $DB->next_record(MYSQLI_NUM, array(9, 12));

$DB->query("select count(1) from thumb where ToUserID = $UserID");
list($ThumbCount) = $DB->next_record();

$donationInfo = $donation->info($UserID);

View::show_header(t('server.user.staff_tools'), "jquery.imagesloaded,jquery.wookmark,user,bbcode,comments,info_paster,tiles", "PageUserShow");
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, true, true, true, false, true, false, true) ?></h2>
    </div>
    <form id="staff_tools" class="Form manage_form" name="user" id="form" action="user.php" method="post">
        <input type="hidden" name="comment_hash" value="<?= $CommentHash ?>" />
        <input type="hidden" name="action" value="moderate" />
        <input type="hidden" name="userid" value="<?= $UserID ?>" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <div class="Form-rowList" variant="header">
            <table>
                <tr class="Form-rowHeader">
                    <td class="Form-title" colspan="2">
                        <?= t('server.common.edit') ?>
                    </td>
                </tr>
                <tr class="Form-rowSubHeader">
                    <td class="Form-title" colspan="2">
                        <?= t('server.user.info') ?>
                    </td>
                </tr>
                <? if (check_perms('users_edit_usernames', $Class)) { ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.account') ?></td>
                        <td class="Form-inputs"><input class="Input" type="text" size="20" name="Username" value="<?= display_str($Username) ?>" /></td>
                    </tr>
                <?
                }
                if (check_perms('users_edit_titles')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.customtitle') ?></td>
                        <td class="Form-inputs"><input class="Input" type="text" name="Title" value="<?= display_str($CustomTitle) ?>" /></td>
                    </tr>
                <?
                }

                if (check_perms('users_promote_below', $Class) || check_perms('users_promote_to', $Class - 1)) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.promote_class') ?></td>
                        <td class="Form-inputs">
                            <select class="Input" name="Class">
                                <?
                                foreach ($ClassLevels as $CurClass) {
                                    if ($CurClass['Secondary']) {
                                        continue;
                                    } elseif ($LoggedUser['ID'] != $UserID && !check_perms('users_promote_to', $Class - 1) && $CurClass['Level'] == $LoggedUser['EffectiveClass']) {
                                        break;
                                    } elseif ($CurClass['Level'] > $LoggedUser['EffectiveClass']) {
                                        break;
                                    }
                                    if ($Class === $CurClass['Level']) {
                                        $Selected = ' selected="selected"';
                                    } else {
                                        $Selected = '';
                                    }
                                ?>
                                    <option class="Select-option" value="<?= $CurClass['ID'] ?>" <?= $Selected ?>><?= $CurClass['Name'] . ' (' . $CurClass['Level'] . ')' ?></option>
                                <?      } ?>
                            </select>
                        </td>
                    </tr>
                <?
                }

                if (check_perms('users_give_donor')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.donor') ?></td>
                        <td class="Form-inputs"><input class="Input" type="checkbox" name="Donor" <? if ($Donor == 1) { ?> checked="checked" <? } ?> /></td>
                    </tr>
                <?
                }
                if (check_perms('users_promote_below') || check_perms('users_promote_to')) { ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.se_class') ?></td>
                        <td class="Form-inputs">
                            <?
                            $DB->query("SELECT p.ID, p.Name, l.UserID FROM permissions AS p LEFT JOIN users_levels AS l ON l.PermissionID = p.ID AND l.UserID = '$UserID' WHERE p.Secondary = 1 ORDER BY p.Name");
                            $i = 0;
                            while (list($PermID, $PermName, $IsSet) = $DB->next_record()) {
                                $i++;
                            ?>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" id="perm_<?= $PermID ?>" name="secondary_classes[]" value="<?= $PermID ?>" <? if ($IsSet) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="perm_<?= $PermID ?>"><?= $PermName ?></label>
                                </div>
                            <? if ($i % 3 == 0) {
                                    echo "\t\t\t\t<br />\n";
                                }
                            } ?>
                        </td>
                    </tr>
                <?  }
                if (check_perms('users_make_invisible')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.view_list') ?></td>
                        <td class="Form-inputs">
                            <input class="Input" type="checkbox" name="Visible" <? if ($Visible == 1) { ?> checked="checked" <? } ?> />
                        </td>
                    </tr>
                <?
                }

                if (check_perms('users_edit_ratio', $Class) || (check_perms('users_edit_own_ratio') && $UserID == $LoggedUser['ID'])) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.uploaded_title') ?>"><?= t('server.user.uploaded') ?></td>
                        <td class="Form-inputs">
                            <input type="hidden" name="OldUploaded" value="<?= $Uploaded ?>" />
                            <input class="Input" type="text" size="20" name="Uploaded" value="<?= $Uploaded ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.downloaded_title') ?>"><?= t('server.user.downloaded') ?></td>
                        <td class="Form-inputs">
                            <input type="hidden" name="OldDownloaded" value="<?= $Downloaded ?>" />
                            <input class="Input" type="text" size="20" name="Downloaded" value="<?= $Downloaded ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.bonus_points_title') ?>"><?= t('server.user.bonus_points') ?></td>
                        <td class="Form-inputs">
                            <input type="hidden" name="OldBonusPoints" value="<?= $BonusPoints ?>" />
                            <input class="Input" type="text" size="20" name="BonusPoints" value="<?= $BonusPoints ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.merge_from_title') ?>"><?= t('server.user.merge_from') ?></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" size="40" name="MergeStatsFrom" />
                        </td>
                    </tr>
                <?
                }

                if (check_perms('users_edit_invites')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="Number of invites"><?= t('server.user.invite') ?></td>
                        <td class="Form-inputs"><input class="Input" type="text" size="5" name="Invites" value="<?= $Invites ?>" /></td>
                    </tr>
                <?
                }

                if (check_perms('admin_manage_user_fls')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="Number of FL tokens"><?= t('server.user.token') ?></td>
                        <td class="Form-inputs"><input class="Input" type="text" size="5" name="FLTokens" value="<?= $FLTokens ?>" /></td>
                    </tr>
                <?
                }

                if (check_perms('admin_manage_fls') || (check_perms('users_mod') && $OwnProfile)) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.staff_mark_title') ?>"><?= t('server.user.staff_mark') ?></td>
                        <td class="Form-inputs"><input class="Input" type="text" name="SupportFor" value="<?= display_str($SupportFor) ?>" /></td>
                    </tr>
                <?
                }

                if (check_perms('users_edit_reset_keys')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.reset') ?></td>
                        <td class="Form-inputs" id="reset_td">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ResetRatioWatch" id="ResetRatioWatch" />
                                <label class="Checkbox-label" for="ResetRatioWatch"><?= t('server.user.ratio_watch') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ResetPasskey" id="ResetPasskey" />
                                <label class="Checkbox-label" for="ResetPasskey"><?= t('server.user.passkey') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ResetAuthkey" id="ResetAuthkey" />
                                <label class="Checkbox-label" for="ResetAuthkey"><?= t('server.user.authkey') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ResetIPHistory" id="ResetIPHistory" />
                                <label class="Checkbox-label" for="ResetIPHistory"><?= t('server.user.ip_history') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ResetEmailHistory" id="ResetEmailHistory" />
                                <label class="Checkbox-label" for="ResetEmailHistory"><?= t('server.user.email_history') ?></label>
                            </div>
                            <br />
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ResetSnatchList" id="ResetSnatchList" />
                                <label class="Checkbox-label" for="ResetSnatchList"><?= t('server.user.snatch_list') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="ResetDownloadList" id="ResetDownloadList" />
                                <label class="Checkbox-label" for="ResetDownloadList"><?= t('server.user.download_list') ?></label>
                            </div>
                        </td>
                    </tr>
                <?
                }

                if (check_perms('users_edit_password')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.new_password') ?></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" size="30" id="change_password" name="ChangePassword" />
                            <button class="Button" type="button" id="random_password"><?= t('server.user.generate') ?></button>
                        </td>
                    </tr>

                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.2fa') ?></td>
                        <td class="Form-inputs">
                            <? if ($FA_Key) { ?>
                                <a href="user.php?action=2fa&page=user&do=disable&userid=<?= $UserID ?>"><?= t('server.user.close') ?></a>
                            <? } else { ?>
                                <?= t('server.user.closed') ?>
                            <? } ?>
                        </td>
                    </tr>
                <? } ?>

                <? if (check_perms('users_warn')) { ?>
                    <tr class="Form-rowSubHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.warn') ?>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label">
                            <?= t('server.user.warned') ?>
                        </td>
                        <td class="Form-inputs">
                            <input type="checkbox" name="Warned" <? if ($Warned != '0000-00-00 00:00:00') { ?> checked="checked" <? } ?> />
                        </td>
                    </tr>
                    <? if ($Warned == '0000-00-00 00:00:00') { /* user is not warned */ ?>
                        <tr class="Form-row">
                            <td class="Form-label">
                                <?= t('server.user.warn_time') ?>
                            </td>
                            <td class="Form-inputs">
                                <select class="Input" name="WarnLength">
                                    <option class="Select-option" value="">---</option>
                                    <option class="Select-option" value="1"><?= t('server.user.1_week') ?></option>
                                    <option class="Select-option" value="2"><?= t('server.user.2_week') ?></option>
                                    <option class="Select-option" value="4"><?= t('server.user.4_week') ?></option>
                                    <option class="Select-option" value="8"><?= t('server.user.8_week') ?></option>
                                </select>
                            </td>
                        </tr>
                    <? } else { /* user is warned */ ?>
                        <tr class="Form-row">
                            <td class="Form-label">
                                <?= t('server.user.warn_time') ?>
                            </td>
                            <td class="Form-inputs">
                                <select class="Input" name="ExtendWarning" onchange="ToggleWarningAdjust(this);">
                                    <option class="Select-option">---</option>
                                    <option class="Select-option" value="1"><?= t('server.user.1_week') ?></option>
                                    <option class="Select-option" value="2"><?= t('server.user.2_week') ?></option>
                                    <option class="Select-option" value="4"><?= t('server.user.4_week') ?></option>
                                    <option class="Select-option" value="8"><?= t('server.user.8_week') ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="Form-row" id="ReduceWarningTR">
                            <td class="Form-label">
                                <?= t('server.user.free_time') ?>
                            </td>
                            <td class="Form-inputs">
                                <select class="Input" name="ReduceWarning">
                                    <option class="Select-option">---</option>
                                    <option class="Select-option" value="1"><?= t('server.user.1_week') ?></option>
                                    <option class="Select-option" value="2"><?= t('server.user.2_week') ?></option>
                                    <option class="Select-option" value="4"><?= t('server.user.4_week') ?></option>
                                    <option class="Select-option" value="8"><?= t('server.user.8_week') ?></option>
                                </select>
                            </td>
                        </tr>
                    <? } ?>
                    <tr class="Form-row">
                        <td class="Form-label" data-tooltip="<?= t('server.user.warn_reason_title') ?>">
                            <?= t('server.user.warn_reason') ?>
                        </td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" name="WarnReason" />
                        </td>
                    </tr>
                <?  } ?>
                <? if (check_perms('users_disable_any')) { ?>
                    <tr class="Form-rowSubHeader">
                        <td class="Form-title" colspan="2">
                            <?= t('server.user.disable_account') ?>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.account_disable') ?></td>
                        <td class="Form-inputs">
                            <input type="checkbox" name="LockAccount" id="LockAccount" <? if ($LockedAccount) { ?> checked="checked" <? } ?> />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.reason') ?></td>
                        <td class="Form-inputs">
                            <select class="Input" name="LockReason">
                                <option class="Select-option" value="---">---</option>
                                <option class="Select-option" value="<?= STAFF_LOCKED ?>" <? if ($LockedAccount == STAFF_LOCKED) { ?> selected <? } ?>><?= t('server.user.admin_account') ?></option>
                            </select>
                        </td>
                    </tr>
                <?  }  ?>
                <tr class="Form-rowSubHeader">
                    <td class="Form-title" colspan="2">
                        <?= t('server.user.user_po') ?>
                    </td>
                </tr>
                <? if (check_perms('users_disable_posts') || check_perms('users_disable_any')) {
                    $DB->query("SELECT DISTINCT Email, IP FROM users_history_emails WHERE UserID = $UserID ORDER BY Time ASC");
                    $Emails = $DB->to_array();
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.user.user_disable') ?></td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="DisablePosting" id="DisablePosting" <? if ($DisablePosting == 1) { ?> checked="checked" <? } ?> />
                                <label class="Checkbox-label" for="DisablePosting"><?= t('server.user.posting') ?></label>
                            </div>
                            <? if (check_perms('users_disable_any')) { ?>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableAvatar" id="DisableAvatar" <? if ($DisableAvatar == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableAvatar"><?= t('server.user.avatar') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableForums" id="DisableForums" <? if ($DisableForums == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableForums"><?= t('server.user.forums') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableIRC" id="DisableIRC" <? if ($DisableIRC == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableIRC"><?= t('server.user.irc') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisablePM" id="DisablePM" <? if ($DisablePM == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisablePM"><?= t('server.user.pm') ?></label>
                                </div>
                                <br />
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableLeech" id="DisableLeech" <? if ($DisableLeech == 0) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableLeech"><?= t('server.user.leech') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableRequests" id="DisableRequests" <? if ($DisableRequests == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableRequests"><?= t('server.common.requests') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableUpload" id="DisableUpload" <? if ($DisableUpload == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableUpload"><?= t('server.user.torrent_upload') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisablePoints" id="DisablePoints" <? if ($DisablePoints == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisablePoints"><?= t('server.user.bonus_points') ?></label>
                                </div>
                                <br />
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableTagging" id="DisableTagging" <? if ($DisableTagging == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableTagging" data-tooltip="<?= t('server.user.tagging_title') ?>"><?= t('server.user.tagging') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableWiki" id="DisableWiki" <? if ($DisableWiki == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableWiki"><?= t('server.user.wiki') ?></label>
                                </div>
                                <br />
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableInvites" id="DisableInvites" <? if ($DisableInvites == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableInvites"><?= t('server.user.invites') ?></label>
                                </div>
                                <br />
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableCheckAll" id="DisableCheckAll" <? if ($DisableCheckAll == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableCheckAll"><?= t('server.user.check_all_torrents') ?></label>
                                </div>
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="DisableCheckSelf" id="DisableCheckSelf" <? if ($DisableCheckSelf == 1) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="DisableCheckSelf"><?= t('server.user.check_his_her_torrents') ?></label>
                                </div>
                        </td>
                    </tr>
                    <? if ($Emails) { ?>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.user.hacked') ?></td>
                            <td class="Form-inputs">
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="SendHackedMail" id="SendHackedMail" />
                                    <label class="Checkbox-label" for="SendHackedMail">
                                        <?= t('server.user.send_hacked_account_email_to') ?>
                                    </label>
                                </div>
                                <select class="Input" name="HackedEmail">
                                    <?
                                    foreach ($Emails as $Email) {
                                        list($Address, $IP) = $Email;
                                    ?>
                                        <option class="Select-option" value="<?= display_str($Address) ?>">
                                            <?= display_str($Address) ?> - <?= display_str($IP) ?>
                                        </option>
                                    <? } ?>
                                </select>
                            </td>
                        </tr>
                    <? } ?>
                <? } ?>
            <? } ?>
            <? if (check_perms('users_disable_any')) { ?>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.user.account') ?></td>
                    <td class="Form-inputs">
                        <select class="Input" name="UserStatus">
                            <option class="Select-option" value="0" <? if ($Enabled == '0') { ?> selected="selected" <? } ?>><?= t('server.user.unconfirmed') ?></option>
                            <option class="Select-option" value="1" <? if ($Enabled == '1') { ?> selected="selected" <? } ?>><?= t('server.user.enabled') ?></option>
                            <option class="Select-option" value="2" <? if ($Enabled == '2') { ?> selected="selected" <? } ?>><?= t('server.user.disabled') ?></option>
                            <? if (check_perms('users_delete_users')) { ?>
                                <optgroup class="Select-group" label="-- WARNING --">
                                    <option class="Select-option" value="delete"><?= t('server.user.delete_account') ?></option>
                                </optgroup>
                            <?      } ?>
                        </select>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" data-tooltip="<?= t('server.user.user_reason_title') ?>"><?= t('server.user.user_reason') ?></td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="UserReason" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" data-tooltip="<?= t('server.user.restricted_forums_title') ?>"><?= t('server.user.restricted_forums') ?></td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="RestrictedForums" value="<?= display_str($RestrictedForums) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" data-tooltip="<?= t('server.user.permitted_forums_title') ?>"><?= t('server.user.permitted_forums') ?></td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="PermittedForums" value="<?= display_str($PermittedForums) ?>" />
                    </td>
                </tr>

            <?  } ?>
            <? if (check_perms('users_logout')) { ?>
                <tr class="Form-rowSubHeader">
                    <td class="Form-title" colspan="2">
                        <?= t('server.user.session') ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.user.reset_session') ?></td>
                    <td class="Form-inputs"><input type="checkbox" name="ResetSession" id="ResetSession" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.user.logout') ?></td>
                    <td class="Form-inputs"><input type="checkbox" name="LogOut" id="LogOut" /></td>
                </tr>
            <?
            }
            if (check_perms('users_mod')) {
                DonationsView::render_mod_donations($donationInfo['Rank'], $donationInfo['TotRank']);
            }
            ?>
            <tr class="Form-rowSubHeader">
                <td class="Form-title" colspan="2"><?= t('server.user.submit') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label" data-tooltip="<?= t('server.user.reason_title') ?>"><?= t('server.user.reason') ?>:</td>
                <td class="Form-inputs">
                    <textarea class="Input wide_input_text" rows="1" cols="35" name="Reason" id="Reason" onkeyup="resize('Reason');"></textarea>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.paste_user_stats') ?>:</td>
                <td class="Form-inputs">
                    <button class="Button" type="button" id="paster"><?= t('server.user.paste') ?></button>
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2">
                    <button class="Button" type="submit" value="Save changes"><?= t('client.common.save') ?></button>
                </td>
            </tr>
            </table>
        </div>
    </form>
</div>
<? View::show_footer(); ?>