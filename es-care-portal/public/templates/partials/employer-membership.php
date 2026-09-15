<?php
/**
 * Employer membership info.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Membership', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Partner with ES Care Services to reach qualified care professionals. Tell us about your staffing needs and we will help you choose the right plan.', 'es-care-portal' ); ?></p>

	<section class="esc-card">
		<ul class="esc-membership-list">
			<li><?php esc_html_e( 'Post open positions to the careers portal', 'es-care-portal' ); ?></li>
			<li><?php esc_html_e( 'Review applications and candidate details', 'es-care-portal' ); ?></li>
			<li><?php esc_html_e( 'Request recruiting support from our team', 'es-care-portal' ); ?></li>
		</ul>
		<p class="esc-actions">
			<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'request' ) ); ?>"><?php esc_html_e( 'Request membership info', 'es-care-portal' ); ?></a>
			<a class="esc-button esc-button--ghost" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'post' ) ); ?>"><?php esc_html_e( 'Post a job', 'es-care-portal' ); ?></a>
		</p>
	</section>
</section>
