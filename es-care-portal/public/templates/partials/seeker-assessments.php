<?php
/**
 * Assessment list.
 *
 * @package ESC_Portal
 *
 * @var object[] $assessments
 * @var object   $user
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Assessment Tests', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Complete the assessments required for jobs you applied to. General tests that are not tied to a specific job also appear here. You can retake a test if you need a better score.', 'es-care-portal' ); ?></p>

	<?php if ( empty( $assessments ) ) : ?>
		<p><?php esc_html_e( 'No assessments are available yet. Apply to a job that requires a test, or check back after staff publish assessments.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<div class="esc-tiles">
			<?php foreach ( $assessments as $item ) : ?>
				<?php $latest = ESC_Portal_Assessments::latest_attempt( $user->id, $item->id ); ?>
				<div class="esc-tile esc-tile--static">
					<span class="esc-tile-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
					</span>
					<strong><?php echo esc_html( $item->title ); ?></strong>
					<p class="esc-muted"><?php echo esc_html( $item->description ); ?></p>
					<?php if ( ! empty( $item->for_jobs ) ) : ?>
						<p class="esc-muted"><?php echo esc_html( sprintf( __( 'Required for: %s', 'es-care-portal' ), implode( ', ', $item->for_jobs ) ) ); ?></p>
					<?php endif; ?>
					<?php if ( $latest ) : ?>
						<p class="esc-muted"><?php echo esc_html( sprintf( __( 'Last score: %1$d%% (%2$s)', 'es-care-portal' ), (int) $latest->score, $latest->passed ? __( 'Passed', 'es-care-portal' ) : __( 'Not passed', 'es-care-portal' ) ) ); ?></p>
					<?php endif; ?>
					<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'take', array( 'assessment' => $item->id ) ) ); ?>"><?php echo $latest ? esc_html__( 'Retake test', 'es-care-portal' ) : esc_html__( 'Start test', 'es-care-portal' ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
