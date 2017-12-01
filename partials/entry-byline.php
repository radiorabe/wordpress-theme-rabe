<?php
/**
 * Template for entry byline
 * 
 * On home, front-page and archive pages, show broadcast+date
 * On other pages (single post, broadcast archive), show author+date
 * 
 * @package rabe
 * @since version 1.0.0
 */

// Get broadcast object
$broadcast = get_broadcast();
?>

<div class="entry-meta">
	<?php
	// Print broadcast name when it's a broadcast or on a plural page
	if ( $broadcast && ! is_tax( 'broadcast' ) && ( is_home() || is_search() || is_archive() ) ) {
		?>
		<span><a rel="bookmark" href="<?php echo broadcast_link( $broadcast ); ?>"><?php echo broadcast_name( $broadcast ) ?></a></span>
		<span class="middot"> &middot; </span>
		<time <?php omega_attr( 'entry-published' ); ?>><?php echo get_the_date(); ?></time>
		<?php
	// Else print standard omega entry-byline (from omega/partials/entry-byline.php) with author and such
	} else {
		?>
		<time <?php omega_attr( 'entry-published' ); ?>><?php echo get_the_date(); ?></time>
		<span class="middot"> &middot; </span>
		<span <?php omega_attr( 'entry-author' ); ?>><?php the_author_posts_link(); ?></span>
		<?php	
	}
	
	edit_post_link( __('Edit', 'rabe'), ' | ' ); ?>
</div><!-- .entry-meta -->
