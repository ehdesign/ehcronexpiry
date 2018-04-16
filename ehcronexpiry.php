<?php
/*
Plugin Name: EH Cron Expiry plugin
Plugin URI:   http://designedbyeh.com/
Author URI:   http://designedbyeh.com/
Description: A simple plugin to run daily and find expiration dates
Author: Eric Hall - EH Design & Consulting
Version: 0.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

register_activation_hook( __FILE__, 'activateFunction' );
register_deactivation_hook( __FILE__, 'deactivateFunction' );

add_action('admin_menu', 'ehExpiry_setup_menu');

function ehExpiry_setup_menu(){
	add_menu_page( 'Cron Expiry', 'Expire to InfusionSoft ', 'manage_options', 'ehCronExpiry_plugin', 'ehCronExpiry_init', 'dashicons-controls-repeat', 999999);
}

function ehCronExpiry_init(){
	// this is called when the page loads
	echo "<h1>Welcome to the Cron &amp; Find Expiration Page!</h1>";
	//Time of Next Run
	$timestamp = wp_next_scheduled('ehD_event'); 
	$dt = new DateTime("@$timestamp");
	$dt->setTimeZone(new DateTimeZone('America/New_York'));
	echo '<p><strong>The next scheduled run is:</strong><br />';
	echo $dt->format('F j, Y, g:ia');
	echo ' (Eastern Time)';
	echo '</p>';
	echo '<p><strong>The following will be updated at next run:</strong><br />';
	global $wpdb;	
	$results=$wpdb->get_results("SELECT comment_id, user_id, comment_date FROM wp_comments WHERE comment_post_ID = 8942 AND comment_approved LIKE 'complete' AND comment_type LIKE 'sensei_course_status' AND (YEAR(comment_date)=YEAR(CURRENT_DATE())-1) AND (DAYOFMONTH(comment_date)=DAYOFMONTH(CURRENT_DATE())) AND (MONTH(comment_date)=MONTH(CURRENT_DATE())+2) ORDER BY comment_date DESC");
	$count = 0;
	foreach($results as $result) {
		$count++;
		$user_info = get_userdata($result->user_id);		
		$renewdate = date('Y-m-d', strtotime('+2 years', strtotime($result->comment_date)));
		echo "#" . $count . "- " . $user_info->user_email . " passed on " . date('m/d/y',strtotime($result->comment_date)) . " at " . date('h:i a',strtotime($result->comment_date)) . "<br />";
	}
	echo "</p>";
}

function activateFunction() {
	//this is called when the plugin is activated
	//ADD the cron
	wp_schedule_event(time(), 'hourly', 'ehD_event');
}

function deactivateFunction() {
	//this is called when the plugin is deactivated
	//REMOVE the cron
	wp_clear_scheduled_hook('ehD_event');
}

function ehD_schedule_function() {
	//Find All users with expire two months in past - 1 month window
	global $wpdb;	
	$results=$wpdb->get_results("SELECT comment_id, user_id, comment_date FROM wp_comments WHERE comment_post_ID = 8942 AND comment_approved LIKE 'complete' AND comment_type LIKE 'sensei_course_status' AND (comment_date <= DATE_SUB(NOW(),INTERVAL 12 MONTH)) AND (comment_date >= DATE_SUB(NOW(),INTERVAL 13 MONTH)) ORDER BY comment_date DESC");
	
	// Loop Through and Add them to Infusionsoft and add a tag to them
	/*
	foreach($results as $result) {
		require_once('Infusionsoft/infusionsoft.php');
		$user_info = get_userdata($result->user_id);
		$email = $user_info->user_email;
		$groupID = 123;
		//look up InfusionSoft ID from email
		require_once('Infusionsoft/infusionsoft.php');
		$contacts = Infusionsoft_DataService::query(new Infusionsoft_Contact(), array('Email' => $email));
		if(count($contacts) > 0) {
			//user exists - add tag
			$contactId = $contacts[0]->Id;
			Infusionsoft_ContactService::addToGroup($contactID, $groupID);
		} else {
			//user doesn't exist - add user, then add tag
			$contact = new Infusionsoft_Contact();
			$contact->FirstName = $user_info->first_name;
			$contact->LastName = $user_info->last_name;
			$contact->Email = $email;
			$contact->save();
			$contactId = $contacts[0]->Id;
			Infusionsoft_ContactService::addToGroup($contactID, $groupID);
		}
	}
	*/
}

function ehAddSettingsLink( $links ) {
	//add settings link to plugin
    $settings_link = '<a href="admin.php?page=ehCronExpiry_plugin">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}

//Add Filters / Actions / etc.
add_action('ehD_event', 'ehD_schedule_function');
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'ehAddSettingsLink' );
?>