<?php
/**
 * step-case-study-html.php — Player-facing embedded HTML case study step
 *
 * Renders a tall iframe pointing at a self-contained, vendor-authored HTML
 * activity (score/questions all client-side). Progress and completion are
 * reported to the parent page over postMessage by a bridge script baked into
 * the launch file itself — see js/casestudy-api.js for the listener side.
 *
 * PHP variables in scope (from quest.php / page-quest.php):
 *   $step         – current step row
 *   $steps        – all steps for this quest
 *   $i            – current step index (0-based)
 *   $q            – quest row
 *   $adventure    – adventure row  ($adventure->adventure_id)
 */

$cs_settings = $step->step_settings ? json_decode( $step->step_settings, true ) : [];
$launch_url  = isset( $cs_settings['launch_url'] ) ? $cs_settings['launch_url'] : '';
$pass_score  = isset( $cs_settings['pass_score'] ) && $cs_settings['pass_score'] !== '' ? (int) $cs_settings['pass_score'] : 14;
$total       = isset( $cs_settings['total'] ) && $cs_settings['total'] !== '' ? (int) $cs_settings['total'] : 20;
$is_last     = ( $i >= count( $steps ) - 1 );
$next_step_order = $is_last ? 0 : (int) $steps[ $i + 1 ]->step_order;

$user_id     = get_current_user_id();
$done_at     = get_user_meta( $user_id, "br_casestudy_done_{$step->step_id}", true );
$saved_state = get_user_meta( $user_id, "br_casestudy_state_{$step->step_id}", true );
$saved_state = $saved_state ? json_decode( $saved_state, true ) : null;
$cs_done     = ! empty( $done_at );
$cs_score    = get_user_meta( $user_id, "br_casestudy_score_{$step->step_id}", true );

global $wpdb;
$cs_attempts = (int) $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM {$wpdb->prefix}br_casestudy_attempts WHERE player_id=%d AND step_id=%d AND adventure_id=%d",
	$user_id, $step->step_id, $adventure->adventure_id
) );
?>
<div class="step <?= $i == 0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include( TEMPLATEPATH . '/steps-background.php' ); ?>
	<div class="step-content-container case-study-step-container">

		<?php if ( $launch_url ) : ?>
			<div class="casestudy-wrapper"
			     data-step-id="<?= esc_attr( $step->step_id ); ?>"
			     data-quest-id="<?= esc_attr( $q->quest_id ); ?>"
			     data-adventure-id="<?= esc_attr( $adventure->adventure_id ); ?>"
			     data-next-step="<?= esc_attr( $next_step_order ); ?>">
				<iframe
					id="casestudy-frame-<?= $step->step_id; ?>"
					class="casestudy-frame"
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
					<p><?= __( 'Case study not yet configured.', 'bluerabbit' ); ?></p>
				</div>
				<div class="edge-right"></div>
				<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
			</div>
		<?php endif; ?>

		<?php if ( $cs_done ) { ?>
		<div class="br-step-feedback br-step-feedback-success">
			<span class="icon icon-check"></span> <?= __( "You have completed this content", "bluerabbit" ); ?>
			<?php if ( $cs_score !== '' ) { ?>
				<span class="br-text-12-muted"><?= sprintf( __( "Best score: %d%%", "bluerabbit" ), (int) $cs_score ); ?></span>
			<?php } ?>
		</div>
		<?php } ?>

		<?php if ( $launch_url ) { ?>
		<div class="casestudy-retake">
			<button type="button" class="br-btn" onClick="brCaseStudyRetake(<?= (int) $step->step_id; ?>);">
				<span class="icon icon-rotate"></span>
				<?= $cs_done ? __( "Take it again", "bluerabbit" ) : __( "Start over", "bluerabbit" ); ?>
			</button>
			<span class="br-form-hint">
				<?php if ( $cs_attempts ) { ?>
					<?= sprintf( _n( "%d attempt so far. Retaking never removes a previous result.", "%d attempts so far. Retaking never removes a previous result.", $cs_attempts, "bluerabbit" ), $cs_attempts ); ?>
				<?php } else { ?>
					<?= __( "You can take this as many times as you like.", "bluerabbit" ); ?>
				<?php } ?>
			</span>
		</div>
		<?php } ?>

		<?php include( TEMPLATEPATH . '/step-nav-button-back.php' ); ?>

		<div id="casestudy-next-<?= $step->step_id ?>" class="scorm-next-wrapper<?= $cs_done ? ' active' : '' ?>">
			<?php include( TEMPLATEPATH . '/step-nav-button-next.php' ); ?>
		</div>
	</div>
</div>

<script>
window.brCaseStudyData = window.brCaseStudyData || {};
window.brCaseStudyData[<?= (int) $step->step_id ?>] = {
	done:        <?= json_encode( $cs_done ); ?>,
	savedState:  <?= json_encode( $saved_state ); ?>,
	passScore:   <?= (int) $pass_score ?>,
	total:       <?= (int) $total ?>,
	stepId:      <?= (int) $step->step_id ?>,
	questId:     <?= (int) $q->quest_id ?>,
	adventureId: <?= (int) $adventure->adventure_id ?>,
	nextStep:    <?= (int) $next_step_order ?>,
	nonce:       <?= json_encode( wp_create_nonce( 'br_casestudy_data_' . $user_id ) ); ?>
};
</script>
