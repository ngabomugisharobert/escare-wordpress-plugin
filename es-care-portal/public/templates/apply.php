<?php
/**
 * Apply form.
 *
 * @package ESC_Portal
 *
 * @var int    $job_id
 * @var bool   $open
 * @var int    $existing
 * @var array  $profile
 * @var array  $settings
 * @var object $user
 */

defined( 'ABSPATH' ) || exit;

$user = isset( $user ) ? $user : ESC_Portal_Auth::current_user();
?>
<div class="esc-portal-wrap esc-apply-wrap">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<header class="esc-hero">
		<p class="esc-kicker"><?php esc_html_e( 'Job application', 'es-care-portal' ); ?></p>
		<h2 class="esc-app-title"><?php esc_html_e( 'Candidate Registration', 'es-care-portal' ); ?></h2>
		<p><?php echo esc_html( get_the_title( $job_id ) ); ?> — <a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>"><?php esc_html_e( 'View job details', 'es-care-portal' ); ?></a></p>
	</header>

	<?php if ( $existing ) : ?>
		<p class="esc-notice esc-notice--info"><?php esc_html_e( 'You have already applied for this position.', 'es-care-portal' ); ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'apply' ) ); ?>"><?php esc_html_e( 'View applications', 'es-care-portal' ); ?></a>
		</p>
	<?php elseif ( ! $open ) : ?>
		<p class="esc-notice esc-notice--error"><?php esc_html_e( 'This position is no longer accepting applications.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<form class="esc-form esc-app-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'esc_apply', 'esc_apply_nonce' ); ?>
			<input type="hidden" name="action" value="esc_apply">
			<?php
			$show_job_select = false;
			include ESC_PORTAL_DIR . 'public/templates/partials/application-fields.php';
			?>
			<p class="esc-actions"><button type="submit" class="esc-button"><?php esc_html_e( 'Submit application', 'es-care-portal' ); ?></button></p>
		</form>
	<?php endif; ?>
</div>
