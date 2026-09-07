/**
 * 首页内容卡片流:类型 Tab 筛选 + 关键词搜索 + 无限瀑布流加载。
 * 追加/替换卡片后派发 glintide:cards-appended,
 * 各卡片脚本(轮播/视频/音乐/点赞)监听该事件为新内容重新绑定。
 */
(function ($) {
	'use strict';

	function initCardStream() {
		var stream = document.querySelector('.glintide-card-stream');
		if (!stream || stream.__glintideStreamInited) {
			return;
		}
		stream.__glintideStreamInited = true;

		var tabs = Array.prototype.slice.call(stream.querySelectorAll('.glintide-card-tab'));
		var form = stream.querySelector('.glintide-card-search');
		var input = form ? form.querySelector('.glintide-card-search-input') : null;
		var clearBtn = form ? form.querySelector('.glintide-card-search-clear') : null;

		var grid = null;
		var sentinel = null;
		var loading = null;
		var emptyNote = null;
		var searchTimer = 0;

		var state = {
			paged: 1,
			maxPages: 1,
			type: '',
			search: '',
			busy: false,
			done: false,
			retries: 0
		};

		function ensureGrid() {
			if (grid && grid.isConnected) {
				return grid;
			}
			grid = stream.querySelector('.glintide-card-grid');
			if (!grid) {
				grid = document.createElement('div');
				grid.className = 'glintide-card-grid';
				stream.appendChild(grid);
			}
			return grid;
		}

		function ensureSentinel() {
			if (sentinel && sentinel.isConnected) {
				return sentinel;
			}
			sentinel = stream.querySelector('[data-glintide-infinite]');
			if (!sentinel) {
				sentinel = document.createElement('div');
				sentinel.className = 'glintide-card-feed-sentinel';
				sentinel.setAttribute('data-glintide-infinite', '');
				sentinel.innerHTML = '<span class="glintide-card-feed-loading" hidden><i class="ri-loader-4-line" aria-hidden="true"></i>加载中…</span>';
				var g = ensureGrid();
				if (g.parentNode) {
					g.parentNode.insertBefore(sentinel, g.nextSibling);
				} else {
					stream.appendChild(sentinel);
				}
			}
			loading = sentinel.querySelector('.glintide-card-feed-loading');
			return sentinel;
		}

		function setLoading(show) {
			if (loading) {
				loading.hidden = !show;
			}
		}

		function removeEmptyNote() {
			if (emptyNote) {
				emptyNote.remove();
				emptyNote = null;
			}
		}

		function showEmptyNote() {
			removeEmptyNote();
			emptyNote = document.createElement('div');
			emptyNote.className = 'glintide-card-empty glintide-card-empty--filter';
			emptyNote.innerHTML =
				'<i class="ri-search-eye-line" aria-hidden="true"></i>' +
				'<strong>没有找到相关内容</strong>' +
				'<span>试试其他关键词，或清除筛选条件。</span>' +
				'<button type="button" class="glintide-card-filter-reset">清除筛选</button>';
			emptyNote.querySelector('.glintide-card-filter-reset').addEventListener('click', function () {
				setTab('');
				if (input) {
					input.value = '';
					updateClear();
				}
				applyFilters('', '');
			});
			ensureGrid();
			if (grid.parentNode) {
				grid.parentNode.insertBefore(emptyNote, grid.nextSibling);
			} else {
				stream.appendChild(emptyNote);
			}
		}

		// 连续失败后停止自动重试,改为手动点击重试,避免无限转圈
		function showRetry() {
			state.done = true;
			ensureSentinel();
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
				state.retries = 0;
				state.done = false;
				request(state.paged + 1, false);
			};
		}

		function request(paged, replace) {
			if (state.busy || !window.jQuery) {
				return;
			}
			state.busy = true;
			if (!replace) {
				ensureSentinel();
				setLoading(true);
			}

			window.jQuery.post(
				window.glintide_card_ajax.url,
				{
					action: 'glintide_card_feed',
					paged: paged,
					type: state.type,
					search: state.search
				},
				function (res) {
					state.busy = false;
					state.retries = 0;

					var data = (res && res.success && res.data) ? res.data : null;
					if (!data) {
						if (replace) {
							setLoading(false);
							showEmptyNote();
						} else {
							state.retries++;
							if (state.retries >= 3) {
								showRetry();
							} else {
								setLoading(false);
							}
						}
						return;
					}

					setLoading(false);
					state.paged = parseInt(data.paged, 10) || paged;
					state.maxPages = parseInt(data.maxPages, 10) || state.paged;

					var html = data.html || '';
					var g = ensureGrid();
					if (replace) {
						removeEmptyNote();
						g.innerHTML = html;
					} else {
						g.insertAdjacentHTML('beforeend', html);
					}

					if (!html) {
						state.done = true;
						if (replace) {
							showEmptyNote();
						}
					} else {
						state.done = (data.hasMore === false) || (state.paged >= state.maxPages);
					}

					var s = ensureSentinel();
					s.setAttribute('data-paged', String(state.paged));
					s.setAttribute('data-max', String(state.maxPages));

					// 让轮播/视频/音乐/点赞脚本为新卡片重新绑定
					document.dispatchEvent(new CustomEvent('glintide:cards-appended'));
				},
				'json'
			).fail(function () {
				state.busy = false;
				setLoading(false);
				state.retries++;
				if (state.retries >= 3) {
					showRetry();
				}
			});
		}

		function loadNext() {
			if (state.busy || state.done) {
				return;
			}
			request(state.paged + 1, false);
		}

		function setTab(type) {
			tabs.forEach(function (tab) {
				var active = (tab.getAttribute('data-card-type') || '') === type;
				tab.classList.toggle('is-active', active);
				tab.setAttribute('aria-pressed', active ? 'true' : 'false');
			});
		}

		function applyFilters(type, search) {
			state.type = type;
			state.search = search;
			state.paged = 1;
			state.maxPages = 1;
			state.done = false;
			state.retries = 0;
			request(1, true);
		}

		function updateClear() {
			if (clearBtn) {
				clearBtn.hidden = !input || !input.value;
			}
		}

		// Tab 切换
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var type = tab.getAttribute('data-card-type') || '';
				if (type === state.type) {
					return;
				}
				setTab(type);
				applyFilters(type, state.search);
			});
		});

		// 搜索:回车立即搜,输入防抖 350ms 自动搜
		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var value = input ? input.value.trim() : '';
				if (value === state.search) {
					return;
				}
				applyFilters(state.type, value);
			});
		}

		if (input) {
			input.addEventListener('input', function () {
				updateClear();
				window.clearTimeout(searchTimer);
				searchTimer = window.setTimeout(function () {
					var value = input.value.trim();
					if (value !== state.search) {
						applyFilters(state.type, value);
					}
				}, 350);
			});
		}

		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				if (input) {
					input.value = '';
				}
				updateClear();
				if (state.search !== '') {
					applyFilters(state.type, '');
				}
				if (input) {
					input.focus();
				}
			});
		}

		// 初始化哨兵状态(首屏由 PHP 渲染,可能没有哨兵)
		var initialSentinel = stream.querySelector('[data-glintide-infinite]');
		if (initialSentinel) {
			ensureSentinel();
			state.paged = parseInt(initialSentinel.getAttribute('data-paged'), 10) || 1;
			state.maxPages = parseInt(initialSentinel.getAttribute('data-max'), 10) || 1;
			state.done = state.paged >= state.maxPages;
		} else {
			state.done = true;
		}

		// 查找现有哨兵,没有就返回 null,不凭空创建
		function findSentinel() {
			if (sentinel && sentinel.isConnected) {
				return sentinel;
			}
			return stream.querySelector('[data-glintide-infinite]');
		}

		// 无限加载:IntersectionObserver 优先,滚动/轮询兜底
		function watchSentinel() {
			if (!('IntersectionObserver' in window)) {
				window.addEventListener('scroll', function () {
					var el = findSentinel();
					if (!el) {
						return;
					}
					var rect = el.getBoundingClientRect();
					if (rect.top < window.innerHeight * 1.5) {
						loadNext();
					}
				}, { passive: true });
				return;
			}

			var initial = findSentinel();
			if (!initial) {
				return;
			}
			var observer = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						ensureSentinel();
						loadNext();
					}
				});
			}, { rootMargin: '600px 0px' });
			observer.observe(initial);
		}
		watchSentinel();

		var pollTimer = window.setInterval(function () {
			if (!stream.isConnected) {
				// pjax 已替换整块内容,停掉旧流的轮询
				window.clearInterval(pollTimer);
				return;
			}
			var el = findSentinel();
			if (!el) {
				return;
			}
			var rect = el.getBoundingClientRect();
			if (rect.top < window.innerHeight + 600 && rect.bottom > -600) {
				loadNext();
			}
		}, 800);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCardStream);
	} else {
		initCardStream();
	}

	// pjax 换页 / 瀑布流追加后,为新流重新挂载(容器整体替换时重新初始化)
	document.addEventListener('glintide:cards-appended', initCardStream);
}(window.jQuery));
