<?php
/**
 * Player page
 *
 * @package rabe
 * @since version 1.0.0
 */


// Stuff below is taken from omega templates
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> <?php omega_attr( 'body' ); ?>>
<?php do_action( 'omega_before' ); ?>
<div class="<?php echo omega_apply_atomic( 'site_container_class', 'site-container' );?>">


<div class="home">
	<div class="live-player">
		
		<div id="ticker">
			<?php
				// Schedule
				rabe_schedule(); // includes/player-functions.php
			?>
		</div>
		
		<div id="webplayer">
			<?php rabe_live_player(); // includes/player-functions.php ?>
		</div>
		
	</div>
</div>

<?php 

// Taken from omega footer.php 
do_action( 'omega_after' ); ?>
<?php wp_footer(); ?>
</body>
</html>
