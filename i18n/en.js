export default {
  like: 'Like',
  copied: 'Copied',
  view: 'View',
  clear: 'Clear',
  hide: 'Hide',
  composer: 'Composer',
  producer: 'Producer',
  confirm_purchase: 'Are you sure you want to purchase {0}?',
  confirm_username: 'Enter username to give tokens to:',
  in_this_release_type: 'in this release type.',
  on_this_page: 'on this page.',
  collapse_this_group:
    'Collapse this group. Hold [Command] <em>(Mac)</em> or [Ctrl] <em>(PC)</em> while clicking to collapse all groups {0}',
  expand_this_group:
    'Expand this group. Hold [Command] <em>(Mac)</em> or [Ctrl] <em>(PC)</em> while clicking to expand all groups {0}',
  are_you_sure_cannot_undone:
    'Are you sure you wish to do this? This cannot be undone!',
  request_has_been_unresolved:
    'The request has been un-resolved. Please refresh your browser to see it.',
  pm_user_on_edit:
    '<span id="pmbox{0}"><label>PM user on edit? <input type="checkbox" name="pm" value="1" /></label></span>',
  are_you_sure_you_want_to_cancel: 'Are you sure you want to cancel?',
  are_you_sure_you_wish_to_delete_this_post:
    'Are you sure you wish to delete this post?',
  add_torrent_description: 'Add Torrent Description',
  a_main_artist_is_required: 'A "Main" artist is required',
  this_field_is_required: 'This field is required.',
  please_fix_this_field: 'Please fix this field.',
  please_enter_a_valid_email_address: 'Please enter a valid email address.',
  please_enter_a_valid_url: 'Please enter a valid URL.',
  please_enter_a_valid_date: 'Please enter a valid date.',
  please_enter_a_valid_date_iso: 'Please enter a valid date (ISO).',
  please_enter_a_valid_date_number: 'Please enter a valid number.',
  please_enter_only_digits: 'Please enter only digits.',
  please_enter_a_valid_credit_card_number:
    'Please enter a valid credit card number.',
  please_enter_the_same_value_again: 'Please enter the same value again.',
  please_enter_no_more_than_n_characters:
    'Please enter no more than {0} characters.',
  please_enter_at_least_n_characters: 'Please enter at least {0} characters.',
  please_enter_a_value_between_x_and_y_characters_long:
    'Please enter a value between {0} and {1} characters long.',
  please_enter_a_value_between_x_and_y:
    'Please enter a value between {0} and {1}.',
  please_enter_a_value_less_than_or_equal_to_n:
    'Please enter a value less than or equal to {0}.',
  please_enter_a_value_greater_than_or_equal_to_n:
    'Please enter a value greater than or equal to {0}.',
  check_all: 'Check all',
  uncheck_all: 'Uncheck all',
  hide: 'Hide',
  show: 'Show',

  screenshot_comparison: {
    help: `Press ? to read help`,
    help_title: 'Screenshot Comparison Help',
    help_content: `
	        - Move your mouse over the images from the left to the right to toggle between the comparison sides. <br/>
          - Each image is divided into sections: for a two-sided comparison the toggling happens when the cursor moves from the left side of an image to its right side. <br/>
          - You can also use the arrow keys to navigate: up/k and down/j jumps between rows; left/h and right/l toggles comparison sides. <br/>
          - Comparison sides can also toggled by the number keys (1-9). <br/>
          - Press \`Escape\` key to close the comparison.<br/>
          - Press \`a\` key to open/close the Pixel Compare tool.<br/>
          - Press \`s\` key to open/close the Solar Curve tool.<br/>
        `,
    loading: `Loading...`,
    pixel_compare: 'Pixel Compare',
    solar_curve: 'Solar Curve',
    gpw_helper_not_installed:
      'Please install <a href="https://greasyfork.org/scripts/445653-gpw-helper/code/GPW-Helper.user.js" target="_blank">GPW Helper</a> and reload the page',
  },

  // Upload Page
  upload: {
    torrent_file_required: 'Please select a torrent file!',
    imdb_link_required: 'IMDb link or ID with a correct format is required!',
    releasetype_required:
      'Please select the type according to the definitions in the <a href="/rules.php?p=upload">upload rules</a>.',
    movie_title_required: 'Please specify the title!',
    year_required: 'Please specify the year of the original release!',
    poster_required:
      "Please specify the poster!	If you can't find any then take a picture from the movie and write the movie's title on it.",
    movie_desc_required: 'Please specify the movie description',
    source_required:
      'Please select the source. If you are unsure then select Other and enter Unknown.',
    tag_required: 'Please specify at least one genre!',
    subtitles_required:
      'Please specify the subtitles or select "No Subtitles" if there are not any!',
    subtitles_with_mediainfo:
      'MediaInfo provides subtitles, please specify the subtitles.',
    codec_required: 'Please select a codec.',
    resolution_required: 'Please select a resolution.',
    container_required: 'Please select a container.',
    processing_required: 'Please select a processing.',
    remaster_required: 'Please clarify the specific content of the extras.',
    mediainfo_required: `MediaInfo (or BDInfo) log is required.<br/>
      For DVD images make sure to include the MediaInfo log of the largest VOB and the preceding IFO that contains the total duration.`,
    mediainfo_invalid_chars: `There are some mistakes in the MediaInfo/BDInfo you submitted, please scan by yourself with mediainfo app, do not paste from other places. Feel free to feedback if it doesn't work.`,
    mediainfo_complete_name_required: `Must contains Complete name (or Disc Title/Label).`,
    mediainfo_table_space: `BDInfo table space cause the problem, please use tab (\\t) instead.`,
    desc_img_3_png: `At least three PNG screenshots are required, see <a href="wiki.php?action=article&id=51" target="_blank">help</a>.`,
    desc_img_hosts: `Please use image hosts that are recommended by Upload Rule <a href="rules.php?p=upload#r2.2.1" target="_blank">2.2.1</a>.`,
  },
  invalid_imdb_link_note:
    'Please enter a valid IMDb link like "tt1234567" or "https://www.imdb.com/title/tt1234567".',
  torrent_group_exists_note:
    "The movie already <a href='/torrents.php?id={0}'>exists</a> on the site. Please make sure that you're not uploading a duplicate and use the [Add format] link to upload a new format for the movie.",
  request_torrent_group_exists_note:
    "The movie already <a href='/torrents.php?id={0}'>exists</a> on the site. Please make sure the format you request does not exist and use the [Request format] link to request your expect format for the movie.",
  imdb_unknown_error: 'Unknown error, please contact staff.',

  bbcode: {
    edit: 'Edit',
    preview: 'Preview',
    loading: 'Loading...',
  },

  // Subtitles Page
  warning_subtitle_file: 'Please select subtitle file!',
  warning_subtitle_format: 'Please select subtitle format!',
  warning_subtitle_language: 'Please select subtitle langauge!',

  torrent_table: {
    collapse_edition:
      'Collapse this resolution group. Hold [Command] <em>(Mac)</em> or [Ctrl] <em>(PC)</em> while clicking to collapse all resolutions in this torrent group.',
    expand_edition:
      'Expand this resolution group. Hold [Command] <em>(Mac)</em> or [Ctrl] <em>(PC)</em> while clicking to expand all resolutions in this torrent group.',
  },

  staffpm: {
    error: 'Something went wrong.',
    response_created: 'Response successfully created',
    response_edited: 'Response successfully edited.',
    response_deleted: 'Response successfully deleted.',
    assign_assigned: 'Conversation successfully assigned.',
  },
}
