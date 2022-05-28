<?php
class Rules {

  /**
   * Displays the site's "Golden Rules".
   *
   */
  public static function display_golden_rules() {
    $golden_rules = array(
      [
        'n' => "1.1",
        'short' => Lang::get('rules', 'short_11'),
        'long' => Lang::get('rules', 'long_11')
      ],
      [
        'n' => "1.2",
        'short' => Lang::get('rules', 'short_12'),
        'long' => Lang::get('rules', 'long_12')
      ],
      [
        'n' => "1.3",
        'short' => Lang::get('rules', 'short_13'),
        'long' => Lang::get('rules', 'long_13')
      ],
      [
        'n' => "1.4",
        'short' => Lang::get('rules', 'short_14'),
        'long' => Lang::get('rules', 'long_14')
      ],
      [
        'n' => "2.1",
        'short' => Lang::get('rules', 'short_21'),
        'long' => Lang::get('rules', 'long_21')
      ],
      [
        'n' => "2.2",
        'short' => Lang::get('rules', 'short_22'),
        'long' => Lang::get('rules', 'long_22')
      ],
      [
        'n' => "2.3",
        'short' => Lang::get('rules', 'short_23'),
        'long' => Lang::get('rules', 'long_23')
      ],
      [
        'n' => "2.4",
        'short' => Lang::get('rules', 'short_24'),
        'long' => Lang::get('rules', 'long_24')
      ],
      [
        'n' => "3.1",
        'short' => Lang::get('rules', 'short_31'),
        'long' => Lang::get('rules', 'long_31')
      ],
      [
        'n' => "3.2",
        'short' => Lang::get('rules', 'short_32'),
        'long' => Lang::get('rules', 'long_32')
      ],
      [
        'n' => "3.3",
        'short' => Lang::get('rules', 'short_33'),
        'long' => Lang::get('rules', 'long_33')
      ],
      [
        'n' => "3.4",
        'short' => Lang::get('rules', 'short_34'),
        'long' => Lang::get('rules', 'long_34')
      ],
      [
        'n' => "3.5",
        'short' => Lang::get('rules', 'short_35'),
        'long' => Lang::get('rules', 'long_35')
      ],
      [
        'n' => "4.1",
        'short' => Lang::get('rules', 'short_41'),
        'long' => Lang::get('rules', 'long_41')
      ],
      [
        'n' => "4.2",
        'short' => Lang::get('rules', 'short_42'),
        'long' => Lang::get('rules', 'long_42')
      ],
      [
        'n' => "4.3",
        'short' => Lang::get('rules', 'short_43'),
        'long' => Lang::get('rules', 'long_43')
      ],
      [
        'n' => "4.4",
        'short' => Lang::get('rules', 'short_44'),
        'long' => Lang::get('rules', 'long_44')
      ],
      [
        'n' => "4.5",
        'short' => Lang::get('rules', 'short_45'),
        'long' => Lang::get('rules', 'long_45')
      ],
      [
        'n' => "4.6",
        'short' => Lang::get('rules', 'short_46'),
        'long' => Lang::get('rules', 'long_46')
      ],
      [
        'n' => "4.7",
        'short' => Lang::get('rules', 'short_47'),
        'long' => Lang::get('rules', 'long_47')
      ],
      [
        'n' => "4.8",
        'short' => Lang::get('rules', 'short_48'),
        'long' => Lang::get('rules', 'long_48')
      ],
      [
        'n' => "5.1",
        'short' => Lang::get('rules', 'short_51'),
        'long' => Lang::get('rules', 'long_51')
      ],
      [
        'n' => "5.2",
        'short' => Lang::get('rules', 'short_52'),
        'long' => Lang::get('rules', 'long_52')
      ],
      [
        'n' => "5.3",
        'short' => Lang::get('rules', 'short_53'),
        'long' => Lang::get('rules', 'long_53')
      ],
      [
        'n' => "6.1",
        'short' => Lang::get('rules', 'short_61'),
        'long' => Lang::get('rules', 'long_61')
      ],
      [
        'n' => "6.2",
        'short' => Lang::get('rules', 'short_62'),
        'long' => Lang::get('rules', 'long_62')
      ],
      [
        'n' => "7.0",
        'short' => Lang::get('rules', 'short_70'),
        'long' => Lang::get('rules', 'long_70')
      ],
      [
        'n' => "7.1",
        'short' => Lang::get('rules', 'short_71'),
        'long' => Lang::get('rules', 'long_71')
      ]
    );
    echo "<ul class=\"rules golden_rules\">\n";
    foreach ($golden_rules as $gr) {
      $r_link = "gr${gr['n']}";
      echo    "<li id=\"${r_link}\">" .
        "<a href=\"#${r_link}\" class=\"rule_link\">${gr['n']}.</a>" .
        '<div class="rule_wrap">' .
        '<div class="rule_short">' .
        $gr['short'] .
        '</div>' .
        '<div class="rule_long">' .
        $gr['long'] .
        '</div>' .
        '</div>' .
        "</li>\n";
    }
    echo "</ul>\n";
  }

  /**
   * Displays the site's rules for tags.
   *
   * @param boolean $OnUpload - whether it's being displayed on a torrent upload form
   */
  public static function display_site_tag_rules($OnUpload = false) {
?>
    <ul>
      <?= Lang::get('rules', "tags_summary" . ($OnUpload ? "_onupload" : "")) ?> </ul>
  <?
  }

  /**
   * Displays the site's rules for the forum
   *
   */
  public static function display_forum_rules() {
  ?>
    <ol>
      <?= Lang::get('rules', 'chat_forums_rules') ?>
    </ol>
  <?
  }

  /**
   * Displays the site's rules for conversing on its IRC network
   *
   */
  public static function display_irc_chat_rules() {
  ?>
    <ol>
      <?= Lang::get('rules', 'chat_forums_irc') ?>
    </ol>
<?
  }
}
