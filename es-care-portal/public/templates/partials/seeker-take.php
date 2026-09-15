<?php
/**
 * Take an assessment.
 *
 * @package ESC_Portal
 *
 * @var object   $assessment
 * @var object[] $questions
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php echo esc_html( $assessment->title ); ?></h2>
	<p class="esc-dash-copy"><?php echo esc_html( $assessment->description ); ?></p>
	<p class="esc-muted"><?php echo esc_html( sprintf( __( 'Passing score: %d%%', 'es-care-portal' ), (int) $assessment->pass_score ) ); ?></p>

	<form class="esc-form esc-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'esc_submit_assessment', 'esc_assessment_nonce' ); ?>
		<input type="hidden" name="action" value="esc_submit_assessment">
		<input type="hidden" name="esc_assessment_id" value="<?php echo esc_attr( (string) $assessment->id ); ?>">

		<?php foreach ( $questions as $index => $question ) : ?>
			<fieldset class="esc-fieldset">
				<legend><?php echo esc_html( sprintf( __( '%1$d. %2$s', 'es-care-portal' ), $index + 1, $question->question ) ); ?></legend>
				<?php foreach ( $question->choices as $choice_i => $choice ) : ?>
					<label class="esc-check">
						<input type="radio" name="esc_answer[<?php echo esc_attr( (string) $question->id ); ?>]" value="<?php echo esc_attr( (string) $choice_i ); ?>" required>
						<?php echo esc_html( $choice ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>

		<button type="submit" class="esc-button"><?php esc_html_e( 'Submit assessment', 'es-care-portal' ); ?></button>
	</form>
</section>
