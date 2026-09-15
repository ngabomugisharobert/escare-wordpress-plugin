<?php
/**
 * Employment form downloads.
 *
 * @package ESC_Portal
 *
 * @var object[] $forms
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Employment Forms', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Download the forms to complete after your application and pre-hire assessment. Fill them out and keep copies for your records. Staff will tell you how to return them.', 'es-care-portal' ); ?></p>

	<?php if ( empty( $forms ) ) : ?>
		<p><?php esc_html_e( 'No employment forms have been posted yet. Please check back soon.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<ul class="esc-job-list">
			<?php foreach ( $forms as $form ) : ?>
				<li class="esc-job-card">
					<div>
						<h3><?php echo esc_html( $form->title ); ?></h3>
						<p class="esc-muted"><?php echo esc_html( $form->original_name ); ?></p>
					</div>
					<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Forms::download_url( $form->id ) ); ?>"><?php esc_html_e( 'Download', 'es-care-portal' ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
