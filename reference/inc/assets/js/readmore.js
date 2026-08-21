/**
 * ReadMore.js
 * 智能文本截断插件 - 支持按字数截断、展开/收起、不截断链接
 *
 * 用法：
 *   ReadMore.init('.read-more-target', { maxLength: 100 });
 *
 * 或 HTML 属性方式（自动初始化）：
 *   <div class="read-more" data-max-length="100">...</div>
 */

(function (global) {
  'use strict';

  // ─── 默认配置 ───────────────────────────────────────────────────────────────
  var DEFAULTS = {
    maxLength:    100,          // 最多显示的字符数（中英文均按 1 个字符计）
    ellipsis:     '...',        // 省略号
    moreText:     '阅读更多',   // "展开"按钮文字
    lessText:     '收起',       // "收起"按钮文字
    moreClass:    'rm-toggle',  // 按钮 class
    expandedClass:'rm-expanded' // 展开状态 class（加在容器上）
  };

  // ─── 工具函数 ────────────────────────────────────────────────────────────────

  /**
   * 合并选项
   */
  function mergeOptions(defaults, opts) {
    var result = {};
    for (var k in defaults) result[k] = defaults[k];
    if (opts) for (var k in opts) result[k] = opts[k];
    return result;
  }

  /**
   * 获取元素的纯文本长度（忽略 HTML 标签）
   */
  function textLength(html) {
    var tmp = document.createElement('div');
    tmp.innerHTML = html;
    return (tmp.textContent || tmp.innerText || '').length;
  }

  /**
   * 核心：按字数智能截断 HTML，不截断标签/链接
   *
   * 思路：遍历 DOM 节点树，累计文本字符数，
   *       当达到 maxLength 时在文本节点内截断，后续节点丢弃；
   *       途中遇到的元素节点（包括 <a>）作为整体：
   *         - 若该元素的全部文本尚未超限，则保留整体；
   *         - 若已超限，则跳过。
   *
   * @param {Node}   node      当前 DOM 节点（深拷贝的节点）
   * @param {Object} state     { count: 已累计字数 }
   * @param {number} maxLength 最大字数
   * @returns {Node|null}      处理后的节点（null 表示丢弃）
   */
  function truncateNode(node, state, maxLength) {
    if (state.count >= maxLength) return null;

    // 文本节点
    if (node.nodeType === Node.TEXT_NODE) {
      var text = node.textContent;
      var remaining = maxLength - state.count;
      if (text.length <= remaining) {
        state.count += text.length;
        return node.cloneNode(false);
      } else {
        // 截断文本
        var cloned = node.cloneNode(false);
        cloned.textContent = text.slice(0, remaining);
        state.count = maxLength;
        return cloned;
      }
    }

    // 元素节点
    if (node.nodeType === Node.ELEMENT_NODE) {
      var tag = node.tagName.toUpperCase();

      // <a> 标签及其他内联元素：作为整体处理，避免在链接中间截断
      var isLink = (tag === 'A');

      if (isLink) {
        // 计算该链接的总文本长度
        var linkTextLen = (node.textContent || '').length;
        if (state.count + linkTextLen > maxLength) {
          // 链接会超限 → 整体丢弃，避免截断链接
          return null;
        }
        // 整体保留
        state.count += linkTextLen;
        return node.cloneNode(true);
      }

      // 其他元素：递归处理子节点
      var clonedEl = node.cloneNode(false); // 只克隆标签，不克隆子节点
      var children = node.childNodes;
      for (var i = 0; i < children.length; i++) {
        if (state.count >= maxLength) break;
        var child = truncateNode(children[i], state, maxLength);
        if (child !== null) clonedEl.appendChild(child);
      }
      return clonedEl;
    }

    // 其他节点（注释等）：原样保留
    return node.cloneNode(false);
  }

  /**
   * 生成截断后的 HTML 字符串
   */
  function getTruncatedHTML(container, maxLength) {
    var state = { count: 0 };
    var wrapper = document.createElement('div');
    var children = container.childNodes;
    for (var i = 0; i < children.length; i++) {
      if (state.count >= maxLength) break;
      var truncated = truncateNode(children[i], state, maxLength);
      if (truncated !== null) wrapper.appendChild(truncated);
    }
    return wrapper.innerHTML;
  }

  // ─── 初始化单个元素 ─────────────────────────────────────────────────────────

  function initElement(el, opts) {
    // 已初始化过则跳过，防止 Ajax 重复调用时重复处理
    if (el.getAttribute('data-rm-done') === '1') return;
    el.setAttribute('data-rm-done', '1');

    var options = mergeOptions(DEFAULTS, opts);

    // 从 data 属性读取覆盖配置
    if (el.dataset) {
      if (el.dataset.maxLength)  options.maxLength  = parseInt(el.dataset.maxLength, 10);
      if (el.dataset.moreText)   options.moreText   = el.dataset.moreText;
      if (el.dataset.lessText)   options.lessText   = el.dataset.lessText;
      if (el.dataset.ellipsis)   options.ellipsis   = el.dataset.ellipsis;
    }

    var fullHTML = el.innerHTML;
    var fullLen  = textLength(fullHTML);

    // 文本未超长，无需处理
    if (fullLen <= options.maxLength) return;

    // 生成截断 HTML
    var shortHTML = getTruncatedHTML(el, options.maxLength);

    // ── 构造"展开/收起"按钮 ──
    var toggle = document.createElement('span');
    toggle.className = options.moreClass;
    toggle.setAttribute('role', 'button');
    toggle.setAttribute('tabindex', '0');
    toggle.style.cssText = 'cursor:pointer;color:#1e68ff;user-select:none;';

    var ellipsisSpan = document.createElement('span');
    ellipsisSpan.className = 'rm-ellipsis';
    ellipsisSpan.textContent = options.ellipsis;

    var btnSpan = document.createElement('span');
    btnSpan.className = 'rm-btn';
    btnSpan.textContent = options.moreText;
    btnSpan.style.cssText = 'margin-left:4px;';

    toggle.appendChild(ellipsisSpan);
    toggle.appendChild(btnSpan);

    // 初始状态：显示截断内容
    el.innerHTML = shortHTML;
    el.appendChild(toggle);

    var expanded = false;

    function expand() {
      expanded = true;
      el.classList.add(options.expandedClass);

      // 写入完整内容，再追加"收起"按钮
      el.innerHTML = fullHTML;

      var collapseToggle = document.createElement('span');
      collapseToggle.className = options.moreClass;
      collapseToggle.setAttribute('role', 'button');
      collapseToggle.setAttribute('tabindex', '0');
      collapseToggle.style.cssText = 'cursor:pointer;color:#1e68ff;user-select:none;margin-left:4px;';
      collapseToggle.textContent = options.lessText;
      collapseToggle.addEventListener('click', collapse);
      collapseToggle.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') collapse();
      });

      el.appendChild(document.createTextNode(' '));
      el.appendChild(collapseToggle);
    }

    function collapse() {
      expanded = false;
      el.classList.remove(options.expandedClass);
      el.innerHTML = shortHTML;
      el.appendChild(toggle);
    }

    toggle.addEventListener('click', expand);
    toggle.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') expand();
    });
  }

  // ─── 公开 API ────────────────────────────────────────────────────────────────

  var ReadMore = {
    /**
     * 初始化
     * @param {string|NodeList|Element} selector  CSS 选择器 / NodeList / 单个元素
     * @param {Object} [opts]  配置项
     */
    init: function (selector, opts) {
      var elements;
      if (typeof selector === 'string') {
        elements = document.querySelectorAll(selector);
      } else if (selector && selector.nodeType) {
        elements = [selector];
      } else {
        elements = selector;
      }
      for (var i = 0; i < elements.length; i++) {
        initElement(elements[i], opts);
      }
    }
  };

  // ─── 自动初始化（DOMContentLoaded 后扫描 data-max-length 属性） ────────────
  function autoInit() {
    var els = document.querySelectorAll('[data-max-length]');
    for (var i = 0; i < els.length; i++) {
      initElement(els[i], {});
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoInit);
  } else {
    autoInit();
  }

  // ─── 导出 ────────────────────────────────────────────────────────────────────
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = ReadMore;          // CommonJS
  } else if (typeof define === 'function' && define.amd) {
    define([], function () { return ReadMore; }); // AMD
  } else {
    global.ReadMore = ReadMore;         // 全局变量
  }

}(typeof window !== 'undefined' ? window : this));
