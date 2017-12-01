<?php
/**
 * The template for displaying and exporting a month calendar view of the schedule
 *
 * @since version 1.0.0
 * @package rabe
 * @link https://github.com/hhurz/tableExport.jquery.plugin
 */

/**
 * Remove unneeded scripts for this page
 *
 * @since version 1.0.0
 * @package rabe
 */
function stattradio_dequeue_scripts() {
	wp_dequeue_script( 'jquery-json' );
	wp_dequeue_script( 'stickymenu' );
	wp_dequeue_script( 'custom-mediaelement' );
	wp_dequeue_script( 'broadcast-ticker' );
	wp_dequeue_script( 'colorbox' );

	// Add scripts for exporting table
	wp_enqueue_script( 'FileSaver', get_stylesheet_directory_uri().'/js/FileSaver.min.js', array( 'jquery' ), '1.0', false );
	wp_enqueue_script( 'tableExport', get_stylesheet_directory_uri().'/js/tableExport.min.js', array( 'FileSaver', 'jquery' ), '1.6.3', false );
	wp_enqueue_script( 'stattradio', get_stylesheet_directory_uri().'/js/stattradio2print.js', false, '1.0.0', false );
}
add_action( 'wp_print_scripts', 'stattradio_dequeue_scripts', 100 );


/**
 * Remove unneded styles for this page and add own style
 *
 * @since version 1.0.0
 * @package rabe
 */
function stattradio_remove_stylesheets() {
	wp_deregister_style( 'omega-style' );
	wp_deregister_style( 'colorbox' );
	wp_enqueue_style( 'stattradio',  get_stylesheet_directory_uri() . '/css/stattradio2print.css' );
}
add_action( 'wp_enqueue_scripts', 'stattradio_remove_stylesheets', 100 );


/**
 * Calculates the month and the year and displays printable stattradio calendar
 *
 * @since version 1.0.0
 * @package rabe
 */
function rabe_printable_calendar( $month = '', $year = '' ) {
	// Get month and year
	if ( isset( $_GET['rabe_month'] ) && isset( $_GET['rabe_year'] ) ) {
		$month = $_GET['rabe_month'];
		$year = $_GET['rabe_year'];
	} else {
		// Time from now plus one month
		$month = date( 'm', strtotime( '+1 month', strtotime( 'now' ) ) );
		$year = date( 'Y', strtotime( '+1 month', strtotime( 'now' ) ) );
	}

	// Chosen month in Y-m-d string format
	$chosen_time_string = (string) $year . '-' . $month . '-01';

	// Generate months and years for links
	$chosen_time = strtotime( $chosen_time_string );
	$next_month = date( 'm', strtotime( '+1 month', $chosen_time ) );
	$prev_month = date( 'm', strtotime( '-1 month', $chosen_time ) );
	$next_year = date( 'Y', strtotime( '+1 month', $chosen_time ) );
	$prev_year = date( 'Y', strtotime( '-1 month', $chosen_time ) );

	// Generate next and previous month links
	global $wp;
	$current_url = home_url(add_query_arg(array(),$wp->request));
	$next_url = add_query_arg( array( 'rabe_month'=> $next_month, 'rabe_year' => $next_year ), $current_url	);
	$prev_url = add_query_arg( array( 'rabe_month'=> $prev_month, 'rabe_year' => $prev_year ), $current_url	);
	?>
	<a href="#" onclick="$('#printable_calendar').tableExport({type:'xls',fileName:'Stattradio',worksheetName:'Stattradio',excelstyles:['background','text-align']});"><?php echo __( 'Export to Excel', 'rabe' ); ?></a>
	| <a href="#" class="select-calendar"><?php echo __( 'Select calendar', 'rabe' ); ?></a>
	| <a href="<?php echo $prev_url; ?>"><?php echo __( 'Previous month', 'rabe' ); ?></a>
	| <a href="<?php echo $next_url; ?>"><?php echo __( 'Next month', 'rabe' ); ?></a>
	<?php 
	echo rabe_get_printable_calendar( $month, $year );
}
add_action( 'omega_after_entry', 'rabe_printable_calendar', 2, 10 );

/**
 * Generates a printable monthly calendar in a html table
 * 
 * @since version 1.0.0
 * @package rabe
 * @link http://selfphp.de/code_snippets/code_snippet.php?id=13
 * @uses utf8_wordwrap() UTF-8 safe wordwrap function
 * @param int $month Month for calendar
 * @param int $year Year for calendar
 * @return string $month_string HTML table of a month view of the calendar
 */
function rabe_get_printable_calendar( $month = '', $year = '') {

	if ( ! $month || ! $year ) {
		return __( 'No month or year specified!', 'rabe' );
	}

	// Set locale
	setlocale (LC_TIME, 'de_DE');

	// Add a leading zero
	$month = ( strlen( $month ) == 1 ) ? '0' . $month : $month;
	
	// Day of week (0 is sunday, 6 is saturday)
	$start_day = date ( 'w', mktime ( 0, 0, 0, date ( $month ), 1, date ( $year ) ) );

	if ( ! $start_day ) {
		// Week starts with monday, so sunday
		// set week_day to 7 instead of 0 
		$start_day = 7;
	}

	// Head of the table
	$month_string = '
		<h2>' .  date ( 'F Y', mktime ( 0, 0, 0, date ( $month ), 1, date ( $year ) ) ) . '</h2>
		<table id="printable_calendar">
			<tr>
				<td class="head">' . __( 'Mo', 'rabe' ) . '</td>
				<td class="head">' . __( 'Di', 'rabe' ) . '</td>
				<td class="head">' . __( 'Mi', 'rabe' ) . '</td>
				<td class="head">' . __( 'Do', 'rabe' ) . '</td>
				<td class="head">' . __( 'Fr', 'rabe' ) . '</td>
				<td class="head">' . __( 'Sa', 'rabe' ) . '</td>
				<td class="head">' . __( 'So', 'rabe' ) . '</td>
			</tr>
		<tr>';

	$week_string = '';
	$events_string = '';
	$empty_start = 0;
	
	// Days in this month
	$days_limit = date ( 't', mktime ( 0, 0, 0, date ( $month ), 1, date ( $year ) ) );

	// Build month
	for ( $i = 1; $i <= ( $days_limit + $start_day -1 ); $i++ ) {

		$day_of_month = $i - $start_day + 1;

		$clean_day = ( strlen( $day_of_month ) == 1) ? '0' . $day_of_month : $day_of_month;
		
		if ( $i < $start_day ) {
			// Empty cells to the start of the month
			$week_string .= '<td>&nbsp;</td>';
			$events_string .= '<td>&nbsp;</td>';
			
			// Count empty cells
			$empty_start++;
		} else {
			$week_string .= '<td class="day">' . $day_of_month . '.' . $month . '.</td>';
			$events = eo_get_events(
				array(
					'ondate' => $year.'-'.$month.'-'.$clean_day,
					'event_start_after' => $year.'-'.$month.'-'.$clean_day,
				)
			);
			$event_list = '';
			
			foreach ( $events as $event ) {
				$event_title = get_the_title( $event->ID );
				$event_time = eo_get_the_start( 'H:i', $event->ID, null, $event->occurrence_id );
				$nl = '<br style="mso-data-placement:same-cell;" />';
				
				// Change Wh to w
				$event_title = str_replace( "(Wh)", "(w)", $event_title );
				
				/**
				* Break long event titles
				* Limit: 17 caracters without time, 23 with time and following space (measured with w character)
				* Because titles don't contain only w characters but smaller letters aswell, we go up to 18
				*/
				
				// Special cases
				if ( 'Gschächnütschlimmers' == $event_title ) {
					$event_title = 'Gschächnüt-' . $nl .'schlimmers';
				} elseif ( 'House Music DJ Lord' == $event_title ) {
					$event_title = 'House Music' . $nl . 'DJ Lord';
				} elseif ( 'unerhörtes-ungehörtes' == $event_title ) {
					$event_title = 'unerhörtes-' . $nl . 'ungehörtes';
				} elseif ( 'Rhythm & Blues Juke Box' == $event_title ) {
					$event_title = 'Rhythm \& Blues' . $nl . 'Juke Box';
				} elseif ( 'Zur Lage des Planeten' == $event_title ) {
					$event_title = 'Zur Lage' . $nl . 'des Planeten';
				} elseif ( 'Spazz-Time Kontinuum' == $event_title ) {
					$event_title = 'Spazz-Time' . $nl . 'Kontinuum';
				// Just has enough space for these long words
				// FIXME: Make this configurable
				} elseif ( 'Radio loco-motivo (w)' == $event_title 
					|| 'Radio Silbergrau (w)' == $event_title 
					|| 'Stimme der Kutüsch' == $event_title 
					|| 'Bärner Schlagerwelt' == $event_title 
					|| 'Las Vienas Abiertas' == $event_title ) {
					$event_title = $event_title;
				// utf8 characters contains more than one byte (one byte = one char for strlen), so use utf8_decode
				// &ndash; and stuff like that look to strlen() as multiple chars, so use html_entity_decode
				} elseif ( 18 <= strlen( utf8_decode( html_entity_decode( $event_title ) ) ) ) {
					// mb_wordpress is a multibyte aware wordwrap function (utf-8, anyone?)
					$event_title = mb_wordwrap( html_entity_decode( $event_title ), 18, $nl, false );
				}
				
				$event_list .= $event_time . ' ' . $event_title . $nl ;
			}
			$events_string .= '<td class="event">'  . $event_list . '</td>';
		}

		if ( ! ( $i % 7 ) ) {
			// Break rows after 7 days and put it to month_string
			$month_string .= $week_string . '</tr><tr>' . $events_string . '</tr><tr>';
			
			// Reset week and events
			$week_string = '';
			$events_string = '';
		// In the end of the month, days of the month maybe won't go to 7th day, so fill with empty cells and break
		} elseif ( $i >= ( $days_limit + $empty_start ) ) {
			// Empty cells to the end of the month
			$empty_end = 7 - ( $i % 7 );
			$week_string .= str_repeat( '<td>&nbsp;</td>', $empty_end );
			$events_string .= str_repeat( '<td>&nbsp;</td>', $empty_end );
			$month_string .= $week_string . '</tr><tr>' . $events_string ;
			break;
		}
	}
	
	// Finish table
	$month_string .= '</tr></table>';

	// Return month
	return $month_string;

}

/**
 * UTF-8 safe wordwrap function
 * 
 * @since version 1.0.0
 * @package rabe
 * @link http://stackoverflow.com/questions/3825226/multi-byte-safe-wordwrap-function-for-utf-8
 */
function mb_wordwrap( $str, $width = 75, $break = "\n", $cut = false ) {
    $lines = explode( $break, $str );
    foreach ( $lines as &$line ) {
        $line = rtrim( $line );

        $words = explode(' ', $line);
        $line = '';
        $actual = '';
        foreach ( $words as $word ) {
            if ( mb_strlen( $actual . $word ) <= $width )
                $actual .= $word.' ';
            else {
                if ( $actual != '' )
                    $line .= rtrim( $actual ) . $break;
                $actual = $word;
                if ($cut) {
                    while ( mb_strlen( $actual ) > $width ) {
                        $line .= mb_substr( $actual, 0, $width ) . $break;
                        $actual = mb_substr( $actual, $width );
                    }
                }
                $actual .= ' ';
            }
        }
        $line .= trim( $actual );
    }
    return implode( $break, $lines );
}


// START taken from omega header.php
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php 
		wp_head(); 
		// Add noindex and nofollow
	?>
	<meta name="robots" content="noindex,nofollow">
	</head>
	<body <?php body_class(); ?> <?php omega_attr( 'body' ); ?>>
		<?php // END taken from omega header.php

		// START taken from omega page.php ?>
		<main  class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>
				<?php 
				do_action( 'omega_content' );
				do_action( 'omega_after_content' ); 
				?>
		</main><!-- .content -->
<?php get_footer();
// END taken from omega page.php
?>

