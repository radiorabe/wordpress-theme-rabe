<?php
/**
 * Template for displaying broadcast archive pages.
 *
 * @since version 1.0.0
 * @package rabe
 */

// include taxonomy broadcast template functions
require_once( 'includes/taxonomy-broadcast-functions.php' );

// Add broadcast taxonomy info pane
add_action('omega_before_content', 'broadcast_info');

// Replace omega_content function, if broadcast has no posts, don't display no-results
remove_action( 'omega_content', 'omega_content');
add_action( 'omega_content', 'broadcast_content');

// Start real taxonomy-broadcast.php template
get_header(); ?>
<main  class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>
	
	<?php 
	do_action( 'omega_before_content' );
	?>
	<div class="broadcast-content">
		<?php 
		do_action( 'omega_content' );
		do_action( 'omega_after_content' );
		?>	
	</div>

</main><!-- .content -->
<?php get_footer(); ?>
