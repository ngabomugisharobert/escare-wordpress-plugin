<?php
/**
 * Fallback index.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="escare-page-body">
	<div class="escare-wrap">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<article <?php post_class( 'escare-section' ); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<div class="escare-empty">
				<h1><?php esc_html_e( 'Nothing to show yet', 'es-care' ); ?></h1>
				<p><a class="escare-btn escare-btn--solid" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'es-care' ); ?></a></p>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
