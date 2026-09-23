<?php
/**
 * Template Name: Equal Opportunity
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
		<h1><?php esc_html_e( 'Equal Opportunity', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Public summary. Draft for owner review.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-prose">
		<p><?php esc_html_e( 'E&S Care Services LLC is an equal opportunity organization. We recruit, place, and work with people without regard to race, color, national origin, religion, sex, gender identity, sexual orientation, age, disability, veteran status, or any other status protected by applicable law.', 'es-care' ); ?></p>
		<p><?php esc_html_e( 'Employment decisions and staffing requests are based on qualifications, availability, and the lawful requirements of the role. Harassment and retaliation are not tolerated.', 'es-care' ); ?></p>
		<p><?php echo esc_html( sprintf( __( 'To raise a concern, contact %s.', 'es-care' ), escare_email() ) ); ?></p>
		</div>
	</div>
</section>
<?php
get_footer();
