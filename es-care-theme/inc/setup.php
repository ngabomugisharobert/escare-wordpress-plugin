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
add_action( 'wp_head', 'escare_preload_critical_assets', 2 );
add_filter( 'get_site_icon_url', 'escare_filter_site_icon_url', 10, 3 );
add_filter( 'body_class', 'escare_body_class' );
add_filter( 'nav_menu_css_class', 'escare_menu_item_classes', 10, 2 );
add_filter( 'nav_menu_link_attributes', 'escare_menu_link_attrs', 10, 2 );
add_filter( 'wp_nav_menu_objects', 'escare_rewrite_contact_menu_urls', 8, 2 );
add_filter( 'wp_nav_menu_objects', 'escare_hide_code_of_conduct_menu_items', 9, 2 );
add_filter( 'wp_nav_menu_objects', 'escare_filter_menu_by_portal_role', 10, 2 );
add_filter( 'the_title', 'escare_filter_dashboard_page_title', 10, 2 );
add_filter( 'document_title_parts', 'escare_filter_dashboard_document_title' );
add_filter( 'template_include', 'escare_contact_template', 999 );
add_action( 'template_redirect', 'escare_block_cross_role_pages', 1 );

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
			'height'      => 90,
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
		'escare-site',
		ESCARE_THEME_URI . '/assets/css/site.css',
		array(),
		ESCARE_THEME_VERSION
	);

	wp_enqueue_script(
		'escare-nav',
		ESCARE_THEME_URI . '/assets/js/nav.js',
		array(),
		ESCARE_THEME_VERSION,
		true
	);

	if ( is_front_page() ) {
		wp_enqueue_script(
			'escare-hero',
			ESCARE_THEME_URI . '/assets/js/hero.js',
			array(),
			ESCARE_THEME_VERSION,
			true
		);
	}

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
 * Preload local body font and the first hero photo only.
 */
function escare_preload_critical_assets() {
	$outfit = escare_asset( 'fonts/outfit-latin.woff2' );
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( $outfit )
	);

	if ( is_front_page() ) {
		printf(
			'<link rel="preload" href="%s" as="image" fetchpriority="high">' . "\n",
			esc_url( escare_asset( 'img/hero-slide-1.jpg' ) )
		);
	}
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
 * Dashboard page ID from the portal plugin.
 *
 * @return int
 */
function escare_dashboard_page_id() {
	if ( ! class_exists( 'ESC_Portal_Helpers' ) || ! method_exists( 'ESC_Portal_Helpers', 'get_page_id' ) ) {
		return 0;
	}

	return (int) ESC_Portal_Helpers::get_page_id( 'dashboard' );
}

/**
 * Replace "Applicant Dashboard" / "Dashboard" with "{Name} Dashboard" in the page heading.
 *
 * @param string $title   Title.
 * @param int    $post_id Post ID.
 * @return string
 */
function escare_filter_dashboard_page_title( $title, $post_id = 0 ) {
	if ( is_admin() || ! in_the_loop() ) {
		return $title;
	}

	$dash_id = escare_dashboard_page_id();
	if ( ! $dash_id || (int) $post_id !== $dash_id ) {
		return $title;
	}

	if ( ! class_exists( 'ESC_Portal_Helpers' ) || ! method_exists( 'ESC_Portal_Helpers', 'dashboard_heading' ) ) {
		return $title;
	}

	return ESC_Portal_Helpers::dashboard_heading( escare_portal_user() );
}

/**
 * Browser tab title for the signed-in dashboard.
 *
 * @param array $parts Title parts.
 * @return array
 */
function escare_filter_dashboard_document_title( $parts ) {
	$dash_id = escare_dashboard_page_id();
	if ( ! $dash_id || ! is_page( $dash_id ) ) {
		return $parts;
	}

	if ( ! class_exists( 'ESC_Portal_Helpers' ) || ! method_exists( 'ESC_Portal_Helpers', 'dashboard_heading' ) ) {
		return $parts;
	}

	$parts['title'] = ESC_Portal_Helpers::dashboard_heading( escare_portal_user() );

	return $parts;
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
 * Header Contact Us is a stored custom URL. Point it at the live /contact/ page
 * even when the menu still has ?page_id=183 or /contact-us/.
 *
 * @param array $items Menu items.
 * @param mixed $args  wp_nav_menu args.
 * @return array
 */
function escare_rewrite_contact_menu_urls( $items, $args ) {
	if ( empty( $items ) || ! is_array( $items ) ) {
		return $items;
	}

	$url = escare_portal_url( 'contact' );
	if ( ! $url ) {
		return $items;
	}

	foreach ( $items as $item ) {
		if ( escare_menu_item_is_contact( $item ) ) {
			$item->url = $url;
		}
	}

	return $items;
}

/**
 * @param object $item Menu item.
 * @return bool
 */
function escare_menu_item_is_contact( $item ) {
	$title = strtolower( trim( wp_strip_all_tags( (string) $item->title ) ) );
	if ( in_array( $title, array( 'contact', 'contact us' ), true ) ) {
		return true;
	}

	$path = strtolower( trim( (string) wp_parse_url( (string) $item->url, PHP_URL_PATH ), '/' ) );
	if ( in_array( $path, array( 'contact', 'contact-us', 'contactus' ), true ) ) {
		return true;
	}

	$query = (string) wp_parse_url( (string) $item->url, PHP_URL_QUERY );
	if ( $query && false !== strpos( $query, 'page_id=' ) ) {
		parse_str( $query, $vars );
		$page_id = isset( $vars['page_id'] ) ? absint( $vars['page_id'] ) : 0;
		if ( $page_id ) {
			$post = get_post( $page_id );
			if ( $post ) {
				$slug  = strtolower( (string) $post->post_name );
				$ptitle = strtolower( trim( wp_strip_all_tags( (string) $post->post_title ) ) );
				return in_array( $slug, array( 'contact', 'contact-us', 'contactus' ), true )
					|| in_array( $ptitle, array( 'contact', 'contact us' ), true );
			}
		}
	}

	return false;
}

/**
 * Hide Employers for job seekers and Job Seekers for employers.
 * Portal admin keeps both. WordPress login alone does not change the menu.
 *
 * @param array  $items Menu items.
 * @param object $args  wp_nav_menu args.
 * @return array
 */
function escare_filter_menu_by_portal_role( $items, $args ) {
	if ( empty( $items ) || ! is_array( $items ) ) {
		return $items;
	}

	$location = '';
	if ( is_object( $args ) && isset( $args->theme_location ) ) {
		$location = (string) $args->theme_location;
	} elseif ( is_array( $args ) && ! empty( $args['theme_location'] ) ) {
		$location = (string) $args['theme_location'];
	}

	if ( $location && 'primary' !== $location ) {
		return $items;
	}

	$user = escare_portal_user();
	if ( ! $user || ! class_exists( 'ESC_Portal_Users' ) ) {
		return $items;
	}

	$hide_employers = ESC_Portal_Users::is_seeker( $user );
	$hide_seekers   = ESC_Portal_Users::is_employer( $user );

	if ( ! $hide_employers && ! $hide_seekers ) {
		return $items;
	}

	$kept = array();
	foreach ( $items as $item ) {
		if ( $hide_employers && escare_menu_item_is_employers( $item ) ) {
			continue;
		}
		if ( $hide_seekers && escare_menu_item_is_job_seekers( $item ) ) {
			continue;
		}
		$kept[] = $item;
	}

	return $kept;
}

/**
 * Code of Conduct lives in the signed-in dashboard, not public chrome.
 *
 * @param array $items Menu items.
 * @param mixed $args  wp_nav_menu args.
 * @return array
 */
function escare_hide_code_of_conduct_menu_items( $items, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( empty( $items ) || ! is_array( $items ) ) {
		return $items;
	}

	$kept = array();
	foreach ( $items as $item ) {
		if ( escare_menu_item_is_code_of_conduct( $item ) ) {
			continue;
		}
		$kept[] = $item;
	}

	return $kept;
}

/**
 * @param object $item Menu item.
 * @return bool
 */
function escare_menu_item_is_code_of_conduct( $item ) {
	$pages = get_option( 'escare_theme_pages', array() );
	$id    = ( is_array( $pages ) && ! empty( $pages['code-of-conduct'] ) ) ? (int) $pages['code-of-conduct'] : 0;

	if ( $id && (int) $item->object_id === $id ) {
		return true;
	}

	$title = strtolower( trim( wp_strip_all_tags( (string) $item->title ) ) );
	$path  = strtolower( trim( (string) wp_parse_url( (string) $item->url, PHP_URL_PATH ), '/' ) );

	return 'code of conduct' === $title || 'code-of-conduct' === $path;
}

/**
 * @param object $item Menu item.
 * @return bool
 */
function escare_menu_item_is_employers( $item ) {
	$pages = get_option( 'escare_theme_pages', array() );
	$id    = ( is_array( $pages ) && ! empty( $pages['employers'] ) ) ? (int) $pages['employers'] : 0;

	if ( $id && (int) $item->object_id === $id ) {
		return true;
	}

	$title = strtolower( trim( wp_strip_all_tags( (string) $item->title ) ) );
	$path  = strtolower( trim( (string) wp_parse_url( (string) $item->url, PHP_URL_PATH ), '/' ) );

	return in_array( $title, array( 'employers', 'employer' ), true )
		|| in_array( $path, array( 'employers', 'employer' ), true );
}

/**
 * @param object $item Menu item.
 * @return bool
 */
function escare_menu_item_is_job_seekers( $item ) {
	$pages = get_option( 'escare_theme_pages', array() );
	$id    = ( is_array( $pages ) && ! empty( $pages['job-seekers'] ) ) ? (int) $pages['job-seekers'] : 0;

	if ( $id && (int) $item->object_id === $id ) {
		return true;
	}

	$title = strtolower( trim( wp_strip_all_tags( (string) $item->title ) ) );
	$path  = strtolower( trim( (string) wp_parse_url( (string) $item->url, PHP_URL_PATH ), '/' ) );

	return in_array( $title, array( 'job seekers', 'job seeker' ), true )
		|| in_array( $path, array( 'job-seekers', 'job-seeker' ), true );
}

/**
 * Default primary menu markup when no menu is assigned.
 */
function escare_fallback_primary_menu() {
	$user         = escare_portal_user();
	$is_seeker    = $user && class_exists( 'ESC_Portal_Users' ) && ESC_Portal_Users::is_seeker( $user );
	$is_employer  = $user && class_exists( 'ESC_Portal_Users' ) && ESC_Portal_Users::is_employer( $user );

	$items = array(
		home_url( '/' )                   => __( 'Home', 'es-care' ),
		escare_page_url( 'about' )        => __( 'About Us', 'es-care' ),
		escare_page_url( 'who-we-staff' ) => __( 'Who We Staff', 'es-care' ),
	);

	if ( ! $is_seeker ) {
		$items[ escare_page_url( 'employers' ) ] = __( 'Employers', 'es-care' );
	}

	if ( ! $is_employer ) {
		$items[ escare_page_url( 'job-seekers' ) ] = __( 'Job Seekers', 'es-care' );
	}

	$items[ escare_portal_url( 'contact' ) ] = __( 'Contact Us', 'es-care' );

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
 * Employers cannot use job-seeker pages, and job seekers cannot use employer pages.
 */
function escare_block_cross_role_pages() {
	$block = escare_cross_role_block();
	if ( ! $block ) {
		return;
	}

	status_header( 200 );
	nocache_headers();
	get_header();
	include ESCARE_THEME_DIR . '/page-templates/partial-role-blocked.php';
	get_footer();
	exit;
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
