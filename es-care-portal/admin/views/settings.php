<?php
/**
 * Plugin settings.
 *
 * @package ESC_Portal
 *
 * @var array $settings Settings.
 * @var array $pages    Page IDs.
 */

defined( 'ABSPATH' ) || exit;

$allowed       = isset( $settings['allowed_types'] ) ? (array) $settings['allowed_types'] : array( 'pdf', 'doc', 'docx' );
$tile_seeker   = isset( $settings['tile_seeker'] ) ? (array) $settings['tile_seeker'] : array();
$tile_employer = isset( $settings['tile_employer'] ) ? (array) $settings['tile_employer'] : array();

$seeker_tiles = array(
	'apply'       => __( 'Job Application / Resume', 'es-care-portal' ),
	'assessments' => __( 'Pre-Hire Assessment Tests', 'es-care-portal' ),
	'results'     => __( 'My Assessment Results', 'es-care-portal' ),
	'forms'       => __( 'Employment Forms', 'es-care-portal' ),
);

$employer_tiles = array(
	'post'       => __( 'Post a Job', 'es-care-portal' ),
	'jobs'       => __( 'Company Jobs', 'es-care-portal' ),
	'profile'    => __( 'Edit Profile', 'es-care-portal' ),
	'membership' => __( 'Membership', 'es-care-portal' ),
);
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'ES Care Portal Settings', 'es-care-portal' ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_save_settings', 'esc_settings_nonce' ); ?>
		<input type="hidden" name="action" value="esc_save_settings">

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="notification_email"><?php esc_html_e( 'Notification email', 'es-care-portal' ); ?></label></th>
				<td>
					<input type="email" class="regular-text" id="notification_email" name="notification_email" value="<?php echo esc_attr( $settings['notification_email'] ); ?>">
					<p class="description"><?php esc_html_e( 'Receives a notice when a new application is submitted.', 'es-care-portal' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="max_file_mb"><?php esc_html_e( 'Max resume size (MB)', 'es-care-portal' ); ?></label></th>
				<td>
					<input type="number" min="1" max="25" id="max_file_mb" name="max_file_mb" value="<?php echo esc_attr( (string) $settings['max_file_mb'] ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Allowed resume types', 'es-care-portal' ); ?></th>
				<td>
					<?php foreach ( array( 'pdf' => 'PDF', 'doc' => 'DOC', 'docx' => 'DOCX' ) as $ext => $label ) : ?>
						<label>
							<input type="checkbox" name="allowed_types[]" value="<?php echo esc_attr( $ext ); ?>" <?php checked( in_array( $ext, $allowed, true ) ); ?>>
							<?php echo esc_html( $label ); ?>
						</label><br>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Built-in SMTP email', 'es-care-portal' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Send portal confirmations directly through your control-panel mailbox. This configuration takes priority over other WordPress mail plugins when enabled.', 'es-care-portal' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable SMTP', 'es-care-portal' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="smtp_enabled" value="1" <?php checked( ! empty( $settings['smtp_enabled'] ) ); ?>>
						<?php esc_html_e( 'Use ES Care Portal SMTP for all WordPress email', 'es-care-portal' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_host"><?php esc_html_e( 'SMTP host', 'es-care-portal' ); ?></label></th>
				<td><input type="text" class="regular-text" id="smtp_host" name="smtp_host" value="<?php echo esc_attr( $settings['smtp_host'] ); ?>" placeholder="escareservices.com"></td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_port"><?php esc_html_e( 'SMTP port', 'es-care-portal' ); ?></label></th>
				<td><input type="number" min="1" max="65535" id="smtp_port" name="smtp_port" value="<?php echo esc_attr( (string) $settings['smtp_port'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_encryption"><?php esc_html_e( 'Encryption', 'es-care-portal' ); ?></label></th>
				<td>
					<select id="smtp_encryption" name="smtp_encryption">
						<option value="ssl" <?php selected( $settings['smtp_encryption'], 'ssl' ); ?>><?php esc_html_e( 'SSL (usually port 465)', 'es-care-portal' ); ?></option>
						<option value="tls" <?php selected( $settings['smtp_encryption'], 'tls' ); ?>><?php esc_html_e( 'TLS (usually port 587)', 'es-care-portal' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_username"><?php esc_html_e( 'SMTP username', 'es-care-portal' ); ?></label></th>
				<td><input type="email" class="regular-text" id="smtp_username" name="smtp_username" autocomplete="username" value="<?php echo esc_attr( $settings['smtp_username'] ); ?>" placeholder="info@escareservices.com"></td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_password"><?php esc_html_e( 'SMTP password', 'es-care-portal' ); ?></label></th>
				<td>
					<input type="password" class="regular-text" id="smtp_password" name="smtp_password" autocomplete="new-password" value="" placeholder="<?php echo esc_attr( ! empty( $settings['smtp_password'] ) ? __( 'Saved — leave blank to keep it', 'es-care-portal' ) : __( 'Email account password', 'es-care-portal' ) ); ?>">
					<p class="description"><?php esc_html_e( 'The password is encrypted before it is saved. Leaving this blank keeps the existing password.', 'es-care-portal' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_from_email"><?php esc_html_e( 'From email', 'es-care-portal' ); ?></label></th>
				<td><input type="email" class="regular-text" id="smtp_from_email" name="smtp_from_email" value="<?php echo esc_attr( $settings['smtp_from_email'] ); ?>" placeholder="info@escareservices.com"></td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_from_name"><?php esc_html_e( 'From name', 'es-care-portal' ); ?></label></th>
				<td><input type="text" class="regular-text" id="smtp_from_name" name="smtp_from_name" value="<?php echo esc_attr( $settings['smtp_from_name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_test_email"><?php esc_html_e( 'Test recipient', 'es-care-portal' ); ?></label></th>
				<td>
					<input type="email" class="regular-text" id="smtp_test_email" name="smtp_test_email" value="<?php echo esc_attr( $settings['notification_email'] ); ?>">
					<p class="description"><?php esc_html_e( 'Save the settings and send a test message to this address.', 'es-care-portal' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Layout theme', 'es-care-portal' ); ?></h2>
		<p class="description"><?php esc_html_e( 'These colors apply site-wide to portal shortcodes and Elementor widgets. You can still override colors per widget in Elementor.', 'es-care-portal' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="color_accent"><?php esc_html_e( 'Accent / buttons', 'es-care-portal' ); ?></label></th>
				<td><input type="color" id="color_accent" name="color_accent" value="<?php echo esc_attr( $settings['color_accent'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="color_sidebar_header"><?php esc_html_e( 'Sidebar header', 'es-care-portal' ); ?></label></th>
				<td><input type="color" id="color_sidebar_header" name="color_sidebar_header" value="<?php echo esc_attr( $settings['color_sidebar_header'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="color_sidebar"><?php esc_html_e( 'Sidebar menu', 'es-care-portal' ); ?></label></th>
				<td><input type="color" id="color_sidebar" name="color_sidebar" value="<?php echo esc_attr( $settings['color_sidebar'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="color_tile"><?php esc_html_e( 'Dashboard tiles', 'es-care-portal' ); ?></label></th>
				<td><input type="color" id="color_tile" name="color_tile" value="<?php echo esc_attr( $settings['color_tile'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="color_cta"><?php esc_html_e( 'Service request box', 'es-care-portal' ); ?></label></th>
				<td><input type="color" id="color_cta" name="color_cta" value="<?php echo esc_attr( $settings['color_cta'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Seeker manage tiles', 'es-care-portal' ); ?></th>
				<td>
					<?php foreach ( $seeker_tiles as $key => $label ) : ?>
						<label>
							<input type="checkbox" name="tile_seeker[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $tile_seeker, true ) ); ?>>
							<?php echo esc_html( $label ); ?>
						</label><br>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Employer manage tiles', 'es-care-portal' ); ?></th>
				<td>
					<?php foreach ( $employer_tiles as $key => $label ) : ?>
						<label>
							<input type="checkbox" name="tile_employer[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $tile_employer, true ) ); ?>>
							<?php echo esc_html( $label ); ?>
						</label><br>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Elementor', 'es-care-portal' ); ?></h2>
		<p>
			<?php if ( did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' ) ) : ?>
				<?php esc_html_e( 'Elementor is active. In the editor, open the widgets panel and find the “ES Care Portal” category, or search for “ES Care Portal Module”.', 'es-care-portal' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Install and activate Elementor to rearrange portal modules visually. Shortcodes still work without Elementor.', 'es-care-portal' ); ?>
			<?php endif; ?>
		</p>
		<p class="description">
			<code>[esc_dash_sidebar]</code>
			<code>[esc_dash_home]</code>
			<code>[esc_dash_view view="apply"]</code>
			<code>[esc_dashboard]</code>
			<code>[esc_jobs]</code>
		</p>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Frontend pages', 'es-care-portal' ); ?></th>
				<td>
					<ul>
						<?php foreach ( (array) $pages as $slug => $page_id ) : ?>
							<li>
								<strong><?php echo esc_html( $slug ); ?>:</strong>
								<?php if ( $page_id && get_post( $page_id ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>"><?php echo esc_html( get_the_title( $page_id ) ); ?></a>
								<?php else : ?>
									<?php esc_html_e( 'Missing — deactivate and reactivate the plugin to recreate pages.', 'es-care-portal' ); ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save settings', 'es-care-portal' ); ?></button>
			<button type="submit" class="button button-secondary" name="esc_save_and_test" value="1"><?php esc_html_e( 'Save and send test email', 'es-care-portal' ); ?></button>
		</p>
	</form>
</div>
