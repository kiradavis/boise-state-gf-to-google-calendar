<?php
/*
 * Plugin Name: Boise State Gravity Forms to Google Calendar
 * Plugin URI: https://webguide.boisestate.edu
 * Description: A plugin designed to export entries from Gravity Forms to Google Calendar as events.
 * Version: 0.6.1
 * Author: Kira Davis, David Lentz
 */
 
defined( 'ABSPATH' ) or die( 'No hackers' );

// Updater

if( ! class_exists( 'Boise_State_Gc_Updater' ) ){
 include_once( plugin_dir_path( __FILE__ ) . 'gf-to-gc-updater.php' );
}

$updater = new Boise_State_Gc_Updater( __FILE__ );
//$updater->set_username( 'OITWPsupport' );
$updater->set_username( 'kiradavis' );
$updater->set_repository( 'boise-state-gf-to-google-calendar' );
$updater->initialize();


//-----------------------------------------------------
// Form Submission 
//-----------------------------------------------------
$formAction = "gform_post_submission_" . get_option('form_id');
add_action($formAction, "boise_state_post_submission", 10, 2); //add_action('gravityflow_workflow_complete', 'post_submission', 10, 3);

function boise_state_post_submission($entry, $form) {
	try {
		require_once plugin_dir_path(__FILE__) . '/google-api-php-client-2.1.3/vendor/autoload.php';

		$promotion = $entry[get_option('promotion_id')];
		$startDate = $entry[get_option('start_date_id')];
		$isMember = $entry[get_option('member_id')];
		
		/* 
		* Changes event values based on the type of promotion. 
		* Writes to a different calendar based on promotion.
		*/
		switch($promotion) {
			case 'digitalsignage':
				$calendarId = get_option('ds_calendar_id');
				if($isMember == "member") {
					$endDate = date('Y-m-d', strtotime($startDate. ' + 365 days'));
				} else {
					$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				}
				break;
			case 'digitaltabletents':
				$calendarId = get_option('dtt_calendar_id');
				if($isMember == "member") {
					$endDate = date('Y-m-d', strtotime($startDate. ' + 365 days'));
				} else {
					$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				}
				break;
			case 'outdoorkiosk':
				$calendarId = get_option('okl_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				break;
			case 'outdoorkiosksmall':
				$calendarId = get_option('oks_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				break;
			case 'posterroute':
				$calendarId = get_option('pr_calendar_id');
				$endDate = $entry[get_option('end_date_id')];
				break;
			case 'toilettalk':
				$calendarId = get_option('tt_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 6 days'));
				break;
			default:  
				$calendarId = get_option('calendar_id');
				$endDate = $entry[get_option('end_date_id')];
		}

		
		$client = new Google_Client();

		define("SCOPE",Google_Service_Calendar::CALENDAR);
		define("APP_NAME","Google Calendar API PHP");
		
		/* 
		* TODO: Find a more secure way to access these credentials?
		* This works by using a Service Account (created at console.developers.google.com) and 
		* using the key for that account as the default credentials. 
		*
		* This solution was found at: https://github.com/google/google-auth-library-php
		*/ 
		$filePath = 'GravityFormtoGoogleCalendar-5c191c0932ae.json';
		//$filePath = get_option('file_upload');
		$path = 'GOOGLE_APPLICATION_CREDENTIALS=' . plugin_dir_path(__FILE__) . $filePath;
		putenv($path);
		$client->useApplicationDefaultCredentials();
			
		$client->setApplicationName(APP_NAME);
		$client->setScopes([SCOPE]);
		
		$service = new Google_Service_Calendar($client);

		$name = $entry[get_option('name_id')];
		$summary = $entry[get_option('summary_id')];
		
		$startYear = substr($startDate, 0, 4);
		$startMonth = substr($startDate, 5, -3);
		$startDay = substr($startDate, -2);
		
		$endYear = substr($endDate, 0, 4);
		$endMonth = substr($endDate, 5, -3);
		$endDay = substr($endDate, -2);
		
		/* Dates are one day behind, adding a +1 to each for now to fix this. */
		$startTimestamp = mktime(0,0,0,$startMonth,$startDay + 1,$startYear);
		$endTimestamp = mktime(0,0,0,$endMonth,$endDay + 1,$endYear);
		
		$start = array(
			'dateTime' => date ('c', $startTimestamp),
			'timeZone' => 'America/Denver',
		);

		$end = array(
			'dateTime' => date ('c', $endTimestamp),
			'timeZone' => 'America/Denver',
		);

		$myEventArray = array(
			'summary' => $name,
			'location' => 'Boise State University',
			'description' => $summary,
			'start' => $start,
			'end' => $end,
			'color' => 11,
		);

		$myEvent = new Google_Service_Calendar_Event($myEventArray);

		$event = $service->events->insert($calendarId, $myEvent);
		
	// Error thrown when event is not added successfully.
	} catch (Exception $e) {
		echo $e->getMessage();
	} 
}	

//-----------------------------------------------------
// Admin Page Settings
//-----------------------------------------------------
 
add_action('admin_menu', 'boise_state_gc_admin_settings');

function boise_state_gc_admin_settings() {
    $page_title = 'Gravity Forms to Google Calendar Settings';
    $menu_title = 'Gravity Forms to Google Calendar';
    $capability = 'edit_posts';
    $menu_slug = 'boise_state_gf_to_gc_options';
    $function = 'boise_state_gc_admin_settings_page_display';
    $icon_url = '';
    $position = 99;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}
function file_upload_field() {
   ?>
        <input type="file" name="file_upload" id="file_upload" /> 
        <?php echo get_option('file_upload'); ?>
   <?php
}
/* Calendar Fields*/
/* function api_key_field() { ?> <input type="textarea" name="api_key" id="api_key" value="<?php echo get_option('api_key'); ?>" /> <?php } */
function ds_calendar_id_field() { ?> <input type="text" name="ds_calendar_id" id="ds_calendar_id" value="<?php echo get_option('ds_calendar_id'); ?>" /> <?php }
function dtt_calendar_id_field() { ?> <input type="text" name="dtt_calendar_id" id="dtt_calendar_id" value="<?php echo get_option('dtt_calendar_id'); ?>" /> <?php }
function idb_calendar_id_field() { ?> <input type="text" name="idb_calendar_id" id="idb_calendar_id" value="<?php echo get_option('idb_calendar_id'); ?>" /> <?php }
function okl_calendar_id_field() { ?> <input type="text" name="okl_calendar_id" id="okl_calendar_id" value="<?php echo get_option('okl_calendar_id'); ?>" /> <?php }
function oks_calendar_id_field() { ?> <input type="text" name="oks_calendar_id" id="oks_calendar_id" value="<?php echo get_option('oks_calendar_id'); ?>" /> <?php }
function pr_calendar_id_field() { ?> <input type="text" name="pr_calendar_id" id="pr_calendar_id" value="<?php echo get_option('pr_calendar_id'); ?>" /> <?php }
function tt_calendar_id_field() { ?> <input type="text" name="tt_calendar_id" id="tt_calendar_id" value="<?php echo get_option('tt_calendar_id'); ?>" /> <?php }

/* Gravity Forms */
function form_id_field() { ?> <input type="text" name="form_id" id="form_id" value="<?php echo get_option('form_id'); ?>" /> <?php }
function promotion_id_field() { ?> <input type="text" name="promotion_id" id="promotion_id" value="<?php echo get_option('promotion_id'); ?>" /> <?php }
function name_id_field() { ?> <input type="text" name="name_id" id="name_id" value="<?php echo get_option('name_id'); ?>" /> <?php }
function summary_id_field() { ?> <input type="text" name="summary_id" id="summary_id" value="<?php echo get_option('summary_id'); ?>" /> <?php }
function start_date_id_field() { ?> <input type="text" name="start_date_id" id="start_date_id" value="<?php echo get_option('start_date_id'); ?>" /> <?php }
function end_date_id_field() { ?> <input type="text" name="end_date_id" id="end_date_id" value="<?php echo get_option('end_date_id'); ?>" /> <?php }
function member_id_field() { ?> <input type="text" name="member_id" id="member_id" value="<?php echo get_option('member_id'); ?>" /> <?php }

function boise_state_gc_display_theme_panel_fields() {
	add_settings_section("gc-section", "Google Calendar & Gravity Forms", null, "gc-options");
	add_settings_field("file_upload", "Upload API Key", "file_upload_field", "gc-options", "gc-section");
	
	/* Calendar Settings */
	/* add_settings_field("api_key", "API Key", "api_key_field", "gc-options", "gc-section"); */
	add_settings_field("ds_calendar_id", "Digital Signage (Calendar ID)", "ds_calendar_id_field", "gc-options", "gc-section");
	add_settings_field("dtt_calendar_id", "Digital Table Tents (Calendar ID)", "dtt_calendar_id_field", "gc-options", "gc-section");
	add_settings_field("okl_calendar_id", "Outdoor Kiosk Large (Calendar ID)", "okl_calendar_id_field", "gc-options", "gc-section");
	add_settings_field("oks_calendar_id", "Outdoor Kiosk Small (Calendar ID)", "oks_calendar_id_field", "gc-options", "gc-section");
	add_settings_field("pr_calendar_id", "Poster Route (Calendar ID)", "pr_calendar_id_field", "gc-options", "gc-section");
	add_settings_field("tt_calendar_id", "Toilet Talk (Calendar ID)", "tt_calendar_id_field", "gc-options", "gc-section");
		
	/* Gravity Forms Setting */
	add_settings_field("form_id", "Gravity Form ID", "form_id_field", "gc-options", "gc-section");
	add_settings_field("promotion_id", "Promotions Field ID", "promotion_id_field", "gc-options", "gc-section");
	add_settings_field("name_id", "Event Name Field ID", "name_id_field", "gc-options", "gc-section");
	add_settings_field("summary_id", "Description Field ID", "summary_id_field", "gc-options", "gc-section");
	add_settings_field("start_date_id", "Start Date Field ID", "start_date_id_field", "gc-options", "gc-section");
	add_settings_field("end_date_id", "End Date Field ID", "end_date_id_field", "gc-options", "gc-section");
	add_settings_field("member_id", "Member Field ID", "member_id_field", "gc-options", "gc-section");

	/* Register All Settings */
	/* register_setting("gc-section", "api_key"); */
	register_setting("gc-section", "file_upload");
	register_setting("gc-section", "tt_calendar_id");
	register_setting("gc-section", "ds_calendar_id");
	register_setting("gc-section", "dtt_calendar_id");
	register_setting("gc-section", "okl_calendar_id");
	register_setting("gc-section", "oks_calendar_id");
	register_setting("gc-section", "pr_calendar_id");
	register_setting("gc-section", "tt_calendar_id");
	register_setting("gc-section", "form_id");
	register_setting("gc-section", "promotion_id");
	register_setting("gc-section", "name_id");
	register_setting("gc-section", "summary_id");
	register_setting("gc-section", "start_date_id");
	register_setting("gc-section", "end_date_id");
	register_setting("gc-section", "member_id");
	
}

add_action("admin_init", "boise_state_gc_display_theme_panel_fields");

//-----------------------------------------------------
// Admin Page Display
//-----------------------------------------------------
function boise_state_gc_admin_settings_page_display() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	?>
	<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<!-- show error/update messages -->
		<?php settings_errors(); ?>
		
	    <form method="post" action="options.php">
	        <?php
				settings_fields("gc-section");
				do_settings_sections("gc-options");      
				submit_button(); 
	        ?>          
		</form>
	</div>
	<?php
}
?>
