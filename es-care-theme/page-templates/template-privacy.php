<?php
/**
 * Template Name: Privacy Policy
 *
 * Draft for owner review.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Privacy Policy', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Draft for owner review. This is not legal advice.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-prose">
		<p><?php esc_html_e( 'E&S Care Service LLC (“we”) operates the public website and the careers portal. This page describes how we handle personal information you submit through the site.', 'es-care' ); ?></p>
		<h2><?php esc_html_e( 'What we collect', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'Contact messages include your name, email, subject, and message. Portal accounts include name, email, phone, role, and — for employers — company name. Applications may include work history, certifications, and a resume file. We do not collect Social Security numbers or driver’s-license numbers through this site.', 'es-care' ); ?></p>
		<h2><?php esc_html_e( 'How we use it', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'We use this information to respond to inquiries, operate the portal, match candidates with jobs, and meet record-keeping duties. Application materials are retained for a limited period (default three years in the portal settings) and then removed.', 'es-care' ); ?></p>
		<h2><?php esc_html_e( 'Sharing', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'We share application details with the facility or employer associated with a job when you apply. We do not sell personal information. Hosting, email, and similar vendors may process data solely to run the site.', 'es-care' ); ?></p>
		<h2><?php esc_html_e( 'Your choices', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'You may request access or deletion of portal personal data through the site administrator. WordPress Tools for export and erase include portal records when that feature is enabled.', 'es-care' ); ?></p>
		<p><?php echo esc_html( sprintf( __( 'Questions: %s.', 'es-care' ), escare_email() ) ); ?></p>
		</div>
	</div>
</section>
<?php
get_footer();
