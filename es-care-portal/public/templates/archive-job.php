<?php
/**
 * Job archive / category template.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo do_shortcode( '[esc_jobs]' );
get_footer();
