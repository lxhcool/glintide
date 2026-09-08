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
		$wave_bars = '';
		foreach ( array( 28, 42, 64, 48, 76, 56, 88, 68, 44, 80, 52, 72, 92, 60, 36, 70, 84, 54, 76, 46, 66, 86, 58, 74, 40, 62, 82, 50, 70, 90, 56, 74, 44, 68, 84, 52, 64, 78, 48, 70 ) as $height ) {
			$wave_bars .= '<i data-music-wave-bar style="--pix-wave-height:' . intval( $height ) . '%"></i>';
		}

		$html  = '<div class="pix-music-widget pix-music-immersive pix-music-is-loading' . ( $show_playlist ? ' pix-music-immersive--with-playlist' : '' ) . '" id="' . esc_attr( $uid ) . '" data-api="' . esc_url( $api_url ) . '" data-url="' . esc_attr( $playlist_url ) . '" data-volume="' . $default_volume . '" data-show-playlist="' . ( $show_playlist ? '1' : '0' ) . '" aria-busy="true">';
		$html .= '<div class="pix-music-cover-bg" data-music-cover-bg aria-hidden="true"></div>';
		$html .= '<div class="pix-music-cover-overlay" aria-hidden="true"></div>';
		$html .= '<div class="pix-music-glass pix-music-hero-shell">';
		$html .= '<div class="pix-music-hero">';
		$html .= '<div class="pix-music-cover" data-music-cover-frame>';
		$html .= '<img class="pix-music-cover-img" data-music-cover-art src="" alt="" loading="lazy" decoding="async" hidden>';
		$html .= '<span class="pix-music-cover-placeholder" data-music-cover-placeholder aria-hidden="true"><i class="iconfont icon-icon_play_linear_light"></i></span>';
		$html .= '</div>';
		$html .= '<div class="pix-music-info">';
		$html .= '<span class="pix-music-title" data-music-title>加载中...</span>';
		$html .= '<span class="pix-music-artist" data-music-artist></span>';
		$html .= '</div>';
		$html .= '<div class="pix-music-hero-actions">';
		$html .= '<button type="button" class="pix-music-hero-btn" data-music-hero-toggle aria-label="播放" title="播放"><i class="iconfont icon-icon_play_linear_light" aria-hidden="true"></i></button>';
		$html .= '<button type="button" class="pix-music-hero-next" data-music-next aria-label="下一首" title="下一首"><i class="iconfont icon-a-icon_arrowright_linear_light" aria-hidden="true"></i></button>';
		$html .= '<div class="pix-music-wave-volume-control">';
		$html .= '<button type="button" class="pix-music-wave-volume" data-music-mute aria-label="静音" title="静音"><i class="iconfont icon-sound" aria-hidden="true"></i></button>';
		$html .= '<div class="pix-music-wave-volume-popover" role="group" aria-label="音量调节">';
		$html .= '<div class="pix-music-wave-volume-track pix-music-vol-track" data-music-vol-track role="slider" tabindex="0" aria-label="音量" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . $default_volume . '"><span class="pix-music-wave-volume-fill pix-music-vol-fill" data-music-vol-fill></span></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		if ( $show_playlist ) {
			$html .= '<div class="pix-music-playlist" data-music-playlist><div class="pix-music-skeleton-list" aria-hidden="true">';
			for ( $skeleton_index = 0; $skeleton_index < 3; $skeleton_index++ ) {
				$html .= '<span class="pix-music-skeleton-row"><i class="pix-music-skeleton-index"></i><i class="pix-music-skeleton-line"></i><i class="pix-music-skeleton-time"></i></span>';
			}
			$html .= '</div></div>';
		}
		$html .= '<div class="pix-music-glass pix-music-player-shell">';
		$html .= '<div class="pix-music-wave-player">';
		$html .= '<button type="button" class="pix-music-wave-play" data-music-toggle aria-label="播放" title="播放"><i class="iconfont icon-icon_play_linear_light" aria-hidden="true"></i></button>';
		$html .= '<div class="pix-music-wave-track pix-music-track" data-music-track role="slider" tabindex="0" aria-label="播放进度" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">';
		$html .= '<span class="pix-music-waveform" aria-hidden="true">' . $wave_bars . '</span>';
		$html .= '<span class="pix-music-wave-fill pix-music-fill" data-music-fill aria-hidden="true"></span>';
		$html .= '<span class="pix-music-wave-playhead" data-music-playhead aria-hidden="true"></span>';
		$html .= '</div>';
		$html .= '<span class="pix-music-wave-duration" data-music-duration>0:00</span>';
		$html .= '<span class="pix-music-time pix-music-time--current" data-music-current aria-hidden="true">0:00</span>';
		$html .= '</div>';
		$html .= '<div class="pix-music-legacy-controls" aria-hidden="true">';
		$html .= '<button type="button" data-music-mode aria-label="播放模式"><i class="iconfont icon-icon_list_linear_light" aria-hidden="true"></i></button>';
		$html .= '<button type="button" data-music-prev aria-label="上一首"><i class="iconfont icon-a-icon_arrowleft_linear_light" aria-hidden="true"></i></button>';
		$html .= '</div>';
		$html .= '</div>';
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
			var coverArt = root.querySelector("[data-music-cover-art]");
			var coverPlaceholder = root.querySelector("[data-music-cover-placeholder]");
			var miniCoverArt = root.querySelector("[data-music-mini-cover-art]");
			var miniCoverPlaceholder = root.querySelector("[data-music-mini-cover-placeholder]");
			var titleEl = root.querySelector("[data-music-title]");
			var artistEl = root.querySelector("[data-music-artist]");
			var miniTitleEl = root.querySelector("[data-music-mini-title]");
			var miniArtistEl = root.querySelector("[data-music-mini-artist]");
			var heroToggleBtn = root.querySelector("[data-music-hero-toggle]");
			var toggleBtn = root.querySelector("[data-music-toggle]");
			var prevBtn = root.querySelector("[data-music-prev]");
			var nextBtn = root.querySelector("[data-music-next]");
			var muteBtn = root.querySelector("[data-music-mute]");
			var modeBtn = root.querySelector("[data-music-mode]");
			var volTrack = root.querySelector("[data-music-vol-track]");
			var volFill = root.querySelector("[data-music-vol-fill]");
			var trackEl = root.querySelector("[data-music-track]");
			var fillEl = root.querySelector("[data-music-fill]");
			var playheadEl = root.querySelector("[data-music-playhead]");
			var waveBars = root.querySelectorAll("[data-music-wave-bar]");
			var currentEl = root.querySelector("[data-music-current]");
			var durationEl = root.querySelector("[data-music-duration]");
			var playlistEl = root.querySelector("[data-music-playlist]");
			var errorEl = root.querySelector("[data-music-error]");

			var playlist = [];
			var index = 0;
			var isPlaying = false;
			var currentCoverUrl = "";
			var cacheStorageKey = "glintide_music_playlist_v1:" + encodeURIComponent(playlistUrl);
			var volume = defaultVolume;
			var isMuted = false;
			var modes = ["sequential", "shuffle", "repeat", "repeat-one"];
			var mode = 0;
			var shuffleHistory = [];
			var audioContext = null;
			var analyser = null;
			var analyserData = null;
			var analyserFrame = 0;
			var waveformPeaksReady = false;
			var waveformRequest = 0;
			var realtimeWaveAllowed = false;

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

			function getAudioContext() {
				var AudioContextCtor = window.AudioContext || window.webkitAudioContext;
				if (!AudioContextCtor) return null;
				try {
					if (!audioContext) audioContext = new AudioContextCtor();
					return audioContext;
				} catch (e) {
					return null;
				}
			}

			function ensureAudioGraph() {
				var context = getAudioContext();
				if (!context || !audio) return false;
				try {
					if (!analyser) {
						analyser = context.createAnalyser();
						analyser.fftSize = 256;
						analyser.smoothingTimeConstant = 0.78;
						analyserData = new Uint8Array(analyser.fftSize);
					}
					if (!audio.__pixMusicGraphAttempted) {
						audio.__pixMusicGraphAttempted = true;
						var source = context.createMediaElementSource(audio);
						audio.__pixMusicSource = source;
						source.connect(analyser);
						analyser.connect(context.destination);
					}
					return !!audio.__pixMusicSource;
				} catch (e) {
					return false;
				}
			}

			function setWaveformHeights(buffer) {
				if (!buffer || !buffer.getChannelData || !waveBars.length) return;
				var channel = buffer.getChannelData(0);
				if (!channel || !channel.length) return;
				var peaks = [];
				var maxPeak = 0;
				for (var i = 0; i < waveBars.length; i++) {
					var start = Math.floor(i * channel.length / waveBars.length);
					var end = Math.max(start + 1, Math.floor((i + 1) * channel.length / waveBars.length));
					var stride = Math.max(1, Math.floor((end - start) / 500));
					var peak = 0;
					for (var sample = start; sample < end; sample += stride) {
						peak = Math.max(peak, Math.abs(channel[sample] || 0));
					}
					peaks.push(peak);
					maxPeak = Math.max(maxPeak, peak);
				}
				if (!maxPeak) return;
				for (var barIndex = 0; barIndex < waveBars.length; barIndex++) {
					var height = Math.round(14 + (peaks[barIndex] / maxPeak) * 86);
					waveBars[barIndex].style.setProperty("--pix-wave-height", Math.max(14, Math.min(100, height)) + "%");
				}
			}

			function decodeTrackWaveform(url, requestId) {
				if (!url || !window.fetch || !getAudioContext()) return;
				fetch(url, { mode: "cors", credentials: "omit" })
					.then(function(response) {
						if (!response.ok) throw new Error("waveform request failed");
						return response.arrayBuffer();
					})
					.then(function(data) {
						return new Promise(function(resolve, reject) {
							audioContext.decodeAudioData(data, resolve, reject);
						});
					})
					.then(function(buffer) {
						if (requestId !== waveformRequest) return;
						setWaveformHeights(buffer);
						waveformPeaksReady = true;
						stopRealtimeWave();
					})
					.catch(function() {
						// 远程音频未开放 CORS 时无法读取波形，继续使用安全的兜底显示。
					});
			}

			function renderRealtimeWave() {
				analyserFrame = 0;
				if (!isPlaying || waveformPeaksReady || !analyser || !analyserData) return;
				analyser.getByteTimeDomainData(analyserData);
				var signal = 0;
				for (var i = 0; i < analyserData.length; i++) signal = Math.max(signal, Math.abs(analyserData[i] - 128));
				if (signal > 2) {
					for (var barIndex = 0; barIndex < waveBars.length; barIndex++) {
						var start = Math.floor(barIndex * analyserData.length / waveBars.length);
						var end = Math.max(start + 1, Math.floor((barIndex + 1) * analyserData.length / waveBars.length));
						var peak = 0;
						for (var sample = start; sample < end; sample++) peak = Math.max(peak, Math.abs(analyserData[sample] - 128));
						var height = Math.round(16 + (peak / Math.max(signal, 1)) * 84);
						waveBars[barIndex].style.setProperty("--pix-wave-height", Math.max(16, Math.min(100, height)) + "%");
					}
				}
				analyserFrame = (window.requestAnimationFrame || function(callback) { return window.setTimeout(callback, 60); })(renderRealtimeWave);
			}

			function startRealtimeWave() {
				if (!realtimeWaveAllowed || waveformPeaksReady || !ensureAudioGraph() || analyserFrame) return;
				analyserFrame = (window.requestAnimationFrame || function(callback) { return window.setTimeout(callback, 60); })(renderRealtimeWave);
			}

			function stopRealtimeWave() {
				if (!analyserFrame) return;
				(window.cancelAnimationFrame || window.clearTimeout)(analyserFrame);
				analyserFrame = 0;
			}

			function currentTrack() { return playlist[index] || null; }

			function readPlaylistCache() {
				if (!playlistUrl) return null;
				try {
					if (!window.localStorage) return null;
					var raw = window.localStorage.getItem(cacheStorageKey);
					if (!raw) return null;
					var cached = JSON.parse(raw);
					if (!cached || !Array.isArray(cached.tracks) || !cached.tracks.length) return null;
					return cached;
				} catch (e) {
					return null;
				}
			}

			function writePlaylistCache(data) {
				if (!playlistUrl || !data || !Array.isArray(data.tracks) || !data.tracks.length) return;
				try {
					if (!window.localStorage) return;
					window.localStorage.setItem(cacheStorageKey, JSON.stringify({
						title: data.title || "",
						tracks: data.tracks,
						savedAt: Date.now()
					}));
				} catch (e) {
					// 隐私模式或存储空间不足时，继续使用在线数据。
				}
			}

			function renderLoadingState() {
				if (!playlistEl) return;
				var html = "<div class=\"pix-music-skeleton-list\" aria-hidden=\"true\">";
				for (var i = 0; i < 3; i++) {
					html += "<span class=\"pix-music-skeleton-row\"><i class=\"pix-music-skeleton-index\"></i><i class=\"pix-music-skeleton-line\"></i><i class=\"pix-music-skeleton-time\"></i></span>";
				}
				playlistEl.innerHTML = html + "</div>";
			}

			function renderEmptyState(message) {
				if (playlistEl) playlistEl.innerHTML = "<div class=\"pix-music-empty\">" + esc(message || "暂无歌曲") + "</div>";
			}

			function setLoadingState(loading) {
				root.classList.toggle("pix-music-is-loading", !!loading);
				root.setAttribute("aria-busy", loading ? "true" : "false");
				if (loading && playlistEl && !playlist.length) renderLoadingState();
			}

			function isSameOrigin(url) {
				try {
					return new URL(url, window.location.href).origin === window.location.origin;
				} catch (e) {
					return false;
				}
			}

			function setCover(url, label) {
				var nextCoverUrl = url || "";
				var covers = [
					[coverArt, coverPlaceholder],
					[miniCoverArt, miniCoverPlaceholder]
				];
				covers.forEach(function(pair) {
					var image = pair[0];
					var placeholder = pair[1];
					if (!image) return;
					if (nextCoverUrl) {
						if (image.getAttribute("src") !== nextCoverUrl) image.src = nextCoverUrl;
						image.alt = label || "专辑封面";
						image.hidden = false;
						if (placeholder) placeholder.hidden = true;
					} else {
						image.removeAttribute("src");
						image.alt = "";
						image.hidden = true;
						if (placeholder) placeholder.hidden = false;
					}
				});
				if (nextCoverUrl === currentCoverUrl) return;
				currentCoverUrl = nextCoverUrl;
				if (!coverBg) return;
				if (!nextCoverUrl) {
					coverBg.style.backgroundImage = "";
					coverBg.classList.remove("is-changing");
					return;
				}
				coverBg.style.backgroundImage = "url(\'" + nextCoverUrl + "\')";
				coverBg.classList.remove("is-changing");
				void coverBg.offsetWidth;
				coverBg.classList.add("is-changing");
			}

			function renderPlaylist() {
				if (!playlistEl) return;
				if (!playlist.length) {
					renderEmptyState("暂无歌曲");
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
					html += \'<span class="pix-music-track-meta">\';
					html += \'<span class="pix-music-trk-title">\' + esc(t.title) + \'</span>\';
					if (t.artist) html += \'<span class="pix-music-trk-artist">\' + esc(t.artist) + \'</span>\';
					html += \'</span>\';
					if (t.duration) html += \'<span class="pix-music-trk-duration">\' + fmt(t.duration) + \'</span>\';
					html += \'</button>\';
				}
				playlistEl.innerHTML = html;
			}

			function updateUI() {
				var t = currentTrack();
				titleEl.textContent = t ? t.title : "暂无歌曲";
				artistEl.textContent = t ? t.artist : "";
				if (miniTitleEl) miniTitleEl.textContent = t ? t.title : "暂无歌曲";
				if (miniArtistEl) miniArtistEl.textContent = t ? t.artist : "";
				setCover(t ? t.cover : "", t ? t.title : "");
				var toggleIcon = isPlaying ? \'<i class="iconfont icon-icon_pause_linear_light" aria-hidden="true"></i>\' : \'<i class="iconfont icon-icon_play_linear_light" aria-hidden="true"></i>\';
				var toggleLabel = isPlaying ? "暂停" : "播放";
				toggleBtn.innerHTML = toggleIcon;
				toggleBtn.setAttribute("aria-label", toggleLabel);
				toggleBtn.title = toggleLabel;
				if (heroToggleBtn) {
					heroToggleBtn.innerHTML = toggleIcon;
					heroToggleBtn.setAttribute("aria-label", toggleLabel);
					heroToggleBtn.title = toggleLabel;
				}
				var modeIcons = ["icon-icon_list_linear_light", "icon-a-icon_arrowsort_linear_light", "icon-icon_list_linear_light", "icon-a-icon_arrowsort_facial_light"];
				var modeLabels = ["顺序播放", "随机播放", "循环播放", "单曲循环"];
				modeBtn.innerHTML = \'<i class="iconfont \' + modeIcons[mode] + \'" aria-hidden="true"></i>\';
				modeBtn.title = modeLabels[mode];
				modeBtn.setAttribute("aria-label", modeLabels[mode]);
				renderPlaylist();
			}

			function applyPlaylistData(data) {
				if (!data || !Array.isArray(data.tracks) || !data.tracks.length) return false;
				playlist = data.tracks;
				index = Math.max(0, Math.min(index, playlist.length - 1));
				isPlaying = false;
				errorEl.textContent = "";
				setLoadingState(false);
				updateUI();
				return true;
			}

			function loadPlaylist() {
				if (!playlistUrl) {
					setLoadingState(false);
					titleEl.textContent = "请配置歌单链接";
					renderEmptyState("请配置歌单链接");
					return;
				}

				var cached = readPlaylistCache();
				if (cached) {
					applyPlaylistData(cached);
				} else {
					setLoadingState(true);
				}

				fetch(apiUrl + "?url=" + encodeURIComponent(playlistUrl))
					.then(function(r) { return r.json(); })
					.then(function(data) {
						if (applyPlaylistData(data)) {
							writePlaylistCache(data);
							return;
						}
						if (!playlist.length) {
							setLoadingState(false);
							titleEl.textContent = "歌单为空或解析失败";
							renderEmptyState("歌单为空或解析失败");
						}
					})
					.catch(function() {
						setLoadingState(false);
						if (playlist.length) {
							errorEl.textContent = "已显示缓存，暂时无法更新";
							return;
						}
						titleEl.textContent = "歌单加载失败";
						renderEmptyState("暂时无法加载歌单");
					});
			}

			function play() {
				var t = currentTrack();
				if (!t) return;
				if (t.audioUrl) {
					stopRealtimeWave();
					waveformPeaksReady = false;
					realtimeWaveAllowed = isSameOrigin(t.audioUrl);
					var currentWaveformRequest = ++waveformRequest;
					audio.src = t.audioUrl;
					decodeTrackWaveform(t.audioUrl, currentWaveformRequest);
					if (audioContext && audioContext.state === "suspended") audioContext.resume();
					audio.play().then(function() {
						isPlaying = true;
						errorEl.textContent = "";
						if (!waveformPeaksReady) startRealtimeWave();
						updateUI();
					}).catch(function() {
						isPlaying = false;
						stopRealtimeWave();
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
				stopRealtimeWave();
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
				if (fillEl) fillEl.style.width = (ratio * 100) + "%";
				if (playheadEl) playheadEl.style.left = (ratio * 100) + "%";
				for (var barIndex = 0; barIndex < waveBars.length; barIndex++) {
					waveBars[barIndex].classList.toggle("is-active", barIndex < Math.ceil(ratio * waveBars.length));
				}
				if (currentEl) currentEl.textContent = fmt(audio.currentTime);
				durationEl.textContent = d > 0 ? fmt(d) : "0:00";
				trackEl.setAttribute("aria-valuenow", String(Math.round(ratio * 100)));
				trackEl.setAttribute("aria-valuetext", String(Math.round(ratio * 100)) + "%");
			}

			function syncVolume() {
				audio.volume = volume;
				audio.muted = isMuted;
				var icon = isMuted || volume === 0 ? "icon-a-nosound" : (volume < 0.5 ? "icon-icon_xiaoyinliang" : "icon-sound");
				muteBtn.innerHTML = \'<i class="iconfont \' + icon + \'" aria-hidden="true"></i>\';
				muteBtn.setAttribute("aria-label", isMuted || volume === 0 ? "取消静音" : "静音");
				muteBtn.title = isMuted || volume === 0 ? "取消静音" : "静音";
				volFill.style.width = (isMuted ? 0 : volume * 100) + "%";
				volTrack.setAttribute("aria-valuenow", String(Math.round((isMuted ? 0 : volume) * 100)));
			}

			function setVolumeFromEvent(e) {
				var rect = volTrack.getBoundingClientRect();
				var ratio = (e.clientX - rect.left) / rect.width;
				volume = Math.max(0, Math.min(1, ratio));
				isMuted = volume === 0;
				syncVolume();
			}

			function setVolumeFromKeyboard(e) {
				var next = volume;
				if (e.key === "ArrowRight" || e.key === "ArrowUp") next += 0.05;
				if (e.key === "ArrowLeft" || e.key === "ArrowDown") next -= 0.05;
				if (e.key === "Home") next = 0;
				if (e.key === "End") next = 1;
				if (next === volume) return;
				e.preventDefault();
				volume = Math.max(0, Math.min(1, next));
				isMuted = volume === 0;
				syncVolume();
			}

			// 事件绑定
			toggleBtn.addEventListener("click", toggle);
			if (heroToggleBtn) heroToggleBtn.addEventListener("click", toggle);
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
			volTrack.addEventListener("keydown", setVolumeFromKeyboard);
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
			trackEl.addEventListener("keydown", function(e) {
				var d = isFinite(audio.duration) ? audio.duration : 0;
				if (!d) return;
				var ratio = audio.currentTime / d;
				if (e.key === "ArrowRight" || e.key === "ArrowUp") ratio += 0.05;
				if (e.key === "ArrowLeft" || e.key === "ArrowDown") ratio -= 0.05;
				if (e.key === "Home") ratio = 0;
				if (e.key === "End") ratio = 1;
				if (ratio === audio.currentTime / d) return;
				e.preventDefault();
				seek(ratio);
				updateProgress();
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
				stopRealtimeWave();
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
