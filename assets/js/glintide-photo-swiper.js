/**
 * 照片卡片交互。
 * 卡片使用拼贴网格,点击缩略图后在大图预览层内用 Swiper 浏览。
 */
(function ($) {
	'use strict';

	var photoLightbox = null;
	var photoLightboxSwiper = null;
	var photoLightboxTrigger = null;
	var photoLightboxImages = [];
	var photoLightboxIndex = 0;

	function reducedMotion() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	function ensurePhotoLightbox() {
		if (photoLightbox) {
			return photoLightbox;
		}

		photoLightbox = document.createElement('div');
		photoLightbox.className = 'glintide-photo-lightbox';
		photoLightbox.setAttribute('aria-hidden', 'true');
		photoLightbox.innerHTML =
			'<div class="glintide-photo-lightbox-backdrop" data-glintide-photo-lightbox-close></div>' +
			'<div class="glintide-photo-lightbox-dialog" role="dialog" aria-modal="true" aria-label="照片预览" tabindex="-1">' +
			'<button type="button" class="glintide-photo-lightbox-close" data-glintide-photo-lightbox-close aria-label="关闭照片预览"><i class="ri-close-line" aria-hidden="true"></i></button>' +
			'<div class="glintide-photo-lightbox-swiper swiper" data-glintide-photo-lightbox-swiper>' +
			'<div class="swiper-wrapper"></div>' +
			'</div>' +
			'<button type="button" class="glintide-photo-lightbox-nav glintide-photo-lightbox-nav--prev" data-glintide-photo-prev aria-label="上一张"><i class="ri-arrow-left-line" aria-hidden="true"></i></button>' +
			'<button type="button" class="glintide-photo-lightbox-nav glintide-photo-lightbox-nav--next" data-glintide-photo-next aria-label="下一张"><i class="ri-arrow-right-line" aria-hidden="true"></i></button>' +
			'<div class="glintide-photo-lightbox-foot"><span class="glintide-photo-lightbox-count" data-glintide-photo-lightbox-count></span><span class="glintide-photo-lightbox-caption" data-glintide-photo-lightbox-caption></span></div>' +
			'</div>';
		document.body.appendChild(photoLightbox);

		photoLightbox.addEventListener('click', function (event) {
			if (event.target === photoLightbox || event.target.closest('[data-glintide-photo-lightbox-close]')) {
				closePhotoLightbox();
			}
		});

		return photoLightbox;
	}

	function getGalleryImages(gallery) {
		return Array.prototype.map.call(gallery.querySelectorAll('[data-glintide-photo-open]'), function (tile) {
			var image = tile.querySelector('img');
			return {
				src: image ? (image.getAttribute('data-full-src') || image.currentSrc || image.src) : '',
				alt: image ? image.alt : ''
			};
		}).filter(function (image) {
			return image.src;
		});
	}

	function renderLightboxSlides() {
		var wrapper = photoLightbox.querySelector('.swiper-wrapper');
		wrapper.innerHTML = '';

		photoLightboxImages.forEach(function (image) {
			var slide = document.createElement('div');
			var img = document.createElement('img');
			slide.className = 'swiper-slide';
			img.src = image.src;
			img.alt = image.alt;
			img.decoding = 'async';
			img.draggable = false;
			slide.appendChild(img);
			wrapper.appendChild(slide);
		});
	}

	function updateLightboxMeta(index) {
		var count = photoLightbox.querySelector('[data-glintide-photo-lightbox-count]');
		var caption = photoLightbox.querySelector('[data-glintide-photo-lightbox-caption]');
		var title = photoLightbox.getAttribute('data-glintide-photo-title') || '';

		photoLightboxIndex = Math.max(0, Math.min(index, photoLightboxImages.length - 1));
		count.textContent = (photoLightboxIndex + 1) + '/' + photoLightboxImages.length;
		caption.textContent = title;
	}

	function openPhotoLightbox(gallery, index, trigger) {
		var root = ensurePhotoLightbox();
		photoLightboxImages = getGalleryImages(gallery);
		if (!photoLightboxImages.length) {
			return;
		}

		photoLightboxTrigger = trigger;
		photoLightboxIndex = Math.max(0, Math.min(parseInt(index, 10) || 0, photoLightboxImages.length - 1));
		root.setAttribute('data-glintide-photo-title', gallery.getAttribute('data-glintide-photo-title') || '');
		renderLightboxSlides();
		root.classList.toggle('is-single', photoLightboxImages.length < 2);
		root.classList.add('is-open');
		root.setAttribute('aria-hidden', 'false');
		document.body.classList.add('glintide-photo-lightbox-open');

		if (photoLightboxSwiper) {
			photoLightboxSwiper.destroy(true, true);
			photoLightboxSwiper = null;
		}

		if (window.Swiper) {
			photoLightboxSwiper = new window.Swiper(root.querySelector('[data-glintide-photo-lightbox-swiper]'), {
				initialSlide: photoLightboxIndex,
				slidesPerView: 1,
				spaceBetween: 0,
				speed: reducedMotion() ? 0 : 260,
				grabCursor: photoLightboxImages.length > 1,
				keyboard: {
					enabled: true
				},
				observer: true,
				observeParents: true,
				navigation: {
					prevEl: root.querySelector('[data-glintide-photo-prev]'),
					nextEl: root.querySelector('[data-glintide-photo-next]')
				},
				a11y: {
					enabled: true
				},
				on: {
					init: function (swiper) { updateLightboxMeta(swiper.activeIndex); },
					slideChange: function (swiper) { updateLightboxMeta(swiper.activeIndex); }
				}
			});
		} else {
			updateLightboxMeta(photoLightboxIndex);
		}

		setTimeout(function () {
			var close = root.querySelector('.glintide-photo-lightbox-close');
			if (close) {
				close.focus();
			}
		}, 0);
	}

	function closePhotoLightbox() {
		if (!photoLightbox || !photoLightbox.classList.contains('is-open')) {
			return;
		}

		photoLightbox.classList.remove('is-open');
		photoLightbox.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('glintide-photo-lightbox-open');

		if (photoLightboxSwiper) {
			photoLightboxSwiper.destroy(true, true);
			photoLightboxSwiper = null;
		}

		if (photoLightboxTrigger && photoLightboxTrigger.focus) {
			photoLightboxTrigger.focus();
		}
		photoLightboxTrigger = null;
	}

	function initPhotoGalleries() {
		document.querySelectorAll('[data-glintide-photo-gallery]').forEach(function (gallery) {
			if (gallery.__glintidePhotoGalleryInited) {
				return;
			}
			gallery.__glintidePhotoGalleryInited = true;

			gallery.querySelectorAll('[data-glintide-photo-open]').forEach(function (trigger) {
				trigger.addEventListener('click', function (event) {
					event.preventDefault();
					openPhotoLightbox(gallery, trigger.getAttribute('data-glintide-photo-index'), trigger);
				});
			});
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
		initPhotoGalleries();
		initCardLikes();
	}

	document.addEventListener('keydown', function (event) {
		if (!photoLightbox || !photoLightbox.classList.contains('is-open')) {
			return;
		}
		if (event.key === 'Escape') {
			event.preventDefault();
			closePhotoLightbox();
		}
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCardInteractions);
	} else {
		initCardInteractions();
	}

	// 无限瀑布流追加新卡片后,为新内容重新绑定照片预览和点赞
	document.addEventListener('glintide:cards-appended', initCardInteractions);

}(window.jQuery));
