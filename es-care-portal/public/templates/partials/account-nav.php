<?php
/**
 * Account navigation.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

$current = '';
$portal  = ESC_Portal_Auth::current_user();

if ( is_page() ) {
	foreach ( ESC_Portal_Helpers::page_slugs() as $slug ) {
		if ( get_the_ID() === ESC_Portal_Helpers::get_page_id( $slug ) ) {
			$current = $slug;
			break;
		}
	}
}
?>
<nav class="esc-nav" aria-label="<?php esc_attr_e( 'Careers portal', 'es-care-portal' ); ?>">
	<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'careers' ) ); ?>"><?php esc_html_e( 'Careers', 'es-care-portal' ); ?></a>
	<?php if ( $portal ) : ?>
		<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'dashboard' ) ); ?>"<?php echo 'dashboard' === $current ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Dashboard', 'es-care-portal' ); ?></a>
		<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'profile' ) ); ?>"<?php echo 'profile' === $current ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Profile', 'es-care-portal' ); ?></a>
		<?php if ( ESC_Portal_Users::is_employer( $portal ) || ESC_Portal_Users::is_admin( $portal ) ) : ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'post-job' ) ); ?>"<?php echo 'post-job' === $current ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Post a job', 'es-care-portal' ); ?></a>
		<?php endif; ?>
		<span class="esc-nav-role"><?php echo esc_html( ESC_Portal_Users::role_label( $portal->role ) ); ?></span>
		<a href="<?php echo esc_url( ESC_Portal_Auth::logout_url() ); ?>"><?php esc_html_e( 'Sign out', 'es-care-portal' ); ?></a>
	<?php else : ?>
		<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'login' ) ); ?>"<?php echo 'login' === $current ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Sign in', 'es-care-portal' ); ?></a>
		<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'register' ) ); ?>"<?php echo 'register' === $current ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Register', 'es-care-portal' ); ?></a>
	<?php endif; ?>
</nav>
