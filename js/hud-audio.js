// ═══════════════════════════════════════════════════════════════════════════
// BLUERABBIT HUD — Custom Audio Player
// ═══════════════════════════════════════════════════════════════════════════

function hudFormatTime(secs) {
	var m = Math.floor(secs / 60);
	var s = Math.floor(secs % 60);
	return m + ':' + (s < 10 ? '0' : '') + s;
}

function hudSetPlayState(btn, playing) {
	if (!btn) return;
	var svgPlay  = btn.querySelector('.svg-play');
	var svgPause = btn.querySelector('.svg-pause');
	if (svgPlay)  svgPlay.style.display  = playing ? 'none'  : 'block';
	if (svgPause) svgPause.style.display = playing ? 'block' : 'none';
	btn.setAttribute('data-state', playing ? 'pause' : 'play');
}

function hudToggleAudio(qid) {
	var audio    = document.getElementById('audio-el-' + qid);
	var btn      = document.getElementById('play-icon-' + qid);
	var progress = document.getElementById('progress-' + qid);
	var curTime  = document.getElementById('cur-time-' + qid);
	var durTime  = document.getElementById('dur-time-' + qid);
	var waveform = document.getElementById('waveform-' + qid);

	if (!audio) return;

	audio.addEventListener('loadedmetadata', function() {
		if (durTime) durTime.textContent = hudFormatTime(audio.duration);
	});

	audio.addEventListener('timeupdate', function() {
		if (!audio.duration) return;
		var pct = (audio.currentTime / audio.duration) * 100;
		if (progress) progress.style.width = pct + '%';
		if (curTime)  curTime.textContent  = hudFormatTime(audio.currentTime);
	});

	audio.addEventListener('ended', function() {
		hudSetPlayState(btn, false);
		if (waveform) waveform.querySelectorAll('.bar').forEach(function(b) { b.classList.remove('active'); });
		if (progress) progress.style.width = '0%';
		if (curTime)  curTime.textContent  = '0:00';
	});

	if (audio.paused) {
		// Pause any other playing audio first
		document.querySelectorAll('.hud-audio-player audio').forEach(function(a) {
			if (a !== audio && !a.paused) {
				a.pause();
				var otherId   = a.id.replace('audio-el-', '');
				var otherBtn  = document.getElementById('play-icon-' + otherId);
				var otherWave = document.getElementById('waveform-' + otherId);
				hudSetPlayState(otherBtn, false);
				if (otherWave) otherWave.querySelectorAll('.bar').forEach(function(b) { b.classList.remove('active'); });
			}
		});

		audio.play();
		hudSetPlayState(btn, true);
		if (waveform) waveform.querySelectorAll('.bar').forEach(function(b) { b.classList.add('active'); });
	} else {
		audio.pause();
		hudSetPlayState(btn, false);
		if (waveform) waveform.querySelectorAll('.bar').forEach(function(b) { b.classList.remove('active'); });
	}
}
