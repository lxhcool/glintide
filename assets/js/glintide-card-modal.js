/**
 * 内容卡片详情弹窗(小红书笔记式)。
 *
 * 头部为作者 + 独立页入口;左栏正文阅读,右栏评论列表 + 评论输入框;
 * 照片/音乐/视频卡片为全交互设计,无详情弹窗;链接卡片保持外部链接。
 */
(function () {
	'use strict';

	var MASK = null;
	var lastFocus = null;
	var CURRENT = null; // { postId, nonce }

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
			'<div class="glintide-modal glintide-note-modal" role="dialog" aria-modal="true" tabindex="-1">' +
			'<button type="button" class="glintide-modal-close" data-glintide-modal-close aria-label="关闭"><i class="ri-close-line" aria-hidden="true"></i></button>' +
			'<div class="glintide-modal-inner"></div>' +
			'</div>';
		document.body.appendChild(MASK);

		MASK.addEventListener('click', function (event) {
			// 仅点击遮罩背景或关闭按钮时关闭,弹窗内部点击向上爬会命中遮罩自身
			if (event.target === MASK || event.target.closest('.glintide-modal-close')) {
				closeModal();
				return;
			}
			// 点赞
			var likeBtn = event.target.closest('[data-glintide-note-like]');
			if (likeBtn && CURRENT) {
				toggleLike(likeBtn);
				return;
			}
			// 发表评论
			if (event.target.closest('[data-glintide-note-send]')) {
				submitComment();
				return;
			}
			// 回复某条评论
			var replyBtn = event.target.closest('[data-comment-id][data-comment-author]');
			if (replyBtn && CURRENT) {
				CURRENT.replyTo = {
					id: parseInt(replyBtn.getAttribute('data-comment-id'), 10) || 0,
					author: replyBtn.getAttribute('data-comment-author')
				};
				openComposer();
				var input = MASK.querySelector('[data-glintide-note-input]');
				if (input && CURRENT.replyTo.author) {
					input.placeholder = '回复 @' + CURRENT.replyTo.author + '…';
				}
				return;
			}
			// 取消编辑,回到收起态
			if (event.target.closest('[data-glintide-composer-cancel]')) {
				CURRENT.replyTo = null;
				renderCommentBar();
				return;
			}
			// 点胶囊或评论图标展开输入框
			if (event.target.closest('[data-glintide-bar-open]')) {
				CURRENT.replyTo = null;
				openComposer();
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

	// 打开弹窗:先强制回流,确保遮罩/弹窗从初始状态播放过渡动画
	// (新建元素与加类同帧时浏览器不会触发 transition)
	function openMask() {
		void MASK.offsetWidth;
		MASK.classList.add('is-open');
		document.body.classList.add('glintide-modal-open');
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
		openMask();
	}

	function renderError() {
		var inner = ensureMask().querySelector('.glintide-modal-inner');
		inner.innerHTML = '<div class="glintide-modal-loading">加载失败,请稍后重试</div>';
	}

	function commentHtml(item, postAuthor, isReply) {
		var authorBadge = (postAuthor && item.author === postAuthor)
			? '<span class="glintide-note-comment-badge">作者</span>'
			: '';
		var replyPrefix = (isReply && item.reply_to)
			? '<span class="glintide-note-comment-reply-to">回复 @' + escText(item.reply_to) + '：</span>'
			: '';
		return '<li class="glintide-note-comment' + (isReply ? ' glintide-note-comment--reply' : '') + '" data-comment-id="' + escText(item.id) + '">' +
			'<img class="glintide-note-comment-avatar" src="' + escText(item.avatar) + '" alt="" loading="lazy">' +
			'<div class="glintide-note-comment-body">' +
			'<span class="glintide-note-comment-author">' + escText(item.author) + authorBadge + '</span>' +
			'<p class="glintide-note-comment-content">' + replyPrefix + escText(item.content) + '</p>' +
			'<span class="glintide-note-comment-date" title="' + escText(item.date) + '">' + escText(relativeDate(item.date)) + '</span>' +
			'<button type="button" class="glintide-note-comment-reply-btn" data-comment-id="' + escText(item.id) + '" data-comment-author="' + escText(item.author) + '">回复</button>' +
			'</div>' +
			'</li>';
	}

	// 按根评论分组:根 + 其回复依次排列,回复缩进显示
	function buildCommentSequence(comments) {
		var byId = {};
		var roots = [];
		comments.forEach(function (c) {
			c.replies = [];
			byId[c.id] = c;
		});
		comments.forEach(function (c) {
			if (c.parent && byId[c.parent]) {
				byId[c.parent].replies.push(c);
			} else {
				roots.push(c);
			}
		});

		var seq = [];
		roots.forEach(function (root) {
			seq.push({ item: root, isReply: false });
			root.replies.forEach(function (reply) {
				seq.push({ item: reply, isReply: true });
				reply.replies.forEach(function (nested) {
					// 回复的回复归并到同一根下,最多两级缩进
					seq.push({ item: nested, isReply: true });
				});
			});
		});
		return seq;
	}

	// 小红书式相对时间:刚刚 / N 分钟前 / N 小时前 / N 天前,超过一周显示原日期
	function relativeDate(dateStr) {
		var ts = new Date(String(dateStr).replace(/-/g, '/')).getTime();
		if (isNaN(ts)) {
			return dateStr;
		}
		var diff = Math.floor((Date.now() - ts) / 1000);
		if (diff < 60) {
			return '刚刚';
		}
		if (diff < 3600) {
			return Math.floor(diff / 60) + ' 分钟前';
		}
		if (diff < 86400) {
			return Math.floor(diff / 3600) + ' 小时前';
		}
		if (diff < 7 * 86400) {
			return Math.floor(diff / 86400) + ' 天前';
		}
		return dateStr;
	}

	/* ---------- 表情选择器(原生 Unicode 表情,弹窗/独立页通用) ---------- */
	var EMOJIS = [
		'😀', '😄', '😂', '🤣', '😊', '😍', '😘', '😜', '🤔', '😏',
		'🙂', '🙃', '😉', '😌', '🥰', '🤗', '🤭', '🤫', '😑', '🙄',
		'😴', '🤤', '😪', '😒', '😔', '😢', '😭', '😤', '😡', '🤬',
		'🤯', '😱', '🥵', '🥶', '😳', '🥺', '😇', '🤠', '🤡', '👻',
		'💀', '🤖', '👽', '😹', '🙀', '🙈', '👏', '👍', '👎', '👊',
		'✌️', '🤝', '🙏', '💪', '👋', '🖐️', '✍️', '❤️', '🧡', '💛',
		'💚', '💙', '💜', '🖤', '💔', '💯', '💢', '💥', '💫', '💨',
		'🌹', '🌸', '🌻', '🌞', '🌙', '⭐', '🔥', '✨', '🎉', '🎂',
		'🍉', '🍓', '🍜', '☕', '🍺', '⚽', '🏀', '🎮', '🎵', '🚗'
	];

	var emojiPanel = null;
	var emojiBtn = null;
	var emojiInput = null;

	function buildEmojiPanel() {
		var panel = document.createElement('div');
		panel.className = 'glintide-note-emoji-panel';
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-label', '选择表情');

		EMOJIS.forEach(function (emoji) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'glintide-note-emoji-item';
			btn.textContent = emoji;
			// wp-emoji 会把 emoji 文本替换成 <img>,textContent 变空,存 dataset 保证点击能取到
			btn.dataset.emoji = emoji;
			btn.addEventListener('click', function () {
				insertEmoji(btn.dataset.emoji);
				closeEmojiPanel();
			});
			panel.appendChild(btn);
		});

		document.body.appendChild(panel);
		return panel;
	}

	function insertEmoji(emoji) {
		var input = emojiInput;
		if (!input || !emoji) {
			return;
		}
		var start = typeof input.selectionStart === 'number' ? input.selectionStart : input.value.length;
		var end = typeof input.selectionEnd === 'number' ? input.selectionEnd : start;
		input.value = input.value.slice(0, start) + emoji + input.value.slice(end);
		var pos = start + emoji.length;
		input.setSelectionRange(pos, pos);
		input.focus();
	}

	function closeEmojiPanel() {
		if (emojiPanel) {
			emojiPanel.remove();
			emojiPanel = null;
		}
		if (emojiBtn) {
			emojiBtn.classList.remove('is-open');
		}
		emojiBtn = null;
		emojiInput = null;
	}

	function toggleEmojiPanel(btn, input) {
		if (emojiPanel && emojiBtn === btn) {
			closeEmojiPanel();
			return;
		}
		closeEmojiPanel();
		emojiBtn = btn;
		emojiInput = input;
		emojiPanel = buildEmojiPanel();

		// 定位:贴在触发按钮上方,水平方向收进视口内
		var rect = btn.getBoundingClientRect();
		var width = 272;
		var left = Math.min(Math.max(8, rect.right - width), window.innerWidth - width - 8);
		emojiPanel.style.left = left + 'px';
		emojiPanel.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
		emojiPanel.classList.add('is-open');
		btn.classList.add('is-open');
	}

	document.addEventListener('click', function (event) {
		var toggle = event.target.closest('[data-glintide-emoji-toggle]');
		if (toggle) {
			var form = toggle.closest('.glintide-note-comment-form');
			var input = form ? form.querySelector('input') : null;
			if (input) {
				toggleEmojiPanel(toggle, input);
			}
			return;
		}
		if (emojiPanel && !event.target.closest('.glintide-note-emoji-panel')) {
			closeEmojiPanel();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && emojiPanel) {
			closeEmojiPanel();
			event.stopPropagation();
		}
	}, true);

	document.addEventListener('scroll', function (event) {
		// 面板自身滚动不关闭
		if (emojiPanel && !(event.target && event.target.classList && event.target.classList.contains('glintide-note-emoji-panel'))) {
			closeEmojiPanel();
		}
	}, true);

	function renderNote(data) {
		var comments = data.comments || [];

		CURRENT = {
			postId: data.post_id,
			nonce: data.comment_nonce,
			author: data.author,
			liked: !!data.liked,
			likes: data.likes || 0,
			comments: comments.length,
			replyTo: null
		};

		var commentsHtml = comments.length
			? buildCommentSequence(comments).map(function (entry) { return commentHtml(entry.item, data.author, entry.isReply); }).join('')
			: '<li class="glintide-note-comments-empty">还没有评论,来抢沙发吧~</li>';

		var html =
			'<div class="glintide-note-layout">' +
			'<div class="glintide-note-main">' +
			'<h2 class="glintide-note-title">' + escText(data.title) + '</h2>' +
			'<div class="glintide-note-content">' + (data.content_html || '') + '</div>' +
			'</div>' +
			'<aside class="glintide-note-side">' +
			'<div class="glintide-note-side-top">' +
			'<div class="glintide-note-author">' +
			'<img class="glintide-note-author-avatar" src="' + escText(data.avatar) + '" alt="">' +
			'<div class="glintide-note-author-info">' +
			'<span class="glintide-note-author-name">' + escText(data.author) + '</span>' +
			'<span class="glintide-note-date">' + escText(data.date) + '</span>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'<div class="glintide-note-divider">共 <span data-glintide-note-comment-count>' + comments.length + '</span> 条评论</div>' +
			'<ul class="glintide-note-comments" data-glintide-note-list>' + commentsHtml + '</ul>' +
			'<div class="glintide-note-comment-bar" data-glintide-comment-bar></div>' +
			'</aside>' +
			'</div>';

		ensureMask().querySelector('.glintide-modal-inner').innerHTML = html;
		renderCommentBar();
		openMask();

		var dialog = MASK.querySelector('.glintide-modal');
		if (dialog) {
			dialog.focus();
		}
	}

	/* ---------- 底部操作栏两态:收起(胶囊入口+图标) / 展开(输入框+发送取消) ---------- */

	function renderCommentBar() {
		closeEmojiPanel();
		var bar = MASK.querySelector('[data-glintide-comment-bar]');
		if (!bar) {
			return;
		}
		var likeClass = CURRENT.liked ? ' is-liked' : '';
		var likeIcon = CURRENT.liked ? 'ri-heart-3-fill' : 'ri-heart-3-line';
		var avatar = window.glintide_card_ajax && window.glintide_card_ajax.defaultAvatar
			? '<img src="' + escText(window.glintide_card_ajax.defaultAvatar) + '" alt="">'
			: '';

		bar.innerHTML =
			'<button type="button" class="glintide-note-bar-pill" data-glintide-bar-open>' +
			avatar +
			'<span>说点什么…</span>' +
			'</button>' +
			'<button type="button" class="glintide-note-bar-btn' + likeClass + '" data-glintide-note-like aria-label="点赞">' +
			'<i class="' + likeIcon + '" aria-hidden="true"></i>' +
			'<span data-glintide-note-like-count>' + CURRENT.likes + '</span>' +
			'</button>' +
			'<button type="button" class="glintide-note-bar-btn" data-glintide-bar-open aria-label="评论">' +
			'<i class="ri-chat-3-line" aria-hidden="true"></i>' +
			'<span data-glintide-note-bar-count>' + CURRENT.comments + '</span>' +
			'</button>';
	}

	function openComposer() {
		closeEmojiPanel();
		var bar = MASK.querySelector('[data-glintide-comment-bar]');
		if (!bar) {
			return;
		}
		bar.innerHTML =
			'<div class="glintide-note-comment-form glintide-note-composer">' +
			'<input type="text" class="glintide-note-comment-input" data-glintide-note-input placeholder="说点什么…" maxlength="1000">' +
			'<div class="glintide-note-composer-foot">' +
			'<button type="button" class="glintide-note-emoji-btn" data-glintide-emoji-toggle aria-label="插入表情">' +
			'<i class="ri-emotion-happy-line" aria-hidden="true"></i>' +
			'</button>' +
			'<span class="glintide-note-composer-spacer"></span>' +
			'<button type="button" class="glintide-note-send" data-glintide-note-send>发送</button>' +
			'<button type="button" class="glintide-note-cancel" data-glintide-composer-cancel>取消</button>' +
			'</div>' +
			'</div>';

		var input = bar.querySelector('[data-glintide-note-input]');
		if (input) {
			input.focus();
		}
	}

	// 底部操作栏点赞:切换后以服务端返回为准
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
				btn.setAttribute('aria-pressed', CURRENT.liked ? 'true' : 'false');
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

		var replyTo = CURRENT.replyTo || null;
		var body = new URLSearchParams({
			action: 'glintide_card_comment',
			post_id: CURRENT.postId,
			comment: content,
			nonce: CURRENT.nonce
		});
		if (replyTo) {
			body.set('parent', replyTo.id);
		}

		fetch(ajaxUrl(), { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) { return res.json(); })
			.then(function (res) {
				delete input.dataset.glintidePosting;
				if (!res || !res.success) {
					showCommentTip(input.parentElement, (res && res.data && res.data.msg) || '评论失败');
					return;
				}
				var comment = res.data.comment;
				var isReply = !!(replyTo && comment.parent);
				var list = MASK.querySelector('[data-glintide-note-list]');
				if (list) {
					var empty = list.querySelector('.glintide-note-comments-empty');
					if (empty) {
						empty.remove();
					}
					var html = commentHtml(comment, CURRENT.author, isReply);
					var anchor = replyTo ? list.querySelector('[data-comment-id="' + replyTo.id + '"]') : null;
					if (anchor) {
						// 插到目标评论所在回复串的末尾
						var next = anchor.nextElementSibling;
						while (next && next.classList.contains('glintide-note-comment--reply')) {
							anchor = next;
							next = next.nextElementSibling;
						}
						anchor.insertAdjacentHTML('afterend', html);
					} else {
						list.insertAdjacentHTML('beforeend', html);
					}
					list.scrollTop = list.scrollHeight;
				}
				var count = MASK.querySelector('[data-glintide-note-comment-count]');
				if (count) {
					count.textContent = res.data.count;
				}
				CURRENT.comments = res.data.count;
				CURRENT.replyTo = null;
				input.value = '';
				renderCommentBar();
			})
			.catch(function () {
				delete input.dataset.glintidePosting;
				showCommentTip(input.parentElement, '网络异常,请重试');
			});
	}

	function showCommentTip(form, message) {
		var tip = form.querySelector('.glintide-note-comment-tip');
		if (!tip) {
			tip = document.createElement('span');
			tip.className = 'glintide-note-comment-tip';
			form.insertBefore(tip, form.firstChild);
		}
		tip.textContent = message;
		setTimeout(function () { tip.remove(); }, 3000);
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
				try {
					renderNote(res.data);
				} catch (e) {
					window.__renderErr = String((e && e.message) || e);
					renderError();
				}
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

	// 独立页评论区:复用卡片评论接口,提交后原地追加
	function submitSingleComment(section) {
		if (section.dataset.glintidePosting) {
			return;
		}
		var input = section.querySelector('[data-glintide-single-input]');
		var content = input ? input.value.trim() : '';
		if (!content) {
			return;
		}
		section.dataset.glintidePosting = '1';

		var form = section.querySelector('.glintide-note-comment-form');
		var parentId = parseInt(section.getAttribute('data-glintide-reply-id'), 10) || 0;
		var body = new URLSearchParams({
			action: 'glintide_card_comment',
			post_id: section.getAttribute('data-glintide-post-id'),
			comment: content,
			nonce: section.getAttribute('data-glintide-comment-nonce')
		});
		if (parentId) {
			body.set('parent', parentId);
		}

		fetch(ajaxUrl(), { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) { return res.json(); })
			.then(function (res) {
				delete section.dataset.glintidePosting;
				if (!res || !res.success) {
					showCommentTip(form, (res && res.data && res.data.msg) || '评论失败');
					return;
				}
				var list = section.querySelector('[data-glintide-single-list]');
				if (list) {
					var empty = list.querySelector('.glintide-note-comments-empty');
					if (empty) {
						empty.remove();
					}
					var html = commentHtml(res.data.comment, null, !!parentId);
					var anchor = parentId ? list.querySelector('[data-comment-id="' + parentId + '"]') : null;
					if (anchor) {
						var next = anchor.nextElementSibling;
						while (next && next.classList.contains('glintide-note-comment--reply')) {
							anchor = next;
							next = next.nextElementSibling;
						}
						anchor.insertAdjacentHTML('afterend', html);
					} else {
						list.insertAdjacentHTML('beforeend', html);
					}
					list.scrollTop = list.scrollHeight;
				}
				if (input) {
					input.value = '';
					input.placeholder = '说点什么…';
				}
				section.removeAttribute('data-glintide-reply-id');
				section.removeAttribute('data-glintide-reply-author');
				var hint = section.querySelector('.glintide-single-reply-hint');
				if (hint) {
					hint.remove();
				}
			})
			.catch(function () {
				delete section.dataset.glintidePosting;
				showCommentTip(form, '网络异常,请重试');
			});
	}

	document.addEventListener('click', function (event) {
		var send = event.target.closest('[data-glintide-single-send]');
		if (!send) {
			return;
		}
		var section = send.closest('[data-glintide-single-comments]');
		if (section) {
			submitSingleComment(section);
		}
	});

	// 独立页:点某条评论的"回复",输入框进入回复态
	document.addEventListener('click', function (event) {
		var replyBtn = event.target.closest('.glintide-note-comment-reply-btn');
		if (!replyBtn) {
			return;
		}
		var section = replyBtn.closest('[data-glintide-single-comments]');
		if (!section) {
			return;
		}
		section.setAttribute('data-glintide-reply-id', replyBtn.getAttribute('data-comment-id'));
		section.setAttribute('data-glintide-reply-author', replyBtn.getAttribute('data-comment-author'));

		var input = section.querySelector('[data-glintide-single-input]');
		if (input) {
			input.placeholder = '回复 @' + replyBtn.getAttribute('data-comment-author') + '…';
			input.focus();
		}

		var hint = section.querySelector('.glintide-single-reply-hint');
		if (!hint) {
			hint = document.createElement('div');
			hint.className = 'glintide-single-reply-hint';
			section.insertBefore(hint, section.querySelector('.glintide-note-comment-form'));
		}
		hint.innerHTML = '回复 @' + escText(replyBtn.getAttribute('data-comment-author')) +
			' <button type="button" class="glintide-single-reply-hint-cancel">取消回复</button>';
		hint.querySelector('.glintide-single-reply-hint-cancel').addEventListener('click', function () {
			section.removeAttribute('data-glintide-reply-id');
			section.removeAttribute('data-glintide-reply-author');
			hint.remove();
			if (input) {
				input.placeholder = '说点什么…';
			}
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Enter' || !event.target.matches || !event.target.matches('[data-glintide-single-input]')) {
			return;
		}
		event.preventDefault();
		var section = event.target.closest('[data-glintide-single-comments]');
		if (section) {
			submitSingleComment(section);
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeModal();
		}
	});
}());
