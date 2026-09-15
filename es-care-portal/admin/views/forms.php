<?php
/**
 * Employment form uploads.
 *
 * @package ESC_Portal
 *
 * @var object[] $forms
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'Employment forms', 'es-care-portal' ); ?></h1>
	<p class="esc-admin-lede"><?php esc_html_e( 'Upload PDF or Word documents that job seekers can download after they apply.', 'es-care-portal' ); ?></p>

	<?php if ( ! empty( $_GET['esc_error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-error"><p><?php esc_html_e( 'The form could not be uploaded. Use a PDF, DOC, or DOCX file and include a title.', 'es-care-portal' ); ?></p></div>
	<?php endif; ?>

	<div class="esc-admin-grid">
		<div class="esc-admin-panel">
			<h2><?php esc_html_e( 'Posted forms', 'es-care-portal' ); ?></h2>
			<?php if ( empty( $forms ) ) : ?>
				<p><?php esc_html_e( 'No forms uploaded yet.', 'es-care-portal' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Title', 'es-care-portal' ); ?></th>
							<th><?php esc_html_e( 'File', 'es-care-portal' ); ?></th>
							<th><?php esc_html_e( 'Added', 'es-care-portal' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $forms as $form ) : ?>
							<tr>
								<td><?php echo esc_html( $form->title ); ?></td>
								<td><?php echo esc_html( $form->original_name ); ?></td>
								<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $form->created_at ) ); ?></td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this form?', 'es-care-portal' ) ); ?>');">
										<?php wp_nonce_field( 'esc_form_delete_' . $form->id, 'esc_form_delete_nonce' ); ?>
										<input type="hidden" name="action" value="esc_form_delete">
										<input type="hidden" name="esc_form_id" value="<?php echo esc_attr( (string) $form->id ); ?>">
										<button type="submit" class="button-link-delete"><?php esc_html_e( 'Delete', 'es-care-portal' ); ?></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<div class="esc-admin-panel">
			<h2><?php esc_html_e( 'Upload a form', 'es-care-portal' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'esc_form_upload', 'esc_form_upload_nonce' ); ?>
				<input type="hidden" name="action" value="esc_form_upload">
				<p>
					<label for="esc_form_title"><?php esc_html_e( 'Title', 'es-care-portal' ); ?></label><br>
					<input type="text" class="regular-text" id="esc_form_title" name="esc_form_title" required>
				</p>
				<p>
					<label for="esc_form_file"><?php esc_html_e( 'File (PDF, DOC, DOCX)', 'es-care-portal' ); ?></label><br>
					<input type="file" id="esc_form_file" name="esc_form_file" accept=".pdf,.doc,.docx,application/pdf" required>
				</p>
				<?php submit_button( __( 'Upload form', 'es-care-portal' ) ); ?>
			</form>
		</div>
	</div>
</div>
