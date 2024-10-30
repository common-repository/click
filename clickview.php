<?php
// This file will be inserted with other WordPress files, so some functions and variables can just be used.
global $wpdb; // Allow vaiable from other files
global $post; // Allow variable from other files
if(is_home()){ // The homepage steals IDs, so lets stop it
	$table_name = $wpdb->prefix . "clickhome"; // is wp_clickhome
}
else {
	$table_name = $wpdb->prefix . "click" . $post->ID; // Example: wp_click6
}
global $userdata;
get_currentuserinfo();

if ( !defined('WP_PLUGIN_URL') )
	define( 'WP_PLUGIN_URL', get_option('siteurl') . '/wp-content/plugins');
if ( !defined('WP_PLUGIN_DIR') )
	define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins' );

if(($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) && ($_GET["track"] == get_option('click_password'))) { //You can't have it unless it's there and you have the password
	$loci = $wpdb->get_col("SELECT loci FROM $table_name"); // Pull out the Index locations
	$locx = $wpdb->get_col("SELECT locx FROM $table_name"); // Pull out the X locations
	$locy = $wpdb->get_col("SELECT locy FROM $table_name"); // Pull out the Y locations
	$browser = $wpdb->get_col("SELECT browser FROM $table_name"); // Pull out the browser type
	$timedata = $wpdb->get_col("SELECT time FROM $table_name"); // Pull out the time
	$c = 0; // If you start earlier, you can get done earlier
	while(1){ //Endless loops are fun, so lets add some!
		if($locx[$c]){ // Are you real?
			echo "<span title='" . $loci[$c] . "' class='clickdot " . $browser[$c];
			echo " " . $timedata[$c];
			echo "' style='position:absolute;top:" . $locy[$c] . "%;left:" . $locx[$c] . "%;'>&nbsp;</span>";
			$c++; //Count so we can move on the next item
		} // Stop questioning existence
		else break; // This breaks the endless loop or your grandma's back... I'm unsure at this time. 
	} // The end of endlessness
	// Add some jQuery, so the dots' meaning can be switched.
	echo '
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery(".clickdot").each(function() {
				var index = jQuery(this).attr("title");
				var e_px = jQuery(this).css("left");
				var e_py = jQuery(this).css("top");
				var e_w = jQuery("a").eq(index).width();
				var e_h = jQuery("a").eq(index).height();
				var e_ox = jQuery("a").eq(index).offset().left;
				var e_oy = jQuery("a").eq(index).offset().top;
				var x = eval(e_ox + (e_w * (e_px.replace(/%/, "") / 100)));
				var y = eval(e_oy + (e_h * (e_py.replace(/%/, "") / 100)));
				jQuery(this).css("left",x);
				jQuery(this).css("top",y);
			});
			jQuery("#click_hour").click(function() {
				hidescale();
				jQuery("#selected_click").attr({href : "' . WP_PLUGIN_URL . '/click/click_hour.css"});
				jQuery("#hour_scale").css("display","");
			});
			jQuery("#click_day").click(function() {
				hidescale();
				jQuery("#selected_click").attr({href : "' . WP_PLUGIN_URL . '/click/click_day.css"});
				jQuery("#day_scale").css("display","");
			});
			jQuery("#click_dow").click(function() {
				hidescale();
				jQuery("#selected_click").attr({href : "' . WP_PLUGIN_URL . '/click/click_dow.css"});
				jQuery("#dow_scale").css("display","");
			});
			jQuery("#click_browser").click(function() {
				hidescale();
				jQuery("#selected_click").attr({href : "' . WP_PLUGIN_URL . '/click/click_browser.css"});
				jQuery("#browser_scale").css("display","");
			});
			function hidescale() {
				jQuery("#hour_scale").css("display","none");
				jQuery("#day_scale").css("display","none");
				jQuery("#dow_scale").css("display","none");
				jQuery("#browser_scale").css("display","none");
			}
			hidescale();
			jQuery("#hour_scale").css("display","");
		});
		</script>
		<div style="position:fixed;bottom:0px;right:0px;" id="click_controlpanel">
		<a id="click_hour" href="#">Hour</a> | 
		<a id="click_day" href="#">Day</a> | 
		<a id="click_dow" href="#">Day of Week</a> | 
		<a id="click_browser" href="#">Browser</a>
		<div id="hour_scale">1';
	for($i=1;$i<=24;$i++){ // If I wrote each of these I would go crazy!
		if($i<10) echo '<span title="0' . $i . '" class="clickscale hour0' . $i . '">&nbsp;</span>';
		else echo '<span title="' . $i . '" class="clickscale hour' . $i . '">&nbsp;</span>';
	}
	echo '24</div>
		<div id="day_scale">1';
	for($i=1;$i<=31;$i++){ //If I wrote each of these the file size would double..
		if($i<10) echo '<span title="0' . $i . '" class="clickscale day0' . $i . '">&nbsp;</span>';
		else echo '<span title="' . $i . '" class="clickscale day' . $i . '">&nbsp;</span>';
	}		
	echo '31</div>
		<div id="dow_scale">
		<span class="clickscale mon">M</span>
		<span class="clickscale tue">Tu</span>
		<span class="clickscale wed">W</span>
		<span class="clickscale thu">Th</span>
		<span class="clickscale fri">F</span>
		<span class="clickscale sat">Sa</span>
		<span style="color:white;" class="clickscale sun">Su</span>
		</div>
		<div id="browser_scale">
		<span class="clickscale mozilla">FF</span>
		<span class="clickscale opera">O</span>
		<span class="clickscale safari">S</span>
		<span class="clickscale msie">IE</span>
		<span class="clickscale non">None</span>
		</div>
		</div>
	';
} // Don't fall off the table!
?>
