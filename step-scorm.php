<?php
/**
 * step-scorm.php — Player-facing SCORM 1.2 step
 *
 * Renders a full-width 16:9 iframe pointing to the uploaded SCORM package.
 * window.API (scorm-api.js) sits on the parent page; Genially content finds it
 * via window.parent.API per the SCORM 1.2 API discovery algorithm.
 *
 * PHP variables in scope (from quest.php / page-quest.php):
 *   $step         – current step row
 *   $steps        – all steps for this quest
 *   $i            – current step index (0-based)
 *   $q            – quest row
 *   $adventure    – adventure row  ($adventure->adventure_id)
 *   $adv_parent_id
 */

$scorm_settings  = $step->step_settings ? json_decode( $step->step_settings, true ) : [];
$launch_url      = isset( $scorm_settings['scorm_launch_url'] ) ? $scorm_settings['scorm_launch_url'] : '';
$is_last         = ( $i >= count( $steps ) - 1 );
$next_step_order = $is_last ? 0 : (int) $steps[ $i + 1 ]->step_order;

$user_id         = get_current_user_id();
$lesson_status   = get_user_meta( $user_id, "br_scorm_lesson_status_{$step->step_id}",   true ) ?: 'not attempted';
$lesson_location = get_user_meta( $user_id, "br_scorm_lesson_location_{$step->step_id}", true ) ?: '';
$suspend_data    = get_user_meta( $user_id, "br_scorm_suspend_data_{$step->step_id}",    true ) ?: '';
$scorm_done      = ( $lesson_status === 'completed' || $lesson_status === 'passed' );
?>
<div class="step <?= $i == 0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include( TEMPLATEPATH . '/steps-background.php' ); ?>
	<div class="step-content-container scorm-step-container">

		<?php if ( $launch_url ) : ?>
			<div class="scorm-wrapper"
			     data-step-id="<?= esc_attr( $step->step_id ); ?>"
			     data-quest-id="<?= esc_attr( $q->quest_id ); ?>"
			     data-adventure-id="<?= esc_attr( $adventure->adventure_id ); ?>"
			     data-next-step="<?= esc_attr( $next_step_order ); ?>">
				<iframe
					id="scorm-frame-<?= $step->step_id; ?>"
					class="scorm-frame"
					src="about:blank"
					data-src="<?= esc_url( $launch_url ); ?>"
					allowfullscreen
					allow="fullscreen">
				</iframe>
			</div>
		<?php else : ?>
			<div class="dialogue-box">
				<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
				<div class="edge-left"></div>
				<div class="center">
					<p><?= __( 'SCORM package not yet uploaded.', 'bluerabbit' ); ?></p>
				</div>
				<div class="edge-right"></div>
				<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
			</div>
		<?php endif; ?>

		<?php if ($scorm_done) { ?>
		<div class="br-step-feedback br-step-feedback-success">
			<span class="icon icon-check"></span> <?= __("You have completed this content", "bluerabbit"); ?>
		</div>
		<?php } ?>

		<?php include( TEMPLATEPATH . '/step-nav-button-back.php' ); ?>

		<div id="scorm-next-<?= $step->step_id ?>" class="scorm-next-wrapper<?= $scorm_done ? ' active' : '' ?>">
			<?php include( TEMPLATEPATH . '/step-nav-button-next.php' ); ?>
		</div>
	</div>
</div>

<script>
window.brScormData = window.brScormData || {};
window.brScormData[<?= (int) $step->step_id ?>] = {
	lessonStatus:   <?= json_encode( $lesson_status ); ?>,
	lessonLocation: <?= json_encode( $lesson_location ); ?>,
	suspendData:    <?= json_encode( $suspend_data ); ?>,
	stepId:         <?= (int) $step->step_id ?>,
	questId:        <?= (int) $q->quest_id ?>,
	adventureId:    <?= (int) $adventure->adventure_id ?>,
	nextStep:       <?= (int) $next_step_order ?>,
	nonce:          <?= json_encode( wp_create_nonce( 'br_scorm_data_' . $user_id ) ); ?>
};
</script>
