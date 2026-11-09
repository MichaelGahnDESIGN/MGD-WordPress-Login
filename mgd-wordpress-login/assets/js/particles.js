/* Partikel-Netz für Template "particles" - lokal, ohne Bibliothek */
(function () {
	'use strict';
	var bg = document.querySelector('.mgd-login-bg');
	if (!bg) { return; }

	var body = document.body;
	var style = getComputedStyle(body);
	var canvas = document.createElement('canvas');
	var ctx = canvas.getContext('2d');
	if (!ctx) { return; }
	bg.appendChild(canvas);

	// Farbe aus --mgd-accent (immer Hex)
	var hex = (style.getPropertyValue('--mgd-accent') || '#4cc9f0').trim().replace('#', '');
	if (hex.length === 3) { hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]; }
	var rgb = [parseInt(hex.substr(0, 2), 16) || 76, parseInt(hex.substr(2, 2), 16) || 201, parseInt(hex.substr(4, 2), 16) || 240];
	var speed = parseFloat(style.getPropertyValue('--mgd-speed')) || 1;
	var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	var w = 0, h = 0, dots = [], mouse = { x: -999, y: -999 }, raf = 0;

	function resize() {
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		w = window.innerWidth; h = window.innerHeight;
		canvas.width = w * dpr; canvas.height = h * dpr;
		ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		var count = Math.max(28, Math.min(110, Math.round(w * h / 15000)));
		dots = [];
		for (var i = 0; i < count; i++) {
			dots.push({ x: Math.random() * w, y: Math.random() * h, vx: (Math.random() - .5) * .45, vy: (Math.random() - .5) * .45, r: Math.random() * 1.6 + .8 });
		}
	}

	function frame() {
		ctx.clearRect(0, 0, w, h);
		var link = Math.min(150, Math.max(100, w / 9));
		for (var i = 0; i < dots.length; i++) {
			var a = dots[i];
			if (!still) {
				a.x += a.vx * speed; a.y += a.vy * speed;
				if (a.x < 0 || a.x > w) { a.vx *= -1; }
				if (a.y < 0 || a.y > h) { a.vy *= -1; }
			}
			ctx.beginPath();
			ctx.arc(a.x, a.y, a.r, 0, 6.2832);
			ctx.fillStyle = 'rgba(' + rgb + ',.85)';
			ctx.fill();
			for (var j = i + 1; j < dots.length; j++) {
				var b = dots[j], dx = a.x - b.x, dy = a.y - b.y, d = Math.sqrt(dx * dx + dy * dy);
				if (d < link) {
					ctx.strokeStyle = 'rgba(' + rgb + ',' + (0.28 * (1 - d / link)).toFixed(3) + ')';
					ctx.lineWidth = 1;
					ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
				}
			}
			// Maus zieht Linien zu den Punkten
			var mx = a.x - mouse.x, my = a.y - mouse.y, md = Math.sqrt(mx * mx + my * my);
			if (md < 170) {
				ctx.strokeStyle = 'rgba(' + rgb + ',' + (0.55 * (1 - md / 170)).toFixed(3) + ')';
				ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(mouse.x, mouse.y); ctx.stroke();
				if (!still) { a.x += mx * 0.0009; a.y += my * 0.0009; }
			}
		}
		if (!still) { raf = requestAnimationFrame(frame); }
	}

	window.addEventListener('resize', function () { resize(); if (still) { frame(); } });
	window.addEventListener('pointermove', function (e) { mouse.x = e.clientX; mouse.y = e.clientY; });
	window.addEventListener('pointerleave', function () { mouse.x = mouse.y = -999; });
	// Tab im Hintergrund: Animation pausieren
	document.addEventListener('visibilitychange', function () {
		if (still) { return; }
		if (document.hidden) { cancelAnimationFrame(raf); } else { raf = requestAnimationFrame(frame); }
	});

	resize();
	frame();
})();
