<?php
/**
 * Login form.
 *
 * @package ESC_Portal
 *
 * @var string               $redirect_to Redirect URL.
 * @var array<string,string> $sticky      Previous submission, if any.
 */

defined( 'ABSPATH' ) || exit;

$sticky = isset( $sticky ) && is_array( $sticky ) ? $sticky : array();
$email  = isset( $sticky['email'] ) ? $sticky['email'] : '';
?>
<div class="esc-portal-wrap esc-portal-wrap--auth">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<div class="esc-card esc-card--narrow">
		<p class="esc-kicker"><?php esc_html_e( 'Careers portal', 'es-care-portal' ); ?></p>
		<h2><?php esc_html_e( 'Sign in', 'es-care-portal' ); ?></h2>
		<p><?php esc_html_e( 'Use your job seeker, employer, or portal admin account.', 'es-care-portal' ); ?></p>
		<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'esc_login', 'esc_login_nonce' ); ?>
			<?php echo ESC_Portal_CSRF::field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="action" value="esc_login">
			<?php if ( ! empty( $redirect_to ) ) : ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
			<?php endif; ?>
			<p class="esc-field">
				<label for="esc_email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label>
				<input type="email" id="esc_email" name="esc_email" value="<?php echo esc_attr( $email ); ?>" required autocomplete="username">
			</p>
			<p class="esc-field">
				<label for="esc_password"><?php esc_html_e( 'Password', 'es-care-portal' ); ?></label>
				<input type="password" id="esc_password" name="esc_password" required autocomplete="current-password">
			</p>
			<p class="esc-field esc-check">
				<label>
					<input type="checkbox" name="esc_remember" value="1">
					<?php esc_html_e( 'Keep me signed in', 'es-care-portal' ); ?>
				</label>
			</p>
			<p><button type="submit" class="esc-button"><?php esc_html_e( 'Sign in', 'es-care-portal' ); ?></button></p>
		</form>
		<p class="esc-foot">
			<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'lost-password' ) ); ?>"><?php esc_html_e( 'Forgot password?', 'es-care-portal' ); ?></a>
			&nbsp;·&nbsp;
			<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'register' ) ); ?>"><?php esc_html_e( 'Create an account', 'es-care-portal' ); ?></a>
		</p>
		<form class="esc-form esc-form--compact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'esc_resend_verification_public', 'esc_resend_public_nonce' ); ?>
			<?php echo ESC_Portal_CSRF::field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="action" value="esc_resend_verification_public">
			<p class="esc-field">
				<label for="esc_resend_email"><?php esc_html_e( "Didn't get an activation email?", 'es-care-portal' ); ?></label>
				<input type="email" id="esc_resend_email" name="esc_email" value="<?php echo esc_attr( $email ); ?>" required autocomplete="email">
			</p>
			<p><button type="submit" class="esc-button esc-button--ghost"><?php esc_html_e( 'Resend activation email', 'es-care-portal' ); ?></button></p>
		</form>
	</div>
</div>
