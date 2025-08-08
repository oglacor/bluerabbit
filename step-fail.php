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
			<button class="action-button danger" onClick="failQuest();">
				<?= __("Quest failed!","bluerabbit"); ?>
			</button>
		</div>

		<div class="steps-navigation">
			<button class="step-nav-button step-fail" id="last-button"  onClick="failQuest();">
				<svg id="button-step-fail" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172 172">
					<rect class="outline"  x="1" y="1" width="170" height="170"/>
					<rect class="back-color"  x="15.69" y="15.69" width="140.63" height="140.63"/>
					<polygon  class="arrow arrow-7 arrow-bottom"  points="171 143.29 143.29 171 163.78 171 171 163.78 171 143.29"/>
					<polygon  class="arrow arrow-7 arrow-top"  points="1 163.78 8.22 171 28.71 171 1 143.29 1 163.78"/>
					<polygon  class="arrow arrow-6 arrow-bottom"  points="171 107.86 107.86 171 128.35 171 171 128.35 171 107.86"/>
					<polygon  class="arrow arrow-6 arrow-top"  points="1 128.35 43.65 171 64.14 171 1 107.86 1 128.35"/>
					<polygon  class="arrow arrow-5"  points="1 92.92 79.08 171 92.93 171 171 92.93 171 72.43 86 157.43 1 72.43 1 92.92"/>
					<polygon  class="arrow arrow-4"  points="86 122.01 1 37 1 57.5 86 142.5 171 57.5 171 37.01 86 122.01"/>
					<polygon  class="arrow arrow-3"  points="86 86.58 1 1.58 1 22.07 86 107.07 171 22.07 171 1.58 86 86.58"/>
					<polygon  class="arrow arrow-2"  points="15.36 1 86 71.64 156.65 1 136.15 1 86 51.15 35.85 1 15.36 1"/>
					<polygon  class="arrow arrow-1"  points="50.78 1 86 36.22 121.22 1 100.73 1 86 15.73 71.28 1 50.78 1"/>
					<polygon class="main-arrow"  points="86.96 128.32 121 69.37 101.25 69.37 101.25 43.71 72.67 43.71 72.67 69.37 52.92 69.37 86.96 128.32"/>
				</svg>
			</button>
		</div>



	</div>
</div>
