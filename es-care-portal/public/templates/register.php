<?php
/**
 * Registration form.
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
		<p class="esc-kicker"><?php esc_html_e( 'ES Care Services', 'es-care-portal' ); ?></p>
		<h2><?php esc_html_e( 'Create a portal account', 'es-care-portal' ); ?></h2>
		<p><?php esc_html_e( 'Job seekers and employers register here. This is not the WordPress site login.', 'es-care-portal' ); ?></p>
		<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="esc-register-form">
			<?php wp_nonce_field( 'esc_register', 'esc_register_nonce' ); ?>
			<input type="hidden" name="action" value="esc_register">
			<?php if ( ! empty( $redirect_to ) ) : ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
			<?php endif; ?>
			<fieldset class="esc-fieldset">
				<legend><?php esc_html_e( 'I am a', 'es-care-portal' ); ?></legend>
				<label class="esc-check">
					<input type="radio" name="esc_role" value="job_seeker" checked>
					<?php esc_html_e( 'Job Seeker', 'es-care-portal' ); ?>
				</label>
				<label class="esc-check">
					<input type="radio" name="esc_role" value="employer">
					<?php esc_html_e( 'Employer', 'es-care-portal' ); ?>
				</label>
			</fieldset>
			<div class="esc-grid">
				<p class="esc-field">
					<label for="esc_first_name"><?php esc_html_e( 'First name', 'es-care-portal' ); ?></label>
					<input type="text" id="esc_first_name" name="esc_first_name" required autocomplete="given-name">
				</p>
				<p class="esc-field">
					<label for="esc_last_name"><?php esc_html_e( 'Last name', 'es-care-portal' ); ?></label>
					<input type="text" id="esc_last_name" name="esc_last_name" required autocomplete="family-name">
				</p>
			</div>
			<p class="esc-field esc-company-field" hidden>
				<label for="esc_company_name"><?php esc_html_e( 'Company name', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_company_name" name="esc_company_name" autocomplete="organization">
			</p>
			<p class="esc-field">
				<label for="esc_email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label>
				<input type="email" id="esc_email" name="esc_email" required autocomplete="email">
			</p>
			<p class="esc-field">
				<label for="esc_phone"><?php esc_html_e( 'Phone', 'es-care-portal' ); ?></label>
				<input type="tel" id="esc_phone" name="esc_phone" required autocomplete="tel">
			</p>
			<p class="esc-field">
				<label for="esc_password"><?php esc_html_e( 'Password', 'es-care-portal' ); ?></label>
				<input type="password" id="esc_password" name="esc_password" required minlength="8" autocomplete="new-password">
			</p>
			<p class="esc-field">
				<label for="esc_password_confirm"><?php esc_html_e( 'Confirm password', 'es-care-portal' ); ?></label>
				<input type="password" id="esc_password_confirm" name="esc_password_confirm" required minlength="8" autocomplete="new-password">
			</p>
			<p><button type="submit" class="esc-button"><?php esc_html_e( 'Create account', 'es-care-portal' ); ?></button></p>
		</form>
		<p class="esc-foot"><?php esc_html_e( 'Already have a portal account?', 'es-care-portal' ); ?> <a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'login' ) ); ?>"><?php esc_html_e( 'Sign in', 'es-care-portal' ); ?></a></p>
	</div>
</div>
