<?php
/**
 * Portal admin create / edit assessment.
 *
 * @package ESC_Portal
 *
 * @var object|null $assessment
 * @var object[]    $questions
 */

defined( 'ABSPATH' ) || exit;

$assessment = isset( $assessment ) ? $assessment : null;
$questions  = isset( $questions ) && is_array( $questions ) ? $questions : array();
$aid        = $assessment ? (int) $assessment->id : 0;
$title      = $assessment ? $assessment->title : '';
$desc       = $assessment ? $assessment->description : '';
$pass       = $assessment ? (int) $assessment->pass_score : 70;
$status     = $assessment ? $assessment->status : 'active';

if ( empty( $questions ) ) {
	$questions = array(
		(object) array(
			'question' => '',
			'choices'  => array( '', '', '', '' ),
			'correct'  => 0,
		),
	);
}
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php echo $aid ? esc_html__( 'Edit assessment', 'es-care-portal' ) : esc_html__( 'Create assessment', 'es-care-portal' ); ?></h2>
	<a class="esc-button esc-button--ghost" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'assessments' ) ); ?>"><?php esc_html_e( 'Back to assessments', 'es-care-portal' ); ?></a>
</div>
<p class="esc-dash-copy"><?php esc_html_e( 'Add a title, passing score, and multiple-choice questions. After saving, assign this test to a job from the job form.', 'es-care-portal' ); ?></p>

<form class="esc-form esc-card esc-assessment-editor" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-esc-assessment-editor>
	<?php wp_nonce_field( 'esc_admin_save_assessment', 'esc_assessment_admin_nonce' ); ?>
	<input type="hidden" name="action" value="esc_admin_save_assessment">
	<input type="hidden" name="esc_assessment_id" value="<?php echo esc_attr( (string) $aid ); ?>">

	<p class="esc-field">
		<label for="esc_assessment_title"><?php esc_html_e( 'Title', 'es-care-portal' ); ?> <span class="esc-req">*</span></label>
		<input type="text" id="esc_assessment_title" name="esc_assessment_title" required value="<?php echo esc_attr( $title ); ?>">
	</p>
	<p class="esc-field">
		<label for="esc_assessment_description"><?php esc_html_e( 'Description', 'es-care-portal' ); ?></label>
		<textarea id="esc_assessment_description" name="esc_assessment_description" rows="3"><?php echo esc_textarea( $desc ); ?></textarea>
	</p>
	<div class="esc-grid">
		<p class="esc-field">
			<label for="esc_pass_score"><?php esc_html_e( 'Passing score (%)', 'es-care-portal' ); ?></label>
			<input type="number" min="1" max="100" id="esc_pass_score" name="esc_pass_score" value="<?php echo esc_attr( (string) $pass ); ?>">
		</p>
		<p class="esc-field">
			<label for="esc_assessment_status"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></label>
			<select id="esc_assessment_status" name="esc_assessment_status">
				<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'es-care-portal' ); ?></option>
				<option value="inactive" <?php selected( $status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'es-care-portal' ); ?></option>
			</select>
		</p>
	</div>

	<div class="esc-assessment-questions" data-esc-questions>
		<div class="esc-dash-toolbar" style="margin-top:0.5rem;">
			<h3 class="esc-dash-title esc-dash-title--sub"><?php esc_html_e( 'Questions', 'es-care-portal' ); ?></h3>
			<button type="button" class="esc-button esc-button--small" data-esc-add-question><?php esc_html_e( 'Add question', 'es-care-portal' ); ?></button>
		</div>

		<?php foreach ( $questions as $index => $q ) : ?>
			<?php
			$choices = isset( $q->choices ) && is_array( $q->choices ) ? $q->choices : array( '', '', '', '' );
			$choices = array_pad( array_slice( $choices, 0, 4 ), 4, '' );
			$correct = isset( $q->correct ) ? (int) $q->correct : 0;
			?>
			<fieldset class="esc-assessment-q" data-esc-question>
				<legend><?php echo esc_html( sprintf( __( 'Question %d', 'es-care-portal' ), (int) $index + 1 ) ); ?></legend>
				<p class="esc-field">
					<label><?php esc_html_e( 'Prompt', 'es-care-portal' ); ?></label>
					<textarea name="esc_q[<?php echo esc_attr( (string) $index ); ?>][question]" rows="2" required><?php echo esc_textarea( isset( $q->question ) ? $q->question : '' ); ?></textarea>
				</p>
				<div class="esc-assessment-choices">
					<?php for ( $c = 0; $c < 4; $c++ ) : ?>
						<label class="esc-assessment-choice">
							<input type="radio" name="esc_q[<?php echo esc_attr( (string) $index ); ?>][correct]" value="<?php echo esc_attr( (string) $c ); ?>" <?php checked( $correct, $c ); ?>>
							<span><?php echo esc_html( sprintf( __( 'Choice %d (correct if selected)', 'es-care-portal' ), $c + 1 ) ); ?></span>
							<input type="text" name="esc_q[<?php echo esc_attr( (string) $index ); ?>][choices][<?php echo esc_attr( (string) $c ); ?>]" value="<?php echo esc_attr( $choices[ $c ] ); ?>" placeholder="<?php esc_attr_e( 'Answer text', 'es-care-portal' ); ?>">
						</label>
					<?php endfor; ?>
				</div>
				<p class="esc-actions">
					<button type="button" class="esc-button esc-button--danger esc-button--ghost esc-button--small" data-esc-remove-question><?php esc_html_e( 'Remove question', 'es-care-portal' ); ?></button>
				</p>
			</fieldset>
		<?php endforeach; ?>
	</div>

	<template id="esc-assessment-q-template">
		<fieldset class="esc-assessment-q" data-esc-question>
			<legend><?php esc_html_e( 'Question', 'es-care-portal' ); ?></legend>
			<p class="esc-field">
				<label><?php esc_html_e( 'Prompt', 'es-care-portal' ); ?></label>
				<textarea name="esc_q[__i__][question]" rows="2" required></textarea>
			</p>
			<div class="esc-assessment-choices">
				<?php for ( $c = 0; $c < 4; $c++ ) : ?>
					<label class="esc-assessment-choice">
						<input type="radio" name="esc_q[__i__][correct]" value="<?php echo esc_attr( (string) $c ); ?>" <?php echo 0 === $c ? 'checked' : ''; ?>>
						<span><?php echo esc_html( sprintf( __( 'Choice %d (correct if selected)', 'es-care-portal' ), $c + 1 ) ); ?></span>
						<input type="text" name="esc_q[__i__][choices][<?php echo esc_attr( (string) $c ); ?>]" value="" placeholder="<?php esc_attr_e( 'Answer text', 'es-care-portal' ); ?>">
					</label>
				<?php endfor; ?>
			</div>
			<p class="esc-actions">
				<button type="button" class="esc-button esc-button--danger esc-button--ghost esc-button--small" data-esc-remove-question><?php esc_html_e( 'Remove question', 'es-care-portal' ); ?></button>
			</p>
		</fieldset>
	</template>

	<p class="esc-actions">
		<button type="submit" class="esc-button"><?php echo $aid ? esc_html__( 'Update assessment', 'es-care-portal' ) : esc_html__( 'Save assessment', 'es-care-portal' ); ?></button>
		<a class="esc-button esc-button--ghost" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'assessments' ) ); ?>"><?php esc_html_e( 'Cancel', 'es-care-portal' ); ?></a>
	</p>
</form>
