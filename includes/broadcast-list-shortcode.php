<?php
/**
 * Returns a searchable list of broadcasts
 *	
 * @package rabe
 * @since 1.0.0
 * @link https://paulund.co.uk/add-scripts-shortcode-used
 * @return $shortcode List of broadcasts with a search box
 * @package rabe
 *    
 */
 
$shortcode = new Broadcast_List_Class();

class Broadcast_List_Class {

	// Should scripts be loaded or not?
    private $add_shortcode_script = false;

    public function __construct() {
		
		// Shortcode is [searchable_broadcast_list]
        add_shortcode( 'searchable_broadcast_list', array( $this, 'print_searchable_broadcast_list' ) );

        add_action( 'wp_footer', array( $this, 'broadcast_list_scripts' ) );
        add_action( 'wp_footer', array( $this, 'broadcast_list_noscript_css' ) );
    }

    public function print_searchable_broadcast_list( $attr, $content ) {
        $this->add_shortcode_script = true;
        
		// Search box
		$shortcode = '<input type="text" placeholder="' . _x( 'Search ...', 'placeholder', 'rabe' ) . '" id="broadcasts-search">';
		
		$args = array(
			'hide_empty' => false,
			'taxonomy'	 => 'broadcast',
			'title_li'	 => '',
			'echo'		 => false,
			'walker'	 => new Broadcast_Walker_Category
		);
		
		// Broadcast list
		$shortcode .= '<ul class="broadcast-list-shortcode">' . wp_list_categories( $args ) . '</ul>';

		return $shortcode;
    }

    public function broadcast_list_scripts() {
        if ( ! $this->add_shortcode_script ) {
            return false;
        }

		// Include Javascript
		wp_enqueue_script( 'broadcasts-search', get_stylesheet_directory_uri() . '/js/broadcasts-search.js', false );
    }
    
    public function broadcast_list_noscript_css() {
        if( ! $this->add_shortcode_script ) {
            return false;
        }

		?>
		<noscript>
			<style type="text/css">
				input { display: none; }
			</style>
		</noscript>
		<style id="searchstyle"></style>
		<?php
		
    }
}

/**
 * Create custom walker class by extending Walker_Category for shortcode
 * Copied, shortened and adapted from Walker_Category
 * 
 * @package rabe
 * @since 1.0.0
 */
class Broadcast_Walker_Category extends Walker_Category {
 
    public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $cat_name = apply_filters(
            'list_cats',
            esc_attr( $category->name ),
            $category
        );
        
        // Don't include archived broadcasts
		$archived = get_term_meta( $category->term_id, 'broadcast_archived', true );
        if ( ! $cat_name || $archived ) {
            return;
        }
        
        // Change URL, no /broadcasts folder
        // $link = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';
        $link = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';
        if ( $args['use_desc_for_title'] && ! empty( $category->description ) ) {

            $link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
        }
        $link .= '>';
        $link .= $cat_name . '</a>';
        if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
            $link .= ' ';
            if ( empty( $args['feed_image'] ) ) {
                $link .= '(';
            }
             $link .= '<a href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $args['feed_type'] ) ) . '"';
            if ( empty( $args['feed'] ) ) {
                $alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
            } else {
                $alt = ' alt="' . $args['feed'] . '"';
                $name = $args['feed'];
                $link .= empty( $args['title'] ) ? '' : $args['title'];
            }
            $link .= '>';
            if ( empty( $args['feed_image'] ) ) {
                $link .= $name;
            } else {
                $link .= "<img src='" . $args['feed_image'] . "'$alt" . ' />';
            }
            $link .= '</a>';
            if ( empty( $args['feed_image'] ) ) {
                $link .= ')';
            }
        }
        if ( ! empty( $args['show_count'] ) ) {
            $link .= ' (' . number_format_i18n( $category->count ) . ')';
        }
        if ( 'list' == $args['style'] ) {
            $output .= "\t<li";
            $css_classes = array(
                'cat-item',
                'cat-item-' . $category->term_id,
            );
            if ( ! empty( $args['current_category'] ) ) {
                $_current_terms = get_terms( $category->taxonomy, array(
                    'include' => $args['current_category'],
                    'hide_empty' => false,
                ) );
                foreach ( $_current_terms as $_current_term ) {
			        if ( $category->term_id == $_current_term->term_id ) {
                        $css_classes[] = 'current-cat';
                    } elseif ( $category->term_id == $_current_term->parent ) {
                        $css_classes[] = 'current-cat-parent';
                    }
                    while ( $_current_term->parent ) {
                        if ( $category->term_id == $_current_term->parent ) {
                            $css_classes[] =  'current-cat-ancestor';
                            break;
                        }
                        $_current_term = get_term( $_current_term->parent, $category->taxonomy );
                    }
                }
            }
            $css_classes = implode( ' ', apply_filters( 'category_css_class', $css_classes, $category, $depth, $args ) );
            $output .=  ' class="' . $css_classes . '"';
            
            // Add data-index for searchable fields
            $output .= ' data-index="' . strtolower( str_replace( ' ', '', ( $category->name ) ) ) . '" ';
            $archived = get_term_meta( $category->term_id, 'broadcast_archived', true );
            
            $output .= ">$link\n";
        } elseif ( isset( $args['separator'] ) ) {
            $output .= "\t$link" . $args['separator'] . "\n";
        } else {
            $output .= "\t$link<br />\n";
        }
    } 
}

?>
