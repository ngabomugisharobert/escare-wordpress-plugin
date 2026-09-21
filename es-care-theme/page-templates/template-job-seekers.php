<?php
/**
 * Template Name: Job Seekers
 *
 * Marketing page only. Does not replace the job-seeker dashboard.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Job Seekers', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Find healthcare work that matches your credentials, availability, and the kind of care you want to give.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-prose">
			<p><?php esc_html_e( 'Create a job-seeker account in the portal, complete your profile, then apply to published openings. You do not apply through this marketing page — the careers portal is the source of live jobs.', 'es-care' ); ?></p>
			<h2><?php esc_html_e( 'How to apply', 'es-care' ); ?></h2>
		</div>
		<ol class="escare-steps">
			<li>
				<h3><?php esc_html_e( '1. Register', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Choose Job Seeker and create your portal account.', 'es-care' ); ?></p>
			</li>
			<li>
				<h3><?php esc_html_e( '2. Complete your profile', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Add contact details, certifications, and a resume when you apply.', 'es-care' ); ?></p>
			</li>
			<li>
				<h3><?php esc_html_e( '3. Browse careers', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Filter published jobs by category, type, and location.', 'es-care' ); ?></p>
			</li>
			<li>
				<h3><?php esc_html_e( '4. Apply', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Submit the application in the portal and track status from your dashboard.', 'es-care' ); ?></p>
			</li>
		</ol>
		<div class="escare-prose" style="margin-top:1.5rem;">
			<p><?php esc_html_e( 'Have licenses, certification cards, and a current resume ready. Additional items such as references or health records may be requested later in the process.', 'es-care' ); ?></p>
		</div>
	</div>
</section>

<div class="escare-cta-band">
	<div class="escare-wrap escare-cta-band-inner">
		<p><?php esc_html_e( 'Open the careers portal to continue.', 'es-care' ); ?></p>
		<div class="escare-split-actions">
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'careers' ) ); ?>"><?php esc_html_e( 'View openings', 'es-care' ); ?></a>
			<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'login' ) ); ?>"><?php esc_html_e( 'Job Seeker Login', 'es-care' ); ?></a>
			<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Job Seeker Registration', 'es-care' ); ?></a>
		</div>
	</div>
</div>
<?php
get_footer();
