<?php
/**
 * Operational health.
 *
 * @package ESC_Portal
 *
 * @var array $health Snapshot.
 */

defined( 'ABSPATH' ) || exit;

$health = isset( $health ) && is_array( $health ) ? $health : array();
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'ES Care Portal Health', 'es-care-portal' ); ?></h1>
	<table class="widefat striped">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Plugin version', 'es-care-portal' ); ?></th>
				<td><?php echo esc_html( isset( $health['plugin_version'] ) ? $health['plugin_version'] : '' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Schema version', 'es-care-portal' ); ?></th>
				<td><?php echo esc_html( (string) ( isset( $health['schema_version'] ) ? $health['schema_version'] : 0 ) ); ?> / <?php echo esc_html( (string) ( isset( $health['target_schema'] ) ? $health['target_schema'] : 0 ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Private storage', 'es-care-portal' ); ?></th>
				<td>
					<?php echo ! empty( $health['storage_writable'] ) ? esc_html__( 'Writable', 'es-care-portal' ) : esc_html__( 'Not writable — new resume uploads are blocked', 'es-care-portal' ); ?>
					<br><code><?php echo esc_html( isset( $health['storage_path'] ) ? $health['storage_path'] : '' ); ?></code>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Direct HTTP to legacy resume folder', 'es-care-portal' ); ?></th>
				<td><?php echo ! empty( $health['http_denied'] ) ? esc_html__( 'Denied (expected)', 'es-care-portal' ) : esc_html__( 'Reachable — configure the web server to deny public access', 'es-care-portal' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Mail queue', 'es-care-portal' ); ?></th>
				<td>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: queued, 2: failed, 3: sent */
							__( 'Queued %1$d, failed %2$d, sent %3$d', 'es-care-portal' ),
							isset( $health['mail_queued'] ) ? (int) $health['mail_queued'] : 0,
							isset( $health['mail_failed'] ) ? (int) $health['mail_failed'] : 0,
							isset( $health['mail_sent'] ) ? (int) $health['mail_sent'] : 0
						)
					);
					?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Mail cron', 'es-care-portal' ); ?></th>
				<td><?php echo esc_html( ! empty( $health['mail_cron'] ) ? $health['mail_cron'] : __( 'Not scheduled', 'es-care-portal' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Retention cron', 'es-care-portal' ); ?></th>
				<td><?php echo esc_html( ! empty( $health['retention_cron'] ) ? $health['retention_cron'] : __( 'Not scheduled', 'es-care-portal' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Portal SMTP', 'es-care-portal' ); ?></th>
				<td>
					<?php if ( ! empty( $health['smtp_enabled'] ) ) : ?>
						<?php esc_html_e( 'Enabled for portal mail', 'es-care-portal' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Disabled — forgot-password and other portal mail use PHP mail unless another SMTP plugin is sending WordPress mail', 'es-care-portal' ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'SMTP plugin conflicts', 'es-care-portal' ); ?></th>
				<td>
					<?php if ( ! empty( $health['smtp_conflicts'] ) ) : ?>
						<?php echo esc_html( implode( ', ', $health['smtp_conflicts'] ) ); ?>
						<p class="description"><?php esc_html_e( 'These plugins send WordPress mail globally. Portal SMTP is scoped to portal messages only.', 'es-care-portal' ); ?></p>
					<?php else : ?>
						<?php esc_html_e( 'None detected.', 'es-care-portal' ); ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Recent operational logs', 'es-care-portal' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Time', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Level', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Channel', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Message', 'es-care-portal' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $health['logs'] ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( 'No log entries yet.', 'es-care-portal' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $health['logs'] as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td><?php echo esc_html( $row->level ); ?></td>
						<td><?php echo esc_html( $row->channel ); ?></td>
						<td><?php echo esc_html( $row->message ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<p class="description"><?php esc_html_e( 'Logs never include passwords, tokens, or applicant identity numbers.', 'es-care-portal' ); ?></p>

	<h2><?php esc_html_e( 'Failed mail', 'es-care-portal' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Subject', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Updated', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Error', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Retry', 'es-care-portal' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $health['failed_mail'] ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( 'No failed messages.', 'es-care-portal' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $health['failed_mail'] as $mail ) : ?>
					<tr>
						<td><?php echo esc_html( $mail->subject ); ?></td>
						<td><?php echo esc_html( $mail->updated_at ); ?></td>
						<td><?php echo esc_html( $mail->last_error ); ?></td>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'esc_retry_mail_' . $mail->id, 'esc_retry_mail_nonce' ); ?>
								<input type="hidden" name="action" value="esc_retry_mail">
								<input type="hidden" name="esc_mail_id" value="<?php echo esc_attr( (string) $mail->id ); ?>">
								<button type="submit" class="button"><?php esc_html_e( 'Retry', 'es-care-portal' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
