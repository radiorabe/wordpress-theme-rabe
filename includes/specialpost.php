<?php
/**
 * Functions for setting special flags on posts
 * 
 * Set "specialpost" (and "external_url") flag for a post
 * Set "expired" (and "expire_time") flag for a post
 * Set "broadcast_link" flag for an event
 * 
 * A specialpost is something like a sticky post. Looks similar to a 
 * sticky post on frontpage, but shows only title on frontpage.
 * 
 * A post with the "expired" flag and a certain date isn't shown on the 
 * frontpage after that specific time.
 * 
 * When the broadcast_link is set, change permalink to broadcacst page 
 * not to event itself.
 * 
 * Meta Boxes were generated with help of 
 * http://jeremyhixon.com/tool/wordpress-meta-box-generator/
 * 
 * @package rabe
 */


/**
 * Add meta box for features like "specialpost", "expired" and "broadcast_url"
 *
 * @package rabe
 * @since version 1.0.0
 */
function specialpost_add_meta_box() {
	
	$screens = ( function_exists( 'eventorganiser_load_textdomain' ) ) ? array( 'post', 'event' ) : array( 'post' );

	foreach ( $screens as $screen ) {
		// Specialpost meta box on posts for everybody
		if ( current_user_can( 'edit_posts' ) && 'post' === $screen ) {
			add_meta_box(
				'specialpost',
				__( 'Special', 'rabe' ),
				'specialpost_callback',
				$screen,
				'side',
				'high'
			);
		}
		// Specialpost meta box on events only for editors
		if ( current_user_can( 'edit_pages' ) && 'event' === $screen ) {
			add_meta_box(
				'specialpost',
				__( 'Special', 'rabe' ),
				'specialpost_callback',
				$screen,
				'side',
				'high'
			);
		}
	}
}
add_action( 'add_meta_boxes', 'specialpost_add_meta_box' );

/**
 * Add inline datetimepicker javascript constants for expire_time flag
 *
 * @package rabe
 * @since version 1.0.0
 */
 function rabe_enqueue_expiretimejs() {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {	
			$('#rabe_expiretime').datetimepicker({
				dateFormat: "yy-mm-dd",
				timeFormat: "HH:mm",
				timeText: "<?php _e( 'Time', 'rabe' ) ?>",
				hourText: "<?php _e( 'Hour', 'rabe' ) ?>",
				minuteText: "<?php _e( 'Minute', 'rabe' ) ?>",
				currentText: "<?php _e( 'Now', 'rabe' ) ?>",
				closeText: "<?php _e( 'Ok', 'rabe' ) ?>"
			});
			if ( $( '#rabe_expired').is( ':checked' ) ) 
				$( '#expire_time' ).show();
			
			$( '#rabe_expired' ).click( function() {
				if( this.checked )
					$( '#expire_time' ).show();
				else
					$( '#expire_time' ).hide();
			});
		});
	</script>
	<?php
}

/**
 * Add inline javascript toggeling expire_time field
 *
 * @package rabe
 * @since version 1.0.0
 *    
 */
 function rabe_enqueue_externallink() {
	?>
	<script type="text/javascript">
		jQuery( document ).ready( function($) {
			if ( $( '#rabe_specialpost' ).is( ':checked' ) ) 
				$( '#external_link' ).show();
			
			$( '#rabe_specialpost' ).click( function(){
				if( this.checked )
					$( '#external_link' ).show();
				else
					$( '#external_link' ).hide();

			});
		});
	</script>
	<?php
}


/**
 * Actual special flags function which displays necessary input fields and controls
 *
 * @package rabe
 * @since version 1.0.0
 * @param mixed $post Post
 */
function specialpost_callback( $post ) {

	if ( 'post' === get_post_type( $post ) ) {
		
		// Enqueue scripts and styles for datetimepicker
		wp_enqueue_script( 'jquery-ui-timepicker', get_stylesheet_directory_uri() . '/js/jquery-ui-timepicker-addon.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.6.3', true );
		wp_enqueue_style( 'jquery-ui', get_stylesheet_directory_uri() . '/css/jquery-ui.min.css' );
		wp_enqueue_style( 'jquery-ui-timepicker', get_stylesheet_directory_uri() . '/css/jquery-ui-timepicker-addon.min.css', array( 'jquery-ui' ) );

		// Internationalisation for datetimepicker
		rabe_enqueue_expiretimejs();
		
		// External url field
		rabe_enqueue_externallink();
	}

	wp_nonce_field( '_specialpost_nonce', 'specialpost_nonce' );

	// Set default to checked when not explicitly set to 0
	$broadcast_link_checked = ( 0 === get_post_meta( $post->ID, 'rabe_broadcast_link', true ) ) ? '' : 'checked';
	$broadcast_link = ( function_exists( 'eventorganiser_load_textdomain' ) && 'event' === get_post_type( $post ) ) 
		? '<input type="checkbox" name="rabe_broadcast_link" id="rabe_broadcast_link" value="1" ' . $broadcast_link_checked . '>
		  <label for="rabe_broadcast_link">' . __( 'Link to broadcast page', 'rabe' ) . '</label>' 
		: '';

	?>
	<p>
	<?php 
	// Only editors allowed to set special posts
	if ( current_user_can( 'edit_pages' ) ) { ?>
		<input type="checkbox" name="rabe_specialpost" id="rabe_specialpost" value="1" <?php echo ( 1 === (int) get_post_meta( $post->ID, 'rabe_specialpost', true ) ) ? 'checked' : ''; ?>>
		<label for="rabe_specialpost"><?php _e( 'Specialpost', 'rabe' ); ?></label><br>
		<div id="external_link" style="display:none">
			<label for="rabe_external_url"><?php _e( 'External Link:', 'rabe' ); ?></label>
			<input type="text" name="rabe_external_url" id="rabe_external_url" value="<?php echo ( get_post_meta( $post->ID, 'rabe_external_url', true ) ) ? get_post_meta( $post->ID, 'rabe_external_url', true ) : ''; ?>" placeholder="<?php echo _e( 'No link (http://example.com)', 'rabe' ); ?>">
		</div>
		<hr>
		<?php echo $broadcast_link;
	}
	
	// Display expiring time only for post-type post
	if ( 'post' === get_post_type( $post ) ) {
		
		// Get rabe_expiretime or current time
		$expiretime = ( ! empty( get_post_meta( get_the_ID(), 'rabe_expiretime', true ) ) ) ? get_post_meta( get_the_ID(), 'rabe_expiretime', true ) : current_time( 'Y-m-d H:i' );

		?>
			<input type="checkbox" name="rabe_expired" id="rabe_expired" value="1" <?php echo ( 1 === (int) get_post_meta( $post->ID, 'rabe_expired', true ) ) ? 'checked' : ''; ?>>
			<label for="rabe_expired"><?php _e( 'Hide post on frontpage after this specific time:', 'rabe' ); ?></label>
			<div id="expire_time" style="display:none">
				<input type="text" name="rabe_expiretime" id="rabe_expiretime" value="<?php echo $expiretime; ?>">
			</div>
		<?php
	}
	?>
	</p>
	<?php
}

/**
 * Save and validate all the special flags
 *
 * @package rabe
 * @since version 1.0.0
 *
 * @param int $post_id Post ID
 * @return
 */
function specialpost_save( $post_id ) {
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	
	if ( ! isset( $_POST['specialpost_nonce'] ) ) {
		return $post_id;
	}
		
	if ( ! wp_verify_nonce( $_POST['specialpost_nonce'], '_specialpost_nonce' ) ) {
		return $post_id;
	}
	
	if ( ! current_user_can( 'edit_posts', $post_id ) ) {
		return $post_id;
	}

	// Save specialpost variable
	if ( isset( $_POST['rabe_specialpost'] ) ) {
		update_post_meta( $post_id, 'rabe_specialpost', absint( $_POST['rabe_specialpost'] ) );
	} else {
		update_post_meta( $post_id, 'rabe_specialpost', 0 );
	}

	// Save external link variable
	if ( isset( $_POST['rabe_external_url'] ) ) {
		update_post_meta( $post_id, 'rabe_external_url', esc_url( $_POST['rabe_external_url'] ) );
	} else {
		update_post_meta( $post_id, 'rabe_external_url', '' );
	}
	// Save broadcast_link variable
	if ( isset( $_POST['rabe_broadcast_link'] ) ) {
		update_post_meta( $post_id, 'rabe_broadcast_link', absint( $_POST['rabe_broadcast_link'] ) );
	} else {
		update_post_meta( $post_id, 'rabe_broadcast_link', 0 );
	}

	// Save expired variable
	if ( isset( $_POST['rabe_expired'] ) ) {
		update_post_meta( $post_id, 'rabe_expired', absint( $_POST['rabe_expired'] ) );
	} else {
		update_post_meta( $post_id, 'rabe_expired', 0 );
	}

	// Save expired variable
	if ( isset( $_POST['rabe_expiretime'] ) ) {
		update_post_meta( $post_id, 'rabe_expiretime', esc_attr( $_POST['rabe_expiretime'] ) );
	} else {
		update_post_meta( $post_id, 'rabe_expiretime', current_time( 'Y-m-d H:i' ) );
	}

}
add_action( 'save_post', 'specialpost_save' );


/**
 * Create a view in edit.php for specialposts
 *
 * @package rabe
 * @since version 1.0.0
 *
 * @param array $views Existing views in edit.php of wordpress
 * @return array $views Changed views with specialposts view
 *    
 */
function specialposts_filter( $views ) {

	if ( is_admin() ) {

		$query = array(
			'post_type'  => 'post',
			'meta_query' => array(
				array(
					'key'     => 'rabe_specialpost',
					'value'   => 1,
					'compare' => '=',
				),
			),
		);
		
		// Get special=1 query
		$result = new WP_Query( $query );
		$current = ( isset( $_GET['special'] ) ) ? (int) filter_input( INPUT_GET, 'special', FILTER_SANITIZE_NUMBER_INT ) : '';
		$current = ( 1 === $current ) ? ' class="current"' : '';
		
		$views['specialpost'] = sprintf( __('<a href="%1s" %2s>' . __( 'Special', 'rabe' ) . '<span class="count">(%3d)</span></a>'),
			admin_url('edit.php?special=1'),
			$current,
			$result->found_posts
		);

		return $views;

	}
}
add_filter( 'views_edit-post', 'specialposts_filter' );

/**
 * Query for the specialposts view in edit.php
 *
 * @package rabe
 * @since version 1.0.0
 *
 * @link http://wordpress.stackexchange.com/questions/125142/how-to-add-new-tab-to-admin-list-of-posts-and-handle-result-list
 * @param mixed $query Query of all posts
 * @return mixed $query Query of specialposts
 */
function specialposts_list( $query ) {
	if ( is_admin() && $query->is_main_query() ) {
		
		global $pagenow;
		global $post_type;
		
		if ( 'post' !== $post_type || 'edit.php' !== $pagenow ) {
			return;
		}

		$special = ( isset( $_GET['special'] ) ) ? (int) filter_input( INPUT_GET, 'special', FILTER_SANITIZE_NUMBER_INT ) : '';

		if ( 1 === $special ) {
			$meta_query = array( 'key' => 'rabe_specialpost', 'value' => 1, 'compare' => '=' );
			$query->set( 'meta_query', array( $meta_query ) );
		} else {
			return $query;
		}
	}
}
add_action('pre_get_posts', 'specialposts_list');


/**
 * Randomly insert specialposts on frontpage
 *
 * @package rabe
 * @since version 1.0.0
 *
 * @link https://www.gowp.com/blog/inserting-custom-posts-loop/
 * @param array $posts Queried posts on this page (frontpage)
 * @param mixed $query Current running query of posts
 * @param array $posts Queried posts including the specialposts on random positions
 */
function rabe_insert_specialposts( $posts, $query ) {

	global $excluded_posts;

	if ( is_admin() ) return $posts;

	if ( ! is_main_query() ) return $posts;

	// How many specalposts per page
	$rabe_options = get_option( 'rabe_option_name' );
	$specialposts_per_page = ( isset( $rabe_options['specialposts_per_page'] ) ) ? $rabe_options['specialposts_per_page'] : 0;
	
	// Don't try to insert specialposts when there shouldn't be
	if ( 0 === $specialposts_per_page ) return $posts;

	// Get number of sticky posts minus top sticky post
	$sticky_count = count( get_option( 'sticky_posts' ) ) - 1;
	$all_posts = $query->query_vars['posts_per_page'] + $sticky_count;
	
	// Get current time
	$datetime = current_time( 'Y-m-d H:i' );

	// Query for non expired specialposts
	$args = array(
		'post__not_in'   => get_option( 'sticky_posts' ),
		'posts_per_page' => $specialposts_per_page,
		'meta_query' 	 => array(
			'relation'   => 'AND',
			array(
				'key'     => 'rabe_specialpost',
				'value'   => 1,
				'compare' => '=',
			),
			array(
				'relation' => 'OR',
				array(
					'key' => 'rabe_expired',
					'value' => 1,
					'compare' => '!=',
				),
				array(
				'relation' => 'AND',
					array(
						'key' => 'rabe_expired',
						'value' => 1,
						'compare' => '=',
					),
					array(
						'key' => 'rabe_expiretime',
						'value' => $datetime,
						'compare' => '>' // rabe_expiretime is in the past
					),
				),
			),
		),
	);

	$specialposts = get_posts( $args );
	
	$insert = $sticky_count;
	foreach ( $specialposts as $specialpost ) {
		// Insert specialposts randomly
		$insert =  $insert + random_int( 1, floor( ( $all_posts / $specialposts_per_page ) ) );
		array_splice( $posts, $insert, 0, array( $specialpost ) );
	}

	return $posts;

}


/**
 * Add specialpost class to post
 *
 * @package rabe
 * @since version 1.0.0
 *
 * @link http://wordpress.stackexchange.com/questions/125142/how-to-add-new-tab-to-admin-list-of-posts-and-handle-result-list
 * @param array $attr Post attributes such as html classes
 * @return array $attr Post attributs with added specialpost class
 */
function specialpost_attr_post( $attr ) {
	
	$specialpost = (int) get_post_meta( get_the_ID(), 'rabe_specialpost', true );
	
	if ( 1 === $specialpost && ! is_singular() ) {
		
		// Add specialpost class to specialpost
	    $classes = 'specialpost';
	    $attr['class'] = join( ' ', get_post_class() ) . ' ' . $classes;
	}

	return $attr;
}
// Add specialpost status to omega_attr_content via specialpost_attr_post filter
add_filter('omega_attr_post', 'specialpost_attr_post', 7);
add_filter('omega_attr_content', 'specialpost_attr_post', 7);

?>
