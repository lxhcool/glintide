/**
 * 右栏头像账户菜单 popover。
 *
 * 点击头像展开菜单(进入后台 / 退出登录),点击外部或按 Esc 关闭。
 * 采用 fixed 定位:右栏容器是 overflow-y:auto,absolute 会被裁剪。
 */
(function () {
	'use strict';

	var current = null;

	function closeMenu(refocus) {
		if (!current) {
			return;
		}
		var btn = current.btn;
		current.pop.hidden = true;
		current.wrap.classList.remove('is-open');
		btn.setAttribute('aria-expanded', 'false');
		current = null;

		if (refocus && btn.focus) {
			btn.focus();
		}
	}

	function place(pop, btn) {
		var rect = btn.getBoundingClientRect();
		var width = pop.offsetWidth;
		var height = pop.offsetHeight;

		var top = rect.bottom + 8;
		// 下方空间不足时翻到按钮上方
		if (top + height > window.innerHeight - 8) {
			top = Math.max(8, rect.top - height - 8);
		}

		var left = rect.right - width;
		left = Math.max(8, Math.min(left, window.innerWidth - width - 8));

		pop.style.top = top + 'px';
		pop.style.left = left + 'px';
	}

	function openMenu(wrap) {
		var btn = wrap.querySelector('[data-glintide-user-toggle]');
		var pop = wrap.querySelector('[data-glintide-user-popover]');
		if (!btn || !pop) {
			return;
		}

		// 再次点击同一个头像 = 收起
		if (current && current.wrap === wrap) {
			closeMenu(false);
			return;
		}
		closeMenu(false);

		pop.hidden = false;
		// 强制回流,确保从初始状态播放过渡动画
		void pop.offsetWidth;
		wrap.classList.add('is-open');
		btn.setAttribute('aria-expanded', 'true');

		place(pop, btn);
		current = { wrap: wrap, btn: btn, pop: pop };
	}

	function closestMenu(target) {
		if (!target || !target.closest) {
			return null;
		}
		var toggle = target.closest('[data-glintide-user-toggle]');
		return toggle ? toggle.closest('[data-glintide-user-menu]') : null;
	}

	// 事件委托:PJAX 换页后新渲染的右栏无需重新绑定
	document.addEventListener('click', function (event) {
		var wrap = closestMenu(event.target);
		if (wrap) {
			event.preventDefault();
			openMenu(wrap);
			return;
		}
		if (current && !current.wrap.contains(event.target)) {
			closeMenu(false);
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && current) {
			closeMenu(true);
		}
	});

	// 视口变化时浮层会与按钮脱节,直接关闭
	window.addEventListener('resize', function () {
		closeMenu(false);
	});
	window.addEventListener('scroll', function () {
		closeMenu(false);
	}, true);
}());
