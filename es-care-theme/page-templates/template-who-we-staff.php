<?php
/**
 * Template Name: Who We Staff
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Who We Staff', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Quality over volume. We place experienced, dedicated professionals — not a revolving door of unvetted names.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-media escare-media--reverse" style="margin-bottom:2rem;">
			<img src="<?php echo esc_url( escare_asset( 'img/companionship.jpg' ) ); ?>" alt="<?php esc_attr_e( 'Companionship and group activities as part of home and facility care', 'es-care' ); ?>" width="1600" height="1067" loading="lazy" decoding="async">
			<p class="escare-prose" style="margin:0;"><?php esc_html_e( 'From CNAs and HHAs to LPNs and RNs, we staff the people who keep residents and clients safe, engaged, and treated with dignity.', 'es-care' ); ?></p>
		</div>
		<ul class="escare-roles">
			<li><strong><?php esc_html_e( 'Certified Nursing Assistant (CNA)', 'es-care' ); ?></strong><?php esc_html_e( 'Hands-on support for daily living, mobility, and resident dignity.', 'es-care' ); ?></li>
			<li><strong><?php esc_html_e( 'Licensed Practical Nurse (LPN)', 'es-care' ); ?></strong><?php esc_html_e( 'Clinical coverage for meds, monitoring, and care-plan follow-through.', 'es-care' ); ?></li>
			<li><strong><?php esc_html_e( 'Registered Nurse (RN)', 'es-care' ); ?></strong><?php esc_html_e( 'Assessment, supervision, and skilled nursing where your census requires it.', 'es-care' ); ?></li>
			<li><strong><?php esc_html_e( 'Home Health Aide (HHA)', 'es-care' ); ?></strong><?php esc_html_e( 'In-home support that keeps clients safe and comfortable.', 'es-care' ); ?></li>
			<li><strong><?php esc_html_e( 'Companion / personal care', 'es-care' ); ?></strong><?php esc_html_e( 'Non-medical caregiving for companionship, meals, and household help.', 'es-care' ); ?></li>
			<li><strong><?php esc_html_e( 'CPR / BLS ready', 'es-care' ); ?></strong><?php esc_html_e( 'We look for current emergency-response training alongside the primary credential.', 'es-care' ); ?></li>
		</ul>
		<p class="escare-prose" style="margin-top:1.5rem;"><?php esc_html_e( 'We are equipped to respond quickly when your staffing needs change. If you do not see a specialty listed, send a request and we will tell you whether we can fill it.', 'es-care' ); ?></p>
	</div>
</section>

<div class="escare-cta-band">
	<div class="escare-wrap escare-cta-band-inner">
		<p><?php esc_html_e( 'Looking for one of these roles?', 'es-care' ); ?></p>
		<div class="escare-split-actions">
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'careers' ) ); ?>"><?php esc_html_e( 'View openings', 'es-care' ); ?></a>
			<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Request Staffing', 'es-care' ); ?></a>
		</div>
	</div>
</div>
<?php
get_footer();
