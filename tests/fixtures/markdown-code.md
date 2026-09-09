这篇测试文章展示代码围栏、语言标识、缩进、长行和复制操作。代码块不应被 Markdown 或 HTML 的二次处理破坏。

## JavaScript：保留原始字符

```javascript
function formatArticle(article) {
    const title = article.title ?? '未命名文章';
    const visible = article.count < 10 && article.published;
    return { title, visible, label: `文章：${title}` };
}

// 复制后引号、反引号、尖括号、缩进应保持一致。
console.log(formatArticle({ title: '排版测试', count: 3, published: true }));
```

### 代码后的解释

这里的 `article.count < 10` 是比较表达式，不是 HTML 标签。普通正文应与代码块拉开层次，代码中的字符串、关键字和注释则通过颜色区分。

## CSS：检查缩进与颜色

```css
.article {
    max-width: 720px;
    margin-inline: auto;
    color: var(--color-glintide-ink);
}

.article h2 {
    margin-block: 32px 12px;
    font-size: 18px;
    font-weight: 700;
}
```

## HTML：标签只显示、不执行

```html
<article class="post">
    <h2>一个清楚的章节标题</h2>
    <p>保留 <strong>重点</strong> 与普通文字的区别。</p>
    <button type="button">示例按钮，不应成为真实控件</button>
</article>
```

## 长行与普通文本

下面的长行应在代码区域内横向滚动，不让整个弹窗变宽。

```text
https://example.com/articles/a-very-long-resource-name?first=preserve-original-code&second=horizontal-scroll-only&third=do-not-resize-the-dialog&fourth=keep-comments-visible&fifth=copy-the-complete-line-without-truncation
```

没有语言标识的代码块也要保留空格和换行：

```
第一层
    第二层
        第三层

空行之后继续。
```

### 缩进式代码

    const answer = 42;
    console.log(answer);

## 再次验证列表

1. 点击代码块右上角的复制图标。
2. 粘贴到文本编辑器，核对缩进与符号。
3. 缩小窗口，确认仅代码区域横向滚动。
4. 关闭弹窗，再打开另一篇文章，确认没有重复工具栏。

本文是代码显示测试内容，不属于正式发布的技术教程。
