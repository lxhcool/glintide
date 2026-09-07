/**
 * 照片卡片交互。
 * 卡片使用拼贴网格,点击缩略图后用 lightGallery 打开大图预览。
 * lightGallery 为 UMD 构建,首次点击时按需加载(核心 + 缩略图 + 缩放插件)。
 */
(function ($) {
	'use strict';

	var lightgalleryPromise = null;

	function loadScript(url) {
		return new Promise(function (resolve, reject) {
			var script = document.createElement('script');
			script.src = url;
			script.onload = function () {
				resolve();
			};
			script.onerror = function () {
				reject(new Error('Failed to load ' + url));
			};
			document.head.appendChild(script);
		});
	}

	function loadLightGallery() {
		if (lightgalleryPromise) {
			return lightgalleryPromise;
		}
		var cfg = window.glintide_card_ajax || {};
		if (!cfg.lgUrl || !cfg.lgThumbUrl || !cfg.lgZoomUrl) {
			lightgalleryPromise = Promise.reject(new Error('lightGallery unavailable'));
		} else {
			lightgalleryPromise = loadScript(cfg.lgUrl)
				.then(function () {
					return loadScript(cfg.lgThumbUrl);
				})
				.then(function () {
					return loadScript(cfg.lgZoomUrl);
				});
		}
		return lightgalleryPromise;
	}

	function getGalleryItems(gallery) {
		return Array.prototype.map.call(gallery.querySelectorAll('[data-glintide-photo-open]'), function (tile) {
			var image = tile.querySelector('img');
			var src = image ? (image.getAttribute('data-full-src') || image.getAttribute('src')) : '';
			return {
				src: src,
				// 缩略图复用九宫格小图,缩略图条无需额外加载
				thumb: image ? (image.currentSrc || image.src) : '',
				alt: image ? image.alt : ''
			};
		}).filter(function (item) {
			return item.src;
		});
	}

	function openPhotoLightbox(gallery, index, trigger) {
		var items = getGalleryItems(gallery);
		if (!items.length || !window.lightGallery) {
			return;
		}

		var startIndex = Math.max(0, Math.min(parseInt(index, 10) || 0, items.length - 1));
		var caption = gallery.getAttribute('data-glintide-photo-title') || '';
		var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		var container = document.createElement('div');
		document.body.appendChild(container);

		var galleryInstance = window.lightGallery(container, {
			dynamic: true,
			dynamicEl: items.map(function (item) {
				return {
					src: item.src,
					thumb: item.thumb,
					alt: item.alt,
					subHtml: caption ? '<p class="glintide-lg-caption">' + caption + '</p>' : ''
				};
			}),
			index: startIndex,
			plugins: [window.lgThumbnail, window.lgZoom],
			speed: reducedMotion ? 0 : 400,
			download: false,
			counter: true,
			zoom: true,
			closable: true,
			swipeToClose: !reducedMotion,
			thumbnail: items.length > 1,
			animateThumb: !reducedMotion,
			alignThumbnails: 'left'
		});

		galleryInstance.openGallery(startIndex);

		// lightGallery 实例没有 on() 方法,关闭事件在容器 DOM 元素上以 CustomEvent 派发
		container.addEventListener('lgAfterClose', function () {
			galleryInstance.destroy();
			container.remove();
			if (trigger && trigger.focus) {
				setTimeout(function () {
					trigger.focus();
				}, 0);
			}
		});
	}

	// 事件委托:无限瀑布流追加、PJAX 换页后的新卡片无需重新绑定
	function onTileClick(event) {
		var tile = event.target.closest('[data-glintide-photo-open]');
		if (!tile) {
			return;
		}
		var gallery = tile.closest('[data-glintide-photo-gallery]');
		if (!gallery) {
			return;
		}
		event.preventDefault();
		loadLightGallery().then(function () {
			openPhotoLightbox(gallery, tile.getAttribute('data-glintide-photo-index'), tile);
		}).catch(function () {
			// 加载失败时退化为直接打开原图
			var img = tile.querySelector('img');
			var src = img ? (img.getAttribute('data-full-src') || img.getAttribute('src')) : '';
			if (src) {
				window.open(src, '_blank', 'noopener');
			}
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
		initCardLikes();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCardInteractions);
	} else {
		initCardInteractions();
	}

	// 无限瀑布流追加新卡片后,为新内容重新绑定点赞
	document.addEventListener('glintide:cards-appended', initCardInteractions);

	document.addEventListener('click', onTileClick, false);

}(window.jQuery));
