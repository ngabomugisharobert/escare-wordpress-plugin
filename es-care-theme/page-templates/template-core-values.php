<?php
/**
 * Template Name: Core Values
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Core Values', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Integrity and professionalism guide how we recruit, place, and follow up. These values are a public draft for owner review.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-values">
			<article>
				<h3><?php esc_html_e( 'Integrity', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'We say what we can staff and we do not overpromise. Credentials, availability, and facility needs are represented honestly.', 'es-care' ); ?></p>
			</article>
			<article>
				<h3><?php esc_html_e( 'Results focused', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'The measure is coverage that holds: the right person, on time, ready to work. We stay in contact until the shift is filled or we tell you we cannot fill it.', 'es-care' ); ?></p>
			</article>
			<article>
				<h3><?php esc_html_e( 'Openness', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Facilities and caregivers should know where they stand. We communicate clearly about next steps, screening, and job status.', 'es-care' ); ?></p>
			</article>
			<article>
				<h3><?php esc_html_e( 'Respect', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Patients, residents, families, and staff deserve courteous, professional care. That standard applies to everyone we place.', 'es-care' ); ?></p>
			</article>
		</div>
	</div>
</section>
<?php
get_footer();
