<?php
/*
Plugin Name: Click
Plugin URI: http://ledgerpad.ath.cx/click
Description: Visual click tracking plugin.
Version: 1.0
Author: Dan Cole
Author URI: http://ledgerpad.ath.cx
*/

/*  Copyright 2008 Dan Cole  (email : dcole07@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
global $wpdb;

if ( !defined('WP_PLUGIN_URL') )
	define( 'WP_PLUGIN_URL', get_option('siteurl') . '/wp-content/plugins');
if ( !defined('WP_PLUGIN_DIR') )
	define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins' );

add_action('wp_footer', 'clickdancole'); //Attach the tracking javascript
function clickdancole() { //Lets send data, even if its not needed. Then users will not know what pages are really being tracked. 
	echo "<p style='display:none;' id='dancole'>";
	if(is_home()){echo "home";}
	elseif(is_page || is_single()){echo the_ID();}
	echo "</p>";
	echo "<script type='text/javascript' >
jQuery(document).ready(function(){
	jQuery('a').click(function(e){
		var index = jQuery('a', document.body).index(this); //Get the index of this link in terms of all links
		var xloc = ((e.pageX - this.offsetLeft) / jQuery(this).width())*100; //Find the location within the element
		var yloc = ((e.pageY - this.offsetTop) / jQuery(this).height())*100;		
		if (jQuery.browser.mozilla) {var browser = 'mozilla';}
		else if (jQuery.browser.opera) {var browser = 'opera';}
		else if (jQuery.browser.safari) {var browser = 'safari';}
		else if (jQuery.browser.msie) {var browser = 'msie';}
		else {var browser = 'non';}
		var id = jQuery('p#dancole').text();
		var screenW = jQuery(document).width();
		jQuery.post('" . WP_PLUGIN_URL . "/click/clicksave.php', { e: index, x: xloc, y: yloc, b: browser, i: id, w: screenW} );
	}); 
});
</script>";
}

add_action('template_redirect', 'click_addjquery');
function click_addjquery () {
	wp_enqueue_script('jquery');
}

add_action('wp_footer', 'clicktrack'); //You add javascipt to the end of a page to speed up loading time
add_action('wp_head', 'clickcss'); //You add CSS to the header

function clicktrack() { //This linked to file will add the dots, then move them to the right locations. 
	require_once(WP_PLUGIN_DIR . '/click/clickview.php');
}
function clickcss() { //This allows us to change the color of the dots
	echo "
	<link rel='stylesheet' type='text/css' media='screen' href='" . WP_PLUGIN_URL. "/click/click.css' />
	<link id='selected_click' rel='stylesheet' type='text/css' media='screen' href='" . WP_PLUGIN_URL . "/click/click_hour.css' />
	<link rel='alternate stylesheet' type='text/css' media='screen' href='" . WP_PLUGIN_URL . "/click/click_day.css' />
	<link rel='alternate stylesheet' type='text/css' media='screen' href='" . WP_PLUGIN_URL . "/click/click_dow.css' />
	<link rel='alternate stylesheet' type='text/css' media='screen' href='" . WP_PLUGIN_URL . "/click/click_browser.css' />
	";
}

add_filter('manage_posts_columns', 'click_dan_cole'); //Want to track posts through the Manage Posts section
add_filter('manage_pages_columns', 'click_dan_cole'); //Want to track pages through the Manage Pages section
function click_dan_cole($defaults) {
    $defaults['click'] = __('Track Visitors'); //The title of the column will be this
    return $defaults;
}
add_action('manage_posts_custom_column', 'click_dan_cole_tracked', 10, 2);
add_action('manage_pages_custom_column', 'click_dan_cole_tracked', 10, 2);
function click_dan_cole_tracked($column_name, $post_id) {
	if( $column_name == 'click' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "click" . $post_id;
		$trackingitem = explode("&",get_option("click_tracking")); //List of pages & posts that are being tracked
		if(in_array($post_id, $trackingitem)) {
			echo "<a href='" . get_option('siteurl') . "/wp-admin/options-general.php?page=click-tracking&track=" . $post_id . "'>Stop</a>";
		}
		else {
			echo "<a href='" . get_option('siteurl') . "/wp-admin/options-general.php?page=click-tracking&track=" . $post_id . "'>Start</a>";
		}
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			echo " | <a href='" . get_permalink($post_id) . "?track=" . get_option('click_password') . "'>View</a>";
			echo " | <a href='" . get_option('siteurl') . "/wp-admin/options-general.php?page=click-tracking&deleteclick=" . $post_id . "'>Delete</a>";
		}	}
}

add_option('click_count_level', '10'); //By default everyone is tracked, even admin
add_option('click_password', 'Dan Cole'); //By default my name is the password
add_option('click_tracking', 'home'); //By default the home page is being tracked
add_action('admin_menu', 'click_config_page'); //This adds a sub page to the Settings menu
function click_config_page() {
	add_options_page('Click Tracking', 'Click Tracking', 8, 'click-tracking', 'click_page');
}

//This function will be used below
function addremove_click_table($track_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "click" . $track_id; //Example: wp_click6
	$trackingitem = explode("&",get_option("click_tracking"));
	if(in_array($track_id, $trackingitem)) {
		$trackinglist = null;
		foreach ($trackingitem as $item) {
			if($item != $track_id) {
				if($trackinglist == null){
					$trackinglist = $item;
				}
				else {
					$trackinglist .= "&" . $item;
				}
			}
		}
		update_option("click_tracking", $trackinglist); // Removed one ID from the list
		echo "<h3>No Longer Tracking</h3>";
	}
	else {
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
			loci mediumint(5) DEFAULT '0' NOT NULL,
			locx mediumint(5) DEFAULT '0' NOT NULL,
			locy mediumint(5) DEFAULT '0' NOT NULL,
			browser tinytext NOT NULL,
			screen mediumint(5) DEFAULT '0' NOT NULL,
			time tinytext NOT NULL
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //Get dbDelta function to insert sql data
			dbDelta($sql); //Insert sql data through special WordPress function
		}
		$nowtracking = get_option("click_tracking");
		update_option("click_tracking", $nowtracking . "&" . $track_id); //Track new ID (page or post)
		echo "<h3>Now Tracking</h3>";
	}
}
function delete_click_table($track_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "click" . $track_id; //Example: wp_click6
	$wpdb->query( "DROP TABLE IF EXISTS " . $table_name );

	$trackingitem = explode("&",get_option("click_tracking"));
	if(in_array($track_id, $trackingitem)) {
		$trackinglist = null;
		foreach ($trackingitem as $item) {
			if($item != $track_id) {
				if($trackinglist == null){
					$trackinglist = $item;
				}
				else {
					$trackinglist .= "&" . $item;
				}
			}
		}
		update_option("click_tracking", $trackinglist); // Removed one ID from the list
		echo "<h3>No Longer Tracking</h3>";
	}
	echo "<h3>Data Deleted</h3>";
}
function click_page() {
if($_GET["track"]) { //Don't add or remove an ID unless told to do so
	addremove_click_table($_GET["track"]);
}
if($_GET["deleteclick"]) { //Don't add or remove an ID unless told to do so
	delete_click_table($_GET["deleteclick"]);
}

?>
<div class="wrap">
<h2>Click Tracking</h2>
<form method='post' action='options.php'>
<?php wp_nonce_field('update-options'); ?>
<input type="hidden" name="action" value="update" />
The Password to view the clicks (add ?track= and your password to view):<br />
<input type="text" name="click_password" value="<?php echo get_option('click_password'); ?>" /><br />
<br />Note: The Password is needed because being logged in can misslocates some of the dots.<br />
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
</p>
</form>
</div>
<?php } // end function click_page 
?>
