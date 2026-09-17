<?php
/**
 * Employer service request.
 *
 * @package ESC_Portal
 *
 * @var object[] $requests
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Service Request', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Need staffing support, a custom hiring plan, or help with your listings? Send a request and our team will follow up.', 'es-care-portal' ); ?></p>

	<form class="esc-form esc-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_service_request', 'esc_request_nonce' ); ?>
		<input type="hidden" name="action" value="esc_service_request">
		<p class="esc-field">
			<label for="esc_subject"><?php esc_html_e( 'Subject', 'es-care-portal' ); ?></label>
			<input type="text" id="esc_subject" name="esc_subject" required>
		</p>
		<p class="esc-field">
			<label for="esc_message"><?php esc_html_e( 'Message', 'es-care-portal' ); ?></label>
			<textarea id="esc_message" name="esc_message" rows="6" required></textarea>
		</p>
		<button type="submit" class="esc-button"><?php esc_html_e( 'Send request', 'es-care-portal' ); ?></button>
	</form>

	<?php if ( ! empty( $requests ) ) : ?>
		<section class="esc-card">
			<h3><?php esc_html_e( 'Your recent requests', 'es-care-portal' ); ?></h3>
			<div class="esc-table-wrap">
				<table class="esc-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Subject', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Sent', 'es-care-portal' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $requests as $request ) : ?>
							<tr>
								<td><?php echo esc_html( $request->subject ); ?></td>
								<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $request->created_at ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>
	<?php endif; ?>
</section>
