<?php
/**
 * The home template file.
 * 
 * @since version 1.0.0
 * @package rabe
 */

// Don't display entry footers on front-page
remove_action( 'omega_after_entry', 'omega_entry_footer' );

// Get develop var from dev.php
global $develop;

get_header(); ?>

<main class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>
	<?php 
	do_action( 'omega_before_content' );
	
	/**
	 * Replace main query with own query because of duplicates in content-before (@see partials/content-before.php)
	 * 
	 * Function omega_content was copied and adapted from omega/lib/functions/hooks.php
	 */
	if ( have_posts() ) : 		

		do_action( 'omega_before_loop' );
		
		// get excluded posts from content_before
		global $excluded_posts;
		
		// Fix pagination with custom query
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		// Get current time
		$datetime = current_time( 'Y-m-d H:i' );

		// Get quantitiy of specialposts on frontpage
		$rabe_options = get_option( 'rabe_option_name' );
		$specialposts_per_page = $rabe_options['specialposts_per_page'];
		$posts_per_page = get_option( 'posts_per_page' );
	
		// First page
		if ( 1 === $paged ) {

			/*
			 * Execute following SQL statement to activate rabe_expiretime in all posts
			 * Can be used for specialposts too
			 * 
			 * INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
			 * SELECT wp_posts.ID, 'rabe_expiretime', '2016-07-14 22:00'
			 * FROM wp_posts
			 * WHERE wp_posts.post_status = 'publish' and wp_posts.post_type = 'post'
			 */
			
			// Actual posts_per_page for frontpage (posts_per_page minus specialposts_per_page)
			$posts_per_page_first = $posts_per_page - $specialposts_per_page;

			// Custom home query args without excluded posts (from content-before) and without special posts which are display randomly
			$home_query_first_page_args = array(
				'post_type'		 => 'post',
				'posts_per_page' => $posts_per_page_first,
				'post__not_in'	 => $excluded_posts,
				'paged'			 => $paged,
				'meta_query'	 => array(
					'relation' => 'AND',
					array(
						'key'     => 'rabe_specialpost',
						'value'   => 1,
						'compare' => '!=',
					),
					array(
						'relation' => 'OR',
						array(
							'key' 	  => 'rabe_expired',
							'value'	  => 1,
							'compare' => '!=',
						),
						array(
						'relation' => 'AND',
							array(
								'key'	  => 'rabe_expired',
								'value'	  => 1,
								'compare' => '=',
							),
							array(
								'key'	  => 'rabe_expiretime',
								'value'	  => $datetime,
								'compare' => '>' // rabe_expiretime is in the past
							),
						),
					),
				),
			);
			
			// Add randomly specialposts filter
			add_filter( 'the_posts', 'rabe_insert_specialposts', 10, 2 );

			// Cached query? Don't cache on developer sites
			if ( ( false === ( $home_query_first_page = get_transient( 'home_query_first_page' ) ) ) && ( false === $develop ) ) {
				$home_query_first_page = new WP_Query( $home_query_first_page_args );
				// Cache db query for ten minutes
				set_transient( 'home_query_first_page', $home_query_first_page, 60 * 10 );
			} else {
				$home_query_first_page = new WP_Query( $home_query_first_page_args );
			}
			
			$home_query = $home_query_first_page;
			
			// Remove specialposts filter
			remove_filter( 'the_posts', 'rabe_insert_specialposts', 10, 2 );
			
		} else {
			// Manually calculate page_offset because of specialposts
			// See: https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination#The_Problem
			$page_offset = ( ( $paged - 1 ) * $posts_per_page ) - $specialposts_per_page;

			// Custom home query args with specialposts, they aren't displayed randomly anymore
			$home_query_following_pages_args = array(
				'post__not_in' => $excluded_posts,
				'paged'		   => $paged,
				'offset'	   => $page_offset,
				'meta_query'   => array(
					'relation' => 'OR',
					// Display all posts without expired flag
					array(
						'key'     => 'rabe_expired',
						'value'   => 1,
						'compare' => '!=',
					),
					array(
						'relation' => 'AND',
						// Hide all posts with expired flag and date in past
						array(
							'key'	  => 'rabe_expired',
							'value'	  => 1,
							'compare' => '=',
						),
						array(
							'key'	  => 'rabe_expiretime',
							'value'	  => $datetime,
							'compare' => '>' // rabe_expiretime is in the past
						),
					),
				),
			);
			
			$home_query = new WP_Query( $home_query_following_pages_args );

		}

		/* Start custom loop Loop */
		while ( $home_query->have_posts() ) : $home_query->the_post();
			?>
			<article <?php omega_attr( 'post' ); ?>>
				<div class="entry-wrap">
					<div class="text-wrap">
					<?php
						
					// Is it a specialpost?
					$specialpost = ( 1 === (int) get_post_meta( get_the_ID(), 'rabe_specialpost', true ) ) ? true : false;
					
					if ( $specialpost ) {
						// Only print title
						echo '<header class="entry-header">';
						get_template_part( 'partials/entry', 'title' );
						echo '</header><!-- .entry-header -->';
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

		endwhile; 
		
		do_action( 'omega_after_loop' );			

	else : 
			get_template_part( 'partials/no-results', 'archive' ); 
	endif;
	
	wp_reset_query();

	// do_action( 'omega_content' ); 		
	do_action( 'omega_after_content' ); 
	?>
	
</main><!-- .content -->

<?php get_footer(); ?>
