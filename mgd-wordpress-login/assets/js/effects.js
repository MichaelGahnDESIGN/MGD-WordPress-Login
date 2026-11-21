/* MGD Login Effekte - Partikel, Lichtkegel, 3D-Karte. Lokal, ohne Bibliothek */
(function () {
	'use strict';
	var conf = window.MGDLoginFX || {};
	var bg = document.querySelector('.mgd-login-bg');
	if (!bg) { return; }

	var body = document.body;
	var css = getComputedStyle(body);
	var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var speed = parseFloat(css.getPropertyValue('--mgd-speed')) || 1;
	var amount = Math.max(0.1, Math.min(2, parseFloat(conf.amount) || 1));

	// Akzent als RGB (immer Hex aus PHP)
	function rgbOf(name, fb) {
		var hex = (css.getPropertyValue(name) || fb).trim().replace('#', '');
		if (hex.length === 3) { hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]; }
		var n = parseInt(hex, 16);
		if (isNaN(n) || hex.length !== 6) { n = parseInt(fb.replace('#', ''), 16); }
		return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
	}
	var acc = rgbOf('--mgd-accent', '#8b6cff');
	var txt = rgbOf('--mgd-text', '#ffffff');
	function rgba(c, a) { return 'rgba(' + c[0] + ',' + c[1] + ',' + c[2] + ',' + a.toFixed(3) + ')'; }

	var mouse = { x: -9999, y: -9999, on: false };
	window.addEventListener('pointermove', function (e) { mouse.x = e.clientX; mouse.y = e.clientY; mouse.on = true; }, { passive: true });
	document.addEventListener('pointerleave', function () { mouse.on = false; mouse.x = mouse.y = -9999; });

	/* ---------- Lichtkegel folgt der Maus ---------- */
	if (conf.light && !still) {
		var lx = 50, ly = 30, tx = 50, ty = 30;
		window.addEventListener('pointermove', function (e) { tx = e.clientX / window.innerWidth * 100; ty = e.clientY / window.innerHeight * 100; }, { passive: true });
		(function glide() {
			lx += (tx - lx) * 0.08; ly += (ty - ly) * 0.08;
			body.style.setProperty('--mgd-lx', lx.toFixed(2) + '%');
			body.style.setProperty('--mgd-ly', ly.toFixed(2) + '%');
			requestAnimationFrame(glide);
		})();
	}

	/* ---------- Karte neigt sich zur Maus ---------- */
	if (conf.tilt && !still && window.matchMedia('(hover: hover)').matches) {
		var card = document.querySelector('#login form');
		if (card) {
			var rx = 0, ry = 0, trx = 0, try_ = 0;
			window.addEventListener('pointermove', function (e) {
				var r = card.getBoundingClientRect();
				var cx = (e.clientX - (r.left + r.width / 2)) / window.innerWidth;
				var cy = (e.clientY - (r.top + r.height / 2)) / window.innerHeight;
				try_ = Math.max(-1, Math.min(1, cx * 2)) * 7;
				trx = Math.max(-1, Math.min(1, cy * 2)) * -6;
				card.style.setProperty('--mgd-gx', ((e.clientX - r.left) / r.width * 100).toFixed(1) + '%');
				card.style.setProperty('--mgd-gy', ((e.clientY - r.top) / r.height * 100).toFixed(1) + '%');
			}, { passive: true });
			// warten bis die Einblende-Animation durch ist, sonst ruckelt es
			setTimeout(function () {
				card.classList.add('mgd-tilt-on');
				(function tilt() {
					rx += (trx - rx) * 0.1; ry += (try_ - ry) * 0.1;
					card.style.transform = 'perspective(900px) rotateX(' + rx.toFixed(2) + 'deg) rotateY(' + ry.toFixed(2) + 'deg)';
					requestAnimationFrame(tilt);
				})();
			}, 1100);
		}
	}

	/* ---------- Partikel ---------- */
	var mode = conf.particles || 'none';
	if (mode === 'none') { return; }

	var canvas = document.createElement('canvas');
	canvas.className = 'mgd-fx-canvas';
	var ctx = canvas.getContext('2d');
	if (!ctx) { return; }
	bg.appendChild(canvas);

	var w = 0, h = 0, dpr = 1, items = [], raf = 0, t = 0;
	var R = Math.random;

	// Menge je Modus bei 100 %, skaliert mit Fläche
	var base = { stars: 160, snow: 120, fireflies: 46, bubbles: 30, network: 80, warp: 260, confetti: 90 };

	function make(fresh) {
		var p = { x: R() * w, y: R() * h, s: R(), a: R() * 6.283, v: R() };
		switch (mode) {
			case 'stars': p.r = R() * 1.3 + .3; p.tw = R() * 2 + .6; p.vx = (R() - .5) * .04; break;
			case 'snow': p.r = R() * 2.6 + .8; p.vy = p.r * .25 + .2; p.sw = R() * 1.2 + .4; if (!fresh) { p.y = -10; } break;
			case 'fireflies': p.r = R() * 2 + 1.2; p.vx = (R() - .5) * .5; p.vy = (R() - .5) * .5; p.ph = R() * 6.283; break;
			case 'bubbles': p.r = R() * 26 + 8; p.vy = -(R() * .45 + .25); p.sw = R() * .8 + .3; if (!fresh) { p.y = h + p.r + 10; } break;
			case 'network': p.r = R() * 1.6 + .8; p.vx = (R() - .5) * .45; p.vy = (R() - .5) * .45; break;
			case 'warp': p.x = (R() - .5) * w * 2; p.y = (R() - .5) * h * 2; p.z = fresh ? R() * w : w; p.pz = p.z; break;
			case 'confetti': p.r = R() * 5 + 4; p.vy = R() * .9 + .6; p.vx = (R() - .5) * .6; p.rot = R() * 6.283; p.vr = (R() - .5) * .08; p.c = [acc, txt, [255, 196, 70], [255, 92, 160], [80, 210, 255]][Math.floor(R() * 5)]; if (!fresh) { p.y = -12; } break;
		}
		return p;
	}

	function resize() {
		dpr = Math.min(window.devicePixelRatio || 1, 2);
		// Größe der Hintergrund-Fläche, bei Split nur die linke Hälfte
		w = bg.clientWidth || window.innerWidth; h = bg.clientHeight || window.innerHeight;
		canvas.width = w * dpr; canvas.height = h * dpr;
		ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		var area = Math.min(1.6, (w * h) / (1440 * 900));
		var n = Math.max(12, Math.round((base[mode] || 60) * area * amount));
		items = [];
		for (var i = 0; i < n; i++) { items.push(make(true)); }
	}

	function frame() {
		t += 0.016 * speed;
		ctx.clearRect(0, 0, w, h);
		var i, p;

		if (mode === 'network') {
			var link = Math.min(150, Math.max(100, w / 9));
			for (i = 0; i < items.length; i++) {
				p = items[i];
				if (!still) { p.x += p.vx * speed; p.y += p.vy * speed; if (p.x < 0 || p.x > w) { p.vx *= -1; } if (p.y < 0 || p.y > h) { p.vy *= -1; } }
				ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 6.283); ctx.fillStyle = rgba(acc, .85); ctx.fill();
				for (var j = i + 1; j < items.length; j++) {
					var q = items[j], dx = p.x - q.x, dy = p.y - q.y, d = Math.sqrt(dx * dx + dy * dy);
					if (d < link) { ctx.strokeStyle = rgba(acc, .28 * (1 - d / link)); ctx.lineWidth = 1; ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y); ctx.stroke(); }
				}
				var mx = p.x - mouse.x, my = p.y - mouse.y, md = Math.sqrt(mx * mx + my * my);
				if (md < 170) {
					ctx.strokeStyle = rgba(acc, .55 * (1 - md / 170)); ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(mouse.x, mouse.y); ctx.stroke();
					if (!still) { p.x += mx * .0009; p.y += my * .0009; }
				}
			}
		} else if (mode === 'warp') {
			// Sternenflug: Punkte kommen aus der Mitte, Maus verschiebt den Fluchtpunkt leicht
			var cx = w / 2 + (mouse.on ? (mouse.x - w / 2) * .08 : 0), cy = h / 2 + (mouse.on ? (mouse.y - h / 2) * .08 : 0);
			for (i = 0; i < items.length; i++) {
				p = items[i];
				p.pz = p.z;
				if (!still) { p.z -= 4.2 * speed; }
				if (p.z < 1) { items[i] = make(false); continue; }
				var sx = cx + p.x / p.z * w * .5, sy = cy + p.y / p.z * h * .5;
				var px = cx + p.x / p.pz * w * .5, py = cy + p.y / p.pz * h * .5;
				if (sx < -50 || sx > w + 50 || sy < -50 || sy > h + 50) { items[i] = make(false); continue; }
				var k = 1 - p.z / w;
				ctx.strokeStyle = rgba(k > .7 ? acc : txt, Math.min(1, k * 1.2));
				ctx.lineWidth = Math.max(.4, k * 2.2);
				ctx.beginPath(); ctx.moveTo(px, py); ctx.lineTo(sx, sy); ctx.stroke();
			}
		} else {
			for (i = 0; i < items.length; i++) {
				p = items[i];
				if (mode === 'stars') {
					if (!still) { p.x += p.vx * speed; if (p.x < 0) { p.x = w; } if (p.x > w) { p.x = 0; } }
					var tw = still ? .7 : .45 + .55 * Math.abs(Math.sin(t * p.tw + p.a));
					ctx.fillStyle = rgba(p.s > .85 ? acc : txt, tw * (.5 + p.s * .5));
					ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 6.283); ctx.fill();
					if (p.r > 1.3 && tw > .9) { ctx.strokeStyle = rgba(txt, (tw - .9) * 3); ctx.lineWidth = .6; ctx.beginPath(); ctx.moveTo(p.x - p.r * 4, p.y); ctx.lineTo(p.x + p.r * 4, p.y); ctx.moveTo(p.x, p.y - p.r * 4); ctx.lineTo(p.x, p.y + p.r * 4); ctx.stroke(); }
				} else if (mode === 'snow') {
					if (!still) { p.y += p.vy * speed; p.x += Math.sin(t * p.sw + p.a) * .4 * speed; }
					if (p.y > h + 10) { items[i] = make(false); continue; }
					ctx.fillStyle = rgba([255, 255, 255], .45 + p.s * .45);
					ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 6.283); ctx.fill();
				} else if (mode === 'fireflies') {
					if (!still) {
						p.vx += (R() - .5) * .04; p.vy += (R() - .5) * .04; p.vx *= .98; p.vy *= .98;
						p.x += p.vx * speed; p.y += p.vy * speed;
						if (p.x < -20) { p.x = w + 20; } if (p.x > w + 20) { p.x = -20; } if (p.y < -20) { p.y = h + 20; } if (p.y > h + 20) { p.y = -20; }
					}
					var gl = still ? .7 : .35 + .65 * Math.max(0, Math.sin(t * 1.4 + p.ph));
					var g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.r * 7);
					g.addColorStop(0, rgba([255, 240, 190], gl)); g.addColorStop(.25, rgba(acc, gl * .55)); g.addColorStop(1, rgba(acc, 0));
					ctx.fillStyle = g; ctx.beginPath(); ctx.arc(p.x, p.y, p.r * 7, 0, 6.283); ctx.fill();
				} else if (mode === 'bubbles') {
					if (!still) { p.y += p.vy * speed; p.x += Math.sin(t * p.sw + p.a) * .35 * speed; }
					if (p.y < -p.r - 10) { items[i] = make(false); continue; }
					var bg2 = ctx.createRadialGradient(p.x - p.r * .35, p.y - p.r * .35, p.r * .1, p.x, p.y, p.r);
					bg2.addColorStop(0, 'rgba(255,255,255,.55)'); bg2.addColorStop(.6, rgba(acc, .06)); bg2.addColorStop(1, rgba(acc, .22));
					ctx.fillStyle = bg2; ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 6.283); ctx.fill();
					ctx.strokeStyle = 'rgba(255,255,255,.55)'; ctx.lineWidth = 1; ctx.stroke();
				} else if (mode === 'confetti') {
					if (!still) { p.y += p.vy * speed; p.x += (p.vx + Math.sin(t + p.a) * .3) * speed; p.rot += p.vr * speed; }
					if (p.y > h + 14) { items[i] = make(false); continue; }
					ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rot);
					ctx.fillStyle = rgba(p.c, .85); ctx.fillRect(-p.r / 2, -p.r / 4, p.r, p.r / 2 * Math.abs(Math.cos(t * 2 + p.a)) + 1);
					ctx.restore();
				}
			}
		}
		if (!still) { raf = requestAnimationFrame(frame); }
	}

	window.addEventListener('resize', function () { resize(); if (still) { frame(); } });
	// Tab im Hintergrund: Pause, spart Akku
	document.addEventListener('visibilitychange', function () {
		if (still) { return; }
		if (document.hidden) { cancelAnimationFrame(raf); } else { raf = requestAnimationFrame(frame); }
	});

	resize();
	frame();
})();
