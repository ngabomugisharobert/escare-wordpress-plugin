<?php
/**
 * Shared care-experience fields.
 *
 * @package ESC_Portal
 *
 * @var array $profile Profile values.
 * @var bool  $require_care Whether care fields are required.
 */

defined( 'ABSPATH' ) || exit;

$profile      = isset( $profile ) && is_array( $profile ) ? $profile : array();
$require_care = ! empty( $require_care );
$certs        = isset( $profile['certifications'] ) ? (array) $profile['certifications'] : array();
$avail        = isset( $profile['availability'] ) ? (array) $profile['availability'] : array();
?>
<fieldset class="esc-fieldset">
	<legend><?php esc_html_e( 'Contact', 'es-care-portal' ); ?></legend>
	<div class="esc-grid">
		<p class="esc-field">
			<label for="esc_first_name"><?php esc_html_e( 'First name', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_first_name" name="esc_first_name" required value="<?php echo esc_attr( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_last_name"><?php esc_html_e( 'Last name', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_last_name" name="esc_last_name" required value="<?php echo esc_attr( isset( $profile['last_name'] ) ? $profile['last_name'] : '' ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_phone"><?php esc_html_e( 'Phone', 'es-care-portal' ); ?></label>
			<input type="tel" id="esc_phone" name="esc_phone" required value="<?php echo esc_attr( isset( $profile['phone'] ) ? $profile['phone'] : '' ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_city"><?php esc_html_e( 'City', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_city" name="esc_city" <?php echo $require_care ? 'required' : ''; ?> value="<?php echo esc_attr( isset( $profile['city'] ) ? $profile['city'] : '' ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_state"><?php esc_html_e( 'State', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_state" name="esc_state" <?php echo $require_care ? 'required' : ''; ?> value="<?php echo esc_attr( isset( $profile['state'] ) ? $profile['state'] : '' ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_zip"><?php esc_html_e( 'ZIP', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_zip" name="esc_zip" value="<?php echo esc_attr( isset( $profile['zip'] ) ? $profile['zip'] : '' ); ?>">
		</p>
	</div>
</fieldset>

<fieldset class="esc-fieldset">
	<legend><?php esc_html_e( 'Care experience', 'es-care-portal' ); ?></legend>
	<p class="esc-field">
		<span class="esc-label"><?php esc_html_e( 'Certifications', 'es-care-portal' ); ?></span>
		<?php foreach ( ESC_Portal_Helpers::certifications() as $key => $label ) : ?>
			<label class="esc-check">
				<input type="checkbox" name="esc_certifications[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $certs, true ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
	</p>
	<p class="esc-field">
		<label for="esc_certifications_other"><?php esc_html_e( 'Other certifications', 'es-care-portal' ); ?></label>
		<input type="text" id="esc_certifications_other" name="esc_certifications_other" value="<?php echo esc_attr( isset( $profile['certifications_other'] ) ? $profile['certifications_other'] : '' ); ?>">
	</p>
	<p class="esc-field">
		<label for="esc_years_experience"><?php esc_html_e( 'Years of experience', 'es-care-portal' ); ?></label>
		<input type="number" min="0" max="60" id="esc_years_experience" name="esc_years_experience" value="<?php echo esc_attr( isset( $profile['years_experience'] ) ? $profile['years_experience'] : '' ); ?>">
	</p>
	<p class="esc-field">
		<span class="esc-label"><?php esc_html_e( 'Availability', 'es-care-portal' ); ?></span>
		<?php foreach ( ESC_Portal_Helpers::availability_options() as $key => $label ) : ?>
			<label class="esc-check">
				<input type="checkbox" name="esc_availability[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $avail, true ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
	</p>
	<p class="esc-field">
		<label for="esc_work_authorization"><?php esc_html_e( 'Work authorization', 'es-care-portal' ); ?></label>
		<select id="esc_work_authorization" name="esc_work_authorization" <?php echo $require_care ? 'required' : ''; ?>>
			<option value=""><?php esc_html_e( 'Select one', 'es-care-portal' ); ?></option>
			<?php foreach ( ESC_Portal_Helpers::work_auth_options() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $profile['work_authorization'] ) ? $profile['work_authorization'] : '', $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
</fieldset>
