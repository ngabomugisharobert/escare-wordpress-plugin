<?php
/**
 * Employer dashboard home — Manage + Account tiles.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

$manage = array(
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'post' ),
		'label' => __( 'Post a Job', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="3"><path d="M32 12v40M12 32h40"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'jobs' ),
		'label' => __( 'Company Jobs', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="3"><rect x="10" y="22" width="44" height="32" rx="4"/><path d="M24 22v-6a8 8 0 0 1 8-8h0a8 8 0 0 1 8 8v6"/><path d="M10 34h44"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'profile' ),
		'label' => __( 'Edit Profile', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="3"><path d="M14 10h28l12 12v32H14z"/><path d="M42 10v12h12"/><path d="M22 38l6 6 14-14"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'membership' ),
		'label' => __( 'Membership', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="3"><circle cx="22" cy="22" r="8"/><circle cx="42" cy="24" r="7"/><path d="M6 52c2-10 8-15 16-15s14 5 16 15"/><path d="M34 40c4-2 9-1 14 5 2 3 3 6 4 7"/></svg>',
	),
);

$account = array(
	array(
		'url'   => ESC_Portal_Auth::logout_url(),
		'label' => __( 'Logout', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="3"><circle cx="32" cy="32" r="22"/><path d="M32 14v18"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'password' ),
		'label' => __( 'Change Password', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="3"><path d="M32 10v44M10 32h44M16 16l32 32M48 16 16 48"/></svg>',
	),
	array(
		'url'   => ESC_Portal_Helpers::dashboard_url( 'password' ) . '#esc-delete-account',
		'label' => __( 'Delete Account', 'es-care-portal' ),
		'icon'  => '<svg viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 18h40M24 18V12h16v6M20 18l4 36h16l4-36"/></svg>',
	),
);
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Manage', 'es-care-portal' ); ?></h2>
	<div class="esc-tiles">
		<?php foreach ( $manage as $tile ) : ?>
			<a class="esc-tile" href="<?php echo esc_url( $tile['url'] ); ?>">
				<span class="esc-tile-icon" aria-hidden="true"><?php echo $tile['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html( $tile['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<h3 class="esc-dash-title esc-dash-title--sub esc-dash-title--rule"><?php esc_html_e( 'Account', 'es-care-portal' ); ?></h3>
	<div class="esc-tiles esc-tiles--account">
		<?php foreach ( $account as $tile ) : ?>
			<a class="esc-tile" href="<?php echo esc_url( $tile['url'] ); ?>">
				<span class="esc-tile-icon" aria-hidden="true"><?php echo $tile['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html( $tile['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
