(function (blocks, blockEditor, components, element, i18n) {
    if (!blocks || !blockEditor || !element) {
        return;
    }

    var el = element.createElement;
    var __ = i18n.__;
    var InnerBlocks = blockEditor.InnerBlocks;
    var useBlockProps = blockEditor.useBlockProps;

    blocks.registerBlockType('pixpro/ppo-hide', {
        title: __('隐藏内容', 'b2'),
        description: __('用于输入付费阅读隐藏内容，前台会按文章付费阅读设置自动判断显示。', 'b2'),
        icon: 'hidden',
        category: 'pixpro',
        keywords: [
            __('隐藏内容', 'b2'),
            __('付费阅读', 'b2'),
            __('密码查看', 'b2')
        ],
        supports: {
            html: false,
            reusable: true
        },
        edit: function () {
            var blockProps = useBlockProps({
                className: 'pix-ppo-hide-block-editor'
            });

            return el(
                'div',
                blockProps,
                el(
                    'div',
                    { className: 'pix-ppo-hide-block-editor__header' },
                    el(
                        'span',
                        { className: 'pix-ppo-hide-block-editor__icon dashicons dashicons-hidden' }
                    ),
                    el(
                        'div',
                        { className: 'pix-ppo-hide-block-editor__text' },
                        el('strong', null, __('隐藏内容', 'b2')),
                        el('span', null, __('这里输入的内容会在前台按付费阅读规则锁定。', 'b2'))
                    )
                ),
                el(
                    'div',
                    { className: 'pix-ppo-hide-block-editor__body' },
                    el(InnerBlocks, {
                        templateLock: false,
                        renderAppender: InnerBlocks.ButtonBlockAppender
                    })
                )
            );
        },
        save: function () {
            var blockProps = useBlockProps.save({
                className: 'pix-ppo-hide-block'
            });

            return el(
                'div',
                blockProps,
                el(InnerBlocks.Content)
            );
        }
    });
})(
    window.wp.blocks,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components,
    window.wp.element,
    window.wp.i18n
);
