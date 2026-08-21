(function($) {
    
    tinymce.PluginManager.add('ppo_hide', function( editor, url ) {
        var url = ppo_admin_global.theme_url;
        editor.addButton( 'ppo_hide', {
            text: '',
            //icon: 'icon ri-eye-off-line',
            image: url+'/img/icon/hide.png',
            tooltip: '隐藏内容',
            onclick: function() {
                var selectedText = editor.selection.getContent({ format: 'html' });
                

                var $existingHideContent = $(editor.getBody()).find('.hide-content');
                if ($existingHideContent.length) {
                    alert('已经存在隐藏内容，请先删除现有的隐藏内容再操作！');
                    return;
                }
                // 检查是否有选中的文本
                if (selectedText && selectedText.trim() !== '') {
                    // 给选中文本添加标签包裹
                    var wrappedText = '<div class="hide-content" contenteditable="false"><div class="hide-title">隐藏内容</div><p class="qt-hide">[ppo_hide]</p><div id="cet" contenteditable="true" style="padding:0 5px"><p>' + selectedText + '</p></div><p class="qt-hide">[/ppo_hide]</p></div>';
                    
                    // 将更新后的内容插入到编辑器中
                    editor.selection.setContent(wrappedText);
                } else {
                    var wrappedText = '<div class="hide-content" contenteditable="false"><div class="hide-title">隐藏内容</div><p class="qt-hide">[ppo_hide]</p><div id="cet" contenteditable="true" style="padding:0 5px"><p></p></div><p class="qt-hide">[/ppo_hide]</p></div>';
                    
                    // 将更新后的内容插入到编辑器中
                    editor.selection.setContent(wrappedText);
                }
            }
        });
    });
})(jQuery);