<?php
/**
 * Settings page for rabe theme where social links, special pages and 
 * more can be set.
 * 
 * Generated with http://jeremyhixon.com/wp-tools/option-page/
 * 
 * @package rabe
 */
 
/**
 * Adds capability for editors to change custom theme settings.
 *
 * @since version 1.0.0
 * @package rabe
 */
function rabe_capability(){
	$cap = 'edit_pages';
	return $cap;
}
add_filter( 'option_page_capability_rabe_option_group', 'rabe_capability');

/**
 * Creates the settings page for rabe theme
 *
 * @since version 1.0.0
 * @package rabe
 */
class Rabe {
	private $rabe_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'rabe_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'rabe_page_init' ) );
	}

	public function rabe_add_plugin_page() {
		add_theme_page(
			__( 'RaBe Theme Settings', 'rabe'), // page_title
			__( 'RaBe settings', 'rabe'), // menu_title
			'edit_pages', // capability
			'rabe_settings', // menu_slug
			array( $this, 'rabe_create_admin_page' ), // function
			'dashicons-admin-generic', // icon_url
			99 // position
		);
	}

	public function rabe_create_admin_page() {
		$this->rabe_options = get_option( 'rabe_option_name' ); ?>

		<div class="wrap">
			<h2><?php echo __( 'RaBe Settings', 'rabe'); ?></h2>
			<p><?php echo __( 'Settings for rabe theme', 'rabe'); ?></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'rabe_option_group' );
					do_settings_sections( 'rabe-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	/**
	 * Initializes all the fields in the setting page
	 *
	 * @since version 1.0.0
	 * @package rabe
	 */	
	public function rabe_page_init() {
		register_setting(
			'rabe_option_group', // option_group
			'rabe_option_name', // option_name
			array( $this, 'rabe_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'rabe_setting_section', // id
			__( 'Settings', 'rabe'), // title
			array( $this, 'rabe_section_info' ), // callback
			'rabe-admin' // page
		);

		/* Field for meta description of site */
		add_settings_field(
			'meta_description', // id
			__( 'Meta description', 'rabe'), // title
			array( $this, 'meta_description_callback' ), // callback
			'rabe-admin', // page
			'rabe_setting_section' // section
		);

		/* Contact mail address field */
		add_settings_field(
			'email',
			__( 'E-Mail', 'rabe'),
			array( $this, 'email_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Contact mail address field */
		add_settings_field(
			'phone',
			__( 'Phone', 'rabe'),
			array( $this, 'phone_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Facebook URL */
		add_settings_field(
			'facebookurl',
			__( 'Facebook', 'rabe'),
			array( $this, 'facebookurl_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Twitter URL */
		add_settings_field(
			'twitterurl',
			__( 'Twitter', 'rabe'),
			array( $this, 'twitterurl_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Soundcloud URL */
		add_settings_field(
			'soundcloudurl',
			__( 'Soundcloud', 'rabe'),
			array( $this, 'soundcloudurl_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Mixcloud URL */
		add_settings_field(
			'mixcloudurl',
			__( 'Mixcloud', 'rabe'),
			array( $this, 'mixcloudurl_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Youtube URL */
		add_settings_field(
			'youtubeurl',
			__( 'Youtube', 'rabe'),
			array( $this, 'youtubeurl_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Vimeo URL */
		add_settings_field(
			'vimeourl',
			__( 'Vimeo', 'rabe'),
			array( $this, 'vimeourl_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Textarea for help notices in post editor */
		add_settings_field(
			'post_help_notice',
			__( 'Help notices for posts', 'rabe'),
			array( $this, 'post_help_notice_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Textarea for help notices in broadcast editor */
		add_settings_field(
			'broadcast_help_notice',
			__( 'Help notices for broadcasts', 'rabe'),
			array( $this, 'broadcast_help_notice_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		if ( function_exists( 'eventorganiser_load_textdomain' ) ) {
			
			/* Textarea for help notices in events editor */
			add_settings_field(
				'event_help_notice',
				__( 'Help notices for events', 'rabe'),
				array( $this, 'event_help_notice_callback' ),
				'rabe-admin',
				'rabe_setting_section'
			);
			
			/* Mail address for notification about pending events */
			add_settings_field(
				'pending_event_notification_email',
				__( 'Mail notification on pending event', 'rabe'),
				array( $this, 'pending_event_notification_email_callback' ),
				'rabe-admin',
				'rabe_setting_section'
			);
		}
		
		/* Field for URL of page with receiving options */
		add_settings_field(
			'receiving_page',
			__( 'Receiving options page', 'rabe'),
			array( $this, 'receiving_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for URL of page with contacts */
		add_settings_field(
			'contact_page',
			__( 'Contact page', 'rabe'),
			array( $this, 'contact_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for URL of impressum page */
		add_settings_field(
			'impressum_page',
			__( 'Impressum page', 'rabe'),
			array( $this, 'impressum_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for URL of support page */
		add_settings_field(
			'support_page',
			__( 'Broadcast-Support page', 'rabe'),
			array( $this, 'support_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for URL of mailcontact page */
		add_settings_field(
			'mailcontact_page',
			__( 'Mail contact page', 'rabe'),
			array( $this, 'mailcontact_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for fallback broadcast, normally a sound pool */
		add_settings_field(
			'fallback_broadcast',
			__( 'Which is the fallback/default broadcast?', 'rabe'),
			array( $this, 'fallback_broadcast_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for info broadcast */
		add_settings_field(
			'info_broadcast',
			__( 'Which is info broadcast?', 'rabe'),
			array( $this, 'info_broadcast_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for "general" broadcast, when not specific broadcast can be specified */
		add_settings_field(
			'general_broadcast',
			__( 'Which is the general broadcast?', 'rabe'),
			array( $this, 'general_broadcast_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Field for short name of site which is displayed in mobile view */
		add_settings_field(
			'short_blogname',
			__( 'Short name of site title in top menu', 'rabe'),
			array( $this, 'short_blogname_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Field for specifying quantity of specialposts on frontpage */
		add_settings_field(
			'specialposts_per_page',
			__( 'Maximum of specialposts on the front page?', 'rabe'),
			array( $this, 'specialposts_per_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Field for not_in_header category */
		add_settings_field(
			'not_in_header',
			__( 'Which category is not shown in header?', 'rabe'),
			array( $this, 'not_in_header_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		
		/* Livestream URL */
		add_settings_field(
			'stream_url',
			__( 'Live-Stream URL', 'rabe'),
			array( $this, 'stream_url_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		/* Postfinance E-Payment ID*/
		add_settings_field(
			'postfinance_pspid',
			__( 'Postfinance E-Payment - ID', 'rabe'),
			array( $this, 'postfinance_pspid_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		/* Postfinance E-Payment URL*/
		add_settings_field(
			'postfinance_url',
			__( 'Postfinance E-Payment - URL', 'rabe'),
			array( $this, 'postfinance_url_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		/* Postfinance E-Payment SHA SIGN IN*/
		add_settings_field(
			'postfinance_shasign',
			__( 'Postfinance E-Payment - SHA-Key', 'rabe'),
			array( $this, 'postfinance_shasign_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		/* Paypal user */
		add_settings_field(
			'paypal_user',
			__( 'Paypal-User', 'rabe'),
			array( $this, 'paypal_user_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		/* Paypal payment URL*/
		add_settings_field(
			'paypal_url',
			__( 'Paypal-URL', 'rabe'),
			array( $this, 'paypal_url_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

		/* Field for URL of payment thankyou page */
		add_settings_field(
			'payment_thankyou_page',
			__( 'Displayed page after successful payment?', 'rabe'),
			array( $this, 'payment_thankyou_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		/* Field for URL of payment error page */
		add_settings_field(
			'payment_error_page',
			__( 'Displayed page after failed payment', 'rabe'),
			array( $this, 'payment_error_page_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		if ( class_exists( 'siContactForm' ) ) {
			/* Form for supporting a broadcast */
			add_settings_field(
				'support_form',
				__( 'Supporter form', 'rabe'),
				array( $this, 'support_form_callback' ),
				'rabe-admin',
				'rabe_setting_section'
			);
			
			/* Form for sending Mails to broadcasts or broadcast members */
			add_settings_field(
				'mail_form',
				__( 'Mail form', 'rabe'),
				array( $this, 'mail_form_callback' ),
				'rabe-admin',
				'rabe_setting_section'
			);
			
			/* Form for membership */
			add_settings_field(
				'membership_form',
				__( 'Membership form', 'rabe'),
				array( $this, 'membership_form_callback' ),
				'rabe-admin',
				'rabe_setting_section'
			);
			
			/* Form for donation */
			add_settings_field(
				'donation_form',
				__( 'Donation form', 'rabe'),
				array( $this, 'donation_form_callback' ),
				'rabe-admin',
				'rabe_setting_section'
			);
			
		}
		/* Interval of songticker updates */
		add_settings_field(
			'rabe_songticker_interval',
			__( 'Interval of songticker updates', 'rabe'),
			array( $this, 'songticker_interval_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);
		/* Songticker URL*/
		add_settings_field(
			'rabe_songticker_url',
			__( 'Songticker URL', 'rabe'),
			array( $this, 'songticker_url_callback' ),
			'rabe-admin',
			'rabe_setting_section'
		);

	}

	/**
	 * Sanitizes the settings
	 *
	 * @since version 1.0.0
	 * @package rabe
	 */
	public function rabe_sanitize( $input ) {
		$sanitary_values = array();
		if ( isset( $input['meta_description'] ) ) {
			$sanitary_values['meta_description'] = esc_textarea( $input['meta_description'] );
		}

		if ( isset( $input['email'] ) ) {
			$sanitary_values['email'] = sanitize_email( $input['email'] );
		}

		if ( isset( $input['phone'] ) ) {
			$sanitary_values['phone'] = sanitize_text_field( $input['phone'] );
		}

		if ( isset( $input['facebookurl'] ) ) {
			$sanitary_values['facebookurl'] = esc_url( $input['facebookurl'] );
		}
		
		if ( isset( $input['twitterurl'] ) ) {
			$sanitary_values['twitterurl'] = esc_url( $input['twitterurl'] );
		}
		
		if ( isset( $input['soundcloudurl'] ) ) {
			$sanitary_values['soundcloudurl'] = esc_url( $input['soundcloudurl'] );
		}
		
		if ( isset( $input['mixcloudurl'] ) ) {
			$sanitary_values['mixcloudurl'] = esc_url( $input['mixcloudurl'] );
		}

		if ( isset( $input['youtubeurl'] ) ) {
			$sanitary_values['youtubeurl'] = esc_url( $input['youtubeurl'] );
		}

		if ( isset( $input['vimeourl'] ) ) {
			$sanitary_values['vimeourl'] = esc_url( $input['vimeourl'] );
		}

		if ( isset( $input['post_help_notice'] ) ) {
			$sanitary_values['post_help_notice'] = wp_kses_post( $input['post_help_notice'] );
		}
		
		if ( isset( $input['event_help_notice'] ) ) {
			$sanitary_values['event_help_notice'] = wp_kses_post( $input['event_help_notice'] );
		}
		
		if ( isset( $input['broadcast_help_notice'] ) ) {
			$sanitary_values['broadcast_help_notice'] = wp_kses_post( $input['broadcast_help_notice'] );
		}
		
		if ( isset( $input['pending_event_notification_email'] ) ) {
			$sanitary_values['pending_event_notification_email'] = sanitize_email( $input['pending_event_notification_email'] );
		}
		
		if ( isset( $input['receiving_page'] ) ) {
			$sanitary_values['receiving_page'] = absint( $input['receiving_page'] );
		}
		
		if ( isset( $input['contact_page'] ) ) {
			$sanitary_values['contact_page'] = absint( $input['contact_page'] );
		}
		
		if ( isset( $input['impressum_page'] ) ) {
			$sanitary_values['impressum_page'] = absint( $input['impressum_page'] );
		}
		
		if ( isset( $input['support_page'] ) ) {
			$sanitary_values['support_page'] = absint( $input['support_page'] );
		}
		
		if ( isset( $input['mailcontact_page'] ) ) {
			$sanitary_values['mailcontact_page'] = absint( $input['mailcontact_page'] );
		}
		
		if ( isset( $input['fallback_broadcast'] ) ) {
			$sanitary_values['fallback_broadcast'] = absint( $input['fallback_broadcast'] );
		}
		
		if ( isset( $input['general_broadcast'] ) ) {
			$sanitary_values['general_broadcast'] = absint( $input['general_broadcast'] );
		}

		if ( isset( $input['info_broadcast'] ) ) {
			$sanitary_values['info_broadcast'] = absint( $input['info_broadcast'] );
		}
		
		if ( isset( $input['short_blogname'] ) ) {
			$sanitary_values['short_blogname'] = sanitize_text_field( $input['short_blogname'] );
		}
		
		if ( isset( $input['specialposts_per_page'] ) ) {
			$sanitary_values['specialposts_per_page'] = absint( $input['specialposts_per_page'] );
		}
		
		if ( isset( $input['not_in_header'] ) ) {
			$sanitary_values['not_in_header'] = absint( $input['not_in_header'] );
		}

		if ( isset( $input['stream_url'] ) ) {
			$sanitary_values['stream_url'] = esc_url( $input['stream_url'] );
		}

		if ( isset( $input['postfinance_url'] ) ) {
			$sanitary_values['postfinance_url'] = esc_url( $input['postfinance_url'] );
		}

		if ( isset( $input['postfinance_pspid'] ) ) {
			$sanitary_values['postfinance_pspid'] = sanitize_text_field( $input['postfinance_pspid'] );
		}

		if ( isset( $input['postfinance_shasign'] ) ) {
			$sanitary_values['postfinance_shasign'] = sanitize_text_field( $input['postfinance_shasign'] );
		}

		if ( isset( $input['paypal_url'] ) ) {
			$sanitary_values['paypal_url'] = esc_url( $input['paypal_url'] );
		}

		if ( isset( $input['paypal_user'] ) ) {
			$sanitary_values['paypal_user'] = sanitize_email( $input['paypal_user'] );
		}
		
		if ( isset( $input['payment_thankyou_page'] ) ) {
			$sanitary_values['payment_thankyou_page'] = absint( $input['payment_thankyou_page'] );
		}
		
		if ( isset( $input['payment_error_page'] ) ) {
			$sanitary_values['payment_error_page'] = absint( $input['payment_error_page'] );
		}
		
		if ( isset( $input['support_form'] ) ) {
			$sanitary_values['support_form'] = absint( $input['support_form'] );
		}
		
		if ( isset( $input['mail_form'] ) ) {
			$sanitary_values['mail_form'] = absint( $input['mail_form'] );
		}
		
		if ( isset( $input['membership_form'] ) ) {
			$sanitary_values['membership_form'] = absint( $input['membership_form'] );
		}
		
		if ( isset( $input['donation_form'] ) ) {
			$sanitary_values['donation_form'] = absint( $input['donation_form'] );
		}
		
		if ( isset( $input['rabe_songticker_interval'] ) ) {
			$sanitary_values['rabe_songticker_interval'] = absint( $input['rabe_songticker_interval'] );
		}

		if ( isset( $input['rabe_songticker_url'] ) ) {
			$sanitary_values['rabe_songticker_url'] = esc_url( $input['rabe_songticker_url'] );
		}

		return $sanitary_values;
	}


	/**
	 * Callbacks for setting fields
	 */
	public function rabe_section_info() {
		echo '<h3>' . __( 'Site Settings', 'rabe' ) . '</h3>';
	}

	public function meta_description_callback() {
		printf(
			'<textarea class="text" cols="60" rows="3" name="rabe_option_name[meta_description]" id="meta_description">%s</textarea>
			<p class="description">' . __( 'Write a short description of your site. This will be displayed by search engines.', 'rabe' ) . '</p>',
			isset( $this->rabe_options['meta_description'] ) ? esc_attr( $this->rabe_options['meta_description'] ) : ''
		);
	}

	public function phone_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[phone]" id="phone" value="%s">',
			isset( $this->rabe_options['phone'] ) ? esc_attr( $this->rabe_options['phone'] ) : ''
		);
	}

	public function email_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[email]" id="email" value="%s">',
			isset( $this->rabe_options['email'] ) ? esc_attr( $this->rabe_options['email'] ) : ''
		);
	}

	public function facebookurl_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[facebookurl]" id="facebookurl" value="%s">',
			isset( $this->rabe_options['facebookurl'] ) ? esc_attr( $this->rabe_options['facebookurl'] ) : ''
		);
	}

	public function twitterurl_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[twitterurl]" id="twitterurl" value="%s">',
			isset( $this->rabe_options['twitterurl'] ) ? esc_attr( $this->rabe_options['twitterurl'] ) : ''
		);
	}

	public function soundcloudurl_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[soundcloudurl]" id="soundcloudurl" value="%s">',
			isset( $this->rabe_options['soundcloudurl'] ) ? esc_attr( $this->rabe_options['soundcloudurl'] ) : ''
		);
	}

	public function mixcloudurl_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[mixcloudurl]" id="mixcloudurl" value="%s">',
			isset( $this->rabe_options['mixcloudurl'] ) ? esc_attr( $this->rabe_options['mixcloudurl'] ) : ''
		);
	}

	public function youtubeurl_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[youtubeurl]" id="youtubeurl" value="%s">',
			isset( $this->rabe_options['youtubeurl'] ) ? esc_attr( $this->rabe_options['youtubeurl'] ) : ''
		);
	}

	public function vimeourl_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[vimeourl]" id="vimeourl" value="%s">',
			isset( $this->rabe_options['vimeourl'] ) ? esc_attr( $this->rabe_options['vimeourl'] ) : ''
		);
	}
	
	public function post_help_notice_callback() {
		$post_help_notice_editor = array( 'media_buttons' => false, 'textarea_name' => 'rabe_option_name[post_help_notice]', 'teeny' => true );
		$post_help_notice_content = isset( $this->rabe_options['post_help_notice'] ) ? wp_kses_post( $this->rabe_options['post_help_notice'] ) : '';
		wp_editor($post_help_notice_content, 'post_help_notice', $post_help_notice_editor);
		echo '<p class="description">' . __( 'Help notice for entering a post', 'rabe' ) . '</p>';
	}
	
	public function event_help_notice_callback() {
		$event_help_notice_editor = array( 'media_buttons' => false, 'textarea_name' => 'rabe_option_name[event_help_notice]', 'teeny' => true );
		$event_help_notice_content = isset( $this->rabe_options['event_help_notice'] ) ? wp_kses_post( $this->rabe_options['event_help_notice'] ) : '';
		wp_editor($event_help_notice_content, 'event_help_notice', $event_help_notice_editor);
		echo '<p class="description">' . __( 'Help notice for entering an event', 'rabe' ) . '</p>';
	}
	
	public function broadcast_help_notice_callback() {
		$broadcast_help_notice_editor = array( 'media_buttons' => false, 'textarea_name' => 'rabe_option_name[broadcast_help_notice]', 'teeny' => true );
		$broadcast_help_notice_content = isset( $this->rabe_options['broadcast_help_notice'] ) ? wp_kses_post( $this->rabe_options['broadcast_help_notice'] ) : '';
		wp_editor($broadcast_help_notice_content, 'broadcast_help_notice', $broadcast_help_notice_editor);
		echo '<p class="description">' . __( 'Help notice for entering a broadcast', 'rabe' ) . '</p>';
	}
	
	public function pending_event_notification_email_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[pending_event_notification_email]" id="pending_event_notification_email" value="%1s">
			<p class="description">%2s</p>',
			isset( $this->rabe_options['pending_event_notification_email'] ) ? esc_attr( $this->rabe_options['pending_event_notification_email'] ) : '',
			__( 'Mail address which will be notified upon a pending event', 'rabe' )
		);
	}
	
	public function receiving_page_callback() {
		$receiving_page_id = isset( $this->rabe_options['receiving_page'] ) ? $this->rabe_options['receiving_page'] : 0;
		$receiving_page_args = array(
			'name'       => 'rabe_option_name[receiving_page]',
			'selected' => $receiving_page_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
			'option_none_value'	=> null
		);
		wp_dropdown_pages( $receiving_page_args );
		echo '<p class="description">' . __( 'Which page lists all receiving options of the radio?', 'rabe' ) . '</p>';

	}
	
	public function contact_page_callback() {
		$contact_page_id = isset( $this->rabe_options['contact_page'] ) ? $this->rabe_options['contact_page'] : 0;
		$contact_page_args = array(
			'name'				=> 'rabe_option_name[contact_page]',
			'selected'			=> $contact_page_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
			'option_none_value'	=> null
		);
		wp_dropdown_pages( $contact_page_args );
	}
	
	public function impressum_page_callback() {
		$impressum_page_id = isset( $this->rabe_options['impressum_page'] ) ? $this->rabe_options['impressum_page'] : 0;
		$impressum_page_args = array(
			'name'				=> 'rabe_option_name[impressum_page]',
			'selected'			=> $impressum_page_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
			'option_none_value'	=> null
		);
		wp_dropdown_pages( $impressum_page_args );

	}
	
	public function support_page_callback() {
		$support_page_id = isset( $this->rabe_options['support_page'] ) ? $this->rabe_options['support_page'] : 0;
		$support_page_args = array(
			'name'				=> 'rabe_option_name[support_page]',
			'selected'			=> $support_page_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
			'option_none_value'	=> null
		);
		wp_dropdown_pages( $support_page_args );
		echo '<p class="description">' . __( 'Set the page where you can support a specific broadcast.', 'rabe' ) . '</p>';

	}
	
	public function mailcontact_page_callback() {
		$mailcontact_page_id = isset( $this->rabe_options['mailcontact_page'] ) ? $this->rabe_options['mailcontact_page'] : 0;
		$mailcontact_page_args = array(
			'name'				=> 'rabe_option_name[mailcontact_page]',
			'selected'			=> $mailcontact_page_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
			'option_none_value'	=> null
		);
		wp_dropdown_pages( $mailcontact_page_args );
		echo '<p class="description">' . __( 'Set the page for sending a mail to a broadcast or a broadcaster.', 'rabe' ) . '</p>';

	}
	
	public function fallback_broadcast_callback() {
		$fallback_broadcast_id = isset( $this->rabe_options['fallback_broadcast'] ) ? $this->rabe_options['fallback_broadcast'] : 0;
		$fallback_broadcast_args = array(
			'name'				=> 'rabe_option_name[fallback_broadcast]',
			'selected'			=> $fallback_broadcast_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
		    'taxonomy'			=> 'broadcast',
		    'hide_empty'		=> false,
		    'orderby'			=> 'name',
			'option_none_value'	=> null
		);
		wp_dropdown_categories( $fallback_broadcast_args );
		echo '<p class="description">' . __( 'Set a fallback broadcast which is shown in the player, when no real broadcast is scheduled.', 'rabe' ) . '</p>';
	}
	
	public function general_broadcast_callback() {
		$general_broadcast_id = isset( $this->rabe_options['general_broadcast'] ) ? $this->rabe_options['general_broadcast'] : 0;
		$general_broadcast_args = array(
			'name'				=> 'rabe_option_name[general_broadcast]',
			'selected'			=> $general_broadcast_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
		    'taxonomy'			=> 'broadcast',
		    'hide_empty'		=> false,
		    'orderby'			=> 'name',
			'option_none_value'	=> null
		);
		wp_dropdown_categories( $general_broadcast_args );
		echo '<p class="description">' . __( 'Set a general broadcast for all broadcasts with no explicit broadcast name.', 'rabe' ) . '</p>';
	}

	public function info_broadcast_callback() {
		$info_broadcast_id = isset( $this->rabe_options['info_broadcast'] ) ? $this->rabe_options['info_broadcast'] : 0;
		$info_broadcast_args = array(
			'name'				=> 'rabe_option_name[info_broadcast]',
			'selected'			=> $info_broadcast_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
		    'taxonomy'			=> 'broadcast',
		    'hide_empty'		=> false,
		    'orderby'			=> 'name',
			'option_none_value'	=> null
		);
		wp_dropdown_categories( $info_broadcast_args );
		echo '<p class="description">' . __( 'Set the news magazine.', 'rabe' ) . '</p>';

	}
	
	public function short_blogname_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[short_blogname]" id="short_blogname" value="%s">
			<p class="description">' . __( 'The short name of the site will be displayed in the header of the mobile view.', 'rabe' ) . '</p>',
			isset( $this->rabe_options['short_blogname'] ) ? esc_attr( $this->rabe_options['short_blogname'] ) : ''
		);
	}

	public function specialposts_per_page_callback() {
		printf(
			'<input class="regular-text" type="number" min="0" max="5" name="rabe_option_name[specialposts_per_page]" id="specialposts_per_page" value="%s">
			<p class="description">' . __( 'How many specialposts do you want to show on the frontpage?', 'rabe' ) . '</p>',
			isset( $this->rabe_options['specialposts_per_page'] ) ? absint( $this->rabe_options['specialposts_per_page'] ) : ''
		);
	}

	public function not_in_header_callback() {
		$not_in_header = (int) get_term_by( 'slug', 'not-in-header', 'category' )->term_id;
		$not_in_header_id = isset( $this->rabe_options['not_in_header'] ) ? $this->rabe_options['not_in_header'] : $not_in_header;
		$not_in_header_args = array(
			'name'				=> 'rabe_option_name[not_in_header]',
			'selected'			=> $not_in_header_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
		    'taxonomy'			=> 'category',
		    'hide_empty'		=> false,
		    'orderby'			=> 'name',
			'option_none_value'	=> null
		);
		wp_dropdown_categories( $not_in_header_args );

	}

	public function stream_url_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[stream_url]" id="stream_url" value="%s">',
			isset( $this->rabe_options['stream_url'] ) ? esc_url( $this->rabe_options['stream_url'] ) : ''
		);
	}
	
	public function postfinance_url_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[postfinance_url]" id="postfinance_url" value="%s">',
			isset( $this->rabe_options['postfinance_url'] ) ? esc_url( $this->rabe_options['postfinance_url'] ) : ''
		);
	}
	
	public function postfinance_pspid_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[postfinance_pspid]" id="postfinance_pspid" value="%s">
			<p class="description">' . __( 'ID for Postfinance E-Payment', 'rabe' ) . '</p>',
			isset( $this->rabe_options['postfinance_pspid'] ) ? esc_attr( $this->rabe_options['postfinance_pspid'] ) : ''
		);
	}
	
	public function postfinance_shasign_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[postfinance_shasign]" id="postfinance_shasign" value="%s">
			<p class="description">' . __( 'SHA-Key for Postfinance E-Payment', 'rabe' ) . '</p>',
			isset( $this->rabe_options['postfinance_shasign'] ) ? esc_attr( $this->rabe_options['postfinance_shasign'] ) : ''
		);
	}

	public function paypal_url_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[paypal_url]" id="paypal_url" value="%s">',
			isset( $this->rabe_options['paypal_url'] ) ? esc_url( $this->rabe_options['paypal_url'] ) : ''
		);
	}
	
	public function paypal_user_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[paypal_user]" id="paypal_user" value="%s">
			<p class="description">' . __( 'User for Paypal payment', 'rabe' ) . '</p>',
			isset( $this->rabe_options['paypal_user'] ) ? esc_attr( $this->rabe_options['paypal_user'] ) : ''
		);
	}
	
	public function payment_thankyou_page_callback() {
		$payment_thankyou_page_id = isset( $this->rabe_options['payment_thankyou_page'] ) ? $this->rabe_options['payment_thankyou_page'] : 0;
		$payment_thankyou_page_args = array(
			'name'				=> 'rabe_option_name[payment_thankyou_page]',
			'selected'			=> $payment_thankyou_page_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
			'option_none_value'	=> null
		);
		wp_dropdown_pages( $payment_thankyou_page_args );
	}
	
	public function payment_error_page_callback() {
		$payment_error_page_id = isset( $this->rabe_options['payment_error_page'] ) ? $this->rabe_options['payment_error_page'] : 0;
		$payment_error_page_args = array(
			'name'				=> 'rabe_option_name[payment_error_page]',
			'selected'			=> $payment_error_page_id,
		    'show_option_none'	=> __( 'None', 'rabe' ),
			'option_none_value'	=> null
		);
		wp_dropdown_pages( $payment_error_page_args );
	}
	

	public function support_form_callback() {
		$support_form_id = isset( $this->rabe_options['support_form'] ) ? $this->rabe_options['support_form'] : 0;
		require_once ABSPATH . 'wp-content/plugins/si-contact-form/includes/class-fscf-util.php';
		$form_options = FSCF_Util::get_global_options();
		echo '<select id="rabe_option_name[support_form]" name="rabe_option_name[support_form]">';
		foreach ( $form_options['form_list'] as $key => $val ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( (int) $support_form_id == $key ) {
				echo ' selected="selected"';
			}
			echo '>' .sprintf( __( 'Form %d: %s', 'rabe' ), esc_html( $key ), esc_html( $val ) ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . __( 'Which form is on the page for supporting a broadcast?', 'rabe' ) . '</p>';
	}
	
	public function mail_form_callback() {
		$mail_form_id = isset( $this->rabe_options['mail_form'] ) ? $this->rabe_options['mail_form'] : 0;
		require_once ABSPATH . 'wp-content/plugins/si-contact-form/includes/class-fscf-util.php';
		$form_options = FSCF_Util::get_global_options();
		echo '<select id="rabe_option_name[mail_form]" name="rabe_option_name[mail_form]"><option value="">' . __( 'None', 'rabe' ) .'</option>';
		foreach ( $form_options['form_list'] as $key => $val ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( (int) $mail_form_id == $key ) {
				echo ' selected="selected"';
			}
			echo '>' .sprintf( __( 'Form %d: %s', 'rabe' ), esc_html( $key ), esc_html( $val ) ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . __( 'Which form for the mail contact page?', 'rabe' ) . '</p>';

	}
	
	public function membership_form_callback() {
		$membership_form_id = isset( $this->rabe_options['membership_form'] ) ? $this->rabe_options['membership_form'] : 0;
		require_once ABSPATH . 'wp-content/plugins/si-contact-form/includes/class-fscf-util.php';
		$form_options = FSCF_Util::get_global_options();
		echo '<select id="rabe_option_name[membership_form]" name="rabe_option_name[membership_form]"><option value="">' . __( 'None', 'rabe' ) .'</option>';
		foreach ( $form_options['form_list'] as $key => $val ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( (int) $membership_form_id == $key ) {
				echo ' selected="selected"';
			}
			echo '>' .sprintf( __( 'Form %d: %s', 'rabe' ), esc_html( $key ), esc_html( $val ) ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . __( 'Which form is on the Membership page?', 'rabe' ) . '</p>';

	}
	
	public function donation_form_callback() {
		$donation_form_id = isset( $this->rabe_options['donation_form'] ) ? $this->rabe_options['donation_form'] : 0;
		require_once ABSPATH . 'wp-content/plugins/si-contact-form/includes/class-fscf-util.php';
		$form_options = FSCF_Util::get_global_options();
		echo '<select id="rabe_option_name[donation_form]" name="rabe_option_name[donation_form]"><option value="">' . __( 'None', 'rabe' ) .'</option>';
		foreach ( $form_options['form_list'] as $key => $val ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( (int) $donation_form_id == $key ) {
				echo ' selected="selected"';
			}
			echo '>' .sprintf( __( 'Form %d: %s', 'rabe' ), esc_html( $key ), esc_html( $val ) ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . __( 'Which form is on the donation page?', 'rabe' ) . '</p>';

	}
	
	public function songticker_interval_callback() {
		printf(
			'<input class="regular-text" type="number" min="0" max="60" name="rabe_option_name[rabe_songticker_interval]" id="rabe_songticker_interval" value="%s">
			<p class="description">' . __( 'Set interval of how often to check for new songs in seconds', 'rabe' ) . '</p>',
			isset( $this->rabe_options['rabe_songticker_interval'] ) ? absint( $this->rabe_options['rabe_songticker_interval'] ) : 10 // Default to ten seconds
		);
	}
	
	public function songticker_url_callback() {
		printf(
			'<input class="regular-text" type="text" name="rabe_option_name[rabe_songticker_url]" id="rabe_songticker_url" value="%s">',
			isset( $this->rabe_options['rabe_songticker_url'] ) ? esc_url( $this->rabe_options['rabe_songticker_url'] ) : ''
		);
	}
	
}
if ( is_admin() )
	$rabe = new Rabe();

/* 
 * Example how to retrieve values with:
 * $rabe_options = get_option( 'rabe_option_name' ); // Array of all options
 * $value = $rabe_options['value']; // Value
 */
