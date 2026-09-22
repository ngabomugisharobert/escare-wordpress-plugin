<?php
/**
 * Job-seeker dashboard secondary menu.
 *
 * @package ESC_Portal
 *
 * @var string $view
 */

defined( 'ABSPATH' ) || exit;

$items = array(
	'home'        => __( 'My Dashboard', 'es-care-portal' ),
	'apply'       => __( 'Job Application Form', 'es-care-portal' ),
	'assessments' => __( 'Assessment Tests', 'es-care-portal' ),
	'results'     => __( 'Assessment Results', 'es-care-portal' ),
	'password'    => __( 'Change Password', 'es-care-portal' ),
);

$active = $view;

if ( 'take' === $active ) {
	$active = 'assessments';
}

$current_label = isset( $items[ $active ] ) ? $items[ $active ] : __( 'Menu', 'es-care-portal' );

if ( 'request' === $view ) {
	$current_label = __( 'Service Request', 'es-care-portal' );
}

if ( 'conduct' === $view ) {
	$current_label = __( 'Code of Conduct', 'es-care-portal' );
}
?>
<header class="esc-side esc-subnav">
	<p class="esc-side-kicker esc-subnav-kicker esc-subnav-kicker--bar"><?php esc_html_e( 'For Job Seekers / Candidate', 'es-care-portal' ); ?></p>
	<button
		type="button"
		class="esc-subnav-toggle"
		aria-expanded="false"
		aria-controls="esc-dash-nav-seeker"
		aria-label="<?php echo esc_attr( sprintf( __( 'Dashboard menu, %s', 'es-care-portal' ), $current_label ) ); ?>"
	>
		<span class="esc-subnav-toggle-copy">
			<span class="esc-side-kicker esc-subnav-kicker"><?php esc_html_e( 'Job Seeker', 'es-care-portal' ); ?></span>
			<span class="esc-subnav-current"><?php echo esc_html( $current_label ); ?></span>
		</span>
		<span class="esc-subnav-chevron" aria-hidden="true"></span>
	</button>
	<nav id="esc-dash-nav-seeker" class="esc-side-nav esc-subnav-links" aria-label="<?php esc_attr_e( 'Job seeker menu', 'es-care-portal' ); ?>">
		<?php foreach ( $items as $key => $label ) : ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( $key ) ); ?>"<?php echo $active === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'profile' ) ); ?>"><?php esc_html_e( 'My Profile', 'es-care-portal' ); ?></a>
		<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'request' ) ); ?>"<?php echo 'request' === $view ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Service Request', 'es-care-portal' ); ?></a>
		<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'conduct' ) ); ?>"<?php echo 'conduct' === $view ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Code of Conduct', 'es-care-portal' ); ?></a>
		<a class="esc-subnav-logout" href="<?php echo esc_url( ESC_Portal_Auth::logout_url() ); ?>"><?php esc_html_e( 'Logout', 'es-care-portal' ); ?></a>
	</nav>
</header>
