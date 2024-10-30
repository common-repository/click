<?php
$index = $_POST["e"]; // The element index
$locx = $_POST["x"]; // The X location of mouse
$locy = $_POST["y"]; // The Y location of mouse
$browser = $_POST["b"]; // The browser, full spelling
$id = $_POST["i"]; // The WordPress Post ID
$screen = $_POST["w"]; // Screen Width
// H=Hour (double digit)
// D=Day of Week (three letters)
// d=day of month (double digit)
$clicktime = "hour" . date("H") . " " . strtolower(date("D")) . " day" . date("d"); //Example: hour06 Wed day06

// The following screen data is not used, but will be in the future... so lets be ready
if($screen <= 800){$screen = 8;}
elseif(($screen > 800) && ($screen <= 1200)){$screen = 10;}
elseif(($screen > 1200) && ($screen <= 1500)){$screen = 14;}
elseif(($screen > 1500) && ($screen <= 1700)){$screen = 16;}
else{$screen = 18;}

require_once('../../../wp-blog-header.php'); //Is there a better way to call this?
global $wpdb; //Make sure we can get the WordPress prefix

$table_name = $wpdb->prefix . "click" . $id; //Example: wp_click6
$trackingitem = explode("&",get_option("click_tracking")); //List of trackable pages & posts

$ul = 0; // Delay feature of not tracking logged in users until next release
if($userdata->user_level >= get_option('click_access_level')){print "check3";}
if(($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) && (in_array($id, $trackingitem)) && ($ul <= get_option('click_access_level'))) { //Does the table exist? Does it want to be tracked? Do you want to track the user?
	$insert = "INSERT INTO " . $table_name .
		" (loci, locx, locy, browser, screen, time) " .
		"VALUES ('" . $wpdb->escape($index) . "', '" . $wpdb->escape($locx) . "', '" . $wpdb->escape($locy) . "', '" . $wpdb->escape($browser) . "', '" . $wpdb->escape($screen) . "', '" . $clicktime . "')";
	$results = $wpdb->query( $insert ); // Now add the click data we got at the top of the file
} //End the IF for table exisiting
echo "done";
?>
