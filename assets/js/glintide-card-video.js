/**
 * 视频卡片交互。
 * 使用自定义控制层播放、调节音量和切换全屏状态。
 */
(function () {
	'use strict';

	function formatTime(value) {
		if (!isFinite(value) || value < 0) {
			return '00:00';
		}

		var total = Math.floor(value);
		var hours = Math.floor(total / 3600);
		var minutes = Math.floor((total % 3600) / 60);
		var seconds = total % 60;
		var minuteText = String(minutes).padStart(2, '0');
		var secondText = String(seconds).padStart(2, '0');

		return hours > 0 ? hours + ':' + minuteText + ':' + secondText : minuteText + ':' + secondText;
	}

	function initVideoCards() {
		var frames = document.querySelectorAll('[data-glintide-video]');

		frames.forEach(function (frame) {
			if (frame.__glintideVideoInited) {
				return;
			}
			frame.__glintideVideoInited = true;

			var video       = frame.querySelector('video');
			var toggle      = frame.querySelector('[data-glintide-video-toggle]');
			var progress    = frame.querySelector('[data-glintide-video-progress]');
			var currentTime = frame.querySelector('[data-glintide-video-current]');
			var duration    = frame.querySelector('[data-glintide-video-duration]');
			var volume      = frame.querySelector('[data-glintide-video-volume]');
			var fullscreen  = frame.querySelector('[data-glintide-video-fullscreen]');
			var controlsTimer;

			if (!video || !toggle) {
				return;
			}

			// 没有封面图的自上传视频:元数据加载后 seek 到开头,
			// 让视频元素自己渲染首帧当封面(仅一次,已开播则不干预)
			function applyFirstFrame() {
				if (frame.__glintideFirstFrameDone) {
					return;
				}
				frame.__glintideFirstFrameDone = true;

				if (video.hasAttribute('poster')) {
					return;
				}
				if (video.readyState < 1 || !video.paused || video.currentTime > 0) {
					return;
				}

				var total = isFinite(video.duration) ? video.duration : 0;
				var target = total > 0.2 ? 0.1 : Math.max(0, total / 2);
				try {
					video.currentTime = target;
				} catch (error) { /* 个别格式 seek 失败时保持黑帧 */ }
			}

			if (!video.hasAttribute('poster')) {
				if (video.readyState >= 1) {
					applyFirstFrame();
				} else {
					video.addEventListener('loadedmetadata', applyFirstFrame, { once: true });
				}
			}

			function updateProgress() {
				var total = isFinite(video.duration) ? video.duration : 0;
				var value = isFinite(video.currentTime) ? video.currentTime : 0;
				var ratio = total > 0 ? Math.min(100, Math.max(0, value / total * 100)) : 0;

				if (progress) {
					progress.max = total;
					progress.value = value;
					progress.style.setProperty('--glintide-video-progress', ratio + '%');
				}
				if (currentTime) {
					currentTime.textContent = formatTime(value);
				}
				if (duration) {
					duration.textContent = formatTime(total);
				}
			}

			function clearControlsTimer() {
				if (controlsTimer) {
					window.clearTimeout(controlsTimer);
					controlsTimer = null;
				}
			}

			function revealControls(autoHide) {
				clearControlsTimer();
				frame.classList.add('is-controls-visible');
				if (autoHide && !video.paused) {
					controlsTimer = window.setTimeout(function () {
						frame.classList.remove('is-controls-visible');
						controlsTimer = null;
					}, 1400);
				}
			}

			function hideControls() {
				clearControlsTimer();
				if (!video.paused) {
					frame.classList.remove('is-controls-visible');
				}
			}

			function setState(playing) {
				frame.classList.toggle('is-playing', playing);
				toggle.setAttribute('aria-label', playing ? '暂停视频' : '播放视频');
				toggle.setAttribute('title', playing ? '暂停视频' : '播放视频');

				var icon = toggle.querySelector('i');
				if (icon) {
					icon.classList.toggle('ri-play-fill', !playing);
					icon.classList.toggle('ri-pause-line', playing);
				}

				if (!playing) {
					clearControlsTimer();
					frame.classList.add('is-controls-visible');
					return;
				}

				var keyboardFocus = document.activeElement && frame.contains(document.activeElement) && document.activeElement.matches && document.activeElement.matches(':focus-visible');
				revealControls(keyboardFocus);
			}

			function pauseOthers() {
				document.querySelectorAll('[data-glintide-video]').forEach(function (other) {
					if (other === frame) {
						return;
					}
					var otherVideo = other.querySelector('video');
					if (otherVideo && !otherVideo.paused) {
						otherVideo.pause();
					}
				});
			}

			function togglePlayback() {
				if (video.paused || video.ended) {
					if (video.ended) {
						video.currentTime = 0;
					}
					pauseOthers();
					var playing = video.play();
					if (playing && playing.catch) {
						playing.catch(function () {});
					}
				} else {
					video.pause();
				}
			}

			toggle.addEventListener('click', function (event) {
				event.preventDefault();
				togglePlayback();
			});

			video.addEventListener('click', function () {
				togglePlayback();
			});

			frame.addEventListener('pointermove', function () {
				if (!video.paused) {
					revealControls(true);
				}
			});

			frame.addEventListener('pointerleave', function () {
				if (!video.paused) {
					hideControls();
				}
			});

			frame.addEventListener('focusin', function () {
				revealControls(false);
			});

			frame.addEventListener('focusout', function (event) {
				if (!event.relatedTarget || !frame.contains(event.relatedTarget)) {
					revealControls(true);
				}
			});

			if (progress) {
				progress.addEventListener('input', function () {
					var value = parseFloat(progress.value);
					if (isFinite(value)) {
						video.currentTime = value;
					}
					updateProgress();
				});
			}

			if (volume) {
				volume.addEventListener('click', function (event) {
					event.preventDefault();
					video.muted = !video.muted;
				});
			}

			if (fullscreen) {
				fullscreen.addEventListener('click', function (event) {
					event.preventDefault();
					if (document.fullscreenElement === frame) {
						if (document.exitFullscreen) {
							document.exitFullscreen();
						}
						return;
					}
					if (frame.requestFullscreen) {
						var request = frame.requestFullscreen();
						if (request && request.catch) {
							request.catch(function () {});
						}
					} else if (video.webkitEnterFullscreen) {
						video.webkitEnterFullscreen();
					}
				});
			}

			video.addEventListener('loadedmetadata', updateProgress);
			video.addEventListener('durationchange', updateProgress);
			video.addEventListener('timeupdate', updateProgress);
			video.addEventListener('progress', updateProgress);
			video.addEventListener('play', function () {
				setState(true);
			});
			video.addEventListener('pause', function () {
				setState(false);
			});
			video.addEventListener('ended', function () {
				setState(false);
				updateProgress();
			});
			video.addEventListener('volumechange', function () {
				if (!volume) {
					return;
				}
				var icon = volume.querySelector('i');
				var muted = video.muted || video.volume === 0;
				volume.setAttribute('aria-label', muted ? '打开声音' : '静音');
				volume.setAttribute('title', muted ? '打开声音' : '静音');
				if (icon) {
					icon.classList.toggle('ri-volume-mute-line', muted);
					icon.classList.toggle('ri-volume-up-line', !muted);
				}
			});

			updateProgress();
			setState(false);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initVideoCards);
	} else {
		initVideoCards();
	}

	// 无限瀑布流追加新卡片后,为新内容的视频重新绑定
	document.addEventListener('glintide:cards-appended', initVideoCards);
}());
