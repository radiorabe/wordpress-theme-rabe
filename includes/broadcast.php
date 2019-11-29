<?php
/**
 * Broadcast taxonomy functions and definitions
 * 
 * Single taxonomy term with added fields for broadcasters, urls, an image and
 * broadcast portrait file
 *
 * @package rabe
 * @since version 1.0.0
 * @link https://en.bainternet.info/tax-meta-class-faq/
 */


/**
 * Single Taxonomy Term with radio buttons
 *
 * @package rabe
 * @since version 1.0.0
 * @link https://github.com/WebDevStudios/Taxonomy_Single_Term
 */
require_once( get_stylesheet_directory() . '/classes/class.taxonomy-single-term.php' );


/**
 * Shortcode for listing items of broadcast taxonomy
 */
require_once( get_stylesheet_directory() . '/includes/broadcast-list-shortcode.php' );


/**
 * Creates custom taxonomy broadcast
 * 
 * Broadcast taxonomy has no parents and is a single term taxonomy.
 * A post can't have more than one term of this taxonomy.
 *
 * @package rabe
 * @since version 1.0.0
 * @link https://github.com/WebDevStudios/Taxonomy_Single_Term
 */
function broadcast_taxonomy() {
	
	$labels = array(
		'name'                       => _x( 'Broadcasts', 'Taxonomy General Name', 'rabe' ),
		'singular_name'              => _x( 'Broadcast', 'Taxonomy Singular Name', 'rabe' ),
		'menu_name'                  => __( 'Broadcasts', 'rabe' ),
		'all_items'                  => __( 'All Broadcasts', 'rabe' ),
		'parent_item'                => __( 'Parent Broadcast', 'rabe' ),
		'parent_item_colon'          => __( 'Parent Broadcast:', 'rabe' ),
		'new_item_name'              => __( 'New Broadcast Name', 'rabe' ),
		'add_new_item'               => __( 'Add New Broadcast', 'rabe' ),
		'edit_item'                  => __( 'Edit Broadcast', 'rabe' ),
		'update_item'                => __( 'Update Broadcast', 'rabe' ),
		'view_item'                  => __( 'View Item', 'rabe' ),
		'separate_items_with_commas' => __( 'Separate genres with commas', 'rabe' ),
		'add_or_remove_items'        => __( 'Add or remove broadcasts', 'rabe' ),
		'choose_from_most_used'      => __( 'Choose from the most used broadcasts', 'rabe' ),
		'popular_items'              => __( 'Popular Items', 'rabe' ),
		'search_items'               => __( 'Search broadcasts', 'rabe' ),
		'not_found'                  => __( 'Not Found', 'rabe' ),
	);

	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true, // Important for quickedit
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => array( 'slug' => 'broadcast' ),
		'capabilities' => array (
			// Needed to allow broadcasters (edit_posts) to edit their broadcasts
			'manage_terms' => 'edit_posts',
			'edit_terms'   => 'edit_posts',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_posts',
		)
	);
	
	$post_types = ( function_exists('eventorganiser_load_textdomain') ) ? array( 'post', 'event' ) : array( 'post' );
	
	register_taxonomy( 'broadcast', $post_types, $args );

}
add_action( 'init', 'broadcast_taxonomy', 11 );


/**
 * Admin functions for broadcast taxonomy
 **********************************************************************/

if ( is_admin() ) {
	
	/**
	 * Initializes more meta fields for custom taxomy broadcast
	 * 
	 * Unfortunately Tax-meta-class doesn't support localization, so translate directly here
	 *
	 * @link https://en.bainternet.info/wordpress-taxonomies-extra-fields-the-easy-way/
	 * 
	 */
	require_once( get_stylesheet_directory() . '/classes/Tax-meta-class/Tax-meta-class.php');

	$prefix = 'broadcast_';

	// Metabox for an image
	$config = array(
		'id'             => 'broadcast_meta', 
		'title'          => __( 'Broadcast', 'rabe' ),
		'pages'          => array( 'broadcast' ), 
		'context'        => 'normal',
		'fields'         => array(),
		'local_images'   => false, // Use local or hosted images (meta box images for add/remove)
		'use_with_theme' => get_stylesheet_directory_uri() . '/classes/Tax-meta-class' // Change path if used with theme set to true, false for a plugin or anything else for a custom path (default false).
	);

	// Initiate metabox
	$broadcast_meta =  new Tax_Meta_Class($config);

	// Add image to metabox
	$broadcast_meta->addImage( $prefix . 'image', array( 'name' => 'Bild zur Sendung' , 'width' => '1200' ));

	// Add broadcast members
	$broadcast_members[] = $broadcast_meta->addUser($prefix . 'member', array( 'name' => 'Sendungsmachende(r)' ), true);
	$broadcast_meta->addRepeaterBlock( $prefix . 'members', array( 'inline' => true, 'name' => 'Sendungsmachende' , 'fields' => $broadcast_members ));

	// Add a text fields for website
	$broadcast_meta->addText( $prefix . 'website', array( 'name'=> 'Webseite' ));
	$broadcast_meta->addText( $prefix . 'email', array( 'name'=> 'E-Mail' ));
	$broadcast_meta->addText( $prefix . 'facebook', array( 'name'=> 'Facebook' ));
	$broadcast_meta->addText( $prefix . 'soundcloud', array( 'name'=> 'Soundcloud' ));
	$broadcast_meta->addText( $prefix . 'mixcloud', array( 'name'=> 'Mixcloud' ));
	$broadcast_meta->addText( $prefix . 'youtube', array( 'name'=> 'Youtube' ));
	$broadcast_meta->addText( $prefix . 'vimeo', array( 'name'=> 'Vimeo' ));

	// Add broadcast portrait as mp3 file
	$broadcast_meta->addFile( $prefix . 'portrait', array( 'name'=> 'Portrait als MP3' , 'ext' => 'mp3' ));

	// Mark broadcast as archived
	$broadcast_meta->addCheckbox( $prefix . 'archived', array( 'name' => 'Archiviert') );

	// Finish meta box declaration
	$broadcast_meta->Finish();


	/**
	 * Show broadcasts menu as main entry
	 */
	function rabe_menu_item() {
		if ( is_admin() ) {
			// Move broadcast menu
			remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=broadcast' ); // Remove broadcast taxonomy submenu
			add_menu_page( __('Broadcasts', 'rabe' ), __('Broadcasts', 'rabe' ), 'edit_posts', 'edit-tags.php?taxonomy=broadcast', null, 'dashicons-category', 4 ); // Add broadcast taxonomy menu
		}
	} 
	add_action( 'admin_menu', 'rabe_menu_item' , 12);	
	
	
	/**
	 * Validates a post before publishing
	 * 
	 * Every post must have a defined broadcast term, otherwise don't publish it.
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 * @uses get_broadcast() Gets the id of the broadcast term
	 * 		 rabe_draft_failed() Saves failing post to draft
	 * @link http://www.paulund.co.uk/check-a-featured-image-is-set-before-publishing
	 * @params int $post_id Post ID
	 */
	function rabe_validate( $post_id ) {
	
		
		// only validate on post edit screen
		global $pagenow;
		if ( 'post.php' !== $pagenow || 'post-new.php' !== $pagenow ) {
			return $post_id;
		}
			
		// only validate on posts and events
		global $post_type;
		if ( 'post' !== $post_type && 'event' !== $post_type ) {
			return $post_id;
		}

		// don't validate newly created posts and when moving posts to trash
		if ( 'auto-draft' === get_post_status( $post_id ) || 'trash' === get_post_status( $post_id ) ) {
			return $post_id;
		}

		// don't validate on revisions
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		// don't validate on autosave routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// Check if post has at least one broadcast
		if ( ! get_broadcast( $post_id ) ) {

			// No broadcast set?!
			set_transient( 'rabe_validate_failed', 'broadcast' );   

			// Save as draft when broadcaster
			rabe_draft_failed( $post_id );
					
		}
		
		// Check that only one event is saved on the same time
		if ( 'event' === $post_type && current_user_can( 'publish_events' ) ) {

			$occurrences = eo_get_the_occurrences_of( $post_id );

			foreach ( $occurrences as $occurrence ) {

				// Printable format
				$start = eo_format_datetime( $occurrence['start'] , 'Y-m-d H:i:s' );
				$end = eo_format_datetime( $occurrence['end'] , 'Y-m-d H:i:s' );

				// Get events starting during current occurrence
				$starting_events[] = eo_get_events(
					array(
						'event_start_after'  => $start,
						'event_start_before' => $end,
						'post__not_in'		 => array( $post_id ),

					)
				);
				// Get events ending during current occurrence
				$ending_events[] = eo_get_events(
					array(
						'event_end_after'  	=> $start,
						'event_end_before' 	=> $end,
						'post__not_in'		=> array( $post_id ),
					)
				);
				// Get events starting before and ending after current occurrence
				$outer_events[] = eo_get_events(
					array(
						'event_start_before' => $start,
						'event_end_after'	 => $end,
						'post__not_in'		 => array( $post_id ),
					)
				);
				
				// Expand current occurrence timespan to check for events on same time, (inner events)
				// Remove a second
				date_add( $occurrence['start'], date_interval_create_from_date_string( '-1 second' ) );
				$start = eo_format_datetime( $occurrence['start'] , 'Y-m-d H:i:s' );

				// Add a second
				date_add( $occurrence['end'], date_interval_create_from_date_string( '1 second' ) );
				$end = eo_format_datetime( $occurrence['end'] , 'Y-m-d H:i:s' );
				
				// Get events starting before and ending after current occurrence
				$inner_events[] = eo_get_events(
					array(
						'event_start_after' => $start,
						'event_end_before'	 => $end,
						'post__not_in'		 => array( $post_id ),
					)
				);
			}
			// Some event is double
			if ( ! empty ( array_filter( $starting_events ) ) 
				|| ! empty ( array_filter( $ending_events ) ) 
				|| ! empty ( array_filter( $outer_events ) )
				|| ! empty ( array_filter( $inner_events ) ) ) {

				// No available time
				set_transient( 'rabe_validate_failed', 'unavailable' );   

				// Save as draft when no available time is present
				rabe_draft_failed( $post_id );
			
			}
		}
	}
	add_action('save_post', 'rabe_validate' );


	/**
	 * Save "failed" post to draft
	 *
	 * @package rabe
	 * @since version 1.0.0
	 * @params int $post_id Post ID
	 */
	function rabe_draft_failed( $post_id ) {
		// remove rabe_validate
		remove_action( 'save_post', 'rabe_validate' );
		
		// update post to draft status
		wp_update_post(
			array(
				'ID'		  => $post_id,
				'post_status' => 'draft',
			)
		);

		// Re-add rabe_validate
		add_action( 'save_post', 'rabe_validate' );
	}

	/**
	 * Set error messages in transients for failed posts
	 *
	 * @package rabe
	 * @since version 1.0.0
	 * @params array $post
	 */
	function rabe_validate_error( $post ) {	
		// Check type of transient and display corresponding error message
		if ( get_transient( 'rabe_validate_failed' ) ) {
						
			if ( 'broadcast' === get_transient( 'rabe_validate_failed' ) ) {
				$error_message = __( 'No broadcast set.', 'rabe' );
				delete_transient( 'rabe_validate_failed' );
			} elseif ( 'unavailable' === get_transient( 'rabe_validate_failed' ) ) {
				$error_message = __( 'There exists already another event at this time/these times. You have to delete them before saving this event.', 'rabe' );
				delete_transient( 'rabe_validate_failed' );
			}
			
			// Post was updated before resetting it to draft, but do not show updated info box
			$hide_update_notice = '<style>.wp-admin .updated { display: none; } </style>';
			
			// Print error messages
			echo '<div id="message" class="notice error"><p>' . $error_message . '</p></div>' . $hide_update_notice;
		}
	}
	add_action( 'admin_notices', 'rabe_validate_error' );


	/**
	 * Automatically add broadcast 
	 * 
	 * If there is only one broadcast term available, set it by default
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 */
	function rabe_auto_broadcast() {
		
		// Get post
		global $post;
		
		// Only for broadcaster role
		if ( ! current_user_can( 'edit_pages' ) && isset( $post->ID ) ) {

			// Get available broadcast terms
			$broadcast_terms = get_terms(
				array(
					'taxonomy'	 => 'broadcast',
					'hide_empty' => false,
				)
			);

			// Automatically set broadcast term 
			if ( 1 === count( $broadcast_terms ) ) {
				// There is only one term, so set it
				$broadcast = array_values( $broadcast_terms )[0]->term_id;
				wp_set_post_terms( $post->ID, array( $broadcast ), 'broadcast', false );
			}
		} 
	}
	add_action( 'save_post', 'rabe_auto_broadcast', 12 );
	add_action( 'draft_to_publish', 'rabe_auto_broadcast', 12 );
	add_action( 'new_to_publish', 'rabe_auto_broadcast', 12 );
	add_action( 'pending_to_publish', 'rabe_auto_broadcast', 12 );
	add_action( 'future_to_publish', 'rabe_auto_broadcast', 12 );
	

	/**
	 * Set defaults for broadcast taxonomy meta box
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 */
	function broadcast_metabox() {

		// Is event organiser plugin enabled?
		$post_types = ( function_exists('eventorganiser_load_textdomain') ) ? array( 'post', 'event' ) : array( 'post' );

		// Show radio bullets for broadcaster, select box for others
		$type = ( current_user_can( 'edit_pages' ) ) ? 'select' : 'radio';
		
		// Sreate new metabox for taxonomy genre in event posts
		$broadcast_metabox = new Taxonomy_Single_Term( 'broadcast', $post_types, $type );
		$broadcast_metabox->set( 'priority', 'high' );
		$broadcast_metabox->set( 'indented', false );

		// Broadcasters must, Staff should choose a broadcast
		if ( current_user_can( 'edit_pages' ) ) {
			$broadcast_metabox->set( 'force_selection', false );
		} else {
			$broadcast_metabox->set( 'force_selection', true );
		}

		// Capabilities for broadcast taxonomy
		if ( current_user_can( 'manage_categories' ) ) {
			$broadcast_metabox->set( 'allow_new_terms', true );
		} else {
			$broadcast_metabox->set( 'allow_new_terms', false );
		}
	}
	add_action( 'after_setup_theme', 'broadcast_metabox'  );
	
	/**
	 * Disallow creation of new terms in taxonomy broadcast for non-editors
	 *
	 * @since version 1.0.0
	 * @param string|int $term Actual broadcast term
	 * @return string|int $term Taxonomy term (the broadcast)
	 */
	function prevent_terms ( $term, $taxonomy ) {
		if ( $taxonomy === 'broadcast' && ! current_user_can( 'edit_pages' ) ) {
			return new WP_Error( 'term_addition_blocked', __( 'You cannot add broadcasts!', 'rabe' ) );
		}

		return $term;
	}
	add_action( 'pre_insert_term', 'prevent_terms', 1, 2 );

	/**
	 * Add tinymce wysiwyg editor for broadcast description via jquery
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 * @link http://wordpress.stackexchange.com/questions/190510/replace-taxomony-description-field-with-visual-wysiwyg-editor
	 * 
	 * @param array $term Full array of broacdast (term of custom taxonomy brodacst)
	 * @param string|int $taxonomy Custom taxonomy ID or slug
	 */
	function add_tinymce_description( $term, $taxonomy ){
		?>
		<tr valign="top">
			<th scope="row"><?php echo __( 'Description', 'rabe' ) ?></th>
			<td>
				<?php wp_editor( html_entity_decode( $term->description ), 'description', array( 'media_buttons' => false, 'teeny' => true ) ); ?>
				<script>
					jQuery(window).ready(function(){
						jQuery('label[for=description]').parent().parent().remove();
					});
				</script>
			</td>
		</tr>
		<?php
	}
	add_action( 'broadcast_edit_form_fields', 'add_tinymce_description', 10, 2);

	// Allow html tags in term_description.
	foreach ( array( 'pre_term_description' ) as $filter ) {
		remove_filter( $filter, 'wp_filter_kses' );
	}
	foreach ( array( 'term_description' ) as $filter ) {
		remove_filter( $filter, 'wp_kses_data' );
	}
}


/***********************************************************************
 * Frontend functions for broadcast taxonomy
 **********************************************************************/

/**
 * Rewrite http://domain.tld/broadcast/broadcast-slug to http://domain.tld/broadcast-slug
 * 
 * @package rabe
 * @since version 1.0.0
 */
function broadcast_rewrite() {
	
	// Get broadcast terms
	$broadcast_terms = get_terms( array( 'taxonomy' => 'broadcast', 'hide_empty' => false ) );
	
	// Add rewrite rules
	foreach ( $broadcast_terms as $broadcast ) {
		add_rewrite_rule( $broadcast->slug . '/page/?([0-9]{1,})/?$', 'index.php?broadcast=' . $broadcast->slug . '&paged=$matches[1]', 'top' );
		add_rewrite_rule( $broadcast->slug . '([^/].+)?', 'index.php?broadcast=' . $broadcast->slug, 'top' );
	}
	
	// Update polylang rewrite rules with active polylang plugin
	if ( function_exists( 'broadcast_rewrite_polylang' ) ) {
		broadcast_rewrite_polylang();
	}
}
add_action('init', 'broadcast_rewrite', 12 );


/**
 * Automatically update rewrite rules upon editing/deleting/creating new broadcast
 * 
 * @package rabe
 * @since version 1.0.0
 */
function update_broadcast_rewrite() {

	// Update rewrite rules upon creating new broadcast
	broadcast_rewrite();

	// Flush rules
	flush_rewrite_rules();
}
add_action( 'created_broadcast', 'update_broadcast_rewrite', 12 );
add_action( 'delete_broadcast', 'update_broadcast_rewrite', 12 );
add_action( 'edited_broadcast', 'update_broadcast_rewrite', 12 );


/**
 * Remove "/broacast" of url
 * 
 * @package rabe
 * @since version 1.0.0
 *
 * @param string $url Broadcast url
 * @param string|int $term Actual broadcast slug
 * @param string|int $taxonomy "broadcast"
 * @return $url Broadcast url without taxonomy slug
 */
function remove_broadcast_from_term_link( $url, $term, $taxonomy ) {
   if ( 'broadcast' === $taxonomy ) {
	   return str_replace( '/broadcast', '', $url );
   }
   return $url;
}
add_filter('term_link', 'remove_broadcast_from_term_link', 10, 3);


/**
 * Add broadcast classes to post
 * 
 * @package rabe
 * @since version 1.0.0
 * 
 * @param array $attr Post attributes
 * @return array $attr Changed post attributes
 */
function rabe_attr_post( $attr ) {
	
	if ( has_term( null, 'broadcast' ) ) {
		
		$terms = wp_get_post_terms( get_the_ID(), 'broadcast' );
		// FIXME: a post could maybe have more broadcast taxonomies, for now we only take one
		$term = array_values( $terms )[0];
	    $classes = 'taxonomy-broadcast broadcast-' . $term->slug . ' ' . $term->slug;
	    $attr['class'] = join( ' ', get_post_class() ) . ' ' . $classes;
	}
	return $attr;
}
add_filter( 'omega_attr_post', 'rabe_attr_post', 7 );


/**
 * Query only posts in taxonomy query of taxonomy broadcast
 * 
 * @package rabe
 * @since version 1.0.0
 * 
 * @param array $query Posts query
 * @return array $attr Changed posts query
 */
function broadcast_taxonomy_query( $query ) {
    if ( $query->is_tax( 'broadcast' ) && $query->is_main_query() ) {
        $query->set( 'post_type', array( 'post' ) );
	}
    return $query;
}
add_filter( 'pre_get_posts', 'broadcast_taxonomy_query' );


/***********************************************************************
 * Helper functions for broadcast taxonomy
 **********************************************************************/
 
/**
* Returns the ID of the broadcast term
*
* Can be used inside the loop to output the broadcast id of the current post by not passing an ID.
* Otherwise it returns the broadcast ID of the passed post ID.
*
* ### Examples
* This function can be used inside the Loop to return the broadcast ID of the current post
* <code>
*    $current_broadcast_id = get_broadcast();
* </code>  
* To obtain the broadcast ID of post 23:
* <code>
*    $broadcast_id = get_broadcast(23);
* </code>
* @since 1.0.0
* @param int $post_id The event (post) ID. Uses current event if empty.
* @return int The corresponding broadcast (broadcast term) ID
 */
 function get_broadcast( $post_id = '' ) {
	global $post;

	if ( empty( $post_id ) ) {
		$post_id = ( isset( $post->ID ) ? $post->ID : 0);
	}

	$broadcast = get_the_terms( $post_id, 'broadcast' );

	if ( empty( $broadcast ) || is_wp_error( $broadcast ) )
		return false;

	$broadcast = array_pop( $broadcast );

	$broadcast_id = $broadcast->term_id;
	
	return (int) $broadcast_id;
}


/**
* Returns the slug of the broadcast
*
* When used without an argument it uses the post specified in the global $post (e.g. current post in the loop).
* Can be used inside the loop to output the broadcast id of the current event.
* 
* ### Examples
* Inside the loop, you can output the current post's broadcast
* <code>
*   <?php echo get_broadcast_slug(); ?> 
* </code>    
* Get the last start date of event with id 7
* <code>
*   <?php $broadcast_slug = get_broadcast_slug(7); ?>
* </code>
* 
* @since 1.0.0
* @param int $post_id The post ID. Uses current post if empty.
* @return int The corresponding broadcast (broadcast term) slug
 */
function get_broadcast_slug( $post_id = '' ) {

	global $post;

	if( ! empty( $post_id ) ) {
		$post_id = $post_id;
	} else {
		$post_id = ( isset( $post->ID ) ? $post->ID : 0 );
	}

	$broadcast = get_the_terms( $post_id, 'broadcast' );

	if ( empty( $broadcast ) || is_wp_error( $broadcast ) )
		return false;

	$broadcast = array_pop( $broadcast );

	return $broadcast->slug;

}

/**
* Get the the id of a broadcast term
* 
* A utility function for getting the broadcast ID from a broadcast ID or slug.
* Useful for when we don't know which is being passed to us, but we want the ID.
* IDs **must** be cast as integers
*
* @package rabe
* @since version 1.0.0
* @uses get_broadcast(), get_broadcast_by()
*
* @param mixed $broadcast_slug_or_id The broadcast ID as an integer. Or Slug as string. Uses broadcast of current event if empty.
* @return int The corresponding broadcast (event-broadcast term) ID or false if not found.
*/

function get_broadcast_id_by_slugorid( $broadcast_slug_or_id = '' ) {

	$broadcast = $broadcast_slug_or_id;

	if( empty( $broadcast ) )
		return get_broadcast();

	if( is_int( $broadcast ) )
		return (int) $broadcast;

	$broadcast = get_broadcast_by( 'slug', $broadcast );

	if( $broadcast ) {
		$broadcast_id = $broadcast->term_id;
		return (int) $broadcast_id;
	} else {
		return false;
	}
}

/**
 * Get all broadcast data from database by broadcast field and data. This acts as a simple wrapper for  {@see `get_term_by()`}
 *
 * Warning: `$value` is not escaped for 'name' `$field`. You must do it yourself, if required.
 * 
 * If `$value` does not exist for that `$field`, the return value will be false other the term will be returned.
 *
 * ###Example
 * Get the broadcast ID by slug (A better way is to use {@see `get_broadcast_id_by_slugorid()`}
 * <code>
 *     $broadcast = get_broadcast_by('slug','my-broadcast-slug'); 
 *     if( $broadcast )
 *          $broadcast_id = (int) $broadcast->term_id;
 *</code>
 *
 * @package rabe
 * @uses get_term_by()
 * @since 1.0.0
 *
 * @param string $field Either 'slug', 'name', or 'id'
 * @param string|int $value Search for this term value
 * @param string $output Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional, default is raw or no WordPress defined filter will applied.
 * @return mixed Term Row from database. Will return false if $taxonomy does not exist or $term was not found.
 */
 function get_broadcast_by( $field, $value, $output = OBJECT, $filter = 'raw' ) {
    $broadcast = get_term_by( $field, $value, 'broadcast', $output, $filter );
    return $broadcast;
}

/**
* Returns the name of the broadcast of an event
* 
* If used without any arguments uses the broadcast of the current event.
*
* Returns the name of a broadcast specified by it's slug or ID. If used inside the loop, it can return the name of the current post's broadcast. If specifying the broadcast by ID, **the ID must be an integer**.
* This function behaves differently to {@see `get_broadcast_slug()`} which takes the event ID, rather than broadcast ID or slug, as an optional argument.
*
* ### Examples
* Inside the loop, you can output the current event's broadcast
* <code>
*      <?php echo get_broadcast_name(); ?>
* </code>   
* To get the name of event with id 7, you can use `get_broadcast` to obtain the broadcast ID of the event.
* <code>
*      <?php 
*         $broadcast_id = get_broadcast(7); 
*         $broadcast_name = get_broadcast_name($broadcast_id); 
*       ?>
* </code>
* 
* @package rabe
* @since 1.0.0
* @param int|string $broadcast_slug_or_id The broadcast ID (as an integer) or slug (as a string). Uses broadcast of current event if empty.
* @return string The name of the corresponding broadcast
 */
function get_broadcast_name( $broadcast_slug_or_id = '' ) {
    $broadcast_id = get_broadcast_id_by_slugorid( $broadcast_slug_or_id );
    $broadcast = get_term( $broadcast_id, 'broadcast' );
    
    if ( empty( $broadcast ) || is_wp_error( $broadcast ) )
	    return false;

    return $broadcast->name;
}

/**
* Echos the broadcast of the event
*
* @package rabe
* @since 1.0.0
* @uses get_broadcast_name()
* @param (int) broadcast id or (string) broadcast slug
*/
function broadcast_name( $broadcast_slug_or_id = '' ) {
    echo get_broadcast_name( $broadcast_slug_or_id );
}


/**
* Returns the permalink of a broadcast
* 
* If used with any arguments uses the broadcast of the current post. Removes the "/broadcast" of URL
* 
* @package rabe
* @since 1.0.0
* @uses get_term_link()
* @param int|string $broadcast_slug_or_id The broadcast ID (as an integer) or slug (as a string). Uses broadcast of current event if empty.
* @return string Link of the broadcast page
*/
function get_broadcast_link( $broadcast_slug_or_id='' ) {
    $broadcast_id =  get_broadcast_id_by_slugorid( $broadcast_slug_or_id );

	$rabe_options = get_option( 'rabe_option_name' );
	$general_broadcast = ( isset( $rabe_options['general_broadcast'] ) ) ? $rabe_options['general_broadcast'] : false ;
		
	// Don't link the "general" (unspecified) broadcast (@see settings.php)
	if ( $broadcast_id === $general_broadcast ) {
		return '#';
    // Build pretty URL when permalink is activated
	} elseif ( get_option( 'permalink_structure' ) ) {
	    $broadcast = get_term( $broadcast_id, 'broadcast' );
		return site_url( '/' . $broadcast->slug );
	// Else just get link
	} else {
		return get_term_link( $broadcast_id, 'broadcast' );
	}
}

/**
* Prints the permalink of a broadcast
* 
* If used with any arguments uses the broadcast of the current event.
* 
* @package rabe
* @since 1.0.0
* @uses get_broadcast_link()
*
* @param int|string $broadcast_slug_or_id The broadcast ID (as an integer) or slug (as a string). Uses broadcast of current event if empty.
*/
function broadcast_link( $broadcast_slug_or_id = '' ) {
    $broadcast_id = get_broadcast_id_by_slugorid( $broadcast_slug_or_id );
    echo get_broadcast_link( $broadcast_slug_or_id );
}

/**
* Gets an array of active broadcasts
* 
* @package rabe
* @since 1.0.0
* @return array $active_broadcasts An object array of active broadcasts get_broadcast_link()
*
*/
function get_active_broadcasts() {
	$active_broadcasts = '';
	$active_broadcasts = get_terms( array(
		'taxonomy' => 'broadcast',
		'meta_query' =>	array(
				array(
					'key' => 'broadcast_archived', 
					'value' => '1', 
					'compare' => 'NOT EXISTS'
				)
			)
		)
	);
	return $active_broadcasts;
}


?>
