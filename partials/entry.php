<?php
/**
 * Template for entry
 *
 * Copied and adapted function omega_entry from omega/lib/functions/hooks.php
 * Inserts audio if there is some
 */
$rabe_options = get_option( 'rabe_option_name' );
$general_broadcast = ( isset( $rabe_options['general_broadcast'] ) ) ? $rabe_options['general_broadcast'] : false;
// home, archive, search, broadcast taxonomy archives and singular broadcast pages
if ( is_home() || is_archive() || is_search() || ( has_term( null, 'broadcast', null ) && ! has_term( $general_broadcast, 'broadcast', null ) && is_singular() )  ) {

	// Remove embedded http url string from embedded media
	add_filter( 'get_the_content', 'remove_embed_httpstring' );
	add_filter( 'get_the_content_limit', 'remove_embed_httpstring' );
	add_filter( 'get_the_excerpt', 'remove_embed_httpstring' );

	// Remove iframes
	add_filter( 'get_the_content', 'remove_iframes' );
	add_filter( 'get_the_content_limit', 'remove_iframes' );
	add_filter( 'get_the_excerpt', 'remove_iframes' );

	?>	
	<div <?php omega_attr( 'entry-summary' ); ?>>
	<?php
	
	// Are we on home, broadcast archive page or broadcast single page? entry.php is used by more_broadcast_posts()
	$broadcast = ( is_home() || is_tax( 'broadcast' ) || ( has_term( null, 'broadcast', null ) && is_singular() ) ) ? true : false;
	
	// Is there an image or an embedded media?
	$image_embed = false;
	
	if( get_theme_mod( 'post_thumbnail', 1 ) && has_post_thumbnail() ) {

		// When on home, broadcast archive or broadcast single post, take rabe_front_tile size of image, else get theme_mod size
		$size = ( $broadcast ) ? 'rabe_front_tile' : get_theme_mod( 'image_size' );
		
		if ( ! class_exists( 'Get_The_Image' ) ) {
			apply_filters ( 'omega_featured_image' , printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_post_thumbnail( get_the_ID(), $size, array('class' => $size ) ) ));
			$image_embed = true;
		} else {
			get_the_image( array( 'size' => $size ) );
			$image_embed = true;
		}
	}
	
	// Is there audio?
	$audio = false;
	
	// Get first audio if there is any on broadcast sites
	if ( rabe_get_first_attached_audio( get_the_ID() ) ) {
		$audio_content = rabe_get_first_attached_audio( get_the_ID() );
		$audio = true;
	// Get first audio (and only audio) on broadcast sites
	} elseif ( rabe_get_first_embed_audio( get_the_ID() ) ) {
		$audio_content = rabe_get_first_embed_audio( get_the_ID() );
		$audio = true;
	// Get first embed (such as audio, youtube, mixcloud and soundcloud ) when there is no image
	} elseif ( rabe_get_first_embed( get_the_ID() ) && ! $image_embed ) {
		echo rabe_get_first_embed( get_the_ID() );
		$image_embed = true;
	}
	
	// Print audio before excerpt on 
	if ( $broadcast && $audio ) {
		echo $audio_content;
	}

	// Custom excerpt limit for home and broadcast archives or broadcast single posts
	if ( $broadcast && $image_embed && $audio ) {
		// three line title + image/embed + audio leaves space for one line (approx 80 characters)
		$excerpt_chars_limit = 80;
	} elseif ( $broadcast && $image_embed ) {
		// three line title + image leaves space for two lines ( approx 160 characters)
		$excerpt_chars_limit = 160;
	} else {
		$excerpt_chars_limit = ( get_theme_mod( 'excerpt_chars_limit', 0 ) ) ? (int) get_theme_mod( 'excerpt_chars_limit' ) : false;
	}

	if ( 'excerpts' === get_theme_mod( 'post_excerpt', 'excerpts' ) ) {
		if ( isset( $excerpt_chars_limit ) ) {
			the_content_limit( $excerpt_chars_limit, get_theme_mod( 'more_text', '[Read more...]' ) );
		} else {
			the_excerpt();
		}
		// Include audio files in excerpt (when not a broadcast site)
		if ( ( is_archive() || is_search() ) && ! $broadcast ) {
			if ( $audio ) {
				echo $audio_content;
			}
		}
	}
	
	else {
		the_content( get_theme_mod( 'more_text' ) );
	}
?>	
	</div>
<?php 	
} else {
?>	
	<div <?php omega_attr( 'entry-content' ); ?>>
<?php 	
	the_content();
	wp_link_pages( array( 'before' => '<p class="page-links">' . '<span class="before">' . __( 'Pages:', 'rabe' ) . '</span>', 'after' => '</p>' ) );
?>	
	</div>
<?php 	
}

?>
