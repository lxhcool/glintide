(function ($) {
	'use strict';

	var editor = document.querySelector('[data-glintide-card-editor]');

	if (!editor) {
		return;
	}

	var typeInputs = editor.querySelectorAll('input[name="glintide_card_type"]');
	var fieldGroups = editor.querySelectorAll('[data-glintide-card-fields]');
	var mediaButton = editor.querySelector('[data-glintide-card-media-select]');
	var galleryInput = editor.querySelector('#glintide-card-gallery');

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
			var isActive = fieldGroups[index].getAttribute('data-glintide-card-fields') === selectedType;
			fieldGroups[index].classList.toggle('is-active', isActive);
			fieldGroups[index].setAttribute('aria-hidden', isActive ? 'false' : 'true');
		}
	}

	for (var inputIndex = 0; inputIndex < typeInputs.length; inputIndex += 1) {
		typeInputs[inputIndex].addEventListener('change', updateCardFields);
	}

	updateCardFields();

	if (mediaButton && galleryInput && window.wp && wp.media) {
		var mediaFrame;

		mediaButton.addEventListener('click', function (event) {
			event.preventDefault();

			if (!mediaFrame) {
				mediaFrame = wp.media({
					title: '选择卡片照片',
					button: {
						text: '添加到照片组'
					},
					library: {
						type: 'image'
					},
					multiple: true
				});

				mediaFrame.on('select', function () {
					var currentUrls = galleryInput.value.split(/\r?\n/).map(function (url) {
						return url.trim();
					}).filter(Boolean);
					var selectedItems = mediaFrame.state().get('selection').toJSON();

					selectedItems.forEach(function (attachment) {
						if (attachment.url && currentUrls.indexOf(attachment.url) === -1) {
							currentUrls.push(attachment.url);
						}
					});

					galleryInput.value = currentUrls.join('\n');
				});
			}

			mediaFrame.open();
		});
	}
}(window.jQuery));
