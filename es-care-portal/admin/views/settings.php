<?php
/**
 * Plugin settings.
 *
 * @package ESC_Portal
 *
 * @var array $settings Settings.
 * @var array $pages    Page IDs.
 */

defined( 'ABSPATH' ) || exit;

$allowed = isset( $settings['allowed_types'] ) ? (array) $settings['allowed_types'] : array( 'pdf', 'doc', 'docx' );
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'ES Care Portal Settings', 'es-care-portal' ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_save_settings', 'esc_settings_nonce' ); ?>
		<input type="hidden" name="action" value="esc_save_settings">

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="notification_email"><?php esc_html_e( 'Notification email', 'es-care-portal' ); ?></label></th>
				<td>
					<input type="email" class="regular-text" id="notification_email" name="notification_email" value="<?php echo esc_attr( $settings['notification_email'] ); ?>">
					<p class="description"><?php esc_html_e( 'Receives a notice when a new application is submitted.', 'es-care-portal' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="max_file_mb"><?php esc_html_e( 'Max resume size (MB)', 'es-care-portal' ); ?></label></th>
				<td>
					<input type="number" min="1" max="25" id="max_file_mb" name="max_file_mb" value="<?php echo esc_attr( (string) $settings['max_file_mb'] ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Allowed resume types', 'es-care-portal' ); ?></th>
				<td>
					<?php foreach ( array( 'pdf' => 'PDF', 'doc' => 'DOC', 'docx' => 'DOCX' ) as $ext => $label ) : ?>
						<label>
							<input type="checkbox" name="allowed_types[]" value="<?php echo esc_attr( $ext ); ?>" <?php checked( in_array( $ext, $allowed, true ) ); ?>>
							<?php echo esc_html( $label ); ?>
						</label><br>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Frontend pages', 'es-care-portal' ); ?></th>
				<td>
					<ul>
						<?php foreach ( (array) $pages as $slug => $page_id ) : ?>
							<li>
								<strong><?php echo esc_html( $slug ); ?>:</strong>
								<?php if ( $page_id && get_post( $page_id ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>"><?php echo esc_html( get_the_title( $page_id ) ); ?></a>
								<?php else : ?>
									<?php esc_html_e( 'Missing — deactivate and reactivate the plugin to recreate pages.', 'es-care-portal' ); ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<p class="description"><?php esc_html_e( 'Uninstalling the plugin removes these pages, roles, and settings. Jobs, applications, and resume files are kept.', 'es-care-portal' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save settings', 'es-care-portal' ) ); ?>
	</form>
</div>
