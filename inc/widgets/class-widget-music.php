<?php
/**
 * 小工具: 音乐播放器(网易云歌单)
 *
 * 从项目 reference 备份完整移植原版播放器，保留当前主题的 REST API 地址。
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Music extends Glintide_Widget {

	public static $id          = 'glintide_music_widget';
	public static $title       = 'PPO · 音乐播放器';
	public static $description = '网易云歌单播放器，支持播放/进度/音量/播放模式';
	public static $classname   = 'ppo-widget glintide_music_widget';

	public static function fields() {
		return array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'title' => '标题',
			),
			array(
				'id'    => 'playlist_url',
				'type'  => 'text',
				'title' => '网易云歌单链接',
				'desc'  => '粘贴网易云歌单地址，如 https://music.163.com/#/playlist?id=3778678',
			),
			array(
				'id'      => 'show_playlist',
				'type'    => 'switcher',
				'title'   => '显示播放列表',
				'default' => true,
			),
			array(
				'id'      => 'default_volume',
				'type'    => 'slider',
				'title'   => '默认音量',
				'min'     => 0,
				'max'     => 100,
				'step'    => 1,
				'unit'    => '%',
				'default' => 65,
			),
		);
	}

	public static function render( $instance ) {
		$playlist_url   = $instance['playlist_url'] ?? '';
		$show_playlist  = ! empty( $instance['show_playlist'] );
		$default_volume = isset( $instance['default_volume'] ) ? intval( $instance['default_volume'] ) : 65;
		$default_volume = max( 0, min( 100, $default_volume ) );

		$uid     = 'pix-music-' . uniqid();
		$api_url = rest_url( 'glintide/v1/netease-playlist' );

		$html  = '<div class="pix-music-widget pix-music-immersive" id="' . esc_attr( $uid ) . '" data-api="' . esc_url( $api_url ) . '" data-url="' . esc_attr( $playlist_url ) . '" data-volume="' . $default_volume . '" data-show-playlist="' . ( $show_playlist ? '1' : '0' ) . '">';
		$html .= '<div class="pix-music-cover-bg" data-music-cover-bg></div>';
		$html .= '<div class="pix-music-cover-overlay"></div>';
		$html .= '<div class="pix-music-body">';
		$html .= '<div class="pix-music-top">';
		$html .= '<span class="pix-music-title" data-music-title>加载中...</span>';
		$html .= '<div class="pix-music-ctrls">';
		$html .= '<button type="button" class="pix-music-btn" data-music-mode aria-label="播放模式"><i class="ri-repeat-line"></i></button>';
		$html .= '<button type="button" class="pix-music-btn" data-music-prev aria-label="上一首"><i class="ri-skip-back-line"></i></button>';
		$html .= '<button type="button" class="pix-music-btn is-main" data-music-toggle aria-label="播放/暂停"><i class="ri-play-line"></i></button>';
		$html .= '<button type="button" class="pix-music-btn" data-music-next aria-label="下一首"><i class="ri-skip-forward-line"></i></button>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="pix-music-sub">';
		$html .= '<span class="pix-music-artist" data-music-artist></span>';
		$html .= '<div class="pix-music-vol">';
		$html .= '<button type="button" class="pix-music-mini" data-music-mute aria-label="静音"><i class="ri-volume-up-line"></i></button>';
		$html .= '<div class="pix-music-vol-track" data-music-vol-track><div class="pix-music-vol-fill" data-music-vol-fill></div></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="pix-music-progress">';
		$html .= '<span class="pix-music-time" data-music-current>0:00</span>';
		$html .= '<div class="pix-music-track" data-music-track><div class="pix-music-fill" data-music-fill></div></div>';
		$html .= '<span class="pix-music-time" data-music-duration>0:00</span>';
		$html .= '</div>';
		$html .= '</div>';
		if ( $show_playlist ) {
			$html .= '<div class="pix-music-playlist" data-music-playlist></div>';
		}
		$html .= '<p class="pix-music-error" data-music-error></p>';
		$html .= '<audio data-music-audio preload="metadata"></audio>';
		$html .= '</div>';

		$html .= '<script>
		(function(){
			var root = document.getElementById("' . esc_attr( $uid ) . '");
			if (!root) return;
			var apiUrl = root.getAttribute("data-api");
			var playlistUrl = root.getAttribute("data-url");
			var defaultVolume = parseFloat(root.getAttribute("data-volume") || "65") / 100;
			var showPlaylist = root.getAttribute("data-show-playlist") === "1";

			var audio = root.querySelector("[data-music-audio]");
			var coverBg = root.querySelector("[data-music-cover-bg]");
			var titleEl = root.querySelector("[data-music-title]");
			var artistEl = root.querySelector("[data-music-artist]");
			var toggleBtn = root.querySelector("[data-music-toggle]");
			var prevBtn = root.querySelector("[data-music-prev]");
			var nextBtn = root.querySelector("[data-music-next]");
			var muteBtn = root.querySelector("[data-music-mute]");
			var modeBtn = root.querySelector("[data-music-mode]");
			var volTrack = root.querySelector("[data-music-vol-track]");
			var volFill = root.querySelector("[data-music-vol-fill]");
			var trackEl = root.querySelector("[data-music-track]");
			var fillEl = root.querySelector("[data-music-fill]");
			var currentEl = root.querySelector("[data-music-current]");
			var durationEl = root.querySelector("[data-music-duration]");
			var playlistEl = root.querySelector("[data-music-playlist]");
			var errorEl = root.querySelector("[data-music-error]");

			var playlist = [];
			var index = 0;
			var isPlaying = false;
			var volume = defaultVolume;
			var isMuted = false;
			var modes = ["sequential", "shuffle", "repeat", "repeat-one"];
			var mode = 0;
			var shuffleHistory = [];

			function esc(s) {
				var d = document.createElement("div");
				d.textContent = s || "";
				return d.innerHTML;
			}

			function fmt(t) {
				if (!isFinite(t) || t <= 0) return "0:00";
				var m = Math.floor(t / 60);
				var s = Math.floor(t % 60);
				return m + ":" + (s < 10 ? "0" : "") + s;
			}

			function currentTrack() { return playlist[index] || null; }

			function setCover(url) {
				if (!coverBg) return;
				if (!url) {
					coverBg.style.backgroundImage = "";
					return;
				}
				coverBg.style.backgroundImage = "url(\'" + url + "\')";
				coverBg.classList.remove("is-changing");
				void coverBg.offsetWidth;
				coverBg.classList.add("is-changing");
			}

			function renderPlaylist() {
				if (!playlistEl) return;
				if (!playlist.length) {
					playlistEl.innerHTML = \'<div class="pix-music-empty">暂无歌曲</div>\';
					return;
				}
				var html = "";
				for (var i = 0; i < playlist.length; i++) {
					var t = playlist[i];
					var active = i === index;
					html += \'<button type="button" class="pix-music-track-item\' + (active ? " is-active" : "") + \'" data-index="\' + i + \'">\';
					if (active && isPlaying) {
						html += \'<span class="pix-music-eq"><span></span><span></span><span></span></span>\';
					} else {
						html += \'<span class="pix-music-idx">\' + (i + 1) + \'</span>\';
					}
					html += \'<span class="pix-music-trk-title">\' + esc(t.title) + \'</span>\';
					html += \'</button>\';
				}
				playlistEl.innerHTML = html;
			}

			function updateUI() {
				var t = currentTrack();
				titleEl.textContent = t ? t.title : "暂无歌曲";
				artistEl.textContent = t ? t.artist : "";
				setCover(t ? t.cover : "");
				toggleBtn.innerHTML = isPlaying ? \'<i class="ri-pause-line"></i>\' : \'<i class="ri-play-line"></i>\';
				var modeIcons = ["ri-repeat-line", "ri-shuffle-line", "ri-repeat-line", "ri-repeat-one-line"];
				var modeLabels = ["顺序播放", "随机播放", "循环播放", "单曲循环"];
				modeBtn.innerHTML = \'<i class="\' + modeIcons[mode] + \'"></i>\';
				modeBtn.title = modeLabels[mode];
				renderPlaylist();
			}

			function loadPlaylist() {
				if (!playlistUrl) {
					titleEl.textContent = "请配置歌单链接";
					return;
				}
				titleEl.textContent = "加载歌单中...";
				fetch(apiUrl + "?url=" + encodeURIComponent(playlistUrl))
					.then(function(r) { return r.json(); })
					.then(function(data) {
						if (data.tracks && data.tracks.length) {
							playlist = data.tracks;
							index = 0;
							isPlaying = false;
							if (data.title) titleEl.textContent = data.title;
							updateUI();
						} else {
							titleEl.textContent = "歌单为空或解析失败";
						}
					})
					.catch(function() {
						titleEl.textContent = "歌单加载失败";
					});
			}

			function play() {
				var t = currentTrack();
				if (!t) return;
				if (t.audioUrl) {
					audio.src = t.audioUrl;
					audio.play().then(function() {
						isPlaying = true;
						errorEl.textContent = "";
						updateUI();
					}).catch(function() {
						isPlaying = false;
						errorEl.textContent = "播放失败，请重试";
						updateUI();
					});
				} else {
					errorEl.textContent = "该歌曲无可用音频";
					isPlaying = false;
					updateUI();
				}
			}

			function pause() {
				audio.pause();
				isPlaying = false;
				updateUI();
			}

			function toggle() {
				if (!currentTrack()) return;
				if (isPlaying) { pause(); } else { play(); }
			}

			function getShuffleIndex() {
				var len = playlist.length;
				if (len <= 1) return -1;
				var available = [];
				for (var i = 0; i < len; i++) if (i !== index) available.push(i);
				var recent = shuffleHistory.slice(-2);
				var candidates = available.filter(function(i) { return recent.indexOf(i) === -1; });
				var pool = candidates.length ? candidates : available;
				var idx = pool[Math.floor(Math.random() * pool.length)];
				shuffleHistory.push(idx);
				if (shuffleHistory.length > 10) shuffleHistory.shift();
				return idx;
			}

			function nextTrack() {
				if (!playlist.length) return;
				if (mode === 1) {
					var n = getShuffleIndex();
					if (n >= 0) index = n;
				} else if (mode === 2) {
					index = (index + 1) % playlist.length;
				} else if (mode === 3) {
					// 单曲循环：重播当前
				} else {
					if (index >= playlist.length - 1) {
						index = 0;
						isPlaying = false;
						updateUI();
						return;
					}
					index = index + 1;
				}
				isPlaying = true;
				play();
			}

			function prevTrack() {
				if (!playlist.length) return;
				if (mode === 1) {
					var n = getShuffleIndex();
					if (n >= 0) index = n;
				} else if (mode === 3) {
					// 单曲循环：重播当前
				} else {
					index = (index - 1 + playlist.length) % playlist.length;
				}
				isPlaying = true;
				play();
			}

			function selectTrack(i) {
				index = i;
				isPlaying = true;
				play();
			}

			function seek(ratio) {
				if (audio && isFinite(audio.duration) && audio.duration > 0) {
					audio.currentTime = Math.max(0, Math.min(1, ratio)) * audio.duration;
				}
			}

			function updateProgress() {
				var d = isFinite(audio.duration) ? audio.duration : 0;
				var ratio = d > 0 ? Math.min(audio.currentTime / d, 1) : 0;
				fillEl.style.width = (ratio * 100) + "%";
				currentEl.textContent = fmt(audio.currentTime);
				durationEl.textContent = d > 0 ? fmt(d) : "0:00";
			}

			function syncVolume() {
				audio.volume = volume;
				audio.muted = isMuted;
				var icon = isMuted || volume === 0 ? "ri-volume-mute-line" : (volume < 0.5 ? "ri-volume-down-line" : "ri-volume-up-line");
				muteBtn.innerHTML = \'<i class="\' + icon + \'"></i>\';
				volFill.style.width = (isMuted ? 0 : volume * 100) + "%";
			}

			function setVolumeFromEvent(e) {
				var rect = volTrack.getBoundingClientRect();
				var ratio = (e.clientX - rect.left) / rect.width;
				volume = Math.max(0, Math.min(1, ratio));
				isMuted = volume === 0;
				syncVolume();
			}

			// 事件绑定
			toggleBtn.addEventListener("click", toggle);
			prevBtn.addEventListener("click", prevTrack);
			nextBtn.addEventListener("click", nextTrack);
			modeBtn.addEventListener("click", function() {
				mode = (mode + 1) % modes.length;
				updateUI();
			});
			muteBtn.addEventListener("click", function() {
				isMuted = !isMuted;
				syncVolume();
			});
			volTrack.addEventListener("click", setVolumeFromEvent);
			volTrack.addEventListener("mousedown", function(e) {
				setVolumeFromEvent(e);
				var onMove = function(ev) { setVolumeFromEvent(ev); };
				var onUp = function() {
					document.removeEventListener("mousemove", onMove);
					document.removeEventListener("mouseup", onUp);
				};
				document.addEventListener("mousemove", onMove);
				document.addEventListener("mouseup", onUp);
			});
			trackEl.addEventListener("click", function(e) {
				var rect = trackEl.getBoundingClientRect();
				seek((e.clientX - rect.left) / rect.width);
			});
			if (playlistEl) {
				playlistEl.addEventListener("click", function(e) {
					var btn = e.target.closest("[data-index]");
					if (btn) selectTrack(parseInt(btn.getAttribute("data-index"), 10));
				});
			}
			audio.addEventListener("timeupdate", updateProgress);
			audio.addEventListener("loadedmetadata", updateProgress);
			audio.addEventListener("ended", nextTrack);
			audio.addEventListener("error", function() {
				isPlaying = false;
				errorEl.textContent = "音频加载失败";
				updateUI();
			});

			// 初始化
			syncVolume();
			loadPlaylist();
		})();
		</script>';

		return $html;
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_music_widget' ) ) {
	function glintide_music_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Music::render( $instance );
		echo $args['after_widget'];
	}
}
