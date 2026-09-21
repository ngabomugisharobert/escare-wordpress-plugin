<?php
/**
 * Template Name: Contact Us
 *
 * Always renders the portal [esc_contact] form. Does not output leftover
 * Elementor / PointLab page content.
 *
 * @package ES_Care
 */

defined( 'ABSPATH' ) || exit;

get_header();

$email    = escare_email();
$phone    = escare_phone();
$hours    = escare_hours();
$address  = escare_address();
$ubi      = escare_ubi();
$pool_ref = escare_pool_ref();
?>
<header class="escare-page-hero">
	<div class="escare-wrap">
		<h1><?php esc_html_e( 'Contact Us', 'es-care' ); ?></h1>
		<p><?php esc_html_e( 'Send a message and the E&S Care Services team will follow up. You do not need an account.', 'es-care' ); ?></p>
	</div>
</header>

<section class="escare-section escare-contact-section">
	<div class="escare-wrap escare-contact-layout">
		<aside class="escare-contact-details">
			<h2><?php esc_html_e( 'Reach us', 'es-care' ); ?></h2>
			<ul class="escare-footer-contact">
				<li>
					<strong><?php esc_html_e( 'Email', 'es-care' ); ?></strong><br>
					<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</li>
				<?php if ( $phone ) : ?>
					<li>
						<strong><?php esc_html_e( 'Phone', 'es-care' ); ?></strong><br>
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					</li>
				<?php endif; ?>
				<?php if ( $hours ) : ?>
					<li>
						<strong><?php esc_html_e( 'Hours', 'es-care' ); ?></strong><br>
						<?php echo esc_html( $hours ); ?>
					</li>
				<?php endif; ?>
				<?php if ( $address ) : ?>
					<li>
						<strong><?php esc_html_e( 'Location', 'es-care' ); ?></strong><br>
						<?php echo nl2br( esc_html( $address ) ); ?>
					</li>
				<?php endif; ?>
				<?php if ( $ubi ) : ?>
					<li>
						<strong><?php esc_html_e( 'WA UBI', 'es-care' ); ?></strong><br>
						<?php echo esc_html( $ubi ); ?>
					</li>
				<?php endif; ?>
				<?php if ( $pool_ref ) : ?>
					<li>
						<strong><?php esc_html_e( 'WA Pool Ref', 'es-care' ); ?></strong><br>
						<?php echo esc_html( $pool_ref ); ?>
					</li>
				<?php endif; ?>
			</ul>
		</aside>
		<div class="escare-contact-form">
			<?php
			if ( shortcode_exists( 'esc_contact' ) ) {
				echo do_shortcode( '[esc_contact]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<p>' . esc_html__( 'Activate the ES Care Portal plugin to enable this form.', 'es-care' ) . '</p>';
			}
			?>
		</div>
	</div>
</section>
<?php
get_footer();
