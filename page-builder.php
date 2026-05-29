<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
    <div class="journey-builder-controls">
        <button class="reset form-ui" onClick="resetMilestonePositions('data-color');"><?= __("Group by color","bluerabbit"); ?> </button>
        <button class="reset form-ui" onClick="resetMilestonePositions('data-id');"><?= __("Individual","bluerabbit"); ?> </button>
        <button class="reset form-ui" onClick="resetMilestonesToList();"><?= __("List","bluerabbit"); ?> </button>
        <button class="reset form-ui" onClick="resetMilestonePositions('data-beehive',50, 10, 1500, <?= $quests ? count($quests)/20*150 : "250" ; ?>);"><?= __("All together","bluerabbit"); ?> </button>
        <button class="reset form-ui" onClick="resetMilestoneSizes();"><?= __("Reset Sizes","bluerabbit"); ?> </button>
    </div>
    <div class="journey-builder-container">
        <?php
        $builder_tabis = getTabis($adv_parent_id);

        // Count quests per tabi so the node can show the number
        $builder_tabi_quest_count = [];
        if($all_quests) {
            foreach($all_quests as $q) {
                if($q->tabi_id) {
                    $builder_tabi_quest_count[$q->tabi_id] = ($builder_tabi_quest_count[$q->tabi_id] ?? 0) + 1;
                }
            }
        }
        ?>
        <?php if($all_quests || $builder_tabis){ ?>
            <div class="builder" id="builder">

                <?php // Free milestones — skip any quest assigned to a tabi ?>
                <?php foreach ($all_quests as $k=>$m){ ?>
                    <?php
                    if($m->quest_type != 'blog-post' && $m->quest_type != 'lore' && !$m->tabi_id){
                        $scaleVal = $m->milestone_z;
                        if($scaleVal > 5){
                            $scaleVal = 5;
                        }elseif($scaleVal < 1){
                            $scaleVal = 1;
                        }

                        $baseWidth = 108;
                        $baseHeight = 95;
                        $scaledWidth = $baseWidth * $scaleVal;
                        $scaledHeight = $baseHeight * $scaleVal;
                        $scale = 'width: '.$scaledWidth.'px; height: '.$scaledHeight.'px;';

                        if(!$m->milestone_left || !$m->milestone_top){
                            $mTop = 350; $mLeft = 350;
                        }else{
                            $mTop = $m->milestone_top;
                            $mLeft = $m->milestone_left;
                        }
                        ?>
                        <div data-id="<?= $m->quest_id;?>" data-color="<?=$m->quest_color; ?>" data-beehive="all" id="milestone-<?= $m->quest_id; ?>" class="milestone milestone-order-<?= $k; ?> milestone-color-<?= $m->quest_color; ?>" style="top:<?=$mTop; ?>px; left:<?=$mLeft; ?>px;">
                            <div class="milestone-name">
                                <?= $m->quest_title; ?>
                            </div>
                            <div class="milestone-handle milestone-content <?= $m->quest_color; ?>" style="background-image:url(<?= $m->mech_badge; ?>); <?= $scale; ?>">
                                <span class="icon icon-<?= $m->quest_icon; ?>"></span>
                            </div>
                            <div class="milestone-controls">
                                <button class="z-up" onClick="zFront(<?= $m->quest_id; ?>);">
                                    <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-z-up.svg">
                                </button>
                                <button class="milestone-reset" onClick="milestoneReset(<?= $m->quest_id; ?>);">
                                    <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-rotate-cw.svg">
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
                                <input type="hidden" class="z-pos" value="<?=$m->milestone_z ? $m->milestone_z : 1; ?>">
                                <input type="hidden" class="rotation" value="<?=$m->milestone_rotation; ?>">
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php // Tabi nodes — draggable units that group their quests ?>
                <?php if($builder_tabis) { foreach($builder_tabis as $t) {
                    $tNodeTop  = $t->tabi_top  ?: 350;
                    $tNodeLeft = $t->tabi_left ?: 350;
                    $qCount    = $builder_tabi_quest_count[$t->tabi_id] ?? 0;
                    ?>
                    <div class="builder-tabi tabi-node <?= esc_attr($t->tabi_color); ?>"
                         id="tabi-node-<?= $t->tabi_id; ?>"
                         data-tabi-id="<?= $t->tabi_id; ?>"
                         style="top:<?= $tNodeTop; ?>px; left:<?= $tNodeLeft; ?>px;">
                        <div class="tabi-node-icon"><span class="icon icon-tabi"></span></div>
                        <div class="tabi-node-name"><?= esc_html($t->tabi_name); ?></div>
                        <div class="tabi-node-count"><?= $qCount; ?> <?= __('quests','bluerabbit'); ?></div>
                    </div>
                <?php } } ?>

            </div>
            <script>
                initializeBuilderMilestones();
                initializeBuilderTabis();
                resizeJourneyMapWithPadding(1000, 'builder', '.milestone, .builder-tabi');
            </script>
        <?php }else{ ?>
            <div class="sys-message">
                <?= __("No Milestones available","bluerabbit"); ?>
            </div>
        <?php } ?>

    </div>
    <input type="hidden" id="tabi-position-nonce" value="<?= wp_create_nonce('tabi_position_nonce'); ?>">
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

