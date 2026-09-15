<?php
/**
 * Job-seeker dashboard home.
 *
 * @package ESC_Portal
 *
 * @var object $user
 * @var bool   $profile_complete
 */

defined( 'ABSPATH' ) || exit;

$tiles = array(
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'apply' ),
		'label' => __( 'Job Application / Resume', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/><path d="M10 13h6M10 17h4"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'assessments' ),
		'label' => __( 'Pre-Hire Assessment Tests', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'results' ),
		'label' => __( 'My Assessment Results', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 4h10v17l-5-3-5 3z"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'forms' ),
		'label' => __( 'Employment Forms', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 3h8l4 4v14H7z"/><path d="M15 3v4h4"/><path d="M10 12h6M10 16h4"/></svg>',
	),
);

$account = array(
	array(
		'url'   => ESC_Portal_Auth::logout_url(),
		'label' => __( 'Logout', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3v8"/><path d="M7.2 6.5a7 7 0 1 0 9.6 0"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'password' ),
		'label' => __( 'Change Password', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M5 12h14M7.1 7.1l9.8 9.8M16.9 7.1 7.1 16.9"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'password' ) . '#esc-delete-account',
		'label' => __( 'Delete Account', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 7h14M9 7V5h6v2M8 7l1 13h6l1-13"/></svg>',
	),
);
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Dashboard', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy">
		<?php
		echo wp_kses(
			sprintf(
				/* translators: 1: application link, 2: assessment link, 3: forms link */
				__( 'Welcome. At ES Care Services, we are committed to finding opportunities that work with your abilities, schedule and experience. Our application process is simple. %1$s then take the %2$s, and finally %3$s to be filled. On completion, leave the rest to us to find you the best opportunity that works for you. For any clarifications feel free to get in touch with us.', 'es-care-portal' ),
				'<a href="' . esc_url( ESC_Portal_Helpers::dashboard_url( 'apply' ) ) . '">' . esc_html__( 'Submit your Application/Resume', 'es-care-portal' ) . '</a>',
				'<a href="' . esc_url( ESC_Portal_Helpers::dashboard_url( 'assessments' ) ) . '">' . esc_html__( 'Pre-Hire Assessment Test', 'es-care-portal' ) . '</a>',
				'<a href="' . esc_url( ESC_Portal_Helpers::dashboard_url( 'forms' ) ) . '">' . esc_html__( 'Download Employment Forms', 'es-care-portal' ) . '</a>'
			),
			array(
				'a' => array(
					'href' => array(),
				),
			)
		);
		?>
	</p>

	<?php if ( empty( $profile_complete ) ) : ?>
		<div class="esc-notice esc-notice--info">
			<?php esc_html_e( 'Finish your profile so applications pre-fill faster.', 'es-care-portal' ); ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'profile' ) ); ?>"><?php esc_html_e( 'Update profile', 'es-care-portal' ); ?></a>
		</div>
	<?php endif; ?>

	<div class="esc-tiles">
		<?php foreach ( $tiles as $tile ) : ?>
			<a class="esc-tile" href="<?php echo esc_url( $tile['url'] ); ?>">
				<span class="esc-tile-icon" aria-hidden="true"><?php echo $tile['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html( $tile['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<h3 class="esc-dash-title esc-dash-title--sub"><?php esc_html_e( 'Account', 'es-care-portal' ); ?></h3>
	<div class="esc-tiles esc-tiles--account">
		<?php foreach ( $account as $tile ) : ?>
			<a class="esc-tile" href="<?php echo esc_url( $tile['url'] ); ?>">
				<span class="esc-tile-icon" aria-hidden="true"><?php echo $tile['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html( $tile['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
