<?php
/**
 * Change password and delete account.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

$deletion_pending = ESC_Portal_Users::deletion_requested( ESC_Portal_Auth::current_user_id() );
?>
<section class="esc-dash-panel esc-dash-panel--centered">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Change Password', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Use 8+ characters with uppercase, lowercase, and a number. Other sessions will be signed out after the update.', 'es-care-portal' ); ?></p>

	<form class="esc-form esc-card esc-card--narrow" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_change_password', 'esc_password_nonce' ); ?>
		<input type="hidden" name="action" value="esc_change_password">
		<p class="esc-field">
			<label for="esc_current_password"><?php esc_html_e( 'Current password', 'es-care-portal' ); ?></label>
			<input type="password" id="esc_current_password" name="esc_current_password" required autocomplete="current-password">
		</p>
		<p class="esc-field">
			<label for="esc_password"><?php esc_html_e( 'New password', 'es-care-portal' ); ?></label>
			<input type="password" id="esc_password" name="esc_password" required minlength="8" autocomplete="new-password">
		</p>
		<p class="esc-field">
			<label for="esc_password_confirm"><?php esc_html_e( 'Confirm new password', 'es-care-portal' ); ?></label>
			<input type="password" id="esc_password_confirm" name="esc_password_confirm" required minlength="8" autocomplete="new-password">
		</p>
		<button type="submit" class="esc-button"><?php esc_html_e( 'Update password', 'es-care-portal' ); ?></button>
	</form>

	<section class="esc-card esc-card--narrow" id="esc-delete-account">
		<h3><?php esc_html_e( 'Delete Account', 'es-care-portal' ); ?></h3>
		<?php if ( ! empty( $deletion_pending ) ) : ?>
			<p><?php esc_html_e( 'A deletion request for this account is already waiting for administrator review. You can keep using the portal until staff process it.', 'es-care-portal' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'This sends a deletion request to ES Care staff. They will review it and remove the account. Submitted applications are anonymized when the account is deleted.', 'es-care-portal' ); ?></p>
			<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-esc-confirm="<?php echo esc_attr__( 'Send an account deletion request to ES Care staff?', 'es-care-portal' ); ?>">
				<?php wp_nonce_field( 'esc_delete_account', 'esc_delete_nonce' ); ?>
				<input type="hidden" name="action" value="esc_delete_account">
				<p class="esc-field">
					<label for="esc_delete_confirm"><?php esc_html_e( 'Type DELETE to confirm', 'es-care-portal' ); ?></label>
					<input type="text" id="esc_delete_confirm" name="esc_delete_confirm" required>
				</p>
				<button type="submit" class="esc-button esc-button--danger"><?php esc_html_e( 'Request account deletion', 'es-care-portal' ); ?></button>
			</form>
		<?php endif; ?>
	</section>
</section>
