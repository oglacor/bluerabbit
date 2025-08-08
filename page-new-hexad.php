<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$hexad = createHexad();
	shuffle($hexad);

?>

		<div class="container boxed max-w-1200 ">
			<div class="card">
				<div class="w-full h-250 relative  fluid">
					<div class="spacer fixed-250">
						<div class="background green-bg-800 opacity-60"></div>
						<div class="table foreground">
							<div class="table-cell text-center">
								<h1 class="font _48 w900 condensed kerning-5 white-color padding-20"><?php _e("New Player Type Test","bluerabbit"); ?></h1>
								<a class="form-ui green-bg-400" href="http://gamified.uk" target="_blank"><?php _e("Please support this work at http://gamified.uk","bluerabbit"); ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="body-ui white-bg">
				<div class="row test">
					<table width="100%">
						<thead>
							<tr>
								<td><strong><?php _e('Question','bluerabbit'); ?></strong></td>
								<td><strong><?php _e('Value','bluerabbit'); ?></strong></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($hexad as $key=>$q){ ?>
								<tr>
									<td><?php echo $q['question']; ?></td>
									<td>
										<select class="type-<?php echo $q['player_style']; ?>" id="select-<?php echo $key; ?>">
											<option value="7"><?php _e("Strongly Agree","bluerabbit"); ?></option>
											<option value="6"><?php _e("Agree","bluerabbit"); ?></option>
											<option value="5"><?php _e("Somewhat Agree","bluerabbit"); ?></option>
											<option value="4" selected><?php _e("Neither","bluerabbit"); ?></option>
											<option value="3"><?php _e("Somewhat Disagree","bluerabbit"); ?></option>
											<option value="2"><?php _e("Disagree","bluerabbit"); ?></option>
											<option value="1"><?php _e("Strongly Disagree","bluerabbit"); ?></option>
										</select>
									</td>
								</tr>	
							<?php } ?>
						</tbody>
					</table>
				</div>
				<div class="row">
					<div class="credits">
						<h3 class="text-center"><?php _e("This test was developed completely by Andzrej Marczewski","bluerabbit"); ?></h3>
						<div class="APA-ref">
	<div id="copy-target-99791202" class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied">Marczewski, A. (2015). <a href="http://gamified.uk/user-types/" rel="nofollow" target="_blank">User Types</a>. In <em><a href="http://www.gamified.uk/even-ninja-monkeys-like-to-play/">Even Ninja Monkeys Like to Play</a>: Gamification, Game Thinking and Motivational Design</em> (1st ed., pp. 65-80). CreateSpace Independent Publishing Platform.</div><div class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied"><ul><li><strong>ISBN-10:</strong> 1514745666</li><li><strong>ISBN-13:</strong> 978-1514745663</li></ul><p><a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license"><img style="border-width: 0;" src="//i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" alt="Creative Commons Licence"/></a> Gamification User Types Hexad by <a href="https://www.gamified.uk/user-types" rel="cc:attributionURL">Andrzej Marczewski</a> is licensed under a <a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.</p></div>
						</div>
						<div class="text-center">
							<a class="form-ui green-bg-400" href="http://gamified.uk" target="_blank"><?php _e("Please support his work at http://gamified.uk","bluerabbit"); ?></a>
						</div>
					</div>
				</div>
			</div>
			<div class="footer-ui grey-bg-800 text-center">
				<input type="hidden" id="nonce-hexad" value="<?php echo wp_create_nonce('br_new_hexad_nonce'); ?>"/>
				<button class="form-ui green-bg-400" id="new-hexad-button" onClick="newHexad();">
					<span class="enabled-text">
						<span class="icon icon-hexad"></span><?php _e('Get your player type ','bluerabbit'); ?>
					</span>
					<span class="disabled-text">
						<span class="icon icon-warning"></span><?php _e('You must answer all questions','bluerabbit'); ?>
					</span>
				<br>
				</button>
			</div>
		</div>
	</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>