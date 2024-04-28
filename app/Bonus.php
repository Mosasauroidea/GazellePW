<?php


namespace Gazelle;

class Bonus {
    private $items;
    /** @var \DB_MYSQL */
    private $db;
    /** @var \CACHE */
    private $cache;

    const CACHE_ITEM = 'bonus_item';
    const CACHE_SUMMARY = 'bonus_summary.';
    const CACHE_HISTORY = 'bonus_history.';

    public function __construct($db, $cache) {
        $this->db = $db;
        $this->cache = $cache;
        $this->items = $this->cache->get_value(self::CACHE_ITEM);
//        $this->items = false;
        if ($this->items === false) {
            $this->db->query("SELECT ID, Price, Amount, MinClass, FreeClass, OffPrice, OffClass, Label, Title FROM bonus_item order by rank");
            $this->items = $this->db->has_results() ? $this->db->to_array('Label') : [];
            $this->cache->cache_value(self::CACHE_ITEM, $this->items, 86400 * 30);
        }
    }

    public function getList() {
        return $this->items;
    }

    public function getItem($label) {
        return array_key_exists($label, $this->items) ? $this->items[$label] : null;
    }

    public function getEffectivePrice($label, $effective_class) {
        $item  = $this->items[$label];
        $price = $item['Price'];
        if ($effective_class >= $item['OffClass']) {
            $price = $item['OffPrice'];
        }
        if ($effective_class >= $item['FreeClass']) {
            $price = 0;
        }

        return $price;
    }

    public function getListOther($user_id, $balance) {
        $list_other = [];
        foreach ($this->items as $label => $item) {
            if (!\Users::canPurchaseInvite($user_id, $item['MinClass'])) {
                continue;
            }
            if (preg_match('/^other-\d$/', $label) && $balance >= $item['Price']) {
                $list_other[] = [
                    'Label' => $item['Label'],
                    'Name'  => $item['Title'],
                    'Price' => $item['Price'],
                    'After' => $balance - $item['Price'],
                ];
            }
        }
        return $list_other;
    }

    public function getUserSummary($user_id) {
        $key = self::CACHE_SUMMARY . $user_id;
        $summary = $this->cache->get_value($key);
        if ($summary === false) {
            $this->db->prepared_query('SELECT count(*) AS nr, sum(price) AS total FROM bonus_history WHERE UserID = ?', $user_id);
            $summary = $this->db->has_results() ? $this->db->next_record(MYSQLI_ASSOC) : ['nr' => 0, 'total' => 0];
            $this->cache->cache_value($key, $summary, 86400 * 7);
        }
        return $summary;
    }

    public function getUserHistory($user_id, $page, $items_per_page) {
        $key = self::CACHE_HISTORY . "{$user_id}.{$page}";
        $history = $this->cache->get_value($key);
        if ($history === false) {
            $this->db->prepared_query(
                '
				SELECT i.Title, i.Label, h.Price, h.PurchaseDate, h.OtherUserID
				FROM bonus_history h
				INNER JOIN bonus_item i ON i.ID = h.ItemID
				WHERE h.UserID = ?
				ORDER BY PurchaseDate DESC
				LIMIT ? OFFSET ?
				',
                $user_id,
                $items_per_page,
                $items_per_page * ($page - 1)
            );
            $history = $this->db->has_results() ? $this->db->to_array() : null;
            $this->cache->cache_value($key, $history, 86400 * 3);
            /* since we had to fetch this page, invalidate the next one */
            $this->cache->delete_value(self::CACHE_HISTORY . "{$user_id}." . ($page + 1));
        }
        return $history;
    }

    public function purchaseInvite($user_id) {
        $item = $this->items['invite'];
        if (!\Users::canPurchaseInvite($user_id, $item['MinClass'])) {
            return false;
        }


        $this->db->begin_transaction();
        $this->db->prepared_query(
            "UPDATE users_main SET Invites = Invites + 1, BonusPoints = BonusPoints - ? WHERE BonusPoints >= ? AND ID = ?",
            $item['Price'],
            $item['Price'],
            $user_id
        );
        if ($this->db->affected_rows() != 1) {
            $this->db->rollback();
            return false;
        }

        $this->addPurchaseHistory($item['ID'], $user_id, $item['Price']);
        $this->db->commit();
        $this->cache->delete_value('user_stats_' . $user_id);
        $this->cache->delete_value('user_info_heavy_' . $user_id);
        return true;
    }


    public function purchaseHNR($user_id) {
        $item = $this->items['eliminate_a_hnr'];
        if (!\Users::canPurchaseInvite($user_id, $item['MinClass'])) {
            return false;
        }
        $price = $item['Price'];
        $stats = \Users::user_stats($user_id, true);
        if ($stats['BonusPoints'] < $price) {
            return false;
        }
        $this->db->begin_transaction();
        if ($price > 0) {
            /* if the price is 0, nothing changes so avoid hitting the db */
            $this->db->prepared_query(
                'UPDATE users_main SET BonusPoints = BonusPoints - ? WHERE BonusPoints >= ? AND ID = ?',
                $price,
                $price,
                $user_id
            );
            if ($this->db->affected_rows() != 1) {
                $this->db->rollback();
                return false;
            }
            // Sanity check
            $new_stats = \Users::user_stats($user_id, true);
            if (!($new_stats['BonusPoints'] >= 0 && $new_stats['BonusPoints'] < $stats['BonusPoints'])) {
                $this->db->rollback();
                return false;
            }
        }
        // get latest hr
        $Stats = \Users::eliminate_latest_hnr($user_id);
        if ($Stats != 1) {
            $this->db->rollback();
            return $Stats;
        }
        $this->addPurchaseHistory($item['ID'], $user_id, $price);
        $this->db->commit();
        $this->cache->delete_value('user_info_heavy_' . $user_id);
        return true;
    }

    public function purchaseTitle($user_id, $label, $title, $effective_class) {
        $item = $this->items[$label];
        $title = $label === 'title-bb-y' ? \Text::full_format($title) : \Text::strip_bbcode($title);
        $price = $this->getEffectivePrice($label, $effective_class);
        $stats = \Users::user_stats($user_id, true);
        if ($stats['BonusPoints'] < $price) {
            return false;
        }

        $this->db->begin_transaction();
        if ($price > 0) {
            /* if the price is 0, nothing changes so avoid hitting the db */
            $this->db->prepared_query(
                'UPDATE users_main SET BonusPoints = BonusPoints - ? WHERE BonusPoints >= ? AND ID = ?',
                $price,
                $price,
                $user_id
            );
            if ($this->db->affected_rows() != 1) {
                $this->db->rollback();
                return false;
            }
            // Sanity check
            $new_stats = \Users::user_stats($user_id, true);
            if (!($new_stats['BonusPoints'] >= 0 && $new_stats['BonusPoints'] < $stats['BonusPoints'])) {
                $this->db->rollback();
                return false;
            }
        }
        if (!\Users::setCustomTitle($user_id, $title)) {
            $this->db->rollback();
            return false;
        }
        $this->addPurchaseHistory($item['ID'], $user_id, $price);
        $this->db->commit();
        $this->cache->delete_value('user_info_heavy_' . $user_id);
        return true;
    }

    public function purchaseUpload($user_id, $label) {
        if (!array_key_exists($label, $this->items)) {
            return false;
        }
        $item  = $this->items[$label];
        $amount = $item['Amount'] * 1024 * 1024 * 1024; // 单位GB
        $price  = $item['Price'];

        $stats  = \Users::user_stats($user_id, true);
        if ($stats['BonusPoints'] < $price) {
            return false;
        }
        $this->db->begin_transaction();
        $this->db->prepared_query(
            'UPDATE users_main SET Uploaded = Uploaded + ?, BonusUploaded = BonusUploaded + ?, BonusPoints = BonusPoints - ? WHERE BonusPoints >= ? AND ID = ?',
            $amount,
            $amount,
            $price,
            $price,
            $user_id
        );
        if ($this->db->affected_rows() != 1) {
            $this->db->rollback();
            return false;
        }
        $new_stats = \Users::user_stats($user_id, true);
        if (!($new_stats['BonusPoints'] >= 0 && $new_stats['BonusPoints'] < $stats['BonusPoints'])) {
            $this->db->rollback();
            return false;
        }
        $this->addPurchaseHistory($item['ID'], $user_id, $price);
        $this->db->commit();
        $this->cache->delete_value('user_info_heavy_' . $user_id);
        return true;
    }

    public function purchaseToken($user_id, $label) {
        if (!array_key_exists($label, $this->items)) {
            return false;
        }
        $item  = $this->items[$label];
        $amount = $item['Amount'];
        $price  = $item['Price'];
        $stats  = \Users::user_stats($user_id, true);
        if ($stats['BonusPoints'] < $price) {
            return false;
        }
        $this->db->begin_transaction();
        $this->db->prepared_query(
            'UPDATE users_main SET FLTokens = FLTokens + ?, BonusPoints = BonusPoints - ? WHERE BonusPoints >= ? AND ID = ?',
            $amount,
            $price,
            $price,
            $user_id
        );
        if ($this->db->affected_rows() != 1) {
            $this->db->rollback();
            return false;
        }
        $new_stats = \Users::user_stats($user_id, true);
        if (!($new_stats['BonusPoints'] >= 0 && $new_stats['BonusPoints'] < $stats['BonusPoints'])) {
            $this->db->rollback();
            return false;
        }
        $this->addPurchaseHistory($item['ID'], $user_id, $price);
        $this->db->commit();
        $this->cache->delete_value('user_info_heavy_' . $user_id);
        return true;
    }

    public function purchaseTokenOther($fromID, $toID, $label, &$logged_user) {
        if ($fromID === $toID) {
            return 0;
        }
        if (!array_key_exists($label, $this->items)) {
            return 0;
        }
        $item  = $this->items[$label];
        $amount = $item['Amount'];
        $price  = $item['Price'];
        if (!isset($price) and !($price > 0)) {
            return 0;
        }
        $From = \Users::user_info($fromID);
        $To = \Users::user_info($toID);
        if ($From['Enabled'] != 1 || $To['Enabled'] != 1) {
            return 0;
        }
        // get the bonus points of the giver from the database
        // verify they could be legally spent, and then update the receiver
        $stats = \Users::user_stats($fromID, true);
        if ($stats['BonusPoints'] < $price) {
            return 0;
        }
        $this->db->begin_transaction();
        $this->db->prepared_query('UPDATE users_main SET BonusPoints = BonusPoints - ? WHERE BonusPoints >= 0 AND ID = ?', $price, $fromID);
        if ($this->db->affected_rows() != 1) {
            $this->db->rollback();
            return 0;
        }
        $new_stats = \Users::user_stats($fromID, true);
        if (!($new_stats['BonusPoints'] >= 0 && $new_stats['BonusPoints'] < $stats['BonusPoints'])) {
            $this->db->rollback();
            return 0;
        }
        $this->db->prepared_query("UPDATE users_main SET FLTokens = FLTokens + ? WHERE ID=?", $amount, $toID);
        if ($this->db->affected_rows() != 1) {
            $this->db->rollback();
            return 0;
        }
        $this->addPurchaseHistory($item['ID'], $fromID, $price, $toID);
        $this->db->commit();

        $this->cache->delete_value("user_info_heavy_{$fromID}");
        $this->cache->delete_value("user_info_heavy_{$toID}");
        // the calling code may not know this has been invalidated, so we cheat
        $logged_user['BonusPoints'] = $new_stats['BonusPoints'];
        $this->sendPmToOther($From['Username'], $toID, $amount);

        return $amount;
    }

    public function purchaseRecommendMovie($user_id, $label, $effective_class) {
        if (!array_key_exists($label, $this->items)) {
            return false;
        }
        $item = $this->items[$label];
        $price = $this->getEffectivePrice($label, $effective_class);
        $stats = \Users::user_stats($user_id, true);
        if ($stats['BonusPoints'] < $price) {
            return false;
        }
        $this->db->begin_transaction();
        if ($price > 0) {
            /* if the price is 0, nothing changes so avoid hitting the db */
            $this->db->prepared_query(
                'UPDATE users_main SET BonusPoints = BonusPoints - ? WHERE BonusPoints >= ? AND ID = ?',
                $price,
                $price,
                $user_id
            );
            if ($this->db->affected_rows() != 1) {
                $this->db->rollback();
                return false;
            }
            // Sanity check
            $new_stats = \Users::user_stats($user_id, true);
            if (!($new_stats['BonusPoints'] >= 0 && $new_stats['BonusPoints'] < $stats['BonusPoints'])) {
                $this->db->rollback();
                return false;
            }
        }

        $this->addPurchaseHistory($item['ID'], $user_id, $price);
        $this->db->commit();
        $this->cache->delete_value('user_info_heavy_' . $user_id);
        return true;
    }

    public function sendPmToOther($from, $toID, $amount) {
        $to = \Users::user_info($toID);
        \Misc::send_pm_with_tpl($toID, 'token_other', ['To' => $to['Username'], 'From' => $from, 'Amount' => $amount]);
    }

    private function addPurchaseHistory($item_id, $user_id, $price, $other_user_id = null) {
        $this->cache->delete_value(self::CACHE_SUMMARY . $user_id);
        $this->cache->delete_value(self::CACHE_HISTORY . $user_id . ".1");
        $this->db->prepared_query(
            'INSERT INTO bonus_history (ItemID, UserID, price, OtherUserID) VALUES (?, ?, ?, ?)',
            $item_id,
            $user_id,
            $price,
            $other_user_id
        );
        return $this->db->affected_rows();
    }
}
