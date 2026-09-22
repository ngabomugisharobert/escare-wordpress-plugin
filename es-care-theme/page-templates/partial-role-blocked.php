<?php
/**
 * Signed-in user tried to open the other role's page.
 *
 * @package ES_Care
 *
 * @var string $block employer-on-seeker|seeker-on-employer
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $block ) ) {
	$block = escare_cross_role_block();
}

$is_seeker_blocked = 'employer-on-seeker' === $block;
?>
<div class="escare-empty">
	<div class="escare-wrap">
		<?php if ( $is_seeker_blocked ) : ?>
			<h1><?php esc_html_e( 'This page is for job seekers', 'es-care' ); ?></h1>
			<p><?php esc_html_e( 'You are signed in as an employer, so you cannot access job seeker pages.', 'es-care' ); ?></p>
		<?php else : ?>
			<h1><?php esc_html_e( 'This page is for employers', 'es-care' ); ?></h1>
			<p><?php esc_html_e( 'You are signed in as a job seeker, so you cannot access employer pages.', 'es-care' ); ?></p>
		<?php endif; ?>
		<p>
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'dashboard' ) ); ?>"><?php esc_html_e( 'Go to dashboard', 'es-care' ); ?></a>
			<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'es-care' ); ?></a>
		</p>
	</div>
</div>
