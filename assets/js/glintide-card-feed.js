/**
 * 首页内容卡片无限瀑布流加载。
 * 哨兵元素进入视口后按页拉取卡片并追加,追加后派发 glintide:cards-appended,
 * 各卡片脚本(轮播/视频/音乐/点赞)监听该事件为新内容重新绑定。
 */
(function ($) {
	'use strict';

	function initInfiniteFeed() {
		var sentinel = document.querySelector('[data-glintide-infinite]');
		if (!sentinel || sentinel.__glintideInfiniteInited) {
			return;
		}
		sentinel.__glintideInfiniteInited = true;

		var grid = document.querySelector('.glintide-card-grid');
		var loading = sentinel.querySelector('.glintide-card-feed-loading');
		var paged = parseInt(sentinel.getAttribute('data-paged'), 10) || 1;
		var maxPages = parseInt(sentinel.getAttribute('data-max'), 10) || 1;
		var busy = false;
		var done = false;
		var retries = 0;

		function setLoading(show) {
			if (loading) {
				loading.hidden = !show;
			}
		}

		// 连续失败后停止自动重试,改为手动点击重试,避免无限转圈
		function showRetry() {
			done = true;
			setLoading(false);
			if (!loading) {
				return;
			}
			loading.innerHTML = '加载失败，点击重试';
			loading.hidden = false;
			loading.style.cursor = 'pointer';
			loading.onclick = function () {
				loading.onclick = null;
				loading.style.cursor = '';
				loading.innerHTML = '<i class="ri-loader-4-line" aria-hidden="true"></i>加载中…';
				retries = 0;
				done = false;
				loadNext();
			};
		}

		function loadNext() {
			if (busy || done || !sentinel.isConnected) {
				return;
			}
			if (retries >= 3) {
				showRetry();
				return;
			}
			busy = true;
			setLoading(true);

			window.jQuery.post(
				glintide_card_ajax.url,
				{ action: 'glintide_card_feed', paged: paged + 1 },
				function (res) {
					busy = false;
					setLoading(false);
					retries = 0;

					if (!(res && res.success && res.data && res.data.html)) {
						done = true;
						return;
					}

					paged = parseInt(res.data.paged, 10) || (paged + 1);
					grid.insertAdjacentHTML('beforeend', res.data.html);

					// 让轮播/视频/音乐/点赞脚本为新卡片重新绑定
					document.dispatchEvent(new CustomEvent('glintide:cards-appended'));

					if (res.data.hasMore === false || paged >= maxPages) {
						done = true;
					}
				},
				'json'
			).fail(function () {
				busy = false;
				setLoading(false);
				retries++;
				if (retries >= 3) {
					showRetry();
				}
			});
		}

		if (!('IntersectionObserver' in window)) {
			// 兜底:滚动到底部附近直接加载
			window.addEventListener('scroll', function () {
				var rect = sentinel.getBoundingClientRect();
				if (rect.top < window.innerHeight * 1.5) {
					loadNext();
				}
			}, { passive: true });
			return;
		}

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					loadNext();
				}
			});
		}, { rootMargin: '600px 0px' });

		observer.observe(sentinel);

		// 后台标签页等场景下 IntersectionObserver 回调可能不触发,用低频轮询兜底
		var pollTimer = window.setInterval(function () {
			if (done || !sentinel.isConnected) {
				window.clearInterval(pollTimer);
				return;
			}
			var rect = sentinel.getBoundingClientRect();
			if (rect.top < window.innerHeight + 600 && rect.bottom > -600) {
				loadNext();
			}
		}, 800);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initInfiniteFeed);
	} else {
		initInfiniteFeed();
	}

	// pjax 换页 / 瀑布流追加后,为新哨兵重新挂载观察器
	document.addEventListener('glintide:cards-appended', initInfiniteFeed);
}(window.jQuery));
