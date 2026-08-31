/**
 * 内容卡片详情弹窗(小红书笔记式)。
 *
 * 左栏为笔记内容,右栏为作者 + 评论列表 + 点赞/评论互动栏 + 评论输入框;
 * 照片/音乐/视频卡片为全交互设计,无详情弹窗;链接卡片保持外部链接。
 */
(function () {
	'use strict';

	var MASK = null;
	var lastFocus = null;
	var CURRENT = null; // { postId, liked, likes }

	function ajaxUrl() {
		return (window.glintide_card_ajax && window.glintide_card_ajax.url) || '/wp-admin/admin-ajax.php';
	}

	function ensureMask() {
		if (MASK) {
			return MASK;
		}
		MASK = document.createElement('div');
		MASK.className = 'glintide-modal-mask';
		MASK.setAttribute('data-glintide-modal-close', '');
		MASK.innerHTML =
			'<div class="glintide-modal glintide-note-modal" role="dialog" aria-modal="true">' +
			'<button type="button" class="glintide-modal-close" data-glintide-modal-close aria-label="关闭">&times;</button>' +
			'<div class="glintide-modal-inner"></div>' +
			'</div>';
		document.body.appendChild(MASK);

		MASK.addEventListener('click', function (event) {
			// 仅点击遮罩背景或关闭按钮时关闭,弹窗内部点击向上爬会命中遮罩自身
			if (event.target === MASK || event.target.closest('.glintide-modal-close')) {
				closeModal();
			}
		});

		MASK.addEventListener('click', function (event) {
			// 点赞
			var likeBtn = event.target.closest('[data-glintide-note-like]');
			if (likeBtn && CURRENT) {
				toggleLike(likeBtn);
				return;
			}
			// 发表评论
			if (event.target.closest('[data-glintide-note-send]')) {
				submitComment();
			}
		});

		MASK.addEventListener('keydown', function (event) {
			if (event.key === 'Enter' && event.target.matches && event.target.matches('[data-glintide-note-input]')) {
				event.preventDefault();
				submitComment();
			}
		});

		return MASK;
	}

	function closeModal() {
		if (!MASK) {
			return;
		}
		MASK.classList.remove('is-open');
		document.body.classList.remove('glintide-modal-open');
		if (lastFocus && lastFocus.focus) {
			lastFocus.focus();
		}
		lastFocus = null;
		CURRENT = null;
	}

	function escText(text) {
		var div = document.createElement('div');
		div.textContent = text == null ? '' : String(text);
		return div.innerHTML;
	}

	function renderLoading() {
		ensureMask().querySelector('.glintide-modal-inner').innerHTML =
			'<div class="glintide-modal-loading"><i class="ri-loader-4-line" aria-hidden="true"></i>加载中…</div>';
		MASK.classList.add('is-open');
		document.body.classList.add('glintide-modal-open');
	}

	function renderError() {
		var inner = ensureMask().querySelector('.glintide-modal-inner');
		inner.innerHTML = '<div class="glintide-modal-loading">加载失败,请稍后重试</div>';
	}

	function commentHtml(item) {
		return '<li class="glintide-note-comment">' +
			'<img class="glintide-note-comment-avatar" src="' + escText(item.avatar) + '" alt="" loading="lazy">' +
			'<div class="glintide-note-comment-body">' +
			'<span class="glintide-note-comment-author">' + escText(item.author) + '</span>' +
			'<p class="glintide-note-comment-content">' + escText(item.content) + '</p>' +
			'<span class="glintide-note-comment-date">' + escText(item.date) + '</span>' +
			'</div>' +
			'</li>';
	}

	function renderNote(data) {
		CURRENT = { postId: data.post_id, liked: !!data.liked, likes: data.likes || 0, nonce: data.comment_nonce };

		var comments = data.comments || [];
		var commentsHtml = comments.length
			? comments.map(commentHtml).join('')
			: '<li class="glintide-note-comments-empty">还没有评论,来抢沙发吧~</li>';

		var likeClass = CURRENT.liked ? ' is-liked' : '';
		var likeIcon = CURRENT.liked ? 'ri-heart-3-fill' : 'ri-heart-3-line';

		var html =
			'<div class="glintide-note">' +
			'<div class="glintide-note-author">' +
			'<img class="glintide-note-author-avatar" src="' + escText(data.avatar) + '" alt="">' +
			'<div class="glintide-note-author-info">' +
			'<span class="glintide-note-author-name">' + escText(data.author) + '</span>' +
			'<span class="glintide-note-date">' + escText(data.date) + '</span>' +
			'</div>' +
			'<span class="glintide-note-chip">' + escText(data.type_label) + '</span>' +
			'</div>' +
			'<h2 class="glintide-note-title">' + escText(data.title) + '</h2>' +
			'<div class="glintide-note-content">' + (data.content_html || '') + '</div>' +
			'<div class="glintide-note-actions">' +
			'<button type="button" class="glintide-note-like' + likeClass + '" data-glintide-note-like>' +
			'<i class="' + likeIcon + '" aria-hidden="true"></i>' +
			'<span data-glintide-note-like-count>' + CURRENT.likes + '</span>' +
			'</button>' +
			'<span class="glintide-note-action-count"><i class="ri-chat-3-line" aria-hidden="true"></i><span data-glintide-note-comment-count>' + comments.length + '</span></span>' +
			'</div>' +
			'<div class="glintide-note-divider">' + comments.length + ' 条评论</div>' +
			'<ul class="glintide-note-comments" data-glintide-note-list>' + commentsHtml + '</ul>' +
			'</div>' +
			'<div class="glintide-note-comment-form">' +
			'<input type="text" class="glintide-note-comment-input" data-glintide-note-input placeholder="说点什么…" maxlength="1000">' +
			'<button type="button" class="glintide-note-send" data-glintide-note-send>发送</button>' +
			'</div>' +
			'<div class="glintide-modal-foot"><a href="' + escText(data.permalink) + '" data-glintide-no-pjax>在独立页面打开 <i class="ri-arrow-right-up-line" aria-hidden="true"></i></a></div>';

		ensureMask().querySelector('.glintide-modal-inner').innerHTML = html;
		MASK.classList.add('is-open');
		document.body.classList.add('glintide-modal-open');

		var input = MASK.querySelector('[data-glintide-note-input]');
		if (input) {
			input.focus();
		}
	}

	function toggleLike(btn) {
		if (!CURRENT || btn.dataset.glintideLiking) {
			return;
		}
		btn.dataset.glintideLiking = '1';

		var body = new URLSearchParams({
			action: 'glintide_card_like',
			post_id: CURRENT.postId
		});

		fetch(ajaxUrl(), { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) { return res.json(); })
			.then(function (res) {
				delete btn.dataset.glintideLiking;
				if (!res || !res.success) {
					return;
				}
				CURRENT.liked = !!res.data.liked;
				CURRENT.likes = res.data.count;
				btn.classList.toggle('is-liked', CURRENT.liked);
				var icon = btn.querySelector('i');
				if (icon) {
					icon.className = CURRENT.liked ? 'ri-heart-3-fill' : 'ri-heart-3-line';
				}
				var count = btn.querySelector('[data-glintide-note-like-count]');
				if (count) {
					count.textContent = CURRENT.likes;
				}
			})
			.catch(function () { delete btn.dataset.glintideLiking; });
	}

	function submitComment() {
		if (!CURRENT) {
			return;
		}
		var input = MASK.querySelector('[data-glintide-note-input]');
		var content = input ? input.value.trim() : '';
		if (!content || input.dataset.glintidePosting) {
			return;
		}
		input.dataset.glintidePosting = '1';

		var body = new URLSearchParams({
			action: 'glintide_card_comment',
			post_id: CURRENT.postId,
			comment: content,
			nonce: CURRENT.nonce
		});

		fetch(ajaxUrl(), { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) { return res.json(); })
			.then(function (res) {
				delete input.dataset.glintidePosting;
		if (!res || !res.success) {
			var tip = input.parentElement.querySelector('.glintide-note-comment-tip');
			if (!tip) {
				tip = document.createElement('span');
				tip.className = 'glintide-note-comment-tip';
				input.parentElement.insertBefore(tip, input);
			}
			tip.textContent = (res && res.data && res.data.msg) || '评论失败';
			setTimeout(function () { tip.remove(); }, 3000);
			return;
		}
				var list = MASK.querySelector('[data-glintide-note-list]');
				if (list) {
					var empty = list.querySelector('.glintide-note-comments-empty');
					if (empty) {
						empty.remove();
					}
					list.insertAdjacentHTML('beforeend', commentHtml(res.data.comment));
					list.scrollTop = list.scrollHeight;
				}
				var count = MASK.querySelector('[data-glintide-note-comment-count]');
				if (count) {
					count.textContent = res.data.count;
				}
				input.value = '';
			})
			.catch(function () {
				delete input.dataset.glintidePosting;
				var tip = input.parentElement.querySelector('.glintide-note-comment-tip');
				if (!tip) {
					tip = document.createElement('span');
					tip.className = 'glintide-note-comment-tip';
					input.parentElement.insertBefore(tip, input);
				}
				tip.textContent = '网络异常,请重试';
				setTimeout(function () { tip.remove(); }, 3000);
			});
	}

	function openModal(url, postId) {
		lastFocus = document.activeElement;
		renderLoading();

		var body = new URLSearchParams({ action: 'glintide_card_detail', post_id: postId });
		fetch(ajaxUrl(), { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) { return res.json(); })
			.then(function (res) {
				if (!res || !res.success || !res.data) {
					renderError();
					return;
				}
				res.data.post_id = postId;
				res.data.nonce = res.data.comment_nonce;
				renderNote(res.data);
			})
			.catch(function () {
				renderError();
			});
	}

	document.addEventListener('click', function (event) {
		var trigger = event.target && event.target.closest ? event.target.closest('[data-glintide-modal]') : null;
		if (!trigger || event.defaultPrevented) {
			return;
		}
		if (event.target.closest('button, video, audio, input')) {
			return;
		}
		var postId = trigger.getAttribute('data-glintide-modal');
		if (!postId) {
			return;
		}
		event.preventDefault();
		openModal(trigger.getAttribute('href') || '#', postId);
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeModal();
		}
	});
}());
