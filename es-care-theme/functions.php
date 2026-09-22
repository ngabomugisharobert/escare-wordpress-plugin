<?php
/**
 * E&S Care theme bootstrap.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

define( 'ESCARE_THEME_VERSION', '1.2.9' );
define( 'ESCARE_THEME_DIR', get_template_directory() );
define( 'ESCARE_THEME_URI', get_template_directory_uri() );

require_once ESCARE_THEME_DIR . '/inc/helpers.php';
require_once ESCARE_THEME_DIR . '/inc/setup.php';
require_once ESCARE_THEME_DIR . '/inc/customizer.php';
require_once ESCARE_THEME_DIR . '/inc/pages.php';
