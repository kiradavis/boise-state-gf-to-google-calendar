<?php
/*
Plugin Name: Boise State OIT Help Desk Chat Scheduler
Description: Allows Help Desk staff to defined the hours during which chat service is available. Directs users to either a Bomgar chat page or a "we're closed" message, based on that schedule.
Plugin URI: https://github.com/OITWPsupport/boise-state-help-desk-chat-scheduler
Version: 0.0.3
Author: David Lentz
Author URI: https://webguide.boisestate.edu
*/

defined( 'ABSPATH' ) or die( 'No hackers' );

// Future functionality: auto-updating
/*
if( ! class_exists( 'Boise_State_OIT_Help_Desk_Chat_Scheduler_Updater' ) ){
	include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
}
$updater = new Boise_State_OIT_Help_Desk_Chat_Scheduler_Updater( __FILE__ );
$updater->set_username( 'OITWPsupport' );
$updater->set_repository( 'boise-state-help-desk-chat-scheduler' );
$updater->initialize();
*/

function boise_state_help_desk_chat_scheduler() {

date_default_timezone_set('America/Boise');

	$timeString = date("Hi");
	$time = intval($timeString); // Cast that as an int (was a string)

	$options_array = get_option(chat_schedule_options);
	// This array is made of elements named like 'Mon_open' and 'Mon_close'
	$openIndex = date('D') . "_open"; // Array element that holds today's opening time
	$closeIndex = date('D') . "_close"; // Array element that holds today's closing time
	$openTime = $options_array[$openIndex];
	$closeTime = $options_array[$closeIndex];

	$open = ($openTime < $time && $time < $closeTime) ? TRUE : FALSE;

	if ($open) {
		$location = 'http://rc.boisestate.edu/api/start_session.ns?issue_menu=1&id=1&c2cjs=1';
	} else {
		$location = 'http://oit.boisestate.edu/chat-support-is-closed';
	}

	echo "<br />Redirecting you to $location";
}

add_shortcode('boise_state_chat_schedule', 'boise_state_help_desk_chat_scheduler');

function chat_schedule_settings_init() {
	register_setting( 'chat_schedule_options_group', 'chat_schedule_options' );
 
	// register a new section in the "chat_schedule" page
	add_settings_section(
		'chat_schedule_options_section',
		__( 'Here is this part.', 'chat_schedule_options_group' ),
		'chat_schedule_options_section_cb',
		'chat_schedule'
	);
 
	// Register a new field in the "chat_schedule_options_section" section, inside the "chat_schedule" page
	add_settings_field(
		'Mon_open', // as of WP 4.6 this value is used only internally
		// use the $args label_for to populate the id inside the callback
		__( 'Monday open', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Mon_open',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Mon_close', // as of WP 4.6 this value is used only internally
		// use the $args label_for to populate the id inside the callback
		__( 'Monday close', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Mon_close',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Tue_open',
		__( 'Tuesday open', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Tue_open',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Tue_closed',
		__( 'Tuesday closed', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Tue_closed',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Wed_open',
		__( 'Wednesday open', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Wed_open',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Wed_closed',
		__( 'Wednesday closed', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Wed_closed',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Thu_open',
		__( 'Thursday open', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Thu_open',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Thu_closed',
		__( 'Thursday closed', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Thu_closed',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Fri_open',
		__( 'Friday open', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Fri_open',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Fri_closed',
		__( 'Friday closed', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Fri_closed',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Sat_open',
		__( 'Saturday open', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Sat_open',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Sat_closed',
		__( 'Saturday closed', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Sat_closed',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Sun_open',
		__( 'Sunday open', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Sun_open',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'Sun_closed',
		__( 'Sunday closed', 'chat_schedule' ),
		'settings_cb',
		'chat_schedule',
		'chat_schedule_options_section',
		[
			'label_for' => 'Sun_closed',
			'class' => 'chat_hours_row',
			'chat_schedule_custom_data' => 'custom',
		]
	);

}

// Register our chat_schedule_settings_init to the admin_init action hook
add_action( 'admin_init', 'chat_schedule_settings_init' );


// Custom option and settings: callback functions

// chat_schedule_options_section callback
 
// Section callbacks can accept an $args parameter (array).
// $args have the following keys defined: title, id, callback.
// The values are defined at the add_settings_section() function.
function chat_schedule_options_section_cb( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Enter the hours the Help Desk chat service is available each day', 'chat_schedule' ); ?></p>
	<?php
}
 
// thing1 field cb
 
// field callbacks can accept an $args parameter, which is an array.
// $args is defined at the add_settings_field() function.
// wordpress has magic interaction with the following keys: label_for, class.
// the "label_for" key value is used for the "for" attribute of the <label>.
// the "class" key value is used for the "class" attribute of the <tr> containing the field.
// you can add custom key value pairs to be used inside your callbacks.
function settings_cb( $args ) {
	// get the value of the setting we registered with register_setting()
	$options = get_option( 'chat_schedule_options' );
	// output the field
	?>

		<select id="<?php echo esc_attr( $args['label_for'] ); ?>"
			data-custom="<?php echo esc_attr( $args['chat_schedule_custom_data'] ); ?>"
			name="chat_schedule_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
		>
			<option value="0000" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0000', false ) ) : ( '' ); ?>> 
				<?php esc_html_e( '0000', 'chat_schedule' ); ?>
			</option>
			<option value="0100" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0100', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0100', 'chat_schedule' ); ?>
			</option>
			<option value="0200" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0200', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0200', 'chat_schedule' ); ?>
			</option>
			<option value="0300" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0300', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0300', 'chat_schedule' ); ?>
			</option>
			<option value="0400" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0400', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0400', 'chat_schedule' ); ?>
			</option>
			<option value="0500" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0500', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0500', 'chat_schedule' ); ?>
			</option>
			<option value="0600" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0600', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0600', 'chat_schedule' ); ?>
			</option>
			<option value="0700" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0700', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0700', 'chat_schedule' ); ?>
			</option>
			<option value="0800" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0800', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0800', 'chat_schedule' ); ?>
			</option>
			<option value="0900" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '0900', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '0900', 'chat_schedule' ); ?>
			</option>
			<option value="1000" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1000', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1000', 'chat_schedule' ); ?>
			</option>
			<option value="1100" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1100', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1100', 'chat_schedule' ); ?>
			</option>
			<option value="1200" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1200', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1200', 'chat_schedule' ); ?>
			</option>
			<option value="1300" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1300', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1300', 'chat_schedule' ); ?>
			</option>
			<option value="1400" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1400', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1400', 'chat_schedule' ); ?>
			</option>
			<option value="1500" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1500', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1500', 'chat_schedule' ); ?>
			</option>
			<option value="1600" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1600', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1600', 'chat_schedule' ); ?>
			</option>
			<option value="1700" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1700', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1700', 'chat_schedule' ); ?>
			</option>
			<option value="1800" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1800', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1800', 'chat_schedule' ); ?>
			</option>
			<option value="1900" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1900', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '1900', 'chat_schedule' ); ?>
			</option>
			<option value="2000" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '2000', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '2000', 'chat_schedule' ); ?>
			</option>
			<option value="2100" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '2100', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '2100', 'chat_schedule' ); ?>
			</option>
			<option value="2200" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '2200', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '2200', 'chat_schedule' ); ?>
			</option>
			<option value="2300" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '2300', false ) ) : ( '' ); ?>>
				<?php esc_html_e( '2300', 'chat_schedule' ); ?>
			</option>

		</select>

	<?php
}

 
// Top level menu
function chat_schedule_options_page() {
	// add top level menu page
	add_menu_page(
	'Boise State OIT Help Desk Chat Hours',
	'Help Desk Chat Hours',
	'manage_options',
	'chat_schedule',
	'chat_schedule_options_page_html'
	);
}

// Register chat_schedule_options_page to the admin_menu action hook
add_action( 'admin_menu', 'chat_schedule_options_page' );
 
// Top level menu callback functions
function chat_schedule_options_page_html() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
 
	// add error/update messages
 
	// check if the user have submitted the settings
	// wordpress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'chat_schedule_messages', 'chat_schedule_message', __( 'Settings Saved', 'chat_schedule' ), 'updated' );
	}
 
	// show error/update messages
	settings_errors( 'chat_schedule_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
		<?php
			settings_fields( 'chat_schedule_options_group' );
			// output setting sections and their fields
		// (sections are registered for "dl_test", each field is registered to a specific section)
		do_settings_sections( 'chat_schedule' );
		// output save settings button
		submit_button( 'Save Settings' );
	?>
	</form>
	</div>
	<?php
}

