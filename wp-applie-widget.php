<?php
/*
Plugin Name: WP-Applie-Widget
Plugin URI: 
Description: This plugin allows you to display the application ranking of applie.net.
Version: 0.0.0
Author: smeghead
 */
$ranking_types = array(
  array(name =>  'price_down_app_ranking', url =>  '/ranking/price_down_app/'),
  array(name =>  'pv_review_ranking', url =>  '/ranking/pv_review/'),
  array(name =>  'pv_app_ranking', url =>  '/ranking/pv_app/'),
  array(name =>  'recommend_app_ranking', url =>  '/ranking/recommend_app/'),
  array(name =>  'rank_up_app_ranking', url =>  '/ranking/rank_up_app/'),
  array(name =>  'rated_app_ranking', url =>  '/ranking/rated_app/'),
  array(name =>  'kuchikomi_app_ranking', url =>  '/ranking/kuchikomi_app/'),
  array(name =>  'version_up_app_ranking', url =>  '/ranking/version_up_app/'),
  array(name =>  'posted_reviewer_ranking', url =>  '/ranking/posted_reviewer/'),
  array(name =>  'rated_reviewer_ranking', url =>  '/ranking/rated_reviewer/'),
  array(name =>  'appstore_charged_app_ranking', url =>  '/ranking/appstore_charged_app/'),
  array(name =>  'appstore_free_app_ranking', url =>  '/ranking/appstore_free_app/'),
  array(name =>  'high_price_app_ranking', url =>  '/ranking/high_price_app/'),
  array(name =>  'low_price_app_ranking', url =>  '/ranking/low_price_app/'));

function get_ranking_url($ranking_type) {
  global $ranking_types;
  foreach ($ranking_types as $item) {
    if ($item['name'] == $ranking_type) {
      return 'http://applie.net/' . $item['url'];
    }
  }
  throw new Exception('invalid ranking type.');
}

// wpaw_options_page() displays the page content for the Test Options submenu
function wpaw_options_page() {
  global $ranking_types;
  // Read in existing option value from database
  $widget_title = get_option('wpaw_widget_title');
  $ranking_type = get_option('wpaw_ranking_type' );

  // See if the user has posted us some information
  // If they did, this hidden field will be set to 'Y'
  if( $_POST['is_submit'] == 'Y' ) {
    $widget_title = $_POST['wpaw_widget_title'];
    $ranking_type = $_POST['wpaw_ranking_type'];
    update_option('wpaw_widget_title', $widget_title);
    update_option('wpaw_ranking_type', $ranking_type);
    //clear caches.
    update_option('last_got_date', '');
    update_option('last_got_json', '');
  }
?>

  <input type="hidden" name="is_submit" value="Y">
  <p><?php _e("WP Applie Widget Widget Title", 'mt_trans_domain' ); ?> 
    <input type="text" name="wpaw_widget_title" value="<?php echo $widget_title; ?>" size="40">
  </p>
  <p><?php _e("WP Applie Widget Category:", 'mt_trans_domain' ); ?> 
    <select name="wpaw_ranking_type">
      <?php print_ranking_typs($ranking_types, $ranking_type); ?>
    </select>
  </p>
<?php
}
function print_ranking_typs($ranking_types, $value) {
  foreach ($ranking_types as $type) {
    ?><option value="<?php echo $type['name']; ?>" <?php if ($value == $type['name']) { echo 'selected="selected"';} ?>><?php echo _e($type['name'], 'mt_trans_domain'); ?></option><?php
  }
} 

function get_ranking_items($ranking_type) {
  $last_got_date = get_option('last_got_date'); 
  $last_got_json = get_option('last_got_json');
  if (!$last_got_date || !$last_got_json || $last_got_date + 60 * 60 * 3 < time()) { //cache is older than 3 hous?
    $json_contents = file_get_contents(get_ranking_url($ranking_type));
    update_option('last_got_date', time());
    update_option('last_got_json', $json_contents);
  } else {
    $json_contents = $last_got_json;
  }
  $contents = json_decode($json_contents);
  return $contents;
}
function wpaw_show_widget($args) {
  $widget_title = get_option('wpaw_widget_title'); 
  $ranking_type = get_option('wpaw_ranking_type');

  if (!$widget_title) {
    $widget_title = 'Applie.net Ranking';
    update_option('wpaw_widget_title', $widget_title);
  }
  if (!$ranking_type) {
    $ranking_type = 'rank_up_app_ranking';
    update_option('wpaw_ranking_type', $ranking_type);
  }

  echo $args['before_title'] . $widget_title . $args['after_title'] . $args['before_widget'];

  $contents = get_ranking_items($ranking_type);
?>
  <style>
    ul.wpaw-widget li { width: 100%; }
    ul.wpaw-widget li img { margin-right: 5px; }
  </style>
 <ul class="wpaw-widget">
<?php
  foreach ($contents->context->ranking as $item) {
    $ret = preg_match("@src='([^']*)'@", $item->image_url, $matches);
    $image_url = $matches[1];
    if (function_exists('mb_strimwidth')) { // if mb_strimwidth exists, this will cut too long link string.
      $link_str = mb_strimwidth($item->name, 0, 50, "...", utf8);
    } else {
      $link_str = $item->name;
    }
    ?>
      <li>
        <a href="http://applie.net<?php echo $item->url; ?>" rel="nofollow" target="_blank">
          <img src="http://applie.net<?php echo $image_url; ?>" width="32" height="32" align="left" />
          <?php echo $link_str; ?>
          <br clear="all"/>
        </a>
      </li>
    <?php
  }
  echo '</ul>';
  echo $args['after_widget'];
}

function wpaw_init_widget() {
  register_sidebar_widget('WP Applie Widget', 'wpaw_show_widget');
  register_widget_control('WP Applie Widget', 'wpaw_options_page', 250, 200 );
}
add_action("plugins_loaded", "wpaw_init_widget");

?>
