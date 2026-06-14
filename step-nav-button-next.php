<?php if(!$steps ){ ?>
	<div class="steps-navigation action-buttons">
		<button class="action-button success" onClick="submitPlayerWork();">
			<?= __("Submit Answer!","bluerabbit"); ?>
		</button>
	</div>
<?php }elseif($i >= count($steps)-1){ ?>
	<div class="steps-navigation action-buttons">
		<button class="step-nav-button step-next" id="last-button"  onClick="submitPlayerWork();">
			<svg id="button-step-next" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172 172">
				<rect class="outline"  x="1" y="1" width="170" height="170"/>
				<rect class="back-color"  x="15.69" y="15.69" width="140.63" height="140.63"/>
				<polygon class="arrow arrow-7 arrow-bottom"  points="143.29 1 171 28.71 171 8.22 163.78 1 143.29 1"/>
				<polygon class="arrow arrow-7 arrow-top"  points="163.78 171 171 163.78 171 143.29 143.29 171 163.78 171"/>
				<polygon class="arrow arrow-6 arrow-bottom"  points="107.86 1 171 64.14 171 43.65 128.35 1 107.86 1"/>
				<polygon class="arrow arrow-6 arrow-top"  points="128.35 171 171 128.35 171 107.86 107.86 171 128.35 171"/>
				<polygon class="arrow arrow-5"  points="92.92 171 171 92.92 171 79.07 92.93 1 72.43 1 157.43 86 72.43 171 92.92 171"/>
				<polygon class="arrow arrow-4"  points="122.01 86 37 171 57.5 171 142.5 86 57.5 1 37.01 1 122.01 86"/>
				<polygon class="arrow arrow-3"  points="86.58 86 1.58 171 22.07 171 107.07 86 22.07 1 1.58 1 86.58 86"/>
				<polygon class="arrow arrow-2"  points="1 156.64 71.64 86 1 15.35 1 35.85 51.15 86 1 136.15 1 156.64"/>
				<polygon class="arrow arrow-1"  points="1 121.22 36.22 86 1 50.78 1 71.27 15.73 86 1 100.72 1 121.22"/>
				<polygon class="main-arrow"  points="129.27 86.02 70.31 51.98 70.31 71.73 44.66 71.73 44.66 100.31 70.31 100.31 70.31 120.06 129.27 86.02"/>
			</svg>
		</button>
	</div>
<?php }else{ ?>
	<div class="steps-navigation">
		<a class="step-nav-button step-next" href="#step-<?= $steps[($i+1)]->step_order; ?>">
			<svg id="button-step-next" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172 172">
				<rect class="outline"  x="1" y="1" width="170" height="170"/>
				<rect class="back-color"  x="15.69" y="15.69" width="140.63" height="140.63"/>
				<polygon class="arrow arrow-7 arrow-bottom"  points="143.29 1 171 28.71 171 8.22 163.78 1 143.29 1"/>
				<polygon class="arrow arrow-7 arrow-top"  points="163.78 171 171 163.78 171 143.29 143.29 171 163.78 171"/>
				<polygon class="arrow arrow-6 arrow-bottom"  points="107.86 1 171 64.14 171 43.65 128.35 1 107.86 1"/>
				<polygon class="arrow arrow-6 arrow-top"  points="128.35 171 171 128.35 171 107.86 107.86 171 128.35 171"/>
				<polygon class="arrow arrow-5"  points="92.92 171 171 92.92 171 79.07 92.93 1 72.43 1 157.43 86 72.43 171 92.92 171"/>
				<polygon class="arrow arrow-4"  points="122.01 86 37 171 57.5 171 142.5 86 57.5 1 37.01 1 122.01 86"/>
				<polygon class="arrow arrow-3"  points="86.58 86 1.58 171 22.07 171 107.07 86 22.07 1 1.58 1 86.58 86"/>
				<polygon class="arrow arrow-2"  points="1 156.64 71.64 86 1 15.35 1 35.85 51.15 86 1 136.15 1 156.64"/>
				<polygon class="arrow arrow-1"  points="1 121.22 36.22 86 1 50.78 1 71.27 15.73 86 1 100.72 1 121.22"/>
				<polygon class="main-arrow"  points="129.27 86.02 70.31 51.98 70.31 71.73 44.66 71.73 44.66 100.31 70.31 100.31 70.31 120.06 129.27 86.02"/>
			</svg>
		</a>
	</div>
<?php } ?>
