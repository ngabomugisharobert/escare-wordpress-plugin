<?php
/**
 * Template Name: About Us
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'About Us', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'E&S Care Service LLC exists to keep quality care staffed — matching facilities with professionals who show up prepared, and helping caregivers find work they can be proud of.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-media">
			<img src="<?php echo esc_url( escare_asset( 'img/caregiver-visit.jpg' ) ); ?>" alt="<?php esc_attr_e( 'A caregiver in scrubs talking with an older adult during a visit', 'es-care' ); ?>" width="1600" height="1067">
			<div class="escare-prose">
		<h2><?php esc_html_e( 'Mission', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'To provide trusted healthcare staffing that protects continuity of care. We recruit people who are qualified, compassionate, and ready to work, and we place them where they can make a difference.', 'es-care' ); ?></p>
		<h2><?php esc_html_e( 'Who we serve', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'We support hospitals, nursing homes, assisted living communities, rehabilitation programs, and home-care settings. Placement types include full-time, part-time, PRN, contract, and live-in coverage.', 'es-care' ); ?></p>
		<h2><?php esc_html_e( 'How our staff stand out', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'We look for more than a credential. The professionals we place are expected to communicate clearly, treat residents and patients with dignity, and represent your facility with a calm, professional presence.', 'es-care' ); ?></p>
		<p class="escare-note"><?php esc_html_e( 'Licenses, years in business, and service-area details will be published here once the owner confirms them.', 'es-care' ); ?></p>
			</div>
		</div>
	</div>
</section>

<div class="escare-cta-band">
	<div class="escare-wrap escare-cta-band-inner">
		<p><?php esc_html_e( 'Need staff, or ready to apply?', 'es-care' ); ?></p>
		<div class="escare-split-actions">
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Request Staffing', 'es-care' ); ?></a>
			<?php
			$user = escare_portal_user();
			if ( $user ) :
				?>
				<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'dashboard' ) ); ?>"><?php esc_html_e( 'Go to dashboard', 'es-care' ); ?></a>
			<?php else : ?>
				<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Apply Now', 'es-care' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
