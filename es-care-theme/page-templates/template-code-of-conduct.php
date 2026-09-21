<?php
/**
 * Template Name: Code of Conduct
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
		<h1><?php esc_html_e( 'Code of Conduct', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Public summary. Draft for owner review.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-prose">
		<p><?php esc_html_e( 'Everyone placed by E&S Care Service LLC is expected to act professionally, legally, and respectfully in every assignment.', 'es-care' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'Follow facility policies, care plans, and lawful instructions from supervisors.', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Protect resident, patient, and family privacy. Do not share health information except as required for care.', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Arrive on time, fit for duty, and in appropriate attire.', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Treat colleagues, clients, and the public without harassment, discrimination, or abuse.', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Report safety concerns, incidents, and errors promptly.', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Do not offer or accept improper payments or gifts that could influence placements or care.', 'es-care' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Failure to meet these expectations may result in removal from an assignment and from future placements.', 'es-care' ); ?></p>
		</div>
	</div>
</section>
<?php
get_footer();
