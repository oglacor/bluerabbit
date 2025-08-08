<li class="question" id="question-<?php echo $key; ?>">
	<div class="highlight padding-10 teal-bg-50 <?php echo !$q['image'] ? 'sticky' : ''; ?>">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40  teal-bg-400 white-color">
				<span class="icon icon-survey"></span>
			</span>
			<span class="icon-content">
				<span class="line font _24 teal-800"><?php echo $q['text']; ?></span>
				<span class="line font _14 grey-600"> <?php echo __("Question","bluerabbit")." ".($count+1); ?></span>
			</span>
		</span>
		<?php if($q['image']) { ?>
			<div class="question-image">
				<img src="<?php echo $q['image']; ?>">
			</div>
		<?php } ?>
	</div>
	<?php $nonceStr = "{$q['text']}-$key"; ?>
	<input type="hidden" id="sq-nonce-<?php echo $key; ?>" value="<?php echo wp_create_nonce($nonceStr); ?>"/>
	<div class="content grey-bg-50">
		<?php if($q['survey_question_type']=='open'){ ?>
			<div class="input-group w-full font _30">
				<label class="teal-bg-400"><?php _e("Your answer","bluerabbit"); ?></label>
				<input type="text" class="form-ui" value="<?php echo $q['survey_answer_value']; ?>" id="question-answer-value-<?php echo $key; ?>" onChange="submitSurveyAnswer(<?php echo $key; ?>);">
			</div>
		<?php }elseif($q['survey_question_type']=='rating'){ ?>
			<input type="hidden" class="form-ui" value="<?php echo $q['survey_answer_value']; ?>" id="question-answer-value-<?php echo $key; ?>">
			<div class="highlight padding-10 grey-bg-100 text-center">
				<span class="rating">
					<?php _e("Rating","bluerabbit"); ?>
					<button class="icon-button font _24 sq-40  star-1 <?php echo $q['survey_answer_value'] >= 1 ? 'amber-bg-400' : ''; ?>" onClick="updateQuestionValue('<?php echo $key; ?>',1);">
						<span class="icon icon-star"></span>
						<span class="tool-tip top">
							<span class="tool-tip-text"><?php _e("1 STAR"); ?></span>
						</span>
					</button>
					<button class="icon-button font _24 sq-40  star-2 <?php echo $q['survey_answer_value'] >= 2 ? 'amber-bg-400' : ''; ?>" onClick="updateQuestionValue('<?php echo $key; ?>',2);">
						<span class="icon icon-star"></span>
						<span class="tool-tip top">
							<span class="tool-tip-text"><?php _e("2 STARS"); ?></span>
						</span>
					</button>
					<button class="icon-button font _24 sq-40  star-3 <?php echo $q['survey_answer_value'] >= 3 ? 'amber-bg-400' : ''; ?>" onClick="updateQuestionValue('<?php echo $key; ?>',3);">
						<span class="icon icon-star"></span>
						<span class="tool-tip top">
							<span class="tool-tip-text"><?php _e("3 STARS"); ?></span>
						</span>
					</button>
					<button class="icon-button font _24 sq-40  star-4 <?php echo $q['survey_answer_value'] >= 4 ? 'amber-bg-400' : ''; ?>" onClick="updateQuestionValue('<?php echo $key; ?>',4);">
						<span class="icon icon-star"></span>
						<span class="tool-tip top">
							<span class="tool-tip-text"><?php _e("4 STARS"); ?></span>
						</span>
					</button>
					<button class="icon-button font _24 sq-40  star-5 <?php echo $q['survey_answer_value'] >= 5 ? 'amber-bg-400' : ''; ?>" onClick="updateQuestionValue('<?php echo $key; ?>',5);">
						<span class="icon icon-star"></span>
						<span class="tool-tip top">
							<span class="tool-tip-text"><?php _e("5 STARS"); ?></span>
						</span>
					</button>
				</span>
			</div>
		<?php }else{ ?>
			<ul class="question-options">
				<?php 
				$oCount = 0;
				foreach($q['options'] as $oKey=>$o) {
					$oCount ++;
					include (TEMPLATEPATH . '/survey-question-option.php');
				}
				?>
			</ul>
		<?php } ?>
		<br class="clear">
	</div>
</li>
