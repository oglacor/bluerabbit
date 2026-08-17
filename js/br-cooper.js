/**
 * Cooper — the in-adventure assistant panel.
 *
 * Replaces the old cross-origin iframe. Everything here talks to admin-ajax on
 * the same origin, so the player's session is simply already there — no token to
 * mint, no postMessage bridge, and the transcript survives a page change because
 * the conversation id is kept in sessionStorage.
 */
(function () {
	'use strict';

	var cfg = window.brCooper || {};
	var conversationId = 0;
	var busy = false;

	var panel, log, input, sendBtn, form;

	function storageKey() {
		return 'br_cooper_conv_' + (cfg.adventureId || 0);
	}

	function init() {
		panel = document.getElementById('br-cooper');
		if (!panel) return;

		log     = document.getElementById('br-cooper-log');
		input   = document.getElementById('br-cooper-input');
		sendBtn = document.getElementById('br-cooper-send');
		form    = document.getElementById('br-cooper-form');

		conversationId = parseInt(sessionStorage.getItem(storageKey()) || '0', 10) || 0;

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			send();
		});

		// Enter sends, Shift+Enter breaks the line — chat convention, and the
		// textarea exists precisely so a player can paste a multi-line question.
		input.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey) {
				e.preventDefault();
				send();
			}
		});
		input.addEventListener('input', autoGrow);

		document.querySelectorAll('[data-br-cooper-open]').forEach(function (el) {
			el.addEventListener('click', open);
		});
		document.querySelectorAll('[data-br-cooper-close]').forEach(function (el) {
			el.addEventListener('click', close);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && panel.classList.contains('br-cooper-open')) close();
		});

		if (conversationId) {
			loadTranscript();
		} else {
			greet();
		}
	}

	function open() {
		panel.classList.add('br-cooper-open');
		panel.setAttribute('aria-hidden', 'false');
		setTimeout(function () { input.focus(); }, 80);
	}

	function close() {
		panel.classList.remove('br-cooper-open');
		panel.setAttribute('aria-hidden', 'true');
	}

	function autoGrow() {
		input.style.height = 'auto';
		input.style.height = Math.min(input.scrollHeight, 140) + 'px';
	}

	function greet() {
		addBubble('assistant', cfg.greeting || "Hi! I'm Cooper. Ask me anything about your adventure.");
		renderSuggestions();
	}

	/**
	 * Openers, not decoration: the three questions Cooper is uniquely able to
	 * answer now that it can see the player's progress. A blank chat box is the
	 * fastest way to make someone close a support tool.
	 */
	function renderSuggestions() {
		if (!cfg.suggestions || !cfg.suggestions.length) return;

		var wrap = document.createElement('div');
		wrap.className = 'br-cooper-suggestions';

		cfg.suggestions.forEach(function (text) {
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'br-cooper-suggestion';
			b.textContent = text;
			b.addEventListener('click', function () {
				wrap.remove();
				input.value = text;
				send();
			});
			wrap.appendChild(b);
		});

		log.appendChild(wrap);
		scroll();
	}

	function send() {
		if (busy) return;

		var text = input.value.trim();
		if (!text) return;

		input.value = '';
		autoGrow();
		setBusy(true);

		addBubble('user', text);
		var thinking = addThinking();

		var body = new URLSearchParams({
			action: 'br_cooper_chat',
			message: text,
			adventure_id: cfg.adventureId || 0,
			conversation_id: conversationId
		});

		fetch(cfg.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				thinking.remove();
				if (!data || !data.success) {
					addBubble('error', (data && data.error) || cfg.genericError);
					return;
				}
				if (data.conversation_id) {
					conversationId = data.conversation_id;
					sessionStorage.setItem(storageKey(), conversationId);
				}
				addBubble('assistant', data.reply, data.sources);
			})
			.catch(function () {
				thinking.remove();
				addBubble('error', cfg.genericError);
			})
			.finally(function () {
				setBusy(false);
				input.focus();
			});
	}

	function setBusy(state) {
		busy = state;
		sendBtn.disabled = state;
		panel.classList.toggle('br-cooper-busy', state);
	}

	function addThinking() {
		var el = document.createElement('div');
		el.className = 'br-cooper-msg br-cooper-assistant br-cooper-thinking';
		el.innerHTML = '<span></span><span></span><span></span>';
		log.appendChild(el);
		scroll();
		return el;
	}

	function addBubble(role, text, sources) {
		var el = document.createElement('div');
		el.className = 'br-cooper-msg br-cooper-' + role;
		el.innerHTML = format(text);

		if (sources && Object.keys(sources).length) {
			var foot = document.createElement('div');
			foot.className = 'br-cooper-sources';
			Object.keys(sources).forEach(function (url) {
				var a = document.createElement('a');
				a.href = url;
				a.target = '_blank';
				a.rel = 'noopener';
				a.textContent = sources[url];
				foot.appendChild(a);
			});
			el.appendChild(foot);
		}

		log.appendChild(el);
		scroll();
		return el;
	}

	/**
	 * Minimal Markdown, escaped first.
	 *
	 * The model's output is not trusted markup: it can quote a player's own
	 * message straight back, and that message can contain anything. So every
	 * character is escaped before any formatting is re-introduced, and only the
	 * four constructs Cooper actually uses are honoured.
	 */
	function format(text) {
		var safe = String(text)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');

		return safe
			.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
			.replace(/(^|\s)\*([^*\n]+)\*/g, '$1<em>$2</em>')
			.replace(/`([^`\n]+)`/g, '<code>$1</code>')
			.replace(/^[-*]\s+(.*)$/gm, '<span class="br-cooper-li">$1</span>')
			.replace(/\n{2,}/g, '<br><br>')
			.replace(/\n/g, '<br>');
	}

	function scroll() {
		log.scrollTop = log.scrollHeight;
	}

	function loadTranscript() {
		fetch(cfg.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: new URLSearchParams({
				action: 'br_cooper_transcript',
				conversation_id: conversationId
			}).toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (!data || !data.messages || !data.messages.length) { greet(); return; }
				data.messages.forEach(function (m) {
					addBubble(m.msg_role === 'user' ? 'user' : 'assistant', m.msg_content);
				});
			})
			.catch(greet);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
