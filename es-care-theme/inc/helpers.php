<?php
/**
 * Theme helpers and portal URL wrappers.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme asset URL.
 *
 * @param string $relative Path under assets/.
 * @return string
 */
function escare_asset( $relative ) {
	return ESCARE_THEME_URI . '/assets/' . ltrim( $relative, '/' );
}

/**
 * Brand logo URL — transparent WebP (cleared from Media Library upload).
 * Source: https://escareservices.com/wp-content/uploads/2026/09/escarelogo.webp
 *
 * @return string
 */
function escare_logo_url() {
	return escare_asset( 'img/escarelogo.webp' );
}

/**
 * Square brand mark for favicon / site icon — transparent WebP.
 * Source: https://escareservices.com/wp-content/uploads/2026/09/escarelogosquare.webp
 *
 * @return string
 */
function escare_favicon_url() {
	return escare_asset( 'img/escarelogosquare.webp' );
}

/**
 * Portal page URL. Uses the ES Care Portal plugin when it is active.
 *
 * @param string $slug Portal page key.
 * @return string
 */
function escare_portal_url( $slug ) {
	if ( class_exists( 'ESC_Portal_Helpers' ) && method_exists( 'ESC_Portal_Helpers', 'get_page_url' ) ) {
		return ESC_Portal_Helpers::get_page_url( $slug );
	}

	$fallbacks = array(
		'register'        => '/register/',
		'login'           => '/sign-in/',
		'dashboard'       => '/portal-dashboard/',
		'profile'         => '/portal-profile/',
		'careers'         => '/careers/',
		'apply'           => '/apply/',
		'post-job'        => '/post-a-job/',
		'lost-password'   => '/lost-password/',
		'reset-password'  => '/reset-password/',
		'contact'         => '/contact-us/',
	);

	$path = isset( $fallbacks[ $slug ] ) ? $fallbacks[ $slug ] : '/';

	return home_url( $path );
}

/**
 * Theme-created marketing page URL.
 *
 * @param string $key Page key stored in escare_theme_pages.
 * @return string
 */
function escare_page_url( $key ) {
	$pages = get_option( 'escare_theme_pages', array() );

	if ( is_array( $pages ) && ! empty( $pages[ $key ] ) ) {
		$url = get_permalink( absint( $pages[ $key ] ) );
		if ( $url ) {
			return $url;
		}
	}

	$slugs = array(
		'about'             => '/about-us/',
		'who-we-staff'      => '/who-we-staff/',
		'services'          => '/services/',
		'core-values'       => '/core-values/',
		'employers'         => '/employers/',
		'job-seekers'       => '/job-seekers/',
		'privacy'           => '/privacy-policy/',
		'code-of-conduct'   => '/code-of-conduct/',
		'equal-opportunity' => '/equal-opportunity/',
	);

	return home_url( isset( $slugs[ $key ] ) ? $slugs[ $key ] : '/' );
}

/**
 * @param string $mod     Theme mod key.
 * @param string $default Default.
 * @return string
 */
function escare_mod( $mod, $default = '' ) {
	$value = get_theme_mod( $mod, $default );
	return is_string( $value ) ? trim( $value ) : $default;
}

/**
 * Public email.
 *
 * @return string
 */
function escare_email() {
	$email = escare_mod( 'escare_email', 'info@escareservices.com' );
	return $email ? $email : 'info@escareservices.com';
}

/**
 * Public phone.
 *
 * @return string
 */
function escare_phone() {
	return escare_mod( 'escare_phone', '360-742-8095' );
}

/**
 * @return string
 */
function escare_hours() {
	return escare_mod( 'escare_hours', '24/7 shift coverage' );
}

/**
 * Public address.
 *
 * @return string
 */
function escare_address() {
	return escare_mod( 'escare_address', "3917 Boulevard Rd SE\nOlympia, WA 98501" );
}

/**
 * Washington UBI number.
 *
 * @return string
 */
function escare_ubi() {
	return escare_mod( 'escare_ubi', '605-397-045' );
}

/**
 * Washington nursing pool reference.
 *
 * @return string
 */
function escare_pool_ref() {
	return escare_mod( 'escare_pool_ref', 'NPOL.NR.70152565' );
}

/**
 * Whether the current request is a portal utility page.
 *
 * @return bool
 */
function escare_is_portal_surface() {
	if ( ! class_exists( 'ESC_Portal_Helpers' ) ) {
		return false;
	}

	foreach ( ESC_Portal_Helpers::page_slugs() as $slug ) {
		$id = ESC_Portal_Helpers::get_page_id( $slug );
		if ( $id && is_page( $id ) ) {
			return true;
		}
	}

	return is_singular( 'esc_job' ) || is_post_type_archive( 'esc_job' ) || is_tax( 'esc_job_category' );
}

/**
 * Contact Us page, including leftover PointLab/Elementor "Contact" pages.
 *
 * @return bool
 */
function escare_is_contact_page() {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}

	$id = get_queried_object_id();
	if ( ! $id ) {
		return false;
	}

	if ( class_exists( 'ESC_Portal_Helpers' ) && $id === ESC_Portal_Helpers::get_page_id( 'contact' ) ) {
		return true;
	}

	$post = get_post( $id );
	if ( ! $post ) {
		return false;
	}

	$slug = strtolower( (string) $post->post_name );
	if ( in_array( $slug, array( 'contact', 'contact-us', 'contactus' ), true ) ) {
		return true;
	}

	$title = strtolower( trim( wp_strip_all_tags( (string) $post->post_title ) ) );
	if ( in_array( $title, array( 'contact', 'contact us' ), true ) ) {
		return true;
	}

	return has_shortcode( (string) $post->post_content, 'esc_contact' );
}
