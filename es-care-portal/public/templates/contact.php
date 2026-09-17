<?php
/**
 * Public Contact Us form. No login required.
 *
 * @package ESC_Portal
 *
 * @var object|null $user Current portal user, if any.
 */

defined( 'ABSPATH' ) || exit;

$name  = $user && ! empty( $user->display_name ) ? $user->display_name : '';
$email = $user && ! empty( $user->email ) ? $user->email : '';
?>
<div class="esc-portal-wrap esc-portal-wrap--contact">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<div class="esc-card esc-card--register">
		<p class="esc-kicker"><?php esc_html_e( 'ES Care Services', 'es-care-portal' ); ?></p>
		<h2><?php esc_html_e( 'Contact us', 'es-care-portal' ); ?></h2>
		<p><?php esc_html_e( 'Send a message and our team will follow up. You do not need an account.', 'es-care-portal' ); ?></p>
		<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'esc_contact', 'esc_contact_nonce' ); ?>
			<?php echo ESC_Portal_CSRF::field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="action" value="esc_contact">
			<p class="esc-honeypot" aria-hidden="true">
				<label for="esc_website"><?php esc_html_e( 'Website', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_website" name="esc_website" tabindex="-1" autocomplete="off">
			</p>
			<p class="esc-field">
				<label for="esc_name"><?php esc_html_e( 'Name', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_name" name="esc_name" value="<?php echo esc_attr( $name ); ?>" required autocomplete="name">
			</p>
			<p class="esc-field">
				<label for="esc_contact_email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label>
				<input type="email" id="esc_contact_email" name="esc_email" value="<?php echo esc_attr( $email ); ?>" required autocomplete="email">
			</p>
			<p class="esc-field">
				<label for="esc_subject"><?php esc_html_e( 'Subject', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_subject" name="esc_subject" required maxlength="190">
			</p>
			<p class="esc-field">
				<label for="esc_message"><?php esc_html_e( 'Message', 'es-care-portal' ); ?></label>
				<textarea id="esc_message" name="esc_message" rows="6" required maxlength="4000"></textarea>
			</p>
			<p><button type="submit" class="esc-button"><?php esc_html_e( 'Send message', 'es-care-portal' ); ?></button></p>
		</form>
	</div>
</div>
