/**
 * 视频卡片交互。
 * 中央播放按钮起播,播放中显示原生控制条;同一时间只保留一个视频在播放。
 */
(function () {
	'use strict';

	function initVideoCards() {
		var frames = document.querySelectorAll('[data-glintide-video]');

		frames.forEach(function (frame) {
			if (frame.__glintideVideoInited) {
				return;
			}
			frame.__glintideVideoInited = true;

			var video  = frame.querySelector('video');
			var toggle = frame.querySelector('[data-glintide-video-toggle]');
			if (!video || !toggle) {
				return;
			}

			function setState(playing) {
				frame.classList.toggle('is-playing', playing);

				// 播放中交给原生控制条,未播放时保持覆盖层干净
				if (playing) {
					video.setAttribute('controls', 'controls');
				} else {
					video.removeAttribute('controls');
				}

				toggle.setAttribute('aria-label', playing ? '暂停' : '播放视频');
				var icon = toggle.querySelector('i');
				if (icon) {
					icon.classList.toggle('ri-play-fill', !playing);
					icon.classList.toggle('ri-pause-line', playing);
				}
			}

			function pauseOthers() {
				document.querySelectorAll('[data-glintide-video]').forEach(function (other) {
					if (other === frame) {
						return;
					}
					var v = other.querySelector('video');
					if (v && !v.paused) {
						v.pause();
					}
				});
			}

			toggle.addEventListener('click', function () {
				if (video.paused) {
					pauseOthers();
					var playing = video.play();
					if (playing && playing.catch) {
						playing.catch(function () {});
					}
				} else {
					video.pause();
				}
			});

			// 未起播时视频本身没有控制条,点画面任意位置也能播放
			video.addEventListener('click', function () {
				if (!video.hasAttribute('controls')) {
					pauseOthers();
					var playing = video.play();
					if (playing && playing.catch) {
						playing.catch(function () {});
					}
				}
			});

			video.addEventListener('play', function () {
				setState(true);
			});
			video.addEventListener('pause', function () {
				setState(false);
			});
			video.addEventListener('ended', function () {
				setState(false);
			});
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
