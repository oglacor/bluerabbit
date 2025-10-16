<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
    <div class="journey-builder-controls">
        <button class="reset form-ui" onClick="resetMilestonePositions('data-color');"><?= __("Group by color","bluerabbit"); ?> </button>
        <button class="reset form-ui" onClick="resetMilestonePositions('data-id');"><?= __("Individual","bluerabbit"); ?> </button>
        <button class="reset form-ui" onClick="resetMilestonesToList();"><?= __("List","bluerabbit"); ?> </button>
        <button class="reset form-ui" onClick="resetMilestonePositions('data-beehive',50, 10, 1500, <?= $quests ? count($quests)/20*150 : "250" ; ?>);"><?= __("All together","bluerabbit"); ?> </button>
    </div>
    <div class="journey-builder-container">
        <?php if($all_quests){ ?>
            <div class="builder" id="builder">
                <?php foreach ($all_quests as $k=>$m){ ?>
                    <?php
                    if($m->quest_type != 'blog-post' && $m->quest_type != 'lore'){
                        $scale = ' scale(1) ';
                        if($m->milestone_z == 100){
                            $scale = ' scale(0.9) ';
                        }elseif($m->milestone_z == -100){
                            $scale = ' scale(1.1) ';
                        }elseif($m->milestone_z == -200){
                            $scale = ' scale(1.2) ';
                        }else{
                            $scale = ' scale(1) ';
                        }
                        ?>

                        <div data-id="<?= $m->quest_id;?>" data-color="<?=$m->quest_color; ?>" data-beehive="all" id="milestone-<?= $m->quest_id; ?>" class="milestone milestone-order-<?= $k; ?>  milestone-color-<?= $m->quest_color; ?>" style="top:<?=$m->milestone_top; ?>px; left:<?=$m->milestone_left; ?>px; transform: translateZ(<?= $m->milestone_z;?>px) <?= $scale; ?> rotate(<?= $m->milestone_rotation;?>deg);">
                            <div class="milestone-name">
                                <?= $m->quest_title; ?>
                            </div>
                            <div class="milestone-handle milestone-content <?= $m->quest_color; ?>" style="background-image:url(<?= $m->mech_badge; ?>); ">
                                <span class="icon icon-<?= $m->quest_icon; ?>"></span>
                            </div>
                            <div class="milestone-controls">
                                <button class="z-up" onClick="zFront(<?= $m->quest_id; ?>);">
                                    <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-z-up.svg">
                                </button>
                                <button class="z-down" onClick="zBack(<?= $m->quest_id; ?>);">
                                    <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-z-down.svg">
                                </button>

                            </div>
                            <div class="milestone-data">
                                <input type="hidden" class="top" value="<?=$m->milestone_top; ?>">
                                <input type="hidden" class="left" value="<?=$m->milestone_left; ?>">
                                <input type="hidden" class="x-pos" value="<?=$m->milestone_x; ?>">
                                <input type="hidden" class="y-pos" value="<?=$m->milestone_y; ?>">
                                <input type="hidden" class="z-pos" value="<?=$m->milestone_z ? $m->milestone_z : 0; ?>">
                                <input type="hidden" class="rotation" value="<?=$m->milestone_rotation; ?>">
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
            <script>
                initializeBuilderMilestones();
                resizeJourneyMapWithPadding(1000, 'builder', '.milestone');
            </script>
        <?php }else{ ?>
            <div class="sys-message">
                <?= __("No Milestones available","bluerabbit"); ?>
            </div>
        <?php } ?>

    </div>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

