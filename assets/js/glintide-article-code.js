(function () {
	'use strict';
	function copyText(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}
		return new Promise(function (resolve, reject) {
			var previous = document.activeElement;
			var input = document.createElement('textarea');
			input.value = text;
			input.setAttribute('readonly', '');
			input.style.position = 'fixed';
			input.style.opacity = '0';
			document.body.appendChild(input);
			input.select();
			var copied = document.execCommand('copy');
			input.remove();
			if (previous && previous.focus) { previous.focus(); }
			if (copied) { resolve(); } else { reject(new Error('copy')); }
		});
	}

	function initCode() {
		document.querySelectorAll('.glintide-note-content pre, .glintide-card-single-content pre').forEach(function (pre) {
			if (pre.dataset.glintideCode) { return; }
			var code = pre.querySelector('code');
			if (!code) { return; }
			pre.dataset.glintideCode = '1';
			var languageClass = Array.from(code.classList).find(function (name) { return name.indexOf('language-') === 0; });
			var language = languageClass ? languageClass.slice(9) : 'text';
			var block = document.createElement('div');
			block.className = 'glintide-code-block';
			var bar = document.createElement('div');
			bar.className = 'glintide-code-toolbar';
			var label = document.createElement('span');
			label.textContent = language;
			var button = document.createElement('button');
			button.type = 'button';
			button.title = '复制代码';
			button.setAttribute('aria-label', '复制代码');
			button.innerHTML = '<i class="ri-file-copy-line" aria-hidden="true"></i>';
			var status = document.createElement('span');
			status.className = 'screen-reader-text';
			status.setAttribute('role', 'status');
			bar.append(label, button, status);
			pre.before(block);
			block.append(bar, pre);
			pre.tabIndex = 0;
			pre.setAttribute('aria-label', language + ' 代码');
			if (window.Prism && window.Prism.languages[language]) { window.Prism.highlightElement(code); }
			button.addEventListener('click', function () {
				copyText(code.textContent).then(function () {
					status.textContent = '代码已复制';
					button.title = '已复制';
					button.querySelector('i').className = 'ri-check-line';
					setTimeout(function () {
						button.title = '复制代码';
						button.querySelector('i').className = 'ri-file-copy-line';
						status.textContent = '';
					}, 2000);
				}).catch(function () {
					status.textContent = '复制失败，请选择代码后复制';
					button.title = status.textContent;
				});
			});
		});
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initCode); }
	else { initCode(); }
	document.addEventListener('glintide:cards-appended', initCode);
}());
