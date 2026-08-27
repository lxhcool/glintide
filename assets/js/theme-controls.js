(function () {
	'use strict';

	var root = document.documentElement;
	var storageKey = 'glintide-theme';

	function getStoredTheme() {
		try {
			return window.localStorage.getItem( storageKey );
		} catch ( error ) {
			return null;
		}
	}

	function storeTheme( theme ) {
		try {
			window.localStorage.setItem( storageKey, theme );
		} catch ( error ) {
			// Storage can be unavailable in private or restricted browsing modes.
		}
	}

	function updateToggle( isDark ) {
		document.querySelectorAll( '[data-glintide-theme-toggle]' ).forEach( function ( button ) {
			var icon = button.querySelector( '[data-glintide-theme-icon]' );
			button.setAttribute( 'aria-pressed', isDark ? 'true' : 'false' );
			button.setAttribute( 'aria-label', isDark ? '切换浅色模式' : '切换深色模式' );
			button.setAttribute( 'title', isDark ? '切换浅色模式' : '切换深色模式' );

			if ( icon ) {
				icon.classList.toggle( 'ri-moon-line', ! isDark );
				icon.classList.toggle( 'ri-sun-line', isDark );
			}
		} );
	}

	function applyTheme( theme, persist ) {
		var isDark = theme === 'dark';

		root.classList.toggle( 'glintide-dark', isDark );
		root.setAttribute( 'data-glintide-theme', isDark ? 'dark' : 'light' );
		root.style.colorScheme = isDark ? 'dark' : 'light';
		updateToggle( isDark );

		if ( persist ) {
			storeTheme( isDark ? 'dark' : 'light' );
		}
	}

	function getPreferredTheme() {
		if ( window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' ).matches ) {
			return 'dark';
		}

		return 'light';
	}

	var storedTheme = getStoredTheme();
	var initialTheme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : getPreferredTheme();
	applyTheme( initialTheme, false );

	document.addEventListener( 'error', function ( event ) {
		var image = event.target;
		var fallback;

		if ( ! image || ! image.matches || ! image.matches( '[data-glintide-avatar]' ) ) {
			return;
		}

		fallback = image.getAttribute( 'data-glintide-avatar-fallback' );
		if ( ! fallback || image.getAttribute( 'src' ) === fallback ) {
			return;
		}

		image.setAttribute( 'src', fallback );
	}, true );

	document.addEventListener( 'click', function ( event ) {
		var target = event.target;
		var themeToggle = target && target.closest ? target.closest( '[data-glintide-theme-toggle]' ) : null;

		if ( themeToggle ) {
			applyTheme( root.classList.contains( 'glintide-dark' ) ? 'light' : 'dark', true );
		}
	} );

	document.addEventListener( 'DOMContentLoaded', function () {
		updateToggle( root.classList.contains( 'glintide-dark' ) );
	} );
}());
