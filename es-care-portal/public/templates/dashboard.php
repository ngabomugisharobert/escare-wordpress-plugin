<?php
/**
 * Applicant dashboard.
 *
 * @package ESC_Portal
 *
 * @var WP_User   $user             Current user.
 * @var bool      $profile_complete Profile flag.
 * @var WP_Post[] $applications     Applications.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="esc-portal-wrap">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<header class="esc-hero">
		<p class="esc-kicker"><?php esc_html_e( 'Applicant dashboard', 'es-care-portal' ); ?></p>
		<h2><?php echo esc_html( sprintf( __( 'Hello, %s', 'es-care-portal' ), $user->first_name ? $user->first_name : $user->display_name ) ); ?></h2>
		<p><?php esc_html_e( 'Track applications and keep your care profile current.', 'es-care-portal' ); ?></p>
	</header>

	<?php if ( ! $profile_complete ) : ?>
		<div class="esc-notice esc-notice--info">
			<?php esc_html_e( 'Finish your profile so applications pre-fill faster.', 'es-care-portal' ); ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'profile' ) ); ?>"><?php esc_html_e( 'Update profile', 'es-care-portal' ); ?></a>
		</div>
	<?php endif; ?>

	<div class="esc-actions">
		<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'careers' ) ); ?>"><?php esc_html_e( 'Browse open jobs', 'es-care-portal' ); ?></a>
	</div>

	<section class="esc-card">
		<h3><?php esc_html_e( 'Your applications', 'es-care-portal' ); ?></h3>
		<?php if ( empty( $applications ) ) : ?>
			<p><?php esc_html_e( 'You have not applied for any positions yet.', 'es-care-portal' ); ?></p>
		<?php else : ?>
			<div class="esc-table-wrap">
				<table class="esc-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Position', 'es-care-portal' ); ?></th>
							<th><?php esc_html_e( 'Submitted', 'es-care-portal' ); ?></th>
							<th><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $applications as $application ) : ?>
							<?php
							$job_id = (int) get_post_meta( $application->ID, '_esc_job_id', true );
							$status = (string) get_post_meta( $application->ID, '_esc_status', true );
							?>
							<tr>
								<td>
									<?php if ( $job_id ) : ?>
										<a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>"><?php echo esc_html( get_the_title( $job_id ) ); ?></a>
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
</div>
