<?php
/**
 * Portal admin sidebar.
 *
 * @package ESC_Portal
 *
 * @var string $view
 */

defined( 'ABSPATH' ) || exit;

$items = array(
	'home'         => __( 'Overview', 'es-care-portal' ),
	'users'        => __( 'Users', 'es-care-portal' ),
	'jobs'         => __( 'Jobs', 'es-care-portal' ),
	'applications' => __( 'Applications', 'es-care-portal' ),
);
?>
<aside class="esc-side" aria-label="<?php esc_attr_e( 'Admin menu', 'es-care-portal' ); ?>">
	<p class="esc-side-kicker"><?php esc_html_e( 'Portal Admin', 'es-care-portal' ); ?></p>
	<nav class="esc-side-nav">
		<?php foreach ( $items as $key => $label ) : ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( $key ) ); ?>"<?php echo $view === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( ESC_Portal_Auth::logout_url() ); ?>"><?php esc_html_e( 'Logout', 'es-care-portal' ); ?></a>
	</nav>
</aside>
