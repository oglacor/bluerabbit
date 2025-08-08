<?php if(isset($step->step_background) && $step->step_background){ ?>
    <div class="step-background" style="background-image: url(<?= $step->step_background;?>);">
        <?php $mime = (wp_check_filetype($step->step_background));?>
        <?php if(strstr($mime['type'], "video")){ ?>
            <video id="step-background-video-<?= $step->step_order; ?>" loop autoplay class="overlay-background-video step-background-video <?= $i==0 ? 'active' : ''; ?>" >
                <source src="<?=$step->step_background; ?>">
            </video>
        <?php } ?>
    </div>
<?php } ?>
