/**
 * 内容卡片交互。
 * 使用 Swiper 处理照片背景轮播和点赞，并为无限加载追加的卡片重新绑定事件。
 */
(function ($) {
	'use strict';

	function initPhotoCarousels() {
		var carousels = document.querySelectorAll('[data-glintide-photo-carousel]');
		if (!window.Swiper) {
			return;
		}

		carousels.forEach(function (carousel) {
			if (carousel.__glintidePhotoSwiperInited) {
				return;
			}

			var container  = carousel.querySelector('[data-glintide-photo-viewport]');
			var pagination = carousel.querySelector('[data-glintide-photo-pagination]');
			var slides     = Array.prototype.slice.call(carousel.querySelectorAll('[data-glintide-photo-slide]'));

			if (!container || slides.length < 2) {
				return;
			}

			function updateSlideState(swiper) {
				slides.forEach(function (slide, index) {
					slide.setAttribute('aria-hidden', index === swiper.activeIndex ? 'false' : 'true');
				});
			}

			var swiper = new window.Swiper(container, {
				slidesPerView: 1,
				spaceBetween: 0,
				speed: 260,
				threshold: 4,
				grabCursor: true,
				simulateTouch: true,
				allowTouchMove: true,
				resistance: true,
				resistanceRatio: 0.75,
				watchOverflow: true,
				observer: true,
				observeParents: true,
				a11y: {
					enabled: true
				},
				pagination: pagination ? {
					el: pagination,
					clickable: true
				} : undefined,
				on: {
					init: updateSlideState,
					slideChange: updateSlideState
				}
			});

			carousel.__glintidePhotoSwiperInited = true;
			updateSlideState(swiper);
		});
	}

	// 内容卡片点赞(爱心)
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
					var ajaxUrl = window.glintide_card_ajax && window.glintide_card_ajax.url
						? window.glintide_card_ajax.url
						: '/wp-admin/admin-ajax.php';
					window.jQuery.post(
						ajaxUrl,
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

	function initCardInteractions() {
		initPhotoCarousels();
		initCardLikes();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCardInteractions);
	} else {
		initCardInteractions();
	}

	// 无限瀑布流追加新卡片后,为新内容重新绑定轮播和点赞
	document.addEventListener('glintide:cards-appended', initCardInteractions);

}(window.jQuery));
