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
		'contact'         => '/contact/',
	);

	$path = isset( $fallbacks[ $slug ] ) ? $fallbacks[ $slug ] : '/';

	return home_url( $path );
}

/**
 * Current ES Care Portal user, or null for guests.
 *
 * @return object|null
 */
function escare_portal_user() {
	if ( class_exists( 'ESC_Portal_Auth' ) && method_exists( 'ESC_Portal_Auth', 'current_user' ) ) {
		$user = ESC_Portal_Auth::current_user();
		return $user ? $user : null;
	}

	return null;
}

/**
 * Portal sign-out URL.
 *
 * @return string
 */
function escare_portal_logout_url() {
	if ( class_exists( 'ESC_Portal_Auth' ) && method_exists( 'ESC_Portal_Auth', 'logout_url' ) ) {
		return ESC_Portal_Auth::logout_url();
	}

	return escare_portal_url( 'login' );
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
	return escare_mod( 'escare_address', "3817 Boulevard Rd SE\nOlympia, WA 98501" );
}

/**
 * Last path segment of the current request.
 *
 * @return string
 */
function escare_request_slug() {
	if ( is_singular( 'page' ) ) {
		$post = get_queried_object();
		if ( $post && ! empty( $post->post_name ) ) {
			return sanitize_title( $post->post_name );
		}
	}

	$pagename = get_query_var( 'pagename' );
	if ( ! $pagename ) {
		$pagename = get_query_var( 'name' );
	}
	if ( $pagename ) {
		$parts = explode( '/', (string) $pagename );
		return sanitize_title( (string) end( $parts ) );
	}

	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return '';
	}

	$uri  = wp_unslash( $_SERVER['REQUEST_URI'] );
	$path = wp_parse_url( $uri, PHP_URL_PATH );
	$path = trim( (string) $path, '/' );
	if ( '' === $path ) {
		return '';
	}

	$parts = explode( '/', $path );
	return sanitize_title( rawurldecode( (string) end( $parts ) ) );
}

/**
 * Why this request is blocked for the signed-in portal role, if at all.
 *
 * @return string employer-on-seeker|seeker-on-employer|
 */
function escare_cross_role_block() {
	$user = escare_portal_user();
	if ( ! $user || ! class_exists( 'ESC_Portal_Users' ) || ESC_Portal_Users::is_admin( $user ) ) {
		return '';
	}

	$slug     = escare_request_slug();
	$seeker   = in_array( $slug, array( 'job-seekers', 'job-seeker', 'apply' ), true );
	$employer = in_array( $slug, array( 'employers', 'employer', 'post-a-job', 'post-job' ), true );

	$pages = get_option( 'escare_theme_pages', array() );
	if ( is_array( $pages ) ) {
		if ( ! empty( $pages['job-seekers'] ) && is_page( absint( $pages['job-seekers'] ) ) ) {
			$seeker = true;
		}
		if ( ! empty( $pages['employers'] ) && is_page( absint( $pages['employers'] ) ) ) {
			$employer = true;
		}
	}

	$view = isset( $_GET['esc_view'] ) ? sanitize_key( wp_unslash( $_GET['esc_view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( class_exists( 'ESC_Portal_Helpers' ) ) {
		$apply_id = ESC_Portal_Helpers::get_page_id( 'apply' );
		$post_id  = ESC_Portal_Helpers::get_page_id( 'post-job' );
		$dash_id  = ESC_Portal_Helpers::get_page_id( 'dashboard' );

		if ( $apply_id && is_page( $apply_id ) ) {
			$seeker = true;
		}
		if ( $post_id && is_page( $post_id ) ) {
			$employer = true;
		}
		if ( $dash_id && is_page( $dash_id ) ) {
			if ( in_array( $view, array( 'apply', 'assessments', 'take', 'results', 'forms' ), true ) ) {
				$seeker = true;
			}
			if ( in_array( $view, array( 'jobs', 'post', 'profile', 'request' ), true ) ) {
				$employer = true;
			}
		}
	}

	if ( ESC_Portal_Users::is_employer( $user ) && $seeker ) {
		return 'employer-on-seeker';
	}

	if ( ESC_Portal_Users::is_seeker( $user ) && $employer ) {
		return 'seeker-on-employer';
	}

	return '';
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

	if ( class_exists( 'ESC_Portal_Helpers' ) && method_exists( 'ESC_Portal_Helpers', 'find_page_id' ) && $id === ESC_Portal_Helpers::find_page_id( 'contact' ) ) {
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

/**
 * Public Code of Conduct page (gated to signed-in dashboard users).
 *
 * @return bool
 */
function escare_is_code_of_conduct_page() {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}

	$id = get_queried_object_id();
	if ( ! $id ) {
		return false;
	}

	$pages = get_option( 'escare_theme_pages', array() );
	if ( is_array( $pages ) && ! empty( $pages['code-of-conduct'] ) && (int) $pages['code-of-conduct'] === $id ) {
		return true;
	}

	$post = get_post( $id );
	if ( ! $post ) {
		return false;
	}

	$slug = strtolower( (string) $post->post_name );
	if ( 'code-of-conduct' === $slug ) {
		return true;
	}

	$title = strtolower( trim( wp_strip_all_tags( (string) $post->post_title ) ) );
	if ( 'code of conduct' === $title ) {
		return true;
	}

	return 'page-templates/template-code-of-conduct.php' === (string) get_page_template_slug( $id );
}
