<?php
/**
 * Single job template.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

get_header();

$job_id  = get_the_ID();
$open    = ESC_Portal_Helpers::is_job_open( $job_id );
$loc     = get_post_meta( $job_id, '_esc_location', true );
$emp     = get_post_meta( $job_id, '_esc_employment_type', true );
$shift   = get_post_meta( $job_id, '_esc_shift', true );
$pay     = get_post_meta( $job_id, '_esc_pay_range', true );
$closing = get_post_meta( $job_id, '_esc_closing_date', true );
$types   = ESC_Portal_Helpers::employment_types();
$terms   = get_the_terms( $job_id, 'esc_job_category' );
$portal_user = ESC_Portal_Auth::current_user();
$can_apply   = ! $portal_user || ESC_Portal_Users::is_seeker( $portal_user );
?>
<main class="esc-portal-wrap esc-single-job">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<?php echo ESC_Portal_Helpers::render_query_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article class="esc-card" id="job-<?php the_ID(); ?>">
				<p class="esc-kicker"><?php esc_html_e( 'Open role', 'es-care-portal' ); ?></p>
				<h1><?php the_title(); ?></h1>
				<p class="esc-meta">
					<?php if ( $loc ) : ?><span><?php echo esc_html( $loc ); ?></span><?php endif; ?>
					<?php if ( isset( $types[ $emp ] ) ) : ?><span><?php echo esc_html( $types[ $emp ] ); ?></span><?php endif; ?>
					<?php if ( $shift ) : ?><span><?php echo esc_html( $shift ); ?></span><?php endif; ?>
					<?php if ( $pay ) : ?><span><?php echo esc_html( $pay ); ?></span><?php endif; ?>
					<?php if ( $closing ) : ?><span><?php echo esc_html( sprintf( __( 'Closes %s', 'es-care-portal' ), $closing ) ); ?></span><?php endif; ?>
					<span class="esc-status esc-status--<?php echo $open ? 'open' : 'closed'; ?>"><?php echo $open ? esc_html__( 'Open', 'es-care-portal' ) : esc_html__( 'Closed', 'es-care-portal' ); ?></span>
				</p>
				<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
					<p class="esc-tags">
						<?php foreach ( $terms as $term ) : ?>
							<span><?php echo esc_html( $term->name ); ?></span>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>
				<div class="esc-content">
					<?php the_content(); ?>
				</div>
				<p class="esc-actions">
					<?php if ( $open && $can_apply ) : ?>
						<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Apply::apply_url( $job_id ) ); ?>"><?php esc_html_e( 'Apply for this role', 'es-care-portal' ); ?></a>
					<?php elseif ( $open && $portal_user ) : ?>
						<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'dashboard' ) ); ?>"><?php esc_html_e( 'Go to dashboard', 'es-care-portal' ); ?></a>
					<?php elseif ( ! $open ) : ?>
						<span class="esc-notice esc-notice--info"><?php esc_html_e( 'This position is no longer accepting applications.', 'es-care-portal' ); ?></span>
					<?php endif; ?>
					<a class="esc-button esc-button--ghost" href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'careers' ) ); ?>"><?php esc_html_e( 'All careers', 'es-care-portal' ); ?></a>
				</p>
			</article>
			<?php
		endwhile;
		endif;
		?>
</main>
<?php
get_footer();
