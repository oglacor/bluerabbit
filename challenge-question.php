<div class="challenge-question layer base <?= $i==0 ? 'current': ''; ?>" id="question-<?php echo $qs[$i]->question_id; ?>">
	<h3 class="font _18 w100 text-center"><?= __("Question","bluerabbit")." <strong>".($i+1)."</strong> / ".$qtd; ?></h3>
	<input class="question-type" type="hidden" value="<?php echo $qs[$i]->question_type; ?>">
	<h1 class="question-title"><?= $qs[$i]->question_title; ?></h1>
	<?php if($qs[$i]->question_image) { ?>
		<?php $mime = (wp_check_filetype($qs[$i]->question_image));?>
	
		<?php if(strstr($mime['type'], "image")){ ?>
			<img src="<?= $qs[$i]->question_image; ?>" class="question-image">
		<?php }elseif(strstr($mime['type'], "video")){ ?>
			<video class="question-image" controls>
				<source src="<?= $qs[$i]->question_image; ?>">
			</video>
		<?php }elseif(strstr($mime['type'], "audio")){ ?>
			<audio class="question-image" controls>
				<source src="<?= $qs[$i]->question_image; ?>">
			</audio>
		<?php }?> 
	<?php } ?>
	<ul class="question-options">
		<?php 
		$oCount = 0;
		foreach($answers as $a){ 
			if($a->question_id == $qs[$i]->question_id){
				$oCount ++;
				include (TEMPLATEPATH . '/challenge-question-option.php');
			} 
		}
		?>
	</ul>
</div>
