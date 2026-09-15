<?php
/**
 * Portal users (custom table) in WP admin.
 *
 * @package ESC_Portal
 *
 * @var object[] $users
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'Portal Users', 'es-care-portal' ); ?></h1>
	<p><?php esc_html_e( 'These accounts live in a separate table from WordPress users. Job seekers, employers, and portal admins sign in on the frontend.', 'es-care-portal' ); ?></p>

	<h2><?php esc_html_e( 'Create portal admin', 'es-care-portal' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_create_portal_admin', 'esc_portal_admin_nonce' ); ?>
		<input type="hidden" name="action" value="esc_create_portal_admin">
		<table class="form-table">
			<tr>
				<th><label for="esc_first_name"><?php esc_html_e( 'First name', 'es-care-portal' ); ?></label></th>
				<td><input type="text" id="esc_first_name" name="esc_first_name" required></td>
			</tr>
			<tr>
				<th><label for="esc_last_name"><?php esc_html_e( 'Last name', 'es-care-portal' ); ?></label></th>
				<td><input type="text" id="esc_last_name" name="esc_last_name" required></td>
			</tr>
			<tr>
				<th><label for="esc_email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label></th>
				<td><input type="email" id="esc_email" name="esc_email" required></td>
			</tr>
			<tr>
				<th><label for="esc_password"><?php esc_html_e( 'Password', 'es-care-portal' ); ?></label></th>
				<td><input type="password" id="esc_password" name="esc_password" required minlength="8"></td>
			</tr>
		</table>
		<?php submit_button( __( 'Create portal admin', 'es-care-portal' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'All portal users', 'es-care-portal' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'es-care-portal' ); ?></th>
				<th><?php esc_html_e( 'Email', 'es-care-portal' ); ?></th>
				<th><?php esc_html_e( 'Role', 'es-care-portal' ); ?></th>
				<th><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
				<th><?php esc_html_e( 'Update', 'es-care-portal' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $users ) ) : ?>
				<tr><td colspan="5"><?php esc_html_e( 'No portal users yet. Register on the frontend or create an admin above.', 'es-care-portal' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $users as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->display_name ); ?></td>
						<td><?php echo esc_html( $row->email ); ?></td>
						<td><?php echo esc_html( ESC_Portal_Users::role_label( $row->role ) ); ?></td>
						<td><?php echo esc_html( $row->status ); ?></td>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'esc_portal_user', 'esc_user_nonce' ); ?>
								<input type="hidden" name="action" value="esc_portal_user">
								<input type="hidden" name="esc_from_wp" value="1">
								<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
								<select name="esc_role">
									<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row->role, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<select name="esc_status">
									<option value="active" <?php selected( $row->status, 'active' ); ?>><?php esc_html_e( 'Active', 'es-care-portal' ); ?></option>
									<option value="disabled" <?php selected( $row->status, 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'es-care-portal' ); ?></option>
								</select>
								<?php submit_button( __( 'Save', 'es-care-portal' ), 'secondary', 'submit', false ); ?>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
