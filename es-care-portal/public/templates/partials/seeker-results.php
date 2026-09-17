<?php
/**
 * Assessment results.
 *
 * @package ESC_Portal
 *
 * @var object[] $attempts
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Assessment Results', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Your completed tests appear here. A passing score lets hiring staff move you forward.', 'es-care-portal' ); ?></p>

	<?php if ( empty( $attempts ) ) : ?>
		<p><?php esc_html_e( 'You have not taken an assessment yet.', 'es-care-portal' ); ?></p>
		<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'assessments' ) ); ?>"><?php esc_html_e( 'Take an assessment', 'es-care-portal' ); ?></a>
	<?php else : ?>
		<div class="esc-table-wrap">
			<table class="esc-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Test', 'es-care-portal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Score', 'es-care-portal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Result', 'es-care-portal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Date', 'es-care-portal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $attempts as $attempt ) : ?>
						<tr>
							<td><?php echo esc_html( $attempt->title ); ?></td>
							<td><?php echo esc_html( (string) $attempt->score ); ?>%</td>
							<td>
								<span class="esc-status esc-status--<?php echo $attempt->passed ? 'hired' : 'rejected'; ?>">
									<?php echo $attempt->passed ? esc_html__( 'Passed', 'es-care-portal' ) : esc_html__( 'Not passed', 'es-care-portal' ); ?>
								</span>
							</td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $attempt->created_at ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>
