<?php
/**
 * Default page.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_portal = function_exists( 'escare_is_portal_surface' ) && escare_is_portal_surface();
?>
<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>
	<header class="escare-page-hero<?php echo $is_portal ? ' escare-page-hero--portal' : ''; ?>">
		<div class="escare-wrap">
			<h1><?php the_title(); ?></h1>
		</div>
	</header>
	<div class="escare-page-body<?php echo $is_portal ? ' escare-page-body--portal' : ''; ?>">
		<div class="escare-wrap">
			<?php if ( $is_portal ) : ?>
				<div class="escare-portal-stage">
					<?php the_content(); ?>
				</div>
			<?php else : ?>
				<div class="escare-prose">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endwhile; ?>
<?php
get_footer();
