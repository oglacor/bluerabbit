<?php 
$style = $q['survey_question_type'] ? $q['survey_question_type'] : $style; 

switch ($style){
	case 'open' : $color='blue'; $icon='comment'; break;
	case 'number' : $color='purple'; $icon='skill';  break;
	case 'guild-vote' : $color='light-green'; $icon='guild';  break;
	case 'multi-choice' : $color='red'; $icon='list';  break;
	case 'rating' : $color='amber'; $icon='star';  break;
	default : $color='teal'; $icon='check';  break;
}
?>
<li class="question" id="question-<?=  $key; ?>">
	<div class="highlight padding-10 <?=$color; ?>-bg-50 <?=  !$q['image'] ? 'sticky' : ''; ?>">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40  <?=$color; ?>-bg-400 white-color">
				<span class="icon icon-<?=$icon; ?>"></span>
			</span>
			<span class="icon-content">
				<span class="line font _14 grey-600"> <?=  __("Question","bluerabbit")." ".($key); ?></span>
				<span class="line font _24 <?=$color; ?>-800"><?=  $q['text']; ?></span>
				<span class="line font _18 grey-600"><?=  $q['survey_question_description']; ?></span>
			</span>
		</span>
		<span class="highlight-cell pull-right">
			<?=  __("Total Answers","bluerabbit").": ".($answers[$key]['total']);?>
		</span>
		<?php if($q['image']) { ?>
			<div class="question-image">
				<img src="<?=  $q['image']; ?>">
			</div>
		<?php } ?>
	</div>
	<?php $nonceStr = "{$q['text']}-$key"; ?>
	<input type="hidden" id="sq-nonce-<?=  $key; ?>" value="<?=  wp_create_nonce($nonceStr); ?>"/>
	<div class="content grey-bg-50">
		<?php if($q['survey_question_type']=='open'){ ?>
			<ol>
			<?php foreach($answers[$key]['values'] as $aKey=>$answer){ ?>
				<li> <?=  $answer; ?></li>
			<?php } ?>	
			</ol>
			<?php
				/* foreach($answers[$key]['values'] as $aKey=>$answer){ ?>
				<tr>
					<td> <?=  $s->achievement_name; ?></td>
					<td> <?=  $key; ?></td>
					<td> <?=  $q['survey_question_description']; ?></td>
					<td> <?=  $q['text']; ?></td>
					<td> <?=  $answer; ?></td>
				</tr>	
			<?php } */ 
			?>
		<?php }elseif($q['survey_question_type']=='rating'){ ?>
			<table class="table small">
				<?php 
				$rating=[];
				foreach($answers[$key]['values'] as $answer){
					$rating[$answer]++;
				} 
				?>	
				<thead>
					<tr>
						<td class="text-center"><?= __("Stars","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Answers","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php for ($i=0; $i<=$q['range']; $i++){ ?>
						<tr class="font _16 w300">
							<td class="text-center">
								<?php
									if($i==0){
										_e("N / A","bluerabbit");
									}else{
										echo ($i)." ".__("Stars"); 
									}
								?>
							</td>
							<td class="text-center font w900"><?=  $rating[$i] ? $rating[$i] : 0; ?></td>
						</tr>				
					<?php } ?>
				</tbody>
			</table>
		<?php }elseif($q['survey_question_type']=='number'){ ?>
			<table class="table small">
				<?php 
				$rating=[];
				foreach($answers[$key]['values'] as $answer){
					$rating[$answer]++;
				}
				$absValue = 0;
				?>	
				<thead>
					<tr>
						<td class="text-center"><?= __("Value","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Answers","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rating as $key=>$r){ ?>
						<?php $absValue += ($key*$r); ?>
						<tr class="font _16 w300">
							<td class="text-center"><?=  ($key); ?></td>
							<td class="text-center font w900"><?=  $r; ?></td>
						</tr>				
					<?php } ?>
				</tbody>
			</table>
			<?php if($q['survey_question_display']=='spinner'){ ?>
				<div class="highlight text-center padding-5 purple-bg-50 font _24 grey-800">
					<?= __("Added Value","bluerabbit").": <strong>$absValue</strong>"; ?>
				</div>
			<?php } ?>
		<?php }elseif($q['survey_question_type']=='guild-vote'){ ?>
			<?php 
				$oCount = 0;
				$guilds = $wpdb->get_results("
					SELECT 
					a.*, b.player_id
					FROM {$wpdb->prefix}br_guilds a
					LEFT JOIN {$wpdb->prefix}br_player_guild b
					ON a.guild_id = b.guild_id AND b.player_id=$current_user->ID
					WHERE a.adventure_id=$adventure->adventure_id AND a.guild_status='publish' AND a.guild_group='{$q['survey_question_display']}'
					GROUP BY a.guild_id ORDER BY a.guild_name ASC
				");
			?>
			<table class="table small">
				<?php 
				$rating=[];
				foreach($answers[$key]['values'] as $answer){
					$rating[$answer]++;
				}
				$absValue = 0;
				?>	
				<thead>
					<tr>
						<td colspan="3" class="font _14 grey-600"><?=  __("Question","bluerabbit")." ".($key); ?></td>
					</tr>
					<tr>
						<td colspan="3" class="font _24 <?=$color; ?>-800"><?=  $q['text']; ?></td>
					</tr>
					<tr>
						<td colspan="3" class="font _18 grey-600"><?=  $q['survey_question_description']; ?></td>
					</tr>
					<tr>
						<td class="text-center"><?= __("Guild","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Value #","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Value %","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody>
				<?php foreach($guilds as $oKey=>$t) { ?>
					<?php
					$oCount ++;
					$value = round($answers[$key][$t->guild_id]*100/$answers[$key]['total']);
					$value = is_nan($value) ? 0 : $value;
					?>
					<tr class="font _16 w300">
						<td class="text-center">
							<div class="icon-group">
								<span class="icon-button font _24 sq-40 " style="background-image: url(<?=  $t->guild_logo; ?>);"></span>
								<span class="icon-content">
									<span class="line"><?=  $t->guild_name; ?></span>
								</span>
							</div>
						</td>
						<td class="text-center font w900"><?= $answers[$key][$t->guild_id]; ?></td>
						<td class="text-center font w900"><?=  $value; ?>%</td>
					</tr>				
				<?php }	?>
				</tbody>
			</table>
		<?php }elseif($q['survey_question_type']=='multi-choice'){ ?>
			<table class="table small">
				<?php 
				$values = [];
				foreach($answers[$key]['values'] as $ak){
					$cur_val = explode(',',$ak);
					foreach($cur_val as $cv){
						$values[$cv]++;
					}
				}
				?>
				<thead>
					<tr>
						<td class="text-center"><?= __("Option","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Answers","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($q['options'] as $oKey=>$o) { ?>
						<tr class="font _16 w300">
							<td class="text-center">
								<div class="icon-group">
									<?php if($o['image']) { ?>
										<span class="icon-button font _24 sq-40 " style="background-image: url(<?=  $o['image']; ?>);"></span>
									<?php } ?>
									<span class="icon-content">
										<span class="line"><?=  $o['text']; ?></span>
									</span>
								</div>
							</td>
							<td class="text-center font w900"><?=  $values[$oKey] ? $values[$oKey] : 0; ?></td>
						</tr>				
					<?php } ?>
				</tbody>
			</table>
		<?php }else{ ?>
			<table class="table small">
<!--
				<thead>
					<tr>
						<td class="text-center"><?= __("Track","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Question ID","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Description","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Question","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Options","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Answers","bluerabbit"); ?></td>
						<td class="text-center"><?= __("Percentage","bluerabbit"); ?></td>
					</tr>
				</thead>
-->
				<tbody>
					<?php foreach($q['options'] as $oKey=>$o) { ?>
						<tr class="font _16 w300">
							<td class="text-center">
								<div class="icon-group">
									<?php if($o['image']) { ?>
										<span class="icon-button font _24 sq-40 " style="background-image: url(<?=  $o['image']; ?>);"></span>
									<?php } ?>
									<span class="icon-content">
										<span class="line"><?=  $o['text']; ?></span>
									</span>
								</div>
							</td>
							<td class="text-center font w900">
								<?=  $o['total_answers'] ? $o['total_answers'] : 0; ?>
							
							</td>
							<td class="text-center font w900">
								<?=  $o['value'] ? $o['value'] : 0; ?>%
							
							</td>
						</tr>				
					<?php } ?>
				</tbody>
			</table>
		<?php }?>
		<br class="clear">
	</div>
</li>
