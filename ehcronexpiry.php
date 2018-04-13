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

/*
Check out page-aclsidcard.php (and the rest) in the root of Dynamic-Child - these were created by Element to dynamically create the digital cards and certificates. you’ll find references to completion and expiration.

This is code from the page Barnes referenced:

	global $current_user;
	wp_get_current_user();

	$course_id = 7422;

	$user_cert_date = get_user_meta( $current_user->ID, 'NRP Cert Date', true );
	$user_from_date = date('M j, Y', strtotime('+0 years', strtotime($user_cert_date)));
	$user_new_date = date('M j, Y', strtotime('+2 years', strtotime($user_cert_date)));

	$course_end = WooThemes_Sensei_Utils::sensei_check_for_activity( array( 'post_id' => intval( $course_id ), 'user_id' => intval( $current_user->ID ), 'type' => 'sensei_course_status' ), true );
	$course_end_date = $course_end->comment_date;
	$course_from_date = date('M j, Y', strtotime('+0 years', strtotime($course_end_date)));
	$course_new_date = date('M j, Y', strtotime('+2 years', strtotime($course_end_date)));

*/

register_activation_hook( __FILE__, 'activateFunction' );
register_deactivation_hook( __FILE__, 'deactivateFunction' );

add_action('admin_menu', 'ehExpiry_setup_menu');

function ehExpiry_setup_menu(){
	add_menu_page( 'Cron Expiry', 'Expire to InfusionSoft ', 'manage_options', 'ehCronExpiry_plugin', 'ehCronExpiry_init', 'dashicons-controls-repeat', 999999);
}

function ehCronExpiry_init(){
	// this is called when the page loads
	//TITLE
	echo "<h1>Welcome to the Cron &amp; Find Expiration Page!</h1>";
	//Time of Next Run
	$timestamp = wp_next_scheduled('hourlyEvent3'); 
	$dt = new DateTime("@$timestamp");
	$dt->setTimeZone(new DateTimeZone('America/New_York'));
	echo '<p><strong>The next scheduled run is:</strong><br />';
	echo $dt->format('F j, Y, g:ia');
	echo ' (Eastern Time)';
	echo '</p>';	
}

function activateFunction() {
	//this is called when the plugin is activated
	//ADD the cron
	wp_schedule_event(time(), 'hourly', 'hourlyEvent3');
}

function deactivateFunction() {
	//this is called when the plugin is deactivated
	//REMOVE the cron
	wp_clear_scheduled_hook('hourlyEvent3');
}

function ehD_hourlyFunction() {
	// Hourly Function via WP Cron
	//Find all users with an expiration date 30 days in the future - erichall68->103800
	/*
	global $wpdb;
	$results=$wpdb->get_results("SELECT userID, expireDate FROM testDate WHERE expireDate >= NOW() AND TO_DAYS(expireDate)-TO_DAYS(NOW())=30");
	
	// Loop Through and Add them to Infusionsoft and add a tag to them
	foreach($results as $result) {
		//THIS IS ALL TESTING... erichall68 = 103800
		require_once('Infusionsoft/infusionsoft.php');
		$user_info = get_userdata($result->userID);
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
add_action('hourlyEvent3', 'ehD_hourlyFunction');
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'ehAddSettingsLink' );


?>