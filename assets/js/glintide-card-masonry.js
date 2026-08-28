(function (window, document) {
	'use strict';

	var Glintide = window.Glintide = window.Glintide || {};
	var requestFrame = window.requestAnimationFrame || function (callback) {
		return window.setTimeout(callback, 16);
	};

	function getColumnCount(width) {
		if (window.innerWidth <= 768 || width < 520) {
			return 1;
		}

		if (width >= 880) {
			return 3;
		}

		return 2;
	}

	function initMasonry(grid) {
		var cards = grid.querySelectorAll('.glintide-card');

		if (!cards.length) {
			return;
		}

		var mediaItems = grid.querySelectorAll('img, iframe, video, audio');
		var frame = 0;

		function schedule() {
			if (frame) {
				return;
			}

			frame = requestFrame(function () {
				frame = 0;
				layout();
			});
		}

		function layout() {
			var width = grid.clientWidth;

			if (!width) {
				return;
			}

			var styles = window.getComputedStyle(grid);
			var gap = parseFloat(styles.getPropertyValue('--glintide-masonry-gap')) || 16;
			var columns = Math.min(getColumnCount(width), cards.length);
			var columnWidth = (width - (gap * (columns - 1))) / columns;
			var heights = [];
			var index;

			grid.classList.add('is-masonry');

			for (index = 0; index < columns; index += 1) {
				heights[index] = 0;
			}

			for (index = 0; index < cards.length; index += 1) {
				var card = cards[index];
				var shortestColumn = 0;
				var columnIndex;

				card.style.width = columnWidth + 'px';

				for (columnIndex = 1; columnIndex < columns; columnIndex += 1) {
					if (heights[columnIndex] < heights[shortestColumn]) {
						shortestColumn = columnIndex;
					}
				}

				card.style.left = (shortestColumn * (columnWidth + gap)) + 'px';
				card.style.top = heights[shortestColumn] + 'px';
				heights[shortestColumn] += card.offsetHeight + gap;
			}

			var maxHeight = 0;
			for (index = 0; index < heights.length; index += 1) {
				maxHeight = Math.max(maxHeight, heights[index]);
			}

			grid.style.height = Math.max(0, maxHeight - gap) + 'px';
		}

		for (var mediaIndex = 0; mediaIndex < mediaItems.length; mediaIndex += 1) {
			var media = mediaItems[mediaIndex];
			media.addEventListener('load', schedule);
			media.addEventListener('loadedmetadata', schedule);
			media.addEventListener('loadeddata', schedule);
		}

		if (window.ResizeObserver) {
			var observer = new window.ResizeObserver(schedule);
			for (var cardIndex = 0; cardIndex < cards.length; cardIndex += 1) {
				observer.observe(cards[cardIndex]);
			}
		}

		window.addEventListener('resize', schedule);
		window.addEventListener('load', schedule);
		layout();
		schedule();
	}

	Glintide.initCardMasonry = initMasonry;

	function init() {
		var grids = document.querySelectorAll('[data-glintide-masonry], .glintide-card-grid');

		for (var index = 0; index < grids.length; index += 1) {
			initMasonry(grids[index]);
		}
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}(window, document));
