/* MGD Login Admin - Live-Vorschau, Medien, Farben */
(function ($) {
	'use strict';
	var form = document.getElementById('mgd-la-form');
	if (!form) { return; }

	var timer = 0;

	// Farben: leer = Template-Standard. Feld zeigt dann die Standardfarbe (data-custom=0), geschickt wird dann leer
	var colors = $('.mgd-la-color');
	function tplId() { return (form.querySelector('input[name$="[template]"]:checked') || {}).value; }
	function fillDefaults() {
		var d = (MGDLogin.defaults || {})[tplId()] || {};
		colors.each(function () {
			if (this.getAttribute('data-custom') === '1' || !d[this.getAttribute('data-key')]) { return; }
			this.value = d[this.getAttribute('data-key')];
			// Farbknopf nachziehen, ohne dass das Feld als geändert gilt
			var ev = new Event('input', { bubbles: false });
			this.dispatchEvent(ev);
		});
	}
	colors.on('input', function () { this.setAttribute('data-custom', '1'); });
	// fillDefaults feuert input > danach wieder auf Standard stellen
	function fillSilent() {
		var keep = colors.filter('[data-custom="0"]');
		fillDefaults();
		keep.attr('data-custom', '0');
	}
	function blankDefaults() {
		var changed = [];
		colors.filter('[data-custom="0"]').each(function () { changed.push([this, this.value]); this.value = ''; });
		return changed;
	}
	function restore(changed) { changed.forEach(function (c) { c[0].value = c[1]; }); }

	// Vorschau: Formular kurz in den iframe schicken, danach alles zurücksetzen
	function preview() {
		var oldAction = form.getAttribute('action');
		var oldTarget = form.getAttribute('target');
		var changed = blankDefaults();
		form.setAttribute('action', MGDLogin.previewUrl);
		form.setAttribute('target', 'mgd-la-preview');
		HTMLFormElement.prototype.submit.call(form);
		restore(changed);
		form.setAttribute('action', oldAction);
		if (oldTarget === null) { form.removeAttribute('target'); } else { form.setAttribute('target', oldTarget); }
	}
	function later() {
		clearTimeout(timer);
		timer = setTimeout(function () { contrast(); preview(); }, 350);
	}

	// Farbwähler vom Baukasten (data-mgd-color) feuert input/change
	$(form).on('input change', 'input, select, textarea', later);
	// anderes Template > Standardfarben nachziehen
	$(form).on('change', 'input[name$="[template]"]', fillSilent);
	// Speichern: unveränderte Farben leer schicken
	$(form).on('submit', function () { blankDefaults(); });

	// Medien
	$('.mgd-la-media').each(function () {
		var box = $(this), frame;
		var input = box.find('input[type="hidden"]');
		var holder = box.find('.mgd-la-media__box');
		var clear = box.find('[data-clear]');
		box.find('[data-pick]').on('click', function () {
			if (!frame) {
				frame = wp.media({ title: 'Bild wählen', library: { type: 'image' }, multiple: false });
				frame.on('select', function () {
					var a = frame.state().get('selection').first().toJSON();
					var url = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url;
					input.val(a.id);
					holder.empty().append($('<img>', { src: url, alt: '' }));
					clear.prop('hidden', false);
					later();
				});
			}
			frame.open();
		});
		clear.on('click', function () {
			input.val('0');
			holder.empty().append('<span>Kein Bild</span>');
			clear.prop('hidden', true);
			later();
		});
	});

	// Geräte-Größe
	$('.mgd-la-devices button').on('click', function () {
		$('.mgd-la-devices button').removeClass('is-active').attr('aria-pressed', 'false');
		$(this).addClass('is-active').attr('aria-pressed', 'true');
		$('.mgd-la-frame').css('max-width', $(this).data('w'));
	});

	// Zurück auf Template-Standard (nur Farben und Maße)
	$('#mgd-la-reset').on('click', function () {
		colors.attr('data-custom', '0');
		fillSilent();
		$.each(['radius', 'card_opacity', 'blur', 'card_width', 'overlay'], function (i, k) { $('#mgd-' + k).val(''); });
		// Effekte zurück auf "Wie das Layout"
		$.each(['particles', 'light', 'card_fx', 'position'], function (i, k) { $('#mgd-' + k).val(''); });
		$('#mgd-particle_amount').val('100');
		later();
	});

	// Kontrast Text auf Karte, WCAG 4.5:1
	function lum(hex) {
		hex = (hex || '').replace('#', '');
		if (hex.length === 3) { hex = hex.replace(/(.)/g, '$1$1'); }
		if (!/^[0-9a-f]{6}$/i.test(hex)) { return null; }
		var c = [0, 2, 4].map(function (i) {
			var v = parseInt(hex.substr(i, 2), 16) / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
		});
		return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
	}
	function contrast() {
		var tpl = (form.querySelector('input[name$="[template]"]:checked') || {}).value;
		var d = (MGDLogin.defaults || {})[tpl] || {};
		var t = lum($('#mgd-text').val() || d.text), c = lum($('#mgd-card').val() || d.card);
		var warn = document.getElementById('mgd-la-contrast');
		if (t === null || c === null || !warn) { return; }
		var r = (Math.max(t, c) + 0.05) / (Math.min(t, c) + 0.05);
		warn.hidden = r >= 4.5;
	}

	fillSilent();
	contrast();
	preview();
})(jQuery);
