<?php
/**
 * Review an application on the frontend.
 *
 * @package ESC_Portal
 *
 * @var array $review
 */

defined( 'ABSPATH' ) || exit;

$snap = $review['snap'];
$id   = $review['id'];
?>
<section class="esc-card">
	<h3><?php esc_html_e( 'Review application', 'es-care-portal' ); ?></h3>
	<p><strong><?php echo esc_html( trim( $snap['first_name'] . ' ' . $snap['last_name'] ) ); ?></strong> — <?php echo esc_html( get_the_title( $snap['job_id'] ) ); ?></p>
	<p><?php echo esc_html( $snap['email'] ); ?> · <?php echo esc_html( $snap['phone'] ); ?></p>
	<p><?php echo esc_html( trim( $snap['city'] . ', ' . $snap['state'] . ' ' . $snap['zip'], ', ' ) ); ?></p>
	<p><?php esc_html_e( 'Certifications:', 'es-care-portal' ); ?> <?php echo esc_html( ESC_Portal_Helpers::format_choices( $snap['certifications'], ESC_Portal_Helpers::certifications() ) ); ?></p>
	<div class="esc-actions">
		<?php foreach ( ESC_Portal_Uploads::document_types() as $doc_key => $doc ) : ?>
			<?php
			$file_key = ( 'resume' === $doc_key ) ? 'resume_file' : ( $doc_key . '_file' );
			if ( empty( $snap[ $file_key ] ) ) {
				continue;
			}
			?>
			<a class="esc-button esc-button--ghost esc-button--small" href="<?php echo esc_url( ESC_Portal_Uploads::download_url( $id, $doc_key ) ); ?>">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: document label */
						__( 'Download %s', 'es-care-portal' ),
						$doc['label']
					)
				);
				?>
			</a>
		<?php endforeach; ?>
	</div>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="esc-form">
		<?php wp_nonce_field( 'esc_portal_status', 'esc_status_nonce' ); ?>
		<input type="hidden" name="action" value="esc_portal_status">
		<input type="hidden" name="esc_application_id" value="<?php echo esc_attr( (string) $id ); ?>">
		<p class="esc-field">
			<label for="esc_status"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></label>
			<select id="esc_status" name="esc_status">
				<?php foreach ( ESC_Portal_Helpers::application_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $snap['status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="esc-field">
			<label for="esc_notes"><?php esc_html_e( 'Internal notes', 'es-care-portal' ); ?></label>
			<textarea id="esc_notes" name="esc_notes" rows="5"><?php echo esc_textarea( $snap['notes'] ); ?></textarea>
		</p>
		<p><button type="submit" class="esc-button"><?php esc_html_e( 'Save review', 'es-care-portal' ); ?></button></p>
	</form>
</section>
