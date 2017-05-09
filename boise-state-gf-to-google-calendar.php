/**
 *Gravity Forms to Google Calendar
 *@Kira Davis
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

		/**
		* Figure out a way to reauthorize the token, because the current one will expire.
		**/
		
		//$auth_url = $client->createAuthUrl();
		//echo "Open the following link in your browser: <a href='$auth_url' target='_blank'>" .  $auth_url . "</a>";
		//header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
		//$token = 'token.txt';
		
	$client->setAccessToken('ya29.GltFBBQpM8eYSd1VhVc0QjSwEHPtbvNoYaFXlSaQqaB12doW3Frx5ovSI8qktnZHHz8OHcO0v-hlfyzx1xNBUZicWSVppunrM_0gQakayymlBC7YH8AC-X4868dV');

	$service = new Google_Service_Calendar($client);

	$calendarId = 'boisestate.edu_pr1pk32qsegav3dr56j56aeoa8@group.calendar.google.com';

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
	
	/**
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
			'end' => $end
	);

	$myEvent = new Google_Service_Calendar_Event($myEventArray);

	$event = $service->events->insert($calendarId, $myEvent);
	printf('Event created: %s', $event->htmlLink);
}	