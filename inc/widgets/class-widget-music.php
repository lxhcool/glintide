<?php
/**
 * 小工具: 音乐播放器(网易云歌单)
 *
 * 以专辑封面为视觉中心的玻璃播放器，保留歌单解析与原生 audio 播放能力。
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Music extends Glintide_Widget {

	public static $id          = 'glintide_music_widget';
	public static $title       = 'PPO · 音乐播放器';
	public static $description = '网易云歌单播放器, 封面玻璃卡片 UI';
	public static $classname   = 'ppo-widget glintide_music_widget';

	public static function fields() {
		return array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'title' => '标题',
				'desc'  => '留空则使用当前歌曲或歌手名称',
			),
			array(
				'id'    => 'playlist_url',
				'type'  => 'text',
				'title' => '网易云歌单链接',
				'desc'  => '粘贴网易云歌单地址, 如 https://music.163.com/#/playlist?id=3778678',
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
		$default_volume = isset( $instance['default_volume'] ) ? intval( $instance['default_volume'] ) : 65;
		$default_volume = max( 0, min( 100, $default_volume ) );

		$uid            = 'glintide-music-' . uniqid();
		$api_url        = rest_url( 'glintide/v1/netease-playlist' );
		$fallback_cover = GLINTIDE_URL . '/assets/images/banner.jpg';
		$display_title  = isset( $instance['title'] ) ? trim( (string) $instance['title'] ) : '';

		$html  = '<div class="glintide-music-widget glintide-music-immersive" id="' . esc_attr( $uid ) . '" data-api="' . esc_url( $api_url ) . '" data-url="' . esc_attr( $playlist_url ) . '" data-volume="' . esc_attr( $default_volume ) . '" data-fallback-cover="' . esc_url( $fallback_cover ) . '" data-display-title="' . esc_attr( $display_title ) . '">';
		$html .= '<div class="glintide-music-cover-bg" data-music-cover-bg style="background-image: url(\'' . esc_url( $fallback_cover ) . '\');" aria-hidden="true"></div>';
		$html .= '<div class="glintide-music-glass-fade" aria-hidden="true"></div>';
		$html .= '<div class="glintide-music-shell">';

		$html .= '<div class="glintide-music-hero">';
		$html .= '<div class="glintide-music-cover">';
		$html .= '<img src="' . esc_url( $fallback_cover ) . '" data-music-cover alt="当前歌曲封面" width="96" height="96" loading="lazy">';
		$html .= '</div>';
		$html .= '<div class="glintide-music-info">';
		$html .= '<div class="glintide-music-title" data-music-title>Daydream Coast</div>';
		$html .= '<div class="glintide-music-hero-artist" data-music-hero-artist>Luna Marina</div>';
		$html .= '</div>';
		$html .= '<button type="button" class="glintide-music-hero-play" data-music-toggle aria-label="播放/暂停">';
		$html .= '<i class="iconfont icon-icon_play_facial_light" aria-hidden="true"></i>';
		$html .= '</button>';
		$html .= '</div>';

		$html .= '<div class="glintide-music-playlist-panel" data-music-playlist-panel>';
		$html .= '<div class="glintide-music-playlist" data-music-playlist></div>';
		$html .= '</div>';

		$html .= '<div class="glintide-music-playerbar">';
		$html .= '<svg class="glintide-music-player-progress" data-music-progress-track role="slider" tabindex="0" aria-label="调整播放进度" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">';
		$html .= '<path class="glintide-music-player-progress-track" data-music-progress-base></path>';
		$html .= '<path class="glintide-music-player-progress-fill" data-music-progress-fill style="opacity:0"></path>';
		$html .= '</svg>';
		$html .= '<span class="glintide-music-progress-tooltip" data-music-progress-tooltip aria-hidden="true">0:00</span>';
		$html .= '<img class="glintide-music-current-cover" src="' . esc_url( $fallback_cover ) . '" data-music-cover alt="当前播放封面" width="48" height="48" loading="lazy">';
		$html .= '<div class="glintide-music-current">';
		$html .= '<span class="glintide-music-current-title" data-music-current-title>Daydream Coast</span>';
		$html .= '<span class="glintide-music-current-artist" data-music-current-artist>Luna Marina</span>';
		$html .= '</div>';
		$html .= '<div class="glintide-music-player-controls">';
		$html .= '<button type="button" class="glintide-music-control" data-music-toggle aria-label="播放/暂停"><i class="iconfont icon-icon_pause_linear_light1" aria-hidden="true"></i></button>';
		$html .= '<button type="button" class="glintide-music-control" data-music-next aria-label="下一首"><i class="iconfont icon-a-icon_arrowright_linear_light" aria-hidden="true"></i></button>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '</div>';
		$html .= '<p class="glintide-music-error" data-music-error role="status"></p>';
		$html .= '<audio data-music-audio preload="metadata" aria-hidden="true"></audio>';
		$html .= '</div>';

		$html .= '<script>
		(function(){
			var root = document.getElementById("' . esc_attr( $uid ) . '");
			if (!root) return;

			var apiUrl = root.getAttribute("data-api");
			var playlistUrl = root.getAttribute("data-url");
			var fallbackCover = root.getAttribute("data-fallback-cover");
			var displayTitle = root.getAttribute("data-display-title") || "";
			var defaultVolume = parseFloat(root.getAttribute("data-volume") || "65") / 100;
			var audio = root.querySelector("[data-music-audio]");
			var coverBg = root.querySelector("[data-music-cover-bg]");
			var coverImgs = root.querySelectorAll("[data-music-cover]");
			var titleEl = root.querySelector("[data-music-title]");
			var heroArtistEl = root.querySelector("[data-music-hero-artist]");
			var toggleButtons = root.querySelectorAll("[data-music-toggle]");
			var nextBtn = root.querySelector("[data-music-next]");
			var playerbarEl = root.querySelector(".glintide-music-playerbar");
			var progressTrackEl = root.querySelector("[data-music-progress-track]");
			var progressBaseEl = root.querySelector("[data-music-progress-base]");
			var progressFillEl = root.querySelector("[data-music-progress-fill]");
			var progressTooltipEl = root.querySelector("[data-music-progress-tooltip]");
			var currentTitleEl = root.querySelector("[data-music-current-title]");
			var currentArtistEl = root.querySelector("[data-music-current-artist]");
			var playlistEl = root.querySelector("[data-music-playlist]");
			var errorEl = root.querySelector("[data-music-error]");

			var playlist = [];
			var index = 0;
			var isPlaying = false;
			var progressGeometryLength = 0;
			var coverRequest = 0;

			function esc(value) {
				var node = document.createElement("div");
				node.textContent = value || "";
				return node.innerHTML;
			}

			function fmt(seconds) {
				if (!isFinite(seconds) || seconds <= 0) return "0:00";
				var minutes = Math.floor(seconds / 60);
				var secs = Math.floor(seconds % 60);
				return minutes + ":" + (secs < 10 ? "0" : "") + secs;
			}

			function currentTrack() {
				return playlist[index] || null;
			}

			function setCover(url) {
				var nextCover = url || fallbackCover;
				var request = ++coverRequest;

				function applyCover() {
					if (request !== coverRequest) return;
					for (var i = 0; i < coverImgs.length; i++) {
						if (coverImgs[i].getAttribute("src") !== nextCover) {
							coverImgs[i].setAttribute("src", nextCover);
						}
					}
					if (coverBg) {
						coverBg.style.backgroundImage = "url(" + JSON.stringify(nextCover) + ")";
					}
				}

			var preload = new Image();
			preload.onload = applyCover;
			preload.onerror = function(){
				if (nextCover === fallbackCover) return;
				var fallback = new Image();
				fallback.onload = function(){
					if (request !== coverRequest) return;
					for (var i = 0; i < coverImgs.length; i++) coverImgs[i].setAttribute("src", fallbackCover);
					if (coverBg) coverBg.style.backgroundImage = "url(" + JSON.stringify(fallbackCover) + ")";
				};
				fallback.src = fallbackCover;
			};
			preload.src = nextCover;
			}

			function roundedRectPath(width, height, radius, inset) {
				var x = inset;
				var y = inset;
				var right = width - inset;
				var bottom = height - inset;
				var maxRadius = Math.min((width - inset * 2) / 2, (height - inset * 2) / 2);
				var safeRadius = Math.max(0, Math.min(radius - inset, maxRadius));
				var rx = safeRadius;
				var ry = safeRadius;
				var curve = 0.55228475;

				function n(value) {
					return Number(value.toFixed(3));
				}

				return [
					"M", n(x + rx), n(y),
					"H", n(right - rx),
					"C", n(right - rx + rx * curve), n(y), n(right), n(y + ry - ry * curve), n(right), n(y + ry),
					"V", n(bottom - ry),
					"C", n(right), n(bottom - ry + ry * curve), n(right - rx + rx * curve), n(bottom), n(right - rx), n(bottom),
					"H", n(x + rx),
					"C", n(x + rx - rx * curve), n(bottom), n(x), n(bottom - ry + ry * curve), n(x), n(bottom - ry),
					"V", n(y + ry),
					"C", n(x), n(y + ry - ry * curve), n(x + rx - rx * curve), n(y), n(x + rx), n(y),
					"Z"
				].join(" ");
			}

			function syncProgressPath() {
				if (!progressTrackEl || !playerbarEl) return;
				var bounds = progressTrackEl.getBoundingClientRect();
				if (!bounds.width || !bounds.height) return;
				var computed = window.getComputedStyle(playerbarEl);
				var radius = parseFloat(computed.borderTopLeftRadius) || 0;
				progressTrackEl.setAttribute("viewBox", "0 0 " + bounds.width + " " + bounds.height);
				var path = roundedRectPath(bounds.width, bounds.height, radius, 0.8);
				if (progressBaseEl) progressBaseEl.setAttribute("d", path);
				if (progressFillEl) {
					progressFillEl.setAttribute("d", path);
					if (typeof progressFillEl.getTotalLength === "function") {
						progressGeometryLength = progressFillEl.getTotalLength();
						progressFillEl.style.strokeDasharray = "0 " + (progressGeometryLength + 1);
						progressFillEl.style.strokeDashoffset = "0";
					}
				}
			}

			function updateProgress() {
				if (!progressGeometryLength) syncProgressPath();
				var track = currentTrack();
				var duration = audio.duration || (track && track.duration) || 0;
				var current = isFinite(audio.currentTime) ? audio.currentTime : 0;
				var percent = duration > 0 ? Math.min(100, Math.max(0, current / duration * 100)) : 0;
				var hasProgress = duration > 0 && current > 0.01;
				root.classList.toggle("is-progressing", hasProgress);
				if (progressFillEl) progressFillEl.style.opacity = hasProgress ? "1" : "0";
				if (progressFillEl && progressGeometryLength) {
					var elapsedLength = progressGeometryLength * percent / 100;
					progressFillEl.style.strokeDasharray = elapsedLength + " " + (progressGeometryLength + 1);
					progressFillEl.style.strokeDashoffset = "0";
				}
				if (progressTrackEl) {
					progressTrackEl.setAttribute("aria-valuemin", "0");
					progressTrackEl.setAttribute("aria-valuemax", "100");
					progressTrackEl.setAttribute("aria-valuenow", String(Math.round(percent)));
				}
			}

			function progressDuration() {
				var track = currentTrack();
				return audio.duration || (track && track.duration) || 0;
			}

			function progressHit(event) {
				if (!progressTrackEl || !progressFillEl || !progressGeometryLength || typeof progressFillEl.getPointAtLength !== "function") return null;
				var matrix = progressTrackEl.getScreenCTM();
				if (!matrix) return null;

				var pointer = progressTrackEl.createSVGPoint();
				pointer.x = event.clientX;
				pointer.y = event.clientY;
				var local = pointer.matrixTransform(matrix.inverse());
				var total = progressGeometryLength;
				var samples = 72;
				var bestLength = 0;
				var bestDistance = Infinity;

				function consider(length) {
					var point = progressFillEl.getPointAtLength(length);
					var dx = point.x - local.x;
					var dy = point.y - local.y;
					var distance = dx * dx + dy * dy;
					if (distance < bestDistance) {
						bestDistance = distance;
						bestLength = length;
					}
				}

				for (var i = 0; i <= samples; i++) consider(total * i / samples);
				var searchRange = total / samples;
				for (var pass = 0; pass < 5; pass++) {
					var start = Math.max(0, bestLength - searchRange);
					var end = Math.min(total, bestLength + searchRange);
					for (var step = 0; step <= 8; step++) consider(start + (end - start) * step / 8);
					searchRange = Math.max((end - start) / 8, 0.01);
				}

				return {
					ratio: Math.min(1, Math.max(0, bestLength / total)),
					distance: Math.sqrt(bestDistance)
				};
			}

			function showProgressTooltip(event) {
				var duration = progressDuration();
				if (!duration || !progressTooltipEl) return;
				var hit = progressHit(event);
				if (!hit || hit.distance > 12) {
					hideProgressTooltip();
					return;
				}
				var rect = playerbarEl.getBoundingClientRect();
				var tooltipLeft = Math.min(94, Math.max(6, (event.clientX - rect.left) / rect.width * 100));
				progressTooltipEl.textContent = fmt(duration * hit.ratio);
				progressTooltipEl.style.left = tooltipLeft + "%";
				progressTooltipEl.classList.add("is-visible");
			}

			function hideProgressTooltip() {
				if (progressTooltipEl) progressTooltipEl.classList.remove("is-visible");
			}

			function setToggleIcons() {
				for (var i = 0; i < toggleButtons.length; i++) {
					toggleButtons[i].innerHTML = isPlaying
						? \'<i class="iconfont icon-icon_pause_linear_light1" aria-hidden="true"></i>\'
						: \'<i class="iconfont icon-icon_play_facial_light" aria-hidden="true"></i>\';
				}
				root.classList.toggle("is-playing", isPlaying);
			}

			function renderPlaylist() {
				if (!playlistEl) return;
				if (!playlist.length) {
					playlistEl.innerHTML = \'<div class="glintide-music-empty">暂无歌曲</div>\';
					return;
				}

				var html = "";
				for (var i = 0; i < playlist.length; i++) {
					var track = playlist[i] || {};
					var active = i === index;
					html += \'<button type="button" class="glintide-music-track-item\' + (active ? " is-active" : "") + \'" data-index="\' + i + \'">\';
					html += active && isPlaying
						? \'<span class="glintide-music-eq" aria-hidden="true"><span></span><span></span><span></span><span></span></span>\'
						: \'<span class="glintide-music-idx">\' + (i + 1) + \'</span>\';
					html += \'<span class="glintide-music-trk-title">\' + esc(track.title || "Untitled") + \'</span>\';
					html += \'<span class="glintide-music-trk-artist">\' + esc(track.artist || "") + \'</span>\';
					html += \'</button>\';
				}
				playlistEl.innerHTML = html;
			}

			function updateUI() {
				var track = currentTrack();
				var trackTitle = track ? (track.title || "Untitled") : "Daydream Coast";
				var artist = track ? (track.artist || "Unknown artist") : "Luna Marina";

				titleEl.textContent = trackTitle;
				heroArtistEl.textContent = artist;
				currentTitleEl.textContent = trackTitle;
				currentArtistEl.textContent = artist;
				setCover(track && track.cover ? track.cover : fallbackCover);
				setToggleIcons();
				updateProgress();
				renderPlaylist();
			}

			function setError(message) {
				if (!errorEl) return;
				errorEl.textContent = message || "";
				root.classList.toggle("has-error", Boolean(message));
			}

			function play() {
				var track = currentTrack();
				if (!track) {
					setError("请先配置歌单链接");
					return;
				}
				if (!track.audioUrl) {
					setError("该歌曲暂无可用音频");
					isPlaying = false;
					updateUI();
					return;
				}

				if (audio.src !== track.audioUrl) audio.src = track.audioUrl;
				audio.play().then(function(){
					isPlaying = true;
					setError("");
					updateUI();
				}).catch(function(){
					isPlaying = false;
					setError("播放失败，请重试");
					updateUI();
				});
			}

			function pause() {
				audio.pause();
				isPlaying = false;
				updateUI();
			}

			function toggle() {
				if (isPlaying) {
					pause();
				} else {
					play();
				}
			}

			function nextTrack() {
				if (!playlist.length) {
					setError("请先配置歌单链接");
					return;
				}
				index = (index + 1) % playlist.length;
				isPlaying = false;
				updateUI();
				play();
			}

			function selectTrack(nextIndex) {
				if (!playlist[nextIndex]) return;
				index = nextIndex;
				isPlaying = false;
				setError("");
				updateUI();
				play();
			}

			for (var i = 0; i < toggleButtons.length; i++) {
				toggleButtons[i].addEventListener("click", toggle);
			}
			if (nextBtn) nextBtn.addEventListener("click", nextTrack);
			if (playerbarEl) {
				playerbarEl.addEventListener("pointermove", showProgressTooltip);
				playerbarEl.addEventListener("pointerleave", hideProgressTooltip);
				playerbarEl.addEventListener("click", function(event){
					if (event.target.closest && event.target.closest("button")) return;
					if (event.detail === 0) return;
					var duration = progressDuration();
					if (!duration) return;
					var hit = progressHit(event);
					if (!hit || hit.distance > 14) return;
					audio.currentTime = duration * hit.ratio;
					updateProgress();
					showProgressTooltip(event);
				});
			}
			if (progressTrackEl) {
				progressTrackEl.addEventListener("keydown", function(event){
					if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") return;
					var duration = progressDuration();
					if (!duration) return;
					event.preventDefault();
					audio.currentTime = Math.min(duration, Math.max(0, audio.currentTime + (event.key === "ArrowRight" ? 5 : -5)));
					updateProgress();
				});
			}
			if (window.ResizeObserver && playerbarEl) {
				new ResizeObserver(function(){
					syncProgressPath();
					updateProgress();
				}).observe(playerbarEl);
			}
			if (playlistEl) {
				playlistEl.addEventListener("click", function(event){
					var item = event.target.closest("[data-index]");
					if (item) selectTrack(parseInt(item.getAttribute("data-index"), 10));
				});
			}
			audio.addEventListener("loadedmetadata", function(){
				updateProgress();
			});
			audio.addEventListener("timeupdate", updateProgress);
			audio.addEventListener("ended", nextTrack);
			audio.addEventListener("error", function(){
				isPlaying = false;
				setError("音频加载失败");
				updateUI();
			});

			if (audio) audio.volume = defaultVolume;
			updateUI();

			function loadPlaylist() {
				if (!playlistUrl) return;
				fetch(apiUrl + "?url=" + encodeURIComponent(playlistUrl))
					.then(function(response){ return response.json(); })
					.then(function(data){
						if (data.tracks && data.tracks.length) {
							playlist = data.tracks;
							index = 0;
							isPlaying = false;
							setError("");
							updateUI();
						} else {
							setError("歌单为空或解析失败");
						}
					})
					.catch(function(){ setError("歌单加载失败"); });
			}

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
