<?php
/**
 * Dashboard users (custom table) — separate from WordPress users.
 *
 * @package ESC_Portal
 *
 * @var object[] $users
 * @var array<string,int> $counts
 * @var string $table_name
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'Dashboard Users', 'es-care-portal' ); ?></h1>
	<div class="notice notice-info inline">
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: database table name */
					__( 'Job seekers, employers, and portal admins are stored in %s — not in WordPress Users (wp_users). They sign in on the careers portal only.', 'es-care-portal' ),
					$table_name
				)
			);
			?>
		</p>
	</div>

	<div class="esc-admin-stats" style="margin:1.25rem 0;">
		<div class="esc-admin-stat">
			<strong><?php echo esc_html( (string) $counts['job_seeker'] ); ?></strong>
			<span><?php esc_html_e( 'Job Seekers', 'es-care-portal' ); ?></span>
		</div>
		<div class="esc-admin-stat">
			<strong><?php echo esc_html( (string) $counts['employer'] ); ?></strong>
			<span><?php esc_html_e( 'Employers', 'es-care-portal' ); ?></span>
		</div>
		<div class="esc-admin-stat">
			<strong><?php echo esc_html( (string) $counts['admin'] ); ?></strong>
			<span><?php esc_html_e( 'Portal Admins', 'es-care-portal' ); ?></span>
		</div>
	</div>

	<h2><?php esc_html_e( 'Create dashboard user', 'es-care-portal' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_create_dashboard_user', 'esc_dashboard_user_nonce' ); ?>
		<input type="hidden" name="action" value="esc_create_dashboard_user">
		<table class="form-table">
			<tr>
				<th><label for="esc_role"><?php esc_html_e( 'Role', 'es-care-portal' ); ?></label></th>
				<td>
					<select id="esc_role" name="esc_role" required>
						<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="esc_first_name"><?php esc_html_e( 'First name', 'es-care-portal' ); ?></label></th>
				<td><input type="text" id="esc_first_name" name="esc_first_name" required></td>
			</tr>
			<tr>
				<th><label for="esc_last_name"><?php esc_html_e( 'Last name', 'es-care-portal' ); ?></label></th>
				<td><input type="text" id="esc_last_name" name="esc_last_name" required></td>
			</tr>
			<tr>
				<th><label for="esc_company_name"><?php esc_html_e( 'Company (employers)', 'es-care-portal' ); ?></label></th>
				<td><input type="text" id="esc_company_name" name="esc_company_name"></td>
			</tr>
			<tr>
				<th><label for="esc_email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></label></th>
				<td><input type="email" id="esc_email" name="esc_email" required></td>
			</tr>
			<tr>
				<th><label for="esc_phone"><?php esc_html_e( 'Phone', 'es-care-portal' ); ?></label></th>
				<td><input type="tel" id="esc_phone" name="esc_phone"></td>
			</tr>
			<tr>
				<th><label for="esc_password"><?php esc_html_e( 'Password', 'es-care-portal' ); ?></label></th>
				<td>
					<input type="password" id="esc_password" name="esc_password" required minlength="8" autocomplete="new-password">
					<p class="description"><?php esc_html_e( '8+ characters with uppercase, lowercase, and a number.', 'es-care-portal' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Create dashboard user', 'es-care-portal' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'All dashboard users', 'es-care-portal' ); ?></h2>
	<?php
	$req         = isset( $table_req ) && is_array( $table_req ) ? $table_req : ESC_Portal_Helpers::table_request( array( 'created_at', 'email', 'role', 'last_name', 'status' ) );
	$users_total = isset( $users_total ) ? (int) $users_total : count( (array) $users );
	$base        = admin_url( 'admin.php?page=esc-portal-users' );
	?>
	<form method="get" style="margin:1rem 0;">
		<input type="hidden" name="page" value="esc-portal-users">
		<input type="hidden" name="_esc_table" value="<?php echo esc_attr( wp_create_nonce( 'esc_portal_table' ) ); ?>">
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Search users', 'es-care-portal' ); ?></span>
			<input type="search" name="esc_q" value="<?php echo esc_attr( $req['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search name or email…', 'es-care-portal' ); ?>">
		</label>
		<label for="esc_admin_role"><?php esc_html_e( 'Role', 'es-care-portal' ); ?>
			<select id="esc_admin_role" name="esc_role">
				<option value=""><?php esc_html_e( 'All roles', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $req['role'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label for="esc_admin_status"><?php esc_html_e( 'Status', 'es-care-portal' ); ?>
			<select id="esc_admin_status" name="esc_status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Users::statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $req['status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php submit_button( __( 'Filter', 'es-care-portal' ), 'secondary', '', false ); ?>
	</form>
	<table class="widefat striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'ID', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Name', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Role', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Update', 'es-care-portal' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $users ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No dashboard users match this search.', 'es-care-portal' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $users as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $row->id ); ?></td>
						<td><?php echo esc_html( $row->display_name ); ?></td>
						<td><?php echo esc_html( $row->email ); ?></td>
						<td><?php echo esc_html( ESC_Portal_Users::role_label( $row->role ) ); ?></td>
						<td><?php echo esc_html( ESC_Portal_Users::status_label( $row->status ) ); ?></td>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'esc_portal_user', 'esc_user_nonce' ); ?>
								<input type="hidden" name="action" value="esc_portal_user">
								<input type="hidden" name="esc_from_wp" value="1">
								<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
								<select name="esc_role" aria-label="<?php esc_attr_e( 'Change role', 'es-care-portal' ); ?>">
									<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row->role, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<select name="esc_status" aria-label="<?php esc_attr_e( 'Change status', 'es-care-portal' ); ?>">
									<?php foreach ( ESC_Portal_Users::statuses() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row->status, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<?php submit_button( __( 'Save', 'es-care-portal' ), 'secondary', 'submit', false ); ?>
							</form>
							<?php if ( ESC_Portal_Users::ROLE_EMPLOYER === $row->role && ESC_Portal_Users::STATUS_PENDING_EMAIL === $row->status ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:0.4rem;">
									<?php wp_nonce_field( 'esc_resend_verification_' . $row->id, 'esc_resend_nonce' ); ?>
									<input type="hidden" name="action" value="esc_resend_verification">
									<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
									<?php submit_button( __( 'Resend verification', 'es-care-portal' ), 'secondary', 'submit', false ); ?>
								</form>
							<?php endif; ?>
							<?php if ( ESC_Portal_Users::ROLE_EMPLOYER === $row->role && ESC_Portal_Users::STATUS_PENDING_ADMIN === $row->status ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-top:0.4rem;">
									<?php wp_nonce_field( 'esc_approve_employer_' . $row->id, 'esc_approve_nonce' ); ?>
									<input type="hidden" name="action" value="esc_approve_employer">
									<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
									<?php submit_button( __( 'Approve', 'es-care-portal' ), 'primary', 'submit', false ); ?>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-top:0.4rem;">
									<?php wp_nonce_field( 'esc_reject_employer_' . $row->id, 'esc_reject_nonce' ); ?>
									<input type="hidden" name="action" value="esc_reject_employer">
									<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
									<?php submit_button( __( 'Reject', 'es-care-portal' ), 'delete', 'submit', false ); ?>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php echo ESC_Portal_Helpers::pagination_html( $users_total, $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
