(function ($) {
	'use strict';

	var editor = document.querySelector('[data-glintide-card-editor]');

	if (!editor) {
		return;
	}

	var typeInputs = editor.querySelectorAll('input[name="glintide_card_type"]');
	var fieldGroups = editor.querySelectorAll('[data-glintide-card-fields]');

	function updateCardFields() {
		var selectedType = '';
		var index;

		for (index = 0; index < typeInputs.length; index += 1) {
			if (typeInputs[index].checked) {
				selectedType = typeInputs[index].value;
				break;
			}
		}

		for (index = 0; index < typeInputs.length; index += 1) {
			typeInputs[index].parentNode.classList.toggle('is-selected', typeInputs[index].checked);
		}

		for (index = 0; index < fieldGroups.length; index += 1) {
			var fieldTypes = fieldGroups[index].getAttribute('data-glintide-card-fields').split(/\s*,\s*/);
			var isActive = fieldTypes.indexOf(selectedType) !== -1;
			fieldGroups[index].classList.toggle('is-active', isActive);
			fieldGroups[index].setAttribute('aria-hidden', isActive ? 'false' : 'true');
		}

		// 照片类型:只保留标题 + 特色图片 + 照片组,隐藏正文/分类/标签
		var isPhoto = selectedType === 'photo';
		var body = document.getElementById('postdivrich');
		var excerpt = document.getElementById('postexcerpt');
		var commentsdiv = document.getElementById('commentsdiv');
		var categorydiv = document.getElementById('categorydiv');
		var tagsdiv = document.getElementById('tagsdiv-post_tag');
		var formatdiv = document.getElementById('formatdiv');
		var trackbacksdiv = document.getElementById('trackbacksdiv');
		var slugHelp = document.getElementById('slugdiv');
		var hideSelectors = [body, excerpt, commentsdiv, categorydiv, tagsdiv, formatdiv, trackbacksdiv, slugHelp];
		for (var h = 0; h < hideSelectors.length; h += 1) {
			if (hideSelectors[h]) {
				hideSelectors[h].style.display = isPhoto ? 'none' : '';
			}
		}
		// 文档标题后缀提示
		document.body.setAttribute('data-glintide-card-type', selectedType);
	}

	for (var inputIndex = 0; inputIndex < typeInputs.length; inputIndex += 1) {
		typeInputs[inputIndex].addEventListener('change', updateCardFields);
	}

	updateCardFields();

	// 照片组:多图上传(媒体库选择)
	var galleryRoot = editor.querySelector('[data-glintide-card-gallery]');
	if (galleryRoot && window.wp && wp.media) {
		var listEl = galleryRoot.querySelector('.glintide-card-gallery-list');
		var inputEl = galleryRoot.querySelector('.glintide-card-gallery-input');
		var addBtn = galleryRoot.querySelector('.glintide-card-gallery-add');
		var clearBtn = galleryRoot.querySelector('.glintide-card-gallery-clear');
		var frame;

		function getIds() {
			return inputEl.value ? inputEl.value.split(',').map(function (v) { return parseInt(v, 10); }).filter(function (v) { return !isNaN(v); }) : [];
		}

		function renderList(ids) {
			listEl.innerHTML = '';
			ids.forEach(function (id) {
				var item = wp.media.attachment(id);
				var url = item.get('url');
				if (item.get('sizes') && item.get('sizes').thumbnail) {
					url = item.get('sizes').thumbnail.url;
				}
				if (!url) {
					return;
				}
				var li = document.createElement('li');
				var img = document.createElement('img');
				img.src = url;
				li.appendChild(img);
				listEl.appendChild(li);
			});
		}

		addBtn.addEventListener('click', function (event) {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			var ids = getIds();

			frame = wp.media({
				frame: 'post',
				state: ids.length ? 'gallery-edit' : 'gallery-library',
				title: '选择照片',
				editing: !!ids.length,
				multiple: true,
				selection: ids,
				library: { type: 'image' }
			});

			frame.on('update', function (selection) {
				var picked = [];
				if (selection && selection.models) {
					selection.models.forEach(function (attachment) {
						picked.push(String(attachment.id));
					});
				} else if (selection && typeof selection.each === 'function') {
					selection.each(function (attachment) {
						picked.push(String(attachment.id));
					});
				}

				var MAX = 9;
				var newIds = picked.slice(0, MAX);
				var existing = newIds.length;

				if (picked.length > MAX) {
					window.alert('最多只能上传 9 张照片，已截取前 ' + MAX + ' 张。');
				}

				inputEl.value = newIds.join(',');
				renderList(newIds);
				clearBtn.style.display = newIds.length ? '' : 'none';
				addBtn.textContent = newIds.length ? '编辑图片' : '添加图片';
			});

			frame.open();
		});

		clearBtn.addEventListener('click', function (event) {
			event.preventDefault();
			inputEl.value = '';
			listEl.innerHTML = '';
			clearBtn.style.display = 'none';
			addBtn.textContent = '添加图片';
		});
	}

	// 音乐封面选择
	var coverRoot = editor.querySelector('[data-glintide-cover]');
	if (coverRoot && window.wp && wp.media) {
		var coverInput  = coverRoot.querySelector('.glintide-card-cover-input');
		var coverAdd    = coverRoot.querySelector('.glintide-card-cover-add');
		var coverClear  = coverRoot.querySelector('.glintide-card-cover-clear');
		var coverPrev   = coverRoot.querySelector('.glintide-card-cover-preview');
		var coverFrame;

		function coverShow(url) {
			if (url) {
				coverPrev.innerHTML = '<img src="' + url + '" alt="">';
				coverClear.style.display = '';
				coverAdd.textContent = '更换封面';
			} else {
				coverPrev.innerHTML = '<span class="glintide-card-cover-empty"><i class="ri-image-line" aria-hidden="true"></i></span>';
				coverClear.style.display = 'none';
				coverAdd.textContent = '选择封面';
			}
		}

		coverAdd.addEventListener('click', function (event) {
			event.preventDefault();
			if (coverFrame) {
				coverFrame.open();
				return;
			}
			coverFrame = wp.media({
				title: '选择封面',
				library: { type: 'image' },
				multiple: false,
				button: { text: '使用此封面' }
			});
			coverFrame.on('select', function () {
				var att = coverFrame.state().get('selection').first().toJSON();
				coverInput.value = String(att.id);
				coverShow(att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url);
			});
			coverFrame.open();
		});

		coverClear.addEventListener('click', function (event) {
			event.preventDefault();
			coverInput.value = '';
			coverShow('');
		});
	}
}(window.jQuery));
