<?php
/**
 * Portal admin users table.
 *
 * @package ESC_Portal
 *
 * @var object[] $users
 */

defined( 'ABSPATH' ) || exit;

$users = is_array( $users ) ? $users : array();
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Users', 'es-care-portal' ); ?></h2>
</div>
<p class="esc-dash-copy"><?php esc_html_e( 'Dashboard accounts only — not WordPress site users. Search, filter, and sort the table below.', 'es-care-portal' ); ?></p>

<div class="esc-card esc-data-panel">
	<div class="esc-data-toolbar" data-esc-table-toolbar="esc-admin-users">
		<label class="esc-data-search">
			<span class="screen-reader-text"><?php esc_html_e( 'Search users', 'es-care-portal' ); ?></span>
			<input type="search" class="esc-data-search-input" placeholder="<?php esc_attr_e( 'Search name or email…', 'es-care-portal' ); ?>">
		</label>
		<label class="esc-data-filter">
			<span><?php esc_html_e( 'Role', 'es-care-portal' ); ?></span>
			<select data-esc-filter="role">
				<option value=""><?php esc_html_e( 'All roles', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="esc-data-filter">
			<span><?php esc_html_e( 'Status', 'es-care-portal' ); ?></span>
			<select data-esc-filter="status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<option value="active"><?php esc_html_e( 'Active', 'es-care-portal' ); ?></option>
				<option value="disabled"><?php esc_html_e( 'Disabled', 'es-care-portal' ); ?></option>
			</select>
		</label>
		<p class="esc-data-count" aria-live="polite"></p>
	</div>

	<div class="esc-table-wrap">
		<table class="esc-table esc-data-table" id="esc-admin-users">
			<thead>
				<tr>
					<th><button type="button" class="esc-sort" data-esc-sort="name"><?php esc_html_e( 'Name', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="role"><?php esc_html_e( 'Role', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="status"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></button></th>
					<th><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $users ) ) : ?>
					<tr class="esc-data-empty">
						<td colspan="5"><?php esc_html_e( 'No dashboard users yet.', 'es-care-portal' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $users as $row ) : ?>
						<tr
							data-esc-name="<?php echo esc_attr( strtolower( $row->display_name ) ); ?>"
							data-esc-email="<?php echo esc_attr( strtolower( $row->email ) ); ?>"
							data-esc-role="<?php echo esc_attr( $row->role ); ?>"
							data-esc-status="<?php echo esc_attr( $row->status ); ?>"
							data-esc-search="<?php echo esc_attr( strtolower( $row->display_name . ' ' . $row->email ) ); ?>"
						>
							<td data-label="<?php esc_attr_e( 'Name', 'es-care-portal' ); ?>">
								<strong><?php echo esc_html( $row->display_name ); ?></strong>
								<?php if ( ! empty( $row->company_name ) ) : ?>
									<span class="esc-muted esc-table-sub"><?php echo esc_html( $row->company_name ); ?></span>
								<?php endif; ?>
							</td>
							<td data-label="<?php esc_attr_e( 'Email', 'es-care-portal' ); ?>"><?php echo esc_html( $row->email ); ?></td>
							<td data-label="<?php esc_attr_e( 'Role', 'es-care-portal' ); ?>">
								<span class="esc-pill esc-pill--role"><?php echo esc_html( ESC_Portal_Users::role_label( $row->role ) ); ?></span>
							</td>
							<td data-label="<?php esc_attr_e( 'Status', 'es-care-portal' ); ?>">
								<span class="esc-status esc-status--<?php echo esc_attr( 'active' === $row->status ? 'hired' : 'closed' ); ?>"><?php echo esc_html( $row->status ); ?></span>
							</td>
							<td data-label="<?php esc_attr_e( 'Actions', 'es-care-portal' ); ?>">
								<div class="esc-row-action-group">
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="esc-row-actions">
										<?php wp_nonce_field( 'esc_portal_user', 'esc_user_nonce' ); ?>
										<input type="hidden" name="action" value="esc_portal_user">
										<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
										<select name="esc_role" aria-label="<?php esc_attr_e( 'Change role', 'es-care-portal' ); ?>">
											<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
												<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row->role, $key ); ?>><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
										</select>
										<select name="esc_status" aria-label="<?php esc_attr_e( 'Change status', 'es-care-portal' ); ?>">
											<option value="active" <?php selected( $row->status, 'active' ); ?>><?php esc_html_e( 'Active', 'es-care-portal' ); ?></option>
											<option value="disabled" <?php selected( $row->status, 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'es-care-portal' ); ?></option>
										</select>
										<button type="submit" class="esc-button esc-button--small"><?php esc_html_e( 'Save', 'es-care-portal' ); ?></button>
									</form>
									<?php if ( (int) $row->id !== ESC_Portal_Auth::current_user_id() ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-esc-confirm="<?php echo esc_attr__( 'Permanently delete this dashboard user? This cannot be undone.', 'es-care-portal' ); ?>">
											<?php wp_nonce_field( 'esc_admin_delete_user_' . $row->id, 'esc_delete_user_nonce' ); ?>
											<input type="hidden" name="action" value="esc_admin_delete_user">
											<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
											<button type="submit" class="esc-button esc-button--danger esc-button--small"><?php esc_html_e( 'Delete', 'es-care-portal' ); ?></button>
										</form>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
