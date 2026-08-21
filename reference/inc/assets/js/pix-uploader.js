(function(window, document, $) {
    'use strict';

    function pixToast(message, type) {
        if (typeof toastfy === 'function') {
            toastfy(message, type || 'info');
            return;
        }
        if (window.console) {
            console.log(message);
        }
    }

    function uid() {
        return 'pix_' + Math.random().toString(16).slice(2) + Date.now().toString(16);
    }

    function fileKind(file) {
        if (!file || !file.type) return 'file';
        if (file.type.indexOf('image/') === 0) return 'image';
        if (file.type.indexOf('video/') === 0) return 'video';
        if (file.type.indexOf('audio/') === 0) return 'audio';
        return 'file';
    }

    function formatSize(bytes) {
        bytes = Number(bytes || 0);
        if (!bytes) return '';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        if (bytes < 1024 * 1024 * 1024) return (bytes / 1024 / 1024).toFixed(1) + ' MB';
        return (bytes / 1024 / 1024 / 1024).toFixed(1) + ' GB';
    }

    function kindLabel(kind) {
        if (kind === 'image') return '图片';
        if (kind === 'video') return '视频';
        if (kind === 'audio') return '音频';
        return '文件';
    }

    function previewIcon(item) {
        var type = item && (item.kind || item.type);
        if (type === 'video') return 'ri-play-fill';
        if (type === 'image') return 'ri-eye-line';
        return 'ri-search-eye-line';
    }

    function previewText(item) {
        var type = item && (item.kind || item.type);
        if (type === 'video') return '预览视频';
        if (type === 'image') return '预览图片';
        return '预览文件';
    }

    function normalizeBvid(value) {
        var match = String(value || '').trim().match(/BV[0-9A-Za-z]{8,20}/);
        return match ? match[0] : '';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/`/g, '&#096;');
    }

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return String(value).replace(/["\\]/g, '\\$&');
    }

    function readAsDataURL(file) {
        return new Promise(function(resolve, reject) {
            var reader = new FileReader();
            reader.onload = function() { resolve(reader.result); };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    function canvasToBlob(canvas, mime, quality) {
        return new Promise(function(resolve) {
            canvas.toBlob(resolve, mime, quality);
        });
    }

    async function compressImage(file, options) {
        if (!options.compress && !options.convertWebp) return file;
        if (fileKind(file) !== 'image' || file.type === 'image/gif') return file;

        var dataUrl = await readAsDataURL(file);
        var img = await new Promise(function(resolve, reject) {
            var image = new Image();
            image.onload = function() { resolve(image); };
            image.onerror = reject;
            image.src = dataUrl;
        });

        var maxWidth = options.maxWidth || 1920;
        var ratio = Math.min(1, maxWidth / img.width);
        var width = Math.round(img.width * ratio);
        var height = Math.round(img.height * ratio);
        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        canvas.getContext('2d').drawImage(img, 0, 0, width, height);

        var mime = options.convertWebp ? 'image/webp' : (file.type || 'image/jpeg');
        var blob = await canvasToBlob(canvas, mime, options.quality || 0.86);
        if (!blob) return file;

        var originalExt = file.name.indexOf('.') > -1 ? file.name.substring(file.name.lastIndexOf('.')) : '';
        var name = mime === 'image/webp' ? file.name.replace(/\.[^.]+$/, '') + '.webp' : file.name;
        if (!originalExt && mime === 'image/jpeg') {
            name += '.jpg';
        }
        if (!originalExt && mime === 'image/png') {
            name += '.png';
        }
        return new File([blob], name || file.name, { type: mime, lastModified: Date.now() });
    }

    function videoPoster(file, seekTime) {
        return new Promise(function(resolve) {
            var video = document.createElement('video');
            var url = URL.createObjectURL(file);
            video.preload = 'metadata';
            video.muted = true;
            video.playsInline = true;
            video.src = url;

            video.onloadedmetadata = function() {
                video.currentTime = Math.min(seekTime || 0.8, Math.max(0, video.duration - 0.1));
            };

            video.onseeked = function() {
                var canvas = document.createElement('canvas');
                canvas.width = video.videoWidth || 1280;
                canvas.height = video.videoHeight || 720;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function(blob) {
                    URL.revokeObjectURL(url);
                    if (!blob) {
                        resolve(null);
                        return;
                    }
                    resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '-poster.jpg', {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    }));
                }, 'image/jpeg', 0.84);
            };

            video.onerror = function() {
                URL.revokeObjectURL(url);
                resolve(null);
            };
        });
    }

    class PixEditor {
        constructor(el, options) {
            this.el = typeof el === 'string' ? document.querySelector(el) : el;
            this.options = Object.assign({
                input: null,
                placeholder: '写点什么...',
                onChange: null
            }, options || {});

            if (!this.el) return;
            this.input = typeof this.options.input === 'string' ? document.querySelector(this.options.input) : this.options.input;
            this.suggestTimer = null;
            this.suggestKeyword = '';
            this.savedRange = null;
            this.toolbarTimer = null;
            this.isSelecting = false;
            this.colorPalette = ['default', 'gray', 'blue', 'sky', 'green', 'yellow', 'orange', 'red', 'pink', 'purple'];
            this.bgPalette = ['default', 'gray', 'blue', 'sky', 'green', 'yellow', 'orange', 'red', 'pink', 'purple'];
            this.styleColors = {
                gray: '#697089',
                blue: getComputedStyle(document.documentElement).getPropertyValue('--color-pix-primary').trim() || '#3157ff',
                sky: '#008ecf',
                green: '#1f9a62',
                yellow: '#b88600',
                orange: '#d66812',
                red: '#dc3545',
                pink: '#d63384',
                purple: '#7b3fe4'
            };
            this.styleBackgrounds = {
                gray: '#eef0f5',
                blue: '#e8edff',
                sky: '#e3f5ff',
                green: '#e7f7ee',
                yellow: '#fff5cf',
                orange: '#fff0df',
                red: '#ffe8eb',
                pink: '#ffeaf5',
                purple: '#f1e8ff'
            };
            this.init();
        }

        init() {
            this.el.setAttribute('contenteditable', 'true');
            this.el.setAttribute('data-placeholder', this.options.placeholder);
            this.el.classList.add('pix-editor');
            this.createSuggest();
            this.createToolbar();

            var self = this;
            var sync = function() {
                self.sync();
                self.handleSuggest();
            };
            this.el.addEventListener('input', sync);
            this.el.addEventListener('keyup', function(event) {
                if (event.key === 'Escape') {
                    self.hideSuggest();
                    return;
                }
                self.handleSuggest();
                self.updateToolbar();
            });
            this.el.addEventListener('blur', sync);
            this.el.addEventListener('paste', this.onPaste.bind(this));
            this.el.addEventListener('mouseup', function() {
                self.isSelecting = false;
                self.scheduleToolbarUpdate(160);
            });
            this.el.addEventListener('mousedown', function() {
                self.isSelecting = true;
                self.hideToolbar();
            });
            this.el.addEventListener('focus', function() {
                self.scheduleToolbarUpdate(180);
            });
            this.suggest.addEventListener('mousedown', function(event) {
                event.preventDefault();
            });
            this.suggest.addEventListener('click', function(event) {
                var item = event.target.closest('.pix-editor-suggest-item');
                if (!item) return;
                self.insertTopic(item.dataset.name || '', item.dataset.id || '');
            });
            document.addEventListener('mousedown', function(event) {
                if (self.toolbar && self.toolbar.contains(event.target)) return;
                if (!self.suggest || self.suggest.contains(event.target) || self.el.contains(event.target)) return;
                self.hideSuggest();
                self.hideToolbar();
            });
            document.addEventListener('selectionchange', function() {
                self.scheduleToolbarUpdate(180);
            });
            sync();
        }

        createSuggest() {
            this.suggest = document.createElement('div');
            this.suggest.className = 'pix-editor-suggest is-hidden';
            this.suggest.innerHTML = '<div class="pix-editor-suggest-empty">输入 #话题 试试</div>';
            if (this.el.parentNode) {
                this.el.parentNode.appendChild(this.suggest);
            }
        }

        createToolbar() {
            this.toolbar = document.createElement('div');
            this.toolbar.className = 'pix-editor-toolbar is-hidden';
            this.toolbar.innerHTML = '' +
                '<div class="pix-editor-toolbar-main">' +
                    '<button type="button" data-cmd="bold" title="加粗"><strong>B</strong></button>' +
                    '<button type="button" data-cmd="underline" title="下划线"><u>U</u></button>' +
                    '<button type="button" data-cmd="strikeThrough" title="删除线"><s>S</s></button>' +
                    '<button type="button" data-action="link" title="链接"><i class="ri-link"></i></button>' +
                    '<button type="button" data-action="clear" title="清除格式"><i class="ri-eraser-line"></i></button>' +
                '</div>' +
                '<div class="pix-editor-popover pix-editor-link-popover is-hidden" data-popover="link">' +
                    '<input type="url" placeholder="https://example.com">' +
                    '<button type="button" data-action="apply-link">确定</button>' +
                '</div>';

            if (this.el.parentNode) {
                this.el.parentNode.appendChild(this.toolbar);
            }

            this.toolbar.addEventListener('mousedown', function(event) {
                if (event.target.closest('input, textarea')) return;
                event.preventDefault();
            });
            this.toolbar.addEventListener('click', this.onToolbarClick.bind(this));
        }

        renderStylePopover() {
            var popover = this.toolbar.querySelector('[data-popover="style"]');
            if (!popover) return;
            popover.innerHTML = '' +
                '<span>文字颜色</span><div class="pix-editor-color-grid">' +
                    this.colorPalette.map(function(color) {
                        return '<button type="button" class="pix-editor-color-swatch rt-color-' + color + '" data-action="color" data-value="' + color + '"></button>';
                    }).join('') +
                '</div>' +
                '<span>背景颜色</span><div class="pix-editor-color-grid">' +
                    this.bgPalette.map(function(color) {
                        return '<button type="button" class="pix-editor-color-swatch rt-bg-' + color + '" data-action="bg" data-value="' + color + '"></button>';
                    }).join('') +
                '</div>' +
                '<button type="button" class="pix-editor-style-reset" data-action="clear-style">恢复默认</button>';
        }

        getSelectionRange() {
            var selection = window.getSelection ? window.getSelection() : null;
            if (!selection || !selection.rangeCount) return null;
            var range = selection.getRangeAt(0);
            if (!this.el.contains(range.commonAncestorContainer)) return null;
            return range;
        }

        hasSelection() {
            var range = this.getSelectionRange();
            return !!range && !range.collapsed;
        }

        saveSelection() {
            var range = this.getSelectionRange();
            if (range) this.savedRange = range.cloneRange();
        }

        restoreSelection() {
            if (!this.savedRange) return false;
            var selection = window.getSelection ? window.getSelection() : null;
            if (!selection) return false;
            selection.removeAllRanges();
            selection.addRange(this.savedRange);
            return true;
        }

        updateToolbar() {
            if (!this.toolbar || document.activeElement && this.toolbar.contains(document.activeElement)) return;
            if (this.isSelecting) return;
            var range = this.getSelectionRange();
            if (!range || range.collapsed) {
                this.hideToolbar();
                return;
            }
            this.savedRange = range.cloneRange();
            var rect = range.getBoundingClientRect();
            var parentRect = this.el.parentNode.getBoundingClientRect();
            this.toolbar.style.left = Math.max(8, rect.left - parentRect.left + rect.width / 2) + 'px';
            this.toolbar.style.top = (rect.bottom - parentRect.top + 12) + 'px';
            this.toolbar.classList.remove('is-hidden');
            this.updateToolbarState();
        }

        scheduleToolbarUpdate(delay) {
            clearTimeout(this.toolbarTimer);
            this.toolbarTimer = setTimeout(function() {
                this.updateToolbar();
            }.bind(this), delay || 120);
        }

        hideToolbar() {
            if (!this.toolbar) return;
            clearTimeout(this.toolbarTimer);
            this.toolbar.classList.add('is-hidden');
            this.closeToolbarPopovers();
        }

        closeToolbarPopovers(except) {
            if (!this.toolbar) return;
            Array.prototype.slice.call(this.toolbar.querySelectorAll('.pix-editor-popover')).forEach(function(node) {
                if (except && node.dataset.popover === except) return;
                node.classList.add('is-hidden');
            });
        }

        onToolbarClick(event) {
            var button = event.target.closest('button');
            if (!button) return;
            var cmd = button.dataset.cmd;
            var action = button.dataset.action;
            var panel = button.dataset.panel;

            if (panel) {
                this.saveSelection();
                this.closeToolbarPopovers(panel);
                var popover = this.toolbar.querySelector('[data-popover="' + panel + '"]');
                if (popover) popover.classList.toggle('is-hidden');
                this.updateToolbarState();
                return;
            }

            if (cmd) {
                this.applyCommand(cmd);
                return;
            }

            if (action === 'inline-code') {
                this.toggleClassSpan('rt-inline-code');
                return;
            }

            if (action === 'clear') {
                this.clearSelectionFormatting();
                return;
            }

            if (action === 'link') {
                this.saveSelection();
                this.closeToolbarPopovers('link');
                var linkPopover = this.toolbar.querySelector('[data-popover="link"]');
                if (linkPopover) {
                    linkPopover.classList.toggle('is-hidden');
                    var input = linkPopover.querySelector('input');
                    if (input) {
                        input.value = this.getSelectionLinkUrl() || '';
                        input.focus();
                        input.select();
                    }
                }
                this.updateToolbarState();
                return;
            }

            if (action === 'apply-link') {
                var urlInput = this.toolbar.querySelector('[data-popover="link"] input');
                this.applySelectionLink(urlInput ? urlInput.value : '');
                return;
            }

            if (action === 'clear-style') {
                this.applyClassSpan('', ['rt-color-', 'rt-bg-']);
                this.closeToolbarPopovers();
                this.updateToolbarState();
                return;
            }

            if (action === 'color' || action === 'bg') {
                var value = button.dataset.value || 'default';
                if (value === 'default') {
                    this.applyClassSpan('', action === 'color' ? 'rt-color-' : 'rt-bg-');
                    this.updateToolbarState();
                    return;
                }
                this.applyClassSpan('rt-' + action + '-' + value, action === 'color' ? 'rt-color-' : 'rt-bg-');
            }
        }

        applyCommand(cmd) {
            this.restoreSelection();
            document.execCommand(cmd, false, null);
            this.sync();
            this.updateToolbar();
        }

        selectionHasClass(className) {
            var range = this.getSelectionRange() || this.savedRange;
            if (!range) return false;
            var node = range.commonAncestorContainer;
            if (node.nodeType !== 1) node = node.parentElement;
            while (node && node !== this.el) {
                if (node.classList && node.classList.contains(className)) return true;
                node = node.parentElement;
            }
            return false;
        }

        getSelectionClassValue(prefix) {
            var range = this.getSelectionRange() || this.savedRange;
            if (!range) return '';
            var nodes = this.collectSelectedElements(range);
            var found = '';
            nodes.some(function(node) {
                if (!node.classList) return false;
                return Array.prototype.slice.call(node.classList).some(function(className) {
                    if (className.indexOf(prefix) !== 0) return false;
                    found = className.slice(prefix.length);
                    return true;
                });
            });
            return found;
        }

        getSelectionLinkUrl() {
            var range = this.getSelectionRange() || this.savedRange;
            if (!range) return '';
            var node = range.commonAncestorContainer;
            if (node.nodeType !== 1) node = node.parentElement;
            while (node && node !== this.el) {
                if (node.nodeName === 'A' && node.getAttribute('href')) return node.getAttribute('href');
                node = node.parentElement;
            }
            var nodes = this.collectSelectedElements(range);
            var link = nodes.find(function(item) {
                return item.nodeName === 'A' && item.getAttribute('href');
            });
            return link ? link.getAttribute('href') : '';
        }

        updateToolbarState() {
            if (!this.toolbar) return;
            var codeButton = this.toolbar.querySelector('[data-action="inline-code"]');
            if (codeButton) {
                codeButton.classList.toggle('is-active', this.selectionHasClass('rt-inline-code'));
            }
            var linkButton = this.toolbar.querySelector('[data-action="link"]');
            if (linkButton) {
                linkButton.classList.toggle('is-active', !!this.getSelectionLinkUrl());
            }
            var colorValue = this.getSelectionClassValue('rt-color-');
            var bgValue = this.getSelectionClassValue('rt-bg-');
            var styleButton = this.toolbar.querySelector('[data-panel="style"]');
            if (styleButton) {
                styleButton.classList.toggle('has-style', !!(colorValue || bgValue));
                styleButton.style.setProperty('--rt-current-color', colorValue && this.styleColors[colorValue] ? this.styleColors[colorValue] : '#24284d');
                styleButton.style.setProperty('--rt-current-bg', bgValue && this.styleBackgrounds[bgValue] ? this.styleBackgrounds[bgValue] : '#f0f2ff');
            }
            Array.prototype.slice.call(this.toolbar.querySelectorAll('[data-action="color"]')).forEach(function(node) {
                node.classList.toggle('is-active', (node.dataset.value || '') === (colorValue || 'default'));
            });
            Array.prototype.slice.call(this.toolbar.querySelectorAll('[data-action="bg"]')).forEach(function(node) {
                node.classList.toggle('is-active', (node.dataset.value || '') === (bgValue || 'default'));
            });
        }

        unwrapNode(node) {
            if (!node || !node.parentNode) return;
            while (node.firstChild) {
                node.parentNode.insertBefore(node.firstChild, node);
            }
            node.remove();
        }

        collectSelectedElements(range) {
            var root = range.commonAncestorContainer;
            if (root.nodeType !== 1) root = root.parentElement;
            var nodes = [];
            var ancestor = root;
            while (ancestor && ancestor !== this.el) {
                if (ancestor.nodeType === 1) nodes.push(ancestor);
                ancestor = ancestor.parentElement;
            }
            if (root && root.nodeType === 1 && range.intersectsNode(root)) {
                nodes.push(root);
            }
            Array.prototype.slice.call((root || this.el).querySelectorAll('*')).forEach(function(node) {
                if (range.intersectsNode(node)) nodes.push(node);
            });
            return nodes.filter(function(node, index) {
                return nodes.indexOf(node) === index;
            });
        }

        toggleClassSpan(className) {
            if (this.selectionHasClass(className)) {
                this.applyClassSpan('', [className]);
                return;
            }
            this.applyClassSpan(className);
        }

        applyClassSpan(className, removePrefix) {
            if (!className && !removePrefix) return;
            this.restoreSelection();
            var range = this.getSelectionRange();
            if (!range || range.collapsed) return;
            var wrapperClass = this.buildRichTextClassList(className, removePrefix);
            var text = range.extractContents();
            var span = document.createElement('span');
            this.stripRichTextClasses(text, ['rt-color-', 'rt-bg-', 'rt-inline-code']);
            if (wrapperClass) span.className = wrapperClass;
            span.appendChild(text);
            range.insertNode(span);
            this.liftNodeOutOfRichAncestors(span);
            range.selectNodeContents(span);
            var selection = window.getSelection ? window.getSelection() : null;
            if (selection) {
                selection.removeAllRanges();
                selection.addRange(range);
            }
            this.normalizeRichTextSpans(removePrefix);
            this.cleanupEmptyFormattingNodes();
            this.sync();
            this.updateToolbar();
        }

        buildRichTextClassList(className, removePrefix) {
            var removeList = Array.isArray(removePrefix) ? removePrefix : (removePrefix ? [removePrefix] : []);
            var classes = [];
            var colorValue = this.getSelectionClassValue('rt-color-');
            var bgValue = this.getSelectionClassValue('rt-bg-');

            if (colorValue && !this.richClassMatchesRemove('rt-color-' + colorValue, removeList)) {
                classes.push('rt-color-' + colorValue);
            }
            if (bgValue && !this.richClassMatchesRemove('rt-bg-' + bgValue, removeList)) {
                classes.push('rt-bg-' + bgValue);
            }
            if (this.selectionHasClass('rt-inline-code') && !this.richClassMatchesRemove('rt-inline-code', removeList)) {
                classes.push('rt-inline-code');
            }

            String(className || '').split(/\s+/).filter(Boolean).forEach(function(item) {
                classes = classes.filter(function(existing) {
                    if (item.indexOf('rt-color-') === 0) return existing.indexOf('rt-color-') !== 0;
                    if (item.indexOf('rt-bg-') === 0) return existing.indexOf('rt-bg-') !== 0;
                    if (item === 'rt-inline-code') return existing !== 'rt-inline-code';
                    return true;
                });
                classes.push(item);
            });

            return classes.filter(function(item, index) {
                return classes.indexOf(item) === index;
            }).join(' ');
        }

        richClassMatchesRemove(className, removeList) {
            return removeList.some(function(item) {
                return item.slice(-1) === '-' ? className.indexOf(item) === 0 : className === item;
            });
        }

        stripRichTextClasses(root, removePrefix) {
            if (!removePrefix) return;
            var prefixes = Array.isArray(removePrefix) ? removePrefix : [removePrefix];
            var nodes = [];
            if (root.nodeType === 1) nodes.push(root);
            if (root.querySelectorAll) {
                nodes = nodes.concat(Array.prototype.slice.call(root.querySelectorAll('*')));
            }
            nodes.forEach(function(node) {
                if (!node.classList) return;
                Array.prototype.slice.call(node.classList).forEach(function(className) {
                    prefixes.some(function(prefix) {
                        if (prefix.slice(-1) === '-' ? className.indexOf(prefix) !== 0 : className !== prefix) return false;
                        node.classList.remove(className);
                        return true;
                    });
                });
            });
        }

        nodeHasRichTextClass(node) {
            return !!(node && node.classList && Array.prototype.slice.call(node.classList).some(function(className) {
                return className.indexOf('rt-color-') === 0 || className.indexOf('rt-bg-') === 0 || className === 'rt-inline-code';
            }));
        }

        liftNodeOutOfRichAncestors(node) {
            var target = node;
            while (target && target.parentNode && target.parentNode !== this.el) {
                var ancestor = target.parentElement;
                while (ancestor && ancestor !== this.el && !this.nodeHasRichTextClass(ancestor)) {
                    ancestor = ancestor.parentElement;
                }
                if (!ancestor || ancestor === this.el) break;

                var direct = target;
                while (direct.parentNode !== ancestor) {
                    direct = direct.parentNode;
                }

                var parent = ancestor.parentNode;
                var before = ancestor.cloneNode(false);
                var after = ancestor.cloneNode(false);
                while (ancestor.firstChild && ancestor.firstChild !== direct) {
                    before.appendChild(ancestor.firstChild);
                }
                if (direct.parentNode === ancestor) {
                    ancestor.removeChild(direct);
                }
                while (ancestor.firstChild) {
                    after.appendChild(ancestor.firstChild);
                }
                if (before.childNodes.length) parent.insertBefore(before, ancestor);
                parent.insertBefore(direct, ancestor);
                if (after.childNodes.length) parent.insertBefore(after, ancestor);
                parent.removeChild(ancestor);
                target = node;
            }
        }

        clearSelectionClasses(pattern) {
            this.restoreSelection();
            var range = this.getSelectionRange();
            if (!range || range.collapsed) return;
            var nodes = this.collectSelectedElements(range);
            nodes.forEach(function(node) {
                if (!node.classList) return;
                Array.prototype.slice.call(node.classList).forEach(function(className) {
                    if (pattern.test(className)) node.classList.remove(className);
                });
            });
            this.cleanupEmptyFormattingNodes();
            this.sync();
            this.updateToolbar();
        }

        clearSelectionFormatting() {
            this.restoreSelection();
            document.execCommand('removeFormat', false, null);
            document.execCommand('unlink', false, null);
            this.saveSelection();
            this.applyClassSpan('', ['rt-color-', 'rt-bg-', 'rt-inline-code']);
        }

        cleanupEmptyFormattingNodes() {
            Array.prototype.slice.call(this.el.querySelectorAll('span')).forEach(function(node) {
                if (node.classList.contains('pix-editor-topic') || node.classList.contains('pix-editor-link')) return;
                if (node.className) return;
                if (!node.attributes.length) {
                    node.replaceWith.apply(node, Array.prototype.slice.call(node.childNodes));
                }
            });
            Array.prototype.slice.call(this.el.querySelectorAll('a')).forEach(function(node) {
                if (node.getAttribute('href') || node.classList.contains('pix-editor-link')) return;
                node.replaceWith.apply(node, Array.prototype.slice.call(node.childNodes));
            });
        }

        normalizeRichTextSpans(removePrefix) {
            if (!removePrefix) return;
            var prefixes = Array.isArray(removePrefix) ? removePrefix : [removePrefix];
            prefixes.forEach(function(prefix) {
                Array.prototype.slice.call(this.el.querySelectorAll('span[class*="' + prefix + '"]')).forEach(function(node) {
                    var classes = Array.prototype.slice.call(node.classList);
                    var seen = false;
                    classes.reverse().forEach(function(className) {
                        if (className.indexOf(prefix) !== 0) return;
                        if (!seen) {
                            seen = true;
                            return;
                        }
                        node.classList.remove(className);
                    });
                    if (!node.getAttribute('class')) {
                        node.replaceWith(document.createTextNode(node.textContent || ''));
                    }
                });
            }, this);
        }

        applySelectionLink(url) {
            url = String(url || '').trim();
            if (!/^https?:\/\//i.test(url)) {
                pixToast('链接必须为 http(s):// 开头', 'error');
                return;
            }
            this.restoreSelection();
            document.execCommand('createLink', false, url);
            Array.prototype.slice.call(this.el.querySelectorAll('a')).forEach(function(node) {
                if (node.getAttribute('href') !== url) return;
                node.setAttribute('target', '_blank');
                node.setAttribute('rel', 'nofollow noopener noreferrer');
                node.classList.add('mo-inner-link');
                if (!node.querySelector('i.ri-link')) {
                    var icon = document.createElement('i');
                    icon.className = 'ri-link';
                    node.insertBefore(icon, node.firstChild);
                    node.insertBefore(document.createTextNode(' '), icon.nextSibling);
                }
            });
            this.closeToolbarPopovers();
            this.sync();
            this.updateToolbar();
        }

        emojiSrc(code) {
            return window.Theme ? Theme.ppo_url + '/img/emoji/' + code + '.png' : '';
        }

        createEmojiNode(code) {
            var img = document.createElement('img');
            img.className = 'pix-editor-emoji wp-smiley';
            img.src = this.emojiSrc(code);
            img.alt = '[s=' + code + ']';
            img.setAttribute('data-emoji-code', code);
            return img;
        }

        hydrateEmojiCodes() {
            var self = this;
            var nodes = [];
            var walker = document.createTreeWalker(this.el, NodeFilter.SHOW_TEXT, {
                acceptNode: function(node) {
                    if (!node.nodeValue || (node.parentElement && node.parentElement.closest('.pix-editor-topic, .pix-editor-link'))) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return /\[s=([^\]\s]+)\]/.test(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                }
            });
            while (walker.nextNode()) {
                nodes.push(walker.currentNode);
            }

            nodes.forEach(function(node) {
                var text = node.nodeValue;
                var frag = document.createDocumentFragment();
                var lastIndex = 0;
                text.replace(/\[s=([^\]\s]+)\]/g, function(match, code, offset) {
                    if (offset > lastIndex) {
                        frag.appendChild(document.createTextNode(text.slice(lastIndex, offset)));
                    }
                    frag.appendChild(self.createEmojiNode(code));
                    lastIndex = offset + match.length;
                    return match;
                });
                if (lastIndex < text.length) {
                    frag.appendChild(document.createTextNode(text.slice(lastIndex)));
                }
                node.parentNode.replaceChild(frag, node);
            });
        }

        hydrateLinkTokens() {
            var self = this;
            var nodes = [];
            var walker = document.createTreeWalker(this.el, NodeFilter.SHOW_TEXT, {
                acceptNode: function(node) {
                    if (!node.nodeValue || (node.parentElement && node.parentElement.closest('.pix-editor-topic, .pix-editor-link'))) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return /\[link t="(.*?)" u="(.*?)"\]/.test(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                }
            });
            while (walker.nextNode()) {
                nodes.push(walker.currentNode);
            }

            nodes.forEach(function(node) {
                var text = node.nodeValue;
                var frag = document.createDocumentFragment();
                var lastIndex = 0;
                text.replace(/\[link t="(.*?)" u="(.*?)"\]/g, function(match, title, url, offset) {
                    if (offset > lastIndex) {
                        frag.appendChild(document.createTextNode(text.slice(lastIndex, offset)));
                    }
                    frag.appendChild(self.createLinkNode(title, url));
                    lastIndex = offset + match.length;
                    return match;
                });
                if (lastIndex < text.length) {
                    frag.appendChild(document.createTextNode(text.slice(lastIndex)));
                }
                node.parentNode.replaceChild(frag, node);
            });
        }

        createLinkNode(title, url) {
            title = String(title || '').trim();
            url = String(url || '').trim();
            var node = document.createElement('span');
            node.className = 'pix-editor-link';
            node.setAttribute('data-link-title', title || url);
            node.setAttribute('data-link-url', url);
            node.setAttribute('contenteditable', 'false');
            node.innerHTML = '<i class="ri-link"></i><span></span>';
            node.querySelector('span').textContent = title || url;
            return node;
        }

        ensureTopicToken(topic) {
            topic = topic || {};
            var name = String(topic.name || '').trim();
            var id = String(topic.id || '').trim();
            var tokens = Array.prototype.slice.call(this.el.querySelectorAll('.pix-editor-topic'));

            if (!name) {
                this.syncTopicUI('', '');
                return;
            }

            var token = tokens.shift();
            tokens.forEach(function(node) {
                node.remove();
            });

            if (!token) {
                token = document.createElement('span');
                token.className = 'pix-editor-topic';
                if (this.el.firstChild) {
                    this.el.insertBefore(document.createTextNode('\u00a0'), this.el.firstChild);
                    this.el.insertBefore(token, this.el.firstChild);
                } else {
                    this.el.appendChild(token);
                    this.el.appendChild(document.createTextNode('\u00a0'));
                }
            }

            token.setAttribute('data-tag-id', id);
            token.textContent = '#' + name;
            this.syncTopicUI(name, id);
        }

        setContent(html, topic) {
            this.el.innerHTML = html || '';
            this.hydrateLinkTokens();
            this.hydrateEmojiCodes();
            this.ensureTopicToken(topic || {});
            this.sync();
        }

        onPaste(event) {
            var text = (event.clipboardData || window.clipboardData).getData('text/plain');
            if (!text) return;
            event.preventDefault();
            document.execCommand('insertText', false, text);
            this.sync();
        }

        sync() {
            this.syncTopicFromEditor();
            var html = this.serializeContent();
            if (this.input) {
                this.input.value = html;
                this.input.dispatchEvent(new Event('input', { bubbles: true }));
                this.input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            this.syncCount();
            if (typeof this.options.onChange === 'function') {
                this.options.onChange(html, this);
            }
        }

        serializeContent() {
            var clone = this.el.cloneNode(true);
            Array.prototype.slice.call(clone.querySelectorAll('.pix-editor-emoji')).forEach(function(node) {
                var code = node.getAttribute('data-emoji-code') || '';
                node.replaceWith(document.createTextNode(code ? '[s=' + code + ']' : ''));
            });
            Array.prototype.slice.call(clone.querySelectorAll('.pix-editor-link')).forEach(function(node) {
                var title = node.getAttribute('data-link-title') || '';
                var url = node.getAttribute('data-link-url') || '';
                node.replaceWith(document.createTextNode(url ? '[link t="' + title.replace(/"/g, '&quot;') + '" u="' + url.replace(/"/g, '&quot;') + '"]' : ''));
            });
            Array.prototype.slice.call(clone.querySelectorAll('*')).forEach(function(node) {
                if (!node.classList) return;
                Array.prototype.slice.call(node.classList).forEach(function(className) {
                    if (/^rt-(color|bg)-/.test(className) || className === 'rt-inline-code') {
                        node.classList.remove(className);
                    }
                });
                if (node.nodeName === 'SPAN' && !node.getAttribute('class') && !node.attributes.length) {
                    node.replaceWith.apply(node, Array.prototype.slice.call(node.childNodes));
                }
            });
            return clone.innerHTML.trim();
        }

        syncCount() {
            if (!window.jQuery) return;
            var text = (this.el.textContent || '').replace(/\u00a0/g, '').trim();
            var emojiCount = this.el.querySelectorAll('.pix-editor-emoji').length;
            $('.mo-num').text(text.length + emojiCount);
        }

        syncTopicFromEditor() {
            var topic = this.el.querySelector('.pix-editor-topic');
            if (topic) {
                this.syncTopicUI(topic.textContent.replace(/^#/, '').trim(), topic.getAttribute('data-tag-id') || '');
                return;
            }
            if (window.jQuery && $('.push-mo-btn').attr('tagid')) {
                this.syncTopicUI('', '');
            }
        }

        currentTopicTrigger() {
            var selection = window.getSelection ? window.getSelection() : null;
            if (!selection || !selection.rangeCount) return null;
            var range = selection.getRangeAt(0);
            if (!range.collapsed || !this.el.contains(range.endContainer)) return null;

            var before = range.cloneRange();
            before.selectNodeContents(this.el);
            before.setEnd(range.endContainer, range.endOffset);
            var text = before.toString();
            var match = text.match(/(?:^|\s)#([^\s#@]{1,24})$/);
            return match ? {
                keyword: match[1],
                range: range.cloneRange()
            } : null;
        }

        handleSuggest() {
            var trigger = this.currentTopicTrigger();
            if (!trigger || !trigger.keyword) {
                this.hideSuggest();
                return;
            }
            if (trigger.keyword === this.suggestKeyword && !this.suggest.classList.contains('is-hidden')) {
                return;
            }
            this.suggestKeyword = trigger.keyword;
            clearTimeout(this.suggestTimer);
            this.suggest.classList.remove('is-hidden');
            this.suggest.innerHTML = '<div class="pix-editor-suggest-loading">正在搜索话题...</div>';
            this.suggestTimer = setTimeout(function() {
                this.searchTopics(trigger.keyword);
            }.bind(this), 260);
        }

        searchTopics(keyword) {
            if (!window.Theme || !Theme.ajaxurl || !Theme.moment_nonce) {
                this.renderSuggest([]);
                return;
            }
            $.ajax({
                url: Theme.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'search_mo_tag',
                    security: Theme.moment_nonce,
                    keyword: keyword
                },
                success: function(response) {
                    var list = [];
                    if (response && response.html) {
                        var box = document.createElement('div');
                        box.innerHTML = response.html;
                        Array.prototype.slice.call(box.querySelectorAll('.mo-huati-item')).forEach(function(node) {
                            var title = node.querySelector('.title');
                            var count = node.querySelector('.count-mo');
                            list.push({
                                id: node.getAttribute('tagid') || '',
                                name: title ? title.textContent.trim() : '',
                                count: count ? count.textContent.trim() : ''
                            });
                        });
                    }
                    this.renderSuggest(list);
                }.bind(this),
                error: function() {
                    this.suggest.innerHTML = '<div class="pix-editor-suggest-empty">话题搜索失败，请稍后重试</div>';
                }.bind(this)
            });
        }

        renderSuggest(list) {
            if (!list.length) {
                this.suggest.innerHTML = '<div class="pix-editor-suggest-empty">没有找到相关话题</div>';
                return;
            }
            this.suggest.innerHTML = list.map(function(item) {
                return '<button type="button" class="pix-editor-suggest-item" data-id="' + escapeAttr(item.id) + '" data-name="' + escapeAttr(item.name) + '">' +
                    '<i class="ri-hashtag"></i><span>' + escapeHtml(item.name) + '</span><small>' + escapeHtml(item.count || '') + '</small>' +
                '</button>';
            }).join('');
        }

        placeCaretAtEnd() {
            this.el.focus();
            var range = document.createRange();
            range.selectNodeContents(this.el);
            range.collapse(false);
            var selection = window.getSelection ? window.getSelection() : null;
            if (!selection) return;
            selection.removeAllRanges();
            selection.addRange(range);
        }

        removeTopicTokens() {
            Array.prototype.slice.call(this.el.querySelectorAll('.pix-editor-topic')).forEach(function(node) {
                if (node.nextSibling && node.nextSibling.nodeType === 3) {
                    node.nextSibling.nodeValue = node.nextSibling.nodeValue.replace(/^\u00a0?\s?/, '');
                }
                node.remove();
            });
        }

        syncTopicUI(name, id) {
            if (!window.jQuery) return;
            var $btn = $('.mo-tag-btn');
            $btn.find('span').text(name || '话题');
            if (name && !$('.remove-motag').length) {
                $btn.find('span').after('<a class="remove-motag pix-moment-topic-clear"><i class="ri-close-line"></i></a>');
            }
            if (name) {
                $('.push-mo-btn').attr('tagid', id || '');
                $btn.addClass('active is-selected');
            } else {
                $('.push-mo-btn').removeAttr('tagid');
                $btn.removeClass('active is-selected');
                $('.remove-motag').remove();
            }
        }

        insertTopic(name, id) {
            if (!name) return;
            var selection = window.getSelection ? window.getSelection() : null;
            if (!selection || !selection.rangeCount || !this.el.contains(selection.getRangeAt(0).endContainer)) {
                this.placeCaretAtEnd();
                selection = window.getSelection ? window.getSelection() : null;
            }
            if (!selection || !selection.rangeCount) return;
            var range = selection.getRangeAt(0);
            if (this.el.contains(range.endContainer) && range.endContainer.nodeType === 3) {
                var nodeText = range.endContainer.nodeValue || '';
                var beforeText = nodeText.slice(0, range.endOffset);
                var match = beforeText.match(/(^|\s)#([^\s#@]{1,24})$/);
                if (match) {
                    var preserveSpace = match[1] || '';
                    range.setStart(range.endContainer, range.endOffset - match[0].length);
                    range.deleteContents();
                    if (preserveSpace) {
                        range.insertNode(document.createTextNode(preserveSpace));
                        range.collapse(false);
                    }
                }
            }
            this.removeTopicTokens();
            this.placeCaretAtEnd();
            var html = '<span class="pix-editor-topic" data-tag-id="' + escapeAttr(id) + '">#' + escapeHtml(name) + '</span>&nbsp;';
            document.execCommand('insertHTML', false, html);
            this.syncTopicUI(name, id);
            this.hideSuggest();
            this.sync();
            this.el.focus();
        }

        removeTopic() {
            this.removeTopicTokens();
            this.syncTopicUI('', '');
            this.hideSuggest();
            this.sync();
        }

        insertEmoji(code, src) {
            code = String(code || '').trim();
            if (!code) return;
            src = src || this.emojiSrc(code);
            var selection = window.getSelection ? window.getSelection() : null;
            if (!selection || !selection.rangeCount || !this.el.contains(selection.getRangeAt(0).endContainer)) {
                this.placeCaretAtEnd();
            }
            var html = '<img class="pix-editor-emoji wp-smiley" src="' + escapeAttr(src) + '" alt="[s=' + escapeAttr(code) + ']" data-emoji-code="' + escapeAttr(code) + '">&nbsp;';
            document.execCommand('insertHTML', false, html);
            this.sync();
            this.el.focus();
        }

        insertLink(title, url) {
            title = String(title || '').trim();
            url = String(url || '').trim();
            if (!url) return;
            var selection = window.getSelection ? window.getSelection() : null;
            if (!selection || !selection.rangeCount || !this.el.contains(selection.getRangeAt(0).endContainer)) {
                this.placeCaretAtEnd();
            }
            var node = this.createLinkNode(title || url, url);
            var html = node.outerHTML + '&nbsp;';
            document.execCommand('insertHTML', false, html);
            this.sync();
            this.el.focus();
        }

        hideSuggest() {
            if (!this.suggest) return;
            clearTimeout(this.suggestTimer);
            this.suggestKeyword = '';
            this.suggest.classList.add('is-hidden');
        }

        value() {
            return this.el ? this.el.innerHTML.trim() : '';
        }
    }

    class PixUploader {
        constructor(el, options) {
            this.el = typeof el === 'string' ? document.querySelector(el) : el;
            this.options = Object.assign({
                context: 'moment_gallery',
                type: 'auto',
                limit: 9,
                multiple: true,
                compress: true,
                convertWebp: false,
                maxSize: 0,
                maxWidth: 1920,
                quality: 0.86,
                accept: '',
                allowExternal: true,
                allowLibrary: true,
                allowBili: true,
                allowCard: true,
                allowedKinds: null,
                preventOutsideClose: false,
                nonce: window.Theme ? Theme.upload_nonce : '',
                uploadAction: 'pix_upload_asset',
                libraryAction: 'pix_media_library',
                libraryContext: null,
                deleteAction: 'pix_delete_media',
                batchDeleteAction: 'pix_delete_media_batch',
                biliInfoAction: 'pix_bili_video_info',
                onChange: null,
                onUploaded: null,
                onError: null
            }, options || {});

            this.items = [];
            this.activeKind = '';
            this.touchSort = null;
            this.biliPending = false;
            if (this.el) this.init();
        }

        init() {
            this.el.classList.add('pix-uploader');
            this.el.innerHTML = this.template();
            this.input = this.el.querySelector('.pix-uploader-input');
            this.list = this.el.querySelector('.pix-uploader-list');
            this.drop = this.el.querySelector('.pix-uploader-drop');
            this.panel = this.el.closest('.pix-moment2-panel');
            this.inlineForms = this.el.querySelectorAll('.pix-uploader-inline-form');
            this.panelTitle = this.panel ? this.panel.querySelector('.pix-moment-attachment-copy span') : null;
            this.panelDesc = this.panel ? this.panel.querySelector('.pix-moment-attachment-copy small') : null;
            this.defaultPanelTitle = this.panelTitle ? this.panelTitle.textContent : '';
            this.defaultPanelDesc = this.panelDesc ? this.panelDesc.textContent : '';

            this.bind();
            this.syncToolVisibility('');
        }

        template() {
            return '' +
                '<div class="pix-uploader-drop pix-moment-attachment-drop">' +
                    '<input class="pix-uploader-input pix-moment-attachment-input" type="file" ' + (this.options.multiple ? 'multiple' : '') + ' accept="' + (this.options.accept || '') + '">' +
                    '<div class="pix-uploader-drop-main pix-moment-attachment-drop-main"><i class="ri-upload-cloud-2-line"></i><span>拖拽、粘贴或点击添加附件</span></div>' +
                    '<div class="pix-uploader-tools pix-moment-attachment-tools">' +
                        (this.options.allowExternal ? '<button type="button" class="pix-uploader-external pix-moment-attachment-tool pix-moment-attachment-tool-external">外链图片</button>' : '') +
                        (this.options.allowBili ? '<button type="button" class="pix-uploader-bili pix-moment-attachment-tool pix-moment-attachment-tool-bili">B站视频</button>' : '') +
                        (this.options.allowCard ? '<button type="button" class="pix-uploader-card pix-moment-attachment-tool pix-moment-attachment-tool-card">内容卡片</button>' : '') +
                        (this.options.allowLibrary ? '<button type="button" class="pix-uploader-library pix-moment-attachment-tool pix-moment-attachment-tool-library">我的媒体</button>' : '') +
                    '</div>' +
                    '<div class="pix-uploader-inline-form pix-moment-attachment-inline-form pix-moment-attachment-inline-external is-hidden" data-form="external">' +
                        '<i class="ri-image-line pix-moment-attachment-inline-icon"></i><input type="url" class="pix-uploader-external-input pix-moment-attachment-inline-input pix-moment-attachment-inline-input-external" placeholder="粘贴图片外链 URL">' +
                        '<button type="button" class="pix-uploader-inline-submit pix-moment-attachment-inline-submit" data-submit="external">添加</button>' +
                        '<button type="button" class="pix-uploader-inline-cancel pix-moment-attachment-inline-cancel" aria-label="取消"><i class="ri-close-line"></i></button>' +
                    '</div>' +
                    '<div class="pix-uploader-inline-form pix-moment-attachment-inline-form pix-moment-attachment-inline-bili is-hidden" data-form="bili">' +
                        '<i class="ri-bilibili-line pix-moment-attachment-inline-icon"></i><input type="text" class="pix-uploader-bili-input pix-moment-attachment-inline-input pix-moment-attachment-inline-input-bili" placeholder="输入 B站视频 BV 号，如 BV1xx411c7mD">' +
                        '<button type="button" class="pix-uploader-inline-submit pix-moment-attachment-inline-submit" data-submit="bili">添加</button>' +
                        '<button type="button" class="pix-uploader-inline-cancel pix-moment-attachment-inline-cancel" aria-label="取消"><i class="ri-close-line"></i></button>' +
                    '</div>' +
                    '<div class="pix-uploader-inline-form pix-moment-attachment-inline-form pix-moment-attachment-inline-card is-hidden" data-form="card">' +
                        '<i class="ri-article-line pix-moment-attachment-inline-icon"></i><input type="url" class="pix-uploader-card-input pix-moment-attachment-inline-input pix-moment-attachment-inline-input-card" placeholder="粘贴本站文章、页面或片刻链接">' +
                        '<button type="button" class="pix-uploader-inline-submit pix-moment-attachment-inline-submit" data-submit="card">生成</button>' +
                        '<button type="button" class="pix-uploader-inline-cancel pix-moment-attachment-inline-cancel" aria-label="取消"><i class="ri-close-line"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="pix-uploader-list pix-moment-attachment-list"></div>';
        }

        bind() {
            var self = this;

            this.drop.addEventListener('click', function(event) {
                if (event.target.closest('button, input, .pix-uploader-tools, .pix-uploader-inline-form')) return;
                if (self.requestedKind() === 'card') return;
                self.input.click();
            });

            this.input.addEventListener('change', function() {
                self.addFiles(Array.prototype.slice.call(self.input.files || []));
                self.input.value = '';
            });

            ['dragenter', 'dragover'].forEach(function(type) {
                self.drop.addEventListener(type, function(event) {
                    event.preventDefault();
                    if (self.requestedKind() === 'card') return;
                    self.drop.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function(type) {
                self.drop.addEventListener(type, function(event) {
                    event.preventDefault();
                    self.drop.classList.remove('is-dragover');
                });
            });

            this.drop.addEventListener('drop', function(event) {
                if (self.requestedKind() === 'card') return;
                self.addFiles(Array.prototype.slice.call(event.dataTransfer.files || []));
            });

            document.addEventListener('paste', function(event) {
                if (!self.el || !self.el.offsetParent) return;
                if (self.requestedKind() === 'card') return;
                var files = Array.prototype.slice.call((event.clipboardData || {}).files || []);
                if (files.length) self.addFiles(files);
            });

            this.el.addEventListener('click', function(event) {
                if (self.options.preventOutsideClose) {
                    event.stopPropagation();
                }

                var remove = event.target.closest('.pix-uploader-remove');
                if (remove) {
                    self.requestRemoveItem(remove.closest('.pix-uploader-item').dataset.id);
                    return;
                }

                var confirmRemove = event.target.closest('.pix-uploader-remove-confirm');
                if (confirmRemove) {
                    self.removeItem(confirmRemove.closest('.pix-uploader-item').dataset.id);
                    return;
                }

                var preview = event.target.closest('.pix-uploader-preview-btn');
                if (preview) {
                    var item = self.items.find(function(row) {
                        return String(row.id) === String(preview.closest('.pix-uploader-item').dataset.id);
                    });
                    self.previewItem(item);
                    return;
                }

                var undo = event.target.closest('.pix-uploader-undo');
                if (undo) {
                    self.undoRemoveItem(undo.closest('.pix-uploader-item').dataset.id);
                    return;
                }

                if (event.target.closest('.pix-uploader-external')) {
                    self.toggleInlineForm('external');
                    return;
                }

                if (event.target.closest('.pix-uploader-library')) {
                    self.openLibrary();
                    return;
                }

                if (event.target.closest('.pix-uploader-bili')) {
                    self.toggleInlineForm('bili');
                    return;
                }

                if (event.target.closest('.pix-uploader-card')) {
                    self.toggleInlineForm('card');
                    return;
                }

                var submit = event.target.closest('.pix-uploader-inline-submit');
                if (submit) {
                    self.submitInlineForm(submit.dataset.submit);
                    return;
                }

                if (event.target.closest('.pix-uploader-inline-cancel')) {
                    self.closeInlineForms();
                    return;
                }
            });

            this.el.addEventListener('keydown', function(event) {
                if (event.key !== 'Enter') return;
                var form = event.target.closest('.pix-uploader-inline-form');
                if (!form) return;
                event.preventDefault();
                self.submitInlineForm(form.dataset.form);
            });
        }

        open(kind, options) {
            options = options || {};
            if (!this.panel) return;
            var previousKind = this.requestedKind();
            this.panel.classList.remove('is-collapsed');
            this.panel.classList.add('is-open');
            if (kind) {
                this.panel.dataset.activeKind = kind;
                if (this.input) this.input.setAttribute('accept', this.acceptFor(kind));
            }
            var nextKind = kind || this.requestedKind();
            if (!options.keepInlineForms && (!kind || previousKind !== nextKind)) {
                this.closeInlineForms();
            }
            this.syncPanelMode(nextKind);
            this.syncToolVisibility(nextKind);
        }

        syncPanelMode(kind) {
            if (!this.panel) return;
            var isCardMode = kind === 'card';
            this.panel.classList.toggle('is-card-mode', isCardMode);
            if (this.panelTitle) {
                this.panelTitle.textContent = isCardMode ? '添加卡片' : this.defaultPanelTitle;
            }
            if (this.panelDesc) {
                this.panelDesc.textContent = isCardMode ? '粘贴本站文章、页面或片刻链接，生成内容卡片' : this.defaultPanelDesc;
            }
        }

        syncToolVisibility(kind) {
            var tools = this.el ? this.el.querySelector('.pix-uploader-tools') : null;
            if (!tools) return;
            kind = kind || this.requestedKind();

            var canUseExternal = this.options.allowExternal;
            if (window.current_mo_data && current_mo_data.gallery_link === false) {
                canUseExternal = false;
            }

            var canUseLibrary = ['image', 'video', 'file'].indexOf(kind) > -1;
            var rules = {
                external: !kind ? this.options.allowExternal : kind === 'image' && canUseExternal,
                bili: !kind ? this.options.allowBili : false,
                card: !kind ? this.options.allowCard : kind === 'card' && this.options.allowCard,
                library: !kind ? this.options.allowLibrary : canUseLibrary && this.options.allowLibrary
            };

            var visibleCount = 0;
            Object.keys(rules).forEach(function(name) {
                var button = tools.querySelector('.pix-uploader-' + name);
                if (!button) return;
                var visible = !!rules[name];
                button.classList.toggle('is-hidden', !visible);
                if (visible) visibleCount += 1;
            });
            tools.classList.toggle('is-hidden', !!kind && visibleCount === 0);
        }

        requestedKind() {
            return this.activeKind || (this.panel && this.panel.dataset.activeKind) || '';
        }

        allowedKinds() {
            if (!this.options.allowedKinds) return [];
            if (Array.isArray(this.options.allowedKinds)) {
                return this.options.allowedKinds.filter(Boolean);
            }
            return String(this.options.allowedKinds).split(',').map(function(kind) {
                return kind.trim();
            }).filter(Boolean);
        }

        isKindAllowed(kind) {
            var allowed = this.allowedKinds();
            return !allowed.length || allowed.indexOf(kind) > -1;
        }

        acceptFor(kind) {
            if (kind === 'image') return 'image/*';
            if (kind === 'video') return 'video/*';
            if (kind === 'file') return '.txt,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.7z';
            return this.options.accept || '';
        }

        openCardForm() {
            if (this.activeKind && this.activeKind !== 'card') {
                pixToast('一个片刻只能使用一种附件类型', 'error');
                this.open(this.activeKind);
                return;
            }
            this.toggleInlineForm('card');
        }

        pick(kind) {
            this.closeInlineForms();
            if (this.activeKind && kind && this.activeKind !== kind) {
                pixToast('一个片刻只能使用一种附件类型', 'error');
                this.open(this.activeKind);
                return;
            }
            this.open(kind || this.activeKind || '');
            if (this.input) {
                this.input.setAttribute('accept', this.acceptFor(kind));
                this.input.click();
            }
        }

        canAccept(files) {
            if (!files.length) return false;
            var kind = fileKind(files[0]);
            if (!this.isKindAllowed(kind)) {
                pixToast('这里只能上传图片', 'error');
                return false;
            }
            var mixed = files.some(function(file) {
                return fileKind(file) !== kind;
            });

            if (mixed) {
                pixToast('一次只能选择同一种附件类型', 'error');
                return false;
            }

            var requestedKind = this.requestedKind();
            if (requestedKind && requestedKind !== kind) {
                pixToast('一个片刻只能使用一种附件类型', 'error');
                return false;
            }

            for (var i = 0; i < files.length; i++) {
                var fileKindValue = fileKind(files[i]);
                var maxSize = this.maxSizeForKind(fileKindValue);
                if (maxSize && files[i].size > maxSize * 1024 * 1024) {
                    pixToast(kindLabel(fileKindValue) + '最大尺寸为 ' + maxSize + 'MB', 'error');
                    return false;
                }
            }

            var activeCount = this.items.filter(function(item) {
                return item.status !== 'removing';
            }).length;

            if ((kind === 'video' || kind === 'audio') && (activeCount || files.length > 1)) {
                pixToast('视频或音频片刻一次只能添加一个文件', 'error');
                return false;
            }

            var limit = this.limitForKind(kind);
            if (activeCount + files.length > limit) {
                pixToast('最多只能添加 ' + limit + ' 个附件', 'error');
                return false;
            }

            return true;
        }

        limitForKind(kind) {
            var fallback = Math.max(1, parseInt(this.options.limit, 10) || 9);
            if (!/^moment_/.test(this.options.context || '')) {
                return fallback;
            }

            var current = window.current_mo_data || {};
            if (kind === 'image') return Math.max(1, parseInt(current.gallery_num, 10) || fallback);
            if (kind === 'file') return Math.max(1, parseInt(current.file_num, 10) || fallback);
            if (kind === 'card') return Math.max(1, parseInt(current.card_num, 10) || 3);
            if (kind === 'video' || kind === 'audio') return 1;
            return fallback;
        }

        maxSizeForKind(kind) {
            if (!/^moment_/.test(this.options.context || '')) {
                return parseFloat(this.options.maxSize || 0) || 0;
            }

            var sizes = window.current_mo_data && current_mo_data.media_max_size ? current_mo_data.media_max_size : {};
            if (kind === 'image') return parseFloat(sizes.image || 3);
            if (kind === 'video') return parseFloat(sizes.video || 20);
            if (kind === 'file') return parseFloat(sizes.file || 10);
            return 0;
        }

        async addFiles(files) {
            if (!this.canAccept(files)) return;
            this.activeKind = this.activeKind || this.requestedKind() || fileKind(files[0]);

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var originalSize = file.size || 0;
                if (fileKind(file) === 'image') {
                    file = await compressImage(file, this.options);
                }
                file.originalSize = originalSize;
                var item = await this.buildLocalItem(file);
                this.items.push(item);
                this.render();
                this.uploadItem(item);
            }
            this.changed();
        }

        async buildLocalItem(file) {
            var kind = fileKind(file);
            var item = {
                id: uid(),
                kind: kind,
                file: file,
                name: file.name,
                size: file.size || 0,
                originalSize: file.originalSize || file.size || 0,
                mime: file.type || '',
                status: 'queued',
                progress: 0,
                source: 'local'
            };

            if (kind === 'image') {
                item.preview = URL.createObjectURL(file);
            } else if (kind === 'video') {
                item.preview = URL.createObjectURL(file);
                var poster = await videoPoster(file);
                if (poster) {
                    item.posterFile = poster;
                    item.poster = URL.createObjectURL(poster);
                }
            } else if (kind === 'file') {
                item.preview = URL.createObjectURL(file);
            }

            return item;
        }

        uploadItem(item) {
            var self = this;
            var form = new FormData();
            form.append('action', this.options.uploadAction);
            form.append('nonce', this.options.nonce);
            form.append('context', this.options.context);
            form.append('term_id', this.currentTermId());
            form.append('original_size', item.originalSize || item.size || 0);
            form.append('files[]', item.file, item.file.name);

            item.status = 'uploading';
            this.render();

            $.ajax({
                url: Theme.ajaxurl,
                type: 'POST',
                data: form,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(event) {
                            if (!event.lengthComputable) return;
                            item.progress = Math.round((event.loaded / event.total) * 100);
                            self.render();
                        });
                    }
                    return xhr;
                },
                success: function(response) {
                    if (!response || response.status != 1 || !response.items || !response.items.length) {
                        self.failItem(item, response && response.msg ? response.msg : '上传失败');
                        return;
                    }
                    var localPreview = item.preview;
                    var localPoster = item.poster;
                    var localPosterFile = item.posterFile;
                    Object.assign(item, response.items[0], {
                        status: 'done',
                        progress: 100,
                        attachment_id: response.items[0].id,
                        kind: response.items[0].type || item.kind,
                        preview: localPreview || response.items[0].url,
                        poster: localPoster || response.items[0].thumb,
                        posterFile: localPosterFile || null
                    });
                    self.render();
                    self.changed();
                    if (item.kind === 'video' && item.posterFile) {
                        self.uploadPoster(item);
                    }
                    if (typeof self.options.onUploaded === 'function') {
                        self.options.onUploaded(item, self);
                    }
                },
                error: function(xhr) {
                    var msg = '上传失败，请稍后重试';
                    if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg;
                    self.failItem(item, msg);
                }
            });
        }

        uploadPoster(item) {
            var self = this;
            var form = new FormData();
            form.append('action', this.options.uploadAction);
            form.append('nonce', this.options.nonce);
            form.append('context', 'moment_gallery');
            form.append('term_id', this.currentTermId());
            form.append('poster_for', item.attachment_id || item.id || 0);
            form.append('files[]', item.posterFile, item.posterFile.name);

            item.posterStatus = 'uploading';
            this.render();

            $.ajax({
                url: Theme.ajaxurl,
                type: 'POST',
                data: form,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response && response.status == 1 && response.items && response.items.length) {
                        item.poster_id = response.items[0].id;
                        item.poster = response.items[0].url;
                        item.thumb = response.items[0].url;
                        item.posterStatus = 'done';
                        self.render();
                        self.changed();
                        return;
                    }
                    item.posterStatus = 'error';
                    self.render();
                },
                error: function() {
                    item.posterStatus = 'error';
                    self.render();
                }
            });
        }

        failItem(item, message) {
            item.status = 'error';
            item.error = message;
            this.render();
            this.changed();
            pixToast(message, 'error');
            if (typeof this.options.onError === 'function') {
                this.options.onError(message, item, this);
            }
        }

        currentTermId() {
            if (window.current_mo_data && current_mo_data.term_id) {
                return current_mo_data.term_id;
            }
            var btn = document.querySelector('.mo-cir-btn[catid]');
            return btn ? btn.getAttribute('catid') : '';
        }

        toggleInlineForm(type) {
            var target = this.el.querySelector('.pix-uploader-inline-form[data-form="' + cssEscape(type) + '"]');
            if (!target) return;
            var willOpen = target.classList.contains('is-hidden');
            this.closeInlineForms();
            if (willOpen) {
                this.open(type === 'bili' ? 'video' : (type === 'external' ? 'image' : type), {
                    keepInlineForms: true
                });
                target.classList.remove('is-hidden');
                var input = target.querySelector('input');
                if (input) input.focus();
            }
        }

        closeInlineForms() {
            Array.prototype.slice.call(this.inlineForms || []).forEach(function(form) {
                form.classList.add('is-hidden');
            });
        }

        submitInlineForm(type) {
            if (type === 'external') {
                var externalInput = this.el.querySelector('.pix-uploader-external-input');
                this.addExternal(externalInput ? externalInput.value : '');
                if (externalInput) externalInput.value = '';
                return;
            }
            if (type === 'bili') {
                var biliInput = this.el.querySelector('.pix-uploader-bili-input');
                this.addBiliVideo(biliInput ? biliInput.value : '');
                if (biliInput) biliInput.value = '';
                return;
            }
            if (type === 'card') {
                var cardInput = this.el.querySelector('.pix-uploader-card-input');
                this.addCard(cardInput ? cardInput.value : '');
                if (cardInput) cardInput.value = '';
            }
        }

        addExternal(url) {
            if (window.current_mo_data && current_mo_data.gallery_link === false) {
                pixToast('当前圈子不允许使用外链图片', 'error');
                return;
            }

            url = String(url || '').trim();
            if (!url) {
                pixToast('请先粘贴图片外链 URL', 'error');
                return;
            }
            if (!/^https?:\/\//i.test(url)) {
                pixToast('请输入有效的图片 URL', 'error');
                return;
            }
            if (this.activeKind && this.activeKind !== 'image') {
                pixToast('当前片刻已经是其他附件类型，不能加入图片', 'error');
                return;
            }
            var activeCount = this.items.filter(function(item) {
                return item.status !== 'removing';
            }).length;
            var limit = this.limitForKind('image');
            if (activeCount >= limit) {
                pixToast('最多只能添加 ' + limit + ' 个附件', 'error');
                return;
            }
            this.activeKind = 'image';
            this.items.push({
                id: uid(),
                kind: 'image',
                source: 'external',
                status: 'done',
                url: url,
                thumb: url,
                preview: url,
                name: url.split('/').pop()
            });
            this.closeInlineForms();
            this.render();
            this.changed();
        }

        addBiliVideo(value) {
            var self = this;
            if (this.activeKind && this.activeKind !== 'video') {
                pixToast('一个片刻只能使用一种附件类型', 'error');
                return;
            }
            if (this.activeItems().length) {
                pixToast('视频片刻一次只能添加一个视频', 'error');
                return;
            }

            var bvid = normalizeBvid(value);
            if (!bvid) {
                pixToast('请输入正确的 BV 号', 'error');
                return;
            }
            if (this.biliPending) {
                pixToast('正在获取 B站视频信息，请稍候', 'info');
                return;
            }

            this.biliPending = true;
            var submit = this.el.querySelector('.pix-uploader-inline-submit[data-submit="bili"]');
            if (submit) submit.classList.add('is-loading');
            $.ajax({
                type: 'POST',
                url: Theme.ajaxurl,
                dataType: 'json',
                data: {
                    action: this.options.biliInfoAction,
                    nonce: this.options.nonce,
                    bvid: bvid
                },
                success: function(response) {
                    var data = response && response.status == 1 ? (response.data || {}) : {};
                    self.activeKind = 'video';
                    self.items.push({
                        id: uid(),
                        kind: 'video',
                        type: 'video',
                        source: 'bili',
                        status: 'done',
                        bvid: bvid,
                        title: data.title || ('B站视频 ' + bvid),
                        name: data.title || ('B站视频 ' + bvid),
                        thumb: data.pic || '',
                        preview: data.pic || '',
                        cover: data.cover || data.pic || '',
                        mime: 'bilibili',
                        url: '//player.bilibili.com/player.html?bvid=' + encodeURIComponent(bvid) + '&page=1'
                    });
                    self.open('video');
                    self.closeInlineForms();
                    self.render();
                    self.changed();
                    if (!data.title && response && response.msg) {
                        pixToast(response.msg, 'info');
                    }
                },
                error: function() {
                    self.activeKind = 'video';
                    self.items.push({
                        id: uid(),
                        kind: 'video',
                        type: 'video',
                        source: 'bili',
                        status: 'done',
                        bvid: bvid,
                        title: 'B站视频 ' + bvid,
                        name: 'B站视频 ' + bvid,
                        cover: '',
                        mime: 'bilibili',
                        url: '//player.bilibili.com/player.html?bvid=' + encodeURIComponent(bvid) + '&page=1'
                    });
                    self.open('video');
                    self.closeInlineForms();
                    self.render();
                    self.changed();
                    pixToast('B站信息获取失败，已使用 BV 号生成预览', 'info');
                },
                complete: function() {
                    self.biliPending = false;
                    if (submit) submit.classList.remove('is-loading');
                }
            });
        }

        addCard(value) {
            var self = this;
            var url = String(value || '').trim();
            if (!url) {
                pixToast('请先粘贴内容链接', 'error');
                return;
            }
            if (this.activeKind && this.activeKind !== 'card') {
                pixToast('一个片刻只能使用一种附件类型', 'error');
                return;
            }
            if (this.activeItems().length >= this.cardLimit()) {
                pixToast('最多可添加 ' + this.cardLimit() + ' 个卡片', 'error');
                return;
            }

            var submit = this.el.querySelector('.pix-uploader-inline-submit[data-submit="card"]');
            if (submit) submit.classList.add('is-loading');
            $.ajax({
                type: 'POST',
                url: Theme.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'ajax_get_card_data',
                    security: Theme.moment_nonce,
                    url: url
                },
                success: function(response) {
                    if (!response || response.status != 1 || !response.card) {
                        pixToast(response && response.msg ? response.msg : '卡片生成失败', 'error');
                        return;
                    }
                    var card = response.card;
                    self.activeKind = 'card';
                    self.items.push({
                        id: uid(),
                        kind: 'card',
                        type: 'card',
                        source: 'card',
                        status: 'done',
                        pid: card.pid,
                        url: card.url,
                        title: card.title,
                        name: card.title,
                        desc: card.des,
                        thumb: card.image,
                        preview: card.image,
                        mime: 'card'
                    });
                    self.render();
                    self.changed();
                    pixToast('卡片已生成', 'success');
                },
                error: function() {
                    pixToast('卡片生成失败，请稍后重试', 'error');
                },
                complete: function() {
                    if (submit) submit.classList.remove('is-loading');
                }
            });
        }

        cardLimit() {
            return this.limitForKind('card');
        }

        openLibrary() {
            var self = this;
            var modal = document.createElement('div');
            modal.className = 'pix-media-modal pix-moment-media-library-modal';
            var allowedKinds = this.allowedKinds();
            var requestedKind = this.requestedKind();
            var lockedKind = allowedKinds.length === 1 ? allowedKinds[0] : (['image', 'video', 'file'].indexOf(requestedKind) > -1 ? requestedKind : '');
            var tabLabels = {
                image: '图片',
                video: '视频',
                file: '文件'
            };
            var tabs = lockedKind
                ? '<button type="button" class="is-active pix-moment-media-library-tab" data-type="' + escapeAttr(lockedKind) + '">' + escapeHtml(tabLabels[lockedKind] || '媒体') + '</button>'
                : '<button type="button" class="is-active pix-moment-media-library-tab" data-type="">全部</button>' +
                    '<button type="button" class="pix-moment-media-library-tab" data-type="image">图片</button>' +
                    '<button type="button" class="pix-moment-media-library-tab" data-type="video">视频</button>' +
                    '<button type="button" class="pix-moment-media-library-tab" data-type="file">文件</button>';
            modal.innerHTML = '' +
                '<div class="pix-media-panel pix-moment-media-library-panel">' +
                    '<div class="pix-media-head pix-moment-media-library-head"><span>我的媒体</span><button type="button" class="pix-media-close pix-moment-media-library-close">×</button></div>' +
                    '<div class="pix-media-summary pix-moment-media-library-summary"><span>已使用 <strong>--</strong></span><em>共 0 个文件</em></div>' +
                    '<div class="pix-media-bulkbar pix-moment-media-library-bulkbar"><span>已选择 <strong>0</strong> 个</span><button type="button" class="pix-media-bulk-insert pix-moment-media-library-bulk-insert">批量插入</button><button type="button" class="pix-media-bulk-delete pix-moment-media-library-bulk-delete">批量删除</button><button type="button" class="pix-media-bulk-clear pix-moment-media-library-bulk-clear">取消选择</button></div>' +
                    '<div class="pix-media-toolbar pix-moment-media-library-toolbar">' +
                        '<div class="pix-media-search pix-moment-media-library-search"><i class="ri-search-line"></i><input type="search" placeholder="搜索文件名"></div>' +
                        '<div class="pix-media-sort pix-moment-media-library-sort">' +
                            '<button type="button" class="pix-media-sort-toggle pix-moment-media-library-sort-toggle"><i class="ri-sort-desc"></i><span>最新上传</span><em class="ri-arrow-down-s-line"></em></button>' +
                            '<div class="pix-media-sort-menu pix-moment-media-library-sort-menu">' +
                                '<button type="button" class="is-active pix-moment-media-library-sort-option" data-sort="date_desc">最新上传</button>' +
                                '<button type="button" class="pix-moment-media-library-sort-option" data-sort="date_asc">最早上传</button>' +
                                '<button type="button" class="pix-moment-media-library-sort-option" data-sort="size_desc">文件最大</button>' +
                                '<button type="button" class="pix-moment-media-library-sort-option" data-sort="size_asc">文件最小</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="pix-media-tabs pix-moment-media-library-tabs">' +
                            tabs +
                        '</div>' +
                    '</div>' +
                    '<div class="pix-media-grid pix-moment-media-library-grid"></div>' +
                    '<div class="pix-media-foot pix-moment-media-library-foot"><button type="button" class="pix-media-prev pix-moment-media-library-prev">上一页</button><span></span><button type="button" class="pix-media-next pix-moment-media-library-next">下一页</button></div>' +
                    '<aside class="pix-media-detail pix-moment-media-library-detail" aria-hidden="true"></aside>' +
                '</div>';
            document.body.appendChild(modal);

            var state = {
                page: 1,
                perPage: 24,
                type: lockedKind || requestedKind,
                keyword: '',
                orderby: 'date',
                order: 'DESC',
                items: [],
                selectedIds: [],
                totalPages: 1
            };

            if (state.type) {
                modal.querySelectorAll('.pix-media-tabs button').forEach(function(btn) {
                    btn.classList.toggle('is-active', btn.dataset.type === state.type);
                });
            }

            var load = function() {
                self.loadLibraryPage(modal, state);
            };
            var searchTimer = null;

            modal.addEventListener('click', function(event) {
                if (self.options.preventOutsideClose) {
                    event.stopPropagation();
                }

                if (event.target === modal || event.target.closest('.pix-media-close')) {
                    modal.remove();
                    return;
                }

                var sortToggle = event.target.closest('.pix-media-sort-toggle');
                if (sortToggle) {
                    sortToggle.closest('.pix-media-sort').classList.toggle('is-open');
                    return;
                }

                var sortItem = event.target.closest('.pix-media-sort-menu button');
                if (sortItem) {
                    var parts = String(sortItem.dataset.sort || 'date_desc').split('_');
                    state.orderby = parts[0] || 'date';
                    state.order = (parts[1] || 'desc').toUpperCase();
                    state.page = 1;
                    modal.querySelectorAll('.pix-media-sort-menu button').forEach(function(btn) {
                        btn.classList.toggle('is-active', btn === sortItem);
                    });
                    modal.querySelector('.pix-media-sort-toggle span').textContent = sortItem.textContent;
                    sortItem.closest('.pix-media-sort').classList.remove('is-open');
                    load();
                    return;
                }

                var openSort = modal.querySelector('.pix-media-sort.is-open');
                if (openSort && !event.target.closest('.pix-media-sort')) {
                    openSort.classList.remove('is-open');
                }

                var tab = event.target.closest('.pix-media-tabs button');
                if (tab) {
                    state.type = tab.dataset.type || '';
                    if (lockedKind) state.type = lockedKind;
                    state.page = 1;
                    modal.querySelectorAll('.pix-media-tabs button').forEach(function(btn) {
                        btn.classList.toggle('is-active', btn === tab);
                    });
                    load();
                    return;
                }

                if (event.target.closest('.pix-media-bulk-clear')) {
                    state.selectedIds = [];
                    self.renderLibraryItems(modal, state);
                    return;
                }

                if (event.target.closest('.pix-media-bulk-delete')) {
                    self.deleteSelectedLibraryItems(state, modal, load);
                    return;
                }

                if (event.target.closest('.pix-media-bulk-insert')) {
                    self.insertSelectedLibraryItems(state, modal);
                    return;
                }

                if (event.target.closest('.pix-media-detail-close')) {
                    self.closeMediaDetail(modal);
                    return;
                }

                if (modal.querySelector('.pix-media-detail.is-active') && event.target.closest('.pix-media-panel') && !event.target.closest('.pix-media-detail') && !event.target.closest('.pix-media-detail-btn')) {
                    self.closeMediaDetail(modal);
                }

                var actionToggle = event.target.closest('.pix-media-actions-toggle');
                if (actionToggle) {
                    var actionCard = actionToggle.closest('.pix-media-item');
                    modal.querySelectorAll('.pix-media-item.is-actions-open').forEach(function(card) {
                        if (card !== actionCard) card.classList.remove('is-actions-open');
                    });
                    if (actionCard) actionCard.classList.toggle('is-actions-open');
                    return;
                }

                if (modal.querySelector('.pix-media-item.is-actions-open') && !event.target.closest('.pix-media-actions')) {
                    modal.querySelectorAll('.pix-media-item.is-actions-open').forEach(function(card) {
                        card.classList.remove('is-actions-open');
                    });
                }

                if (event.target.closest('.pix-media-prev')) {
                    if (state.page > 1) {
                        state.page -= 1;
                        load();
                    }
                    return;
                }

                if (event.target.closest('.pix-media-next')) {
                    if (state.page < state.totalPages) {
                        state.page += 1;
                        load();
                    }
                    return;
                }

                var btn = event.target.closest('.pix-media-item');
                if (!btn) return;
                var selected = state.items.find(function(item) { return String(item.id) === btn.dataset.id; });
                if (!selected) return;
                if (event.target.closest('.pix-media-check')) {
                    self.toggleLibrarySelection(selected, state, modal);
                    return;
                }
                if (event.target.closest('.pix-media-preview')) {
                    btn.classList.remove('is-actions-open');
                    self.previewItem(selected);
                    return;
                }
                if (event.target.closest('.pix-media-detail-btn')) {
                    btn.classList.remove('is-actions-open');
                    self.openMediaDetail(selected, modal);
                    return;
                }
                if (event.target.closest('.pix-media-delete')) {
                    btn.classList.remove('is-actions-open');
                    self.deleteLibraryItem(selected, state, modal, btn);
                    return;
                }
                if (!event.target.closest('.pix-media-select')) return;
                self.insertLibraryItems([selected], modal);
            });

            modal.querySelector('.pix-media-search input').addEventListener('input', function() {
                clearTimeout(searchTimer);
                state.keyword = this.value.trim();
                state.page = 1;
                searchTimer = setTimeout(load, 280);
            });

            load();
        }

        canAddLibraryItem(item) {
            return this.canAddLibraryItems([item]);
        }

        canAddLibraryItems(items) {
            items = (items || []).filter(Boolean);
            if (!items.length) return false;
            var firstType = items[0] && items[0].type ? items[0].type : 'file';
            if (!this.isKindAllowed(firstType)) {
                pixToast('当前入口不支持插入这种媒体', 'error');
                return false;
            }
            var mixed = items.some(function(item) {
                return (item && item.type ? item.type : 'file') !== firstType;
            });
            if (mixed) {
                pixToast('一次只能插入同一种媒体', 'error');
                return false;
            }
            var requestedKind = this.requestedKind();
            if (requestedKind && requestedKind !== firstType) {
                pixToast('一个片刻只能使用一种附件类型', 'error');
                return false;
            }
            var activeCount = this.activeItems().length;
            var type = firstType;
            if ((type === 'video' || type === 'audio') && (activeCount || items.length > 1)) {
                pixToast('视频或音频片刻一次只能添加一个文件', 'error');
                return false;
            }
            var limit = this.limitForKind(type);
            if (activeCount + items.length > limit) {
                pixToast('最多只能添加 ' + limit + ' 个附件', 'error');
                return false;
            }
            return true;
        }

        insertLibraryItems(items, modal) {
            items = (items || []).filter(Boolean);
            if (!this.canAddLibraryItems(items)) return false;
            var type = items[0] && items[0].type ? items[0].type : 'file';
            this.activeKind = this.activeKind || type;
            var self = this;
            items.forEach(function(item) {
                self.items.push(Object.assign({}, item, {
                    id: uid(),
                    attachment_id: item.id,
                    kind: item.type,
                    source: 'library',
                    status: 'done'
                }));
            });
            this.render();
            this.changed();
            if (modal) modal.remove();
            pixToast(items.length > 1 ? '已插入 ' + items.length + ' 个媒体' : '已插入媒体', 'success');
            return true;
        }

        insertSelectedLibraryItems(state, modal) {
            var ids = state.selectedIds || [];
            if (!ids.length) {
                pixToast('请先选择媒体', 'error');
                return;
            }
            var items = (state.items || []).filter(function(item) {
                return ids.some(function(id) {
                    return String(id) === String(item.id);
                });
            });
            this.insertLibraryItems(items, modal);
        }

        loadLibraryPage(modal, state) {
            var self = this;
            var grid = modal.querySelector('.pix-media-grid');
            var foot = modal.querySelector('.pix-media-foot');
            if (foot) foot.classList.add('is-hidden');
            grid.innerHTML = '<div class="pix-media-empty pix-media-loading pix-moment-media-library-empty pix-moment-media-library-loading"><i></i><span>正在加载媒体...</span></div>';
            $.ajax({
                url: Theme.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: this.options.libraryAction,
                    nonce: this.options.nonce,
                    context: this.options.libraryContext === null ? this.options.context : this.options.libraryContext,
                    type: state.type || '',
                    keyword: state.keyword || '',
                    orderby: state.orderby || 'date',
                    order: state.order || 'DESC',
                    page: state.page,
                    per_page: state.perPage
                },
                success: function(response) {
                    if (!response || response.status != 1) {
                        pixToast(response && response.msg ? response.msg : '媒体库加载失败', 'error');
                        grid.innerHTML = '<div class="pix-media-empty pix-moment-media-library-empty">媒体库加载失败</div>';
                        if (foot) foot.classList.add('is-hidden');
                        return;
                    }
                    state.items = response.items || [];
                    state.selectedIds = (state.selectedIds || []).filter(function(id) {
                        return state.items.some(function(item) {
                            return String(item.id) === String(id);
                        });
                    });
                    state.totalPages = Math.max(1, parseInt(response.total_pages, 10) || 1);
                    state.page = parseInt(response.current_page, 10) || state.page;
                    state.total = parseInt(response.total, 10) || 0;
                    state.totalSize = parseInt(response.total_size, 10) || 0;
                    self.renderLibraryItems(modal, state);
                },
                error: function() {
                    grid.innerHTML = '<div class="pix-media-empty pix-moment-media-library-empty">媒体库加载失败</div>';
                    if (foot) foot.classList.add('is-hidden');
                    pixToast('媒体库加载失败', 'error');
                },
                complete: function() {
                    if (foot) foot.classList.toggle('is-hidden', !state.totalPages || state.totalPages <= 1);
                }
            });
        }

        isLibraryItemDeletable(item) {
            if (!item || item.used) return false;
            if (item.source === 'wp_library') return false;
            if (item.can_delete === false || item.can_delete === 0) return false;
            if (item.can_delete === '0' || item.can_delete === 'false') return false;
            return true;
        }

        renderLibraryItems(modal, state) {
            var self = this;
            var grid = modal.querySelector('.pix-media-grid');
            var foot = modal.querySelector('.pix-media-foot');
            var summary = modal.querySelector('.pix-media-summary');
            var bulkbar = modal.querySelector('.pix-media-bulkbar');
            var items = state.items || [];
            if (summary) {
                summary.querySelector('strong').textContent = formatSize(state.totalSize || 0) || '0 B';
                summary.querySelector('em').textContent = '共 ' + (state.total || 0) + ' 个文件';
            }
            if (bulkbar) {
                bulkbar.classList.toggle('is-active', !!(state.selectedIds || []).length);
                bulkbar.querySelector('strong').textContent = (state.selectedIds || []).length;
            }
            if (!items.length) {
                grid.innerHTML = '<div class="pix-media-empty pix-moment-media-library-empty">' + (state.keyword ? '没有找到相关媒体' : '暂无媒体') + '</div>';
            } else {
                grid.innerHTML = items.map(function(item) {
                    var thumb = (item.type === 'image' || item.type === 'video') ? item.thumb : '';
                    var icon = item.type === 'video' ? 'ri-video-line' : 'ri-file-3-line';
                    var preview = '';
                    if (item.type === 'video') {
                        preview = '<div class="pix-media-video-cover pix-moment-media-library-video-cover">' +
                            (thumb && thumb !== item.url ? '<img class="pix-moment-media-library-thumb" src="' + escapeAttr(thumb) + '" alt="">' : '<video class="pix-moment-media-library-video" src="' + escapeAttr(item.url) + '" muted preload="metadata" playsinline></video>') +
                        '</div>';
                    }
                    var selected = (state.selectedIds || []).some(function(id) { return String(id) === String(item.id); });
                    var canDelete = self.isLibraryItemDeletable(item);
                    var readonly = item.source === 'wp_library' || item.can_delete === false || item.can_delete === 0 || item.can_delete === '0' || item.can_delete === 'false';
                    var deleteText = item.used ? '不可删除' : (readonly ? '只可插入' : '删除媒体');
                    var actionMenu = '<div class="pix-media-actions pix-moment-media-library-actions">' +
                        '<button type="button" class="pix-media-actions-toggle pix-moment-media-library-actions-toggle" aria-label="更多操作"><i class="ri-more-2-fill"></i></button>' +
                        '<div class="pix-media-actions-menu pix-moment-media-library-actions-menu">' +
                            '<button type="button" class="pix-media-preview pix-moment-media-library-preview"><i class="' + escapeAttr(previewIcon(item)) + '"></i><span>' + escapeHtml(previewText(item)) + '</span></button>' +
                            '<button type="button" class="pix-media-detail-btn pix-moment-media-library-detail-button"><i class="ri-information-line"></i><span>查看详情</span></button>' +
                            '<button type="button" class="pix-media-delete pix-moment-media-library-delete" ' + (!canDelete ? 'disabled' : '') + '><i class="ri-delete-bin-6-line"></i><span>' + deleteText + '</span></button>' +
                        '</div>' +
                    '</div>';
                    return '<div class="pix-media-item pix-moment-media-library-item' + (item.used ? ' is-used' : '') + (readonly ? ' is-readonly' : '') + (selected ? ' is-selected' : '') + '" data-id="' + escapeAttr(item.id) + '">' +
                        (preview || (thumb ? '<img class="pix-moment-media-library-thumb" src="' + escapeAttr(thumb) + '" alt="">' : '<i class="pix-media-placeholder-icon pix-moment-media-library-placeholder ' + escapeAttr(icon) + '"></i>')) +
                        (item.used ? '<b class="pix-media-used pix-moment-media-library-used">已使用' + (item.used_count > 1 ? ' ' + escapeHtml(item.used_count) : '') + '</b>' : '') +
                        '<button type="button" class="pix-media-check pix-moment-media-library-check" aria-label="选择媒体"><i class="' + (selected ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line') + '"></i></button>' +
                        actionMenu +
                        '<span class="pix-moment-media-library-title">' + escapeHtml(item.title || '附件') + '</span>' +
                        '<button type="button" class="pix-media-select pix-moment-media-library-select" aria-label="选择媒体"></button>' +
                    '</div>';
                }).join('');
            }

            foot.querySelector('span').textContent = state.page + ' / ' + state.totalPages;
            foot.querySelector('.pix-media-prev').disabled = state.page <= 1;
            foot.querySelector('.pix-media-next').disabled = state.page >= state.totalPages;
        }

        openMediaDetail(item, modal) {
            var panel = modal.querySelector('.pix-media-detail');
            if (!panel || !item) return;
            var type = item.type || item.kind || 'file';
            var title = item.title || item.filename || '附件';
            var url = item.url || '';
            var dimension = item.width && item.height ? item.width + ' × ' + item.height : '暂无';
            var rows = [
                ['上传时间', item.date || '暂无'],
                ['文件体积', formatSize(item.size) || '暂无'],
                ['文件格式', item.mime || type || '暂无'],
                ['分辨率', dimension],
                ['文件名', item.filename || title],
                ['文件URL', url || '暂无']
            ];
            var cover = '';
            var videoThumb = item.poster || (item.thumb && item.thumb !== item.url ? item.thumb : '');
            if (type === 'image' && (item.thumb || item.url)) {
                cover = '<img class="pix-moment-media-library-detail-image" src="' + escapeAttr(item.thumb || item.url) + '" alt="">';
            } else if (type === 'video' && videoThumb) {
                cover = '<div class="pix-media-detail-video pix-moment-media-library-detail-video">' +
                    '<img class="pix-moment-media-library-detail-image" src="' + escapeAttr(videoThumb) + '" alt="">' +
                    '<i class="ri-play-fill pix-moment-media-library-detail-play"></i>' +
                '</div>';
            } else {
                cover = '<i class="' + escapeAttr(this.fileIcon(item)) + ' pix-moment-media-library-detail-icon"></i>';
            }

            panel.innerHTML = '<div class="pix-media-detail-head pix-moment-media-library-detail-head">' +
                    '<div class="pix-moment-media-library-detail-title"><small>' + escapeHtml(kindLabel(type)) + '详情</small><strong>' + escapeHtml(title) + '</strong></div>' +
                    '<button type="button" class="pix-media-detail-close pix-moment-media-library-detail-close" aria-label="关闭详情">×</button>' +
                '</div>' +
                '<div class="pix-media-detail-cover pix-moment-media-library-detail-cover">' + cover + '</div>' +
                '<dl class="pix-moment-media-library-detail-list">' + rows.map(function(row) {
                    return '<div class="pix-moment-media-library-detail-row"><dt>' + escapeHtml(row[0]) + '</dt><dd>' + escapeHtml(row[1]) + '</dd></div>';
                }).join('') + '</dl>' +
                (url ? '<a class="pix-media-download pix-moment-media-library-download" href="' + escapeAttr(url) + '" download target="_blank" rel="noopener"><i class="ri-download-line"></i>下载文件</a>' : '');
            panel.classList.add('is-active');
            panel.setAttribute('aria-hidden', 'false');
        }

        closeMediaDetail(modal) {
            var panel = modal.querySelector('.pix-media-detail');
            if (!panel) return;
            panel.classList.remove('is-active');
            panel.setAttribute('aria-hidden', 'true');
        }

        toggleLibrarySelection(item, state, modal) {
            if (!item) return;

            var id = String(item.id);
            state.selectedIds = state.selectedIds || [];
            if (state.selectedIds.some(function(selectedId) { return String(selectedId) === id; })) {
                state.selectedIds = state.selectedIds.filter(function(selectedId) {
                    return String(selectedId) !== id;
                });
            } else {
                state.selectedIds.push(id);
            }
            this.updateLibrarySelectionUI(modal, state, id);
        }

        updateLibrarySelectionUI(modal, state, changedId) {
            var selectedIds = state.selectedIds || [];
            var bulkbar = modal.querySelector('.pix-media-bulkbar');
            if (bulkbar) {
                bulkbar.classList.toggle('is-active', !!selectedIds.length);
                bulkbar.querySelector('strong').textContent = selectedIds.length;
            }

            if (!changedId) return;
            var card = modal.querySelector('.pix-media-item[data-id="' + cssEscape(String(changedId)) + '"]');
            if (!card) return;
            var selected = selectedIds.some(function(id) {
                return String(id) === String(changedId);
            });
            card.classList.toggle('is-selected', selected);
            var icon = card.querySelector('.pix-media-check i');
            if (icon) {
                icon.className = selected ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line';
            }
        }

        deleteSelectedLibraryItems(state, modal, reload) {
            var self = this;
            var selectedIds = (state.selectedIds || []).slice();
            var readonlyIds = selectedIds.filter(function(id) {
                var item = (state.items || []).find(function(row) {
                    return String(row.id) === String(id);
                });
                return item && !self.isLibraryItemDeletable(item);
            });
            var ids = selectedIds.filter(function(id) {
                return !readonlyIds.some(function(readonlyId) {
                    return String(readonlyId) === String(id);
                });
            });
            if (!ids.length) {
                pixToast('所选媒体不能在前台删除，可用于批量插入', 'error');
                return;
            }
            if (readonlyIds.length) {
                pixToast('部分媒体不能在前台删除，本次将跳过 ' + readonlyIds.length + ' 个', 'info');
            }

            var runDelete = function() {
                var bulkbar = modal.querySelector('.pix-media-bulkbar');
                if (bulkbar) bulkbar.classList.add('is-loading');
                $.ajax({
                    url: Theme.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: self.options.batchDeleteAction,
                        nonce: self.options.nonce,
                        ids: ids
                    },
                    success: function(response) {
                        if (!response || response.status != 1) {
                            pixToast(response && response.msg ? response.msg : '批量删除失败，请稍后重试', 'error');
                            return;
                        }
                        state.selectedIds = [];
                        var deletedIds = response.deleted || [];
                        if (deletedIds.length) {
                            self.items = self.items.filter(function(row) {
                                return !deletedIds.some(function(id) {
                                    return String(row.attachment_id || row.id) === String(id);
                                });
                            });
                            self.render();
                            self.changed();
                        }
                        var msg = '已删除 ' + (response.deleted_count || 0) + ' 个媒体';
                        if (response.skipped_count) {
                            msg += '，跳过 ' + response.skipped_count + ' 个正在使用或无权删除的媒体';
                        }
                        pixToast(msg, response.deleted_count ? 'success' : 'error');
                        reload();
                    },
                    error: function(xhr) {
                        var msg = '批量删除失败，请稍后重试';
                        if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg;
                        pixToast(msg, 'error');
                    },
                    complete: function() {
                        if (bulkbar) bulkbar.classList.remove('is-loading');
                    }
                });
            };

            if (typeof $.confirm === 'function') {
                $.confirm({
                    title: '批量删除媒体',
                    titleClass: 'moment-delete-title',
                    content: '确认删除选中的 ' + ids.length + ' 个未使用媒体？删除后不可恢复。',
                    boxWidth: '350px',
                    useBootstrap: false,
                    scrollToPreviousElement: false,
                    onOpenBefore: function() {
                        if (this.$el) this.$el.addClass('pix-media-confirm');
                    },
                    buttons: {
                        ok: {
                            text: '确定',
                            btnClass: 'delete-moment-sure',
                            keys: ['enter'],
                            action: runDelete
                        },
                        close: {
                            text: '取消'
                        }
                    }
                });
                return;
            }

            if (window.confirm('确认删除选中的 ' + ids.length + ' 个未使用媒体？删除后不可恢复。')) {
                runDelete();
            }
        }

        previewItem(item) {
            if (!item) return;
            var url = item.preview || item.url || '';
            if (!url) {
                pixToast('暂无可预览的文件地址', 'error');
                return;
            }

            var type = item.kind || item.type || 'file';
            var mime = item.mime || '';
            var title = item.title || item.name || '文件预览';
            var body = '';

            if (type === 'image') {
                body = '<img class="pix-media-preview-image pix-moment-media-preview-image" src="' + escapeAttr(url) + '" alt="">';
            } else if (item.source === 'bili' && item.bvid) {
                body = '<iframe class="pix-media-preview-frame pix-moment-media-preview-frame" src="' + escapeAttr(url) + '" allowfullscreen="true"></iframe>';
            } else if (type === 'video') {
                body = '<video class="pix-moment-media-preview-video" src="' + escapeAttr(url) + '" poster="' + escapeAttr(item.poster || item.thumb || '') + '" controls autoplay playsinline></video>';
            } else if (mime.indexOf('pdf') > -1 || mime.indexOf('text/') === 0 || mime.indexOf('plain') > -1 || /\.(pdf|txt|md|csv|json|log)$/i.test(url)) {
                body = '<iframe class="pix-media-preview-frame pix-moment-media-preview-frame" src="' + escapeAttr(url) + '"></iframe>';
            } else {
                body = '<div class="pix-media-preview-fallback pix-moment-media-preview-fallback">' +
                    '<i class="' + escapeAttr(this.fileIcon(item)) + ' pix-moment-media-preview-fallback-icon"></i>' +
                    '<strong class="pix-moment-media-preview-fallback-title">' + escapeHtml(title) + '</strong>' +
                    '<p class="pix-moment-media-preview-fallback-desc">此文件类型可能无法在浏览器内直接预览，可以在新窗口打开查看。</p>' +
                '</div>';
            }

            var modal = document.createElement('div');
            modal.className = 'pix-media-preview-modal pix-moment-media-preview-modal';
            modal.innerHTML = '<div class="pix-media-preview-panel pix-moment-media-preview-panel is-' + escapeAttr(type) + '">' +
                '<button type="button" class="pix-media-video-close pix-moment-media-preview-close">×</button>' +
                '<div class="pix-media-preview-body pix-moment-media-preview-body">' + body + '</div>' +
                '<div class="pix-media-video-title pix-moment-media-preview-title"><span>' + escapeHtml(title) + '</span><a href="' + escapeAttr(url) + '" target="_blank" rel="noopener">新窗口打开</a></div>' +
            '</div>';
            document.body.appendChild(modal);
            modal.addEventListener('click', function(event) {
                if (event.target === modal || event.target.closest('.pix-media-video-close')) {
                    modal.remove();
                }
            });
        }

        deleteLibraryItem(item, state, modal, card) {
            var self = this;
            if (!item || !item.id) return;
            if (item.used) {
                pixToast('该媒体正在被片刻使用，不能删除', 'error');
                return;
            }
            if (!this.isLibraryItemDeletable(item)) {
                pixToast('该媒体只能插入，不能在前台删除', 'error');
                return;
            }

            var runDelete = function() {
                if (card) card.classList.add('is-deleting');
                $.ajax({
                    url: Theme.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: self.options.deleteAction,
                        nonce: self.options.nonce,
                        id: item.id
                    },
                    success: function(response) {
                        if (!response || response.status != 1) {
                            pixToast(response && response.msg ? response.msg : '删除失败，请稍后重试', 'error');
                            return;
                        }

                        state.items = (state.items || []).filter(function(row) {
                            return String(row.id) !== String(item.id);
                        });
                        self.items = self.items.filter(function(row) {
                            return String(row.attachment_id || row.id) !== String(item.id);
                        });
                        if (!self.items.length) {
                            self.activeKind = '';
                        }
                        self.renderLibraryItems(modal, state);
                        self.render();
                        self.changed();
                        pixToast('媒体文件已删除', 'success');
                    },
                    error: function(xhr) {
                        var msg = '删除失败，请稍后重试';
                        if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg;
                        pixToast(msg, 'error');
                    },
                    complete: function() {
                        if (card) card.classList.remove('is-deleting');
                    }
                });
            };

            if (typeof $.confirm === 'function') {
                $.confirm({
                    title: '删除媒体',
                    titleClass: 'moment-delete-title',
                    content: '媒体文件删除后不可恢复，是否继续？',
                    boxWidth: '350px',
                    useBootstrap: false,
                    scrollToPreviousElement: false,
                    onOpenBefore: function() {
                        if (this.$el) this.$el.addClass('pix-media-confirm');
                    },
                    buttons: {
                        ok: {
                            text: '确定',
                            btnClass: 'delete-moment-sure',
                            keys: ['enter'],
                            action: runDelete
                        },
                        close: {
                            text: '取消'
                        }
                    }
                });
                return;
            }

            if (window.confirm('确认删除这个媒体文件？删除后不可恢复。')) {
                runDelete();
            }
        }

        requestRemoveItem(id) {
            var item = this.items.find(function(row) {
                return String(row.id) === String(id);
            });
            if (!item) return;

            if (item.status === 'uploading') {
                pixToast('附件仍在上传中，请稍后再移除', 'error');
                return;
            }

            item.prevStatus = item.status;
            item.status = 'removing';
            item.removeLeft = null;
            item.removeTimer = null;
            this.render();
            this.changed();
        }

        undoRemoveItem(id) {
            var item = this.items.find(function(row) {
                return String(row.id) === String(id);
            });
            if (!item) return;

            if (item.removeTimer) {
                clearInterval(item.removeTimer);
                item.removeTimer = null;
            }
            item.removeLeft = null;
            item.status = item.prevStatus || 'done';
            item.prevStatus = null;
            this.render();
            this.changed();
        }

        removeItem(id) {
            this.items = this.items.filter(function(item) {
                if (String(item.id) === String(id) && item.removeTimer) {
                    clearInterval(item.removeTimer);
                }
                return String(item.id) !== String(id);
            });
            if (!this.items.filter(function(item) { return item.status !== 'removing'; }).length) this.activeKind = '';
            this.render();
            this.changed();
        }

        activeItems() {
            return this.items.filter(function(item) {
                return item.status !== 'removing';
            });
        }

        hasItems() {
            return this.activeItems().length > 0;
        }

        setItems(items, kind) {
            this.items.forEach(function(item) {
                if (item.removeTimer) {
                    clearInterval(item.removeTimer);
                }
            });
            this.items = (items || []).map(function(item) {
                var base = Object.assign({
                    id: uid(),
                    status: 'done',
                    source: 'edit'
                }, item || {});
                base.id = uid();
                base.status = 'done';
                base.kind = base.kind || base.type || kind || 'file';
                return Object.assign(base, {
                    status: 'done',
                    kind: base.kind
                });
            });
            this.activeKind = kind || (this.items[0] ? (this.items[0].kind || this.items[0].type) : '');
            if (this.panel) {
                this.panel.dataset.activeKind = this.activeKind || '';
            }
            this.syncPanelMode(this.activeKind || '');
            this.syncToolVisibility(this.activeKind || '');
            this.render();
            this.changed();
        }

        getBlockingStatus() {
            var uploading = this.items.some(function(item) {
                return item.status === 'queued' || item.status === 'uploading' || item.posterStatus === 'uploading';
            });
            if (uploading) {
                return {
                    ok: false,
                    message: '附件仍在上传中，请稍后发布'
                };
            }

            var error = this.items.some(function(item) {
                return item.status === 'error' || item.posterStatus === 'error';
            });
            if (error) {
                return {
                    ok: false,
                    message: '请先移除或重新添加上传失败的附件'
                };
            }

            var removing = this.items.some(function(item) {
                return item.status === 'removing';
            });
            if (removing) {
                return {
                    ok: false,
                    message: '附件正在移除确认中，请先等待或撤销'
                };
            }

            return {
                ok: true,
                message: ''
            };
        }

        render() {
            var self = this;
            this.list.innerHTML = this.items.map(function(item) {
                var media = '';
                var isCard = item.kind === 'card';
                var removeButton = '<button type="button" class="pix-uploader-remove pix-moment-attachment-remove' + (isCard ? ' pix-uploader-remove-floating pix-moment-attachment-remove-floating' : '') + '" aria-label="移除附件"><i class="ri-close-line"></i></button>';
                if (item.kind === 'image' || item.type === 'image') {
                    media = '<img class="pix-moment-attachment-media pix-moment-attachment-image" src="' + escapeAttr(item.preview || item.thumb || item.url) + '" alt="">';
                } else if (item.kind === 'card') {
                    media = '<div class="pix-uploader-card-preview pix-moment-attachment-card-preview">' +
                        (item.thumb ? '<img class="pix-moment-attachment-card-cover" src="' + escapeAttr(item.thumb) + '" alt="">' : '<i class="ri-article-line pix-moment-attachment-card-icon"></i>') +
                        '<div class="pix-moment-attachment-card-body"><span class="pix-moment-attachment-title">' + escapeHtml(item.title || item.name || '内容卡片') + '</span><small class="pix-moment-attachment-desc">' + escapeHtml(item.desc || item.url || '') + '</small></div>' +
                    '</div>';
                } else if (item.kind === 'video' && item.source === 'bili') {
                    media = '<div class="pix-uploader-bili-card pix-moment-attachment-bili-card">' +
                        (item.thumb || item.preview ? '<img class="pix-moment-attachment-bili-cover" src="' + escapeAttr(item.thumb || item.preview) + '" alt="">' : '<i class="ri-bilibili-line pix-moment-attachment-bili-icon"></i>') +
                        '<span class="pix-moment-attachment-title">' + escapeHtml(item.name || item.title || 'B站视频') + '</span>' +
                        '<small class="pix-moment-attachment-desc">' + escapeHtml(item.bvid || '') + '</small>' +
                    '</div>';
                } else if (item.kind === 'video') {
                    media = '<div class="pix-uploader-video-info pix-moment-attachment-video-info"><span class="pix-moment-attachment-title">' + escapeHtml(item.name || item.title || '视频') + '</span><small class="pix-moment-attachment-desc">' + escapeHtml((item.mime || 'video') + (item.size ? ' · ' + formatSize(item.size) : '') + (item.posterStatus === 'uploading' ? ' · 正在生成封面' : '')) + '</small></div>' +
                        '<video class="pix-moment-attachment-media pix-moment-attachment-video" src="' + escapeAttr(item.preview || item.url || '') + '" poster="' + escapeAttr(item.poster || item.thumb || '') + '" muted controls playsinline></video>';
                } else {
                    media = '<div class="pix-uploader-file pix-moment-attachment-file"><i class="' + escapeAttr(self.fileIcon(item)) + ' pix-moment-attachment-file-icon"></i><span class="pix-moment-attachment-title">' + escapeHtml(item.name || item.title || '附件') + '</span><small class="pix-moment-attachment-desc">' + escapeHtml(item.mime || item.kind || 'file') + '</small></div>';
                }

                return '<div class="pix-uploader-item pix-moment-attachment-item is-' + escapeAttr(item.status) + ' is-kind-' + escapeAttr(item.kind || item.type || 'file') + ' is-source-' + escapeAttr(item.source || 'local') + '" draggable="' + (item.status === 'removing' ? 'false' : 'true') + '" data-id="' + escapeAttr(item.id) + '">' +
                    '<div class="pix-uploader-preview pix-moment-attachment-preview">' + media + (item.status !== 'error' && (item.preview || item.url) ? '<button type="button" class="pix-uploader-preview-btn pix-moment-attachment-preview-button" aria-label="' + escapeAttr(previewText(item)) + '"><i class="' + escapeAttr(previewIcon(item)) + '"></i></button>' : '') + '</div>' +
                    (isCard ? removeButton : '<div class="pix-uploader-meta pix-moment-attachment-meta"><span class="pix-moment-attachment-name">' + escapeHtml(item.name || item.title || '') + '</span>' + removeButton + '</div>') +
                    (item.status === 'uploading' ? '<div class="pix-uploader-progress pix-moment-attachment-progress" style="--pix-progress:' + (parseInt(item.progress, 10) || 0) + '%"><span style="width:' + (parseInt(item.progress, 10) || 0) + '%"></span></div>' : '') +
                    (item.status === 'error' ? '<div class="pix-uploader-error pix-moment-attachment-error">' + escapeHtml(item.error) + '</div>' : '') +
                    (item.status === 'removing' ? '<div class="pix-uploader-removing pix-moment-attachment-removing"><button type="button" class="pix-uploader-remove-confirm pix-moment-attachment-confirm">确认</button><button type="button" class="pix-uploader-undo pix-moment-attachment-undo">取消</button></div>' : '') +
                '</div>';
            }).join('');

            Array.prototype.slice.call(this.list.querySelectorAll('.pix-uploader-item')).forEach(function(node) {
                node.addEventListener('dragstart', function(event) {
                    if (node.classList.contains('is-removing')) return;
                    self.list.classList.add('is-sorting');
                    node.classList.add('is-dragging');
                    event.dataTransfer.setData('text/plain', node.dataset.id);
                });
                node.addEventListener('dragenter', function(event) {
                    event.preventDefault();
                    self.markSortTarget(node);
                });
                node.addEventListener('dragover', function(event) {
                    event.preventDefault();
                    self.markSortTarget(node);
                });
                node.addEventListener('drop', function(event) {
                    event.preventDefault();
                    self.sort(event.dataTransfer.getData('text/plain'), node.dataset.id);
                    self.clearSortVisual();
                });
                node.addEventListener('dragend', function() {
                    self.clearSortVisual();
                });
                node.addEventListener('pointerdown', function(event) {
                    self.startTouchSort(event, node);
                });
            });
        }

        markSortTarget(node) {
            if (!node || node.classList.contains('is-dragging') || node.classList.contains('is-removing')) return;
            Array.prototype.slice.call(this.list.querySelectorAll('.pix-uploader-item.is-sort-target')).forEach(function(item) {
                if (item !== node) item.classList.remove('is-sort-target');
            });
            node.classList.add('is-sort-target');
        }

        clearSortVisual() {
            if (!this.list) return;
            this.list.classList.remove('is-sorting');
            Array.prototype.slice.call(this.list.querySelectorAll('.is-dragging, .is-sort-target, .is-touch-dragging')).forEach(function(item) {
                item.classList.remove('is-dragging', 'is-sort-target', 'is-touch-dragging');
            });
        }

        startTouchSort(event, node) {
            if (!event.pointerType || event.pointerType === 'mouse') return;
            if (node.classList.contains('is-removing')) return;
            if (event.target.closest('button, input, a, video')) return;
            this.touchSort = {
                fromId: node.dataset.id,
                toId: '',
                startX: event.clientX,
                startY: event.clientY,
                dragging: false,
                node: node
            };
            node.setPointerCapture(event.pointerId);
            node.addEventListener('pointermove', this.onTouchSortMoveBound = this.onTouchSortMove.bind(this));
            node.addEventListener('pointerup', this.onTouchSortEndBound = this.onTouchSortEnd.bind(this));
            node.addEventListener('pointercancel', this.onTouchSortEndBound);
        }

        onTouchSortMove(event) {
            if (!this.touchSort) return;
            var dx = Math.abs(event.clientX - this.touchSort.startX);
            var dy = Math.abs(event.clientY - this.touchSort.startY);
            if (!this.touchSort.dragging && Math.max(dx, dy) < 10) return;
            this.touchSort.dragging = true;
            event.preventDefault();
            this.list.classList.add('is-sorting');
            this.touchSort.node.classList.add('is-touch-dragging');

            var target = document.elementFromPoint(event.clientX, event.clientY);
            target = target ? target.closest('.pix-uploader-item') : null;
            if (!target || !this.list.contains(target) || target.dataset.id === this.touchSort.fromId) return;
            this.touchSort.toId = target.dataset.id;
            this.markSortTarget(target);
        }

        onTouchSortEnd(event) {
            var state = this.touchSort;
            if (state && state.node) {
                state.node.removeEventListener('pointermove', this.onTouchSortMoveBound);
                state.node.removeEventListener('pointerup', this.onTouchSortEndBound);
                state.node.removeEventListener('pointercancel', this.onTouchSortEndBound);
            }
            if (state && state.dragging && state.toId) {
                this.sort(state.fromId, state.toId);
            }
            this.touchSort = null;
            this.clearSortVisual();
        }

        fileIcon(item) {
            var mime = item.mime || '';
            if (mime.indexOf('pdf') > -1) return 'ri-file-pdf-2-line';
            if (mime.indexOf('word') > -1 || mime.indexOf('document') > -1) return 'ri-file-word-2-line';
            if (mime.indexOf('excel') > -1 || mime.indexOf('sheet') > -1) return 'ri-file-excel-2-line';
            if (mime.indexOf('zip') > -1) return 'ri-file-zip-line';
            return 'ri-file-3-line';
        }

        sort(fromId, toId) {
            if (!fromId || !toId || fromId === toId) return;
            var fromIndex = this.items.findIndex(function(item) { return item.id === fromId; });
            var toIndex = this.items.findIndex(function(item) { return String(item.id) === String(toId); });
            if (fromIndex < 0) {
                fromIndex = this.items.findIndex(function(item) { return String(item.id) === String(fromId); });
            }
            if (fromIndex < 0 || toIndex < 0) return;
            var item = this.items.splice(fromIndex, 1)[0];
            this.items.splice(toIndex, 0, item);
            this.render();
            this.changed();
        }

        value() {
            return {
                type: this.activeKind || 'text',
                items: this.items.filter(function(item) {
                    return item.status === 'done';
                }).map(function(item) {
                    return {
                        kind: item.kind || item.type,
                        attachment_id: item.attachment_id || item.id,
                        url: item.url,
                        thumb: item.thumb,
                        poster_id: item.poster_id || 0,
                        poster: item.poster || item.thumb || '',
                        source: item.source,
                        title: item.title || item.name,
                        bvid: item.bvid || '',
                        cover: item.cover || item.thumb || item.preview || '',
                        pid: item.pid || 0,
                        desc: item.desc || ''
                    };
                })
            };
        }

        changed() {
            if (this.panel) {
                var activeCount = this.activeItems().length;
                this.panel.classList.toggle('has-items', !!activeCount);
                if (activeCount) {
                    this.panel.classList.remove('is-collapsed');
                    this.panel.classList.add('is-open');
                    this.panel.dataset.activeKind = this.activeKind || '';
                } else if (!this.panel.matches(':hover')) {
                    this.panel.classList.add('is-collapsed');
                    this.panel.classList.remove('is-open');
                    delete this.panel.dataset.activeKind;
                    this.syncPanelMode('');
                    this.syncToolVisibility('');
                    $('.pix-moment-attach-trigger').removeClass('is-active');
                }
            }
            if (typeof this.options.onChange === 'function') {
                this.options.onChange(this.value(), this);
            }
        }
    }

    window.PixEditor = PixEditor;
    window.PixUploader = PixUploader;

    $(function() {
        $('[data-pix-editor]').each(function() {
            var $el = $(this);
            if ($el.data('pixEditorReady')) return;
            $el.data('pixEditorReady', true);
            var editor = new PixEditor(this, {
                input: $el.data('input') || null,
                placeholder: $el.data('placeholder') || '写点什么...'
            });
            $el.data('pixEditor', editor);
        });

        $('[data-pix-uploader]').each(function() {
            var $el = $(this);
            if ($el.data('pixUploaderReady')) return;
            $el.data('pixUploaderReady', true);
            var uploadSettings = window.Theme && Theme.upload_settings ? Theme.upload_settings : {};
            var compressEnabled = typeof $el.data('compress') !== 'undefined'
                ? String($el.data('compress')) !== 'false'
                : uploadSettings.image_compress_enable !== false;
            var convertWebp = typeof $el.data('webp') !== 'undefined'
                ? String($el.data('webp')) === 'true'
                : uploadSettings.image_convert_webp === true;
            var widthSetting = uploadSettings.image_compress_width || 1920;
            var maxWidth = widthSetting === 'original' ? 999999 : (parseInt(widthSetting, 10) || 1920);
            var quality = parseInt(uploadSettings.image_compress_quality, 10);
            quality = quality ? Math.max(1, Math.min(100, quality)) / 100 : 0.86;

            var uploader = new PixUploader(this, {
                context: $el.data('context') || 'moment_gallery',
                type: $el.data('type') || 'auto',
                limit: parseInt($el.data('limit'), 10) || 9,
                multiple: String($el.data('multiple')) !== 'false',
                accept: $el.data('accept') || '',
                compress: compressEnabled,
                convertWebp: convertWebp,
                maxWidth: maxWidth,
                quality: quality
            });
            $el.data('pixUploader', uploader);
        });

        $(document).on('click', '.pix-moment-attach-trigger', function() {
            var $btn = $(this);
            if ($btn.hasClass('disabled')) {
                pixToast('此圈子未开启该功能', 'error');
                return;
            }
            if ($('.card-wrap .mo-card-item, .card-wrap .card-box').length) {
                pixToast('当前片刻已添加卡片，请先删除卡片后再添加附件', 'error');
                return;
            }
            var uploader = $('#pix-moment-uploader').data('pixUploader');
            if (!uploader) return;
            $('.mo-card-box').slideUp(200);
            $('.mo-card-btn').removeClass('active');
            $('.push-mo-btn').attr('type', 'text');
            $('.pix-moment-attach-trigger').removeClass('is-active');
            $btn.addClass('is-active');
            if (uploader.closeInlineForms) uploader.closeInlineForms();
            uploader.open($btn.data('kind') || '');
        });

        $(document).on('click', '.pix-moment2-close', function() {
            var uploader = $('#pix-moment-uploader').data('pixUploader');
            if (uploader && uploader.items && uploader.items.length) {
                pixToast('请先移除已添加的附件', 'error');
                return;
            }
            $('.pix-moment-attach-trigger').removeClass('is-active');
            $('.mo-card-btn').removeClass('active');
            $('.pix-moment2-panel').addClass('is-collapsed').removeClass('is-open').removeAttr('data-active-kind');
            if (uploader) {
                uploader.activeKind = '';
                if (uploader.closeInlineForms) uploader.closeInlineForms();
            }
            if (uploader.syncPanelMode) uploader.syncPanelMode('');
            if (uploader.syncToolVisibility) uploader.syncToolVisibility('');
        });
    });

})(window, document, jQuery);
