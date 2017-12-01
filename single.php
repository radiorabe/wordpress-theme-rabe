<?php
/**
 * Template for displaying all single posts.
 *
 * If post is of a broadcast, display it the same way broadcast archives are shown
 * 
 * @since version 1.0.0
 * @package rabe
 */

get_header();

$rabe_options = get_option( 'rabe_option_name' );
$general_broadcast = ( isset( $rabe_options['general_broadcast'] ) ) ? $rabe_options['general_broadcast'] : false;

// Check if post hast a broadcast taxonomy term, but not general broadcast term
if ( has_term( null, 'broadcast', null ) && ! has_term( $general_broadcast, 'broadcast', null ) ) {
	
	// include taxonomy broadcast template functions
	require_once( 'includes/taxonomy-broadcast-functions.php' );

	// Add broadcast taxonomy info pane
	add_action('omega_before_content', 'broadcast_info' );

	// Replace omega_content function, if broadcast has no posts, don't display no-results
	remove_action( 'omega_content', 'omega_content' );
	add_action( 'omega_content', 'broadcast_content' );
	
	?>
	<main  class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>
		<?php 
		do_action( 'omega_before_content' );
		?>
		<div class="broadcast-content">
			<div class="first-post">
			<?php
			do_action( 'omega_content' );
			?>
			</div><?php
			// Get more posts from this broadcast
			broadcast_more_posts( get_broadcast() );
			do_action( 'omega_after_content' );
			?>
		</div>
	</main><!-- .content -->
	<?php

// Normal post or page without taxonomy broadcast term (@see omega/single.php)
} else {
	?>
	<main class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>
		<?php
		do_action( 'omega_before_content' );
		do_action( 'omega_content' );
		do_action( 'omega_after_content' );
		?>
	</main><!-- .content -->
	<?php
}

get_footer(); ?>
