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

//Main courses array
$courses = array( 
 array( 
   "name" => 'ACLS Certification', 
   "id" => 7422, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignacls',
   "years" => '2'
 ),
 array( 
   "name" => 'ACLS Recertification', 
   "id" => 30857, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignacls',
   "years" => '2'
 ),
 array( 
   "name" => 'ACLS For Life', 
   "id" => 108316, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignaclsforlife',
   "years" => '2'
 ),
 array( 
   "name" => 'Bloodborne Pathogens', 
   "id" => 30418, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignbbp',
   "years" => '2'
 ),
 array( 
   "name" => 'BLS Certification', 
   "id" => 7793, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignbls',
   "years" => '2'
 ),
 array( 
   "name" => 'BLS Recertification', 
   "id" => 31023, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignbls',
   "years" => '2'
 ),
 array( 
   "name" => 'BLS For Life', 
   "id" => 108443, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaigblsforlife',
   "years" => '2'
 ),
 array( 
   "name" => 'CPR Certification', 
   "id" => 8942, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaigncpr',
   "years" => '2'
 ),
 array( 
   "name" => 'CPR For Life', 
   "id" => 108641, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaigcprforlife',
   "years" => '2'
 ),
 array( 
   "name" => 'PALS Certification', 
   "id" => 8855, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignpals',
   "years" => '2'
 ),
 array( 
   "name" => 'PALS Recertification', 
   "id" => 30948, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaignpals',
   "years" => '2'
 ),
 array( 
   "name" => 'PALS For Life', 
   "id" => 108566, 
   "integration" => 'oq171',
   "campaign" => 'newremindercampaigpalsforlife',
   "years" => '2'
 )
);


function ehExpiry_setup_menu(){
	add_menu_page( 'Cron Expiry', 'Expire to InfusionSoft ', 'manage_options', 'ehCronExpiry_plugin', 'ehCronExpiry_init', 'dashicons-controls-repeat', 999999);
}

function ehCronExpiry_init(){
	//Called when plugin page loads
	echo "<h1>Welcome to the Cron &amp; Find Expiration Page!</h1>";
	//Time of Next Run
	$timestamp = wp_next_scheduled('ehD_event'); 
	$dt = new DateTime("@$timestamp");
	$dt->setTimeZone(new DateTimeZone('America/New_York'));
	echo '<p><strong>The next scheduled run is:</strong><br />';
	echo $dt->format('F j, Y, g:ia');
	echo ' (Eastern Time)';
	echo '</p>';
	
	//Review of what is going to happen at next CRON run
	echo '<p><strong>The following will be updated at next run:</strong></p>';
	global $wpdb;
	global $courses;	
	foreach($courses as $course) {
		echo "<h3>" . $course['name'] . " - API CALL - " . $course['campaign'] . " - " . $course['years'] . " Years</h3>";
		echo "<p style='margin-left:20px;'>";
		if ($course['years']=2) {
			$results=$wpdb->get_results("SELECT comment_id, user_id, comment_date FROM wp_comments WHERE comment_post_ID = " . $course['id'] . " AND comment_approved LIKE 'complete' AND comment_type LIKE 'sensei_course_status' AND (YEAR(comment_date)=YEAR(CURRENT_DATE())-2) AND (DAYOFMONTH(comment_date)=DAYOFMONTH(CURRENT_DATE())) AND (MONTH(comment_date)=MONTH(CURRENT_DATE())+2) ORDER BY comment_date DESC");
		} else {
			$results=$wpdb->get_results("SELECT comment_id, user_id, comment_date FROM wp_comments WHERE comment_post_ID = " . $course['id'] . " AND comment_approved LIKE 'complete' AND comment_type LIKE 'sensei_course_status' AND (YEAR(comment_date)=YEAR(CURRENT_DATE())-1) AND (DAYOFMONTH(comment_date)=DAYOFMONTH(CURRENT_DATE())) AND (MONTH(comment_date)=MONTH(CURRENT_DATE())+2) ORDER BY comment_date DESC");
		}
	
	foreach($results as $result) {
		$user_info = get_userdata($result->user_id);
		$email = $user_info->user_email;
		echo $user_info->user_email . ", " . $user_info->first_name . " " . $user_info->last_name . ", passed on " . date('m/d/y',strtotime($result->comment_date)) . " at " . date('h:i a',strtotime($result->comment_date)) . "<br />";
	}
	}
	echo "</p>";
}

function activateFunction() {
	//this is called when the plugin is activated
	//ADD the cron
	wp_schedule_event(time(), 'daily', 'ehD_event');
	wp_schedule_event(time(), 'daily', 'ehDTODAY_event');
}

function deactivateFunction() {
	//this is called when the plugin is deactivated
	//REMOVE the cron
	wp_clear_scheduled_hook('ehD_event');
}

function ehD_schedule_function() {
	//Find All users with expire two months in past - 1 month window
	global $wpdb;
	global $courses;	
	foreach($courses as $course) {	
	if ($course['years']=2) {
			$results=$wpdb->get_results("SELECT comment_id, user_id, comment_date FROM wp_comments WHERE comment_post_ID = " . $course['id'] . " AND comment_approved LIKE 'complete' AND comment_type LIKE 'sensei_course_status' AND (YEAR(comment_date)=YEAR(CURRENT_DATE())-2) AND (DAYOFMONTH(comment_date)=DAYOFMONTH(CURRENT_DATE())) AND (MONTH(comment_date)=MONTH(CURRENT_DATE())+2) ORDER BY comment_date DESC");
		} else {
			$results=$wpdb->get_results("SELECT comment_id, user_id, comment_date FROM wp_comments WHERE comment_post_ID = " . $course['id'] . " AND comment_approved LIKE 'complete' AND comment_type LIKE 'sensei_course_status' AND (YEAR(comment_date)=YEAR(CURRENT_DATE())-1) AND (DAYOFMONTH(comment_date)=DAYOFMONTH(CURRENT_DATE())) AND (MONTH(comment_date)=MONTH(CURRENT_DATE())+2) ORDER BY comment_date DESC");
		}
	foreach($results as $result) {
		$user_info = get_userdata($result->user_id);
		$email = $user_info->user_email;
		//look up InfusionSoft ID from email
		require_once('Infusionsoft/infusionsoft.php');
		$contacts = Infusionsoft_DataService::query(new Infusionsoft_Contact(), array('Email' => $email));
		if(count($contacts) > 0) {
			//user exists - add to campaign
			$contactId = $contacts[0]->Id;
			//Infusionsoft_FunnelService::achieveGoal('oq171', $course['campaign'], $contactId);
		} else {
			//user doesn't exist - add user, then add tag
			$contact = new Infusionsoft_Contact();
			$contact->FirstName = $user_info->first_name;
			$contact->LastName = $user_info->last_name;
			$contact->Email = $email;
			$contact->save();
			$contactId = $contact->Id;
			//Infusionsoft_FunnelService::achieveGoal('oq171', $course['campaign'], $contactId);
		}		
	}
	}
}

function ehD_daily_pass() {
	//Find All users who passed TODAY and push to infusionSoft
	global $wpdb;
	global $courses;	
	foreach($courses as $course) {	
	$results=$wpdb->get_results("SELECT comment_id, user_id, comment_date FROM wp_comments WHERE comment_post_ID = " . $course['id'] . " AND comment_approved LIKE 'complete' AND comment_type LIKE 'sensei_course_status' AND (YEAR(comment_date)=YEAR(CURRENT_DATE())) AND (DAYOFMONTH(comment_date)=DAYOFMONTH(CURRENT_DATE())) AND (MONTH(comment_date)=MONTH(CURRENT_DATE())) ORDER BY comment_date DESC");
	foreach($results as $result) {
		$user_info = get_userdata($result->user_id);
		$email = $user_info->user_email;
		//look up InfusionSoft ID from email
		require_once('Infusionsoft/infusionsoft.php');
		$contacts = Infusionsoft_DataService::query(new Infusionsoft_Contact(), array('Email' => $email));
		if(count($contacts) > 0) {
			//user exists - update custom fields
			$contactId = $contacts[0]->Id;
			$customFields = array(
				'_field1',
				'_field2',
			);
			$contact = new Infusionsoft_Contact($contactId);
			$contact->FirstName = $user_info->first_name;
			$contact->LastName = $user_info->last_name;
			$contact->Email = $email;
			$contact->_field1 = date('Y-m-d');
				$dateString = date('Y-m-d');
				$t = strtotime($dateString);
				$t2 = strtotime('+2 years', $t);
				$d2 = date('Y-m-d', $t2);
			$contact->_field2 = $d2;
			//$contact->save();
		} else {
			//user doesn't exist - add user, then add tag
			$contact = new Infusionsoft_Contact();
			$contact->FirstName = $user_info->first_name;
			$contact->LastName = $user_info->last_name;
			$contact->Email = $email;
			$contact->_field1 = date('Y-m-d');
				$dateString = date('Y-m-d');
				$t = strtotime($dateString);
				$t2 = strtotime('+2 years', $t);
				$d2 = date('Y-m-d', $t2);
			$contact->_field2 = $d2;

			//$contact->save();
		}		
	}
	}
}

function ehAddSettingsLink( $links ) {
	//add settings link to plugin page
    $settings_link = '<a href="admin.php?page=ehCronExpiry_plugin">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}

//Add Filters / Actions / etc.
$plugin = plugin_basename( __FILE__ );
add_action('admin_menu', 'ehExpiry_setup_menu');
add_action('ehD_event', 'ehD_schedule_function');
add_action('ehDTODAY_event', 'ehD_daily_pass');
add_filter( "plugin_action_links_$plugin", 'ehAddSettingsLink' );
register_activation_hook( __FILE__, 'activateFunction' );
register_deactivation_hook( __FILE__, 'deactivateFunction' );
?>