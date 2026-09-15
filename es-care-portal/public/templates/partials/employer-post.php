<?php
/**
 * Employer post / edit job inside dashboard.
 *
 * @package ESC_Portal
 *
 * @var WP_Post|null $edit_job
 */

defined( 'ABSPATH' ) || exit;

$job      = isset( $edit_job ) ? $edit_job : null;
$job_id   = $job ? (int) $job->ID : 0;
$location = $job_id ? get_post_meta( $job_id, '_esc_location', true ) : '';
$type     = $job_id ? get_post_meta( $job_id, '_esc_employment_type', true ) : '';
$shift    = $job_id ? get_post_meta( $job_id, '_esc_shift', true ) : '';
$pay      = $job_id ? get_post_meta( $job_id, '_esc_pay_range', true ) : '';
$closing  = $job_id ? get_post_meta( $job_id, '_esc_closing_date', true ) : '';
$status   = $job_id ? get_post_meta( $job_id, '_esc_job_status', true ) : 'open';
$terms    = $job_id ? wp_get_post_terms( $job_id, 'esc_job_category' ) : array();
$cat_id   = ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) ? (int) $terms[0]->term_id : 0;
$cats     = get_terms( array( 'taxonomy' => 'esc_job_category', 'hide_empty' => false ) );
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php echo $job_id ? esc_html__( 'Edit Job', 'es-care-portal' ) : esc_html__( 'Post a Job', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Add a listing for candidates to find. You can close or update it anytime from Company Jobs.', 'es-care-portal' ); ?></p>

	<form class="esc-form esc-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_save_job_front', 'esc_job_front_nonce' ); ?>
		<input type="hidden" name="action" value="esc_save_job_front">
		<input type="hidden" name="esc_job_id" value="<?php echo esc_attr( (string) $job_id ); ?>">
		<input type="hidden" name="esc_redirect_view" value="jobs">
		<p class="esc-field">
			<label for="esc_job_title"><?php esc_html_e( 'Job title', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_job_title" name="esc_job_title" required value="<?php echo $job ? esc_attr( $job->post_title ) : ''; ?>">
		</p>
		<p class="esc-field">
			<label for="esc_job_content"><?php esc_html_e( 'Description', 'es-care-portal' ); ?></label>
			<textarea id="esc_job_content" name="esc_job_content" rows="10" required><?php echo $job ? esc_textarea( $job->post_content ) : ''; ?></textarea>
		</p>
		<div class="esc-grid">
			<p class="esc-field">
				<label for="esc_location"><?php esc_html_e( 'Location', 'es-care-portal' ); ?></label>
				<input type="text" id="esc_location" name="esc_location" value="<?php echo esc_attr( $location ); ?>">
			</p>
			<p class="esc-field">
				<label for="esc_employment_type"><?php esc_html_e( 'Employment type', 'es-care-portal' ); ?></label>
				<select id="esc_employment_type" name="esc_employment_type">
					<option value=""><?php esc_html_e( 'Select type', 'es-care-portal' ); ?></option>
					<?php foreach ( ESC_Portal_Helpers::employment_types() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<p class="esc-field">
			<label for="esc_shift"><?php esc_html_e( 'Shift', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_shift" name="esc_shift" value="<?php echo esc_attr( $shift ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_pay_range"><?php esc_html_e( 'Pay range', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_pay_range" name="esc_pay_range" value="<?php echo esc_attr( $pay ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_closing_date"><?php esc_html_e( 'Closing date', 'es-care-portal' ); ?></label>
			<input type="date" id="esc_closing_date" name="esc_closing_date" value="<?php echo esc_attr( $closing ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_job_status"><?php esc_html_e( 'Listing status', 'es-care-portal' ); ?></label>
			<select id="esc_job_status" name="esc_job_status">
				<?php foreach ( ESC_Portal_Helpers::job_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status ? $status : 'open', $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php if ( ! is_wp_error( $cats ) && $cats ) : ?>
			<p class="esc-field">
				<label for="esc_job_category"><?php esc_html_e( 'Category', 'es-care-portal' ); ?></label>
				<select id="esc_job_category" name="esc_job_category">
					<option value="0"><?php esc_html_e( 'None', 'es-care-portal' ); ?></option>
					<?php foreach ( $cats as $term ) : ?>
						<option value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php selected( $cat_id, $term->term_id ); ?>><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		<?php endif; ?>
		<p class="esc-actions">
			<button type="submit" class="esc-button"><?php esc_html_e( 'Save job', 'es-care-portal' ); ?></button>
			<a class="esc-button esc-button--ghost" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'jobs' ) ); ?>"><?php esc_html_e( 'Back to jobs', 'es-care-portal' ); ?></a>
		</p>
	</form>
</section>
