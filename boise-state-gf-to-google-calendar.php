<?php
/*
 * Plugin Name: Boise State Gravity Forms to Google Calendar
 * Plugin URI: https://webguide.boisestate.edu
 * Description: a plugin designed to export entries from Gravity Forms to Google Calendar as events.
 * Version: 0.1
 * Author: Kira Davis & David Lentz
 */

add_action("gform_post_submission_3", "post_submission", 10, 2);

function post_submission($entry, $form) {
	require_once '../../google-api-php-client-2.1.3/vendor/autoload.php';

	$client = new Google_Client();

	$application_creds = 'client_secret.json';
		
	$credentials_file = file_exists($application_creds) ? $application_creds : false;
	define("SCOPE",Google_Service_Calendar::CALENDAR);
	define("APP_NAME","Google Calendar API PHP");	
		
	$client->setAuthConfig($credentials_file);
	$client->setApplicationName(APP_NAME);
	$client->setScopes([SCOPE]);
	$client->setAccessType("offline");

	/*
	 * Figure out a way to reauthorize the token, because the current one will expire.
	 */
		
	//$auth_url = $client->createAuthUrl();
	//echo "Open the following link in your browser: <a href='$auth_url' target='_blank'>" .  $auth_url . "</a>";
	//header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
	//
	//$client->authenticate($_GET['code']);
	//$access_token = $client->getAccessToken();
		
	//$client->setAccessToken('ya29.GltGBDb2wn3jEagKdLu_UEWqDwBkGLbwfdw9fKGzbFhHR8x99XTSQpzElu5BF_hYAOzCFxEdRaGl6RIQUfOtZhwocN_YRs_VedphzLvJ6d4TssnTuAoOOgw-Ndcc');
	$client->setAccessToken(get_option('access_token'));
	
	$service = new Google_Service_Calendar($client);

	$calendarId = 'boisestate.edu_pr1pk32qsegav3dr56j56aeoa8@group.calendar.google.com';

	$promotion = $entry["1"];
	$name = $entry["7"];
	$summary = $entry["10"];
	
	// Start Day
	$startDate = $entry["8"];
	$startYear = substr($startDate, 0, 4);
	$startMonth = substr($startDate, 5, -3);
	$startDay = substr($startDate, -2);
	
	// End Day
	$endDate = $entry["9"];
	$endYear = substr($endDate, 0, 4);
	$endMonth = substr($endDate, 5, -3);
	$endDay = substr($endDate, -2);
	
	/*
	 * Something is off with the start and end days. They appear one day before on the calendar event. 
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
}	

/*
 * Adding in options page & settings.
 */
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

function access_token_field()
{
	?>
    	<input type="text" name="access_token" id="access_token" value="<?php echo get_option('access_token'); ?>" />
    <?php
}

/*
function display_facebook_element()
{
	?>
    	<input type="text" name="facebook_url" id="facebook_url" value="<?php echo get_option('facebook_url'); ?>" />
    <?php
}
*/

function display_theme_panel_fields()
{
	add_settings_section("section", "All Settings", null, "theme-options");
	
	add_settings_field("access_token", "Access Token", "access_token_field", "theme-options", "section");
    //add_settings_field("facebook_url", "Facebook Profile Url", "display_facebook_element", "theme-options", "section");

    register_setting("section", "access_token");
    //register_setting("section", "facebook_url");
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
