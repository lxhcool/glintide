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

	// 无限瀑布流追加新卡片后,为新内容重新初始化轮播
	document.addEventListener('glintide:cards-appended', initPhotoSwipers);

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

	// 无限瀑布流追加新卡片后,为新内容的点赞按钮重新绑定
	document.addEventListener('glintide:cards-appended', initCardLikes);

}(window.jQuery));
