<?php
/**
 * Job listings.
 *
 * @package ESC_Portal
 *
 * @var WP_Query $query
 * @var string   $category
 * @var string   $type
 * @var string   $location
 * @var array|WP_Error $categories
 * @var string[] $locations
 * @var array    $types
 */

defined( 'ABSPATH' ) || exit;

$categories = ( ! is_wp_error( $categories ) && is_array( $categories ) ) ? $categories : array();
?>
<div class="esc-portal-wrap">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<header class="esc-hero">
		<p class="esc-kicker"><?php esc_html_e( 'Careers', 'es-care-portal' ); ?></p>
		<h2><?php esc_html_e( 'Join the ES Care Services team', 'es-care-portal' ); ?></h2>
		<p><?php esc_html_e( 'Browse open roles in home and personal care. Sign in to apply.', 'es-care-portal' ); ?></p>
	</header>

	<form class="esc-filters" method="get">
		<p>
			<label for="esc_cat"><?php esc_html_e( 'Category', 'es-care-portal' ); ?></label>
			<select id="esc_cat" name="esc_cat">
				<option value=""><?php esc_html_e( 'All categories', 'es-care-portal' ); ?></option>
				<?php foreach ( $categories as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $category, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="esc_type"><?php esc_html_e( 'Type', 'es-care-portal' ); ?></label>
			<select id="esc_type" name="esc_type">
				<option value=""><?php esc_html_e( 'All types', 'es-care-portal' ); ?></option>
				<?php foreach ( $types as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="esc_location"><?php esc_html_e( 'Location', 'es-care-portal' ); ?></label>
			<select id="esc_location" name="esc_location">
				<option value=""><?php esc_html_e( 'All locations', 'es-care-portal' ); ?></option>
				<?php foreach ( $locations as $loc ) : ?>
					<option value="<?php echo esc_attr( $loc ); ?>" <?php selected( $location, $loc ); ?>><?php echo esc_html( $loc ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="esc-filters-submit">
			<button type="submit" class="esc-button"><?php esc_html_e( 'Filter', 'es-care-portal' ); ?></button>
		</p>
	</form>

	<?php if ( ! $query->have_posts() ) : ?>
		<p class="esc-empty"><?php esc_html_e( 'No matching jobs right now. Check back soon.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<ul class="esc-job-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$job_id  = get_the_ID();
				$open    = ESC_Portal_Helpers::is_job_open( $job_id );
				$loc     = get_post_meta( $job_id, '_esc_location', true );
				$emp     = get_post_meta( $job_id, '_esc_employment_type', true );
				$shift   = get_post_meta( $job_id, '_esc_shift', true );
				$typeset = ESC_Portal_Helpers::employment_types();
				?>
				<li class="esc-job-card">
					<div>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="esc-meta">
							<?php if ( $loc ) : ?><span><?php echo esc_html( $loc ); ?></span><?php endif; ?>
							<?php if ( isset( $typeset[ $emp ] ) ) : ?><span><?php echo esc_html( $typeset[ $emp ] ); ?></span><?php endif; ?>
							<?php if ( $shift ) : ?><span><?php echo esc_html( $shift ); ?></span><?php endif; ?>
							<span class="esc-status esc-status--<?php echo $open ? 'open' : 'closed'; ?>"><?php echo $open ? esc_html__( 'Open', 'es-care-portal' ) : esc_html__( 'Closed', 'es-care-portal' ); ?></span>
						</p>
						<?php if ( has_excerpt() ) : ?>
							<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $open ) : ?>
						<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Apply::apply_url( $job_id ) ); ?>"><?php esc_html_e( 'Apply', 'es-care-portal' ); ?></a>
					<?php endif; ?>
				</li>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</ul>
	<?php endif; ?>
</div>
