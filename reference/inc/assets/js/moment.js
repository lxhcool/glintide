var current_mo_data = {};

function pix_moment_plain_text(value){
    var text = String(value || '');
    text = text.replace(/\[link\s+([^\]]*)\]/gi, function(match, attrs){
        var title = '';
        attrs.replace(/(\w+)=(["'])(.*?)\2/g, function(all, key, quote, val){
            if(key === 't') title = val;
            return all;
        });
        return title || '';
    });
    text = text.replace(/\[s=[^\]]+\]/gi, '表');
    var node = document.createElement('div');
    node.innerHTML = text;
    return (node.textContent || node.innerText || '').replace(/\u00a0/g, '').trim();
}

function pix_moment_refresh_runtime($scope, uiTarget) {
    var $target = $scope && $scope.length ? $scope : $(document);

    if (typeof window.refresh_user_runtime === 'function') {
        window.refresh_user_runtime($target);
    } else {
        if (window.ReadMore && typeof ReadMore.init === 'function') {
            ReadMore.init('.ppo-rich-text-content');
        }
        if (window.lazyLoadInstance && typeof lazyLoadInstance.update === 'function') {
            lazyLoadInstance.update();
        }
        if ($.fn.timeago) {
            $target.find('time.timeago').timeago();
        }
    }

    if (window.HSStaticMethods && typeof window.HSStaticMethods.autoInit === 'function') {
        window.HSStaticMethods.autoInit();
    }
}

function pix_moment_close_dropdown(selector) {
    var target = document.querySelector(selector);

    if (!target) {
        return;
    }

    var wrapper = target.closest('.hs-dropdown');

    if (wrapper && window.HSDropdown && typeof window.HSDropdown.getInstance === 'function') {
        var dropdown = window.HSDropdown.getInstance(wrapper, true);
        if (dropdown && dropdown.element && typeof dropdown.element.close === 'function') {
            dropdown.element.close();
            return;
        }
    }

    target.classList.add('hidden');
    target.classList.remove('block');

    if (wrapper) {
        wrapper.classList.remove('open');
        var toggle = wrapper.querySelector('.hs-dropdown-toggle');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    }
}

function pix_moment_close_card_action_menus(exceptWrapper) {
    $('.pix-moment-card-action-menu').each(function(){
        var target = this;
        var wrapper = target.closest('.hs-dropdown');

        if (exceptWrapper && wrapper === exceptWrapper) {
            return;
        }

        if (wrapper && window.HSDropdown && typeof window.HSDropdown.getInstance === 'function') {
            var dropdown = window.HSDropdown.getInstance(wrapper, true);
            if (dropdown && dropdown.element && typeof dropdown.element.close === 'function') {
                dropdown.element.close();
            }
        }

        target.classList.add('hidden');
        target.classList.remove('block');

        if (wrapper) {
            wrapper.classList.remove('open');
            var toggle = wrapper.querySelector('.hs-dropdown-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        }
    });
}

function pix_moment_close_quick_actions($except) {
    $('.pix-moment-card-quick-actions.is-open')
        .not($except || $())
        .removeClass('is-open')
        .find('.pix-moment-card-quick-toggle')
        .attr('aria-expanded', 'false');
}

function pix_moment_show_modal(target) {
    var $modal = $(target);

    if (!$modal.length) {
        return $modal;
    }

    if (typeof ppo_show_modal === 'function') {
        ppo_show_modal($modal);
        return $modal;
    }

    if ($modal.hasClass('pix-hs-modal') && $modal.parent().get(0) !== document.body) {
        $modal.appendTo(document.body);
    }

    $modal.removeClass('hidden').addClass('open opened').attr('aria-modal', 'true');
    $('body').addClass('hs-overlay-body-open');

    return $modal;
}

function pix_moment_hide_modal(target) {
    var $modal = $(target);

    if (!$modal.length) {
        return;
    }

    if (typeof ppo_hide_modal === 'function') {
        ppo_hide_modal($modal);
        return;
    }

    $modal.addClass('hidden').removeClass('open opened').removeAttr('aria-modal');
    if (!$('.pix-hs-modal.open, .pix-hs-modal.opened').length) {
        $('body').removeClass('hs-overlay-body-open');
    }
}

function pix_moment_get_delete_modal() {
    var modalId = 'pix-moment-delete-modal';
    var $modal = $('#' + modalId);

    if (!$modal.length) {
        var modal = '<div id="' + modalId + '" class="pix-modern pix-modal pix-hs-modal pix-moment-delete-modal hidden" role="dialog" tabindex="-1" aria-labelledby="' + modalId + '-title">' +
            '<div class="pix-modal-dialog">' +
                '<div class="pix-modal-panel pix-moment-delete-modal-panel">' +
                    '<button type="button" class="pix-modal-close pix-moment-delete-cancel" aria-label="关闭"><i class="ri-close-line"></i></button>' +
                    '<div id="' + modalId + '-title" class="pix-modal-title">删除片刻</div>' +
                    '<p class="pix-modal-text">片刻删除后将移至回收站，是否继续？</p>' +
                    '<div class="pix-modal-footer pix-moment-delete-modal-footer">' +
                        '<button type="button" class="pix-modal-button pix-moment-delete-cancel">取消</button>' +
                        '<button type="button" class="pix-modal-button pix-modal-button-primary pix-moment-delete-confirm">确定</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

        $('body').append(modal);
        $modal = $('#' + modalId);
    }

    return $modal;
}

function pix_moment_open_delete_modal(context) {
    var $modal = pix_moment_get_delete_modal();
    $modal.data('deleteContext', context || {});
    $modal.find('.pix-moment-delete-confirm').prop('disabled', false);

    pix_moment_show_modal($modal);
}

function pix_moment_close_delete_modal() {
    pix_moment_hide_modal('#pix-moment-delete-modal');
}

function pix_moment_get_confirm_modal() {
    var modalId = 'pix-moment-confirm-modal';
    var $modal = $('#' + modalId);

    if (!$modal.length) {
        var modal = '<div id="' + modalId + '" class="pix-modern pix-modal pix-hs-modal pix-moment-confirm-modal hidden" role="dialog" tabindex="-1" aria-labelledby="' + modalId + '-title">' +
            '<div class="pix-modal-dialog">' +
                '<div class="pix-modal-panel pix-moment-confirm-modal-panel">' +
                    '<button type="button" class="pix-modal-close pix-moment-confirm-cancel" aria-label="关闭"><i class="ri-close-line"></i></button>' +
                    '<div id="' + modalId + '-title" class="pix-modal-title pix-moment-confirm-title"></div>' +
                    '<div class="pix-modal-text pix-moment-confirm-text"></div>' +
                    '<div class="pix-modal-footer pix-moment-confirm-modal-footer">' +
                        '<button type="button" class="pix-modal-button pix-moment-confirm-cancel">取消</button>' +
                        '<button type="button" class="pix-modal-button pix-modal-button-primary pix-moment-confirm-submit">确定</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

        $('body').append(modal);
        $modal = $('#' + modalId);
    }

    return $modal;
}

function pix_moment_close_confirm_modal() {
    pix_moment_hide_modal('#pix-moment-confirm-modal');
}

function pix_moment_open_confirm_modal(options) {
    options = options || {};
    var $modal = pix_moment_get_confirm_modal();
    var $submit = $modal.find('.pix-moment-confirm-submit');

    $modal.find('.pix-moment-confirm-title').text(options.title || '确认操作');
    if (options.contentHtml) {
        $modal.find('.pix-moment-confirm-text').html(options.contentHtml);
    } else {
        $modal.find('.pix-moment-confirm-text').text(options.content || '');
    }
    $submit.text(options.confirmText || '确定').prop('disabled', false);
    $submit.toggleClass('pix-modal-button-danger', options.intent === 'danger');

    $modal.find('.pix-moment-confirm-cancel').off('click.pixMomentConfirm').on('click.pixMomentConfirm', function(){
        pix_moment_close_confirm_modal();
    });

    $submit.off('click.pixMomentConfirm').on('click.pixMomentConfirm', function(){
        if (typeof options.onConfirm === 'function') {
            var result = options.onConfirm($modal);
            if (result === false) {
                return;
            }
        }
        pix_moment_close_confirm_modal();
    });

    pix_moment_show_modal($modal);
}

function pix_moment_get_pay_modal() {
    var modalId = 'ppo-pay-modal';
    var $modal = $('#' + modalId);

    if (!$modal.length) {
        var modal = '<div id="' + modalId + '" class="pix-modern pix-modal pix-hs-modal pix-moment-pay-modal hidden" role="dialog" tabindex="-1" aria-labelledby="' + modalId + '-title">' +
            '<div class="pix-modal-dialog pix-moment-pay-modal-dialog">' +
                '<div class="pix-modal-panel ppo-pay-modal pix-moment-pay-modal-panel">' +
                    '<div id="' + modalId + '-title" class="screen-reader-text">购买结算</div>' +
                    '<div class="inner"></div>' +
                    '<button class="pix-modal-close pix-moment-pay-modal-close" type="button" aria-label="关闭"><i class="ri-close-line"></i></button>' +
                '</div>' +
            '</div>' +
        '</div>';

        $('body').append(modal);
        $modal = $('#' + modalId);
    }

    return $modal;
}

function pix_moment_open_pay_modal() {
    var $modal = pix_moment_get_pay_modal();

    pix_moment_show_modal($modal);

    return $modal;
}

function pix_moment_close_pay_modal() {
    pix_moment_hide_modal('#ppo-pay-modal');
}

$('body').on('click', '.pix-moment-pay-modal-close', function(){
    pix_moment_close_pay_modal();
});

var pix_moment_mobile_compose_state = {
    active: false,
    placeholder: null
};

function pix_moment_is_mobile_compose_view(){
    return window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
}

function pix_moment_open_mobile_compose(){
    var $shell = $('.pix-moment-mobile-compose');
    var $body = $shell.find('.pix-moment-mobile-compose-body');
    var $composer = $('.mo-push-wrap.pix-moment-composer').first();

    if(!$shell.length || !$body.length || !$composer.length || !pix_moment_is_mobile_compose_view()){
        return;
    }

    if(!pix_moment_mobile_compose_state.placeholder){
        pix_moment_mobile_compose_state.placeholder = $('<div class="pix-moment-mobile-compose-placeholder" aria-hidden="true"></div>');
        $composer.before(pix_moment_mobile_compose_state.placeholder);
    }

    $body.append($composer);
    pix_moment_mobile_compose_state.active = true;
    $shell.addClass('is-open').attr('aria-hidden','false');
    $('body').addClass('pix-moment-mobile-compose-open');

    window.setTimeout(function(){
        $('#moment-title').trigger('focus');
    }, 120);
}

function pix_moment_close_mobile_compose(){
    var $shell = $('.pix-moment-mobile-compose');
    var $composer = $shell.find('.mo-push-wrap.pix-moment-composer');
    var $placeholder = pix_moment_mobile_compose_state.placeholder;

    if($composer.length && $placeholder && $placeholder.length){
        $placeholder.before($composer);
        $placeholder.remove();
    }

    pix_moment_mobile_compose_state.placeholder = null;
    pix_moment_mobile_compose_state.active = false;
    $shell.removeClass('is-open').attr('aria-hidden','true');
    $('body').removeClass('pix-moment-mobile-compose-open');
}

$('body').on('click', '.pix-moment-mobile-compose-trigger', function(){
    pix_moment_open_mobile_compose();
});

$('body').on('click', '.pix-moment-mobile-compose-close', function(){
    pix_moment_close_mobile_compose();
});

$('body').on('click', '.pix-moment-mobile-compose-submit', function(){
    $('.pix-moment-mobile-compose .push-mo-btn').trigger('click');
});

$('body').on('click', '.pix-moment-edit-mobile-submit', function(){
    $('.pix-moment-edit-page .push-mo-btn').trigger('click');
});

$('body').on('click', '.pix-moment-card-quick-toggle', function(event){
    event.preventDefault();
    event.stopPropagation();

    var $wrap = $(this).closest('.pix-moment-card-quick-actions');
    var isOpen = $wrap.hasClass('is-open');

    pix_moment_close_card_action_menus();
    pix_moment_close_quick_actions($wrap);

    $wrap.toggleClass('is-open', !isOpen);
    $(this).attr('aria-expanded', isOpen ? 'false' : 'true');
});

$('body').on('click', '.pix-moment-card-action-more, .mo-edit-btn', function(){
    var wrapper = this.closest('.pix-moment-card-action-menu-wrap');
    pix_moment_close_quick_actions();
    pix_moment_close_card_action_menus(wrapper);
});

$(document).on('click', function(){
    pix_moment_close_quick_actions();
});

$(document).on('keydown', function(event){
    if(event.key === 'Escape' && pix_moment_mobile_compose_state.active){
        pix_moment_close_mobile_compose();
    }

    if(event.key === 'Escape'){
        pix_moment_close_quick_actions();
    }
});

$(window).on('resize orientationchange', function(){
    if(pix_moment_mobile_compose_state.active && !pix_moment_is_mobile_compose_view()){
        pix_moment_close_mobile_compose();
    }
});

$('body').on('click', '.pix-moment-scroll-top', function(event){
    var target = document.querySelector(this.getAttribute('href') || '#page');

    if (!target) {
        return;
    }

    event.preventDefault();
    target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
});

$(document).ready(function() {
	
    $('#moment_content').on('blur keyup input', function(){
        var editor = document.querySelector('[data-pix-editor][data-input="moment_content"]');
        var text = editor ? (editor.textContent || '').replace(/\u00a0/g, '').trim() : pix_moment_plain_text($(this).val());
        var emojiCount = editor ? editor.querySelectorAll('.pix-editor-emoji').length : 0;
        var count = text.length + emojiCount;
        $('.mo-num').text(count);
    });


});

function has_pix_moment2_attachments(){
    var uploader = $('#pix-moment-uploader').data('pixUploader');
    if(!uploader || !uploader.items){
        return false;
    }
    if(typeof uploader.hasItems === 'function'){
        return uploader.hasItems();
    }
    return uploader.items.some(function(item){
        return item.status !== 'removing';
    });
}

function has_moment_cards(){
    return $('.card-wrap .mo-card-item, .card-wrap .card-box').length > 0;
}

function pix_moment_form_scope($el){
    var $scope = $el.closest('.mo-push-wrap');
    return $scope.length ? $scope : $(document);
}

// 片刻类型
$(document).on('click','.mo-btn',function(){
    var t = $(this);
    var $scope = pix_moment_form_scope(t);
    var type = t.attr('data');
    var box = $scope.find('.mo-'+type+'-box');

    if(t.hasClass('disabled')){
        toastfy('此圈子未开启该功能','error');
        return;
    }

    if(type == 'card'){
        var uploader = $('#pix-moment-uploader').data('pixUploader');
        if(uploader && typeof uploader.openCardForm === 'function'){
            if(uploader.activeKind && uploader.activeKind !== 'card'){
                toastfy('当前片刻已添加其他附件，请先移除附件后再添加卡片','error');
                return;
            }
            $('.mo-toggle-box').slideUp(200);
            $('.mo-btn').removeClass('active');
            t.addClass('active');
            $('.pix-moment-attach-trigger').removeClass('is-active');
            uploader.openCardForm();
            $scope.find('.push-mo-btn').attr('type','text');
            return;
        }
        if(has_pix_moment2_attachments()){
            toastfy('当前片刻已添加附件，请先移除附件后再添加卡片','error');
            return;
        }
    }

    if(t.hasClass('active')){
        box.slideUp(200);
        t.removeClass('active');
        $scope.find('.push-mo-btn').attr('type','text');
    } else {
        t.addClass('active');
        t.siblings().removeClass('active');
        box.siblings('.mo-toggle-box').slideUp(200);
        box.slideDown(200);
        $scope.find('.push-mo-btn').attr('type',type);
    }

});

$(document).on('click','.cancel-toggle-box i',function(){
    var t = $(this);
    var $scope = pix_moment_form_scope(t);
    $scope.find('.push-mo-btn').attr('type','text');
    t.parents('.mo-toggle-box').slideUp(200);
    $scope.find('.mo-btn').removeClass('active');
});

function pix_moment_content_textarea_min_height(textarea){
    return $(textarea).closest('.pix-moment-mobile-compose-body, .pix-moment-edit-page').length ? 150 : 40;
}

$(document).on('input propertychange','textarea#moment_content',function(event){ 
    // 自动调整textarea的高度
    var minHeight = pix_moment_content_textarea_min_height(this);
    this.style.height = minHeight + 'px';
    this.style.height = Math.max(this.scrollHeight, minHeight) + 'px';
 
});

function apply_current_moment_data(data){
    current_mo_data = data || {};

    if(current_mo_data.joined == false){
        toastfy('您没有加入该圈子！','error');
        $('.mo-tool-nav .left-tool').css('opacity',1);
        return false;
    }

    var user_power = current_mo_data.mo_user_power || {};
    $.each(user_power, function(i,val){
        if(val){
            $('.mo-'+i+'-btn').removeClass('disabled');
        } else {
            $('.mo-'+i+'-btn').addClass('disabled');
        }
    });

    if(current_mo_data.gallery_link){
        $('.add-gallery-link').show();
        $('.pix-uploader-external').show();
    } else {
        $('.add-gallery-link').hide();
        $('.pix-uploader-external').hide();
    }

    $('.mo-tool-nav .left-tool').css('opacity',1);
    return true;
}

// 圈子选择
$(document).on('click','.mo-circle-item',function(){
    var t = $(this);
    var $scope = pix_moment_form_scope(t);
    var cat_name = t.find('.title').text(),
        cat_id = t.attr('catid'),
        cat_img = t.find('.left').html(),
        btn = $scope.find('.mo-cir-btn');
        
        $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            data: {
                action:'get_current_mo_data',
                security: Theme.moment_nonce,
                term_id:cat_id
            },
            beforeSend: function () {
            $('.mo-tool-nav .left-tool').css('opacity',0.2);
        },
        success: function(data){
            if(apply_current_moment_data(data) === false){
                return false;
            }

            btn.find('span').text(cat_name);
            btn.find('.cat-thum').html(cat_img);
            
            btn.addClass('active is-selected');
            $scope.find('.push-mo-btn').attr('catid',cat_id);
            pix_moment_close_dropdown('.circle-drop');

            
        }
        
    });


        
});

// 全部圈子页分组导航
$(document).on('click', '.pix-moment-all-nav-link', function(){
    var $link = $(this),
        targetId = $link.attr('data-target'),
        $target = targetId ? $('#' + targetId) : $(),
        $nav = $link.closest('.pix-moment-all-nav');

    if(!$target.length){
        return;
    }

    $('.pix-moment-all-nav-link').removeClass('is-active');
    $link.addClass('is-active');

    var offset = 76;
    if($('.pix-moment-all-page').length && window.innerWidth <= 767){
        offset = 92;
    }

    $('html, body').stop().animate({
        scrollTop: Math.max(0, $target.offset().top - offset)
    }, 280);

    if($nav.length && $nav[0].scrollWidth > $nav.innerWidth()){
        var navLeft = $nav.scrollLeft(),
            linkLeft = $link.position().left,
            linkWidth = $link.outerWidth(),
            navWidth = $nav.innerWidth(),
            targetLeft = navLeft + linkLeft - ((navWidth - linkWidth) / 2);

        $nav.stop().animate({ scrollLeft: Math.max(0, targetLeft) }, 220);
    }
});


function get_pix_moment2_payload(){
    var uploader = $('#pix-moment-uploader').data('pixUploader');
    if(!uploader || typeof uploader.value !== 'function'){
        return null;
    }

    var value = uploader.value();
    if(!value || !value.items || !value.items.length){
        return null;
    }

    var first = value.items[0],
        kind = first.kind || value.type;

    if(kind == 'image'){
        return {
            type: 'gallery',
            data: value.items.map(function(item){
                return {
                    src: item.url,
                    thum: item.thumb || item.url,
                    source: item.source || 'upload',
                    attach_id: item.attachment_id || 0
                };
            })
        };
    }

    if(kind == 'video'){
        if(first.source === 'bili'){
            return {
                type: 'video',
                data: [{
                    bvid: first.bvid,
                    type: 'bili'
                }]
            };
        }

        return {
            type: 'video',
            data: [{
                attach_id: first.attachment_id || first.id || 0,
                cover: first.poster_id || 0,
                cover_url: first.poster || '',
                type: 'local'
            }]
        };
    }

    if(kind == 'card'){
        return {
            type: 'card',
            data: value.items.map(function(item){
                return item.pid || item.attachment_id || item.id;
            }).filter(Boolean)
        };
    }

    return {
        type: 'file',
        data: value.items.map(function(item){
            return {
                attach_id: item.attachment_id || item.id || 0,
                file_title: item.title || '附件'
            };
        })
    };
}

function get_pix_moment2_ready_error(){
    var uploader = $('#pix-moment-uploader').data('pixUploader');
    if(!uploader || !has_pix_moment2_attachments()){
        return '';
    }
    if(typeof uploader.getBlockingStatus === 'function'){
        var status = uploader.getBlockingStatus();
        return status && !status.ok ? status.message : '';
    }

    var hasBusy = uploader.items.some(function(item){
        return item.status === 'queued' || item.status === 'uploading';
    });
    if(hasBusy){
        return '附件仍在上传中，请稍后发布';
    }
    return '';
}

// 发布片刻
$(document).on('click','.push-mo-btn',function(){
    var t = $(this),
        $scope = pix_moment_form_scope(t),
        content = $("#moment_content").val(),
        title = $("#moment-title").val(),
        moment_type = t.attr('type'),
        tagid = t.attr('tagid'),
        action_type = t.attr('action'),
        pid = t.attr('pid') ? t.attr('pid') : 0;

        if(t.hasClass('protect')){
            toastfy('操作过快！','error');
            return;
        }

        var catid = t.attr('catid') || $scope.find('.mo-cir-btn').attr('catid') || Theme.tid;


        if(!catid){
            toastfy('请选择圈子！','error');
            return;
        }

        if(has_pix_moment2_attachments() && has_moment_cards()){
            toastfy('卡片和上传附件不能同时发布，请保留其中一种','error');
            return;
        }

        var uploadError = get_pix_moment2_ready_error();
        if(uploadError){
            toastfy(uploadError,'error');
            return;
        }

        var pixPayload = get_pix_moment2_payload();
        if(pixPayload){
            moment_type = pixPayload.type;
            moment_data = pixPayload.data;
        } else {
            if(get_moment_error(moment_type) === false){
                moment_type = 'text';
            }
            get_moment_data(moment_type);
        }

        var momentSubmitEl = t.get(0);
        var submitMoment = function(logincaptchaPayload) {
            $.ajax({
                type: "post",
                url:Theme.ajaxurl,
                dataType:  'json',
                data: {
                    action: 'push_moment',
                    'security': Theme.moment_nonce,
                    content:content,
                    catid:catid,
                    tagid:tagid,
                    moment_data:moment_data,
                    title:title,
                    moment_type:moment_type,
                    action_type:action_type,
                    pid:pid,
                    pix_guard: $('#pix_guard').val() || '',
                    logincaptcha: logincaptchaPayload ? JSON.stringify(logincaptchaPayload) : ''
                },	
    
                beforeSend: function () {
                    t.addClass('is-loading protect').attr('aria-busy','true').data('old-text', t.text()).html('<i class="ri-loader-4-line"></i><span>发布中</span>');
                },
                success: function(data){
                    if(data.status == '0') {
                        toastfy(data.msg,'error');
                        return false;
                    } 
                    toastfy(data.msg || '发布成功！','success');

                    if(action_type === 'edit'){
                        window.setTimeout(function(){
                            window.location.href = data.post_status === 'pending'
                                ? (data.term_url || data.url || document.referrer || window.location.href)
                                : (data.url || document.referrer || window.location.href);
                        }, 650);
                        return;
                    }

                    if(data.html && $('#moment-item').length){
                        $('#moment-item .no-moment').remove();
                        var $momentHtml = $(data.html);
                        $('#moment-item').prepend($momentHtml);
                        pix_moment_refresh_runtime($momentHtml, $('#moment-item')[0]);
                    } else if(data.post_status === 'pending') {
                        toastfy('审核通过后会显示在列表中','info');
                    } else {
                        window.setTimeout(function(){
                            window.location.reload();
                        }, 500);
                    }

                    reset_moment_form_after_publish();

                    if(pix_moment_mobile_compose_state.active){
                        pix_moment_close_mobile_compose();
                    }
                    
                
                },
                error: function(){
                    toastfy('发布失败，请稍后重试','error');
                },
                complete: function(){
                    var oldText = t.data('old-text') || '发布';
                    t.removeClass('is-loading protect').removeAttr('aria-busy').text(oldText);
                    if (typeof window.pixcapClearContentVerification === 'function') {
                        window.pixcapClearContentVerification('moment', momentSubmitEl, 1800);
                    }
                }
            });
        };

        if ((window.Theme && Theme.content_protect_type) === 'pixcap' && typeof window.pixcapVerifyContent === 'function') {
            window.pixcapVerifyContent('moment', momentSubmitEl).then(function(payload) {
                submitMoment(payload);
            }).catch(function(error) {
                toastfy((error && error.message) || '验证失败，请重试', 'error');
            });
        } else {
            submitMoment(null);
        }

    // 初始化 ReadMore 文本截断
    if (window.ReadMore) {
        ReadMore.init('.ppo-rich-text-content');
    }

});

$(function(){
    $('.mo-push-wrap').each(function(){
        var $scope = $(this);
        var defaultCatId = $scope.find('.mo-cir-btn').attr('catid');
        if(defaultCatId){
            $scope.find('.push-mo-btn').attr('catid', defaultCatId);
        }
    });
});

function reset_moment_form_after_publish(){
    $('#moment-title').val('');
    $('#moment_content').val('');
    var editor = $('#pix-moment-editor').data('pixEditor');
    if(editor && typeof editor.setContent === 'function'){
        editor.setContent('', {});
    } else if(editor && editor.el){
        editor.el.innerHTML = '';
        if(typeof editor.sync === 'function'){
            editor.sync();
        }
    }
    $('.mo-num').text('0');
    $('.mo-toggle-box').slideUp(200);
    $('.mo-btn').removeClass('active');
    $('.card-wrap').empty();
    $('input#mo_card_link, .netease_mo').val('');
    $('.audio-preview-box').empty();
    $('.mo-tag-btn span').text('话题');
    $('.mo-tag-btn').removeClass('active is-selected');
    $('.remove-motag').remove();
    $('.push-mo-btn').attr('type','text').removeAttr('pid').removeAttr('tagid');

    var uploader = $('#pix-moment-uploader').data('pixUploader');
    if(uploader){
        uploader.items.forEach(function(item){
            if(item.removeTimer){
                clearInterval(item.removeTimer);
            }
        });
        uploader.items = [];
        uploader.activeKind = '';
        uploader.render();
        uploader.changed();
    }
}

// 片刻错误提醒
function get_moment_error(type){

    switch (type) {
        case "card":
            var file_item = $('.mo-card-item');
            if(!file_item.length > 0 ){
                return false;
            }

            break;      
        
    }

}

// 获取片刻数据
function get_moment_data(type){
    moment_data = [];  
    switch (type) {
        case "card":

                var v = $(".card-wrap .mo-card-item");
                v.each(function(){
                    var pid = $(this).attr('pid');
                    moment_data.push(pid); 
                });
            
            break;      

        case "audio":

                var aid = $(".netease_mo").val();
                var obj = {
                    aid:aid
                }
                moment_data.push(obj);
            
            break;         
    
        default:
            break;
    }
}

// 删除片刻
$('body').on('click','.mo-delete',function(){
  var $trigger = $(this),
      pid = $trigger.closest('.edit-drop-box').attr('pid');

  pix_moment_open_delete_modal({
      pid: pid,
      $trigger: $trigger
  });
});

$('body').on('click','.pix-moment-delete-cancel',function(e){
    e.preventDefault();
    e.stopPropagation();
    pix_moment_close_delete_modal();
});

$('body').on('click','.pix-moment-delete-confirm',function(){
    var $button = $(this),
        $modal = $('#pix-moment-delete-modal'),
        context = $modal.data('deleteContext') || {},
        pid = context.pid,
        $trigger = context.$trigger,
        action = 'moment_delete';

    if (!pid) {
        toastfy('片刻ID不能为空','error');
        pix_moment_close_delete_modal();
        return;
    }

    $button.prop('disabled', true);
    pix_moment_close_delete_modal();

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': action,
            'security': Theme.moment_nonce,
            'pid': pid,
        },
        beforeSend: function () {
            toastfy('正在删除..','info');
        },
        success: function (data) {
            if(data.code == 1){
                toastfy(data.msg,'success');
                if ($trigger && $trigger.length) {
                    $trigger.parents('.moment_item').remove();
                }
            } else {
                toastfy(data.msg,'error');
            }
        },
        complete: function () {
            $button.prop('disabled', false);
        }
    });
});

// 置顶片刻
$('body').on('click','.mo-edit-top',function(){
    var pid = $(this).closest('[pid]').attr('pid'),
        state = $(this).attr('state'),
        t = $(this),
        action = 'moment_top';

    if (!pid) {
        toastfy('片刻ID不能为空','error');
        return;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': action,
            'security': Theme.moment_nonce,
            'pid': pid,
            'state': state
        },
        beforeSend: function () {
           
        },
        success: function (data) {
            if(data.state == 1){
                toastfy(data.msg,'success');
                t.parents('.moment_item').prependTo('#moment-item');
                t.attr('state','unstick');
                t.html('<i class="ri-arrow-up-line"></i>取消置顶');
                t.parents('.moment_item').find('.moment-footer .right .sticky_m_icon').remove();
                t.parents('.moment_item').find('.moment-footer .right').prepend('<span class="sticky_m_icon"><i class="ri-upload-line"></i>置顶</span>');
            } else {
                toastfy(data.msg,'info');
                t.attr('state','stick');
                t.html('<i class="ri-arrow-up-line"></i>置顶片刻');
                t.parents('.moment_item').find('.moment-footer .right .sticky_m_icon').remove();
            }
        }
    });
});

// 片刻加精
$('body').on('click','.mo-edit-hot',function(){
    var pid = $(this).closest('[pid]').attr('pid'),
        state = $(this).attr('state'),
        t = $(this),
        action = 'moment_hot';

    if (!pid) {
        toastfy('片刻ID不能为空','error');
        return;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': action,
            'security': Theme.moment_nonce,
            'pid': pid,
            'state': state
        },
        beforeSend: function () {
           
        },
        success: function (data) {
            if(data.state == 1){
                toastfy(data.msg,'success');
                t.attr('state','unhot');
                t.html('<i class="ri-vip-diamond-line"></i>取消加精');
                t.parents('.moment_item').find('.moment-footer .right').prepend('<span class="hot_m_icon"><i class="ri-vip-diamond-line"></i>精华</span>');
            } else {
                toastfy(data.msg,'info');
                t.parents('.moment_item').find('.moment-footer .right .hot_m_icon').remove();
                t.html('<i class="ri-vip-diamond-line"></i>加精');
                t.attr('state','hot');
                //window.location.reload();
                /* t.attr('state','stick');
                t.html('<i class="ri-arrow-up-line"></i>置顶片刻');
                t.parents('.moment_item').find('.moment-footer .right .sticky_m_icon').remove(); */
            }
        }
    });
});

//base64转blob
function Base64ToBlob(base64,contentType){
    var arr = base64.split(',')  //去掉base64格式图片的头部
    var bstr = atob(arr[1])   //atob()方法将数据解码
    var leng = bstr.length
    var u8arr = new Uint8Array(leng)
    while(leng--){
       u8arr[leng] =  bstr.charCodeAt(leng) //返回指定位置的字符的 Unicode 编码
    }
    var blob = new Blob([u8arr],{type:contentType})
    var blobImg = {}
    //blobImg.url = URL.createObjectURL(blob)  //创建URL
   // blobImg.name = new Date().getTime() + '.png'
    return blob 	
}

function pix_moment_prepare_card_drag_sort($scope) {
    var $target = $scope && $scope.length ? $scope : $('.card-wrap');
    $target.find('.card-box').attr('draggable', 'true');
}

var pix_moment_prepare_card_sortable = pix_moment_prepare_card_drag_sort;

function pix_moment_card_sort_after($container, pointerY) {
    var items = Array.prototype.slice.call($container[0].querySelectorAll('.card-box:not(.is-dragging)'));

    return items.reduce(function(closest, child) {
        var box = child.getBoundingClientRect(),
            offset = pointerY - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return {
                offset: offset,
                element: child
            };
        }

        return closest;
    }, {
        offset: Number.NEGATIVE_INFINITY,
        element: null
    }).element;
}

$(function(){
    pix_moment_prepare_card_drag_sort($('.card-wrap'));
});

$(document).on('dragstart', '.card-wrap .card-box', function(e) {
    var event = e.originalEvent;
    this.classList.add('is-dragging');
    if (event && event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', this.getAttribute('pid') || '');
    }
});

$(document).on('dragover', '.card-wrap', function(e) {
    e.preventDefault();
    var dragging = this.querySelector('.card-box.is-dragging');

    if (!dragging) {
        return;
    }

    var event = e.originalEvent || e,
        afterElement = pix_moment_card_sort_after($(this), event.clientY);

    if (afterElement) {
        this.insertBefore(dragging, afterElement);
    } else {
        this.appendChild(dragging);
    }
});

$(document).on('drop dragend', '.card-wrap .card-box', function() {
    this.classList.remove('is-dragging');
});

function pix_moment_position_mobile_emoji_dropdown(trigger){
    if(!trigger || !pix_moment_is_mobile_compose_view()){
        return;
    }

    var wrapper = trigger.closest('.pix-moment-emoji-dropdown-wrap');
    var composeBody = trigger.closest('.pix-moment-mobile-compose-body');
    var dropdown = wrapper ? wrapper.querySelector('.hs-dropdown-menu') : null;

    if(!wrapper || !composeBody || !dropdown){
        return;
    }

    var triggerRect = trigger.getBoundingClientRect();
    var bodyRect = composeBody.getBoundingClientRect();
    var top = Math.max(bodyRect.top + 12, triggerRect.bottom + 10);
    var left = Math.max(bodyRect.left + 14, 14);
    var right = Math.max(window.innerWidth - bodyRect.right + 14, 14);

    dropdown.style.setProperty('--pix-moment-emoji-top', top + 'px');
    dropdown.style.setProperty('--pix-moment-emoji-left', left + 'px');
    dropdown.style.setProperty('--pix-moment-emoji-right', right + 'px');
}

//话题表情
$(document).on('click','.mo-smile-btn',function(){
    pix_moment_position_mobile_emoji_dropdown(this);

    if(!$('.mo-smile-drop .add-smily').length > 0){
    $.ajax({
        url : Theme.ajaxurl, // AJAX handler, declared before
        data : {
            'action': 'showemoji', // wp_ajax_cloadmore
            'security': Theme.moment_nonce,
        },
        type : 'POST',
        beforeSend : function ( xhr ) {

            loading_start($('.mo-smile-inner'));
            
            //loading_start($('.emoji-inner'));
        },
        success : function( data ){
            $('.mo-smile-inner').html(data.html);
        }
    });
    }
});

// 圈子选取
$('body').on('click','.moment-tags-item',function(){
    $('.mo-circle-content').empty();
    var t = $(this),
        tag = t.attr('data');

        if ((tag === 'join' || tag === 'create') && typeof ppo_require_login === 'function' && !ppo_require_login('请先登录后查看圈子列表')) {
            loading_done($('.mo-circle-content'));
            return false;
        }

        $('.moment-tags-item').removeClass('active');
        t.addClass('active');

        $.ajax({
            url : Theme.ajaxurl, 
            data : {
                'action': 'get_mocat_list', 
                'security': Theme.moment_nonce,
                tag:tag,
            },
            type : 'POST',
            beforeSend : function ( xhr ) {
    
                loading_start($('.mo-circle-content'));
                
            },
            success : function( data ){
                loading_done($('.mo-circle-content'));
                if (data && data.code === 0) {
                    toastify(data.msg || '请先登录后查看圈子列表', 'warning');
                    if (typeof ppo_open_login_modal === 'function') {
                        ppo_open_login_modal(data.msg || '请先登录后查看圈子列表');
                    }
                    $('.mo-circle-content').html(data.html || '');
                    return;
                }
                $('.mo-circle-content').html(data.html || '');
            },
            error : function(){
                loading_done($('.mo-circle-content'));
                $('.mo-circle-content').html('<div class="empty-content"><img src="'+Theme.ppo_url+'/img/empty.png"><p>圈子加载失败，请稍后重试</p></div>');
                toastify('圈子加载失败，请稍后重试', 'warning');
            }
        });

});

$(document).ready(function() {
    var dragState = null;

    $('body').on('mousedown', '.moment-tags-nav', function(e) {
        var $navWrapper = $(this);
        dragState = {
            $navWrapper: $navWrapper,
            startX: e.pageX - $navWrapper.offset().left,
            scrollLeft: $navWrapper.scrollLeft()
        };
        $navWrapper.addClass('is-dragging');
        e.preventDefault();
    });

    $(document).on('mousemove', function(e) {
        if (!dragState) return;
        var $navWrapper = dragState.$navWrapper;
        var x = e.pageX - $navWrapper.offset().left;
        var walk = (x - dragState.startX) * 1.5;
        $navWrapper.scrollLeft(dragState.scrollLeft - walk);
    });

    $(document).on('mouseup', function() {
        if (!dragState) return;
        dragState.$navWrapper.removeClass('is-dragging');
        dragState = null;
    });

    $('body').on('click', '.moment-tags-nav li', function() {
        var $item = $(this);
        var $navWrapper = $item.closest('.moment-tags-nav');
        var itemOffset = $item.offset().left;
        var wrapperOffset = $navWrapper.offset().left;
        var wrapperWidth = $navWrapper.width();
        var itemWidth = $item.outerWidth();
        var newScrollLeft = $navWrapper.scrollLeft() + itemOffset - wrapperOffset - (wrapperWidth / 2) + (itemWidth / 2);

        $navWrapper.stop(true).animate({
            scrollLeft: newScrollLeft
        }, 300);
    });
});

// 话题插入
$('body').on('click','.mo-huati-item',function(){
   var t = $(this),
       $scope = pix_moment_form_scope(t),
       title = t.find('.title').text(),
       tagid = t.attr('tagid');

       $scope.find('.mo-tag-btn span').text(title);
       if(!$scope.find('.remove-motag').length){
        $scope.find('.mo-tag-btn span').after('<a class="remove-motag pix-moment-topic-clear"><i class="ri-close-line"></i></a>');
       }
       $scope.find('.push-mo-btn').attr('tagid',tagid);
       $scope.find('.mo-tag-btn').addClass('active is-selected');
       var editor = $('#pix-moment-editor').data('pixEditor');
       if(editor && typeof editor.insertTopic === 'function'){
            editor.insertTopic(title, tagid);
       }
       pix_moment_close_dropdown('.motag-drop');

});

$('body').on('click','a.remove-motag',function(){
    var t = $(this);
    var $scope = pix_moment_form_scope(t);

    t.siblings('span').text('话题');
    $scope.find('.push-mo-btn').removeAttr('tagid');
    $scope.find('.mo-tag-btn').removeClass('active is-selected');
    t.remove();
    var editor = $('#pix-moment-editor').data('pixEditor');
    if(editor && typeof editor.removeTopic === 'function'){
        editor.removeTopic();
    }
 
 });


// 表情插入
$(document).on('click','a.add-smily',function(){
    var data = $(this).attr('data-smilies');
    var editor = $('#pix-moment-editor').data('pixEditor');
    if(editor && typeof editor.insertEmoji === 'function'){
        editor.insertEmoji(data, $(this).find('img').attr('src') || '');
        pix_moment_close_dropdown('.mo-smile-drop');
        return;
    }
    var emoji = "[s="+data+"]";
    var textarea = $('textarea#moment_content');
    var content = textarea.val();
	textarea.val(content+emoji);
    pix_moment_close_dropdown('.mo-smile-drop');
	//textarea.focus();
});

// 阅读更多



// 片刻评论
window.PixMomentCommentCache = window.PixMomentCommentCache || (function(){
    var ttl = 2 * 60 * 1000;
    var max = 6;
    var store = {};
    var order = [];

    function touch(pid) {
        pid = String(pid || '');
        order = order.filter(function(id){ return id !== pid; });
        order.push(pid);
        while(order.length > max){
            delete store[order.shift()];
        }
    }

    return {
        get: function(pid) {
            pid = String(pid || '');
            var item = store[pid];
            if(!item || Date.now() - item.time > ttl){
                delete store[pid];
                order = order.filter(function(id){ return id !== pid; });
                return '';
            }
            touch(pid);
            return item.html;
        },
        set: function(pid, html) {
            pid = String(pid || '');
            if(!pid || !html) return;
            store[pid] = {
                html: html,
                time: Date.now()
            };
            touch(pid);
        },
        remove: function(pid) {
            pid = String(pid || '');
            delete store[pid];
            order = order.filter(function(id){ return id !== pid; });
        },
        clear: function() {
            store = {};
            order = [];
        }
    };
})();

function reset_moment_comment_runtime(exceptPid){
    $('#comment_form_tmp').empty();
    $('.moment_item').each(function() {
        var otherid = $(this).find('.moment-comment-btn').attr('pid');
        if (otherid && String(otherid) !== String(exceptPid || '')) {
            var $wrap = $(".t_com_"+otherid);
            var $form = $wrap.find('#t_commentform');
            if($form.length){
                cleanup_moment_comment_form($form);
                $('#comment_form_tmp').html($form.prop('outerHTML'));
            }
            $(".toi_respond_"+otherid).empty();
            $(".comment_box_"+otherid).remove();
            $wrap.hide();
        }
    });
}

function cleanup_moment_comment_form($form){
    if(!$form || !$form.length){
        return;
    }

    var uploader = $form.find('.pix-comment-uploader').data('pixUploader');
    if(uploader && typeof uploader.setItems === 'function'){
        uploader.setItems([], 'image');
    }

    $form.find('textarea#comment').val('').css('height', '');
    $form.find('input[name="comt-uploaded-urls"]').val('');
    $form.find('.pix-comment-image-badge').remove();
    $form.find('.com-img-btn').removeClass('has-uploaded-image').removeAttr('title');
    $form.find('.comt-tool-box, .img-box-drop, .emoji-box, .code-box-drop').hide();
    $form.find('#comment_parent').val('0');
    $form.find('#cancel-comment-reply-link').hide();
}

function normalize_moment_comment_html(html){
    var $content = $('<div></div>').html(html || '');
    var $lists = $content.children('ul.comment-list');

    if($lists.length > 1){
        $lists.slice(1).remove();
    }

    var $list = $content.find('ul.comment-list').first();
    if($list.length){
        var hasComments = $list.find('li[id^="comment-"], li.comment').length > 0;
        var $nodata = $list.find('.nodata');

        if(hasComments){
            $nodata.remove();
        } else if($nodata.length > 1){
            $nodata.slice(1).remove();
        }
    }

    return $content.html();
}

// 点击展开评论区域
$('body').off('click.momentComment', '.moment-comment-btn').on('click.momentComment','.moment-comment-btn',function(){
    var temp = $('#comment_form_tmp'),
        pid = $(this).attr('pid'),
        wrap = $(".t_com_"+pid),
        respond = $(".toi_respond_"+pid+""),
        form = $("#t_commentform").prop('outerHTML');

        reset_moment_comment_runtime(pid);
        if(wrap.hasClass('is-loading-comments')){
            return;
        }

        if(wrap.is(':visible')){
            var $activeForm = wrap.find('#t_commentform');
            cleanup_moment_comment_form($activeForm);
            temp.html($activeForm.length ? $activeForm.prop('outerHTML') : form);
            respond.empty();
            $(".comment_box_"+pid+"").remove();
            wrap.hide();
            return;
        }

        if(!wrap.find('form').length > 0){//判断是否存在打开的评论列表
            wrap.show();
            respond.prepend(form);
            $(".comment_box_"+pid+"").remove();
            respond.after("<div class='comment_box_"+pid+" commentshow' data-pid='"+pid+"'></div>");
            //$("textarea#comment").focus();
            temp.empty();
        } else {
            var $activeForm = wrap.find('#t_commentform');
            cleanup_moment_comment_form($activeForm);
            temp.html($activeForm.length ? $activeForm.prop('outerHTML') : form);
            respond.empty();
            $(".comment_box_"+pid+"").remove();
            wrap.hide();
            return;
        }

        var $commentBox = $(".comment_box_"+pid+"").first();
        var cached = window.PixMomentCommentCache ? PixMomentCommentCache.get(pid) : '';
        if(cached){
            $commentBox.html(normalize_moment_comment_html(cached));
            $("#comment_post_ID").val(pid);
            $("#comment_parent").val('0');
            $("#cancel-comment-reply-link").css("display","none");
            pix_moment_refresh_runtime($commentBox);
            update_comt_img();
            return;
        }

        $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            //dataType:  'json',
            data: {
                'action':'load_moment_comment',
                'security': Theme.moment_nonce,
                pid:pid,
                },	
    
            beforeSend: function () {
                wrap.addClass('is-loading-comments');
                loading_start($commentBox);
            },
            success: function(data){
                var dataHtml = normalize_moment_comment_html(data);
                var $box = $(".comment_box_"+pid+"").first();
                loading_done($box);
                $box.html(dataHtml);
                if(window.PixMomentCommentCache){
                    PixMomentCommentCache.set(pid, dataHtml);
                }
                $("#comment_post_ID").val(pid);
                $("#comment_parent").val('0');
                $("#cancel-comment-reply-link").css("display","none");
                $box.find('.loading_box').remove();
                pix_moment_refresh_runtime($box);
                update_comt_img();
            },
            error: function(){
                loading_done($commentBox);
                if(typeof toastfy === 'function'){
                    toastfy('评论加载失败，请稍后重试','error');
                }
            },
            complete: function(){
                wrap.removeClass('is-loading-comments');
            }	
        });
});

// 片刻类型筛选
var pixMomentFilterRequest = null;
var pixMomentFilterSeq = 0;
var pixMomentFilterTimer = null;
var pixMomentFilterQueued = null;
function render_moment_filter_error(message){
    message = message || '筛选加载失败，请稍后重试';
    return '<div class="no-moment moment-filter-error"><img src="'+Theme.ppo_url+'/img/empty.png"><p>'+message+'</p><button type="button" class="moment-filter-retry">重试</button></div>';
}

function is_moment_filter_running(){
    return pixMomentFilterRequest && pixMomentFilterRequest.readyState !== 4;
}

function run_moment_filter($item, forceRetry){
    var t = $item,
        append_box = '#moment-item',
        load_btn = '.loadmore-btn',
        type = t.attr('type'),
        nav_type = Theme.moment_nav,
        baseurl = window.location.href,
        cat = t.parent().attr('catid');

    if(!t.length){
        return;
    }

    if(t.hasClass('active') && !forceRetry && $('.moment-cat-filter').hasClass('is-filtering')){
        return;
    }

    if(t.hasClass('active') && !forceRetry && !$('.moment-cat-filter').data('filterFailed') && !$('.moment-cat-filter').hasClass('is-filtering')){
        return;
    }

    t.addClass('active');
	t.siblings().removeClass('active');    

    reset_moment_comment_runtime('');
    if(window.PixMomentCommentCache){
        PixMomentCommentCache.clear();
    }

    if(pixMomentFilterTimer){
        clearTimeout(pixMomentFilterTimer);
        pixMomentFilterTimer = null;
    }

    var requestSeq = ++pixMomentFilterSeq;
    $('.moment-cat-filter').addClass('is-filtering');
    $('.moment-cat-filter').removeData('filterFailed');

    if(is_moment_filter_running()){
        pixMomentFilterQueued = t;
        return;
    }

    pixMomentFilterTimer = setTimeout(function(){
        pixMomentFilterTimer = null;
        $(append_box).empty();

        var currentRequest = null;
        currentRequest = pixMomentFilterRequest = $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            timeout: 15000,
            data: {
                'action':'moment_type_filter',
                'security': Theme.moment_nonce,
                type:type,
                cat:cat,
                baseurl:baseurl
            },

            beforeSend: function () {
                loading_start($(append_box));
            },
            success: function(data){
                if(requestSeq !== pixMomentFilterSeq){
                    return;
                }
                loading_done($(append_box));
                if(!data || typeof data.content === 'undefined'){
                    $('.moment-cat-filter').data('filterFailed', true);
                    $(append_box).html(render_moment_filter_error());
                    $('.pagenav-box').hide();
                    if(typeof toastfy === 'function'){
                        toastfy('筛选加载失败，请稍后重试','error');
                    }
                    return;
                }
                $('.moment-cat-filter').removeData('filterFailed');
                Theme.current_page = 1;
                Theme.posts = data.posts;
                Theme.max_page = data.max_page;
                if(nav_type == 'pagenav'){

                    $('.pagination-box').attr({
                        'data-cat':cat,
                        'data-max':data.max_page,
                    });
                    if ( data.max_page < 2 ) {
                        $('.pagination-box').empty();
                    } else {
                        $('.pagination-box').html(data.pagenav);
                    }

                } else {
                    $(load_btn).parents('.pagenav-box').show();
                    $(load_btn).attr({
                        'data-max':data.max_page,
                        'data-paged':'1',
                        //'data-cat':cat,
                    });
        
                    $(load_btn).show().siblings().remove();
                    $('.paged-number .current').text('1');
                    $('.paged-number .total').text(data.max_page);

                    if ( data.max_page < 2 ) {
                        $('.pagenav-box').hide();
                    } else {
                        $('.pagenav-box').show();
                    }
                }
            

                var result = $(data.content);
                $(append_box).css('min-height', '');
                $(append_box).append(result.fadeIn(300));

                pix_moment_refresh_runtime(result);
                if(window.PixMomentListOptimizer && typeof PixMomentListOptimizer.run === 'function'){
                    PixMomentListOptimizer.run(append_box);
                }
                
            },
            error: function(xhr){
                if(requestSeq !== pixMomentFilterSeq || (xhr && xhr.statusText === 'abort')){
                    return;
                }
                loading_done($(append_box));
                $('.moment-cat-filter').data('filterFailed', true);
                var msg = xhr && xhr.statusText === 'timeout' ? '筛选请求超时，请重试' : '筛选加载失败，请稍后重试';
                $(append_box).html(render_moment_filter_error(msg));
                $('.pagenav-box').hide();
                if(typeof toastfy === 'function'){
                    toastfy(msg,'error');
                }
            },
            complete: function(xhr){
                if(currentRequest !== pixMomentFilterRequest){
                    return;
                }

                pixMomentFilterRequest = null;

                if(pixMomentFilterQueued && pixMomentFilterQueued.length){
                    var queuedItem = pixMomentFilterQueued;
                    pixMomentFilterQueued = null;
                    run_moment_filter(queuedItem, true);
                    return;
                }

                if(requestSeq === pixMomentFilterSeq){
                    loading_done($(append_box));
                    $('.moment-cat-filter').removeClass('is-filtering');
                }
            }
        });
    }, 120);
}

$('body').off('click.momentFilter', '.moment-cat-filter a').on('click.momentFilter','.moment-cat-filter a',function(e){
    e.preventDefault();
    run_moment_filter($(this), false);
});

$('body').off('click.momentFilterRetry', '.moment-filter-retry').on('click.momentFilterRetry', '.moment-filter-retry', function(){
    run_moment_filter($('.moment-cat-filter .filter-item.active').first(), true);
});

// 免费圈子加入
$('body').on('click','.free-join',function(){
    var t = $(this),
        term_id = t.attr('term_id');
        
    t.addClass('protect');

        $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            data: {
                'action':'mo_join_free',
                'security': Theme.moment_nonce,
                term_id:term_id,
                },	
    
            beforeSend: function () {
                
            },
            success: function(data){
                if(data.code == 1){
                    toastfy(data.msg,'success');
                    location.reload();
                } else {
                    toastfy(data.msg,'error');
                } 
            }	
        });
});

// 免费圈子申请
$('body').on('click','.verify-join',function(){
    var t = $(this),
        term_id = t.attr('term_id');
        
    t.addClass('protect');

        $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            data: {
                'action':'mo_join_verify',
                'security': Theme.moment_nonce,
                term_id:term_id,
                },	
    
            beforeSend: function () {
                
            },
            success: function(data){
                if(data.code == 1){
                    toastfy(data.msg,'success');
                    //location.reload();
                } else {
                    toastfy(data.msg,'error');
                } 
            }	
        });
});

// 圈子订阅支付
$('body').on('click','.pay-join', function(){
	$('#ppo-pay-modal').remove();
    var term_id = $(this).attr('term_id');
    var t_data = $('.mo-price-list a.price-item.active').attr('type');
    
    if(!$('.mo-price-list a.price-item.active').length > 0){
        toastfy('请选择订阅','error');
        return false;
    }    

	var $modal = pix_moment_open_pay_modal();
	var content = $modal.find('.ppo-pay-modal .inner');
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': 'mo_pay_modal',
            'security': Theme.moment_nonce,
            'uid': Theme.uid,
            term_id:term_id,
            t_data:t_data,
        },
        beforeSend: function () {
            loading_start(content);
        },
        success: function (data) {
            loading_done(content);
            content.append(data.html);
        }
    });

});

// 选择圈子套餐
$('body').on('click','.mo-price-list a.price-item', function(){
    var t = $(this),
        type = t.attr('type');
        t.addClass('active').siblings().removeClass('active');
});

function pixMomentToggleCircleHeroDropdown($box, show){
    if(!$box || !$box.length){
        return;
    }

    $box.toggleClass('hidden', !show).toggleClass('block', show);
}

function pixMomentOpenCircleHeroList($trigger, $box){
    var $wrap = $trigger.closest('.pix-moment-circle-hero-more');

    if(!$wrap.length || !$box.length){
        return false;
    }

    pixMomentToggleCircleHeroDropdown($trigger.closest('.pix-moment-circle-hero-menu'), false);
    $wrap.find('.pix-moment-circle-hero-list-dropdown').not($box).each(function(){
        pixMomentToggleCircleHeroDropdown($(this), false);
    });
    pixMomentToggleCircleHeroDropdown($box, true);

    return true;
}

document.addEventListener('click', function(event){
    var button = event.target.closest('.pix-moment-circle-hero-more-btn');

    if(!button){
        return;
    }

    var wrap = button.closest('.pix-moment-circle-hero-more');

    if(!wrap || !wrap.querySelector('.pix-moment-circle-hero-list-dropdown.block')){
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    var $wrap = $(wrap);
    $wrap.find('.pix-moment-circle-hero-list-dropdown.block').each(function(){
        pixMomentToggleCircleHeroDropdown($(this), false);
    });
    pixMomentToggleCircleHeroDropdown($wrap.find('.pix-moment-circle-hero-menu').first(), true);
    $(button).attr('aria-expanded', 'true');
}, true);

$(document).on('click', function(e){
    if($(e.target).closest('.pix-moment-circle-hero-more').length){
        return;
    }

    $('.pix-moment-circle-hero-list-dropdown.block').each(function(){
        pixMomentToggleCircleHeroDropdown($(this), false);
    });
});

$(document).on('keydown', function(e){
    if(e.key !== 'Escape'){
        return;
    }

    $('.pix-moment-circle-hero-list-dropdown.block').each(function(){
        pixMomentToggleCircleHeroDropdown($(this), false);
    });
});

// 我加入或创建的圈子
$('body').on('click','.mo-join , .mo-create',function(){
    var t = $(this),
     type = t.attr('data'),
     $box = t.closest('.mo-user-box').find('.mo-user-'+type+'-drop'),
     $content = $box.find('[data-hs-dropdown-transition]').first(),
     openedHeroList = pixMomentOpenCircleHeroList(t, $box);

    if(!openedHeroList){
        t.closest('.pix-moment-circle-hero-menu').removeClass('block').addClass('hidden');
    }

    if(!$content.length){
        $content = $box;
    }

    if(typeof ppo_require_login === 'function' && !ppo_require_login('请先登录后查看圈子列表')){
        if(openedHeroList){
            pixMomentToggleCircleHeroDropdown($box, false);
        }
        loading_done($content);
        return;
    }

    if($content.find('.mo-topbar-list').length > 0){
        return;
    }

     $.ajax({
        type: "post",
        url:Theme.ajaxurl,
        dataType:  'json',
        data: {
            'action':'load_user_mo_join',
            'security': Theme.moment_nonce,
            type:type,
            },	
        beforeSend: function () {
            loading_start($content);
        },
        success: function(data){
            loading_done($content);
            if(data.code == 0){
                if(data.msg && data.msg.indexOf('登录') !== -1 && typeof ppo_open_login_modal === 'function'){
                    ppo_open_login_modal(data.msg);
                } else {
                    toastfy(data.msg || '加载失败，请重试','error');
                }
                return;
            }
            $content.append(data.html);
        },
        error: function(xhr){
            loading_done($content);
            var msg = '加载失败，请刷新后重试';
            if(xhr.status == 400 || xhr.status == 403){
                msg = '页面验证已过期，请刷新后重试';
            }
            toastfy(msg,'error');
        }
    });
            
});

// 插入卡片
$('body').on('click','.push_card',function(){
    var url = $(this).prev('input').val();
    if(has_pix_moment2_attachments()){
        toastfy('当前片刻已添加附件，请先移除附件后再生成卡片','error');
        return false;
    }

    if(url == ''){
        toastfy('请输入链接','error');
        return false;
    }

    var num = $('.card-wrap .card-box').length,
        max = current_mo_data.card_num;
    if(num >= max){
        toastfy('最多可上传'+max+'个卡片','error');
        return false;
    }

    $.ajax({
        type: "post",
        url:Theme.ajaxurl,
        dataType:  'json',
        data: {
            'action':'ajax_get_card_data',
            'security': Theme.moment_nonce,
            url:url,
            },	
        beforeSend: function () {
            toastfy('正在生成卡片..','info');
        },
        success: function(data){
            if(data.status == 1){
                toastfy('卡片已生成','success');
                
                $('.card-wrap').append(data.html);
                pix_moment_prepare_card_drag_sort($('.card-wrap'));
                
                lazyLoadInstance.update();
                $('input#mo_card_link').val('');
            } else {
                toastfy(data.msg,'error');
            }
        }
    });
});

$('body').on('click','.de_card',function(){
    var t = $(this);

    pix_moment_open_confirm_modal({
        title: '删除卡片',
        content: '确认删除此卡片？',
        intent: 'danger',
        onConfirm: function(){
            t.parent().remove();
        }
    });

});

// 插入音乐
$('body').on('click','.preview-music',function(){
    var aid = $(this).prev('input').val();
    if(aid == ''){
        toastfy('请输入网易云单曲ID','error');
        return false;
    }

    $.ajax({
        type: "post",
        url:Theme.ajaxurl,
        dataType:  'json',
        data: {
            'action':'ajax_preview_music',
            'security': Theme.moment_nonce,
            aid:aid,
            },	
        beforeSend: function () {
            toastfy('正在生成音乐..','info');
        },
        success: function(data){
            if(data.status == 1){
                toastfy('音乐已生成','success');
                
                $('.audio-preview-box').html(data.html);
                
                //$('input.netease_mo').val('');
            } else {
                toastfy(data.msg,'error');
            }
        }
    });
});

$('body').on('click','a.remove-audio',function(){
    var t = $(this);

    pix_moment_open_confirm_modal({
        title: '移除音乐',
        content: '确认移除此音乐？',
        intent: 'danger',
        onConfirm: function(){
            t.prev().remove();
            t.remove();
        }
    });

});

$(document).ready(function(){

    var initTermId = 0;
    if(Theme.tid && $('body').hasClass('tax-moments')){
        initTermId = Theme.tid;
    } else if($('.mo-cir-btn').attr('catid')){
        initTermId = $('.mo-cir-btn').attr('catid');
        $('.push-mo-btn').attr('catid', initTermId);
    }

    if(initTermId){
        $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            data: {
                'action':'get_current_mo_data',
                'security': Theme.moment_nonce,
                term_id:initTermId
            },
            beforeSend: function () {
                $('.mo-tool-nav .left-tool').css('opacity',0.2);
            },
            success: function(data){
                apply_current_moment_data(data);
            },
            error: function(){
                $('.mo-tool-nav .left-tool').css('opacity',1);
            }
        });
    }

});


// 创建圈子
var pixMomentCreateLoadRequest = null;
var pixMomentCreateLoadToken = 0;
var pixMomentCreateUploadRequest = null;
var pixMomentCreateUploadToken = 0;

function setCircleLocalPreview($input) {
    var file = $input[0] && $input[0].files ? $input[0].files[0] : null;
    if (!file) {
        return;
    }

    if (!/^image\//.test(file.type || '')) {
        toastfy('只能选择图片文件','error');
        $input.val('');
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        toastfy('图片大小不能超过2MB','error');
        $input.val('');
        return;
    }

    var $button = $input.closest('.upload-mos-banner, .upload-mos-logo');
    var $preview = $button.find('img').first();
    var oldUrl = $button.data('previewUrl');
    if (oldUrl) {
        URL.revokeObjectURL(oldUrl);
    }

    var imageURL = URL.createObjectURL(file);
    $button.data('previewUrl', imageURL).attr('data-url', '').addClass('has-image');
    $preview.attr('src', imageURL);
}

function pix_moment_reset_create_submit($button) {
    var $submit = $button && $button.length ? $button : $('#cr-moment-modal .cr-moment-btn');
    $submit.removeClass('protect').prop('disabled', false).text('创建');
    pix_moment_set_create_controls_disabled(false);
}

function pix_moment_set_create_submit_loading($button) {
    var $submit = $button && $button.length ? $button : $('#cr-moment-modal .cr-moment-btn');
    $submit.addClass('protect').prop('disabled', true).text('创建中...');
    pix_moment_set_create_controls_disabled(true);
}

function pix_moment_set_create_controls_disabled(disabled) {
    $('#cr-moment-modal')
        .toggleClass('is-modal-locked', !!disabled)
        .find('.pix-moment-modal-cancel, .pix-moment-create-modal-close')
        .prop('disabled', disabled);
}

function pix_moment_clear_create_previews() {
    $('#cr-moment-modal .upload-mos-banner, #cr-moment-modal .upload-mos-logo').each(function(){
        var $button = $(this);
        var previewUrl = $button.data('previewUrl');
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            $button.removeData('previewUrl');
        }
    });
}

function pix_moment_reset_create_modal_body() {
    $('#cr-moment-modal .cr-moment-box').empty();
}

function pix_moment_abort_create_load() {
    if (pixMomentCreateLoadRequest && pixMomentCreateLoadRequest.readyState !== 4) {
        pixMomentCreateLoadRequest.abort();
    }
    pixMomentCreateLoadRequest = null;
}

function pix_moment_abort_create_upload() {
    if (pixMomentCreateUploadRequest && pixMomentCreateUploadRequest.readyState !== 4) {
        pixMomentCreateUploadRequest.abort();
    }
    pixMomentCreateUploadRequest = null;
}

function pix_moment_cleanup_create_modal_state() {
    pix_moment_abort_create_load();
    pix_moment_abort_create_upload();
    pix_moment_clear_create_previews();
    pix_moment_reset_create_submit();
}

function pix_moment_get_create_modal() {
    var modalId = 'cr-moment-modal';
    var $modal = $('#' + modalId);

    if (!$modal.length) {
        var modal = '<div id="' + modalId + '" class="pix-modern pix-modal pix-hs-modal pix-modern-moment pix-moment-modal hidden" role="dialog" tabindex="-1" aria-labelledby="' + modalId + '-title">' +
                    '<div class="pix-modal-dialog pix-moment-modal-dialog">' +
                        '<div class="pix-modal-panel cr-moment-modal pix-moment-modal-panel">' +
                            '<button class="pix-modal-close pix-moment-modal-close pix-moment-create-modal-close" type="button" aria-label="关闭"><i class="ri-close-line"></i></button>' +
                            '<h2 id="' + modalId + '-title" class="pix-modal-title pix-moment-modal-title">创建圈子</h2>' +
                            '<div class="cr-moment-box pix-moment-modal-body"></div>' +
                            '<div class="cr-footer pix-modal-footer pix-moment-modal-footer">' +
                                '<button class="pix-modal-button pix-moment-modal-cancel" type="button">取消</button>' +
                                '<button class="cr-moment-btn pix-modal-button pix-modal-button-primary pix-moment-modal-submit" type="button">创建</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        $('body').append(modal);
        $modal = $('#' + modalId);

        $modal.find('.pix-moment-modal-cancel, .pix-moment-create-modal-close').on('click', function(){
            pix_moment_close_create_modal();
        });
    }

    return $modal;
}

function pix_moment_open_create_modal() {
    pix_moment_show_modal(pix_moment_get_create_modal());
}

function pix_moment_close_create_modal(force) {
    var $modal = $('#cr-moment-modal');

    if (!force && $modal.hasClass('is-modal-locked')) {
        return;
    }

    pix_moment_cleanup_create_modal_state();
    pix_moment_hide_modal($modal);
}

$('body').on('click','.publish-mos',function(){
    var t = $(this);
    pix_moment_close_dropdown('.circle-drop');
    pix_moment_open_create_modal();
    pix_moment_reset_create_modal_body();
    pix_moment_reset_create_submit();
    pix_moment_abort_create_load();

    pixMomentCreateLoadToken++;
    var createLoadToken = pixMomentCreateLoadToken;
    
    pixMomentCreateLoadRequest = $.ajax({
        type: "post",
        url:Theme.ajaxurl,
        dataType:  'json',
        data: {
            'action':'cr_moments',
            'security': Theme.moment_nonce,
            },	
        beforeSend: function () {
           loading_start($('.cr-moment-box'));
        },
        success: function(data){
           if (createLoadToken !== pixMomentCreateLoadToken) {
                return;
           }
            loading_done($('.cr-moment-box'));
           if(!data || data.status == 0 || !data.html){
                toastfy((data && data.msg) || '创建圈子表单加载失败，请稍后重试','error');
                pix_moment_close_create_modal(true);
                return;
           }
           $('.cr-moment-box').html(data.html);
           if (typeof pix_moment_refresh_runtime === 'function') {
                pix_moment_refresh_runtime($('.cr-moment-box'), $('.cr-moment-box')[0]);
           }
        },
        error: function(xhr, textStatus){
            if (textStatus === 'abort' || createLoadToken !== pixMomentCreateLoadToken) {
                return;
            }
            loading_done($('.cr-moment-box'));
            pix_moment_close_create_modal();
            toastfy('创建圈子表单加载失败，请稍后重试','error');
        },
        complete: function(){
            if (createLoadToken === pixMomentCreateLoadToken) {
                pixMomentCreateLoadRequest = null;
            }
        }
    });

});

$('body').on('click', '.upload-mos-banner, .upload-mos-logo', function(e) {
    if ($(e.target).is('input[type="file"]')) {
        return;
    }

    $(this).find('input[type="file"]').trigger('click');
});

$('body').on('change', '.mos-banner-file, .mos-logo-file', function() {
    setCircleLocalPreview($(this));
});

// 圈子创建类型选择
$('body').on('click','.mos-type-btn',function(){

    var t = $(this),
        type = t.attr('data');
   
    $('.mos-type-info[action="'+type+'"]').show().siblings().hide();    
});

$('body').on('click','.mos-btn',function(){

    var t = $(this),
        type = t.attr('data');
   
    t.addClass('active').siblings().removeClass('active');   
});

$('body').on('click','.mos-show-btn',function(){
    $('.mos-show-preview').toggle($(this).attr('data') === 'join');
});

// 创建圈子提交
$('body').on('click','.cr-moment-btn',function(){

    var t = $(this),
        title = $.trim($('input#cr-mos-title').val()),
        desc = $.trim($('textarea#cr-mos-des').val()),
        slug = $.trim($('input#cr-mos-slug').val()),
        cat = $('.mos-cat-btn.mos-btn.active').attr('data'),
        type = $('.mos-type-btn.mos-btn.active').attr('data'),
        show = $('.mos-show-btn.mos-btn.active').attr('data');

        if(t.hasClass('protect')){
            return;
        }

        if(title == '' || desc == '' || slug == ''){
            toastfy('标题，别名，简介必填！','error');
            return;
        }

        var slugRegex = /^[A-Za-z]+$/;
        if (!slugRegex.test(slug)) {
            toastfy('圈子别名只能填写英文字母','error');
            return;
        }

        var base_data = {
            title:title,
            desc:desc,
            slug:slug,
        }

        var cr_data = {
            ppo_moments_tag:cat,
            mo_join_type:type,
            mo_show_type:show,
            show_num:show === 'join' ? Math.max(0, parseInt($('#cr-mos-show-num').val(), 10) || 0) : 0,
            mo_pay_credit_only:type === 'pay' && $('#mos-pay-credit-only').is(':checked'),
        }

        switch (type) {
            case "free":
                var join_data = $('button.mos-join-btn.mos-btn.active').attr('data');
                break;
        
            case "pay":
                var join_data = [];
                var priceInvalid = false;
                var hasPrice = false;
                $.each($('.mos-type-info[action="pay"] input[type="text"]'),function(){
                    var t = $(this),
                        name = t.attr('data'),
                        val = t.val();

                        var isValid = val === '' || (!isNaN(parseFloat(val)) && isFinite(val));

                            if (!isValid) {
                                toastfy('价格必须是数字！','error');
                                priceInvalid = true;
                                return; 
                            }
                            if (val !== '') {
                                hasPrice = true;
                            }

                        var obj = {
                            name:name,
                            price:val
                        }

                        join_data.push(obj);                  
                });
                if (priceInvalid) {
                    return;
                }
                if (!hasPrice) {
                    toastfy('付费圈子至少填写一个金额','error');
                    return;
                }
                break;

            case "limits":
                var join_data = $('.mos-limits-input:checked').map(function() {
                    return $(this).val();
                  }).get();
                break;
        }

        function insertCircle(img_data) {
            var createSucceeded = false;
            $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            data: {
                'action':'insert_moments',
                'security': Theme.moment_nonce,
                base_data:base_data,
                cr_data:cr_data,
                join_data:join_data,
                uid:Theme.uid,
                img_data:img_data
            },
            beforeSend: function () {
                pix_moment_set_create_submit_loading(t);
                toastfy('数据创建中..','info');
            },
            success: function(data){
               if(data && data.status == 1){
                createSucceeded = true;
                toastfy(data.msg,'success');
                pix_moment_close_create_modal(true);
                if(data.term_link){
                    setTimeout(function(){
                        window.location.href = data.term_link;
                    }, 450);
                } else {
                    setTimeout(function(){
                        window.location.reload();
                    }, 450);
                }

            } else {
                toastfy((data && data.msg) || '创建失败，请稍后重试','error');
            }
        },
            error: function() {
                toastfy('创建失败，请稍后重试','error');
            },
            complete: function() {
                if (!createSucceeded) {
                    pix_moment_reset_create_submit(t);
                }
            }


        });
        }

        function uploadCircleImagesThenInsert() {
            var bannerFileInput = $('.mos-banner-file')[0];
            var logoFileInput = $('.mos-logo-file')[0];
            var hasBanner = bannerFileInput && bannerFileInput.files && bannerFileInput.files[0];
            var hasLogo = logoFileInput && logoFileInput.files && logoFileInput.files[0];

            if (!hasBanner && !hasLogo) {
                insertCircle({
                    banner_img: Theme.ppo_url + '/img/banner.jpg',
                    logo_img: Theme.ppo_url + '/img/modef.png'
                });
                return;
            }

            var formData = new FormData();
            if (hasBanner) {
                formData.append('bannerFile', bannerFileInput.files[0]);
            }
            if (hasLogo) {
                formData.append('logoFile', logoFileInput.files[0]);
            }

            pix_moment_abort_create_upload();
            pixMomentCreateUploadToken++;
            var uploadToken = pixMomentCreateUploadToken;

            pixMomentCreateUploadRequest = $.ajax({
                type: "POST",
                url: Theme.ajaxurl + "?action=upload_mos_img&security=" + Theme.moment_nonce,
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    toastfy('图像上传中..','info');
                    pix_moment_set_create_submit_loading(t);
                },
                success: function(data){
                    if (uploadToken !== pixMomentCreateUploadToken) {
                        return;
                    }
                    if(data && data.status == 1){
                        insertCircle({
                            banner_img: data.banner_img || (Theme.ppo_url + '/img/banner.jpg'),
                            logo_img: data.logo_img || (Theme.ppo_url + '/img/modef.png')
                        });
                    } else {
                        pix_moment_reset_create_submit(t);
                        toastfy((data && data.msg) || '图像上传失败','error');
                    }
                },
                error: function(xhr, textStatus) {
                    if (textStatus === 'abort' || uploadToken !== pixMomentCreateUploadToken) {
                        return;
                    }
                    pix_moment_reset_create_submit(t);
                    toastfy('图像上传失败','error');
                },
                complete: function() {
                    if (uploadToken === pixMomentCreateUploadToken) {
                        pixMomentCreateUploadRequest = null;
                    }
                }
            });
        }

        t.addClass('protect').prop('disabled', true);
        uploadCircleImagesThenInsert();
});

// 圈子分类ajax搜索

var mo_s_delay = 400; // 延迟时间，单位为毫秒
var mo_s_timeoutId;

$('.mo-cat-search , .mo-tag-search').on('input',function(){
    var t = $(this);
    clearTimeout(mo_s_timeoutId);
    var keyword = t.val().trim();

    if(t.hasClass('mo-cat-search')){
        var $mo_s_results = $('.mos-s-content'),
            action = 'search_mo_cat';
    } else {
        var $mo_s_results = $('.mos-s-tag-content'),
            action = 'search_mo_tag';
    }

    if (keyword.length < 2) {
        $mo_s_results.empty();
        $mo_s_results.hide();
        return;
    }

    mo_s_timeoutId = setTimeout(function() {
        $.ajax({
            url: Theme.ajaxurl,
            type: 'POST',
            data: {
                action: action,
                security: Theme.moment_nonce,
                keyword: keyword,
            },
            beforeSend: function() {
                $mo_s_results.show();
                $mo_s_results.empty();
                loading_start($mo_s_results);
            },
            success: function(data) {
                loading_done($mo_s_results);
                if(data && data.status == 0 && data.msg){
                    $mo_s_results.html('<div class="error">'+data.msg+'</div>');
                    return;
                }
                $mo_s_results.html((data && data.html) ? data.html : '<div class="error">没有找到相关内容</div>');
                
            },
            error: function() {
                loading_done($mo_s_results);
                $mo_s_results.html('<div class="error">请求失败，请重试</div>');
            }
        });
    }, mo_s_delay);
});

// 片刻首页轮播
    var mySwiper = new Swiper('#top-mos-cat-slide', {
        slidesPerView : 3,
        spaceBetween : 20,
        breakpoints: {
            0: {
                slidesPerView: 2,
                spaceBetween: 12,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
            disabledClass: 'sw-button-disabled',
          },
          pagination: {
            el: '.swiper-pagination',
            clickable :true,
            dynamicBullets: true,
          },
          autoplay: {
            delay: 3000,
            stopOnLastSlide: false,
            disableOnInteraction: true,
            },
    });




// 圈子批准加入
$('body').on('click','.mo-allow-join , .mo-refuse-join',function(){
    var t = $(this),
        uid = t.parents('.mo-wait-item').attr('uid'),
        term_id = t.parents('.mo-wait-item').attr('term_id'),
        action = 'mo_allow_join',
        type = t.attr('action');

        pix_moment_open_confirm_modal({
            title: type == 'allow' ? '批准加入' : '拒绝加入',
            content: type == "allow" ? "批准加入后用户将可以访问圈子内容" : "拒绝后用户可再次申请加入",
            intent: type == 'allow' ? 'primary' : 'danger',
            onConfirm: function(){
                $.ajax({
                    type: "post",
                    url:Theme.ajaxurl,
                    dataType:  'json',
                    data: {
                        'action': action,
                        'security': Theme.moment_nonce,
                        uid:uid,
                        term_id:term_id,
                        type:type,
                        },
                    beforeSend: function () {
                        //loading_start(t);
                    },
                    success: function(data){
                        //loading_done(t);
                        if(data.status == 1){
                            t.parents('.mo-wait-item').fadeOut(500, function() {
                                $(this).remove();
                            });
                        }
                    }
                });
            }
        });

});    

// 片刻审核功能
$('body').on('click','.mo-pending-allow , .mo-pending-remove',function(){
    var t = $(this),
        uid = Theme.uid,
        pid = t.parents('.pending-footer').attr('pid'),
        action = 'mo_pending_check',
        baseurl = window.location.href,
        type = t.attr('action');

        pix_moment_open_confirm_modal({
            title: type == 'allow' ? '通过审核' : '直接删除',
            content: type == "allow" ? "请仔细审核，以免出现合规问题" : "确认后直接删除片刻",
            intent: type == 'allow' ? 'primary' : 'danger',
            onConfirm: function(){
                $.ajax({
                    type: "post",
                    url:Theme.ajaxurl,
                    dataType:  'json',
                    data: {
                        'action': action,
                        'security': Theme.moment_nonce,
                        uid:uid,
                        pid:pid,
                        type:type,
                        baseurl:baseurl,
                        },
                    beforeSend: function () {
                        //loading_start(t);
                    },
                    success: function(data){
                        //loading_done(t);
                        if(data.status == 1){
                           t.parents('.moment_item').fadeOut(200, function() {
                                $(this).remove();
                            });

                            if($('.manage-page-title .cat-nav a.active').attr('type') == 'review_mo'){
                                if($('.moment_item').length < 1 ){
                                    $('.mo-pending-notice').remove();
                                }
                            }

                            if($('.manage-page-title .cat-nav a.active').attr('type') == 'review_join'){
                                if($('.mo-wait-item').length < 1){
                                    $('.mo-pending-notice').remove();
                                }
                            }

                        }
                    }
                });
            }
        });

});   

// ajax圈子用户管理翻页
$('body').on('click','.gl-paginate a',function(e){
    e.preventDefault();
    var t = $(this),
        action = t.parents('.gl-paginate').attr('action'),
        content = t.parents('.gl-paginate').attr('content'),
        $content = $(content),
        term_id = t.parents('.gl-paginate').attr('term_id'),
        total = t.parents('.gl-paginate').attr('total'),
        url = t.attr('href'),
        match = url ? url.match(/\/page\/(\d+)\/?/) : null,
        paged = match ? match[1] : '',
        baseurl = window.location.href;

        if (!paged && url) {
            try {
                var pageUrl = new URL(url, window.location.href);
                paged = pageUrl.searchParams.get('paged') || pageUrl.searchParams.get('page') || '';
            } catch (err) {
                paged = '';
            }
        }

        paged = paged || 1;
        $content.empty();

    $.ajax({
        url: Theme.ajaxurl,
        type: 'POST',
        dataType:  'json',
        data: {
            action: action,
            'security': Theme.moment_nonce,
            term_id: term_id,
            paged: paged,
            total: total,
            baseurl: baseurl,
        },
        beforeSend: function() {
            loading_start($content);
        },
        success: function(data) {
            loading_done($content);
            $content.html(data.html);
            pix_moment_refresh_runtime($content);
        }

    });

});

// 圈子管理页面导航切换
$('body').on('click','.review-page-btn',function(){
    var t = $(this),
    action = 'mo_manage_content',
    $content = $('.moment-manage-inner'),
    ajax_type = t.attr('type'),
    term_id = t.parents('.cat-nav').attr('term_id');

    t.addClass('active').siblings().removeClass('active');
    $content.empty();

    $.ajax({
        url: Theme.ajaxurl,
        type: 'POST',
        data: {
            action: action,
            'security': Theme.moment_nonce,
            term_id: term_id,
            ajax_type: ajax_type,
        },
        beforeSend: function() {
            loading_start($content);
        },
        success: function(data) {
            loading_done($content);
            $content.html(data.html);
            pix_moment_refresh_runtime($content);
        },
        error: function() {
            $content.html('<div class="error">请求失败，请重试</div>');
        }
    })
});


// 移除圈友
$('body').on('click','.mo-del-user',function(){
    var t = $(this),
        action = 'mo_remove_member',
        uid = t.attr('uid'),
        term_id = t.attr('term_id') || t.closest('.pix-moment-manage').find('.cat-nav').attr('term_id') || $('.cat-nav').attr('term_id');

    pix_moment_open_confirm_modal({
        title: '移除圈友',
        contentHtml: '<span class="comfirm-text">请慎重考虑，是否将用户移除此圈子</span><input type="text" class="mo-remove-info pix-moment-confirm-input" placeholder="移除原因备注"><small class="comfirm-tips">选填，填写后用户消息会收到原因备注</small>',
        confirmText: '确定',
        intent: 'danger',
        onConfirm: function($modal){
            var reason = $modal.find('.mo-remove-info').val() || '';

            $.ajax({
                type: "post",
                url: Theme.ajaxurl,
                dataType: 'json',
                data: {
                    action: action,
                    security: Theme.moment_nonce,
                    uid: uid,
                    term_id: term_id,
                    reason: reason
                },
                success: function(data){
                    if(data.status == 1){
                        t.parents('.mo-user-item').fadeOut(200, function() {
                            $(this).remove();
                        });
                        if(typeof toastfy === 'function'){
                            toastfy(data.msg || '已移除圈友','success');
                        }
                    } else if(typeof toastfy === 'function'){
                        toastfy(data.msg || '移除失败，请重试','error');
                    }
                },
                error: function(){
                    if(typeof toastfy === 'function'){
                        toastfy('移除失败，请重试','error');
                    }
                }
            });

            return true;
        }
    });
})
