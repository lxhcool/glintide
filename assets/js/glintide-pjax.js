/**
 * Glintide PJAX 无刷新导航。
 *
 * 拦截站内链接,只替换左右栏与中央列,右栏工具区(音乐播放器)原样保留,
 * 播放不中断;换页后派发 glintide:cards-appended 让各卡片脚本重新绑定。
 * 任何一步异常都退回整页跳转,保证可用性。
 */
(function () {
	'use strict';

	// 参与替换的容器;右栏(音乐播放器/用户工具)不替换
	var SWAP_SELECTORS = [
		'.glintide-site-column--left',
		'.glintide-site-column--center'
	];

	// 不走 pjax 的路径前缀
	var EXCLUDED_PATHS = ['/wp-admin', '/wp-login.php', '/cart', '/checkout', '/my-account', '/feed'];

	// 非 HTML 资源后缀
	var EXCLUDED_EXTS = /\.(jpg|jpeg|png|gif|webp|avif|svg|ico|css|js|zip|rar|7z|pdf|mp3|mp4|m4a|ogg|wav|webm|txt|xml)(\?|#|$)/i;

	var currentRequest = null;

	function isSameOrigin(url) {
		try {
			return new URL(url, window.location.href).origin === window.location.origin;
		} catch (e) {
			return false;
		}
	}

	function isExcluded(url) {
		var u = new URL(url, window.location.href);
		var path = u.pathname;

		if (EXCLUDED_PATHS.some(function (p) { return path.indexOf(p) === 0; })) {
			return true;
		}
		if (EXCLUDED_EXTS.test(path)) {
			return true;
		}
		// 纯锚点跳转不处理
		return false;
	}

	function shouldHandleLink(link, event) {
		if (!link || event.defaultPrevented) {
			return false;
		}
		if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return false;
		}
		var target = (link.getAttribute('target') || '').toLowerCase();
		if (target && target !== '_self') {
			return false;
		}
		if (link.hasAttribute('download') || link.hasAttribute('data-glintide-no-pjax') || link.hasAttribute('data-no-pjax')) {
			return false;
		}
		if (link.closest('#wpadminbar')) {
			return false;
		}
		var href = link.getAttribute('href');
		if (!href || href.charAt(0) === '#') {
			return false;
		}
		if (!isSameOrigin(href) || isExcluded(href)) {
			return false;
		}
		return true;
	}

	function setBar(show) {
		var bar = document.querySelector('.glintide-pjax-bar');
		if (!bar) {
			bar = document.createElement('div');
			bar.className = 'glintide-pjax-bar';
			document.body.appendChild(bar);
		}
		bar.classList.toggle('is-loading', !!show);
	}

	function syncCurrentThemeStyle() {
		var current = document.getElementById('glintide-style-css');
		var version = window.glintideStyleVersion || '';

		if (!current || !version) {
			return;
		}

		var url = new URL(current.href, window.location.href);
		if (url.searchParams.get('ver') !== version) {
			url.searchParams.set('ver', version);
			current.href = url.toString();
		}
	}

	function syncThemeStyles(newDoc) {
		var incoming = newDoc.getElementById('glintide-style-css');
		var href = incoming ? incoming.getAttribute('href') : '';
		var current = document.getElementById('glintide-style-css');

		if (current && href && current.getAttribute('href') !== href) {
			current.setAttribute('href', href);
		}
		syncCurrentThemeStyle();
	}

	function pauseMedia() {
		document.querySelectorAll('video, audio').forEach(function (media) {
			if (!media.paused) {
				media.pause();
			}
		});
	}

	function swapContent(newDoc, url, push) {
		var pairs = [];
		for (var i = 0; i < SWAP_SELECTORS.length; i++) {
			var sel = SWAP_SELECTORS[i];
			var oldEl = document.querySelector(sel);
			var newEl = newDoc.querySelector(sel);
			if (!oldEl || !newEl) {
				return false;
			}
			pairs.push([oldEl, newEl]);
		}

		pauseMedia();

		pairs.forEach(function (pair) {
			pair[0].innerHTML = pair[1].innerHTML;
		});

		syncThemeStyles(newDoc);
		document.title = newDoc.title || document.title;

		if (push) {
			window.history.pushState({ glintidePjax: true }, '', url);
		}
		window.scrollTo(0, 0);

		// 让轮播/视频/音乐/点赞/无限加载脚本重新绑定新内容
		document.dispatchEvent(new CustomEvent('glintide:cards-appended'));
		document.dispatchEvent(new CustomEvent('glintide:pjax-after', { detail: { url: url } }));
		return true;
	}

	function navigate(url, push) {
		if (currentRequest) {
			currentRequest.abort();
		}

		setBar(true);
		var controller = new AbortController();
		currentRequest = controller;

		fetch(url, {
			credentials: 'same-origin',
			signal: controller.signal,
			headers: { 'X-Glintide-Pjax': '1' }
		}).then(function (res) {
			if (!res.ok) {
				throw new Error('HTTP ' + res.status);
			}
			var detailId = new URL(res.url || url, window.location.href).searchParams.get('glintide_detail');
			if (/^[1-9]\d*$/.test(detailId || '')) {
				if (currentRequest === controller) {
					currentRequest = null;
					setBar(false);
					document.dispatchEvent(new CustomEvent('glintide:open-detail', { detail: { postId: detailId } }));
				}
				return null;
			}
			return res.text();
		}).then(function (html) {
			if (currentRequest !== controller) {
				return;
			}
			currentRequest = null;
			setBar(false);

			var doc = new DOMParser().parseFromString(html, 'text/html');
			if (!swapContent(doc, url, push)) {
				// 结构对不上(如 404/搜索页),整页跳转兜底
				window.location.href = url;
			}
		}).catch(function (err) {
			if (currentRequest !== controller) {
				return;
			}
			currentRequest = null;
			setBar(false);
			if (err && err.name === 'AbortError') {
				return;
			}
			window.location.href = url;
		});
	}

	syncCurrentThemeStyle();

	document.addEventListener('click', function (event) {
		var link = event.target && event.target.closest ? event.target.closest('a') : null;
		if (!shouldHandleLink(link, event)) {
			return;
		}

		var url = new URL(link.href, window.location.href).href;
		if (url === window.location.href) {
			// 点击当前页链接:只回到顶部
			event.preventDefault();
			window.scrollTo({ top: 0, behavior: 'smooth' });
			return;
		}

		event.preventDefault();
		navigate(url, true);
	}, false);

	window.addEventListener('popstate', function () {
		navigate(window.location.href, false);
	});

	if (window.history && 'scrollRestoration' in window.history) {
		window.history.scrollRestoration = 'manual';
	}
}());
