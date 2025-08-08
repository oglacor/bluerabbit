<div class="accordion-tab relative question" id="accordion-tab-question-<?=  $qKey; ?>">
	<div class="accordion-button padding-10 cursor-pointer relative" onClick="activate('#accordion-tab-question-<?=  $qKey; ?>');animateScroll('#accordion-tab-question-<?=  $qKey; ?>');">
		<h3 class="layer base relative"><?= "Q #$qCount: "; ?> <span class="question-text"><?= $question['title']; ?></span></h3>
		<div class="layer background absolute red-bg-100"></div>
	</div>
	<div class="accordion-content relative">
		<table class="table w-full" cellpadding="0" id="question-<?=  $qKey; ?>">
			<tbody>
				<tr class="question">
					<td><?= "Q #$qCount"; ?> </td>
					<td>
						<div class="highlight padding-10 brown-bg-50">
							<textarea id="question-text-<?=  $qKey; ?>" placeholder="<?php _e('Question Text','bluerabbit'); ?>" class="form-ui grey-bg-50 border border-all blue-border-700 border-2" rows="3" maxlength="800" onChange="updateQuestion('challenge',<?=  $qKey; ?>);"><?=  $question['title']; ?></textarea>
						</div>
					</td>
				</tr>
				<tr>
					<td><?= __("Media","bluerabbit"); ?> </td>
					<td>
						<?php insertMultimediaItem('question-'.$qKey.'-img', $question['image'], $qKey); ?>
					</td>
				</tr>
				<tr>
					<td><?= __("Options","bluerabbit"); ?> </td>
					<td>
						<div class="content grey-bg-50">
							<table class="table ">
								<thead>
									<tr>
										<td><?=__("Right/Wrong","bluerabbit");?></td>
										<td><?=__("Image","bluerabbit");?></td>
										<td><?=__("Content","bluerabbit");?></td>
										<td><?=__("Delete","bluerabbit");?></td>
									</tr>
								</thead>
								<tbody class="question-options">
									<?php
									$oCount = 0;

									if($question['answers'] ){
										foreach($question['answers'] as $option) {
											$oKey = $option['answer_id'];
											include (TEMPLATEPATH . '/challenge-question-option-form.php');
											$oCount ++;
										}
									}else{
										$options = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=$qKey ", "ARRAY_A");
										foreach($options as  $option) {
											$oKey = $option['answer_id'];
											include (TEMPLATEPATH . '/challenge-question-option-form.php');
											$oCount ++;
										}
									}
									?>
								</tbody>
							</table>
							<div class="highlight padding-10 green-bg-50 add-option text-center">
								<button class="form-ui light-green-bg-400" onClick="addOption('challenge',<?=  $qKey; ?>);">
									<span class="icon icon-add"></span><?php _e('Add Option','bluerabbit'); ?>
								</button>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="relative">
						<div class="highlight padding-10 red-bg-50 text-right padding-0 question-actions">
							<div class="highlight-cell padding-10 inline-block">
								<button class="form-ui red-bg-400 white-color remove-question" onClick="showOverlay('#confirm-question-<?=  $qKey; ?>');">
									<span class="icon icon-trash"></span><?php _e("Remove Question","bluerabbit"); ?>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-question-<?=  $qKey; ?>">
									<button class="form-ui white-bg" onClick="removeQuestion(<?=  $qKey; ?>,'challenge');">
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
							</div>
							<div class="highlight-cell padding-10 inline-block">
								<button class="form-ui orange-bg-400 white-color duplicate-question" onClick="showOverlay('#confirm-duplicate-<?=  $qKey; ?>');">
									<span class="icon icon-duplicate"></span><?php _e("Duplicate Question","bluerabbit"); ?>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-duplicate-<?=  $qKey; ?>">
									<button class="form-ui white-bg" onClick="duplicateQuestion(<?=  $qKey; ?>,'challenge');">
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
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>