<?php
/**
 * Custom user settings
 * 
 * Add user profile fields for
 * - enabling or disabling help notices
 * - enabling or disabling mailform
 * 
 * @package rabe
 * @since version 1.0.0
 * @link https://paulund.co.uk/add-custom-user-fields-profile-page
 */

class Rabe_User_Settings {
    public function __construct() {
        add_action( 'personal_options_update', array( $this, 'update_rabe_profile' ) );
        add_action( 'edit_user_profile_update', array( $this, 'update_rabe_profile' ) );
        add_action( 'show_user_profile', array( $this, 'add_rabe_profile_fields' ) );
        add_action( 'edit_user_profile', array( $this, 'add_rabe_profile_fields' ) );
    }

	/**
	 * Add new custom field to the profile page
	 *
	 * @param $profileuser
	 */
	public function add_rabe_profile_fields( $user ) {
		$rabe_user_settings = get_user_meta( $user->ID, 'rabe_user_settings', true );

		// Check if user clicked on "remove display notices"
		$alert = ( isset( $_GET['no_help'] ) ) ? (int) filter_input( INPUT_GET, 'no_help', FILTER_SANITIZE_NUMBER_INT ) : '';
		$alert = ( 1 === $alert ) ? ' style="background:#FFE4E1;padding:5px;border: 1px solid red;"' : '';
		
		// Get values for help notices setting
		$help_value = ( isset( $rabe_user_settings['show_help_notices'] ) ) ? $rabe_user_settings['show_help_notices'] : 1;
		$help_checked = ( 1 === $help_value ) ? 'checked' : '';
		
		// Get value for mailto
		$mailto_value = ( isset( $rabe_user_settings['allow_mailto'] ) ) ? $rabe_user_settings['allow_mailto'] : 1;
		$mailto_checked = ( 1 === $mailto_value ) ? 'checked' : '';
		?>
			<h2 id="rabe-user-settings"><?php echo __( 'Custom user settings', 'rabe' ); ?></h2>
			<table class="form-table">
			<tr id="user-settings-help-notices" <?php echo $alert; ?>>
				<th>
					<label for="help_notices_checkbox"><?php echo __( 'Display help notices', 'rabe' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="help_notices_checkbox" id="help-notices" value="1" <?php echo $help_checked ?>>
				</td>
			</tr>
			<tr id="user-settings-allow-mailto" <?php echo $alert; ?>>
				<th>
					<label for="mailto_checkbox"><?php echo __( 'Allow mails via form to user', 'rabe' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="mailto_checkbox" id="allow-mailto" value="1" <?php echo $mailto_checked ?>>
				</td>
			</tr>
			</table>
		<?php
	}

	/**
	 * Update new field on the user profile page
	 *
	 * @param $user_id
	 */
	public function update_rabe_profile( $user_id )	{
		$user_settings['show_help_notices'] = 0;
		$user_settings['allow_mailto'] = 0;

        if ( isset( $_POST['help_notices_checkbox'] ) ) {
            $user_settings['show_help_notices'] = (int) $_POST['help_notices_checkbox'];
        }
        if ( isset( $_POST['mailto_checkbox'] ) ) {
            $user_settings['allow_mailto'] = (int) $_POST['mailto_checkbox'];
        }

        update_user_meta( $user_id, 'rabe_user_settings', $user_settings );
	}
}
$rabe_user = new Rabe_User_Settings;


/**
 * Enable help notices by default for non administrators upon creating user
 * 
 * @package rabe
 * @since version 1.0.0
 * @params int $user_id User ID
 */
function set_help_notices( $user_id ) {

    if ( ! user_can( $user_id, 'manage_options' ) ) {
		$set_help_notices = array( 'show_help_notices' => 1 );
		update_user_meta( $user_id, 'rabe_user_settings', $set_help_notices );
	}
}
add_action( 'user_register', 'set_help_notices' );


/**
 * Enable mailto links by default for non everybody upon creating user
 * 
 * @package rabe
 * @since version 1.0.0
 * @params int $user_id User ID
 */
function set_allow_mailto( $user_id ) {
		$allow_mailto_setting = array( 'allow_mailto' => 1 );
		update_user_meta( $user_id, 'rabe_user_settings', $allow_mailto_setting );
}
add_action( 'user_register', 'set_allow_mailto' );

?>
