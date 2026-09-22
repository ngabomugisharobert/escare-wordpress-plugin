<?php
/**
 * Customizer: owner-confirmable contact details.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', 'escare_customize_register' );

/**
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function escare_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'escare_contact',
		array(
			'title'       => __( 'E&S Care contact', 'es-care' ),
			'description' => __( 'Public details shown in the header, footer, and Contact page.', 'es-care' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_setting(
		'escare_phone',
		array(
			'default'           => '360-742-8095',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'escare_phone',
		array(
			'label'   => __( 'Phone', 'es-care' ),
			'section' => 'escare_contact',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'escare_email',
		array(
			'default'           => 'info@escareservices.com',
			'sanitize_callback' => 'sanitize_email',
		)
	);
	$wp_customize->add_control(
		'escare_email',
		array(
			'label'   => __( 'Public email', 'es-care' ),
			'section' => 'escare_contact',
			'type'    => 'email',
		)
	);

	$wp_customize->add_setting(
		'escare_hours',
		array(
			'default'           => '24/7 shift coverage',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'escare_hours',
		array(
			'label'   => __( 'Office hours', 'es-care' ),
			'section' => 'escare_contact',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'escare_address',
		array(
			'default'           => "3917 Boulevard Rd SE\nOlympia, WA 98501",
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'escare_address',
		array(
			'label'   => __( 'Address or service area', 'es-care' ),
			'section' => 'escare_contact',
			'type'    => 'textarea',
		)
	);
}
