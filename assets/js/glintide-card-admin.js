(function ($) {
	'use strict';

	var editor = document.querySelector('[data-glintide-card-editor]');

	if (!editor) {
		return;
	}

	var typeInputs = editor.querySelectorAll('input[name="glintide_card_type"]');
	var fieldGroups = document.querySelectorAll('[data-glintide-card-fields]');
	var hiddenFields = [
		document.getElementById('postdivrich'),
		document.getElementById('postexcerpt'),
		document.getElementById('commentsdiv'),
		document.getElementById('tagsdiv-post_tag'),
		document.getElementById('formatdiv'),
		document.getElementById('trackbacksdiv'),
		document.getElementById('slugdiv')
	];
	var originalDisplay = [];

	function refreshRichTextEditor() {
		window.dispatchEvent(new Event('resize'));
		if (window.jQuery) {
			window.jQuery(window).trigger('resize');
		}
		window.setTimeout(function () {
			window.dispatchEvent(new Event('resize'));
			if (window.jQuery) {
				window.jQuery(window).trigger('resize');
			}
		}, 240);
	}

	for (var fieldIndex = 0; fieldIndex < hiddenFields.length; fieldIndex += 1) {
		originalDisplay[fieldIndex] = hiddenFields[fieldIndex] ? hiddenFields[fieldIndex].style.display : '';
	}

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

		// 照片类型只隐藏不相关的编辑区域,分类和标签仍属于统一文章信息
		var isPhoto = selectedType === 'photo';
		var isMediaOnly = isPhoto || selectedType === 'music' || selectedType === 'video' || selectedType === 'link';
		for (var h = 0; h < hiddenFields.length; h += 1) {
			if (hiddenFields[h]) {
				hiddenFields[h].style.display = isMediaOnly ? 'none' : originalDisplay[h];
			}
		}
		if (!isPhoto) {
			refreshRichTextEditor();
		}
		// 文档类型状态集中在 body,切换时可逆且不污染编辑器内联样式
		document.body.setAttribute('data-glintide-card-type', selectedType);
		document.body.classList.toggle('glintide-card-photo-mode', isPhoto);
	}

	for (var inputIndex = 0; inputIndex < typeInputs.length; inputIndex += 1) {
		typeInputs[inputIndex].addEventListener('change', updateCardFields);
	}

	updateCardFields();

	var musicSourceRoot = document.querySelector('[data-glintide-music-source]');
	var musicManual = document.querySelector('[data-glintide-music-manual]');
	var musicUrl = document.getElementById('glintide-card-music-url');
	var musicResolve = document.querySelector('.glintide-card-music-resolve');
	var musicUpload = document.querySelector('.glintide-card-music-upload');
	var musicStatus = document.querySelector('.glintide-card-music-status');
	var musicUploadFrame;

	function getMusicSource() {
		var checked = musicSourceRoot ? musicSourceRoot.querySelector('input:checked') : null;
		return checked ? checked.value : 'upload';
	}

	function updateMusicSource() {
		var source = getMusicSource();
		var isRemote = source === 'remote';
		var options = musicSourceRoot ? musicSourceRoot.querySelectorAll('.glintide-card-source-option') : [];
		for (var sourceIndex = 0; sourceIndex < options.length; sourceIndex += 1) {
			var input = options[sourceIndex].querySelector('input');
			options[sourceIndex].classList.toggle('is-selected', input && input.checked);
		}
		if (musicManual) {
			musicManual.style.display = isRemote ? 'none' : 'block';
		}
		if (musicResolve) {
			musicResolve.style.display = isRemote ? 'inline-flex' : 'none';
		}
		if (musicUpload) {
			musicUpload.style.display = isRemote ? 'none' : 'inline-flex';
		}
		if (musicUrl) {
			musicUrl.placeholder = isRemote ? '粘贴网易云歌曲链接' : '选择媒体库中的音频文件';
		}
		if (musicStatus) {
			musicStatus.textContent = isRemote ? '音频地址会自动获取歌名、作者和封面。' : '上传后填写歌名、音乐人和封面。';
		}
	}

	if (musicSourceRoot) {
		var sourceInputs = musicSourceRoot.querySelectorAll('input');
		for (var sourceInputIndex = 0; sourceInputIndex < sourceInputs.length; sourceInputIndex += 1) {
			sourceInputs[sourceInputIndex].addEventListener('change', updateMusicSource);
		}
	}

	if (musicResolve && musicUrl) {
		musicResolve.addEventListener('click', function (event) {
			event.preventDefault();
			var url = musicUrl.value.trim();
			if (!url || !window.glintideMusicAdmin || !glintideMusicAdmin.restUrl) {
				if (musicStatus) {
					musicStatus.textContent = '请先粘贴有效的网易云歌曲链接。';
				}
				return;
			}
			musicResolve.disabled = true;
			if (musicStatus) {
				musicStatus.textContent = '正在获取音乐信息…';
			}
			fetch(glintideMusicAdmin.restUrl + '?url=' + encodeURIComponent(url))
				.then(function (response) { return response.json(); })
				.then(function (result) {
					var data = result && result.title ? result : (result && result.data ? result.data : null);
					if (!data || !data.title) {
						throw new Error('empty');
					}
					document.getElementById('glintide-card-music-title').value = data.title || '';
					document.getElementById('glintide-card-music-artist').value = data.artist || '';
					var coverInput = document.querySelector('input[name="glintide_card_music_cover"]');
					var coverPreview = coverInput ? coverInput.parentNode.querySelector('.glintide-card-cover-preview') : null;
					if (coverInput && data.cover) {
						coverInput.value = data.cover;
						if (coverPreview) {
							coverPreview.innerHTML = '<img src="' + data.cover.replace(/"/g, '&quot;') + '" alt="">';
						}
					}
					if (musicStatus) {
						musicStatus.textContent = '已获取歌名、音乐人和封面。';
					}
				})
				.catch(function () {
					if (musicStatus) {
						musicStatus.textContent = '获取失败，请检查链接后重试。';
					}
				})
				.finally(function () {
					musicResolve.disabled = false;
				});
		});
	}

	if (musicUpload && musicUrl && window.wp && wp.media) {
		musicUpload.addEventListener('click', function (event) {
			event.preventDefault();
			if (musicUploadFrame) {
				musicUploadFrame.open();
				return;
			}
			musicUploadFrame = wp.media({ title: '选择音频', library: { type: 'audio' }, multiple: false, button: { text: '使用此音频' } });
			musicUploadFrame.on('select', function () {
				var attachment = musicUploadFrame.state().get('selection').first().toJSON();
				musicUrl.value = attachment.url || '';
			});
			musicUploadFrame.open();
		});
	}

	updateMusicSource();

	var videoSourceRoot = document.querySelector('[data-glintide-video-source]');
	var videoUrl = document.getElementById('glintide-card-video-url');
	var videoUpload = document.querySelector('.glintide-card-video-upload');
	var videoStatus = document.querySelector('.glintide-card-video-status');
	var videoUploadFrame;

	function updateVideoSource() {
		var checked = videoSourceRoot ? videoSourceRoot.querySelector('input:checked') : null;
		var source = checked ? checked.value : 'youtube';
		var options = videoSourceRoot ? videoSourceRoot.querySelectorAll('.glintide-card-source-option') : [];
		for (var videoOptionIndex = 0; videoOptionIndex < options.length; videoOptionIndex += 1) {
			var input = options[videoOptionIndex].querySelector('input');
			options[videoOptionIndex].classList.toggle('is-selected', input && input.checked);
		}
		if (videoUpload) {
			videoUpload.style.display = source === 'upload' ? 'inline-flex' : 'none';
		}
		if (videoUrl) {
			videoUrl.placeholder = source === 'upload' ? '选择媒体库中的视频文件' : (source === 'bilibili' ? '例如：https://www.bilibili.com/video/BV1xx411c7mD/' : '例如：https://www.youtube.com/watch?v=dQw4w9WgXcQ');
		}
		if (videoStatus) {
			videoStatus.textContent = source === 'upload' ? '上传视频不需要填写正文。' : '粘贴视频链接后不需要填写正文。';
		}
	}

	if (videoSourceRoot) {
		var videoInputs = videoSourceRoot.querySelectorAll('input');
		for (var videoInputIndex = 0; videoInputIndex < videoInputs.length; videoInputIndex += 1) {
			videoInputs[videoInputIndex].addEventListener('change', updateVideoSource);
		}
	}

	if (videoUpload && videoUrl && window.wp && wp.media) {
		videoUpload.addEventListener('click', function (event) {
			event.preventDefault();
			if (videoUploadFrame) {
				videoUploadFrame.open();
				return;
			}
			videoUploadFrame = wp.media({ title: '选择视频', library: { type: 'video' }, multiple: false, button: { text: '使用此视频' } });
			videoUploadFrame.on('select', function () {
				var attachment = videoUploadFrame.state().get('selection').first().toJSON();
				videoUrl.value = attachment.url || '';
			});
			videoUploadFrame.open();
		});
	}

	updateVideoSource();

	// 照片组:多图上传(媒体库选择)
	var galleryRoot = document.querySelector('[data-glintide-card-gallery]');
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

	// 封面选择(文章 / 音乐)
	var coverRoots = document.querySelectorAll('[data-glintide-cover]');
	for (var coverIndex = 0; coverIndex < coverRoots.length; coverIndex += 1) {
		(function (coverRoot) {
			if (!coverRoot || !window.wp || !wp.media) {
				return;
			}

			var coverInput = coverRoot.querySelector('.glintide-card-cover-input');
			var coverAdd   = coverRoot.querySelector('.glintide-card-cover-add');
			var coverClear = coverRoot.querySelector('.glintide-card-cover-clear');
			var coverPrev  = coverRoot.querySelector('.glintide-card-cover-preview');
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
		}(coverRoots[coverIndex]));
	}
}(window.jQuery));
