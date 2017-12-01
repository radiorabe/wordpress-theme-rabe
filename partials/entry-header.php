<?php
/*
 * Replacing omega_entry_header from omega/lib/functions/hooks.php with rabe_entry_header
 * We want post meta before title
 */

echo '<header class="entry-header">';

if ( 'post' == get_post_type() ) : 
		get_template_part( 'partials/entry', 'byline' ); 
endif; 

get_template_part( 'partials/entry', 'title' ); 

echo '</header><!-- .entry-header -->';

?>	
