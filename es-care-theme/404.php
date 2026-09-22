<?php
/**
 * 404 — or a signed-in role hitting the other portal path.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();

$block = escare_cross_role_block();
if ( $block ) {
	include ESCARE_THEME_DIR . '/page-templates/partial-role-blocked.php';
	get_footer();
	return;
}
?>
<div class="escare-empty">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Page not found', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'The page you requested is not available. Return home or open the careers portal.', 'es-care' ); ?></p>
		<p>
			<a class="escare-btn escare-btn--solid" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'es-care' ); ?></a>
			<a class="escare-btn escare-btn--ghost" href="<?php echo esc_url( escare_portal_url( 'careers' ) ); ?>"><?php esc_html_e( 'Careers', 'es-care' ); ?></a>
		</p>
	</div>
</div>
<?php
get_footer();
