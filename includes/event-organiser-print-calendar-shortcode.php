<?php
/**
 * Shortcode to print a printable monthly calendar
 * - Needs event-organiser plugin
 *	
 * https://paulund.co.uk/add-scripts-shortcode-used
 */
$shortcode = new Broadcast_Print_Calendar();
class Broadcast_Print_Calendar
{
	// Should scripts be loaded or not?
    private $add_shortcode_script = false;

    public function __construct()
    {
        add_shortcode( 'printable_calendar', array( $this, 'print_printable_calendar' ) );

        add_action( 'wp_footer', array( $this, 'printable_calendar_scripts' ) );
        add_action( 'wp_footer', array( $this, 'printable_calendar_css' ) );
    }

    public function print_printable_calendar( $attr, $content )
    {
        $this->add_shortcode_script = true;
        
		$shortcode = '';
		
		// Broadcast list
		$shortcode .= 'Printable Calendar';

		return $shortcode;
    }

    public function printable_calendar_scripts()
    {
        if( !$this->add_shortcode_script)
        {
            return false;
        }

		// Include Javascript
		wp_enqueue_script( 'printable-calendar', get_stylesheet_directory_uri().'/js/printable-calendar.js', false );
    }
    
    public function printable_calendar_css()
    {
        if(!$this->add_shortcode_script)
        {
            return false;
        }
        
        wp_enqueue_style( 'stattradio',  get_stylesheet_directory_uri() . '/css/stattradio.css' );

		?>
		<style type="text/css">
		</style>
		<?php
		
    }
}

?>
