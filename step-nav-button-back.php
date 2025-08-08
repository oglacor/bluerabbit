<?php if($i > 0){ ?>
<div class="steps-navigation">
	<a class="step-nav-button step-back" href="#step-<?= $steps[($i-1)]->step_order; ?>">
		<svg class="button-step-back" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122 122">
			<rect class="outline" x="1" y="1" width="120" height="120"/>
			<rect class="back-color"  x="10" y="10" width="100" height="100"/>
			<polygon  class="arrow arrow-7 arrow-bottom"  points="20.56 121 1 101.44 1 115.9 6.1 121 20.56 121"/>
			<polygon  class="arrow arrow-7 arrow-top"  points="6.1 1 1 6.1 1 20.56 20.56 1 6.1 1"/>
			<polygon  class="arrow arrow-6 arrow-bottom"  points="45.57 121 1 76.43 1 90.9 31.1 121 45.57 121"/>
			<polygon  class="arrow arrow-6 arrow-top"  points="31.11 1 1 31.11 1 45.57 45.57 1 31.11 1"/>
			<polygon  class="arrow arrow-5"  points="56.11 1 1 56.11 1 65.89 56.11 121 70.58 121 10.58 61 70.58 1 56.11 1"/>
			<polygon  class="arrow arrow-4"  points="35.58 61 95.58 1 81.12 1 21.12 61 81.12 121 95.58 121 35.58 61"/>
			<polygon  class="arrow arrow-3"  points="60.59 61 120.59 1 106.13 1 46.13 61 106.13 121 120.59 121 60.59 61"/>
			<polygon  class="arrow arrow-2"  points="121 11.13 71.13 61 121 110.87 121 96.4 85.6 61 121 25.6 121 11.13"/>
			<polygon  class="arrow arrow-1"  points="121 36.14 96.14 61 121 85.86 121 71.4 110.61 61 121 50.61 121 36.14"/>
			<polygon class="main-arrow"  points="30.46 60.99 72.07 85.01 72.07 71.07 90.18 71.07 90.18 50.9 72.07 50.9 72.07 36.96 30.46 60.99"/>
		</svg>
	</a>
</div>
<?php }?>
