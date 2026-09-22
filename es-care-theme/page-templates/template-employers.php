<?php
/**
 * Template Name: Employers
 *
 * Marketing page only. Does not replace the employer dashboard.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Employers', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Staffing should not stall care. E&S Care Services helps facilities request coverage, review candidates, and keep shifts filled.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section">
	<div class="escare-wrap">
		<div class="escare-prose">
		<p><?php esc_html_e( 'Share the role, credential, dates, and setting. You can send a staffing request without an account, or register as an employer to post jobs in the portal after email verification and administrator approval.', 'es-care' ); ?></p>
		<h2><?php esc_html_e( 'What to include in a request', 'es-care' ); ?></h2>
		<ul>
			<li><?php esc_html_e( 'Facility name and a contact who can confirm the shift', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Role needed (CNA, LPN, RN, HHA, or other)', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Dates, times, and whether the need is temp, per diem, contract, or ongoing', 'es-care' ); ?></li>
			<li><?php esc_html_e( 'Location and any unit or skill notes', 'es-care' ); ?></li>
		</ul>
		<h2><?php esc_html_e( 'How we screen', 'es-care' ); ?></h2>
		<p><?php esc_html_e( 'Job posts from new employer accounts stay pending until staff review them. Candidates apply through the portal with profile and application details so you are not starting from a blank inbox.', 'es-care' ); ?></p>
		<p class="escare-note"><?php esc_html_e( 'Typical fill times and any service guarantees will be added once the owner confirms them.', 'es-care' ); ?></p>
		</div>
	</div>
</section>

<div class="escare-cta-band">
	<div class="escare-wrap escare-cta-band-inner">
		<p><?php esc_html_e( 'Request coverage or open the employer portal.', 'es-care' ); ?></p>
		<div class="escare-split-actions">
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Request Staffing', 'es-care' ); ?></a>
			<?php
			$user = escare_portal_user();
			if ( $user ) :
				?>
				<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'dashboard' ) ); ?>"><?php esc_html_e( 'Go to dashboard', 'es-care' ); ?></a>
			<?php else : ?>
				<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'login' ) ); ?>"><?php esc_html_e( 'Employer Login', 'es-care' ); ?></a>
				<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Employer Registration', 'es-care' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
