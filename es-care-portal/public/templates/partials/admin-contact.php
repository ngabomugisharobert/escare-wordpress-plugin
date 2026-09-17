<?php
/**
 * Portal admin contact messages.
 *
 * @package ESC_Portal
 *
 * @var object[] $requests
 * @var int      $requests_total
 * @var array    $table_req
 */

defined( 'ABSPATH' ) || exit;

$requests       = is_array( $requests ) ? $requests : array();
$requests_total = isset( $requests_total ) ? (int) $requests_total : count( $requests );
$req            = isset( $table_req ) && is_array( $table_req ) ? $table_req : ESC_Portal_Helpers::table_request( array( 'created_at', 'subject', 'email', 'name' ) );
$base           = ESC_Portal_Helpers::dashboard_url( 'contact' );
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Contact Us', 'es-care-portal' ); ?></h2>
</div>

<div class="esc-card esc-data-panel">
	<form method="get" class="esc-data-toolbar" data-esc-table-toolbar="esc-admin-contact" action="<?php echo esc_url( $base ); ?>">
		<input type="hidden" name="esc_view" value="contact">
		<input type="hidden" name="_esc_table" value="<?php echo esc_attr( wp_create_nonce( 'esc_portal_table' ) ); ?>">
		<label class="esc-data-search">
			<span class="screen-reader-text"><?php esc_html_e( 'Search messages', 'es-care-portal' ); ?></span>
			<input type="search" name="esc_q" class="esc-data-search-input" value="<?php echo esc_attr( $req['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search name, email, or subject…', 'es-care-portal' ); ?>">
		</label>
		<button type="submit" class="esc-button esc-button--small"><?php esc_html_e( 'Filter', 'es-care-portal' ); ?></button>
		<p class="esc-data-count" aria-live="polite" data-esc-total="<?php echo esc_attr( (string) $requests_total ); ?>"></p>
	</form>

	<div class="esc-table-wrap">
		<table class="esc-table esc-data-table" id="esc-admin-contact">
			<thead>
				<tr>
					<?php echo ESC_Portal_Helpers::table_th( 'name', __( 'From', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo ESC_Portal_Helpers::table_th( 'email', __( 'Email', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo ESC_Portal_Helpers::table_th( 'subject', __( 'Subject', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo ESC_Portal_Helpers::table_th( 'created_at', __( 'Sent', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<th scope="col"><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $requests ) ) : ?>
					<tr class="esc-data-empty">
						<td colspan="5"><?php esc_html_e( 'No contact messages match this search.', 'es-care-portal' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $requests as $row ) : ?>
						<tr
							data-esc-name="<?php echo esc_attr( strtolower( $row->display_name ) ); ?>"
							data-esc-email="<?php echo esc_attr( strtolower( $row->email ) ); ?>"
							data-esc-subject="<?php echo esc_attr( strtolower( $row->subject ) ); ?>"
							data-esc-created_at="<?php echo esc_attr( strtotime( $row->created_at ) ); ?>"
							data-esc-search="<?php echo esc_attr( strtolower( $row->display_name . ' ' . $row->email . ' ' . $row->subject . ' ' . $row->message ) ); ?>"
						>
							<td data-label="<?php esc_attr_e( 'From', 'es-care-portal' ); ?>">
								<strong class="esc-user-name"><?php echo esc_html( $row->display_name ? $row->display_name : '—' ); ?></strong>
								<?php if ( empty( $row->user_id ) ) : ?>
									<span class="esc-muted esc-table-sub"><?php esc_html_e( 'Guest', 'es-care-portal' ); ?></span>
								<?php endif; ?>
							</td>
							<td class="esc-email-cell" data-label="<?php esc_attr_e( 'Email', 'es-care-portal' ); ?>"><?php echo esc_html( $row->email ? $row->email : '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Subject', 'es-care-portal' ); ?>"><?php echo esc_html( $row->subject ); ?></td>
							<td data-label="<?php esc_attr_e( 'Sent', 'es-care-portal' ); ?>"><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row->created_at ) ); ?></td>
							<td class="esc-actions-cell" data-label="<?php esc_attr_e( 'Actions', 'es-care-portal' ); ?>">
								<button
									type="button"
									class="esc-button esc-button--ghost esc-button--small"
									data-esc-message-view
									aria-haspopup="dialog"
									aria-controls="esc-contact-modal"
									data-name="<?php echo esc_attr( $row->display_name ); ?>"
									data-email="<?php echo esc_attr( $row->email ); ?>"
									data-subject="<?php echo esc_attr( $row->subject ); ?>"
									data-sent="<?php echo esc_attr( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row->created_at ) ); ?>"
									data-message="<?php echo esc_attr( $row->message ); ?>"
								><?php esc_html_e( 'View', 'es-care-portal' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php echo ESC_Portal_Helpers::pagination_html( $requests_total, $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>

<dialog id="esc-contact-modal" class="esc-modal" aria-labelledby="esc-contact-modal-title">
	<div class="esc-modal-card">
		<header class="esc-modal-head">
			<div>
				<p class="esc-modal-kicker" id="esc-contact-modal-title"><?php esc_html_e( 'Contact message', 'es-care-portal' ); ?></p>
				<p class="esc-modal-user" data-esc-message-name></p>
				<p class="esc-modal-email" data-esc-message-email></p>
			</div>
			<button type="button" class="esc-modal-close" data-esc-modal-close aria-label="<?php esc_attr_e( 'Close', 'es-care-portal' ); ?>">
				<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
					<path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
				</svg>
			</button>
		</header>
		<p class="esc-modal-meta"><strong data-esc-message-subject></strong></p>
		<p class="esc-modal-meta esc-muted" data-esc-message-sent></p>
		<div class="esc-modal-body" data-esc-message-body></div>
	</div>
</dialog>
