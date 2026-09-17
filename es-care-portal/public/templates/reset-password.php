<?php
/**
 * Reset password.
 *
 * @package ESC_Portal
 *
 * @var string $key   Reset key.
 * @var string $login User login.
 * @var bool   $valid Whether the key is valid.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="esc-portal-wrap esc-portal-wrap--auth">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<div class="esc-card esc-card--narrow">
		<h2><?php esc_html_e( 'Choose a new password', 'es-care-portal' ); ?></h2>
		<?php if ( empty( $valid ) ) : ?>
			<p class="esc-notice esc-notice--error"><?php esc_html_e( 'This reset link is invalid or has expired.', 'es-care-portal' ); ?></p>
			<p><a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'lost-password' ) ); ?>"><?php esc_html_e( 'Request a new link', 'es-care-portal' ); ?></a></p>
		<?php else : ?>
			<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'esc_reset_password', 'esc_reset_password_nonce' ); ?>
				<?php echo ESC_Portal_CSRF::field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input type="hidden" name="action" value="esc_reset_password">
				<input type="hidden" name="esc_key" value="<?php echo esc_attr( $key ); ?>">
				<input type="hidden" name="esc_login" value="<?php echo esc_attr( $login ); ?>">
				<p class="esc-field">
					<label for="esc_password"><?php esc_html_e( 'New password', 'es-care-portal' ); ?></label>
					<input type="password" id="esc_password" name="esc_password" required minlength="8" autocomplete="new-password" aria-describedby="esc-password-rules">
					<span class="esc-help" id="esc-password-rules"><?php esc_html_e( '8+ characters with uppercase, lowercase, and a number.', 'es-care-portal' ); ?></span>
				</p>
				<p class="esc-field">
					<label for="esc_password_confirm"><?php esc_html_e( 'Confirm password', 'es-care-portal' ); ?></label>
					<input type="password" id="esc_password_confirm" name="esc_password_confirm" required minlength="8" autocomplete="new-password">
				</p>
				<p><button type="submit" class="esc-button"><?php esc_html_e( 'Update password', 'es-care-portal' ); ?></button></p>
			</form>
		<?php endif; ?>
	</div>
</div>
