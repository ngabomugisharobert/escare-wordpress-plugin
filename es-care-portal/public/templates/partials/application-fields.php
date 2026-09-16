<?php
/**
 * Candidate application form fields.
 *
 * @package ESC_Portal
 *
 * @var array  $profile
 * @var object $user
 * @var WP_Post[]|null $jobs Optional open jobs for selector.
 * @var int    $job_id Selected job ID.
 * @var array  $settings
 * @var bool   $show_job_select
 */

defined( 'ABSPATH' ) || exit;

$profile         = isset( $profile ) && is_array( $profile ) ? $profile : array();
$settings        = isset( $settings ) && is_array( $settings ) ? $settings : ESC_Portal_Helpers::public_settings();
$job_id          = isset( $job_id ) ? absint( $job_id ) : 0;
$show_job_select = ! empty( $show_job_select );
$jobs            = isset( $jobs ) && is_array( $jobs ) ? $jobs : array();
$user            = isset( $user ) ? $user : ESC_Portal_Auth::current_user();
$full_name       = trim( ( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ) . ' ' . ( isset( $profile['last_name'] ) ? $profile['last_name'] : '' ) );
$email           = isset( $profile['email'] ) ? $profile['email'] : ( $user ? $user->email : '' );
$states          = ESC_Portal_Helpers::us_states();
?>
<p class="esc-required-note"><?php esc_html_e( '* = Required Information', 'es-care-portal' ); ?></p>

<?php if ( $show_job_select ) : ?>
	<div class="esc-app-section">
		<div class="esc-app-section-head"><?php esc_html_e( 'Position', 'es-care-portal' ); ?></div>
		<div class="esc-app-section-body">
			<p class="esc-field">
				<label for="esc_job_id"><?php esc_html_e( 'Select a job', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<select id="esc_job_id" name="esc_job_id" required>
					<option value=""><?php esc_html_e( 'Choose an open position', 'es-care-portal' ); ?></option>
					<?php foreach ( $jobs as $job ) : ?>
						<option value="<?php echo esc_attr( (string) $job->ID ); ?>" <?php selected( $job_id, $job->ID ); ?>><?php echo esc_html( get_the_title( $job ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
	</div>
<?php else : ?>
	<input type="hidden" name="esc_job_id" value="<?php echo esc_attr( (string) $job_id ); ?>">
<?php endif; ?>

<div class="esc-app-section">
	<div class="esc-app-section-head"><?php esc_html_e( 'Applicant Information', 'es-care-portal' ); ?></div>
	<div class="esc-app-section-body">
		<div class="esc-grid">
			<p class="esc-field">
				<label for="esc_full_name"><?php esc_html_e( 'Applicant Full Name', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="text" id="esc_full_name" name="esc_full_name" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( $full_name ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_email_display"><?php esc_html_e( 'Your E-mail Address', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="email" id="esc_email_display" value="<?php echo esc_attr( $email ); ?>" disabled>
			</p>
		</div>
		<p class="esc-field">
			<label for="esc_home_address"><?php esc_html_e( 'Home Address', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
			<input type="text" id="esc_home_address" name="esc_home_address" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['home_address'] ) ? $profile['home_address'] : '' ); ?>">
		</p>
		<div class="esc-grid esc-grid--3">
			<p class="esc-field">
				<label for="esc_city"><?php esc_html_e( 'City', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="text" id="esc_city" name="esc_city" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['city'] ) ? $profile['city'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_state"><?php esc_html_e( 'State', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<select id="esc_state" name="esc_state" required>
					<option value=""><?php esc_html_e( 'Select State', 'es-care-portal' ); ?></option>
					<?php foreach ( $states as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( isset( $profile['state'] ) ? $profile['state'] : '', $code ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="esc-field">
				<label for="esc_zip"><?php esc_html_e( 'Zip', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="text" id="esc_zip" name="esc_zip" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['zip'] ) ? $profile['zip'] : '' ); ?>">
			</p>
		</div>
		<div class="esc-grid">
			<p class="esc-field">
				<label for="esc_years_at_address"><?php esc_html_e( 'Number of years at this address', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_years_at_address" name="esc_years_at_address" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['years_at_address'] ) ? $profile['years_at_address'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_daytime_phone"><?php esc_html_e( 'Daytime phone', 'es-care-portal' ); ?></label>
				<input type="tel" id="esc_daytime_phone" name="esc_daytime_phone" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['daytime_phone'] ) ? $profile['daytime_phone'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_evening_phone"><?php esc_html_e( 'Evening Phone', 'es-care-portal' ); ?></label>
				<input type="tel" id="esc_evening_phone" name="esc_evening_phone" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['evening_phone'] ) ? $profile['evening_phone'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_phone"><?php esc_html_e( 'Mobile Phone', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="tel" id="esc_phone" name="esc_phone" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['phone'] ) ? $profile['phone'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_ssn"><?php esc_html_e( 'Social Security Number', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_ssn" name="esc_ssn" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" autocomplete="off" value="">
			</p>
			<p class="esc-field">
				<label for="esc_drivers_license"><?php esc_html_e( "Driver's License / ID Number", 'es-care-portal' ); ?></label>
				<input type="text" id="esc_drivers_license" name="esc_drivers_license" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['drivers_license'] ) ? $profile['drivers_license'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_professional_license"><?php esc_html_e( 'Professional License Number', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_professional_license" name="esc_professional_license" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['professional_license'] ) ? $profile['professional_license'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_date_of_birth"><?php esc_html_e( 'Date of Birth', 'es-care-portal' ); ?></label>
				<input type="date" id="esc_date_of_birth" name="esc_date_of_birth" value="<?php echo esc_attr( isset( $profile['date_of_birth'] ) ? $profile['date_of_birth'] : '' ); ?>">
			</p>
		</div>
	</div>
</div>

<div class="esc-app-section">
	<div class="esc-app-section-head"><?php esc_html_e( 'Emergency Contact', 'es-care-portal' ); ?></div>
	<div class="esc-app-section-body">
		<p class="esc-help"><?php esc_html_e( 'Who should be contacted if you are involved in an emergency?', 'es-care-portal' ); ?></p>
		<div class="esc-grid">
			<p class="esc-field">
				<label for="esc_emergency_name"><?php esc_html_e( 'Contact Name', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="text" id="esc_emergency_name" name="esc_emergency_name" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['emergency_name'] ) ? $profile['emergency_name'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_emergency_phone"><?php esc_html_e( 'Contact Number', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="tel" id="esc_emergency_phone" name="esc_emergency_phone" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['emergency_phone'] ) ? $profile['emergency_phone'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_emergency_relationship"><?php esc_html_e( 'Relationship to you', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_emergency_relationship" name="esc_emergency_relationship" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['emergency_relationship'] ) ? $profile['emergency_relationship'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_emergency_address"><?php esc_html_e( 'Address', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="text" id="esc_emergency_address" name="esc_emergency_address" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['emergency_address'] ) ? $profile['emergency_address'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_emergency_city"><?php esc_html_e( 'City', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="text" id="esc_emergency_city" name="esc_emergency_city" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['emergency_city'] ) ? $profile['emergency_city'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_emergency_state"><?php esc_html_e( 'State', 'es-care-portal' ); ?></label>
				<select id="esc_emergency_state" name="esc_emergency_state">
					<option value=""><?php esc_html_e( 'Select State', 'es-care-portal' ); ?></option>
					<?php foreach ( $states as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( isset( $profile['emergency_state'] ) ? $profile['emergency_state'] : '', $code ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
	</div>
</div>

<div class="esc-app-section">
	<div class="esc-app-section-head"><?php esc_html_e( 'Employment Details', 'es-care-portal' ); ?></div>
	<div class="esc-app-section-body">
		<div class="esc-grid">
			<p class="esc-field">
				<label for="esc_salary_desired"><?php esc_html_e( 'Salary desired', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_salary_desired" name="esc_salary_desired" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['salary_desired'] ) ? $profile['salary_desired'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_available_to_start"><?php esc_html_e( 'When can you start?', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="text" id="esc_available_to_start" name="esc_available_to_start" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['available_to_start'] ) ? $profile['available_to_start'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_years_experience"><?php esc_html_e( 'Years of experience', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<input type="number" min="0" max="60" id="esc_years_experience" name="esc_years_experience" required placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['years_experience'] ) ? $profile['years_experience'] : '' ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_over_18"><?php esc_html_e( 'Are you 18 years of age or older?', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
				<select id="esc_over_18" name="esc_over_18" required>
					<option value=""><?php esc_html_e( 'Select one', 'es-care-portal' ); ?></option>
					<option value="yes" <?php selected( isset( $profile['over_18'] ) ? $profile['over_18'] : '', 'yes' ); ?>><?php esc_html_e( 'Yes', 'es-care-portal' ); ?></option>
					<option value="no" <?php selected( isset( $profile['over_18'] ) ? $profile['over_18'] : '', 'no' ); ?>><?php esc_html_e( 'No', 'es-care-portal' ); ?></option>
				</select>
			</p>
		</div>
		<p class="esc-field">
			<span class="esc-label"><?php esc_html_e( 'Certifications', 'es-care-portal' ); ?> <span class="esc-req">*</span></span>
			<?php
			$certs = isset( $profile['certifications'] ) ? (array) $profile['certifications'] : array();
			foreach ( ESC_Portal_Helpers::certifications() as $key => $label ) :
				?>
				<label class="esc-check">
					<input type="checkbox" name="esc_certifications[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $certs, true ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</p>
		<p class="esc-field">
			<label for="esc_certifications_other"><?php esc_html_e( 'Other certifications', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_certifications_other" name="esc_certifications_other" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>" value="<?php echo esc_attr( isset( $profile['certifications_other'] ) ? $profile['certifications_other'] : '' ); ?>">
		</p>
		<p class="esc-field">
			<span class="esc-label"><?php esc_html_e( 'Availability', 'es-care-portal' ); ?></span>
			<?php
			$avail = isset( $profile['availability'] ) ? (array) $profile['availability'] : array();
			foreach ( ESC_Portal_Helpers::availability_options() as $key => $label ) :
				?>
				<label class="esc-check">
					<input type="checkbox" name="esc_availability[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $avail, true ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</p>
		<p class="esc-field">
			<label for="esc_work_authorization"><?php esc_html_e( 'Work authorization', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
			<select id="esc_work_authorization" name="esc_work_authorization" required>
				<option value=""><?php esc_html_e( 'Select one', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Helpers::work_auth_options() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $profile['work_authorization'] ) ? $profile['work_authorization'] : '', $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="esc-field">
			<label for="esc_cover_letter"><?php esc_html_e( 'Cover letter / additional information', 'es-care-portal' ); ?></label>
			<textarea id="esc_cover_letter" name="esc_cover_letter" rows="5" placeholder="<?php esc_attr_e( 'Enter here', 'es-care-portal' ); ?>"><?php echo esc_textarea( isset( $profile['cover_letter'] ) ? $profile['cover_letter'] : '' ); ?></textarea>
		</p>
		<p class="esc-field">
			<label for="esc_resume"><?php esc_html_e( 'Resume / CV', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
			<input type="file" id="esc_resume" name="esc_resume" required accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" data-esc-max="<?php echo esc_attr( (string) ( absint( $settings['max_file_mb'] ) * 1024 * 1024 ) ); ?>">
			<span class="esc-help"><?php echo esc_html( sprintf( __( 'PDF, DOC, or DOCX. Maximum %d MB.', 'es-care-portal' ), absint( $settings['max_file_mb'] ) ) ); ?></span>
		</p>
	</div>
</div>
