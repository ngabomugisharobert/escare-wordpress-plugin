<?php
/**
 * Job application / resume view with full candidate form.
 *
 * @package ESC_Portal
 *
 * @var WP_Post[] $applications
 * @var WP_Post[] $jobs
 * @var array     $profile
 * @var array     $settings
 * @var object    $user
 */

defined( 'ABSPATH' ) || exit;

$selected_job = isset( $_GET['job'] ) ? absint( $_GET['job'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title esc-app-title"><?php esc_html_e( 'Job Application Form', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Complete the candidate registration below to apply for an open position. Fields marked with * are required.', 'es-care-portal' ); ?></p>

	<?php if ( empty( $jobs ) ) : ?>
		<p class="esc-notice esc-notice--info"><?php esc_html_e( 'There are no open positions right now. Please check back soon.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<form class="esc-form esc-app-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'esc_apply', 'esc_apply_nonce' ); ?>
			<input type="hidden" name="action" value="esc_apply">
			<?php
			$job_id          = $selected_job;
			$show_job_select = true;
			include ESC_PORTAL_DIR . 'public/templates/partials/application-fields.php';
			?>
			<p class="esc-actions"><button type="submit" class="esc-button"><?php esc_html_e( 'Submit application', 'es-care-portal' ); ?></button></p>
		</form>
	<?php endif; ?>

	<section class="esc-card">
		<h3><?php esc_html_e( 'Your applications', 'es-care-portal' ); ?></h3>
		<?php if ( empty( $applications ) ) : ?>
			<p><?php esc_html_e( 'You have not applied for any positions yet.', 'es-care-portal' ); ?></p>
		<?php else : ?>
			<div class="esc-table-wrap">
				<table class="esc-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Position', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Submitted', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $applications as $application ) : ?>
							<?php
							$app_job_id = (int) get_post_meta( $application->ID, '_esc_job_id', true );
							$status     = (string) get_post_meta( $application->ID, '_esc_status', true );
							?>
							<tr>
								<td>
									<?php if ( $app_job_id ) : ?>
										<a href="<?php echo esc_url( get_permalink( $app_job_id ) ); ?>"><?php echo esc_html( get_the_title( $app_job_id ) ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $application->post_title ); ?>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( get_the_date( get_option( 'date_format' ), $application ) ); ?></td>
								<td><span class="esc-status esc-status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ESC_Portal_Helpers::format_status( $status ) ); ?></span></td>
								<td>
									<?php if ( 'pending' === $status ) : ?>
										<form class="esc-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-esc-confirm="<?php echo esc_attr__( 'Withdraw this application?', 'es-care-portal' ); ?>">
											<?php wp_nonce_field( 'esc_withdraw', 'esc_withdraw_nonce' ); ?>
											<input type="hidden" name="action" value="esc_withdraw">
											<input type="hidden" name="esc_application_id" value="<?php echo esc_attr( (string) $application->ID ); ?>">
											<button type="submit" class="esc-text-button"><?php esc_html_e( 'Withdraw', 'es-care-portal' ); ?></button>
										</form>
									<?php else : ?>
										&mdash;
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>
</section>
