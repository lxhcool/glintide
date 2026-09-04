/**
 * 内容卡片 - 音乐播放器
 *
 * 自上传音频:直接使用 audio 元素播放。
 * 网易云链接:先通过 /glintide/v1/netease-song 解析真实音频直链再播放;
 *            若受版权限制拿不到直链,提供网易云官方歌曲页播放入口。
 */
(function () {
	'use strict';

	var config = window.glintideMusic || {};
	var restUrl = config.restUrl || '/wp-json/glintide/v1/netease-song';

	function q(root, selector) {
		return root ? root.querySelector(selector) : null;
	}

	/**
	 * 播放状态与图标同步
	 */
	function setPlaying(root, playing) {
		var toggle = q(root, '[data-glintide-music-toggle]');
		if (!toggle) {
			return;
		}

		var icon = toggle.querySelector('i');
		if (icon) {
			icon.classList.toggle('icon-icon_play-01', !playing);
			icon.classList.toggle('icon-icon_pause_linear_light1', playing);
		}

		toggle.setAttribute('data-state', playing ? 'playing' : 'paused');
		toggle.setAttribute('aria-label', playing ? '暂停' : '播放');
		root.classList.toggle('is-playing', playing);
	}

	/**
	 * 状态提示(解析中 / 失败 / 降级)
	 */
	function setNote(root, text, type) {
		var note = q(root, '[data-glintide-music-note]');
		if (!note) {
			return;
		}

		note.textContent = text || '';
		note.hidden = !text;
		note.classList.toggle('is-error', type === 'error');
	}

	/**
	 * 暂停其他正在播放的音乐卡片
	 */
	function pauseOthers(currentAudio) {
		var audios = document.querySelectorAll('audio.glintide-card-music-audio');
		audios.forEach(function (audio) {
			if (audio !== currentAudio && !audio.paused) {
				audio.pause();
			}
		});

		var roots = document.querySelectorAll('[data-glintide-music]');
		roots.forEach(function (item) {
			var audio = q(item, '[data-glintide-music-audio]');
			if (audio !== currentAudio) {
				setPlaying(item, false);
			}
		});
	}

	/**
	 * 解析网易云音频直链(结果缓存在元素上,避免重复请求)
	 */
	function resolveNetease(root) {
		return new Promise(function (resolve, reject) {
			if (root.__glintideMusicUrl) {
				resolve(root.__glintideMusicUrl);
				return;
			}

			var source = root.getAttribute('data-music-url') || '';
			if (!source) {
				reject(new Error('empty-url'));
				return;
			}

			if (root.__glintideMusicPending) {
				reject(new Error('pending'));
				return;
			}

			root.__glintideMusicPending = true;
			setNote(root, '');

			fetch(restUrl + '?url=' + encodeURIComponent(source), {
				credentials: 'same-origin'
			})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('http-' + response.status);
					}
					return response.json();
				})
					.then(function (data) {
						root.__glintideMusicPending = false;

						if (data && data.cover) {
							applyCover(root, data.cover);
						}

						if (data && data.artist) {
							applyArtist(root, data.artist);
						}

					if (data && data.audioUrl) {
						root.__glintideMusicUrl = data.audioUrl;
						enableDownload(root, data.audioUrl);
						setNote(root, '');
						resolve(data.audioUrl);
						return;
					}

					reject(new Error('no-audio-url'));
				})
				.catch(function () {
					root.__glintideMusicPending = false;
					reject(new Error('resolve-failed'));
				});
		});
	}

	/**
	 * 用网易云返回的封面 URL 替换占位元素。
	 */
	function applyCover(root, url) {
		var el = q(root, '[data-glintide-music-cover]');
		if (!el || !url) {
			return;
		}

		var titleEl = q(root, '.glintide-music-card-title');
		var titleText = titleEl ? titleEl.textContent || '' : '';

		if (el.tagName === 'IMG') {
			if (el.getAttribute('src') !== url) {
				el.src = url;
				el.classList.remove('glintide-music-card-cover-img--placeholder');
			}
		} else {
			// 把占位 span 替换为 img
			var img = document.createElement('img');
			img.className = 'glintide-music-card-cover-img';
			img.setAttribute('data-glintide-music-cover', '');
			img.setAttribute('src', url);
			img.setAttribute('loading', 'lazy');
			img.setAttribute('decoding', 'async');
			img.setAttribute('alt', titleText);

			el.parentNode.replaceChild(img, el);
		}
	}

	/**
	 * 用网易云返回的音乐人名填充标题下方音乐人行(渲染时 PHP 拿不到该数据)
	 */
	function applyArtist(root, artist) {
		var meta = q(root, '.glintide-music-card-meta');
		if (!meta || !artist) {
			return;
		}

		var el = q(meta, '.glintide-music-card-artist');
		if (el) {
			if (el.textContent !== artist) {
				el.textContent = artist;
			}
			return;
		}

		el = document.createElement('p');
		el.className = 'glintide-music-card-artist';
		el.textContent = artist;
		meta.appendChild(el);
	}

	/**
	 * 直链就绪后启用下载按钮
	 */
	function enableDownload(root, url) {
		var link = q(root, '[data-glintide-music-download]');
		if (!link) {
			return;
		}

		link.setAttribute('href', url);
		link.removeAttribute('disabled');
		link.removeAttribute('aria-disabled');
	}

	/**
	 * 降级:提供网易云官方播放入口,避免跨域 iframe 脚本报错
	 */
	function showFallback(root) {
		var wrap = q(root, '[data-glintide-music-fallback]');
		if (!wrap) {
			return;
		}

		root.classList.add('is-fallback');

		if (!wrap.__glintideInited) {
			var source = root.getAttribute('data-music-url') || '';
			if (!source) {
				return;
			}

			wrap.__glintideInited = true;

			var link = document.createElement('a');
			link.className = 'glintide-card-music-official-link';
			link.href = source;
			link.target = '_blank';
			link.rel = 'noopener noreferrer';
			link.textContent = '在网易云音乐中播放';
			wrap.appendChild(link);
		}
		wrap.__glintideInited = true;

		wrap.hidden = false;
	}

	/**
	 * 直接音频播放前确保使用卡片上的最新地址。
	 */
	function prepareDirectAudio(root, audio) {
		if (!audio || root.getAttribute('data-music-source') === 'netease') {
			return;
		}

		var source = root.getAttribute('data-music-url') || '';
		if (!source) {
			return;
		}

		if (audio.getAttribute('src') !== source || audio.error) {
			audio.setAttribute('src', source);
			audio.load();
		}
	}

	/**
	 * 执行播放
	 */
	function doPlay(root, audio) {
		prepareDirectAudio(root, audio);
		pauseOthers(audio);

		var promise = audio.play();
		if (promise && typeof promise.then === 'function') {
			promise
				.then(function () {
					root.__glintidePlayRetried = false;
					setPlaying(root, true);
					setNote(root, '');
				})
				.catch(function (error) {
					setPlaying(root, false);

					if (error && error.name === 'NotAllowedError') {
						setNote(root, '浏览器拦截了播放,请再点一次', 'error');
						return;
					}

					// 直链失效或加载失败:清除缓存,网易云重试一次后仍失败则降级官方播放器
					root.__glintideMusicUrl = '';
					if (root.getAttribute('data-music-source') === 'netease' && !root.__glintidePlayRetried) {
						root.__glintidePlayRetried = true;
						setNote(root, '');
						resolveNetease(root)
							.then(function (mp3) {
								audio.src = mp3;
								doPlay(root, audio);
							})
							.catch(function () {
								setNote(root, '');
								showFallback(root);
							});
						return;
					}

					root.__glintidePlayRetried = false;
					setNote(root, '音频加载失败,请重试', 'error');
				});
		} else {
			setPlaying(root, true);
		}
	}

	/**
	 * 点击播放/暂停
	 */
	function toggle(root) {
		var toggleBtn = q(root, '[data-glintide-music-toggle]');
		var audio = q(root, '[data-glintide-music-audio]');

		if (!toggleBtn || toggleBtn.disabled || !audio) {
			return;
		}

		if (!audio.paused) {
			audio.pause();
			setPlaying(root, false);
			return;
		}

		if (root.getAttribute('data-music-source') === 'netease') {
			resolveNetease(root)
				.then(function (mp3) {
					if (audio.src !== mp3) {
						audio.src = mp3;
					}
					doPlay(root, audio);
				})
				.catch(function (error) {
					setPlaying(root, false);
					if (error.message === 'pending') {
						return;
					}
					if (error.message === 'empty-url') {
						setNote(root, '音频地址未设置', 'error');
						return;
					}
					setNote(root, '');
					showFallback(root);
				});
			return;
		}

		doPlay(root, audio);
	}

	/**
	 * 进度控制:同步播放位置,支持拖动跳转
	 */
	function fmtTime(seconds) {
		var total = Math.max(0, Math.floor(parseFloat(seconds) || 0));
		var min = Math.floor(total / 60);
		var sec = total % 60;
		return (min < 10 ? '0' + min : String(min)) + ':' + (sec < 10 ? '0' + sec : String(sec));
	}

	function initProgress(root, audio) {
		var seek = q(root, '[data-glintide-music-seek]');
		if (!seek || !audio || seek.__glintideSeekInited) {
			return;
		}
		seek.__glintideSeekInited = true;

		var currentEl = q(root, '[data-glintide-music-time-current]');
		var durationEl = q(root, '[data-glintide-music-time-duration]');
		var seeking = false;

		function fill() {
			var max = parseFloat(seek.max) || 0;
			var pct = max > 0 ? ((parseFloat(seek.value) || 0) / max) * 100 : 0;
			seek.style.setProperty('--glintide-music-seek-fill', pct + '%');
		}

		function setCurrentLabel(seconds) {
			if (currentEl) {
				currentEl.textContent = fmtTime(seconds);
			}
		}

		function syncFromAudio() {
			if (seeking) {
				return;
			}

			var duration = audio.duration;
			if (!isFinite(duration) || duration <= 0) {
				return;
			}

			seek.max = Math.round(duration);
			seek.value = Math.round(audio.currentTime);
			setCurrentLabel(audio.currentTime);
			if (durationEl) {
				durationEl.textContent = fmtTime(duration);
			}
			fill();
		}

		seek.addEventListener('input', function () {
			seeking = true;
			setCurrentLabel(seek.value);
			fill();
		});

		seek.addEventListener('change', function () {
			var duration = audio.duration;
			if (isFinite(duration) && duration > 0) {
				audio.currentTime = parseFloat(seek.value) || 0;
			}
			seeking = false;
			fill();
		});

		audio.addEventListener('loadedmetadata', syncFromAudio);
		audio.addEventListener('timeupdate', syncFromAudio);
		audio.addEventListener('seeked', syncFromAudio);

		// 直链过期重新解析后 src 会重置,同步清空进度
		audio.addEventListener('emptied', function () {
			if (!audio.currentSrc) {
				seek.value = 0;
				setCurrentLabel(0);
				if (durationEl) {
					durationEl.textContent = '00:00';
				}
				fill();
			}
		});

		fill();
	}

	/**
	 * 初始化单个音乐卡片
	 */
	function initCard(root) {
		var toggleBtn = q(root, '[data-glintide-music-toggle]');
		var audio = q(root, '[data-glintide-music-audio]');

		if (!toggleBtn || toggleBtn.__glintideInited) {
			return;
		}
		toggleBtn.__glintideInited = true;

		toggleBtn.addEventListener('click', function (event) {
			event.preventDefault();
			toggle(root);
		});

		if (audio) {
			audio.addEventListener('ended', function () {
				setPlaying(root, false);
			});
			audio.addEventListener('pause', function () {
				setPlaying(root, false);
			});
			audio.addEventListener('play', function () {
				setPlaying(root, true);
			});
			audio.addEventListener('error', function () {
				// 网易云直链可能过期,清除缓存以便下次重新解析
				root.__glintideMusicUrl = '';
				setPlaying(root, false);
				if (root.getAttribute('data-music-source') === 'netease') {
					showFallback(root);
					return;
				}
				setNote(root, '音频加载失败,请重试', 'error');
			});

			initProgress(root, audio);
		}

		// 网易云链接且封面为占位:进入视口时预解析,提前拿到真实封面
		if (
			root.getAttribute('data-music-source') === 'netease'
			&& root.__glintideMusicUrl === undefined
			&& q(root, '[data-glintide-music-cover]')
		) {
			prefetch(root);
		}
	}

	/**
	 * 预解析:仅用于提前取回封面,失败静默
	 */
	function prefetch(root) {
		if (!('IntersectionObserver' in window)) {
			resolveNetease(root).catch(function () {});
			return;
		}

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					observer.disconnect();
					resolveNetease(root).catch(function () {});
				}
			});
		});

		observer.observe(root);
	}

	function initAll() {
		var roots = document.querySelectorAll('[data-glintide-music]');
		roots.forEach(initCard);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	// 无限瀑布流追加新卡片后,为新内容的音乐卡片重新绑定
	document.addEventListener('glintide:cards-appended', initAll);
}());
