<?php $settings = $step->step_settings ? json_decode($step->step_settings, true) : []; $audio_url = $settings['url'] ?? ''; ?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-audio">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>
				<?php if ($audio_url) { ?>
				<div class="hud-audio-player" id="audio-player-step-<?= $step->step_id; ?>">
					<audio id="audio-el-step-<?= $step->step_id; ?>" src="<?= esc_attr($audio_url); ?>" preload="metadata"></audio>
					<div class="player-top">
						<button class="play-btn" onClick="hudToggleAudio('step-<?= $step->step_id; ?>')" aria-label="<?= __('Play audio','bluerabbit'); ?>" id="play-icon-step-<?= $step->step_id; ?>" data-state="play">
							<svg class="svg-play" viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<polygon points="6,3 20,12 6,21" fill="#1cc2eb"/>
							</svg>
							<svg class="svg-pause" viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<rect x="5" y="3" width="4" height="18" rx="1" fill="#1cc2eb"/>
								<rect x="15" y="3" width="4" height="18" rx="1" fill="#1cc2eb"/>
							</svg>
						</button>
						<div class="player-info">
							<div class="player-title"><?= __("Listen carefully!","bluerabbit"); ?></div>
							<div class="player-hint">// <?= __("press play to hear the audio","bluerabbit"); ?></div>
						</div>
						<div class="waveform" id="waveform-step-<?= $step->step_id; ?>">
							<?php for($b=0;$b<13;$b++){ ?><div class="bar" style="--bar-h:<?= rand(6,24); ?>px;--bar-delay:<?= ($b*.05); ?>s"></div><?php } ?>
						</div>
					</div>
					<div class="player-track">
						<div class="player-progress" id="progress-step-<?= $step->step_id; ?>"></div>
					</div>
					<div class="player-times">
						<span id="cur-time-step-<?= $step->step_id; ?>">0:00</span>
						<span id="dur-time-step-<?= $step->step_id; ?>">0:00</span>
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
	</div>
</div>
