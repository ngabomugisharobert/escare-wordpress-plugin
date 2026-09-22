<?php
/**
 * Site header — compact brand + nav (not Diligent-style utility/trust/CTA chrome).
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

$phone = escare_phone();
$email = escare_email();
$user  = escare_portal_user();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="escare-skip" href="#escare-main"><?php esc_html_e( 'Skip to content', 'es-care' ); ?></a>

<header class="escare-header" role="banner">
	<div class="escare-wrap escare-header-bar">
		<a class="escare-wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img
				class="escare-logo"
				src="<?php echo esc_url( escare_logo_url() ); ?>"
				alt="<?php echo esc_attr__( 'ES Care Services — Care you can trust', 'es-care' ); ?>"
				width="1888"
				height="716"
				decoding="async"
			>
		</a>

		<nav class="escare-nav" aria-label="<?php esc_attr_e( 'Primary', 'es-care' ); ?>">
			<button class="escare-menu-toggle" type="button" aria-expanded="false" aria-controls="escare-primary-menu">
				<span class="escare-menu-toggle-bars" aria-hidden="true"></span>
				<span class="escare-menu-toggle-label"><?php esc_html_e( 'Menu', 'es-care' ); ?></span>
			</button>
			<div id="escare-primary-menu" class="escare-nav-panel">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'escare-nav-list',
						'fallback_cb'    => 'escare_fallback_primary_menu',
						'depth'          => 1,
					)
				);
				?>
			</div>
		</nav>

		<div class="escare-header-meta">
			<?php if ( $phone ) : ?>
				<a class="escare-header-phone" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
			<?php else : ?>
				<a class="escare-header-mail" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
			<?php if ( $user ) : ?>
				<a class="escare-btn escare-btn--solid escare-btn--compact" href="<?php echo esc_url( escare_portal_url( 'dashboard' ) ); ?>"><?php esc_html_e( 'Dashboard', 'es-care' ); ?></a>
				<a class="escare-btn escare-btn--ghost escare-btn--compact" href="<?php echo esc_url( escare_portal_logout_url() ); ?>"><?php esc_html_e( 'Sign out', 'es-care' ); ?></a>
			<?php else : ?>
				<a class="escare-btn escare-btn--solid escare-btn--compact" href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Apply', 'es-care' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="escare-main" class="escare-main">
