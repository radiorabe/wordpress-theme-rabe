<?php
/**
 * Header menu template
 * 
 * @package rabe
 * @since version 1.0.0
 */
?>	
<nav class="nav-header" <?php omega_attr( 'menu' ); ?>>
	<div class="wrap">
		<?php
		// Because of "float: right" in stylesheet, everything in this div is in reverse order
				
		// Search form (magnifier glass)
		?>
		<form role="search" method="get" class="nav-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">	
			<label for="s" class="icon-search">
				<input id="s" type="search" class="nav-search-field" placeholder="<?php echo esc_attr_x( 'Search ...', 'placeholder', 'rabe' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" title="<?php _e( 'Search for:', 'rabe' ); ?>">
				<input type="submit" class="search-submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'rabe' ); ?>">
			</label>
		</form>
		<?php

		// Get theme settings
		$rabe_options = get_option( 'rabe_option_name' );

		// Get contact page from settings and put it in an array
		$contactlink_arr = ( isset( $rabe_options['contact_page'] ) ) ? array( 'mail' => get_page_link( $rabe_options['contact_page'] ) ) : '';

		// Print contactlinks
		if ( $contactlink_arr ) {
			foreach ( $contactlink_arr as $type => $contactlink ) {
				if ( $contactlink ) {
					echo '<a class="icon-' . $type . '" href="' . $contactlink . '"></a>';
				}
			}
		}

		// Get receiving page from settings
		$receiving_page = ( isset( $rabe_options['receiving_page'] ) ) ? get_page_link( $rabe_options['receiving_page'] ) : null;

		if ( $receiving_page ) {
			echo '<a class="icon-signal" href="' . $receiving_page . '"></a>';
		}
		
		// Is polylang plugin activated? Then show language chooser
		if ( defined( 'POLYLANG_VERSION' ) ) {
			print_rabe_polylang_chooser();
		}

		?>
	</div>
</nav><!-- .nav-header -->
