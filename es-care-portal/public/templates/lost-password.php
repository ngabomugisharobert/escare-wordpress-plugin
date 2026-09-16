<?php
/**
 * Lost password.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="esc-portal-wrap esc-portal-wrap--auth">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<div class="esc-card esc-card--narrow">
		<h2><?php esc_html_e( 'Reset your password', 'es-care-portal' ); ?></h2>
		<p><?php esc_html_e( 'Enter the email on your account. If it is registered, we will send a reset link.', 'es-care-portal' ); ?></p>
		<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'esc_lost_password', 'esc_lost_password_nonce' ); ?>
			<input type="hidden" name="action" value="esc_lost_password">
			<p class="esc-field">
				<label for="esc_email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label>
				<input type="email" id="esc_email" name="esc_email" required autocomplete="email">
			</p>
			<p><button type="submit" class="esc-button"><?php esc_html_e( 'Send reset link', 'es-care-portal' ); ?></button></p>
		</form>
	</div>
</div>
