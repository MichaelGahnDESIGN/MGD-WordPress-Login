/* MGD Login Admin - Live-Vorschau, Medien, Farben */
(function ($) {
	'use strict';
	var form = document.getElementById('mgd-la-form');
	if (!form) { return; }

	var timer = 0;

	// Vorschau: Formular kurz in den iframe schicken, danach alles zurücksetzen
	function preview() {
		var oldAction = form.getAttribute('action');
		var oldTarget = form.getAttribute('target');
		form.setAttribute('action', MGDLogin.previewUrl);
		form.setAttribute('target', 'mgd-la-preview');
		HTMLFormElement.prototype.submit.call(form);
		form.setAttribute('action', oldAction);
		if (oldTarget === null) { form.removeAttribute('target'); } else { form.setAttribute('target', oldTarget); }
	}
	function later() {
		clearTimeout(timer);
		timer = setTimeout(function () { contrast(); preview(); }, 350);
	}

	// Farbwähler
	$('.mgd-la-color').wpColorPicker({ change: later, clear: later });
	$(form).on('input change', 'input, select, textarea', later);

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
		$('.mgd-la-color').each(function () {
			$(this).val('').closest('.wp-picker-container').find('.wp-color-result').css('background-color', '');
		});
		$.each(['radius', 'card_opacity', 'blur', 'card_width', 'overlay'], function (i, k) { $('#mgd-' + k).val(''); });
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

	contrast();
	preview();
})(jQuery);
