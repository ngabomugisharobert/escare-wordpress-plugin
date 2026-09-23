<?php
/**
 * Elementor widget: pick any portal module.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Elementor_Widget_Module extends ESC_Portal_Elementor_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'esc_portal_module';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'ES Care Portal Module', 'es-care-portal' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'esc_content',
			array(
				'label' => __( 'Module', 'es-care-portal' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'module',
			array(
				'label'   => __( 'Portal module', 'es-care-portal' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'dashboard',
				'options' => array(
					'dashboard'      => __( 'Full dashboard', 'es-care-portal' ),
					'sidebar'        => __( 'Dashboard sidebar only', 'es-care-portal' ),
					'home_tiles'     => __( 'Home tiles only', 'es-care-portal' ),
					'dash_view'      => __( 'Dashboard view (content)', 'es-care-portal' ),
					'register'       => __( 'Register form', 'es-care-portal' ),
					'login'          => __( 'Login form', 'es-care-portal' ),
					'jobs'           => __( 'Jobs listing', 'es-care-portal' ),
					'apply'          => __( 'Application form', 'es-care-portal' ),
					'profile'        => __( 'Profile form', 'es-care-portal' ),
					'job_form'       => __( 'Post / edit job form', 'es-care-portal' ),
					'lost_password'  => __( 'Lost password', 'es-care-portal' ),
					'reset_password' => __( 'Reset password', 'es-care-portal' ),
					'contact'        => __( 'Contact us form', 'es-care-portal' ),
					'logout'         => __( 'Logout link', 'es-care-portal' ),
					'notice'         => __( 'Portal notices', 'es-care-portal' ),
				),
			)
		);

		$this->add_control(
			'role',
			array(
				'label'     => __( 'Force role', 'es-care-portal' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'auto',
				'options'   => array(
					'auto'     => __( 'Auto (current user)', 'es-care-portal' ),
					'seeker'   => __( 'Job seeker', 'es-care-portal' ),
					'employer' => __( 'Employer', 'es-care-portal' ),
				),
				'condition' => array(
					'module' => array( 'sidebar', 'home_tiles', 'dash_view' ),
				),
			)
		);

		$this->add_control(
			'view',
			array(
				'label'     => __( 'Dashboard view', 'es-care-portal' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''            => __( 'Current URL view', 'es-care-portal' ),
					'home'        => __( 'Home', 'es-care-portal' ),
					'apply'       => __( 'Seeker: Application', 'es-care-portal' ),
					'assessments' => __( 'Seeker: Assessments', 'es-care-portal' ),
					'results'     => __( 'Seeker: Results', 'es-care-portal' ),
					'forms'       => __( 'Seeker: Employment forms', 'es-care-portal' ),
					'password'    => __( 'Change password', 'es-care-portal' ),
					'request'     => __( 'Service request', 'es-care-portal' ),
					'profile'     => __( 'Employer: Profile', 'es-care-portal' ),
					'jobs'        => __( 'Employer: Jobs', 'es-care-portal' ),
					'post'        => __( 'Employer: Post job', 'es-care-portal' ),
					'users'       => __( 'Admin: Users', 'es-care-portal' ),
					'applications'=> __( 'Admin: Applications', 'es-care-portal' ),
					'conduct'     => __( 'Code of Conduct', 'es-care-portal' ),
				),
				'condition' => array(
					'module' => 'dash_view',
				),
			)
		);

		$this->add_control(
			'layout_hint',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<p style="margin:0;line-height:1.45;">' . esc_html__( 'Tip: place Sidebar in a left column and Home tiles or Dashboard view in a right column to rebuild the dashboard layout in Elementor.', 'es-care-portal' ) . '</p>',
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$module   = isset( $settings['module'] ) ? $settings['module'] : 'dashboard';
		$role     = isset( $settings['role'] ) ? $settings['role'] : 'auto';
		$view     = isset( $settings['view'] ) ? $settings['view'] : '';

		$html = '';

		switch ( $module ) {
			case 'sidebar':
				$html = ESC_Portal_Blocks::sidebar( array( 'role' => $role ) );
				break;
			case 'home_tiles':
				$html = ESC_Portal_Blocks::home_tiles( array( 'role' => $role ) );
				break;
			case 'dash_view':
				$html = ESC_Portal_Blocks::view(
					array(
						'role' => $role,
						'view' => $view,
					)
				);
				break;
			case 'notice':
				$html = ESC_Portal_Blocks::notice();
				break;
			case 'register':
				$html = ESC_Portal_Shortcodes::register_form();
				break;
			case 'login':
				$html = ESC_Portal_Shortcodes::login_form();
				break;
			case 'jobs':
				$html = ESC_Portal_Shortcodes::jobs();
				break;
			case 'apply':
				$html = ESC_Portal_Shortcodes::apply();
				break;
			case 'profile':
				$html = ESC_Portal_Shortcodes::profile();
				break;
			case 'job_form':
				$html = ESC_Portal_Shortcodes::job_form();
				break;
			case 'lost_password':
				$html = ESC_Portal_Shortcodes::lost_password();
				break;
			case 'reset_password':
				$html = ESC_Portal_Shortcodes::reset_password();
				break;
			case 'contact':
				$html = ESC_Portal_Shortcodes::contact_form();
				break;
			case 'logout':
				$html = ESC_Portal_Shortcodes::logout_link();
				break;
			case 'dashboard':
			default:
				$html = ESC_Portal_Shortcodes::dashboard();
				break;
		}

		if ( ! $html ) {
			$is_edit = false;
			if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) ) {
				$is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();
			}
			if ( $is_edit ) {
				$html = '<div class="esc-notice esc-notice--info">' . esc_html__( 'This module has no output for the current visitor (sign in as a job seeker or employer to preview).', 'es-care-portal' ) . '</div>';
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- portal templates escape their own output.
		echo $this->wrap( $html );
	}
}
