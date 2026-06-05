<?php
$q_has_media   = !empty($qs[$i]->question_image);
$q_mime        = $q_has_media ? wp_check_filetype($qs[$i]->question_image) : ['type' => ''];
$q_is_image    = strstr($q_mime['type'], 'image');
$q_is_video    = strstr($q_mime['type'], 'video');
$q_is_audio    = strstr($q_mime['type'], 'audio');

$answer_image_count = 0;
$answer_total       = 0;
foreach($answers as $a){
	if($a->question_id == $qs[$i]->question_id){
		$answer_total++;
		if(!empty($a->answer_image)) $answer_image_count++;
	}
}
$opts_all_images = ($answer_total > 0 && $answer_image_count === $answer_total);
$opts_1col       = $q_has_media && !$opts_all_images;

$opts_grid_class = 'question-options';
if($opts_all_images){
	$opts_grid_class .= ($answer_total <= 2) ? ' opts-images opts-images-2col' : ' opts-images';
} elseif($opts_1col){
	$opts_grid_class .= ' opts-1col';
}
?>

<div class="challenge-question layer base <?= $i==0 ? 'current' : ''; ?>" id="question-<?= $qs[$i]->question_id; ?>">

	<h3 class="font _10 w400 text-center">
		<?= __("Question","bluerabbit"); ?> <strong><?= ($i+1); ?></strong> / <?= $qtd; ?>
	</h3>

	<input class="question-type" type="hidden" value="<?= $qs[$i]->question_type; ?>">

	<?php if($q_has_media){ ?>
	<div class="question-media-wrap">
		<?php if($q_is_image){ ?>
			<span class="media-type-badge"><span class="icon icon-image"></span> <?= __("Image","bluerabbit"); ?></span>
			<img src="<?= $qs[$i]->question_image; ?>" class="question-image" style="margin:0;border-radius:0;" alt="">

		<?php }elseif($q_is_video){ ?>
			<span class="media-type-badge"><span class="icon icon-video"></span> <?= __("Video","bluerabbit"); ?></span>
			<video class="question-image" controls style="margin:0;border-radius:0;">
				<source src="<?= $qs[$i]->question_image; ?>">
			</video>

		<?php }elseif($q_is_audio){ ?>
			<span class="media-type-badge"><span class="icon icon-volume"></span> <?= __("Audio","bluerabbit"); ?></span>
			<div class="hud-audio-player" id="audio-player-<?= $qs[$i]->question_id; ?>">
				<audio id="audio-el-<?= $qs[$i]->question_id; ?>" src="<?= $qs[$i]->question_image; ?>" preload="metadata"></audio>
				<div class="player-top">
					<button class="play-btn" onClick="hudToggleAudio('<?= $qs[$i]->question_id; ?>')" aria-label="<?= __('Play audio','bluerabbit'); ?>" id="play-icon-<?= $qs[$i]->question_id; ?>" data-state="play">
						<svg class="svg-play" viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<polygon points="6,3 20,12 6,21" fill="#1cc2eb"/>
						</svg>
						<svg class="svg-pause" viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display:none">
							<rect x="5" y="3" width="4" height="18" rx="1" fill="#1cc2eb"/>
							<rect x="15" y="3" width="4" height="18" rx="1" fill="#1cc2eb"/>
						</svg>
					</button>
					<div class="player-info">
						<div class="player-title"><?= __("Listen carefully!","bluerabbit"); ?></div>
						<div class="player-hint">// <?= __("press play to hear the question","bluerabbit"); ?></div>
					</div>
					<div class="waveform" id="waveform-<?= $qs[$i]->question_id; ?>">
						<?php for($b=0;$b<13;$b++){ ?><div class="bar" style="height:<?= rand(6,24); ?>px;animation-delay:<?= ($b*.05); ?>s"></div><?php } ?>
					</div>
				</div>
				<div class="player-track">
					<div class="player-progress" id="progress-<?= $qs[$i]->question_id; ?>"></div>
				</div>
				<div class="player-times">
					<span id="cur-time-<?= $qs[$i]->question_id; ?>">0:00</span>
					<span id="dur-time-<?= $qs[$i]->question_id; ?>">0:00</span>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if($qs[$i]->question_title){ ?>
	<h1 class="question-title"><?= $qs[$i]->question_title; ?></h1>
	<?php } ?>

	<ul class="<?= $opts_grid_class; ?>">
		<?php
		$oCount = 0;
		$optLetters = ['A','B','C','D','E','F'];
		foreach($answers as $a){
			if($a->question_id == $qs[$i]->question_id){
				$oCount++;
				$oLetter = isset($optLetters[$oCount-1]) ? $optLetters[$oCount-1] : $oCount;
				$is_image_opt = !empty($a->answer_image);
				include (TEMPLATEPATH . '/challenge-question-option.php');
			}
		}
		?>
	</ul>

</div>
