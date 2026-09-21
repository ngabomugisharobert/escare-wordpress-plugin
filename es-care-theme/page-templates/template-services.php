<?php
/**
 * Template Name: Services
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Services', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Staffing support for facilities and home-care programs, from last-minute coverage to planned assignments.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<ul class="escare-trust-list">
			<li>
				<strong><?php esc_html_e( 'On-demand solutions', 'es-care' ); ?></strong>
				<span><?php esc_html_e( 'Temp, per diem, and last-minute fill-ins when census or call-offs change.', 'es-care' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( '24/7 shift coverage', 'es-care' ); ?></strong>
				<span><?php esc_html_e( 'Nights, weekends, and holidays so care can keep moving.', 'es-care' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Fully vetted RNs, LPNs & CNAs', 'es-care' ); ?></strong>
				<span><?php esc_html_e( 'Screened professionals placed for facilities and home-care programs.', 'es-care' ); ?></span>
			</li>
		</ul>
		<div class="escare-values">
			<article>
				<h3><?php esc_html_e( 'Facility staffing', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Fill open shifts in hospitals, nursing homes, assisted living, and rehab with CNAs, LPNs, RNs, and aides who understand clinical settings.', 'es-care' ); ?></p>
			</article>
			<article>
				<h3><?php esc_html_e( 'Home-care coverage', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Support agencies and families who need reliable in-home caregivers, including companion care and live-in arrangements when available.', 'es-care' ); ?></p>
			</article>
			<article>
				<h3><?php esc_html_e( 'Per diem and PRN fill-ins', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'When census spikes or call-offs hit, we help you cover nights, weekends, and short-notice needs without a full-time hire.', 'es-care' ); ?></p>
			</article>
			<article>
				<h3><?php esc_html_e( 'Contract and ongoing placements', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Need someone for a defined contract or a longer assignment? Tell us the schedule and credential requirements and we will work the request.', 'es-care' ); ?></p>
			</article>
		</div>
	</div>
</section>

<div class="escare-cta-band">
	<div class="escare-wrap escare-cta-band-inner">
		<p><?php esc_html_e( 'Start with a staffing request or an employer account.', 'es-care' ); ?></p>
		<div class="escare-split-actions">
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Request Staffing', 'es-care' ); ?></a>
			<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Employer Registration', 'es-care' ); ?></a>
		</div>
	</div>
</div>
<?php
get_footer();
