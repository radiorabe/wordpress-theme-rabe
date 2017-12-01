<?php
// Is it a specialpost?
if ( 1 === (int) get_post_meta( get_the_ID(), 'rabe_specialpost', true ) ) {
	
	// Does it have an external link?
	$external_url = get_post_meta( get_the_ID(), 'rabe_external_url', true );

	// Replace permalink with external link
	if ( empty( $external_url ) ) {
		$link = get_permalink();
	} else {
		$link = $external_url;
	}
	
	?>
	<h2 class="entry-title" itemprop="headline"><a href="<?php echo $link; ?>" target="_blank" rel="bookmark"><?php the_title(); ?></a></h2>
	<?php
	
// Normal title on plural posts page
} elseif ( is_home() || is_archive() || is_search() || has_term( null, 'broadcast', null ) ) {
	?>
	<h2 class="entry-title" itemprop="headline"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	<?php	
} else {
	?>
	<h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>
	<?php
}
?>
