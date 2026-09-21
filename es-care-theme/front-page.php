<?php
/**
 * Front page — editorial layout (distinct from Diligent boxed-hero / triple cards).
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="escare-hero" aria-label="<?php esc_attr_e( 'Introduction', 'es-care' ); ?>">
	<div class="escare-hero-media" aria-hidden="true"></div>
	<div class="escare-hero-copy">
		<p class="escare-hero-brand">E&amp;S Care Services</p>
		<h1 class="escare-hero-title"><?php esc_html_e( 'Staffing that keeps care moving', 'es-care' ); ?></h1>
		<p class="escare-hero-lede"><?php esc_html_e( 'We connect healthcare facilities with screened professionals, and help caregivers find work that fits their skills and schedule.', 'es-care' ); ?></p>
		<div class="escare-hero-actions">
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Apply now', 'es-care' ); ?></a>
			<a class="escare-btn escare-btn--light" href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Request staffing', 'es-care' ); ?></a>
		</div>
	</div>
</section>

<section class="escare-trust" aria-label="<?php esc_attr_e( 'What we provide', 'es-care' ); ?>">
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
	</div>
</section>

<section class="escare-story">
	<div class="escare-wrap escare-story-grid">
		<div class="escare-story-text">
			<p class="escare-eyebrow"><?php esc_html_e( 'Who we are', 'es-care' ); ?></p>
			<h2><?php esc_html_e( 'A staffing partner built around reliable coverage', 'es-care' ); ?></h2>
			<p><?php esc_html_e( 'E&S Care Services places CNAs, nurses, and aides where facilities and home-care programs need them — with respect for the people we serve and the professionals we place.', 'es-care' ); ?></p>
			<p><?php esc_html_e( 'Tell us the role, shift, and setting. We match screened staff for temp, per diem, contract, and ongoing assignments so care can continue without a long recruiting delay.', 'es-care' ); ?></p>
		</div>
		<figure class="escare-story-photo">
			<img src="<?php echo esc_url( escare_asset( 'img/companionship.jpg' ) ); ?>" alt="<?php esc_attr_e( 'Older adults and caregivers spending time together at home', 'es-care' ); ?>" width="1600" height="1067">
		</figure>
	</div>
</section>

<section class="escare-steps-band" aria-label="<?php esc_attr_e( 'How we help', 'es-care' ); ?>">
	<div class="escare-wrap">
		<p class="escare-eyebrow escare-eyebrow--light"><?php esc_html_e( 'How we help', 'es-care' ); ?></p>
		<h2 class="escare-steps-heading"><?php esc_html_e( 'Three simple paths', 'es-care' ); ?></h2>
		<ol class="escare-pathway">
			<li>
				<span class="escare-pathway-num" aria-hidden="true">01</span>
				<h3><?php esc_html_e( 'Share the need', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Facilities request staffing or post jobs after approval. Caregivers register and complete a profile.', 'es-care' ); ?></p>
			</li>
			<li>
				<span class="escare-pathway-num" aria-hidden="true">02</span>
				<h3><?php esc_html_e( 'Match with care', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'We look for credentials, availability, and the right fit — not a volume dump of unvetted names.', 'es-care' ); ?></p>
			</li>
			<li>
				<span class="escare-pathway-num" aria-hidden="true">03</span>
				<h3><?php esc_html_e( 'Keep coverage steady', 'es-care' ); ?></h3>
				<p><?php esc_html_e( 'Facilities get responsive coverage. Caregivers get a clear path to apply and track openings in the portal.', 'es-care' ); ?></p>
			</li>
		</ol>
	</div>
</section>

<section class="escare-path escare-path--employer">
	<div class="escare-wrap escare-path-inner">
		<div>
			<p class="escare-eyebrow escare-eyebrow--light"><?php esc_html_e( 'For employers', 'es-care' ); ?></p>
			<h2><?php esc_html_e( 'Fill shifts without slowing care', 'es-care' ); ?></h2>
			<p><?php esc_html_e( 'Hospitals, nursing homes, assisted living, rehab, and home-care programs can request temp, per diem, contract, or longer-term support — or open an employer portal account to post jobs.', 'es-care' ); ?></p>
			<div class="escare-split-actions">
				<a class="escare-btn escare-btn--light" href="<?php echo esc_url( escare_portal_url( 'login' ) ); ?>"><?php esc_html_e( 'Employer login', 'es-care' ); ?></a>
				<a class="escare-btn escare-btn--ghost-light" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Employer registration', 'es-care' ); ?></a>
				<a class="escare-btn escare-btn--ghost-light" href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Request staffing', 'es-care' ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="escare-path escare-path--seeker">
	<div class="escare-wrap escare-path-inner escare-path-inner--media">
		<figure>
			<img src="<?php echo esc_url( escare_asset( 'img/caregiver-visit.jpg' ) ); ?>" alt="<?php esc_attr_e( 'A caregiver visiting with an older adult', 'es-care' ); ?>" width="1600" height="1067">
		</figure>
		<div>
			<p class="escare-eyebrow"><?php esc_html_e( 'For job seekers', 'es-care' ); ?></p>
			<h2><?php esc_html_e( 'Find work that fits your license and life', 'es-care' ); ?></h2>
			<p><?php esc_html_e( 'CNAs, HHAs, LPNs, RNs, and caregivers can register, browse careers, and apply through the portal — including full-time, part-time, PRN, contract, weekend, night, and live-in roles as they open.', 'es-care' ); ?></p>
			<div class="escare-split-actions">
				<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'login' ) ); ?>"><?php esc_html_e( 'Job seeker login', 'es-care' ); ?></a>
				<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Job seeker registration', 'es-care' ); ?></a>
				<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'careers' ) ); ?>"><?php esc_html_e( 'View openings', 'es-care' ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
