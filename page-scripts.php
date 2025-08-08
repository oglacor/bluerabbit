<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($current_user->ID ==1){ ?>

<style>
	.board {
	overflow: hidden;
	position: absolute;
	font-size: 0;
	width: 90vw;
	height: 90vw;
	max-width: 90vh;
	max-height: 90vh;
	top: 50%;
	left: 50%;
	transform: translate(-50%,-50%);
}

.piece {
	position: relative;
	cursor: pointer;
	display: inline-block;
	width: 25%; height: 25%;
	border: solid 1px rgba(0,0,0,1);
}

.piece img{
	position: absolute;
	top: 0; left: 0;
	transition: all 0.5s ease-out;
	width: 100%; height: 100%;
}
.piece .rotate-left{
	z-index: 10;
	position: absolute;
	top: 0; left: 0;
	width: 50%;
	height: 100%;
	cursor: pointer;
	background-color: rgba(255,255,255,0);
	transition: all 0.3s ease-out;
}
.piece .rotate-left:hover{
	background-color: rgba(255,255,255,0.4);
}
.piece .rotate-right{
	z-index: 10;
	position: absolute;
	top: 0; right: 0;
	width: 50%;
	height: 100%;
	cursor: pointer;
	background-color: rgba(255,255,255,0);
	transition: all 0.3s ease-out;
}
.piece .rotate-right:hover{
	background-color: rgba(255,255,255,0.4);
}

</style>

<div class="layer base relative boxed max-w-1200 white-color padding-10 font _18 text-center">
	<?php
		$puzzle = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16);
		shuffle($puzzle);
	?>
	<div class="board">
		<?php foreach($puzzle as $key=>$p){ ?>
			<div id="piece-<?=$p;?>" class="piece"><img src="<?= get_bloginfo('stylesheet_directory');?>/images/puzzle/<?=$p;?>.png" alt=""></div>
		<?php } ?>
	</div>

	
</div>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
