<?php
/*
Plugin Name: WP-Applie-Widget
Plugin URI: 
Description: This plugin allows you to display the applie.net ranking.
Version: 0.0.0
Author: smeghead
 */

// Hook for adding admin menus
add_action('admin_menu', 'wpaw_add_pages');

// action function for above hook
function wpaw_add_pages() {
  add_options_page('WP Applie Widget', 'WP Applie Widget', 'administrator', __FILE__, 'wpaw_options_page');
}

// wpaw_options_page() displays the page content for the Test Options submenu
function wpaw_options_page() {
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

    // Put an options updated message on the screen
    ?><div class="updated"><p><strong><?php _e('Options saved . ', 'mt_trans_domain' ); ?></strong></p></div><?php
  }

  // Now display the options editing screen
  echo '<div class="wrap">';

  // header
  echo '<h2>' . __('WP Applie Widget Plugin Options', 'mt_trans_domain') . '</h2>';
?>

<form name="form1" method="post" action="">
  <input type="hidden" name="is_submit" value="Y">

  <p><?php _e("WP Applie Widget Widget Title", 'mt_trans_domain' ); ?> 
    <input type="text" name="wpaw_widget_title" value="<?php echo $widget_title; ?>" size="50">
  </p><hr />

  <p><?php _e("WP Applie Widget Category:", 'mt_trans_domain' ); ?> 
    <select name="wpaw_ranking_type">
      <option value="rank_up_app_ranking" <?php if ($ranking_type == 'rank_up_app_ranking') { echo 'selected="selected"';} ?>><?php echo _e('Ranking Up', 'mt_trans_domain'); ?></option>
    </select>
  </p>
  <hr />

  <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
  </p>
  <hr />
</form>
<?php
}

function wpaw_show_widget($args) {
  extract($args);

  $widget_title = get_option('wpaw_widget_title'); 
  $ranking_type = get_option('wpaw_ranking_type');

  if ($widget_title == '') {
    $widget_title = 'Applie.net Ranking';
  }
  if ($ranking_type == '') {
    $ranking_type = 'topstories';
  }

  echo "$before_title$widget_title$after_title$before_widget";

  $json_contents = file_get_contents('http://applie.net/ranking/rank_up_app/');
  $contents = json_decode($json_contents);
  echo '<ul>';
  foreach ($contents->context->ranking as $item) {
    $ret = preg_match("@src='([^']*)'@", $item->image_url, $matches);
    $image_url = $matches[1];
    ?>
      <li>
        <a href="http://applie.net<?php echo $item->url; ?>" rel="nofollow" target="_blank">
          <img src="http://applie.net<?php echo $image_url; ?>" width="32" />
          <?php echo $item->name; ?>
        </a>
      </li>
    <?php
  }
  echo '</ul>';
  echo $after_widget;
}

function wpaw_init_widget() {
  register_sidebar_widget("WP Applie Widget", "wpaw_show_widget");
}

add_action("plugins_loaded", "wpaw_init_widget");

?>
