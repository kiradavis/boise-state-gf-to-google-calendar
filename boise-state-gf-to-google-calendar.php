<?php
/*
 * Plugin Name: Boise State Gravity Forms to Google Calendar
 * Plugin URI: https://webguide.boisestate.edu
 * Description: A plugin designed to export entries from Gravity Forms to Google Calendar as events.
 * Version: 0.1
 * Author: Kira Davis & David Lentz
 */

$formAction = "gform_post_submission_" . get_option('form_id');
add_action($formAction, "post_submission", 10, 2);

/* Runs after the form is submitted, obtains access token, and adds event to the calendar. */
function post_submission($entry, $form) {
	try {
		require_once '/google-api-php-client-2.1.3/vendor/autoload.php';

		$client = new Google_Client();

		$application_creds = 'client_secret.json';
			
		$credentials_file = file_exists($application_creds) ? $application_creds : false;
		define("SCOPE",Google_Service_Calendar::CALENDAR);
		define("APP_NAME","Google Calendar API PHP");
			
		$client->setAuthConfig($credentials_file);
		$client->setApplicationName(APP_NAME);
		$client->setScopes([SCOPE]);
		$client->setAccessType("offline");
		
		/* TODO: Figure out a way below to reauthorize the token, because the current expires after an hour. */	
		/*
		if (isset($_GET['code'])) 
		{
			$client->authenticate($_GET['code']);
			$_SESSION['token'] = $client->getAccessToken();
			header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
		}

		if (isset($_SESSION['token'])) {

			//Set the new access token after authentication
			$client->setAccessToken($_SESSION['token']);

			//json decode the session token and save it in a variable as object
			$sessionToken = json_decode($_SESSION['token']);

			//Save the refresh token (object->refresh_token) into a cookie called 'token' and make last for 1 month
			$this->Cookie->write('token', $sessionToken->refresh_token, false, '1 month');
		}

		//Each time you need the access token, check if there is something saved in the cookie.
		//If $cookie is empty, you are requested to get a new acces and refresh token by authenticating.
		//If $cookie is not empty, you will tell the client to refresh the token for further use,
		// hence get a new acces token with the help of the refresh token without authenticating..
		//$cookie = $this->Cookie->read('token');

		if(!empty($cookie)){
		    $client->refreshToken($this->Cookie->read('token'));
		} */
		/* END trying to reauthorize token. */
		
		$client->setAccessToken(get_option('access_token'));
		$service = new Google_Service_Calendar($client);

		$calendarId = get_option('calendar_id');

		$promotion = $entry[get_option('promotion_id')];
		$name = $entry[get_option('name_id')];
		$summary = $entry[get_option('summary_id')];
		
		$startDate = $entry[get_option('start_date_id')];
		$startYear = substr($startDate, 0, 4);
		$startMonth = substr($startDate, 5, -3);
		$startDay = substr($startDate, -2);
		
		$endDate = $entry[get_option('end_date_id')];
		$endYear = substr($endDate, 0, 4);
		$endMonth = substr($endDate, 5, -3);
		$endDay = substr($endDate, -2);
		
		/* Something is off with the start and end days. They appear one day before on the calendar event. 
		 * (7th turns to 6th, etc.). Adding a +1 to each for now.
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
		
		/* Change color of event based on what type of promotion. 
		 * Right now, just using default colors 1-7. 
		 * Color guide here: https://eduardopereira.pt/2012/06/google-calendar-api-v3-set-color-color-chart/
		 */
		switch($promotion) {
			case 'digitalsignage':
				$myEvent->setColorId("1");
				break;
			case 'digitaltabletents':
				$myEvent->setColorId("2");
				break;
			case 'infodeskbackdrop':
				$myEvent->setColorId("3");
				break;
			case 'outdoorkiosk':
				$myEvent->setColorId("4");
				break;
			case 'posterroute':
				$myEvent->setColorId("5");
				break;
			case 'toilettalk':
				$myEvent->setColorId("6");
				break;
			default: 
				$myEvent->setColorId("7");
		}

		$event = $service->events->insert($calendarId, $myEvent);
		//printf('Event created: %s', $event->htmlLink);
		
	// Error thrown when event is not added successfully.
	} catch (Exception $e) {
		echo "Something went wrong. The event was not added to the calendar.";
	} 
}	

/* Adding in options page & settings. */
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

function calendar_id_field() {
	?> <input type="text" name="calendar_id" id="calendar_id" value="<?php echo get_option('calendar_id'); ?>" /> <?php
}

function access_token_field() {
	?> <input type="text" name="access_token" id="access_token" value="<?php echo get_option('access_token'); ?>" /> <?php
}

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
	add_settings_section("section", "All Settings", null, "theme-options");
	
	add_settings_field("calendar_id", "Google Calendar ID", "calendar_id_field", "theme-options", "section");
	add_settings_field("access_token", "Access Token", "access_token_field", "theme-options", "section");
    add_settings_field("form_id", "Gravity Form ID", "form_id_field", "theme-options", "section");
	add_settings_field("promotion_id", "Promotions Field ID", "promotion_id_field", "theme-options", "section");
	add_settings_field("name_id", "Event Name Field ID", "name_id_field", "theme-options", "section");
	add_settings_field("summary_id", "Description Field ID", "summary_id_field", "theme-options", "section");
	add_settings_field("start_date_id", "Start Date Field ID", "start_date_id_field", "theme-options", "section");
	add_settings_field("end_date_id", "End Date Field ID", "end_date_id_field", "theme-options", "section");

	register_setting("section", "calendar_id");
    register_setting("section", "access_token");
    register_setting("section", "form_id");
	register_setting("section", "promotion_id");
	register_setting("section", "name_id");
	register_setting("section", "summary_id");
	register_setting("section", "start_date_id");
	register_setting("section", "end_date_id");
}

add_action("admin_init", "display_theme_panel_fields");

function admin_settings_page_display() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
 
	// add error/update messages
 
	// check if the user have submitted the settings
	// wordpress will add the "settings-updated" $_GET parameter to the url
 
	// show error/update messages
	//settings_errors( 'chat_schedule_messages' );
    ?>
	    <div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	    <form method="post" action="options.php">
	        <?php
	            settings_fields("section");
	            do_settings_sections("theme-options");      
	            submit_button(); 
	        ?>          
	    </form>
		</div>
	<?php
}
?>
