<?php
/**
 * Portal admin assessments list + results.
 *
 * @package ESC_Portal
 *
 * @var object[] $assessments
 * @var array    $question_counts
 * @var object[] $attempts
 */

defined( 'ABSPATH' ) || exit;

$assessments     = isset( $assessments ) && is_array( $assessments ) ? $assessments : array();
$question_counts = isset( $question_counts ) && is_array( $question_counts ) ? $question_counts : array();
$attempts        = isset( $attempts ) && is_array( $attempts ) ? $attempts : array();
$post_url        = admin_url( 'admin-post.php' );
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Assessments', 'es-care-portal' ); ?></h2>
	<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'assessment' ) ); ?>"><?php esc_html_e( 'Create assessment', 'es-care-portal' ); ?></a>
</div>
<p class="esc-dash-copy"><?php esc_html_e( 'Build pre-hire tests and assign one to each job listing. Seekers see tests required by jobs they applied to, plus any general (unassigned) tests.', 'es-care-portal' ); ?></p>

<section class="esc-card esc-data-panel">
	<h3><?php esc_html_e( 'Your assessments', 'es-care-portal' ); ?></h3>
	<?php if ( empty( $assessments ) ) : ?>
		<p><?php esc_html_e( 'No assessments yet. Create one to get started.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<div class="esc-table-wrap">
			<table class="esc-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Title', 'es-care-portal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Questions', 'es-care-portal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Pass score', 'es-care-portal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $assessments as $row ) : ?>
						<?php
						$qid_count = isset( $question_counts[ (int) $row->id ] ) ? (int) $question_counts[ (int) $row->id ] : 0;
						$is_active = 'active' === $row->status;
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $row->title ); ?></strong>
								<?php if ( $row->description ) : ?>
									<br><span class="esc-muted"><?php echo esc_html( wp_trim_words( $row->description, 16 ) ); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( (string) $qid_count ); ?></td>
							<td><?php echo esc_html( (string) (int) $row->pass_score ); ?>%</td>
							<td>
								<span class="esc-status esc-status--<?php echo $is_active ? 'open' : 'closed'; ?>">
									<?php echo $is_active ? esc_html__( 'Active', 'es-care-portal' ) : esc_html__( 'Inactive', 'es-care-portal' ); ?>
								</span>
							</td>
							<td>
								<div class="esc-row-action-group">
									<a class="esc-button esc-button--small" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'assessment', array( 'assessment' => (int) $row->id ) ) ); ?>"><?php esc_html_e( 'Edit', 'es-care-portal' ); ?></a>
									<form method="post" action="<?php echo esc_url( $post_url ); ?>">
										<?php wp_nonce_field( 'esc_admin_assessment_action', 'esc_assessment_action_nonce' ); ?>
										<input type="hidden" name="action" value="esc_admin_assessment_action">
										<input type="hidden" name="esc_assessment_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
										<input type="hidden" name="esc_assessment_action" value="<?php echo $is_active ? 'deactivate' : 'activate'; ?>">
										<button type="submit" class="esc-button esc-button--ghost esc-button--small">
											<?php echo $is_active ? esc_html__( 'Deactivate', 'es-care-portal' ) : esc_html__( 'Activate', 'es-care-portal' ); ?>
										</button>
									</form>
									<form method="post" action="<?php echo esc_url( $post_url ); ?>" data-esc-confirm="<?php echo esc_attr__( 'Delete this assessment and its questions? Job assignments will be cleared.', 'es-care-portal' ); ?>">
										<?php wp_nonce_field( 'esc_admin_assessment_action', 'esc_assessment_action_nonce' ); ?>
										<input type="hidden" name="action" value="esc_admin_assessment_action">
										<input type="hidden" name="esc_assessment_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
										<input type="hidden" name="esc_assessment_action" value="delete">
										<button type="submit" class="esc-button esc-button--danger esc-button--small"><?php esc_html_e( 'Delete', 'es-care-portal' ); ?></button>
									</form>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>

<section class="esc-card esc-data-panel" style="margin-top:1.25rem;">
	<h3><?php esc_html_e( 'Recent results', 'es-care-portal' ); ?></h3>
	<?php if ( empty( $attempts ) ) : ?>
		<p><?php esc_html_e( 'No assessment submissions yet.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<div class="esc-table-wrap">
			<table class="esc-table">
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
					<?php foreach ( array_slice( $attempts, 0, 50 ) as $attempt ) : ?>
						<tr>
							<td>
								<?php echo esc_html( trim( $attempt->first_name . ' ' . $attempt->last_name ) ); ?>
								<?php if ( ! empty( $attempt->email ) ) : ?>
									<br><span class="esc-muted"><?php echo esc_html( $attempt->email ); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $attempt->title ); ?></td>
							<td><?php echo esc_html( (string) $attempt->score ); ?>%</td>
							<td><?php echo $attempt->passed ? esc_html__( 'Passed', 'es-care-portal' ) : esc_html__( 'Not passed', 'es-care-portal' ); ?></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $attempt->created_at ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>
