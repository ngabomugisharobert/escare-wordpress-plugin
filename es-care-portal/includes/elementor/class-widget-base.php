<?php
/**
 * Shared Elementor widget helpers.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

abstract class ESC_Portal_Elementor_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * @return array
	 */
	public function get_categories() {
		return array( 'esc-care-portal' );
	}

	/**
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'es care', 'portal', 'jobs', 'dashboard', 'careers' );
	}

	/**
	 * Common layout/style controls.
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'esc_style_section',
			array(
				'label' => __( 'Portal style', 'es-care-portal' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'esc_padding',
			array(
				'label'      => __( 'Padding', 'es-care-portal' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .esc-elementor-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'esc_accent',
			array(
				'label'     => __( 'Accent color', 'es-care-portal' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .esc-elementor-widget' => '--esc-teal: {{VALUE}}; --esc-tile: {{VALUE}};',
					'{{WRAPPER}} .esc-button'           => 'background: {{VALUE}};',
					'{{WRAPPER}} .esc-tile'             => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .esc-tile-icon'        => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'esc_sidebar_color',
			array(
				'label'     => __( 'Sidebar menu color', 'es-care-portal' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .esc-side-nav a[aria-current="page"]' => 'background: {{VALUE}};',
					'{{WRAPPER}} .esc-subnav-links a[aria-current="page"]' => 'background: {{VALUE}};',
					'{{WRAPPER}} .esc-subnav-cta' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'esc_sidebar_header',
			array(
				'label'     => __( 'Sidebar header color', 'es-care-portal' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .esc-side-kicker' => 'background: {{VALUE}};',
					'{{WRAPPER}} .esc-subnav-kicker' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'esc_max_width',
			array(
				'label'      => __( 'Max width', 'es-care-portal' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 320,
						'max' => 1400,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .esc-elementor-widget .esc-portal-wrap' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Wrap rendered HTML.
	 *
	 * @param string $html Inner HTML.
	 * @return string
	 */
	protected function wrap( $html ) {
		return '<div class="esc-elementor-widget">' . $html . '</div>';
	}
}
