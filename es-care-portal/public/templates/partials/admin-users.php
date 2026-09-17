<?php
/**
 * Portal admin users table.
 *
 * @package ESC_Portal
 *
 * @var object[] $users
 * @var int      $users_total
 * @var array    $table_req
 */

defined( 'ABSPATH' ) || exit;

$users       = is_array( $users ) ? $users : array();
$users_total = isset( $users_total ) ? (int) $users_total : count( $users );
$req         = isset( $table_req ) && is_array( $table_req ) ? $table_req : ESC_Portal_Helpers::table_request( array( 'last_name', 'email', 'role', 'status' ) );
$base        = ESC_Portal_Helpers::dashboard_url( 'users' );
$post_url    = admin_url( 'admin-post.php' );
$current_id  = ESC_Portal_Auth::current_user_id();
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Users', 'es-care-portal' ); ?></h2>
</div>

<div class="esc-card esc-data-panel">
	<form method="get" class="esc-data-toolbar" data-esc-table-toolbar="esc-admin-users" action="<?php echo esc_url( $base ); ?>">
		<input type="hidden" name="esc_view" value="users">
		<input type="hidden" name="_esc_table" value="<?php echo esc_attr( wp_create_nonce( 'esc_portal_table' ) ); ?>">
		<label class="esc-data-search">
			<span class="screen-reader-text"><?php esc_html_e( 'Search users', 'es-care-portal' ); ?></span>
			<input type="search" name="esc_q" class="esc-data-search-input" value="<?php echo esc_attr( $req['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search name or email…', 'es-care-portal' ); ?>">
		</label>
		<label class="esc-data-filter" for="esc-filter-role">
			<span><?php esc_html_e( 'Role', 'es-care-portal' ); ?></span>
			<select id="esc-filter-role" name="esc_role" data-esc-filter="role">
				<option value=""><?php esc_html_e( 'All roles', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $req['role'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="esc-data-filter" for="esc-filter-status">
			<span><?php esc_html_e( 'Status', 'es-care-portal' ); ?></span>
			<select id="esc-filter-status" name="esc_status" data-esc-filter="status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Users::statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $req['status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="submit" class="esc-button esc-button--small"><?php esc_html_e( 'Filter', 'es-care-portal' ); ?></button>
		<p class="esc-data-count" aria-live="polite" data-esc-total="<?php echo esc_attr( (string) $users_total ); ?>"></p>
	</form>

	<div class="esc-table-wrap">
		<table class="esc-table esc-data-table" id="esc-admin-users">
			<thead>
				<tr>
					<?php echo ESC_Portal_Helpers::table_th( 'last_name', __( 'User', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo ESC_Portal_Helpers::table_th( 'role', __( 'Role', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo ESC_Portal_Helpers::table_th( 'status', __( 'Status', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<th scope="col"><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $users ) ) : ?>
					<tr class="esc-data-empty">
						<td colspan="4"><?php esc_html_e( 'No dashboard users match this search.', 'es-care-portal' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $users as $row ) : ?>
						<?php
						$can_delete    = (int) $row->id !== $current_id;
						$pending_email = ESC_Portal_Users::ROLE_EMPLOYER === $row->role && ESC_Portal_Users::STATUS_PENDING_EMAIL === $row->status;
						$pending_admin = ESC_Portal_Users::ROLE_EMPLOYER === $row->role && ESC_Portal_Users::STATUS_PENDING_ADMIN === $row->status;
						?>
						<tr
							data-esc-name="<?php echo esc_attr( strtolower( $row->display_name ) ); ?>"
							data-esc-email="<?php echo esc_attr( strtolower( $row->email ) ); ?>"
							data-esc-role="<?php echo esc_attr( $row->role ); ?>"
							data-esc-status="<?php echo esc_attr( $row->status ); ?>"
							data-esc-search="<?php echo esc_attr( strtolower( $row->display_name . ' ' . $row->email ) ); ?>"
						>
							<td data-label="<?php esc_attr_e( 'User', 'es-care-portal' ); ?>">
								<div class="esc-user-cell">
									<strong class="esc-user-name"><?php echo esc_html( $row->display_name ); ?></strong>
									<span class="esc-user-email"><?php echo esc_html( $row->email ); ?></span>
									<?php if ( ! empty( $row->company_name ) ) : ?>
										<span class="esc-muted esc-table-sub"><?php echo esc_html( $row->company_name ); ?></span>
									<?php endif; ?>
								</div>
							</td>
							<td class="esc-role-cell" data-label="<?php esc_attr_e( 'Role', 'es-care-portal' ); ?>">
								<span class="esc-pill esc-pill--role"><?php echo esc_html( ESC_Portal_Users::role_label( $row->role ) ); ?></span>
							</td>
							<td class="esc-status-cell" data-label="<?php esc_attr_e( 'Status', 'es-care-portal' ); ?>">
								<span class="esc-status esc-status--user-<?php echo esc_attr( sanitize_html_class( $row->status ) ); ?>"><?php echo esc_html( ESC_Portal_Users::status_label( $row->status ) ); ?></span>
							</td>
							<td class="esc-actions-cell" data-label="<?php esc_attr_e( 'Actions', 'es-care-portal' ); ?>">
								<button
									type="button"
									class="esc-button esc-button--ghost esc-button--small"
									data-esc-user-manage
									aria-haspopup="dialog"
									aria-controls="esc-user-modal"
									data-user-id="<?php echo esc_attr( (string) $row->id ); ?>"
									data-name="<?php echo esc_attr( $row->display_name ); ?>"
									data-email="<?php echo esc_attr( $row->email ); ?>"
									data-role="<?php echo esc_attr( $row->role ); ?>"
									data-status="<?php echo esc_attr( $row->status ); ?>"
									data-can-delete="<?php echo $can_delete ? '1' : '0'; ?>"
									data-pending-email="<?php echo $pending_email ? '1' : '0'; ?>"
									data-pending-admin="<?php echo $pending_admin ? '1' : '0'; ?>"
									data-delete-nonce="<?php echo esc_attr( $can_delete ? wp_create_nonce( 'esc_admin_delete_user_' . $row->id ) : '' ); ?>"
									data-resend-nonce="<?php echo esc_attr( $pending_email ? wp_create_nonce( 'esc_resend_verification_' . $row->id ) : '' ); ?>"
									data-approve-nonce="<?php echo esc_attr( $pending_admin ? wp_create_nonce( 'esc_approve_employer_' . $row->id ) : '' ); ?>"
									data-reject-nonce="<?php echo esc_attr( $pending_admin ? wp_create_nonce( 'esc_reject_employer_' . $row->id ) : '' ); ?>"
								><?php esc_html_e( 'Manage', 'es-care-portal' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php echo ESC_Portal_Helpers::pagination_html( $users_total, $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>

<dialog id="esc-user-modal" class="esc-modal" aria-labelledby="esc-user-modal-title">
	<div class="esc-modal-card">
		<header class="esc-modal-head">
			<div>
				<p class="esc-modal-kicker" id="esc-user-modal-title"><?php esc_html_e( 'Manage user', 'es-care-portal' ); ?></p>
				<p class="esc-modal-user" data-esc-modal-name></p>
				<p class="esc-modal-email" data-esc-modal-email></p>
			</div>
			<button type="button" class="esc-modal-close" data-esc-modal-close aria-label="<?php esc_attr_e( 'Close', 'es-care-portal' ); ?>">
				<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
					<path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
				</svg>
			</button>
		</header>

		<form method="post" action="<?php echo esc_url( $post_url ); ?>" class="esc-modal-form" data-esc-modal-save>
			<?php wp_nonce_field( 'esc_portal_user', 'esc_user_nonce' ); ?>
			<input type="hidden" name="action" value="esc_portal_user">
			<input type="hidden" name="esc_user_id" value="" data-esc-modal-user-id>
			<label class="esc-field">
				<span><?php esc_html_e( 'Role', 'es-care-portal' ); ?></span>
				<select name="esc_role" data-esc-modal-role>
					<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="esc-field">
				<span><?php esc_html_e( 'Status', 'es-care-portal' ); ?></span>
				<select name="esc_status" data-esc-modal-status>
					<?php foreach ( ESC_Portal_Users::statuses() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<button type="submit" class="esc-button"><?php esc_html_e( 'Save', 'es-care-portal' ); ?></button>
		</form>

		<div class="esc-modal-extras" hidden data-esc-modal-pending-email>
			<form method="post" action="<?php echo esc_url( $post_url ); ?>">
				<input type="hidden" name="esc_resend_nonce" value="" data-esc-modal-resend-nonce>
				<input type="hidden" name="action" value="esc_resend_verification">
				<input type="hidden" name="esc_user_id" value="" data-esc-modal-user-id>
				<button type="submit" class="esc-button esc-button--ghost"><?php esc_html_e( 'Resend verification', 'es-care-portal' ); ?></button>
			</form>
		</div>

		<div class="esc-modal-extras esc-modal-extras--split" hidden data-esc-modal-pending-admin>
			<form method="post" action="<?php echo esc_url( $post_url ); ?>">
				<input type="hidden" name="esc_approve_nonce" value="" data-esc-modal-approve-nonce>
				<input type="hidden" name="action" value="esc_approve_employer">
				<input type="hidden" name="esc_user_id" value="" data-esc-modal-user-id>
				<button type="submit" class="esc-button"><?php esc_html_e( 'Approve', 'es-care-portal' ); ?></button>
			</form>
			<form method="post" action="<?php echo esc_url( $post_url ); ?>">
				<input type="hidden" name="esc_reject_nonce" value="" data-esc-modal-reject-nonce>
				<input type="hidden" name="action" value="esc_reject_employer">
				<input type="hidden" name="esc_user_id" value="" data-esc-modal-user-id>
				<button type="submit" class="esc-button esc-button--danger"><?php esc_html_e( 'Reject', 'es-care-portal' ); ?></button>
			</form>
		</div>

		<form method="post" action="<?php echo esc_url( $post_url ); ?>" class="esc-modal-delete" hidden data-esc-modal-delete data-esc-confirm="<?php echo esc_attr__( 'Permanently delete this dashboard user? Associated applications will be anonymized.', 'es-care-portal' ); ?>">
			<input type="hidden" name="esc_delete_user_nonce" value="" data-esc-modal-delete-nonce>
			<input type="hidden" name="action" value="esc_admin_delete_user">
			<input type="hidden" name="esc_user_id" value="" data-esc-modal-user-id>
			<button type="submit" class="esc-button esc-button--danger esc-button--ghost"><?php esc_html_e( 'Delete user', 'es-care-portal' ); ?></button>
		</form>
	</div>
</dialog>
