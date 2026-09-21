<?php
/**
 * Theme setup, menus, and assets.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'escare_setup' );
add_action( 'wp_enqueue_scripts', 'escare_enqueue_assets' );
add_action( 'wp_head', 'escare_output_favicon', 1 );
add_filter( 'get_site_icon_url', 'escare_filter_site_icon_url', 10, 3 );
add_filter( 'body_class', 'escare_body_class' );
add_filter( 'nav_menu_css_class', 'escare_menu_item_classes', 10, 2 );
add_filter( 'nav_menu_link_attributes', 'escare_menu_link_attrs', 10, 2 );
add_filter( 'template_include', 'escare_contact_template', 999 );

/**
 * Theme supports.
 */
function escare_setup() {
	load_theme_textdomain( 'es-care', ESCARE_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 72,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'customize-selective-refresh-widgets' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'es-care' ),
			'footer'  => __( 'Footer menu', 'es-care' ),
		)
	);
}

/**
 * Front-end CSS and JS.
 */
function escare_enqueue_assets() {
	wp_enqueue_style(
		'escare-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Outfit:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'escare-site',
		ESCARE_THEME_URI . '/assets/css/site.css',
		array( 'escare-fonts' ),
		ESCARE_THEME_VERSION
	);

	wp_enqueue_script(
		'escare-nav',
		ESCARE_THEME_URI . '/assets/js/nav.js',
		array(),
		ESCARE_THEME_VERSION,
		true
	);

	if ( escare_is_contact_page() && defined( 'ESC_PORTAL_URL' ) && defined( 'ESC_PORTAL_VERSION' ) ) {
		wp_enqueue_style(
			'esc-portal',
			ESC_PORTAL_URL . 'public/css/portal.css',
			array( 'escare-site' ),
			ESC_PORTAL_VERSION
		);
		if ( class_exists( 'ESC_Portal_Blocks' ) ) {
			wp_add_inline_style( 'esc-portal', ESC_Portal_Blocks::theme_css() );
		}
	}
}

/**
 * Favicon / apple-touch icons — transparent square mark.
 */
function escare_output_favicon() {
	$url = escare_favicon_url();
	$ico = escare_asset( 'img/favicon.ico' );
	?>
	<link rel="icon" href="<?php echo esc_url( $url ); ?>" type="image/webp">
	<link rel="icon" href="<?php echo esc_url( $ico ); ?>" sizes="any">
	<link rel="apple-touch-icon" href="<?php echo esc_url( $url ); ?>">
	<?php
}

/**
 * Prefer the ES Care square mark whenever WordPress asks for a site icon.
 *
 * @param string $url     Icon URL.
 * @param int    $size    Requested size.
 * @param int    $blog_id Blog ID.
 * @return string
 */
function escare_filter_site_icon_url( $url, $size, $blog_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	return escare_favicon_url();
}

/**
 * @param string[] $classes Body classes.
 * @return string[]
 */
function escare_body_class( $classes ) {
	if ( escare_is_portal_surface() ) {
		$classes[] = 'escare-portal-surface';
	}

	if ( escare_is_contact_page() ) {
		$classes[] = 'escare-contact';
	}

	if ( is_front_page() ) {
		$classes[] = 'escare-home';
	}

	return $classes;
}

/**
 * Highlight current marketing page.
 *
 * @param string[] $classes Item classes.
 * @param WP_Post  $item    Menu item.
 * @return string[]
 */
function escare_menu_item_classes( $classes, $item ) {
	if ( 'custom' === $item->type && untrailingslashit( $item->url ) === untrailingslashit( home_url( '/' ) ) && is_front_page() ) {
		$classes[] = 'current-menu-item';
	}

	return $classes;
}

/**
 * @param array   $atts Link attributes.
 * @param WP_Post $item Menu item.
 * @return array
 */
function escare_menu_link_attrs( $atts, $item ) {
	$classes = is_array( $item->classes ) ? $item->classes : array();

	if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
		$atts['aria-current'] = 'page';
	}

	return $atts;
}

/**
 * Default primary menu markup when no menu is assigned.
 */
function escare_fallback_primary_menu() {
	$items = array(
		home_url( '/' )                 => __( 'Home', 'es-care' ),
		escare_page_url( 'about' )      => __( 'About Us', 'es-care' ),
		escare_page_url( 'who-we-staff' ) => __( 'Who We Staff', 'es-care' ),
		escare_page_url( 'employers' )  => __( 'Employers', 'es-care' ),
		escare_page_url( 'job-seekers' ) => __( 'Job Seekers', 'es-care' ),
		escare_portal_url( 'contact' )  => __( 'Contact Us', 'es-care' ),
	);

	echo '<ul class="escare-nav-list">';
	foreach ( $items as $url => $label ) {
		$current = ( untrailingslashit( $url ) === untrailingslashit( home_url( '/' ) ) && is_front_page() )
			|| ( ! is_front_page() && $url && false !== strpos( untrailingslashit( $url ), untrailingslashit( (string) get_permalink() ) ) );
		printf(
			'<li class="%1$s"><a href="%2$s"%3$s>%4$s</a></li>',
			$current ? 'current-menu-item' : '',
			esc_url( $url ),
			$current ? ' aria-current="page"' : '',
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Always use the portal contact form on Contact pages, not leftover Elementor content.
 *
 * @param string $template Template path.
 * @return string
 */
function escare_contact_template( $template ) {
	if ( ! escare_is_contact_page() ) {
		return $template;
	}

	$contact = ESCARE_THEME_DIR . '/page-templates/template-contact.php';
	return is_readable( $contact ) ? $contact : $template;
}
