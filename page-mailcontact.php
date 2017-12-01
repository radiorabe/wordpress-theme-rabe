<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package rabe
 */

get_header(); ?>
<main  class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>
	<?php 
	do_action( 'omega_before_content' ); 
	?>
	
		<article <?php omega_attr( 'post' ); ?>><div class="entry-wrap">
			<?php 

			// It's message to a certain user
			if ( isset( $_GET['mail_id'] ) ) {
				$user_id = $_GET['mail_id'];
				$contact_name = get_the_author_meta( 'display_name', $user_id );
				$contact_mail = get_the_author_meta( 'user_email', $user_id );
				$contact_title = $contact_name;
				$contact = true;
			// It's a message to a broadcast
			} elseif ( isset( $_GET['broadcast_id'] ) ) {
				$broadcast_id = $_GET['broadcast_id'];
				$contact_mail = get_term_meta( $broadcast_id, 'broadcast_email', true );
				$broadcast = get_term_by( 'id', $broadcast_id, 'broadcast' );
				$contact_name = $broadcast->name;
				$broadcast_mail = '<p><a href="mailto:' . $contact_mail . '">' . $contact_mail . '</a></p>';
				$contact = ( isset( $contact_mail ) ) ? true : false;
			} else {
				$contact = false;
				echo '<p>' . __( 'No contact specified.', 'rabe' ) . '</p>';
			}

			// Get mail_form
			$rabe_options = get_option( 'rabe_option_name' );
			$form_id = $rabe_options['mail_form']; 

			if ( $contact &&  isset( $form_id ) )  {
				echo '<h1 class="entry-title" itemprop="headline">' . __( 'Mail to', 'rabe' ) . ' ' . $contact_name . '</h1>' . $broadcast_mail;

				echo $si_contact_form->si_contact_form_short_code(
					array(
						'form' => $form_id,
						'email_to' => "$contact_name,$contact_mail",
					)
				);
			} else {
				echo '<p>' . __( 'No mail contact form active.', 'rabe' ) . '</p>';
			}

			
			do_action( 'omega_after_entry' ); 
			?>
		</div></article>

	<?php
	do_action( 'omega_after_content' ); 
	?>
</main><!-- .content -->
<?php get_footer(); ?>
