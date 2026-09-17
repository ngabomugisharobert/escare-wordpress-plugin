<?php
/**
 * Assessment attempts in WP admin.
 *
 * @package ESC_Portal
 *
 * @var object[] $attempts
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'Assessment results', 'es-care-portal' ); ?></h1>
	<p class="esc-admin-lede"><?php esc_html_e( 'Scores from the job-seeker pre-hire assessment.', 'es-care-portal' ); ?></p>

	<?php if ( empty( $attempts ) ) : ?>
		<p><?php esc_html_e( 'No assessments have been submitted yet.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Candidate', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Test', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Score', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Result', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Date', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $attempts as $attempt ) : ?>
					<tr>
						<td>
							<?php echo esc_html( trim( $attempt->first_name . ' ' . $attempt->last_name ) ); ?>
							<br><span class="description"><?php echo esc_html( $attempt->email ); ?></span>
						</td>
						<td><?php echo esc_html( $attempt->title ); ?></td>
						<td><?php echo esc_html( (string) $attempt->score ); ?>%</td>
						<td><?php echo $attempt->passed ? esc_html__( 'Passed', 'es-care-portal' ) : esc_html__( 'Not passed', 'es-care-portal' ); ?></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $attempt->created_at ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
