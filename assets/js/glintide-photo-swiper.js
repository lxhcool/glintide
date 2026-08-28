/**
 * 照片卡片轮播初始化。
 * 对 [data-glintide-photo] 容器内嵌的 Swiper 进行分页/自动播放配置。
 */
(function ($) {
	'use strict';

	function initPhotoSwipers() {
		if (typeof window.Swiper === 'undefined') {
			return;
		}

		var roots = document.querySelectorAll('[data-glintide-photo]');
		roots.forEach(function (root) {
			if (root.__glintidePhotoInited) {
				return;
			}
			root.__glintidePhotoInited = true;

			var swiperEl = root.querySelector('.swiper');
			if (!swiperEl) {
				return;
			}

			var paginationEl = root.querySelector('[data-glintide-photo-pagination]');

			new window.Swiper(swiperEl, {
				lazy: false,
				loop: true,
				speed: 500,
				grabCursor: true,
				allowTouchMove: true,
				autoplay: paginationEl ? { delay: 3500, disableOnInteraction: false } : false,
				pagination: paginationEl ? { el: paginationEl, clickable: true } : false
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initPhotoSwipers);
	} else {
		initPhotoSwipers();
	}

	// 内容卡片点赞(爱心)按钮
	function initCardLikes() {
		var buttons = document.querySelectorAll('[data-glintide-like]');
		buttons.forEach(function (btn) {
			if (btn.__glintideLikeInited) {
				return;
			}
			btn.__glintideLikeInited = true;

			btn.addEventListener('click', function (event) {
				event.preventDefault();
				if (btn.dataset.glintideLiking) {
					return;
				}
				btn.dataset.glintideLiking = '1';

				var postId = btn.getAttribute('data-glintide-like');
				var iconEl = btn.querySelector('i');
				var liked  = btn.classList.contains('is-liked');

				// 乐观更新
				if (liked) {
					btn.classList.remove('is-liked');
					btn.setAttribute('aria-pressed', 'false');
					if (iconEl) { iconEl.classList.remove('ri-heart-3-fill'); iconEl.classList.add('ri-heart-3-line'); }
				} else {
					btn.classList.add('is-liked');
					btn.setAttribute('aria-pressed', 'true');
					if (iconEl) { iconEl.classList.remove('ri-heart-3-line'); iconEl.classList.add('ri-heart-3-fill'); }
				}

				if (window.jQuery && window.jQuery.post) {
					window.jQuery.post(
						glintide_card_ajax.url,
						{ action: 'glintide_card_like', post_id: postId },
						function (res) {
							if (res && res.success && iconEl) {
								if (res.data.liked) {
									btn.classList.add('is-liked');
									iconEl.classList.remove('ri-heart-3-line');
									iconEl.classList.add('ri-heart-3-fill');
								} else {
									btn.classList.remove('is-liked');
									iconEl.classList.remove('ri-heart-3-fill');
									iconEl.classList.add('ri-heart-3-line');
								}
							}
						},
						'json'
					).always(function () {
						delete btn.dataset.glintideLiking;
					});
				} else {
					delete btn.dataset.glintideLiking;
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCardLikes);
	} else {
		initCardLikes();
	}

	// 音乐卡片:播放/暂停切换
	function initMusicPlayers() {
		var roots = document.querySelectorAll('[data-glintide-music]');
		roots.forEach(function (root) {
			var toggle = root.querySelector('[data-glintide-music-toggle]');
			if (!toggle || toggle.__glintideMusicInited) {
				return;
			}
			toggle.__glintideMusicInited = true;

			var audio = root.querySelector('[data-glintide-music-audio]');
			var icon  = toggle.querySelector('i');

			function setIcon(playing) {
				if (!icon) { return; }
				if (playing) {
					icon.classList.remove('ri-play-fill');
					icon.classList.add('ri-pause-fill');
				} else {
					icon.classList.remove('ri-pause-fill');
					icon.classList.add('ri-play-fill');
				}
				toggle.setAttribute('aria-label', playing ? '暂停' : '播放');
				toggle.setAttribute('data-state', playing ? 'playing' : 'paused');
			}

			toggle.addEventListener('click', function (event) {
				event.preventDefault();
				if (!audio) {
					// 网易云:简单提示用户去 iframe 播放(嵌入 iframe 内置控件,我们的按钮主要作 UI 装饰)
					if (root.querySelector('[data-glintide-music-netease]')) {
						root.scrollIntoView({ behavior: 'smooth', block: 'center' });
					}
					return;
				}

				if (audio.paused) {
					document.querySelectorAll('audio.glintide-card-music-audio').forEach(function (a) {
						if (a !== audio) { a.pause(); }
					});
					document.querySelectorAll('[data-glintide-music-toggle]').forEach(function (b) {
						if (b !== toggle) { b.setAttribute('data-state', 'paused'); var ii = b.querySelector('i'); if (ii) { ii.classList.remove('ri-pause-fill'); ii.classList.add('ri-play-fill'); } }
					});

					var p = audio.play();
					if (p && typeof p.then === 'function') {
						p.then(function () { setIcon(true); }).catch(function () { setIcon(false); });
					} else {
						setIcon(true);
					}
				} else {
					audio.pause();
					setIcon(false);
				}
			});

			if (audio) {
				audio.addEventListener('ended', function () { setIcon(false); });
				audio.addEventListener('pause', function () {
					if (audio.currentTime === 0 || audio.ended) {
						setIcon(false);
					}
				});
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMusicPlayers);
	} else {
		initMusicPlayers();
	}
}(window.jQuery));
