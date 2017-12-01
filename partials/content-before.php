<?php
/**
 * Posts area before content
 *
 * @package RaBe
 */

/***********************************************************************
TODO, FIXME: Maybe load all this in a dynamic sidebar?! would be nicer

if ( is_active_sidebar( 'before_content' ) ) : ?>

	<aside class="header widget-area <?php echo apply_atomic( 'omega_sidebar_class', 'sidebar' );?>">
		
		<?php
			dynamic_sidebar( 'before_content' );
			// Drei Sachen kommen hier hin
			// - Letzer Info-Podcast
			// - Irgendwelche wichtige News
			// - Radio-Webplayer
		?>

  	</aside><!-- .sidebar -->

<?php endif;  ?> 
***********************************************************************/

// Load latest info post and one sticky post only on home
if ( is_home() ) {

	// Define global variable for posts to be excluded later ...
	global $excluded_posts;

	// Define global variable for info broadcast
	global $info_broadcast;

	// Get info broadcast id
	$rabe_options = get_option( 'rabe_option_name' );
	$info_broadcast = ( isset( $rabe_options['info_broadcast'] ) ) ? $rabe_options['info_broadcast'] : 0;

	// Get latest info post
	function rabe_latest_info_post() {
		global $excluded_posts, $info_broadcast, $not_in_header;

		$latest_info_post_args = array(
			'post_type'			  => 'post',
			'posts_per_page' 	  => 1,
			'ignore_sticky_posts' => 1,
			'category__not_in'	  => array( $not_in_header ),
			'tax_query' 		  => array(
				array(
					'taxonomy' => 'broadcast',
					'field'    => 'ID',	
					'terms'    =>  $info_broadcast,
				),
			),
		);
		$latest_info_post_query = new WP_Query( $latest_info_post_args );

		// Don't need a while loop, we want only one post
		if ( $latest_info_post_query->have_posts() ) {
			$latest_info_post_query->the_post();
			// Get podcast ?>
			<article <?php omega_attr( 'post' ); ?>>
				<div class="entry-wrap header-post first-header-post">
					<?php
					$excluded_posts[] = get_the_ID();
					echo '<header class="entry-header">';
					// get_template_part( 'partials/entry', 'title' ); 
					echo '<h2 class="entry-title" itemprop="headline"><a href="' . get_the_permalink() . '" rel="bookmark">' . get_broadcast_name( $info_broadcast ) . ': ' . get_the_title() .'</a></h2>';
					echo '</header><!-- .entry-header -->';
					if ( rabe_get_first_attached_audio( get_the_ID() ) ) {
						echo rabe_get_first_attached_audio( get_the_ID() );
					}
					?>
				</div>
			</article>
			<?php
		}
		wp_reset_query();

	}

	// Get latest header post
	function rabe_header_post() {
		global $excluded_posts, $not_in_header;
		
		$sticky = get_option( 'sticky_posts' );
		
		$latest_sticky_post_args = array(
			'posts_per_page'      => 1,
			'post__in'            => $sticky,
			'ignore_sticky_posts' => 1,
			'category__not_in'	  => array( $not_in_header ),
			'post_not__in'		  => $excluded_posts
		);

		$has_stickies = ( is_array( $sticky ) ) ? true : false;
		
		if ( $has_stickies )  {
			
			$latest_sticky_post_query = new WP_Query( $latest_sticky_post_args );
			
			$latest_sticky_post_query->the_post();
			// We want only the title ?>
				<article <?php omega_attr( 'post' ); ?>>
				<div class="entry-wrap header-post">
					<?php
					$excluded_posts[] = get_the_ID();
					echo '<header class="entry-header">';
					get_template_part( 'partials/entry', 'title' );
					echo '</header><!-- .entry-header -->';
					?>
				</div>
				</article>
			<?php
			
		} 
		wp_reset_query();

	}
}


// Build content-before div
?>
<div class="content-before">
	<?php // Live Player with float:right ?>
	<div class="live-player">
		<div id="ticker">
			<?php
				// Schedule
				rabe_schedule();
			?>
		</div>
		<div id="webplayer">
			<?php
				// Actual player
				rabe_live_player();
			?>
		</div>
	</div>
	<?php
		if ( is_home() ) {
			// Latest Info Podcast
			rabe_latest_info_post();

			// Latest sticky post
			rabe_header_post();
		}
	?>
</div>
