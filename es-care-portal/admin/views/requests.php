<?php
/**
 * Service requests in WP admin.
 *
 * @package ESC_Portal
 *
 * @var object[] $requests
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'Service requests', 'es-care-portal' ); ?></h1>
	<p class="esc-admin-lede"><?php esc_html_e( 'Messages sent from the public Contact Us form and from signed-in dashboards.', 'es-care-portal' ); ?></p>

	<?php if ( empty( $requests ) ) : ?>
		<p><?php esc_html_e( 'No service requests yet.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'From', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Subject', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Message', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Sent', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $requests as $request ) : ?>
					<tr>
						<td>
							<?php echo esc_html( $request->display_name ); ?>
							<br><span class="description"><?php echo esc_html( $request->email ); ?></span>
						</td>
						<td><?php echo esc_html( $request->subject ); ?></td>
						<td><?php echo esc_html( $request->message ); ?></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $request->created_at ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
