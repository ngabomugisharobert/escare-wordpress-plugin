<?php
/**
 * Site footer.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

$email    = escare_email();
$phone    = escare_phone();
$hours    = escare_hours();
$address = escare_address();
$user    = escare_portal_user();
?>
</main>

<footer class="escare-footer" role="contentinfo">
	<div class="escare-wrap escare-footer-grid">
		<div class="escare-footer-brand">
			<img class="escare-footer-logo" src="<?php echo esc_url( escare_logo_url() ); ?>" alt="<?php echo esc_attr__( 'ES Care Services', 'es-care' ); ?>" width="1888" height="716" decoding="async">
			<p class="escare-footer-legal"><?php esc_html_e( 'E&S Care Service LLC', 'es-care' ); ?></p>
			<p><?php esc_html_e( 'Healthcare staffing services for facilities and caregivers. Care you can trust.', 'es-care' ); ?></p>
		</div>
		<div>
			<h2 class="escare-footer-heading"><?php esc_html_e( 'Explore', 'es-care' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'escare-footer-links',
					'fallback_cb'    => '__return_empty_string',
					'depth'          => 1,
				)
			);
			?>
		</div>
		<div>
			<h2 class="escare-footer-heading"><?php esc_html_e( 'Portal', 'es-care' ); ?></h2>
			<ul class="escare-footer-links">
				<?php if ( $user ) : ?>
					<li><a href="<?php echo esc_url( escare_portal_url( 'dashboard' ) ); ?>"><?php esc_html_e( 'Dashboard', 'es-care' ); ?></a></li>
					<li><a href="<?php echo esc_url( escare_portal_url( 'careers' ) ); ?>"><?php esc_html_e( 'Careers', 'es-care' ); ?></a></li>
					<li><a href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'es-care' ); ?></a></li>
					<li><a href="<?php echo esc_url( escare_portal_logout_url() ); ?>"><?php esc_html_e( 'Sign out', 'es-care' ); ?></a></li>
				<?php else : ?>
					<li><a href="<?php echo esc_url( escare_portal_url( 'login' ) ); ?>"><?php esc_html_e( 'Sign in', 'es-care' ); ?></a></li>
					<li><a href="<?php echo esc_url( escare_portal_url( 'register' ) ); ?>"><?php esc_html_e( 'Register', 'es-care' ); ?></a></li>
					<li><a href="<?php echo esc_url( escare_portal_url( 'careers' ) ); ?>"><?php esc_html_e( 'Careers', 'es-care' ); ?></a></li>
					<li><a href="<?php echo esc_url( escare_portal_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'es-care' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
		<div>
			<h2 class="escare-footer-heading"><?php esc_html_e( 'Contact', 'es-care' ); ?></h2>
			<ul class="escare-footer-contact">
				<?php if ( $phone ) : ?>
					<li><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
				<?php if ( $hours ) : ?>
					<li><?php echo esc_html( $hours ); ?></li>
				<?php endif; ?>
				<?php if ( $address ) : ?>
					<li><?php echo nl2br( esc_html( $address ) ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="escare-footer-bar">
		<div class="escare-wrap">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'E&S Care Service LLC. All rights reserved.', 'es-care' ); ?></p>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
