<?

sleep(5);
if (ENABLE_BADGE) {
    $BadgeLabels = Badges::get_badge_labels();
    foreach ($BadgeLabels as $BadgeLabel) {
        if (Badges::checkBadgeFunc($BadgeLabel['Label']) && $BadgeLabel['Auto']) {
            $Badges = Badges::get_badges_by_label($BadgeLabel['Label']);
            foreach ($Badges as $Badge) {
                $Users = Badges::badgeFunc($BadgeLabel['Label'], $Badge['Count']);
                foreach ($Users as $UserID) {
                    Badges::gave($UserID, $Badge['ID']);
                }
            }
            sleep(1);
        }
    }
}
