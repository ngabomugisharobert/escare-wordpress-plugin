<?php
/**
 * Login form.
 *
 * @package ESC_Portal
 *
 * @var string $redirect_to Redirect URL.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="esc-portal-wrap">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<div class="esc-card esc-card--narrow">
		<p class="esc-kicker"><?php esc_html_e( 'Careers portal', 'es-care-portal' ); ?></p>
		<h2><?php esc_html_e( 'Sign in', 'es-care-portal' ); ?></h2>
		<p><?php esc_html_e( 'Use your job seeker, employer, or portal admin account. WordPress site users sign in separately via wp-login.', 'es-care-portal' ); ?></p>
		<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'esc_login', 'esc_login_nonce' ); ?>
			<input type="hidden" name="action" value="esc_login">
			<?php if ( ! empty( $redirect_to ) ) : ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
			<?php endif; ?>
			<p class="esc-field">
				<label for="esc_email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label>
				<input type="email" id="esc_email" name="esc_email" required autocomplete="username">
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
	</div>
</div>
