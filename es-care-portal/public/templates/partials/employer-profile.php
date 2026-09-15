<?php
/**
 * Employer company profile.
 *
 * @package ESC_Portal
 *
 * @var array  $profile
 * @var object $user
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Company Profile', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Keep your company contact details current so candidates and our team can reach you.', 'es-care-portal' ); ?></p>

	<form class="esc-form esc-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_profile', 'esc_profile_nonce' ); ?>
		<input type="hidden" name="action" value="esc_profile">
		<p class="esc-field">
			<label><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label>
			<input type="email" value="<?php echo esc_attr( $profile['email'] ); ?>" disabled>
		</p>
		<p class="esc-field">
			<label for="esc_company_name"><?php esc_html_e( 'Company name', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_company_name" name="esc_company_name" required value="<?php echo esc_attr( $profile['company_name'] ); ?>">
		</p>
		<div class="esc-grid">
			<p class="esc-field">
				<label for="esc_first_name"><?php esc_html_e( 'First name', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_first_name" name="esc_first_name" required value="<?php echo esc_attr( $profile['first_name'] ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_last_name"><?php esc_html_e( 'Last name', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_last_name" name="esc_last_name" required value="<?php echo esc_attr( $profile['last_name'] ); ?>">
			</p>
		</div>
		<p class="esc-field">
			<label for="esc_phone"><?php esc_html_e( 'Phone', 'es-care-portal' ); ?></label>
			<input type="tel" id="esc_phone" name="esc_phone" required value="<?php echo esc_attr( $profile['phone'] ); ?>">
		</p>
		<div class="esc-grid">
			<p class="esc-field">
				<label for="esc_city"><?php esc_html_e( 'City', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_city" name="esc_city" value="<?php echo esc_attr( $profile['city'] ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_state"><?php esc_html_e( 'State', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_state" name="esc_state" value="<?php echo esc_attr( $profile['state'] ); ?>">
			</p>
		</div>
		<p class="esc-field">
			<label for="esc_zip"><?php esc_html_e( 'ZIP', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_zip" name="esc_zip" value="<?php echo esc_attr( $profile['zip'] ); ?>">
		</p>
		<p><button type="submit" class="esc-button"><?php esc_html_e( 'Save profile', 'es-care-portal' ); ?></button></p>
	</form>
</section>
