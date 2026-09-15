<?php
/**
 * Profile form.
 *
 * @package ESC_Portal
 *
 * @var array  $profile
 * @var object $user
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="esc-portal-wrap">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<div class="esc-card">
		<p class="esc-kicker"><?php echo esc_html( ESC_Portal_Users::role_label( $user->role ) ); ?></p>
		<h2><?php esc_html_e( 'Keep your details current', 'es-care-portal' ); ?></h2>
		<form class="esc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'esc_profile', 'esc_profile_nonce' ); ?>
			<input type="hidden" name="action" value="esc_profile">
			<p class="esc-field">
				<label><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label>
				<input type="email" value="<?php echo esc_attr( $profile['email'] ); ?>" disabled>
			</p>
			<?php if ( ESC_Portal_Users::is_employer( $user ) ) : ?>
				<p class="esc-field">
					<label for="esc_company_name"><?php esc_html_e( 'Company name', 'es-care-portal' ); ?></label>
					<input type="text" id="esc_company_name" name="esc_company_name" required value="<?php echo esc_attr( $profile['company_name'] ); ?>">
				</p>
			<?php endif; ?>
			<?php
			$require_care = ESC_Portal_Users::is_seeker( $user );
			include ESC_PORTAL_DIR . 'public/templates/partials/care-fields.php';
			?>
			<p><button type="submit" class="esc-button"><?php esc_html_e( 'Save profile', 'es-care-portal' ); ?></button></p>
		</form>
	</div>
</div>
