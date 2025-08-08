<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container system-message">
		<div class="dialogue-box" id="step-content-text-<?=$step->step_order;?>">
			<div class="corner-tl"></div>
			<div class="edge-top"></div>
			<div class="corner-tr"></div>

			<div class="edge-left"></div>
			<div class="center">
				<div class="step-content">	
					<?= apply_filters('the_content',$step->step_content);  ?>
				</div>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div>
			<div class="edge-bottom"></div>
			<div class="corner-br"></div>
		</div>
		<div class="action-buttons">
			<button class="action-button success" onClick="submitPlayerWork();">
				<?= __("Quest completed!","bluerabbit"); ?>
			</button>
		</div>

		<div class="steps-navigation">
			<button class="step-nav-button step-success" id="last-button"  onClick="submitPlayerWork();">
				<svg id="button-step-next" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172 172">
					<rect  class="outline" x="1" y="1" width="170" height="170"/>
					<rect  class="back-color" x="15.69" y="15.69" width="140.63" height="140.63"/>
					<polygon  class="arrow arrow-7 arrow-bottom"  points="1 28.71 28.71 1 8.22 1 1 8.22 1 28.71"/>
					<polygon  class="arrow arrow-7 arrow-top"  points="171 8.22 163.78 1 143.29 1 171 28.71 171 8.22"/>
					<polygon  class="arrow arrow-6 arrow-bottom"  points="1 64.14 64.14 1 43.65 1 1 43.65 1 64.14"/>
					<polygon  class="arrow arrow-6 arrow-top"  points="171 43.65 128.35 1 107.86 1 171 64.14 171 43.65"/>
					<polygon  class="arrow arrow-5"  points="171 79.08 92.92 1 79.07 1 1 79.07 1 99.57 86 14.57 171 99.57 171 79.08"/>
					<polygon  class="arrow arrow-4"  points="86 49.99 171 135 171 114.5 86 29.5 1 114.5 1 134.99 86 49.99"/>
					<polygon  class="arrow arrow-3"  points="86 85.42 171 170.42 171 149.93 86 64.93 1 149.93 1 170.42 86 85.42"/>
					<polygon  class="arrow arrow-2"  points="156.64 171 86 100.36 15.35 171 35.85 171 86 120.85 136.15 171 156.64 171"/>
					<polygon  class="arrow arrow-1"  points="121.22 171 86 135.78 50.78 171 71.27 171 86 156.27 100.72 171 121.22 171"/>
					<polygon  class="main-arrow" points="86.96 43.71 52.92 102.67 72.67 102.67 72.67 128.32 101.25 128.32 101.25 102.67 121 102.67 86.96 43.71"/>
				</svg>
			</button>
		</div>


	</div>
</div>
