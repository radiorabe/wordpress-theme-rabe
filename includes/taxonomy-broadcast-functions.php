<?php
/**
 * Functions for broadcast taxonomy archive pages and single broadcast pages
 *
 * @since version 1.0.0
 * @package rabe
 */


/**
 * Manually add broadcast feed link, because somehow it's not working with rewrite stuff
 * 
 * @package rabe
 * @since version 1.0.0
 */
function broadcast_feed_links()
{
	if ( get_option( 'permalink_structure' ) ) {
		echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo( 'name' ) . ' &raquo; ' . __( 'Feed', 'rabe' ) . '" href="' . site_url( '/feed/' ) .'" />';
		echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo( 'name' ) . ' &raquo; ' . get_broadcast_name() . ' ' . __( 'Broadcast Feed', 'rabe' ) . '" href="' . site_url( '/broadcast/' . get_broadcast_slug() ) . '/feed/" />';
	} else {
		echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo( 'name' ) . ' &raquo; ' . __( 'Feed', 'rabe' ) . '" href="' . site_url( '/index.php?&feed=rss2' ) .'" />';
		echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo( 'name' ) . ' &raquo; ' . get_broadcast_name() . ' ' . __( 'Broadcast Feed', 'rabe' ) . '" href="' . site_url( '/index.php?broadcast=' . get_broadcast_slug() ) . '&feed=rss2" />';
	}
}
remove_action('wp_head', 'feed_links_extra', 3 );
remove_action('wp_head', 'feed_links', 2 ); 
add_action( 'wp_head', 'broadcast_feed_links' );


/**
 * Print broadcast title
 * 
 * @package rabe
 * @since version 1.0.0
 */
function broadcast_title() {
	
	// Get term_id
	$term_id = get_queried_object()->term_id;

	?>
	<h1 class="broadcast-title">
		<?php
		echo get_broadcast_name( $term_id );
		?>
	</h1>
	<?php
}


/**
 * Print broadcast image
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_broadcast_image( $term_id ) {
	
	// Show term image
	$term_image = get_term_meta( $term_id, 'broadcast_image' );
	
	// Do nothing when there is no image
	if ( empty( $term_image ) )
		return;
	
	// Get image attachment
	$term_image = wp_get_attachment_image_src( $term_image[0]['id'], 'rabe_tile' );
	
	// Get url
	$broadcast_image = $term_image[0];
	

	if ( ! empty( $term_image ) ) {
		?>
		<div class="broadcast-image">
			<img src="<?php echo $broadcast_image; ?>" alt="<?php echo get_broadcast_name( $term_id ); ?>" />
		</div>
		<?php
	}
}


/**
 * Print description of broadcast to left column of taxonomy broadcast
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_broadcast_description( $term_id ) {
	
	// Show term description.
	$term_description = term_description( $term_id );

	if ( ! empty( $term_description ) ) {
		?>
		<div class="title">
			<?php echo  __( 'Broadcast Description', 'rabe' ); ?>
		</div>
		<div class="broadcast-description">
			<?php echo $term_description; ?>
		</div>
		<?php
	}
}


/**
 * Print websites of broadcast to left column of taxonomy broadcast
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_broadcast_websites( $term_id ) {
	
	// Get broadcast URLs
	$websites = array(
		'email'      => get_term_meta( $term_id, 'broadcast_email', true ),
		'website'    => get_term_meta( $term_id, 'broadcast_website', true  ),
		'facebook'   => get_term_meta( $term_id, 'broadcast_facebook', true  ),
		'soundcloud' => get_term_meta( $term_id, 'broadcast_soundcloud', true  ),
		'mixcloud'   => get_term_meta( $term_id, 'broadcast_mixcloud', true  ),
		'youtube'    => get_term_meta( $term_id, 'broadcast_youtube', true  ),
		'vimeo'      => get_term_meta( $term_id, 'broadcast_vimeo', true  ),
	);

	if ( array_filter( $websites ) ) { ?>
		<div class="title">
			<?php echo  __( 'Broadcast websites', 'rabe' ); ?>
		</div>
		<div class="broadcast-websites">
			<ul>
			<?php

			// Print URL and add class of website type
			foreach ( $websites as $type => $website ) {
				if ( ! empty( $website ) ) {
					// Is it a mail address?
					$website = ( $type == 'email' ) ? 'mailto:' . $website : $website;
					// Is it a mail address and do we have a mailcontact page?
					$rabe_options = get_option( 'rabe_option_name' );
					$mailcontact_page = $rabe_options['mailcontact_page'];
					$website = ( $type === 'email' && isset( $mailcontact_page ) ) ? get_permalink( $mailcontact_page ) . '?broadcast_id=' . $term_id : $website;
					echo '<li><a href="' . $website . '" class="' . $type  . ' icon-' . $type . '"></a></li>';
				}
			}
			?>
			</ul>
		</div>
		<?php
	}
}


/**
 * Print members of broadcast to left column of taxonomy broadcast
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_broadcast_members( $term_id ) {

	$members = get_term_meta( $term_id, 'broadcast_members', true);

	if ( ! empty( $members ) ) { ?>
		<div class="title">
			<?php echo  __( 'Broadcast members', 'rabe' ); ?>
		</div>
		<div class="broadcast-members">
			<?php
			// Set Counter and count members
			$counter = 0;
			asort( $members );
			$members_count = count( $members );

			// Print clickable usernames
			foreach( $members as $broadcast_member => $user_login ) {
				$user_login = $user_login['broadcast_member'];
				$user_id = get_user_by( 'login', $user_login )->data->ID;
				
				// Mail contact page FIXME: Make configurable
				$rabe_options = get_option( 'rabe_option_name' );
				$mailcontact_page = get_permalink( $rabe_options['mailcontact_page'] );

				// User setting for allowing mailform
				$user_settings = get_user_meta( $user_id, 'rabe_user_settings', true );
				$allow_mailto = ( isset( $user_settings['allow_mailto'] ) ) ? $user_settings['allow_mailto'] : 1;
				
				if ( 1 === $allow_mailto && $mailcontact_page ) {
					echo '<a href="' . $mailcontact_page . '?mail_id=' . $user_id . '" title="' . __( 'Mail to broadcaster', 'rabe' ) . '" class="icon-mail">' . get_the_author_meta( 'display_name', $user_id ) . '</a>';
				} else {
					echo get_the_author_meta( 'display_name', $user_id );
				}
				// Print a comma, whent not last item
				if( ++$counter != $members_count ) {
					echo '<br />';
				}
			}
			?>
		</div>
		<?php
	}
}


/**
 * Print members of broadcast to left column of taxonomy broadcast
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $term_id ID of broadcast term
 */
function rabe_broadcast_times( $term_id ) {

	// Get broadcast object
	$broadcast = get_broadcast_by( 'id', $term_id );
	
	// Only run, when event-organiser is installed
	if ( ! function_exists( 'eo_get_events' ) ) return;

	$events = eo_get_events(
		array(
			'posts_per_page'	=> 10,
			'event_start_after'	=> 'now',
			'tax_query'			=> array(
				array(
					'taxonomy' => 'broadcast',
					'field'    => 'slug',
					'terms'    => $broadcast->slug,
				),
			)
		)
	);
	if ( $events ) { ?>
		<div class="title">
			<?php echo  __( 'Next broadcasts', 'rabe' ); ?>
		</div>
		<div class="broadcast-times">
			<ul id="eo-upcoming-dates" class="more-dates">
			<?php

			foreach ( $events as $event ) {
				// FIXME: Is this really needed?
				// setlocale( LC_TIME, 'de_CH' );
				printf(
				'<li>%s, %s - %s</li>',
					eo_get_the_start( 'D, j.n.', $event->ID, null, $event->occurrence_id ),
					eo_get_the_start( get_option( 'time_format' ), $event->ID, null, $event->occurrence_id ),
					eo_get_the_end( get_option( 'time_format' ), $event->ID, null, $event->occurrence_id )
				);
			}
			?>
			</ul>
				
			<?php
	 			//With the ID 'eo-upcoming-dates', JS will hide all but the next 5 dates, with options to show more.
			wp_enqueue_script( 'eo_front' );
			wp_reset_postdata();
			?>
		</div>
		<?php
	}
	wp_reset_query();
}


/**
 * Print broadcast portrait
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $term_id ID of broadcast term
 */
function rabe_broadcast_portrait( $term_id ) {

	$portrait = get_term_meta( $term_id, 'broadcast_portrait' );
	
	if ( ! empty( $portrait ) ) { 
		
		// Get media file url
		$portrait = $portrait[0]['url'];

		?>
		<div class="title">
			<?php echo  __( 'Broadcast portrait', 'rabe' ); ?>
		</div>
		<div class="broadcast-portrait">
			<?php
			// Set Counter and count members
				$attr = array(
					'src'      => $portrait,
					'preload'  => 'metadata'
					);
				echo wp_audio_shortcode( $attr );
			?>
		</div>
		<?php
	}
}


/**
 * Print brodcast infos into broadcast-side div
 * 
 * @package rabe
 * @since version 1.0.0
 */
function broadcast_info() {
	
	// Get term_id
	if ( is_tax( 'broadcast' ) ) {
		$term_id = get_queried_object()->term_id;
	} elseif ( is_single() ) {
		$term_id = get_broadcast();
	} else {
		echo '<pre>' . __( 'No $term_id specified in broadcast_info()', 'rabe' ) . '</pre>';
		break;
	}
	
	// Get "general" broadcast
	$rabe_options = get_option( 'general_broadcast' );
	$general_broadcast = $rabe_options['general_broadcast'];
	
	?>
	<div class="broadcast-side">
		<?php 
			// Check includes/broadcast.php for rabe_broadcast_ functions
			echo rabe_broadcast_image( $term_id );
			echo rabe_broadcast_description( $term_id );
			echo rabe_broadcast_websites( $term_id );
			// Don't display this on "genereal" broadcast
			// TESTME
			if ( ! $general_broadcast ) {
				echo rabe_broadcast_members( $term_id );
				echo rabe_broadcast_times( $term_id );
				echo rabe_broadcast_portrait( $term_id );
				echo broadcast_support_button( $term_id );
			}
		?>
	</div>
	<?php

}

/**
 * Print a support button on the broadcast page
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $term_id ID of broadcast taxonomy term
 */
function broadcast_support_button( $term_id ) {
	$rabe_options = get_option( 'rabe_option_name' );
	$support_page = $rabe_options['support_page'];
	if ( $support_page ) {
		?>
		<a href="<?php echo get_permalink( $support_page ) . '?broadcast-id=' . $term_id; ?>">
			<button><?php echo __( 'Support', 'rabe' ) . ' ' . get_broadcast_name( $term_id ); ?></button>
		</a>
		<?php
	}
}
	
/**
 * Replace omega_archive_title with taxonomy archive-title-function
 * 
 * @package rabe
 * @since version 1.0.0
 */
function broadcast_archive_title() {
	?>
	<header class="page-header">
		
		<div class="archive-title">
			<?php echo __('Broadcast', 'rabe'); ?>
		</div>
		<?php broadcast_title();?>		
	</header><!-- .page-header -->
	<?php 
}

/**
 * Replace omega_content from omega/lib/hooks.php with broadcast_content (without no-results)
 * 
 * Displays first post full and the rest in tiles
 * 
 * @package rabe
 * @since version 1.0.0
 * @uses broadcast_archive_title()
 */
function broadcast_content() {
	// Always print broadcast title
	broadcast_archive_title();
	
	if ( have_posts() ) :           
	
		/* Start the Loop */ 

		// Set variable for first post
		$first_post = true;

		// Get paged variable
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		while ( have_posts() ) : the_post();

			// Is it a specialpost?
			$specialpost = ( 1 === (int) get_post_meta( get_the_ID(), 'rabe_specialpost', true ) ) ? true : false;
			
			// Is it really first post on first page? Open a div with first-post class
			if ( $first_post && 1 === $paged ) {
				$first_post_div = true;
				echo '<div class="first-post">';			
			} else {
				$first_post_div = false;
			}
			
			?>
			<article <?php omega_attr( 'post' ); ?>>
			<div class="entry-wrap">
				<div class="text-wrap">
				<?php
				// Display first post (full post, take the_content())
				if ( $first_post_div ) {
					$first_post = false; // Now we are in first post, set false for next loop
					do_action( 'omega_before_entry' ); ?>
					<div <?php omega_attr( 'entry-content' ); ?>>
						<?php the_content(); ?>
					</div><?php
					do_action( 'omega_after_entry' );
				// Only print title on specialposts
				} elseif ( $specialpost ) {
					echo '<header class="entry-header">';
					get_template_part( 'partials/entry', 'title' );
					echo '</header><!-- .entry-header -->';
				// Normal posts
				} else {
					do_action( 'omega_before_entry' );
					do_action( 'omega_entry' );
					do_action( 'omega_after_entry' );
				}
				?>
				</div>
				<div class="hide-overflow"></div>
			</div>
			</article>
			<?php
			// Close div of first post
			if ( $first_post_div ) {
				echo '</div>';
			}
			
		endwhile; 
		
		do_action( 'omega_after_loop'); 
	endif;  
}

/**
 * Get more posts of a broadcast taxonomy term
 * 
 * @package rabe
 * @since version 1.0.0
 * @uses broadcast_archive_title()
 * @param int $term_id ID of actual broadcast
 */
function broadcast_more_posts( $term_id = '' ) {

	// Get posts of broadcast term
	$broadcast_more_posts_args = array(
		'post_type'		 => 'post',
		'post__not_in'	 => array( get_the_ID() ),
		'tax_query' => array(
			array(
				'taxonomy' => 'broadcast',
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	);

	// Cached query? Don't cache on developer sites
	global $develop;
	if ( ( false === ( $broadcast_more_posts = get_transient( 'broadcast_broadcast_more_posts' ) ) ) && ( false === $develop ) ) {
		$broadcast_more_posts = new WP_Query( $broadcast_more_posts_args );
		// Cache db query for an hour
		set_transient( 'broadcast_broadcast_more_posts', $broadcast_more_posts, 60 * 60 );
	} else {
		$broadcast_more_posts = new WP_Query( $broadcast_more_posts_args );
	}

	if ( $broadcast_more_posts->have_posts() ) :           
	
		/* Start the Loop */ 

		while ( $broadcast_more_posts->have_posts() ) : $broadcast_more_posts->the_post();

			// Is it a specialpost?
			$specialpost = ( 1 === (int) get_post_meta( get_the_ID(), 'rabe_specialpost', true ) ) ? true : false;				

			?>
			<article <?php omega_attr( 'post' ); ?>>
			<div class="entry-wrap">
				<div class="text-wrap">
				<?php
				if ( $specialpost ) {
					// Only print title on specialposts
					echo '<header class="entry-header">';
					get_template_part( 'partials/entry', 'title' );
					echo '</header><!-- .entry-header -->';
				} else {
					// Normal posts
					do_action( 'omega_before_entry' );
					do_action( 'omega_entry' );
				}
				?>
				</div>
				<div class="hide-overflow"></div>
			</div>
			</article>
			<?php
			
		endwhile; 
		
		// Create link to broadcast page
		?>
		<div class="paging-navigation pagination navigation"><a class="page-numbers" href="<?php echo broadcast_link( $term_id ); ?>"><?php echo __( 'More of', 'rabe' ) . ' ' . get_broadcast_name( $term_id ); ?></a></div>
		<?php
		
		do_action( 'omega_after_loop'); 
	endif;  	

}
?>
