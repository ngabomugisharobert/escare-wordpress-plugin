<?php
/**
 * Create marketing pages and menus on theme activation.
 * Does not create portal pages (register, sign-in, dashboard, careers, contact).
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'escare_on_activate' );
add_action( 'admin_init', 'escare_maybe_seed' );

/**
 * Drop a leftover PointLab custom logo so the E&S Care mark shows.
 */
function escare_replace_pointlab_logo() {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( ! $logo_id ) {
		return;
	}

	$post = get_post( $logo_id );
	if ( ! $post ) {
		remove_theme_mod( 'custom_logo' );
		return;
	}

	$haystack = strtolower(
		(string) $post->post_title . ' ' .
		(string) $post->post_name . ' ' .
		(string) basename( (string) get_attached_file( $logo_id ) ) . ' ' .
		(string) get_post_meta( $logo_id, '_wp_attachment_image_alt', true )
	);

	if ( false !== strpos( $haystack, 'pointlab' ) || false !== strpos( $haystack, 'point-lab' ) ) {
		remove_theme_mod( 'custom_logo' );
	}
}

/**
 * Seed marketing pages, front page, and menus once.
 */
function escare_on_activate() {
	escare_create_marketing_pages();
	escare_assign_contact_template();
	escare_setup_menus();
	escare_replace_pointlab_logo();
	escare_seed_contact_defaults();
	update_option( 'escare_theme_seeded', ESCARE_THEME_VERSION, false );
}

/**
 * Fill empty or leftover placeholder contact fields with confirmed business details.
 */
function escare_seed_contact_defaults() {
	$defaults = array(
		'escare_phone'    => '360-742-8095',
		'escare_hours'    => '24/7 shift coverage',
		'escare_address'  => "3917 Boulevard Rd SE\nOlympia, WA 98501",
		'escare_ubi'      => '605-397-045',
		'escare_pool_ref' => 'NPOL.NR.70152565',
	);

	$placeholders = array(
		'Office hours to be confirmed',
		'Service area to be confirmed',
	);

	foreach ( $defaults as $mod => $value ) {
		$current = get_theme_mod( $mod, '' );
		if ( '' === trim( (string) $current ) || in_array( $current, $placeholders, true ) ) {
			set_theme_mod( $mod, $value );
		}
	}
}

/**
 * Re-run seed after a theme update if pages were never created.
 */
function escare_maybe_seed() {
	if ( get_option( 'escare_theme_seeded' ) === ESCARE_THEME_VERSION ) {
		return;
	}

	escare_on_activate();
}

/**
 * Insert missing marketing / legal pages.
 */
function escare_create_marketing_pages() {
	$stored = get_option( 'escare_theme_pages', array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$pages = array(
		'home'              => array(
			'title'    => __( 'Home', 'es-care' ),
			'slug'     => 'home',
			'template' => '',
		),
		'about'             => array(
			'title'    => __( 'About Us', 'es-care' ),
			'slug'     => 'about-us',
			'template' => 'page-templates/template-about.php',
		),
		'who-we-staff'      => array(
			'title'    => __( 'Who We Staff', 'es-care' ),
			'slug'     => 'who-we-staff',
			'template' => 'page-templates/template-who-we-staff.php',
		),
		'services'          => array(
			'title'    => __( 'Services', 'es-care' ),
			'slug'     => 'services',
			'template' => 'page-templates/template-services.php',
		),
		'core-values'       => array(
			'title'    => __( 'Core Values', 'es-care' ),
			'slug'     => 'core-values',
			'template' => 'page-templates/template-core-values.php',
		),
		'employers'         => array(
			'title'    => __( 'Employers', 'es-care' ),
			'slug'     => 'employers',
			'template' => 'page-templates/template-employers.php',
		),
		'job-seekers'       => array(
			'title'    => __( 'Job Seekers', 'es-care' ),
			'slug'     => 'job-seekers',
			'template' => 'page-templates/template-job-seekers.php',
		),
		'privacy'           => array(
			'title'    => __( 'Privacy Policy', 'es-care' ),
			'slug'     => 'privacy-policy',
			'template' => 'page-templates/template-privacy.php',
		),
		'code-of-conduct'   => array(
			'title'    => __( 'Code of Conduct', 'es-care' ),
			'slug'     => 'code-of-conduct',
			'template' => 'page-templates/template-code-of-conduct.php',
		),
		'equal-opportunity' => array(
			'title'    => __( 'Equal Opportunity', 'es-care' ),
			'slug'     => 'equal-opportunity',
			'template' => 'page-templates/template-equal-opportunity.php',
		),
	);

	foreach ( $pages as $key => $config ) {
		$existing_id = isset( $stored[ $key ] ) ? absint( $stored[ $key ] ) : 0;

		if ( $existing_id && get_post( $existing_id ) ) {
			if ( ! empty( $config['template'] ) ) {
				update_post_meta( $existing_id, '_wp_page_template', $config['template'] );
			}
			continue;
		}

		$found = get_page_by_path( $config['slug'] );
		if ( $found && 'page' === $found->post_type ) {
			$stored[ $key ] = (int) $found->ID;
			if ( ! empty( $config['template'] ) ) {
				update_post_meta( $found->ID, '_wp_page_template', $config['template'] );
			}
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_title'   => $config['title'],
				'post_name'    => $config['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$stored[ $key ] = (int) $id;
			if ( ! empty( $config['template'] ) ) {
				update_post_meta( $id, '_wp_page_template', $config['template'] );
			}
		}
	}

	update_option( 'escare_theme_pages', $stored, false );

	if ( ! empty( $stored['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $stored['home'] );
	}
}

/**
 * Wrap every Contact / Contact Us page with the theme template and portal form.
 */
function escare_assign_contact_template() {
	$ids = array();

	if ( class_exists( 'ESC_Portal_Helpers' ) ) {
		$portal_id = ESC_Portal_Helpers::get_page_id( 'contact' );
		if ( $portal_id ) {
			$ids[] = $portal_id;
		}
	}

	$lookups = array( 'contact', 'contact-us', 'contactus' );
	foreach ( $lookups as $slug ) {
		$found = get_page_by_path( $slug );
		if ( $found && 'page' === $found->post_type ) {
			$ids[] = (int) $found->ID;
		}
	}

	foreach ( array( 'Contact', 'Contact Us' ) as $title ) {
		$matches = get_posts(
			array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'title'                  => $title,
				'posts_per_page'         => 5,
				'fields'                 => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		foreach ( $matches as $match_id ) {
			$ids[] = (int) $match_id;
		}
	}

	$ids = array_unique( array_filter( $ids ) );

	foreach ( $ids as $id ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/template-contact.php' );
		delete_post_meta( $id, '_elementor_edit_mode' );

		$content = (string) get_post_field( 'post_content', $id );
		if ( ! has_shortcode( $content, 'esc_contact' ) ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => '[esc_contact]',
				)
			);
		}
	}
}

/**
 * Primary and footer menus — ES Care items only (strips PointLab leftovers).
 */
function escare_setup_menus() {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}

	$pages = get_option( 'escare_theme_pages', array() );
	if ( ! is_array( $pages ) ) {
		$pages = array();
	}

	$primary_id = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( ! $primary_id || ! wp_get_nav_menu_object( $primary_id ) || escare_menu_has_unrelated_items( $primary_id ) ) {
		$primary_id = escare_ensure_named_menu( __( 'E&S Care Primary', 'es-care' ), $primary_id );
		escare_replace_menu_items(
			$primary_id,
			array(
				array( 'key' => 'home', 'title' => __( 'Home', 'es-care' ) ),
				array( 'key' => 'about', 'title' => __( 'About Us', 'es-care' ) ),
				array( 'key' => 'services', 'title' => __( 'Services', 'es-care' ) ),
				array( 'key' => 'who-we-staff', 'title' => __( 'Who We Staff', 'es-care' ) ),
				array( 'key' => 'employers', 'title' => __( 'Employers', 'es-care' ) ),
				array( 'key' => 'job-seekers', 'title' => __( 'Job Seekers', 'es-care' ) ),
				array( 'custom' => true, 'title' => __( 'Job Portal', 'es-care' ), 'url' => escare_portal_url( 'careers' ) ),
				array( 'custom' => true, 'title' => __( 'Contact Us', 'es-care' ), 'url' => escare_portal_url( 'contact' ) ),
			),
			$pages
		);
		$locations['primary'] = $primary_id;
	}

	$footer_id = ! empty( $locations['footer'] ) ? (int) $locations['footer'] : 0;
	if ( ! $footer_id || ! wp_get_nav_menu_object( $footer_id ) || escare_menu_has_unrelated_items( $footer_id ) ) {
		$footer_id = escare_ensure_named_menu( __( 'E&S Care Footer', 'es-care' ), $footer_id );
		escare_replace_menu_items(
			$footer_id,
			array(
				array( 'key' => 'services', 'title' => __( 'Services', 'es-care' ) ),
				array( 'key' => 'core-values', 'title' => __( 'Core Values', 'es-care' ) ),
				array( 'key' => 'privacy', 'title' => __( 'Privacy Policy', 'es-care' ) ),
				array( 'key' => 'code-of-conduct', 'title' => __( 'Code of Conduct', 'es-care' ) ),
				array( 'key' => 'equal-opportunity', 'title' => __( 'Equal Opportunity', 'es-care' ) ),
			),
			$pages
		);
		$locations['footer'] = $footer_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
	escare_purge_unrelated_menu_items_everywhere();
}

/**
 * Titles left over from PointLab / unrelated lab businesses.
 *
 * @return string[]
 */
function escare_unrelated_menu_titles() {
	return array(
		'scientific',
		'chemistry',
		'gemological',
		'gemology',
		'testimonial',
		'testimonials',
		'pointlab',
		'point lab',
		'point-lab',
	);
}

/**
 * @param string $title Menu item title.
 * @return bool
 */
function escare_is_unrelated_menu_title( $title ) {
	$needle = strtolower( trim( wp_strip_all_tags( (string) $title ) ) );
	foreach ( escare_unrelated_menu_titles() as $bad ) {
		if ( $needle === $bad || false !== strpos( $needle, $bad ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @param int $menu_id Menu term ID.
 * @return bool
 */
function escare_menu_has_unrelated_items( $menu_id ) {
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! $items ) {
		return false;
	}
	foreach ( $items as $item ) {
		if ( escare_is_unrelated_menu_title( $item->title ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Delete unrelated leftovers from every nav menu on the site.
 */
function escare_purge_unrelated_menu_items_everywhere() {
	$menus = wp_get_nav_menus();
	if ( empty( $menus ) ) {
		return;
	}

	foreach ( $menus as $menu ) {
		$items = wp_get_nav_menu_items( $menu->term_id );
		if ( ! $items ) {
			continue;
		}
		foreach ( $items as $item ) {
			if ( escare_is_unrelated_menu_title( $item->title ) ) {
				wp_delete_post( (int) $item->ID, true );
			}
		}
	}
}

/**
 * @param string $name     Menu name.
 * @param int    $existing Existing menu ID (0 to create).
 * @return int
 */
function escare_ensure_named_menu( $name, $existing = 0 ) {
	if ( $existing && wp_get_nav_menu_object( $existing ) ) {
		return (int) $existing;
	}

	$found = wp_get_nav_menu_object( $name );
	if ( $found ) {
		return (int) $found->term_id;
	}

	$created = wp_create_nav_menu( $name );
	return ( $created && ! is_wp_error( $created ) ) ? (int) $created : 0;
}

/**
 * Clear a menu and add the given ES Care items.
 *
 * @param int   $menu_id Menu ID.
 * @param array $items   Item definitions.
 * @param array $pages   Seeded page IDs.
 */
function escare_replace_menu_items( $menu_id, $items, $pages ) {
	if ( ! $menu_id ) {
		return;
	}

	$existing = wp_get_nav_menu_items( $menu_id );
	if ( $existing ) {
		foreach ( $existing as $item ) {
			wp_delete_post( (int) $item->ID, true );
		}
	}

	foreach ( $items as $item ) {
		$title = isset( $item['title'] ) ? $item['title'] : '';
		if ( ! empty( $item['custom'] ) ) {
			escare_add_menu_link_item( $menu_id, $title, isset( $item['url'] ) ? $item['url'] : home_url( '/' ) );
			continue;
		}
		$key = isset( $item['key'] ) ? $item['key'] : '';
		escare_add_menu_page_item(
			$menu_id,
			$title,
			isset( $pages[ $key ] ) ? (int) $pages[ $key ] : 0,
			escare_page_url( $key )
		);
	}
}

/**
 * @param int    $menu_id Menu ID.
 * @param string $title   Label.
 * @param int    $page_id Page ID.
 * @param string $url     Fallback URL.
 */
function escare_add_menu_page_item( $menu_id, $title, $page_id, $url ) {
	if ( $page_id && get_post( $page_id ) ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
		return;
	}

	escare_add_menu_link_item( $menu_id, $title, $url );
}

/**
 * @param int    $menu_id Menu ID.
 * @param string $title   Label.
 * @param string $url     URL.
 */
function escare_add_menu_link_item( $menu_id, $title, $url ) {
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'  => $title,
			'menu-item-url'    => $url,
			'menu-item-status' => 'publish',
			'menu-item-type'   => 'custom',
		)
	);
}
