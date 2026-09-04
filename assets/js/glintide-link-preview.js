/**
 * 外部网页预览:固定桌面 viewport 后按卡片宽度缩放。
 */
(function () {
	'use strict';

	function initLinkPreviews() {
		var previews = document.querySelectorAll('[data-glintide-link-preview]');

		previews.forEach(function (preview) {
			if (preview.__glintideLinkPreviewInited) {
				return;
			}

			var frame = preview.querySelector('iframe');
			if (!frame) {
				return;
			}

			function resize() {
				var width = preview.clientWidth || 640;
				var scale = Math.min(1, width / 1280);
				var visibleHeight = (preview.clientHeight || width * 0.5625) / scale;
				var scrollDistance = Math.max(120, 1000 - visibleHeight);

				preview.style.setProperty('--glintide-link-scale', String(scale));
				preview.style.setProperty('--glintide-link-scroll-distance', '-' + scrollDistance + 'px');
			}

			preview.__glintideLinkPreviewInited = true;
			resize();
			window.addEventListener('resize', resize, { passive: true });
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLinkPreviews);
	} else {
		initLinkPreviews();
	}

	document.addEventListener('glintide:cards-appended', initLinkPreviews);
}());