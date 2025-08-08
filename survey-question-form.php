<?php 

$style = $question['survey_question_type'] ? $question['survey_question_type'] : $style; 

switch ($style){
	case 'open' : $color='blue'; $icon='comment'; break;
	case 'number' : $color='purple'; $icon='skill';  break;
	case 'guild-vote' : $color='light-green'; $icon='guild';  break;
	case 'multi-choice' : $color='red'; $icon='list';  break;
	case 'rating' : $color='amber'; $icon='star';  break;
	default : $color='teal'; $icon='check';  break;
}


?>
<li class="accordion-tab relative question <?= $color; ?>-bg-50 " id="question-<?= $qKey; ?>">
	
	<div class="accordion-button padding-10 cursor-pointer relative <?= $color; ?>-bg-100" onClick="activate('#question-<?=  $qKey; ?>');">
		<h5 class="">
			<?= __("ID","bluerabbit").": <strong>$qKey</strong>"; ?> | 
			<?= __("Type","bluerabbit"); ?>: <strong class="font uppercase"><?= $style; ?></strong> 
		</h5>
		<h3>
			 <?= $question['survey_question_text']; ?>
		</h3>
	</div>
	
	<input class="question-id-value" value="<?= $qKey; ?>" type="hidden">
	<input type="hidden" value="<?= $style; ?>" class="question-type">
	<input type="hidden" value="<?= isset($question['survey_question_order']) ? $question['survey_question_order'] : "" ; ?>" class="question-order">
	<div class="accordion-content relative">
		<div class="question-container">
			<div class="question-image">
				<img id="question-<?= $qKey; ?>-img" src="<?= $question['survey_question_image']; ?>" onClick="showWPUpload('question-<?= $qKey; ?>-img','q','survey',<?= $qKey; ?>);">
				<div class="actions">
					<button class="icon-button font _24 sq-40  pink-bg-400 white-color" onClick="clearImage('#question-<?= $qKey; ?>-img','survey',<?= $qKey; ?>);">
						<span class="icon icon-trash"></span>
						<span class="tool-tip bottom">
							<span class="tool-tip-text"><?php _e("Remove image","bluerabbit"); ?></span>
						</span>
					</button>
				</div>
				<div class="background layer cursor-pointer" onClick="showWPUpload('question-<?= $qKey; ?>-img','q','survey',<?= $qKey; ?>);"></div>
			</div>
			<div class="question-content">
				<div class="question-itself">
					<h4 class="grey-600 font _18 w500"><?= __("Question","bluerabbit"); ?>:</h4>
					<textarea id="question-text-<?= $qKey; ?>" rows="3" type="text" placeholder="<?php _e('Question','bluerabbit'); ?>" class="form-ui question-title white-bg" onChange="updateQuestion('survey',<?= $qKey; ?>);"><?= $question['survey_question_text']; ?></textarea>
				</div>
				<div class="question-description">
					<h4 class="grey-600 font _18 w500"><?= __("Description (optional)","bluerabbit"); ?>:</h4>
					<textarea rows="3" id="question-description-<?= $qKey; ?>" placeholder="<?php _e('Describe the question','bluerabbit'); ?>" class="form-ui question-description  white-bg" onChange="updateQuestion('survey',<?= $qKey; ?>);"><?= $question['survey_question_description']; ?></textarea>
				</div>
				<div class="question-config">
				<?php if($style=='rating' ){ ?>
					<?php $range = $question['survey_question_range']  ? $question['survey_question_range'] : 5; ?>
					<h4 class="grey-600 font _18 w500"><?= __("Number of stars","bluerabbit"); ?>:</h4>
					<select onChange="updateQuestion('survey',<?= $qKey; ?>);" class="form-ui" id="question-range-<?= $qKey; ?>">
						<?php for($i=3; $i<=10; $i++){ ?>
							<option value="<?= $i; ?>" <?php if($i == $range) { echo "selected"; } ?> > <?= $i; ?> </option>
						<?php }?>
					</select>
				<?php }elseif($style=='number'){ ?>
					<?php $range = $question['survey_question_range'] !== NULL ? $question['survey_question_range'] : 5; ?>
					<h4 class="grey-600 font _18 w500"><?= __("Max Value","bluerabbit"); ?>:</h4>
					<input type="number" value="<?= $range; ?>" id="question-range-<?= $qKey; ?>" class="form-ui  text-center" onChange="updateQuestion('survey',<?= $qKey; ?>);">
					<h4 class="grey-600 font _18 w500"><?= __("Display as","bluerabbit"); ?>:</h4>
					<select onChange="updateQuestion('survey',<?= $qKey; ?>);" class="form-ui" id="question-display-<?= $qKey; ?>">
						<option value="field" <?php if($question['survey_question_display'] == 'field') { echo "selected"; } ?> > <?= __('Field',"bluerabbit"); ?> </option>
						<option value="dropdown" <?php if($question['survey_question_display'] == 'dropdown') { echo "selected"; } ?> > <?= __('Dropdown',"bluerabbit"); ?> </option>
						<option value="spinner" <?php if($question['survey_question_display'] == 'spinner') { echo "selected"; } ?> > <?= __('Spinner',"bluerabbit"); ?> </option>
					</select>
				<?php }elseif($style=='guild-vote'){ ?>
					<h4 class="grey-600 font _18 w500"><?= __("Players will have to choose one guild to vote for (not theirs)","bluerabbit"); ?></h4>
					<?php if($guild_groups){ ?>
						<div class="input-group w-full font _18">
							<label class="<?= $color; ?>-bg-700 white-color"><?= __("ID","bluerabbit").": $qKey"; ?></label>
							<select onChange="updateQuestion('survey',<?= $qKey; ?>);" class="form-ui" id="question-display-<?= $qKey; ?>">
								<option value="">-<?php _e("Please choose a Guild Group"); ?>-</option>
								<?php foreach($guild_groups as $tg){ ?>
									<option value="<?= $tg->guild_group; ?>" <?php if($tg->guild_group == $question['survey_question_display']) { echo "selected"; } ?> > <?= $tg->guild_group; ?> </option>
								<?php }?>
							</select>
						</div>
					<?php } ?>
				<?php }elseif($style=='multi-choice' || $style=='closed'){ ?>
					<h4 class="grey-600 font _18 w500"><?= __("Question options","bluerabbit"); ?></h4>	
					<ul class="question-options">
						<?php
						$oCount = 0;
						if($question['survey_question_options'] ){
							foreach($question['survey_question_options'] as $oKey=>$option) {
								include (TEMPLATEPATH . '/survey-question-option-form.php');
								$oCount ++;
							}
						}else{
							$options = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_survey_options WHERE survey_question_id=$qKey ", "ARRAY_A");
							foreach($options as $option) {
								$oKey = $option['survey_option_id'];
								include (TEMPLATEPATH . '/survey-question-option-form.php');
								$oCount ++;
							}
						}
						?>
					</ul>
					<div class="highlight padding-10 blue-grey-bg-900 add-option text-center">
						<button class="form-ui green-bg-500" onClick="addOption('survey',<?= $qKey; ?>);">
							<span class="icon icon-add"></span><?php _e('Add Option','bluerabbit'); ?>
						</button>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>


		<div class="highlight text-center blue-grey-bg-900 padding-10">
			<button class="form-ui red-bg-400 white-color" onClick="showOverlay('#confirm-question-<?= $qKey; ?>');">
				<span class="icon icon-trash"></span><?php _e("Remove Question","bluerabbit"); ?>
			</button>
			<div class="confirm-action overlay-layer" id="confirm-question-<?= $qKey; ?>">
				<button class="form-ui white-bg" onClick="removeQuestion(<?= $qKey; ?>,'survey');">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
							<span class="icon icon-trash white-color"></span>
						</span>
						<span class="icon-content">
							<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
							<span class="line font _14 grey-400"><?php _e("You can't undo this","bluerabbit"); ?></span>
						</span>
					</span>
				</button>
				<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
					<span class="icon icon-cancel white-color"></span>
				</button>
			</div>
			<button class="form-ui orange-bg-400 white-color" onClick="showOverlay('#confirm-duplicate-<?= $qKey; ?>');">
				<span class="icon icon-duplicate"></span><?php _e("Duplicate Question","bluerabbit"); ?>
			</button>
			<div class="confirm-action overlay-layer" id="confirm-duplicate-<?= $qKey; ?>">
				<button class="form-ui white-bg" onClick="duplicateQuestion(<?= $qKey; ?>,'survey');">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  orange-bg-400 icon-sm">
							<span class="icon icon-duplicate white-color"></span>
						</span>
						<span class="icon-content">
							<span class="line orange-400 font _18 w900"><?php _e("Duplicate Question?","bluerabbit"); ?></span>
						</span>
					</span>
				</button>
				<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
					<span class="icon icon-cancel white-color"></span>
				</button>
			</div>
		</div>
	</div>
</li>

