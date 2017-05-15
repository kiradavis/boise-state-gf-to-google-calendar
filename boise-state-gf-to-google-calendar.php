<?php
/*
 * Plugin Name: Boise State Gravity Forms to Google Calendar
 * Plugin URI: https://webguide.boisestate.edu
 * Description: A plugin designed to export entries from Gravity Forms to Google Calendar as events.
 * Version: 0.3
 * Author: Kira Davis & David Lentz
 */

//--------------------------------------------------------------------------------
// Load Scripts
//--------------------------------------------------------------------------------
function load_scripts() {
	wp_enqueue_script( 'jquery' );
    //wp_enqueue_script( 'date-picker-script', plugin_dir_url(__FILE__) . '/date-picker-script.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'load_scripts' );

$formAction = "gform_post_submission_" . get_option('form_id');
add_action($formAction, "post_submission", 10, 2); //add_action('gravityflow_workflow_complete', 'post_submission', 10, 3);

//--------------------------------------------------------------------------------
// Form Submission - Taking Info from the Gravity Form, adding to Google Calendar
//--------------------------------------------------------------------------------
function post_submission($entry, $form) {
	try {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/wp/wordpress/wp-content/plugins/boise-state-gf-to-google-calendar/google-api-php-client-2.1.3/vendor/autoload.php';
		//require_once 'https://www.googleapis.com/calendar/v3/calendars/boisestate.edu_pr1pk32qsegav3dr56j56aeoa8@group.calendar.google.com/acl?key=AIzaSyB2-aUwiwZJqIjRHJloiJ0naD8QGutwIhY';

		$promotion = $entry[get_option('promotion_id')];
		$startDate = $entry[get_option('start_date_id')];

		/* 
		* Changes event values based on the type of promotion. 
		* Right now, using the default colors 1-7. 
		* Color guide here: https://eduardopereira.pt/2012/06/google-calendar-api-v3-set-color-color-chart/
		*/
		switch($promotion) {
			case 'digitalsignage':
				//$myEvent->setColorId("1");
				$calendarId = get_option('ds_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				break;
			case 'digitaltabletents':
				//$myEvent->setColorId("2");
				$calendarId = get_option('dtt_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				break;
			case 'infodeskbackdrop':
				//$myEvent->setColorId("3");
				$calendarId = get_option('idb_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				break;
			case 'outdoorkiosk':
				//$myEvent->setColorId("4");
				$calendarId = get_option('okl_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				break;
			case 'outdoorkiosksmall':
				//$myEvent->setColorId("4");
				$calendarId = get_option('oks_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));
				break;
			case 'posterroute':
				//$myEvent->setColorId("5");
				$calendarId = get_option('pr_calendar_id');
				$endDate = $entry[get_option('end_date_id')];
				break;
			case 'toilettalk':
				//$myEvent->setColorId("6");
				$calendarId = get_option('tt_calendar_id');
				$endDate = date('Y-m-d', strtotime($startDate. ' + 6 days'));
				break;
			default: 
				//$myEvent->setColorId("7");
				$calendarId = get_option('calendar_id');
				$endDate = $entry[get_option('end_date_id')];
		}
		
		$client = new Google_Client();
		$application_creds = 'client_secret.json';
			
		$credentials_file = file_exists($application_creds) ? $application_creds : false;
		define("SCOPE",Google_Service_Calendar::CALENDAR);
		define("APP_NAME","Google Calendar API PHP");
		
		/* 
		* TODO: Find a more secure way to access these credentials?
		* This works by using a Service Account (created at console.developers.google.com) and 
		* using the key for that account as the default credentials. 
		*
		* This solution was found at: https://github.com/google/google-auth-library-php
		*/
		$path = 'GOOGLE_APPLICATION_CREDENTIALS=' . $_SERVER['DOCUMENT_ROOT'] . '/wp/wordpress/wp-content/plugins/boise-state-gf-to-google-calendar/MyProject-b2a8b2440207.json';
		putenv($path);
		$client->useApplicationDefaultCredentials();
			
		$client->setAuthConfig($credentials_file);
		$client->setApplicationName(APP_NAME);
		$client->setScopes([SCOPE]);
		//$client->setAccessType("offline");
		
		//$client->setAccessToken(get_option('access_token'));
		$service = new Google_Service_Calendar($client);

		$name = $entry[get_option('name_id')];
		$summary = $entry[get_option('summary_id')];
		
		$startYear = substr($startDate, 0, 4);
		$startMonth = substr($startDate, 5, -3);
		$startDay = substr($startDate, -2);
		
		/* Determine what end date is */
		if ($promotion == "") {
			$endDate = $entry[get_option('end_date_id')];
		}
		
		$endYear = substr($endDate, 0, 4);
		$endMonth = substr($endDate, 5, -3);
		$endDay = substr($endDate, -2);
		
		/* 
		* TODO: Find out why the date logic is off here?
		* Start and end days are one off, they appear one day before. 
		* (7th turns to 6th, etc.). Adding a +1 to each for now to fix this.
		*/
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
		//printf('Event created: %s', $event->htmlLink); 
		
	// Error thrown when event is not added successfully.
	} catch (Exception $e) {
		echo "Something went wrong. The event was not added to the calendar.";
	} 
}	

//-----------------------------------------------------
// Admin Page Settings
//-----------------------------------------------------
add_action('admin_menu', 'admin_settings');

function admin_settings() {
    $page_title = 'Gravity Forms to Google Calendar Settings';
    $menu_title = 'Gravity Forms to Google Calendar';
    $capability = 'edit_posts';
    $menu_slug = 'awesome_page';
    $function = 'admin_settings_page_display';
    $icon_url = '';
    $position = 99;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}
/* Calendars */
function calendar_id_field() {
	?> <input type="text" name="calendar_id" id="calendar_id" value="<?php echo get_option('calendar_id'); ?>" /> <?php
}
function ds_calendar_id_field() {
	?> <input type="text" name="ds_calendar_id" id="ds_calendar_id" value="<?php echo get_option('ds_calendar_id'); ?>" /> <?php
}
function dtt_calendar_id_field() {
	?> <input type="text" name="dtt_calendar_id" id="dtt_calendar_id" value="<?php echo get_option('dtt_calendar_id'); ?>" /> <?php
}
function idb_calendar_id_field() {
	?> <input type="text" name="idb_calendar_id" id="idb_calendar_id" value="<?php echo get_option('idb_calendar_id'); ?>" /> <?php
}
function okl_calendar_id_field() {
	?> <input type="text" name="okl_calendar_id" id="okl_calendar_id" value="<?php echo get_option('okl_calendar_id'); ?>" /> <?php
}
function oks_calendar_id_field() {
	?> <input type="text" name="oks_calendar_id" id="oks_calendar_id" value="<?php echo get_option('oks_calendar_id'); ?>" /> <?php
}
function pr_calendar_id_field() {
	?> <input type="text" name="pr_calendar_id" id="pr_calendar_id" value="<?php echo get_option('pr_calendar_id'); ?>" /> <?php
}
function tt_calendar_id_field() {
	?> <input type="text" name="tt_calendar_id" id="tt_calendar_id" value="<?php echo get_option('tt_calendar_id'); ?>" /> <?php
}

/* Gravity Forms */
function form_id_field() {
	?> <input type="text" name="form_id" id="form_id" value="<?php echo get_option('form_id'); ?>" /> <?php
}
function promotion_id_field() {
	?> <input type="text" name="promotion_id" id="promotion_id" value="<?php echo get_option('promotion_id'); ?>" /> <?php
}
function name_id_field() {
	?> <input type="text" name="name_id" id="name_id" value="<?php echo get_option('name_id'); ?>" /> <?php
}
function summary_id_field() {
	?> <input type="text" name="summary_id" id="summary_id" value="<?php echo get_option('summary_id'); ?>" /> <?php
}
function start_date_id_field() {
	?> <input type="text" name="start_date_id" id="start_date_id" value="<?php echo get_option('start_date_id'); ?>" /> <?php
}
function end_date_id_field() {
	?> <input type="text" name="end_date_id" id="end_date_id" value="<?php echo get_option('end_date_id'); ?>" /> <?php
}

function display_theme_panel_fields() {
	/* Calendar Settings */
	add_settings_section("cal-section", "Google Calendar IDs", null, "theme-options");
	add_settings_section("section", "Gravity Forms", null, "theme-options");
	
	add_settings_field("calendar_id", "Promotions", "calendar_id_field", "theme-options", "cal-section");
	add_settings_field("ds_calendar_id", "Digital Signage", "ds_calendar_id_field", "theme-options", "cal-section");
	add_settings_field("dtt_calendar_id", "Digital Table Tents", "dtt_calendar_id_field", "theme-options", "cal-section");
	add_settings_field("idb_calendar_id", "Info Desk Backdrop", "idb_calendar_id_field", "theme-options", "cal-section");
	add_settings_field("okl_calendar_id", "Outdoor Kiosk Large", "okl_calendar_id_field", "theme-options", "cal-section");
	add_settings_field("oks_calendar_id", "Outdoor Kiosk Small", "oks_calendar_id_field", "theme-options", "cal-section");
	add_settings_field("pr_calendar_id", "Poster Route", "pr_calendar_id_field", "theme-options", "cal-section");
	add_settings_field("tt_calendar_id", "Table Talk", "tt_calendar_id_field", "theme-options", "cal-section");
		
	/* Gravity Forms Setting */
    	add_settings_field("form_id", "Gravity Form ID", "form_id_field", "theme-options", "section");
	add_settings_field("promotion_id", "Promotions Field ID", "promotion_id_field", "theme-options", "section");
	add_settings_field("name_id", "Event Name Field ID", "name_id_field", "theme-options", "section");
	add_settings_field("summary_id", "Description Field ID", "summary_id_field", "theme-options", "section");
	add_settings_field("start_date_id", "Start Date Field ID", "start_date_id_field", "theme-options", "section");
	add_settings_field("end_date_id", "End Date Field ID", "end_date_id_field", "theme-options", "section");

	/* Register All Settings */
	register_setting("cal-section", "calendar_id");
	register_setting("cal-section", "tt_calendar_id");
	register_setting("cal-section", "ds_calendar_id");
	register_setting("cal-section", "dtt_calendar_id");
	register_setting("cal-section", "idb_calendar_id");
	register_setting("cal-section", "okl_calendar_id");
	register_setting("cal-section", "oks_calendar_id");
	register_setting("cal-section", "pr_calendar_id");
	register_setting("cal-section", "tt_calendar_id");
    	register_setting("section", "form_id");
	register_setting("section", "promotion_id");
	register_setting("section", "name_id");
	register_setting("section", "summary_id");
	register_setting("section", "start_date_id");
	register_setting("section", "end_date_id");
	
}

add_action("admin_init", "display_theme_panel_fields");

//-----------------------------------------------------
// Admin Page Display
//-----------------------------------------------------
function admin_settings_page_display() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
 
	// add error/update messages
 
	// check if the user have submitted the settings
	// wordpress will add the "settings-updated" $_GET parameter to the url
 
	// show error/update messages
    ?>
	    <div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	    <form method="post" action="options.php">
	        <?php
				settings_fields("section");
	            settings_fields("cal-section");
	            do_settings_sections("theme-options");      
	            submit_button(); 
	        ?>          
	    </form>
		</div>
	<?php
}
?>
