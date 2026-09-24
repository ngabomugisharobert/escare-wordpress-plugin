<?php
/**
 * Application detail.
 *
 * @package ESC_Portal
 *
 * @var int   $application_id Application ID.
 * @var array $snap           Snapshot.
 */

defined( 'ABSPATH' ) || exit;

$job_id     = $snap['job_id'];
$certs      = ESC_Portal_Helpers::format_choices( $snap['certifications'], ESC_Portal_Helpers::certifications() );
$avail      = ESC_Portal_Helpers::format_choices( $snap['availability'], ESC_Portal_Helpers::availability_options() );
$auth_opts  = ESC_Portal_Helpers::work_auth_options();
$auth_label = isset( $auth_opts[ $snap['work_authorization'] ] ) ? $auth_opts[ $snap['work_authorization'] ] : $snap['work_authorization'];
$states     = ESC_Portal_Helpers::us_states();
$state_label = isset( $states[ $snap['state'] ] ) ? $states[ $snap['state'] ] : $snap['state'];
$estate_label = isset( $states[ $snap['emergency_state'] ] ) ? $states[ $snap['emergency_state'] ] : $snap['emergency_state'];
?>
<div class="wrap esc-admin esc-admin-detail">
	<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=esc-applications' ) ); ?>">&larr; <?php esc_html_e( 'All applications', 'es-care-portal' ); ?></a></p>
	<h1><?php echo esc_html( trim( $snap['first_name'] . ' ' . $snap['last_name'] ) ); ?></h1>
	<p class="esc-admin-lede">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: job title */
				__( 'Applied for %s', 'es-care-portal' ),
				get_the_title( $job_id )
			)
		);
		?>
	</p>

	<div class="esc-admin-grid">
		<div class="esc-admin-panel">
			<h2><?php esc_html_e( 'Applicant information', 'es-care-portal' ); ?></h2>
			<dl class="esc-dl">
				<dt><?php esc_html_e( 'Email', 'es-care-portal' ); ?></dt>
				<dd><a href="mailto:<?php echo esc_attr( $snap['email'] ); ?>"><?php echo esc_html( $snap['email'] ); ?></a></dd>
				<dt><?php esc_html_e( 'Mobile phone', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['phone'] ); ?></dd>
				<dt><?php esc_html_e( 'Daytime / Evening', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( trim( $snap['daytime_phone'] . ' / ' . $snap['evening_phone'], ' /' ) ); ?></dd>
				<dt><?php esc_html_e( 'Home address', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['home_address'] ); ?></dd>
				<dt><?php esc_html_e( 'City / State / Zip', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( trim( $snap['city'] . ', ' . $state_label . ' ' . $snap['zip'], ', ' ) ); ?></dd>
				<dt><?php esc_html_e( 'Years at address', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['years_at_address'] ? $snap['years_at_address'] : '—' ); ?></dd>
				<dt><?php esc_html_e( 'Professional license', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['professional_license'] ? $snap['professional_license'] : '—' ); ?></dd>
				<dt><?php esc_html_e( 'Date of birth', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['date_of_birth'] ? $snap['date_of_birth'] : '—' ); ?></dd>
				<dt><?php esc_html_e( '18 or older', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( 'yes' === $snap['over_18'] ? __( 'Yes', 'es-care-portal' ) : ( 'no' === $snap['over_18'] ? __( 'No', 'es-care-portal' ) : '—' ) ); ?></dd>
				<dt><?php esc_html_e( 'Salary desired', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['salary_desired'] ? $snap['salary_desired'] : '—' ); ?></dd>
				<dt><?php esc_html_e( 'Available to start', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['available_to_start'] ? $snap['available_to_start'] : '—' ); ?></dd>
				<dt><?php esc_html_e( 'Years of experience', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['years_experience'] ); ?></dd>
				<dt><?php esc_html_e( 'Certifications', 'es-care-portal' ); ?></dt>
				<dd>
					<?php echo esc_html( $certs ); ?>
					<?php if ( $snap['certifications_other'] ) : ?>
						<br><?php echo esc_html( $snap['certifications_other'] ); ?>
					<?php endif; ?>
				</dd>
				<dt><?php esc_html_e( 'Availability', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $avail ); ?></dd>
				<dt><?php esc_html_e( 'Work authorization', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $auth_label ); ?></dd>
			</dl>

			<h2><?php esc_html_e( 'Emergency contact', 'es-care-portal' ); ?></h2>
			<dl class="esc-dl">
				<dt><?php esc_html_e( 'Name', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['emergency_name'] ? $snap['emergency_name'] : '—' ); ?></dd>
				<dt><?php esc_html_e( 'Phone', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['emergency_phone'] ? $snap['emergency_phone'] : '—' ); ?></dd>
				<dt><?php esc_html_e( 'Relationship', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( $snap['emergency_relationship'] ? $snap['emergency_relationship'] : '—' ); ?></dd>
				<dt><?php esc_html_e( 'Address', 'es-care-portal' ); ?></dt>
				<dd><?php echo esc_html( trim( $snap['emergency_address'] . ', ' . $snap['emergency_city'] . ' ' . $estate_label, ', ' ) ); ?></dd>
			</dl>

			<h2><?php esc_html_e( 'Cover letter', 'es-care-portal' ); ?></h2>
			<div class="esc-cover">
				<?php echo $snap['cover_letter'] ? wpautop( esc_html( $snap['cover_letter'] ) ) : '<p>' . esc_html__( 'None provided.', 'es-care-portal' ) . '</p>'; ?>
			</div>

			<h2><?php esc_html_e( 'Documents', 'es-care-portal' ); ?></h2>
			<ul class="esc-doc-list">
				<?php foreach ( ESC_Portal_Uploads::document_types() as $doc_key => $doc ) : ?>
					<?php
					$file_meta = (string) get_post_meta( $application_id, $doc['meta_file'], true );
					$name_meta = (string) get_post_meta( $application_id, $doc['meta_name'], true );
					?>
					<li>
						<strong><?php echo esc_html( $doc['label'] ); ?>:</strong>
						<?php if ( $file_meta ) : ?>
							<a href="<?php echo esc_url( ESC_Portal_Uploads::download_url( $application_id, $doc_key ) ); ?>">
								<?php echo esc_html( $name_meta ? $name_meta : __( 'Download', 'es-care-portal' ) ); ?>
							</a>
						<?php else : ?>
							<span class="esc-muted"><?php esc_html_e( 'Not on file', 'es-care-portal' ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="esc-admin-panel">
			<h2><?php esc_html_e( 'Review', 'es-care-portal' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'esc_save_application_' . $application_id, 'esc_application_nonce' ); ?>
				<input type="hidden" name="action" value="esc_save_application">
				<input type="hidden" name="esc_application_id" value="<?php echo esc_attr( (string) $application_id ); ?>">
				<p>
					<label for="esc_status"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></label><br>
					<select name="esc_status" id="esc_status" class="widefat">
						<?php foreach ( ESC_Portal_Helpers::application_statuses() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $snap['status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="esc_notes"><?php esc_html_e( 'Internal notes', 'es-care-portal' ); ?></label><br>
					<textarea name="esc_notes" id="esc_notes" class="widefat" rows="10"><?php echo esc_textarea( $snap['notes'] ); ?></textarea>
				</p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save review', 'es-care-portal' ); ?></button></p>
				<p class="description"><?php esc_html_e( 'Changing status emails the applicant, except when set back to Pending.', 'es-care-portal' ); ?></p>
			</form>
		</div>
	</div>
</div>
