<?php
/**
 * Shortcode to generate a link for old real audio archive
 * 
 * Put the [real_archive] shortcode into a post or a page to display the link generator
 * 
 * @package rabe
 * @since version 1.0.0
 * @link https://paulund.co.uk/add-scripts-shortcode-used
 */
$shortcode = new Real_Archive_Class();
class Real_Archive_Class {
	// Should scripts be loaded or not?
    private $add_shortcode_script = false;

	public function __construct() {
		// Shortcode is [real_archive]
		add_shortcode( 'real_archive', array( $this, 'print_real_archive' ) );

		add_action( 'wp_footer', array( $this, 'real_archive_scripts' ) );
		add_action( 'wp_footer', array( $this, 'real_archive_customscripts' ), 99 );
		add_action( 'wp_footer', array( $this, 'real_archive_css' ) );
	}

	// Returns the code snippet
    public function print_real_archive( $attr, $content ) {
        $this->add_shortcode_script = true;

		$shortcode = '';
		
		// Get form
		$shortcode .= '
			<div class="real_archive">
			<form action="' . get_stylesheet_directory_uri() . '/includes/realarchive-url.php" method="POST" style="padding: 0 0 20px 0;"/>
				<div style="text-align:left;"><label>' . __( 'Day', 'rabe' ) . '</label></div><input type="text" name="real_date" id="real_date" value=""><br>
				<div style="text-align:left;padding-top:5px;"><label>' . __( 'Time', 'rabe' ) . '</label></div><input type="text" name="real_time" id="real_time" value=""><br>
				<div style="padding-top:10px;"><input type="hidden" value="1" name="reallink" id="realink" /></div>
				<strong><input type="submit" value="' . __( 'Generate real audio link', 'rabe' ) . '"/></strong>
			</form>
			</div>';

		return $shortcode;
    }

	// Adds necessary jquery javascript
    public function real_archive_scripts() {
        if( ! $this->add_shortcode_script ) {
            return false;
        }
		wp_enqueue_script( 'jquery-ui-timepicker', get_stylesheet_directory_uri() . '/js/jquery-ui-timepicker-addon.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.6.3', true );
    }
    
    // Adds customization of timepicker addon
    public function real_archive_customscripts() {
        if( ! $this->add_shortcode_script ) {
            return false;
        }
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {	
				jQuery('#real_date').datetimepicker({
					dateFormat: "dd.mm.yy",
					timeFormat: "HH:mm",
					altField: "#real_time",
					timeText: "<?php _e( 'Time', 'rabe' ) ?>",
					hourText: "<?php _e( 'Hour', 'rabe' ) ?>",
					minuteText: "<?php _e( 'Minute', 'rabe' ) ?>",
					currentText: "<?php _e( 'Now', 'rabe' ) ?>",
					closeText: "<?php _e( 'Ok', 'rabe' ) ?>"
				});
			});
		</script>
		<?php
	}
    
    // Adds stylesheets
    public function real_archive_css() {
        if( ! $this->add_shortcode_script ) {
            return false;
        }
		wp_enqueue_style( 'jquery-ui', get_stylesheet_directory_uri() . '/css/jquery-ui.min.css' );
		wp_enqueue_style( 'jquery-ui-timepicker', get_stylesheet_directory_uri() . '/css/jquery-ui-timepicker-addon.min.css', array( 'jquery-ui' ) );
		?>
		<style>
			.ui-timepicker-div {
				line-height: 1;
			}
			.ui-timepicker-div dl dt {
				margin: 0;
			}
			.ui_tpicker_time_input {
				padding: 0;
			}
			.ui-state-active, .ui-widget-content .ui-state-active,
			.ui-widget-header .ui-state-active, a.ui-button:active,
			.ui-button:active, .ui-button.ui-state-active:hover {
				border: 1px solid rgb( 0, 225, 212 ) !important;
				background: rgb( 0, 225, 212 ) !important;
			}
			.ui-widget-content a {
				font-style: normal;
			}
			.ui-datepicker td span,
			.ui-datepicker td a {
				text-align: center !important;
			}
			.ui-state-highlight,
			.ui-widget-content .ui-state-highlight,
			.ui-widget-header .ui-state-highlight {
				border: 2px solid rgb( 0, 225, 212 ) !important;
				background: none !important;
			}
		</style>
		<?php
    }
}
?>
