<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
$puzzle_image = $settings['image'] ?? '';
$cols = max(2, min(8, (int) ($settings['cols'] ?? 3)));
$rows = max(2, min(8, (int) ($settings['rows'] ?? 3)));
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-validate">

		<?php if ($step->step_content) { ?><div class="br-puzzle-prompt"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>

		<?php if ($already_done) { ?>
		<div class="br-puzzle-done">
			<?php if ($puzzle_image) { ?>
			<img src="<?= esc_attr($puzzle_image); ?>" alt="" class="br-puzzle-complete-image">
			<?php } ?>
			<div class="br-step-feedback br-step-feedback-success">
				<span class="icon icon-check"></span> <?= __("Puzzle complete!", "bluerabbit"); ?>
			</div>
		</div>

		<?php } elseif ($puzzle_image) { ?>
		<div class="br-puzzle-wrap"
			id="puzzle-wrap-<?= $step->step_id; ?>"
			data-step-id="<?= $step->step_id; ?>"
			data-quest-id="<?= $q->quest_id; ?>"
			data-adventure-id="<?= $adv_child_id; ?>"
			data-cols="<?= $cols; ?>"
			data-rows="<?= $rows; ?>"
			data-image="<?= esc_attr($puzzle_image); ?>">
			<div class="br-puzzle-board" id="puzzle-board-<?= $step->step_id; ?>"></div>
		</div>
		<p class="br-puzzle-hint"><?= __("Drag to place &middot; Click &#8635; to rotate", "bluerabbit"); ?></p>
		<div id="puzzle-feedback-<?= $step->step_id; ?>" class="br-step-feedback"></div>
		<?php } ?>

		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($already_done) { include (TEMPLATEPATH . "/step-nav-button-next.php"); } ?>
	</div>
</div>

<?php if (!$already_done && $puzzle_image) { ?>
<script>
(function() {
	var wrap  = document.getElementById('puzzle-wrap-<?= $step->step_id; ?>');
	var board = document.getElementById('puzzle-board-<?= $step->step_id; ?>');
	if (!wrap || !board) return;

	var cols = <?= $cols; ?>, rows = <?= $rows; ?>, total = cols * rows;
	var img = new Image();
	img.onload = function() { build(img.naturalWidth, img.naturalHeight); };
	img.src = '<?= esc_js($puzzle_image); ?>';

	function build(imgW, imgH) {
		var wrapW = wrap.clientWidth;
		if (wrapW < 50) wrapW = 500;

		// Board width fills up to 90% of wrap, max 600px
		var boardW = Math.min(wrapW * 0.9, 600);
		// Board height from image aspect ratio
		var ratio = imgH / imgW;
		var boardH = Math.round(boardW * ratio);

		// Cell size from the grid
		var cellW = Math.floor(boardW / cols);
		var cellH = Math.floor(boardH / rows);
		// Adjust board to exact cell multiples
		boardW = cellW * cols;
		boardH = cellH * rows;

		board.style.width  = boardW + 'px';
		board.style.height = boardH + 'px';

		// Wrap height: board + scatter area below
		var scatterRows = Math.ceil(total / Math.floor(wrapW / cellW)) + 1;
		var scatterH = Math.max(cellH * scatterRows, cellH * 2);
		var wrapH = boardH + scatterH + 40;
		wrap.style.height = wrapH + 'px';

		var boardLeft = Math.floor((wrapW - boardW) / 2);

		// Board slots
		var slots = [];
		for (var r = 0; r < rows; r++) {
			for (var c = 0; c < cols; c++) {
				var slot = document.createElement('div');
				slot.className = 'br-puzzle-slot';
				slot.setAttribute('data-col', c);
				slot.setAttribute('data-row', r);
				slot.style.width  = cellW + 'px';
				slot.style.height = cellH + 'px';
				slot.style.left   = (c * cellW) + 'px';
				slot.style.top    = (r * cellH) + 'px';
				board.appendChild(slot);
				slots.push(slot);
			}
		}

		// Shuffle
		var order = [];
		for (var i = 0; i < total; i++) order.push(i);
		for (var i = order.length - 1; i > 0; i--) {
			var j = Math.floor(Math.random() * (i + 1));
			var t = order[i]; order[i] = order[j]; order[j] = t;
		}

		// Scatter zone below board
		var scatterTop = boardH + 30;
		function randomScatterPos() {
			return {
				x: Math.floor(Math.random() * Math.max(0, wrapW - cellW)),
				y: Math.floor(scatterTop + Math.random() * Math.max(0, scatterH - cellH))
			};
		}

		// Hit-test board
		function findSlotAt(vx, vy) {
			var br = board.getBoundingClientRect();
			var bx = vx - br.left;
			var by = vy - br.top;
			if (bx < 0 || by < 0 || bx > br.width || by > br.height) return null;
			var sc = Math.floor(bx / cellW);
			var sr = Math.floor(by / cellH);
			if (sc < 0 || sc >= cols || sr < 0 || sr >= rows) return null;
			var slot = slots[sr * cols + sc];
			if (slot.querySelector('.br-puzzle-piece')) return null;
			return slot;
		}

		var bgSize = boardW + 'px ' + boardH + 'px';
		var rots = [0, 90, 180, 270];
		var drag = null;

		document.addEventListener('mousemove', onMove);
		document.addEventListener('mouseup', onEnd);
		document.addEventListener('touchmove', onMove, { passive: false });
		document.addEventListener('touchend', onEnd);

		function getXY(e) {
			if (e.touches) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
			return { x: e.clientX, y: e.clientY };
		}

		function onMove(e) {
			if (!drag) return;
			e.preventDefault();
			var pt = getXY(e);
			drag.moved = true;
			var wr = wrap.getBoundingClientRect();
			drag.piece.style.left = (pt.x - wr.left - drag.offX) + 'px';
			drag.piece.style.top  = (pt.y - wr.top  - drag.offY) + 'px';
		}

		function onEnd(e) {
			if (!drag) return;
			var piece = drag.piece;
			var wasMoved = drag.moved;
			drag = null;
			piece.style.transition = '';
			piece.classList.remove('br-puzzle-dragging');
			piece.style.zIndex = '';

			if (!wasMoved) return;

			var rect = piece.getBoundingClientRect();
			var cx = rect.left + rect.width / 2;
			var cy = rect.top  + rect.height / 2;
			var slot = findSlotAt(cx, cy);
			if (slot) {
				var sc = parseInt(slot.getAttribute('data-col'));
				var sr = parseInt(slot.getAttribute('data-row'));
				piece.style.left = (boardLeft + sc * cellW) + 'px';
				piece.style.top  = (sr * cellH) + 'px';
			}
			checkSolved();
		}

		// Create pieces
		for (var i = 0; i < total; i++) {
			(function(idx) {
				var pCol = idx % cols;
				var pRow = Math.floor(idx / cols);
				var rot  = rots[Math.floor(Math.random() * rots.length)];
				var pos  = randomScatterPos();

				var piece = document.createElement('div');
				piece.className = 'br-puzzle-piece';
				piece.setAttribute('data-correct-col', pCol);
				piece.setAttribute('data-correct-row', pRow);
				piece.setAttribute('data-rotation', rot);
				piece.style.width  = cellW + 'px';
				piece.style.height = cellH + 'px';
				piece.style.left   = pos.x + 'px';
				piece.style.top    = pos.y + 'px';

				var inner = document.createElement('div');
				inner.className = 'br-puzzle-piece-inner';
				inner.style.backgroundImage    = 'url(' + img.src + ')';
				inner.style.backgroundSize     = bgSize;
				inner.style.backgroundPosition = (-pCol * cellW) + 'px ' + (-pRow * cellH) + 'px';
				inner.style.transform          = 'rotate(' + rot + 'deg)';
				piece.appendChild(inner);

				var btn = document.createElement('button');
				btn.className = 'br-puzzle-rotate-btn';
				btn.type = 'button';
				btn.innerHTML = '&#8635;';
				btn.addEventListener('click', function(e) {
					e.stopPropagation();
					e.preventDefault();
					var cur  = parseInt(piece.getAttribute('data-rotation')) || 0;
					var next = (cur + 90) % 360;
					piece.setAttribute('data-rotation', next);
					inner.style.transform = 'rotate(' + next + 'deg)';
					checkSolved();
				});
				piece.appendChild(btn);

				wrap.appendChild(piece);

				function onStart(e) {
					if (e.target === btn) return;
					e.preventDefault();
					var pt = getXY(e);
					var wr = wrap.getBoundingClientRect();
					var pieceLeft = parseFloat(piece.style.left) || 0;
					var pieceTop  = parseFloat(piece.style.top)  || 0;

					piece.style.transition = 'none';
					piece.classList.add('br-puzzle-dragging');
					drag = {
						piece: piece,
						offX: pt.x - wr.left - pieceLeft,
						offY: pt.y - wr.top  - pieceTop,
						moved: false
					};
					piece.style.zIndex = 100;
				}

				piece.addEventListener('mousedown', onStart);
				piece.addEventListener('touchstart', onStart, { passive: false });
			})(order[i]);
		}

		function checkSolved() {
			var correct = 0;
			for (var s = 0; s < slots.length; s++) {
				var sl = slots[s];
				var slotCol = parseInt(sl.getAttribute('data-col'));
				var slotRow = parseInt(sl.getAttribute('data-row'));
				var targetL = boardLeft + slotCol * cellW;
				var targetT = slotRow * cellH;

				var found = null;
				var pieces = wrap.querySelectorAll('.br-puzzle-piece');
				for (var p = 0; p < pieces.length; p++) {
					var pc = pieces[p];
					var pl = Math.round(parseFloat(pc.style.left) || 0);
					var pt = Math.round(parseFloat(pc.style.top) || 0);
					if (pl === targetL && pt === targetT) { found = pc; break; }
				}

				if (!found) continue;
				var ok = found.getAttribute('data-correct-col') == slotCol
					&& found.getAttribute('data-correct-row') == slotRow
					&& parseInt(found.getAttribute('data-rotation')) % 360 === 0;
				found.classList.toggle('br-puzzle-piece-correct', ok);
				if (ok) correct++;
			}
			if (correct === total) {
				board.classList.add('br-puzzle-solved');
				var sid = wrap.dataset.stepId;
				brSubmitGenericStep(parseInt(sid), parseInt(wrap.dataset.questId), parseInt(wrap.dataset.adventureId), {}, 'puzzle-feedback-' + sid);
			}
		}
	}
})();
</script>
<?php } ?>
