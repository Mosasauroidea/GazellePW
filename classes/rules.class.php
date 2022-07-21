<?php
class Rules {
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
}
