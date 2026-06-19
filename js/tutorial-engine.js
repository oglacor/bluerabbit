function cleanupShepherdTour() {
	document.body.classList.remove('shepherd-enabled', 'shepherd-has-active-tour', 'shepherd-target');
	document.body.style.overflow = '';
	document.body.style.overscrollBehavior = '';

	var svgOverlay = document.querySelector('.shepherd-modal-overlay-container');
	if (svgOverlay) {
		svgOverlay.style.pointerEvents = 'none';
		svgOverlay.style.display = 'none';
	}

	document.querySelectorAll('.shepherd-element, .shepherd-modal-overlay-container').forEach(function (el) {
		el.style.pointerEvents = 'none';
		el.style.display = 'none';
	});
}

function brDismissTutorial(tutorialKey) {
	jQuery.ajax({
		url: runAJAX.ajaxurl,
		data: { action: 'br_dismiss_tutorial', tutorial_key: tutorialKey },
		method: "POST"
	});
}

function brCreateTour(tutorialKey, extraOptions) {
	extraOptions = extraOptions || {};
	var tour = new Shepherd.Tour(Object.assign({
		defaultStepOptions: {
			classes: 'shadow-md bg-purple-dark',
			scrollTo: { behavior: 'smooth', block: 'center' },
			cancelIcon: { enabled: true }
		},
		useModalOverlay: true,
		keyboardNavigation: false
	}, extraOptions));
	tour.on('start', function () {
		brDismissTutorial(tutorialKey);
	});
	tour.on('complete', function () {
		cleanupShepherdTour();
	});
	tour.on('cancel', function () {
		brDismissTutorial(tutorialKey);
		cleanupShepherdTour();
	});
	return tour;
}

function brNextBtn(label) {
	return {
		text: label || BR_TUTORIAL_I18N.next,
		classes: "blue-bg-400 white-color",
		action: function () { return this.next(); }
	};
}

function brBackBtn(label) {
	return {
		text: label || BR_TUTORIAL_I18N.back,
		classes: "br-secondary-button white-color",
		action: function () { return this.back(); }
	};
}

function brSkipBtn(tutorialKey, label) {
	return {
		text: label || BR_TUTORIAL_I18N.skip,
		classes: "br-secondary-button white-color",
		action: function () {
			brDismissTutorial(tutorialKey);
			return this.complete();
		}
	};
}

function brDoneBtn(tutorialKey, label) {
	return {
		text: label || BR_TUTORIAL_I18N.done,
		classes: "br-end-tutorial white-color",
		action: function () {
			brDismissTutorial(tutorialKey);
			return this.complete();
		}
	};
}
