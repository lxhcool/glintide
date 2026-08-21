/* 用户中心 */
var storage = window.localStorage;
var ppo_user_data = [];

// ==== 登录拦截与通用入口 ====

function ppo_is_logged_in(){
	return typeof Theme !== 'undefined' && parseInt(Theme.uid, 10) > 0;
}

function ppo_open_login_modal(message, mode){
	if(message && typeof toastfy === 'function'){
		toastfy(message, 'info');
	}

	var modal = $('#modal-login');
	if(!modal.length){
		if($('.login-btn').length){
			$('.login-btn').first().trigger('click');
		}
		return;
	}

	if(modal.parent().get(0) !== document.body){
		modal.appendTo(document.body);
	}

	if(mode === 'signup' || mode === 'register'){
		$('#pop_signup').trigger('click');
	} else {
		$('#pop_login').trigger('click');
	}

	ppo_show_modal(modal);
}

function ppo_show_modal(target){
	var modal = $(target);
	if(!modal.length){
		return;
	}

	if((modal.hasClass('pix-hs-modal') || modal.hasClass('hs-overlay')) && modal.parent().get(0) !== document.body){
		modal.appendTo(document.body);
	}

	if(modal.hasClass('pix-hs-modal')){
		modal.removeClass('hidden').addClass('open opened').attr('aria-modal', 'true');
		$('body').addClass('hs-overlay-body-open');
		modal.trigger('pix:modal:shown');
		return;
	}

	if(modal.hasClass('hs-overlay') && typeof window.HSOverlay !== 'undefined' && typeof window.HSOverlay.open === 'function'){
		window.HSOverlay.open('#' + modal.attr('id'));
		modal.removeClass('hidden').addClass('open opened').attr('aria-modal', 'true');
		return;
	}

	modal.removeClass('hidden').addClass('open opened');
}

function ppo_hide_modal(target){
	var modal = $(target);
	if(!modal.length){
		return;
	}

	if(modal.hasClass('pix-hs-modal')){
		modal.addClass('hidden').removeClass('open opened').removeAttr('aria-modal');
		if(!$('.pix-hs-modal.open, .pix-hs-modal.opened').length){
			$('body').removeClass('hs-overlay-body-open');
		}
		modal.trigger('pix:modal:hidden');
		return;
	}

	if(modal.hasClass('hs-overlay') && typeof window.HSOverlay !== 'undefined' && typeof window.HSOverlay.close === 'function'){
		window.HSOverlay.close('#' + modal.attr('id'));
		modal.addClass('hidden').removeClass('open opened').removeAttr('aria-modal');
		modal.trigger('pix:modal:hidden');
		return;
	}

	modal.addClass('hidden').removeClass('open opened');
	modal.trigger('pix:modal:hidden');
}

$('body').on('click', '[data-pix-modal-close]', function(e){
	e.preventDefault();
	e.stopImmediatePropagation();

	var modal = $(this).closest('.pix-hs-modal, .hs-overlay, .pix-modal');
	if(!modal.length){
		modal = $($(this).attr('data-pix-modal-close'));
	}

	ppo_hide_modal(modal);
});

$('body').on('click', '[data-pix-modal-open]', function(e){
	e.preventDefault();

	ppo_show_modal($($(this).attr('data-pix-modal-open')));
});

$('body').on('click', '.pix-dashboard-vip-history-pagination a[data-page]', function(e){
	e.preventDefault();

	var btn = $(this),
		page = parseInt(btn.attr('data-page'), 10),
		section = btn.closest('.pix-dashboard-vip-history');

	if(!page || !section.length || section.hasClass('is-loading')){
		return;
	}

	section.addClass('is-loading');

	$.ajax({
		url: Theme.ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'pix_dashboard_vip_history',
			nonce: Theme.user_nonce,
			page: page
		},
		success: function(res){
			if(res && res.success && res.data && res.data.html){
				section.html(res.data.html);
				return;
			}

			var msg = res && res.data && res.data.msg ? res.data.msg : '订阅记录加载失败';
			if(typeof toastfy === 'function'){
				toastfy(msg, 'error');
			}
		},
		error: function(){
			if(typeof toastfy === 'function'){
				toastfy('订阅记录加载失败', 'error');
			}
		},
		complete: function(){
			section.removeClass('is-loading');
		}
	});
});

function pix_init_dashboard_vip_mobile($scope){
	if(!window.matchMedia || !window.matchMedia('(max-width: 960px)').matches){
		return;
	}

	function centerPlan(grid, target, behavior){
		if(!grid || !target || !target.length){
			return;
		}

		var left = target[0].offsetLeft - ((grid.clientWidth - target[0].offsetWidth) / 2);
		left = Math.max(0, left);

		if(Math.abs(grid.scrollLeft - left) < 1){
			return;
		}

		if(typeof grid.scrollTo === 'function'){
			grid.scrollTo({
				left: left,
				behavior: behavior || 'auto'
			});
			return;
		}

		grid.scrollLeft = left;
	}

	function findTarget($grid, grid){
		var target = $grid.find('.pix-dashboard-vip-plan.is-current').first();

		if(!target.length){
			target = $grid.find('.pix-dashboard-vip-plan.is-hot').first();
		}

		if(!target.length){
			target = $grid.find('.pix-dashboard-vip-plan').first();
		}

		return target;
	}

	function snapToNearest($grid, grid){
		var items = $grid.find('.pix-dashboard-vip-plan');

		if(!items.length){
			return;
		}

		var gridCenter = grid.scrollLeft + grid.clientWidth / 2,
			closest = items.eq(0),
			closestDistance = Infinity;

		items.each(function(){
			var itemCenter = this.offsetLeft + this.offsetWidth / 2,
				distance = Math.abs(itemCenter - gridCenter);

			if(distance < closestDistance){
				closestDistance = distance;
				closest = $(this);
			}
		});

		centerPlan(grid, closest, 'smooth');
	}

	var scope = $scope && $scope.length ? $scope : $(document),
		grids = scope.find('.pix-dashboard-vip-plan-grid');

	if(scope.is && scope.is('.pix-dashboard-vip-plan-grid')){
		grids = grids.add(scope);
	}

	grids.each(function(){
		var grid = this,
			$grid = $(grid);

		if($grid.data('pixMobileCentered')){
			return;
		}

		$grid.data('pixMobileCentered', true);

		window.setTimeout(function(){
			centerPlan(grid, findTarget($grid, grid), 'auto');
		}, 80);

		$grid.off('pointerdown.pixDashboardVipSnap touchstart.pixDashboardVipSnap').on('pointerdown.pixDashboardVipSnap touchstart.pixDashboardVipSnap', function(){
			$grid.data('pixDashboardVipDragging', true);
			window.clearTimeout($grid.data('pixDashboardVipSnapTimer'));
		});

		$grid.off('pointerup.pixDashboardVipSnap pointercancel.pixDashboardVipSnap touchend.pixDashboardVipSnap touchcancel.pixDashboardVipSnap').on('pointerup.pixDashboardVipSnap pointercancel.pixDashboardVipSnap touchend.pixDashboardVipSnap touchcancel.pixDashboardVipSnap', function(){
			$grid.data('pixDashboardVipDragging', false);
			window.clearTimeout($grid.data('pixDashboardVipSnapTimer'));
			$grid.data('pixDashboardVipSnapTimer', window.setTimeout(function(){
				snapToNearest($grid, grid);
			}, 180));
		});

		$grid.off('scroll.pixDashboardVipSnap').on('scroll.pixDashboardVipSnap', function(){
			if($grid.data('pixDashboardVipDragging')){
				return;
			}

			window.clearTimeout($grid.data('pixDashboardVipSnapTimer'));
			$grid.data('pixDashboardVipSnapTimer', window.setTimeout(function(){
				snapToNearest($grid, grid);
			}, 140));
		});
	});
}

$(function(){
	pix_init_dashboard_vip_mobile($('.pix-dashboard-vip'));
});

var pix_dashboard_vip_mobile_resize_timer = null;

$(window).off('resize.pixDashboardVipMobile').on('resize.pixDashboardVipMobile', function(){
	window.clearTimeout(pix_dashboard_vip_mobile_resize_timer);
	pix_dashboard_vip_mobile_resize_timer = window.setTimeout(function(){
		$('.pix-dashboard-vip-plan-grid').each(function(){
			$(this).removeData('pixMobileCentered');
		});
		pix_init_dashboard_vip_mobile($('.pix-dashboard-vip'));
	}, 120);
});

function ppo_require_login(message){
	if(ppo_is_logged_in()){
		return true;
	}

	ppo_open_login_modal(message || '请先登录后再操作');
	return false;
}

$('body').on('click', '[data-pix-auth-open]', function(e){
	e.preventDefault();
	e.stopImmediatePropagation();
	var mode = $(e.target).closest('.register').length ? 'signup' : $(this).data('pix-auth-open');
	ppo_open_login_modal(null, mode);
});

$('body').on('click', '.pix-auth-modal-close', function(e){
	if(typeof window.HSOverlay !== 'undefined'){
		return;
	}

	e.preventDefault();
	$('#modal-login').addClass('hidden').removeClass('open opened').removeAttr('aria-modal');
	$('body').removeClass('hs-overlay-body-open');
});

$('body').on('click', '.like-btn, .collect-btn, .comment-like-btn, .follow-user-btn, .free-join, .verify-join, .pay-join, .mo-join, .mo-create, .need-login, .dd-buy-login', function(e){
	if(ppo_is_logged_in()){
		return;
	}

	e.preventDefault();
	e.stopImmediatePropagation();
	ppo_open_login_modal($(this).data('login-tip') || '请先登录后再操作');
});

// ==== 用户资料、安全设置与社交解绑 ====

// 用户资料修改
$('body').on('click','.user-info-edit',function(){
    var modal = $('#user-edit-modal');
    modal.find('.edit-form-box').empty();
    var editBox = $(this).parents('.eidt-box');
    var action = $(this).attr('action');
    var title = editBox.find('.edit-title').html();
    var currentValue = $.trim(editBox.find('.edit-info').text());
    var input = $('<input>', {
        type: 'text',
        class: 'user-edit-form',
        name: action,
        required: true
    }).val(currentValue);
    if(action == 'user_gender'){
        var v = $(this).attr('gender');
        var input = '<input type="hidden" class="user-edit-form" name="'+action+'"><div class="gender-box"><label><input class="pix-form-radio gender-radio gender0" type="radio" name="user_gender" value="0"> 男</label><label><input class="pix-form-radio gender-radio gender1" type="radio" name="user_gender" value="1"> 女</label><label><input class="pix-form-radio gender-radio gender2" type="radio" name="user_gender" value="2"> 保密</label></div>';
    }
    ppo_show_modal(modal);
    $('.edit-modal-title').html(title);
    modal.find('.edit-form-box').prepend(input);
    $('input[name="user_gender"][value="'+v+'"]').prop('checked', true);

});

$('body').on('click','.user-edit-sure',function(){
    var modal = $('#user-edit-modal');
    var v = $('input.user-edit-form').val();
    var type = $('input.user-edit-form').attr('name');
    var uid = $(this).attr('uid');

    if(type == 'user_gender'){
        var v = $('input[name="user_gender"]:checked').val();
    }
    action = 'ajax_user_edit';
    if(v == ''){
        toastfy('请填写修改内容','error');
        return;
    }

    $.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'type': type,
	        'value': v,
            'uid': uid,
            'nonce': Theme.user_nonce,
	    },
        beforeSend: function () {
            toastfy('修改中..','info');
		},
	    success: function (data) {
            if(data.code == 0 ){
                toastfy(data.msg,'success');
                
                if(type == 'user_gender'){
                    $('.user-info-edit[action="user_gender"]').attr('gender',v);
                   if(v == 0){
                    var vv = '男';
                   } else if(v == 1){
                    var vv = '女';
                   } else {
                    var vv = '保密';
                   }
                   $(".user-info-edit[action='"+type+"']").prev('.left').find('.edit-info').text(vv);
                } else {
                    $(".user-info-edit[action='"+type+"']").prev('.left').find('.edit-info').text(v);
                }

                ppo_hide_modal(modal);
            } else {
                toastfy(data.msg,'error');
            }
			
	    }
	});
});

// 修改密码
$('body').on('click','.user-pass-edit',function(){
	var modal = $('#user-repass-modal');
	modal.find('.safe-form-box').empty();
	var type = $(this).attr('action');

	var title = $(this).parents('.safe-eidt-box').find('.edit-title').html();
	action = 'user_pass_edit';
	ppo_show_modal(modal);
	$('.safe-modal-title').html(title);

	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'type': type,
            'uid': Theme.uid,
            'nonce': Theme.user_nonce,
	    },
        beforeSend: function () {
            modal.find('.safe-form-box').html(page_loader2());
		},
	    success: function (data) {
			if(data.code == 1){
				modal.find('.safe-form-box').html(data.html);
			} else {
				toastfy(data.msg,'error');
			}
	    }
	});
});

//用户安全设置
$('body').on('click','.user-safe-edit',function(){
    var modal = $('#user-safe-modal');
    modal.find('.safe-form-box').empty();
    var type = $(this).attr('action');
    
    var title = $(this).parents('.safe-eidt-box').find('.edit-title').html();
    action = 'user_safe_edit'

    ppo_show_modal(modal);
    $('.safe-modal-title').html(title);
    if(type == 'user_email' || type == 'user_phone'){
        modal.find('.safe-form-box').addClass('ajax-auth');
    }
    $.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'type': type,
            'uid': Theme.uid,
            'nonce': Theme.user_nonce,
	    },
        beforeSend: function () {
            modal.find('.safe-form-box').html(page_loader2());
		},
	    success: function (data) {
			modal.find('.safe-form-box').html(data.html);
	    }
	});
});

// 密码修改ajax
$('body').on('click','.user-pass-sure',function(){
    var btn = this;
	var type = $('.user-pass-action').val();
    var old_pass = '';
	if($('input#old_pass').length > 0 ){
		if($('input#old_pass').val() == ''){
			toastfy('请填写旧密码','error');
			return false;
		}

		var old_pass = $('input#old_pass').val();
	}

	var pass1 = $('input#userpass1').val();
	var pass2 = $('input#userpass2').val();

	if(pass1 != pass2){
		toastfy('两次密码不一样','error');
		return false;
	}

	if(pass1 == ''){
		toastfy('请填写新密码','error');
		return false;
	}

	action = 'user_pass_save';

    var submitUserPass = function(pixcapPayload) {
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'type': type,
            'old_pass' : old_pass ? old_pass : '',
            'pass1' : pass1,
            'pass2' : pass2,
            'uid': Theme.uid,
            'nonce': Theme.user_nonce,
            'logincaptcha': pixcapPayload ? JSON.stringify(pixcapPayload) : '',
	    },
        beforeSend: function () {
            toastfy('密码验证中..','info');
		},
	    success: function (data) {
			if(data.code == '1'){
                toastfy(data.msg,'error');
            } else {
                toastfy(data.msg,'success');	
                if(data.need_relogin){
                    setTimeout(function(){
                        window.location.href = Theme.redirecturl;
                    }, 1500);
                }
            }
	    }
	});
    };

    if (typeof window.pixcapVerifyStandalone === 'function') {
        window.pixcapVerifyStandalone(btn).then(function(payload) {
            submitUserPass(payload);
        }).catch(function(error) {
            toastfy((error && error.message) || '验证失败，请重试', 'error');
        });
    } else {
        submitUserPass(null);
    }

});

$('body').on('click','.user-safe-sure',function(){
   
    var type = $('.user-safe-action').val();
    var code = $('#bind-email-code').val();
    var value = $('.safe-form-box.ajax-auth').find('#email_phone').val();
    //var uid = $(this).attr('uid');

    if(type == 'user_pass'){
		if($('#userpass1').val() == ''){
		 toastfy('请填写新密码','error');
		 return false;
		}

        if($('#userpass1').val() != $('#userpass2').val()){
            toastfy('两次密码不一样','error');
            return false;
        }
        var value = $('#userpass1').val();
    } else if(type == 'user_email'){
		if(value == ''){
		 toastfy('请填写邮箱','error');
		 return false;
		} else if(!/^\w+@\w+\.\w+$/.test(value)){
		 toastfy('邮箱格式错误','error');
		 return false;
		}
	} else {
		if(value == ''){
		 toastfy('请填写手机号','error');
		 return false;
		}
	}


    
    action = 'user_safe_save';

    $.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'type': type,
            'code': code,
            'value' : value,
            'uid': Theme.uid,
            'nonce': Theme.user_nonce,
	    },
        beforeSend: function () {
            toastfy('验证中..','info');
		},
	    success: function (data) {
			if(data.code == '1'){
                toastfy(data.msg,'error');
            } else {
                toastfy(data.msg,'success');
                ppo_hide_modal($('.user-edit-tool').closest('.pix-hs-modal, .hs-overlay, .pix-modal'));
                $(".user-safe-edit[action='"+type+"']").prev('.left').find('.edit-info').text(value);
            }
	    }
	});
});

// 解绑社交登录
$('body').on('click','.unbind-btn',function(){
    var type = $(this).attr('type');
    action = 'unbind_oauth';

    $.confirm({
        title: '',
        content: '确定解绑？',
        boxWidth: '350px',
        useBootstrap: false,
        buttons: {   
            ok: {
                text: "确定",
                btnClass: 'unbind-sure',
                keys: ['enter'],
                action: function(){
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: Theme.ajaxurl,
                        data: {
                            'action': action,
                            'type': type,
                            'uid': Theme.uid,
                            'nonce': Theme.oauth_nonce,
                        },
                        beforeSend: function () {
                            toastfy('解绑中..','info');
                        },
                        success: function (data) {
                            if(data.code == 1){
                                toastfy(data.msg,'success');
                                window.location.reload();
                            } else {
                                toastfy(data.msg,'error');
                            }
                        }
                    });
                }
            },
            close: {
                text: "取消",
            },
        }
    });

});

// ==== 头像、封面和弹窗编辑器 ====

// 头像上传
$('body').on('click','.edit-avatar',function(event){
    event.preventDefault();

    var modal = $('#user-avatar-modal');
    if (!modal.length) {
        return;
    }

    pixInitUserAvatarUploader(modal);
    ppo_show_modal(modal);
    pixInitUserAvatarUploader($('#user-avatar-modal'));
});

function pixInitUserAvatarUploader($scope) {
    if (typeof window.PixUploader === 'undefined') {
        return;
    }

    var scope = $scope && $scope.length ? $scope : $(document);
    var $targets = scope.find('.pix-user-avatar-uploader');
    if (scope.is && scope.is('.pix-user-avatar-uploader')) {
        $targets = $targets.add(scope);
    }

    $targets.each(function() {
        var $mount = $(this);
        if ($mount.data('pixUploader')) {
            return;
        }
        var defaultAvatar = $mount.attr('data-default-avatar') || (Theme.ppo_url + '/img/avap.png');
        $mount.css('--pix-avatar-placeholder', 'url("' + defaultAvatar + '")');

        var uploader = new window.PixUploader(this, {
            context: 'avatar',
            type: 'image',
            limit: 1,
            maxSize: 2,
            multiple: false,
            accept: 'image/*',
            allowExternal: false,
            allowLibrary: false,
            allowBili: false,
            allowCard: false,
            allowedKinds: ['image'],
            preventOutsideClose: true,
            nonce: Theme.upload_nonce || '',
            onUploaded: function(item) {
                if (item && item.url) {
                    $('.user-avatar-show img, .user-pannel-avatar').attr('src', item.url + '?t=' + Date.now()).attr('data-src', item.url + '?t=' + Date.now());
                }
                toastfy('头像上传成功，正在刷新..', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 300);
            }
        });
        $mount.data('pixUploader', uploader);
    });
}

function pixInitUserCoverUploader($scope) {
    if (typeof window.PixUploader === 'undefined') {
        return;
    }

    var scope = $scope && $scope.length ? $scope : $(document);
    var $targets = scope.find('.pix-user-cover-uploader');
    if (scope.is && scope.is('.pix-user-cover-uploader')) {
        $targets = $targets.add(scope);
    }

    $targets.each(function() {
        var $mount = $(this);
        if ($mount.data('pixUploader')) {
            return;
        }

        var uploader = new window.PixUploader(this, {
            context: 'user_cover',
            type: 'image',
            limit: 1,
            maxSize: 3,
            multiple: false,
            accept: 'image/*',
            allowExternal: false,
            allowLibrary: false,
            allowBili: false,
            allowCard: false,
            allowedKinds: ['image'],
            preventOutsideClose: true,
            nonce: Theme.upload_nonce || '',
            onUploaded: function(item, instance) {
                if (item && item.url) {
                    $('.user-cover-preview').attr('src', item.url + '?t=' + Date.now()).attr('data-src', item.url + '?t=' + Date.now());
                }
                if (instance && typeof instance.setItems === 'function') {
                    instance.setItems([], 'image');
                }
                toastfy('封面上传成功', 'success');
            }
        });
        $mount.data('pixUploader', uploader);
    });
}

$(document).ready(function() {
    pixInitUserAvatarUploader();
    pixInitUserCoverUploader();
});

$(document).on('click', '.cover-upload', function(event) {
    event.preventDefault();
    pixInitUserCoverUploader($(this).parent());
    var uploader = $(this).siblings('.pix-user-cover-uploader').first().data('pixUploader');
    if (uploader && uploader.input) {
        uploader.input.click();
    }
});

//选取其他头像
$('body').on('click','.bind-avatar-box a',function(){
	var t = $(this);
	t.siblings('a').removeClass('active');
	t.toggleClass('active');

	$('.avatar-btn').css('display','flex');

	if(!$('.bind-avatar-box a.active').length > 0){
		$('.avatar-btn').css('display','none');
	}
	  
});

$('body').on('click','a.change-avatar',function(){
	var t = $(this);
	if(t.hasClass('protect')){
		return;
	}
	t.addClass('protect');

	var src = $('a.bind-avatar.active').find('img').attr('src');
	var type = t.attr('type');
	action = 'change_avatar';

	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'type': type,
            'src': src,
            'uid': Theme.uid,
            'nonce': Theme.user_nonce,
	    },
        beforeSend: function () {
            toastfy('更换中..','info');
		},
	    success: function (data) {
			if(data.code == '1'){
				t.removeClass('protect');
                toastfy(data.msg,'error');
            } else {
                toastfy(data.msg,'success');
                setTimeout(function() {
					window.location.reload();
				}, 300);
            }
	    },
		error: function () {
			t.removeClass('protect');
			toastfy('更换失败，请重试','error');
		}
	});

});

$(document).ready(function() {

if ($.fn.fileuploader && $('input.avatar-pond').length) {
$('input.avatar-pond').fileuploader({
    	limit: 2,
        extensions: ['image/*'],
		fileMaxSize: 2,
		changeInput: ' ',
		theme: 'avatar',
		addMore: true,
        enableApi: true,
		thumbnails: {
			box: '<div class="fileuploader-wrapper">' +
					'<div class="fileuploader-items"></div>' +
					'<div class="fileuploader-droparea" data-action="fileuploader-input"><i class="fileuploader-icon-main"></i></div>' +
				   '</div>' +
					'<div class="fileuploader-menu">' +
						'<button type="button" class="fileuploader-menu-open"><i class="fileuploader-icon-menu"></i></button>' +
						'<ul>' +
							'<li><a data-action="fileuploader-input"><i class="fileuploader-icon-upload"></i> ${captions.upload}</a></li>' +
							'<li><a data-action="fileuploader-edit"><i class="fileuploader-icon-edit"></i> ${captions.edit}</a></li>' +
							'<li><a data-action="fileuploader-remove"><i class="fileuploader-icon-trash"></i> ${captions.remove}</a></li>' +
						'</ul>' +
					'</div>',
			item: '<div class="fileuploader-item">' +
				      '${image}' +
					  '<span class="fileuploader-action-popup" data-action="fileuploader-edit"></span>' +
					  '<div class="progressbar3" style="display: none"></div>' +
					'</div>',
			item2: null,
			itemPrepend: true,
			startImageRenderer: true,
            canvasImage: false,
			_selectors: {
				list: '.fileuploader-items'
			},
			popup: {
				arrows: false,
				onShow: function(item) {
					item.popup.html.addClass('is-for-avatar');
                    item.popup.html.on('click', '[data-action="remove"]', function(e) {
                        item.popup.close();
                        item.remove();
                    }).on('click', '[data-action="cancel"]', function(e) {
                        item.popup.close();
                    }).on('click', '[data-action="save"]', function(e) {
						if (item.editor && !item.isSaving) {
							item.isSaving = true;
                        	item.editor.save();
						}
						if (item.popup.close)
							item.popup.close();
                    });
                },
				onHide: function(item) {
					if (!item.isSaving && !item.uploaded && !item.appended) {
						item.popup.close = null;
						item.remove();
					}
				} 	
			},
			onItemShow: function(item) {
				if (item.choosed)
					item.html.addClass('is-image-waiting');
			},
			onImageLoaded: function(item, listEl, parentEl, newInputEl, inputEl) {
                if (item.choosed && !item.isSaving) {
					if (item.reader.node && item.reader.width >= 64 && item.reader.height >= 64) {
						item.image.hide();
						item.popup.open();
						item.editor.cropper();
					} else {
						item.remove();
						alert('图片尺寸太小!');
					}
				} else if (item.data.isDefault)
					item.html.addClass('is-default');
				else if (item.image.hasClass('fileuploader-no-thumbnail'))
					item.html.hide();
            },
			onItemRemove: function(html) {
				html.fadeOut(250, function() {
					html.remove();
				});
			}
		},
		dragDrop: {
			container: '.fileuploader-wrapper'
		},
		editor: {
			maxWidth: 512,
			maxHeight: 512,
			quality: 80,
            cropper: {
				showGrid: true,
				ratio: '1:1',
				minWidth: 150,
				minHeight: 150,
			},
			onSave: function(base64, item, listEl, parentEl, newInputEl, inputEl) {
				var api = $.fileuploader.getInstance(inputEl);
                
                if (!base64)
                    return;
				
				// blob
				item.editor._blob = api.assets.dataURItoBlob(base64, item.type);
				
				if (item.upload) {
					if (api.getFiles().length == 2 && (api.getFiles()[0].data.isDefault || api.getFiles()[0].upload))
						api.getFiles()[0].remove();
					parentEl.find('.fileuploader-menu ul a').show();
					
					if (item.upload.send)
						return item.upload.send();
					if (item.upload.resend)
						return item.upload.resend();
				} else if (item.appended) {
					var form = new FormData();
					
					// hide current thumbnail (this is only animation)
					item.image.addClass('fileuploader-loading').html('');
					item.html.find('.fileuploader-action-popup').hide();
					parentEl.find('[data-action="fileuploader-edit"]').hide();
					
					// send ajax
					form.append(inputEl.attr('name'), item.editor._blob);
					form.append('fileuploader', true);
					form.append('name', item.name);
					form.append('editing', true);
					$.ajax({
						url: api.getOptions().upload.url,
						data: form,
						type: 'POST',
						processData: false,
						contentType: false
					}).always(function() {
						delete item.isSaving;
						item.reader.read(function() {
							item.html.find('.fileuploader-action-popup').show();
							parentEl.find('[data-action="fileuploader-edit"]').show();
							item.popup.html = item.popup.node = item.popup.editor = item.editor.crop = item.editor.rotation = item.popup.zoomer = null;
							item.renderThumbnail();
						}, null, true);
					});
				}
			} 
        },
        upload: {
            url: Theme.ajaxurl + "?action=ppo_avatar_upload&nonce=" + encodeURIComponent(Theme.user_nonce),
            data: null, // should be null
            type: 'POST',
            enctype: 'multipart/form-data',
            start: false,
            beforeSend: function(item, listEl, parentEl, newInputEl, inputEl) {
                item.upload.formData = new FormData();

                if (item.editor && item.editor._blob) {
                    item.upload.data.fileuploader = 1;
                    item.upload.data.name = item.name;
                    item.upload.data.editing = item.uploaded;

                    item.upload.formData.append(inputEl.attr('name'), item.editor._blob, item.name);
                }

                item.image.hide();
                item.html.removeClass('upload-complete');
                parentEl.find('[data-action="fileuploader-edit"]').hide();
                this.onProgress({percentage: 0}, item);
            },
            onSuccess: function(result, item, listEl, parentEl, newInputEl, inputEl) {
                var api = $.fileuploader.getInstance(inputEl),
					$progressBar = item.html.find('.progressbar3'),
					data = {};
				
				if (result && result.files)
                    data = result;
                else
					data.hasWarnings = true;
				
				if (api.getFiles().length > 1)
					api.getFiles()[0].remove();
                
				// if success
                if (data.isSuccess && data.files[0]) {
                    item.name = data.files[0].name;
				}
				
				// if warnings
				if (data.hasWarnings) {
					for (var warning in data.warnings) {
						alert(data.warnings[warning]);
					}
					
					item.html.removeClass('upload-successful').addClass('upload-failed');
					return this.onError ? this.onError(item) : null;
				} 
				
				delete item.isSaving;
				item.html.addClass('upload-complete').removeClass('is-image-waiting');
				$progressBar.find('span').html('<i class="fileuploader-icon-success"></i>');
				parentEl.find('[data-action="fileuploader-edit"]').show();
				setTimeout(function() {
					$progressBar.fadeOut(450);
				}, 1250);
				item.image.fadeIn(250);
				window.location.reload();
            },
            onError: function(item, listEl, parentEl, newInputEl, inputEl) {
				var $progressBar = item.html.find('.progressbar3');
				
				item.html.addClass('upload-complete');
				if (item.upload.status != 'cancelled')
					$progressBar.find('span').attr('data-action', 'fileuploader-retry').html('<i class="fileuploader-icon-retry"></i>');
            },
            onProgress: function(data, item) {
                var $progressBar = item.html.find('.progressbar3');
				
				if (data.percentage == 0)
					$progressBar.addClass('is-reset').fadeIn(250).html('');
				else if (data.percentage >= 99)
					data.percentage = 100;
				else
					$progressBar.removeClass('is-reset');
				if (!$progressBar.children().length)
					$progressBar.html('<span></span><svg><circle class="progress-dash"></circle><circle class="progress-circle"></circle></svg>');
				
				var $span = $progressBar.find('span'),
					$svg = $progressBar.find('svg'),
					$bar = $svg.find('.progress-circle'),
					hh = Math.max(60, item.html.height() / 2),
					radius = Math.round(hh / 2.28),
					circumference = radius * 2 * Math.PI,
					offset = circumference - data.percentage / 100 * circumference;
				
				$svg.find('circle').attr({
					r: radius,
					cx: hh,
					cy: hh
				});
				$bar.css({
					strokeDasharray: circumference + ' ' + circumference,
					strokeDashoffset: offset
				});
				
				$span.html(data.percentage + '%');
            },
            onComplete: null,
        },
		afterRender: function(listEl, parentEl, newInputEl, inputEl) {
			var api = $.fileuploader.getInstance(inputEl);
			
			// remove multiple attribute
			inputEl.removeAttr('multiple');
            
            // set drop container
            api.getOptions().dragDrop.container = parentEl.find('.fileuploader-wrapper');
			
			// disabled input
			if (api.isDisabled()) {
				parentEl.find('.fileuploader-menu').remove();
			}
			
			// [data-action]
			parentEl.on('click', '[data-action]', function() {
				var $this = $(this),
					action = $this.attr('data-action'),
					item = api.getFiles().length ? api.getFiles()[api.getFiles().length-1] : null;
				
				switch (action) {
					case 'fileuploader-input':
						api.open();
						break;
					case 'fileuploader-edit':
						if (item && item.popup) {
							if (!$this.is('.fileuploader-action-popup'))
								item.popup.open();
							item.editor.cropper();
						}
						break;
					case 'fileuploader-retry':
						if (item && item.upload.retry)
							item.upload.retry();
						break;
					case 'fileuploader-remove':
						if (item)
							item.remove();
						break;
				}
			});
			
			// menu
			$('body').on('click', function(e) {
				var $target = $(e.target),
					$parent = $target.closest('.fileuploader');
				
				$('.fileuploader-menu').removeClass('is-shown');
				if ($target.is('.fileuploader-menu-open') || $target.closest('.fileuploader-menu-open').length)
					$parent.find('.fileuploader-menu').addClass('is-shown');
			});
		},
		onEmpty: function(listEl, parentEl, newInputEl, inputEl) {
			var api = $.fileuploader.getInstance(inputEl),
				defaultAvatar = inputEl.attr('data-fileuploader-default');
			
			if (defaultAvatar && !listEl.find('> .is-default').length)
				api.append({name: '', type: 'image/png', size: 0, file: defaultAvatar, data: {isDefault: true, popup: false, listProps: {is_default: true}}});
			
			parentEl.find('.fileuploader-menu ul a').hide().filter('[data-action="fileuploader-input"]').show();
		},
		onRemove: function(item) {
			if (item.name && (item.appended || item.uploaded))
				$.post(Theme.ajaxurl + "?action=ppo_avatar_remove", {
					file: item.name,
					nonce: Theme.user_nonce
				});
		},
	captions: 'zh_CN',
}); 
}



});

// ==== VIP、支付和下载流程 ====

var pix_vip_plan_swiper = null;

function pix_init_vip_plan_swiper($scope){
	var scope = $scope && $scope.length ? $scope : $(document),
		element = scope.find('#vip-plan-swiper').get(0) || (scope.is && scope.is('#vip-plan-swiper') ? scope.get(0) : document.getElementById('vip-plan-swiper')),
		isMobile = window.matchMedia('(max-width: 980px)').matches;

	if(!element || typeof window.Swiper === 'undefined'){
		return;
	}

	if(pix_vip_plan_swiper && !pix_vip_plan_swiper.destroyed && pix_vip_plan_swiper.el !== element){
		pix_vip_plan_swiper.destroy(true, true);
		pix_vip_plan_swiper = null;
	}

	if(isMobile){
		if(!pix_vip_plan_swiper || pix_vip_plan_swiper.destroyed){
			var slides = element.querySelectorAll('.vip_show_item'),
				hotIndex = 0;

			for(var i = 0; i < slides.length; i++){
				if(slides[i].classList.contains('hot')){
					hotIndex = i;
					break;
				}
			}

			pix_vip_plan_swiper = new window.Swiper(element, {
				slideClass: 'vip_show_item',
				slidesPerView: 'auto',
				spaceBetween: 12,
				initialSlide: hotIndex,
				centeredSlides: true,
				centeredSlidesBounds: false,
				slidesOffsetBefore: 0,
				slidesOffsetAfter: 0,
				roundLengths: true,
				watchSlidesProgress: true,
				watchOverflow: true,
				observer: true,
				observeParents: true,
				resizeObserver: true,
				pagination: {
					el: element.querySelector('.vip-plan-pagination'),
					clickable: true
				}
			});
		} else {
			pix_vip_plan_swiper.update();
		}
		return;
	}

	if(pix_vip_plan_swiper && !pix_vip_plan_swiper.destroyed){
		pix_vip_plan_swiper.destroy(true, true);
		pix_vip_plan_swiper = null;
	}
}

function pix_init_vip_faq($scope){
	var scope = $scope && $scope.length ? $scope : $(document),
		items = scope.find('.vip-modern-page .vip-qa-item');

	if(!items.length){
		return;
	}

	items.each(function(){
		var item = $(this),
			answer = item.children('.vip-qa-answer');

		if(!answer.length){
			return;
		}

		if(item.attr('open')){
			item.addClass('is-open');
			answer.show();
		} else {
			answer.hide();
		}
	});

	items.children('summary').off('click.pixVipFaq').on('click.pixVipFaq', function(e){
		e.preventDefault();

		var item = $(this).parent('.vip-qa-item'),
			answer = item.children('.vip-qa-answer'),
			list = item.closest('.vip-qa-list'),
			isOpen = item.hasClass('is-open');

		list.find('.vip-qa-item.is-open').not(item).each(function(){
			var other = $(this),
				otherAnswer = other.children('.vip-qa-answer');

			other.removeClass('is-open');
			otherAnswer.stop(true, true).slideUp(180, function(){
				other.removeAttr('open');
			});
		});

		if(isOpen){
			item.removeClass('is-open');
			answer.stop(true, true).slideUp(180, function(){
				item.removeAttr('open');
			});
			return;
		}

		item.attr('open', 'open').addClass('is-open');
		answer.stop(true, true).hide().slideDown(220);
	});
}

$(function(){
	pix_init_vip_plan_swiper();
	pix_init_vip_faq();
});

var pix_vip_plan_resize_timer = null;

$(window).off('resize.pixVipPlanSwiper').on('resize.pixVipPlanSwiper', function(){
	window.clearTimeout(pix_vip_plan_resize_timer);
	pix_vip_plan_resize_timer = window.setTimeout(function(){
		pix_init_vip_plan_swiper();
	}, 16);
});

function pix_update_vip_pay_scroll_hint(modal){
	var info = modal.find('.vip-pay-info').get(0),
		box = modal.find('.vip-info');

	if(!info || !box.length){
		return;
	}

	var update = function(){
		var hasOverflow = info.scrollHeight > info.clientHeight + 2,
			canScrollMore = info.scrollTop + info.clientHeight < info.scrollHeight - 2;
		box.toggleClass('has-more', hasOverflow && canScrollMore);
	};

	$(info).off('scroll.pixVipPayHint').on('scroll.pixVipPayHint', update);
	window.requestAnimationFrame(update);
}

function pix_init_vip_pay_limits_toggle(modal, reset){
	if(!modal || !modal.length){
		return;
	}

	var items = modal.find('.vip-pay-info > li'),
		isMobile = window.matchMedia && window.matchMedia('(max-width: 760px)').matches;

	items.each(function(){
		var item = $(this),
			toggle = item.find('.vip-pay-info-toggle').first(),
			expanded = item.hasClass('is-expanded');

		if(!isMobile){
			item.addClass('is-expanded');
			item.find('.limits-item').show();
			toggle.attr('aria-expanded', 'true');
			return;
		}

		if(reset){
			expanded = false;
			item.removeClass('is-expanded');
			item.find('.limits-item').hide();
		}

		if(expanded){
			item.find('.limits-item').show();
		}

		toggle.attr('aria-expanded', expanded ? 'true' : 'false');
	});
}

function pix_set_vip_pay_tab(modal, index){
	var tabs = modal.find('.vip-pay-tab > li'),
		prices = modal.find('.vip-pay-limits > li'),
		infos = modal.find('.vip-pay-info > li'),
		selected = parseInt(index, 10);

	if(!tabs.length || !prices.length || isNaN(selected) || selected < 0 || selected >= prices.length){
		return;
	}

	tabs.removeClass('is-active').attr('aria-selected', 'false');
	tabs.eq(selected).addClass('is-active').attr('aria-selected', 'true');
	prices.prop('hidden', true).eq(selected).prop('hidden', false);
	infos.prop('hidden', true).eq(selected).prop('hidden', false);
	infos.scrollTop(0);
	infos.removeClass('is-expanded').find('.limits-item').hide();
	infos.find('.vip-pay-info-toggle').attr('aria-expanded', 'false');

	var price = prices.eq(selected).find('.vp-price.active .vp-p').html();
	modal.find('.agree-price span').html(price || '');
	modal.find('.pay-sure-btn').attr('data', selected);
	pix_center_vip_pay_tab(modal, tabs.eq(selected));
	pix_init_vip_pay_limits_toggle(modal, true);
	pix_update_vip_pay_scroll_hint(modal);
}

function pix_center_vip_pay_tab(modal, tab){
	if(!modal || !modal.length || !tab || !tab.length){
		return;
	}

	var list = modal.find('.vip-pay-tab').get(0),
		item = tab.get(0);

	if(!list || !item || list.scrollWidth <= list.clientWidth + 2){
		return;
	}

	window.requestAnimationFrame(function(){
		var left = item.offsetLeft - ((list.clientWidth - item.offsetWidth) / 2),
			maxLeft = list.scrollWidth - list.clientWidth;

		left = Math.max(0, Math.min(left, maxLeft));

		if(Math.abs(list.scrollLeft - left) < 1){
			return;
		}

		if(typeof list.scrollTo === 'function'){
			list.scrollTo({
				left: left,
				behavior: 'smooth'
			});
			window.setTimeout(function(){
				pix_update_vip_pay_upgrade_badge(modal);
			}, 220);
			return;
		}

		list.scrollLeft = left;
		pix_update_vip_pay_upgrade_badge(modal);
	});
}

function pix_update_vip_pay_upgrade_badge(modal){
	if(!modal || !modal.length){
		return;
	}

	var list = modal.find('.vip-pay-tab').first(),
		wrap = modal.find('.vip-pay-modern .left').first(),
		badge = modal.find('.vip-pay-tab-upgrade-badge').first(),
		index = parseInt(list.attr('data-upgrade-index'), 10),
		label = list.attr('data-upgrade-label'),
		tabs = list.children('li');

	if(!badge.length){
		badge = $('<span class="vip-pay-tab-upgrade-badge" aria-hidden="true"></span>').appendTo(wrap);
	}

	if(!list.length || !wrap.length || !label || isNaN(index) || index < 0 || index >= tabs.length){
		badge.hide();
		return;
	}

	window.requestAnimationFrame(function(){
		var item = tabs.eq(index).get(0),
			wrapRect = wrap.get(0).getBoundingClientRect(),
			itemRect = item.getBoundingClientRect();

		badge.text(label).show();

		badge.css({
			left: Math.round(itemRect.left - wrapRect.left),
			top: Math.round(itemRect.top - wrapRect.top - 11)
		});
	});
}

function pix_mark_vip_pay_upgrade(modal, upgradeIndex, selectedIndex){
	var tabs = modal.find('.vip-pay-tab > li'),
		list = modal.find('.vip-pay-tab'),
		upgrade = parseInt(upgradeIndex, 10),
		selected = parseInt(selectedIndex, 10);

	tabs.removeClass('is-upgrade-suggest').removeAttr('data-upgrade-label');
	list.removeAttr('data-upgrade-index').removeAttr('data-upgrade-label');

	if(!tabs.length || isNaN(upgrade) || upgrade < 0 || upgrade >= tabs.length || upgrade === selected){
		pix_update_vip_pay_upgrade_badge(modal);
		return;
	}

	tabs.eq(upgrade).addClass('is-upgrade-suggest').attr('data-upgrade-label', '推荐升级');
	list.attr('data-upgrade-index', upgrade).attr('data-upgrade-label', '推荐升级');
	list.off('scroll.pixVipPayUpgradeBadge').on('scroll.pixVipPayUpgradeBadge', function(){
		pix_update_vip_pay_upgrade_badge(modal);
	});
	pix_update_vip_pay_upgrade_badge(modal);
}

// 会员充值模态框
$('body').on('click','.vip_update',function(e){
	e.preventDefault();

	var vip = $(this).attr('vip-data'),
		upgrade = $(this).attr('data-vip-upgrade'),
		modal = $('#modal-vip-pay'),
		panel = modal.find('.vip-pay-modal');

	if(!modal.length || !panel.length){
		return;
	}

	ppo_show_modal(modal);

	if(modal.find('.vip-pay-box').length > 0){
		pix_set_vip_pay_tab(modal, vip);
		pix_mark_vip_pay_upgrade(modal, upgrade, vip);
		return;
	}

	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': 'vip_pay_modal',
	    },
        beforeSend: function () {
            loading_start(panel);
		},
	    success: function (data) {
			loading_done(panel);
			if(data.state == '1' && data.html){
                panel.append(data.html);
				pix_set_vip_pay_tab(modal, vip);
				pix_mark_vip_pay_upgrade(modal, upgrade, vip);
            } else {
				ppo_hide_modal(modal);
                ppo_open_login_modal('请先登录后再开通会员');
            }
	    },
		error: function () {
			loading_done(panel);
			ppo_hide_modal(modal);
			toastfy('会员订阅信息加载失败，请稍后重试','error');
		}
	});
});

$('body').on('click','#modal-vip-pay .vip-pay-tab li',function(e){
	e.preventDefault();
	var modal = $(this).closest('#modal-vip-pay');
	pix_set_vip_pay_tab(modal, $(this).index());
});

$('body').on('click','#modal-vip-pay .vip-pay-info li h4',function(e){
	var modal = $(this).closest('#modal-vip-pay');

	if(!window.matchMedia || !window.matchMedia('(max-width: 760px)').matches){
		return;
	}

	e.preventDefault();

	var item = $(this).closest('li'),
		toggle = item.find('.vip-pay-info-toggle').first(),
		limits = item.find('.limits-item'),
		expanded = !item.hasClass('is-expanded');

	toggle.attr('aria-expanded', expanded ? 'true' : 'false');

	if(expanded){
		item.addClass('is-expanded');
		limits.stop(true, true).slideDown(180);
	} else {
		limits.stop(true, true).slideUp(180, function(){
			item.removeClass('is-expanded');
		});
	}

	pix_update_vip_pay_scroll_hint(modal);
});

$('body').on('click','#modal-vip-pay .vip-pay-limits .vp-price',function(){
	var modal = $(this).closest('#modal-vip-pay');
	$(this).siblings().removeClass('active');
	$(this).addClass('active');
	modal.find('.agree-price span').html($(this).find('.vp-p').html());
});

$(window).off('resize.pixVipPayHint').on('resize.pixVipPayHint', function(){
	var modal = $('#modal-vip-pay');
	pix_init_vip_pay_limits_toggle(modal, false);
	pix_update_vip_pay_scroll_hint(modal);
	pix_update_vip_pay_upgrade_badge(modal);
});

// 获取支付类型
$('body').on('click','.pay-type-btn',function(){
	var type = $(this).attr('type-data');
	$(this).siblings('.pay-type-btn').removeClass('active');
	$(this).addClass('active');
	$(this).parents('.pay-type-box').find('.pay-sure-btn').attr('pay_type',type);
});

// 支付台
$('body').on('click','.dd-buy-now', function(){
	$('#ppo-pay-modal').remove();
    var modal = ppo_modal('ppo-pay-modal');
	$('body').append(modal);
	var payModal = $('#ppo-pay-modal');
	payModal.one('pix:modal:shown', function () {
		var content = payModal.find('.ppo-pay-modal .inner');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: Theme.ajaxurl,
			data: {
				'action': 'dd_pay_modal',
				'pid': Theme.pid,
				'uid': Theme.uid,
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
	ppo_show_modal(payModal);

});


function ppo_asset_pay_label(pay_type){
	var names = window.Theme || {};
	return pay_type == 'credit' ? (names.credit_name || '积分') : (names.cash_name || '余额');
}

function ppo_xp_label(){
	var names = window.Theme || {};
	return names.xp_name || '经验值';
}

function ppo_escape_html(text){
	return $('<div/>').text(text == null ? '' : text).html();
}

function ppo_asset_pay_amount_text(t, pay_type, pay_value){
	var dialog = t.closest('.pix-modal-panel, .pix-modal-dialog, .pix-hs-modal, .pix-modal').first(),
		amount = $.trim(dialog.find('.agree-price span').first().text());

	if(!amount){
		amount = $.trim(dialog.find('.price-info .price').first().text());
	}

	if(!amount){
		amount = $.trim(dialog.find('.need-pay').first().text().replace(/\s+/g,' '));
	}

	if(!amount && pay_value){
		amount = pay_value;
	}

	if(!amount){
		amount = '以订单实际扣除为准';
	}

	if(pay_type == 'balance' && amount != '以订单实际扣除为准' && amount.indexOf('¥') === -1 && amount.indexOf('￥') === -1){
		amount = '¥' + amount;
	}

	return amount;
}

function ppo_asset_pay_subject_text(t, mode){
	var dialog = t.closest('.pix-modal-panel, .pix-modal-dialog, .pix-hs-modal, .pix-modal').first(),
		subject = $.trim(dialog.find('.scan-title,.vip-pay-title,.pay-title,.title,.need-price').first().text());

	if(subject){
		return subject;
	}

	if(mode == 'vip'){
		return '会员开通订单';
	}

	if(mode == 'jfcz'){
		return ppo_asset_pay_label('credit') + '充值订单';
	}

	if(mode == 'yecz'){
		return ppo_asset_pay_label('balance') + '充值订单';
	}

	return '当前订单';
}

function ppo_show_asset_pay_confirm(options, onConfirm){
	$('#ppo-asset-pay-confirm-modal').remove();

	var html = '<div id="ppo-asset-pay-confirm-modal" class="pix-modal pix-hs-modal pix-asset-pay-confirm-modal hidden" role="dialog" tabindex="-1" aria-labelledby="ppo-asset-pay-confirm-title">';
		html += '<div class="pix-modal-dialog hs-overlay-animation-target">';
		html += '<div class="pix-modal-panel asset-pay-confirm-modal">';
		html += '<button class="pix-modal-close" type="button" data-pix-modal-close="#ppo-asset-pay-confirm-modal" aria-label="关闭"><i class="ri-close-line"></i></button>';
		html += '<div class="asset-pay-confirm-icon '+options.pay_type+'"><i class="'+(options.pay_type == 'credit' ? 'ri-coin-fill' : 'ri-wallet-3-fill')+'"></i></div>';
		html += '<div id="ppo-asset-pay-confirm-title" class="asset-pay-confirm-title">确认使用'+options.label+'支付？</div>';
		html += '<div class="asset-pay-confirm-desc">确认后会立即从您的'+options.label+'中扣除，请核对订单信息。</div>';
		html += '<div class="asset-pay-confirm-card">';
		html += '<div class="asset-pay-confirm-row"><span>支付内容</span><strong>'+ppo_escape_html(options.subject)+'</strong></div>';
		html += '<div class="asset-pay-confirm-row"><span>扣除方式</span><strong>'+ppo_escape_html(options.label)+'支付</strong></div>';
		html += '<div class="asset-pay-confirm-row"><span>预计扣除</span><strong>'+ppo_escape_html(options.amount)+'</strong></div>';
		html += '</div>';
		html += '<div class="asset-pay-confirm-tips"><i class="ri-error-warning-line"></i> 站内资产支付会直接执行，支付成功后请以订单状态为准。</div>';
		html += '<div class="asset-pay-confirm-actions"><button type="button" class="asset-pay-confirm-cancel" data-pix-modal-close="#ppo-asset-pay-confirm-modal">再想想</button><button type="button" class="asset-pay-confirm-submit">确认支付</button></div>';
		html += '</div></div></div>';

	$('body').append(html);

	$('body').off('click.ppoAssetPayConfirm','.asset-pay-confirm-submit').one('click.ppoAssetPayConfirm','.asset-pay-confirm-submit',function(){
		ppo_hide_modal($('#ppo-asset-pay-confirm-modal'));
		onConfirm();
	});

	ppo_show_modal($('#ppo-asset-pay-confirm-modal'));
}

function ppo_submit_pay_order(t, pay_data, icon){
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: pay_data,
        beforeSend: function () {
			t.addClass('protect');
            toastfy('订单提交中..','info');
		
		},
	    success: function (data) {
			if(data.error){
				t.removeClass('protect');
				toastfy(data.error,'error');
				clearInterval(pay_order);
				return;
			}

			if(data.order_id){

				Cookies.set('order_id', data.order_id, { expires: 7 });

			}

			if(data.jump == 'jump'){
				t.parents('.ppo-pay-modal').append('<div class="pay-modal-overlay"><i class="ri-cup-line wait-pay-icon"></i><div class="wait-pay">等待支付中..</div><div class="wait-pay-time">5<b>分</b>0<b>秒</b></div></div>');
				wait_pay_dountdown();
				ppo_open_jump_pay(data.url);
			}	

			if(data.jump == 'scan'){
				t.parents('.ppo-pay-modal').append('<div class="pay-modal-overlay"><div class="pix-spinner"></div><div class="load-qrcode">加载二维码...</div></div>');
				pay_qrcode_modal(data,icon);
			}
	    }
	});

	//轮询订单状态
	pay_interval();
}

function ppo_open_jump_pay(url){
	if(!url){
		toastfy('支付链接生成失败','error');
		return;
	}

	if(typeof url === 'string' && url.indexOf('<form') !== -1){
		document.body.insertAdjacentHTML('beforeend', url);
		if(document.alipaysubmit){
			document.alipaysubmit.submit();
			return;
		}
	}

	window.location.href = url;
}

// 发起支付
$('body').on('click','.pay-sure-btn',function(){
	
	var t = $(this),
	    //order_id = Cookies.get('order_id'),
		mode = t.attr('mode'),
		data_id = t.attr('data'),
		pay_type = t.attr('pay_type'),
		icon = t.parents('.dd-pay-modal').find('.price-info .icon').html();

	if(Theme.uid<1){
		ppo_open_login_modal('请先登录后再支付');
		return;
	}

	if(t.hasClass('protect')){
		return;
	}

	if(!pay_type){
		toastfy('请选择支付方式','error');
		return;
	}

	if(mode == 'vip'){
		var vip_item = $('.vip-pay-item li').eq(data_id).find('.vp-price.active').attr('data');
	} 

	if(mode == 'post'){
		var data_id = Theme.pid;
		var order_email = $('#order-email').val().trim();
		if (!order_email) {
			toastfy('请填写邮箱地址','error');
			return;
		}
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(order_email)) {
			toastfy('邮箱格式不正确','error');
			return;
		}
	} else {
		var order_email = '';
	}

	if(mode == 'mj'){
		var mj_item = $('.mo-price-list a.price-item.active').attr('type'),
			data_id = Theme.tid;
	}

	if(mode == 'jfcz' || mode == 'yecz'){
		var pay_value = t.parents('.charge-inner-html').find('.charge-input-form input').val();
	}

	var pay_data = {
		'action': 'push_ppo_pay',
		nonce: Theme.pay_nonce,
		mode: mode,
		data_id: data_id,
		pay_type: pay_type,
		vip_item: vip_item,
		mj_item: mj_item,
		pay_value: pay_value,
		order_email: order_email,
	};

	if(pay_type == 'balance' || pay_type == 'credit'){
		ppo_show_asset_pay_confirm({
			pay_type: pay_type,
			label: ppo_asset_pay_label(pay_type),
			subject: ppo_asset_pay_subject_text(t, mode),
			amount: ppo_asset_pay_amount_text(t, pay_type, pay_value)
		}, function(){
			ppo_submit_pay_order(t, pay_data, icon);
		});
		return;
	}

	ppo_submit_pay_order(t, pay_data, icon);
});

// 扫码支付弹窗
function pay_qrcode_modal(data, icon){
	$('#ppo-scan-modal').remove();
	var modal = ppo_modal('ppo-scan-modal');
	$('body').append(modal);
	var scanModal = $('#ppo-scan-modal');
	var pay_type = data.pay_type;
	if(pay_type == 'alipay'){
		var typet = '支付宝';
	} else {
		var typet = '微信';
	}

	var html = '<div class="pay_scan_modal pix-animation-fade pix-animation-fast">';
		html += '<div class="scan-title">'+data.subject+'</div>';
		html += '<div class="scan-qrcode"><img src="'+data.url+'"></div>';
		html += '<div class="scan-price"><div class="icon">'+(icon || '¥')+'</div>'+data.total_amount+'</div>';
		html += '<div class="scan-tips">请打开手机，使用'+typet+'扫码支付</div>';
		html += '<div class="pay-countdown">「 <div class="left-time">5<b>分</b>0<b>秒</b></div> 」</div>';
		html += '</div>';

	$('body').addClass(''+pay_type+'-scan-box-open');
	scanModal.one('pix:modal:shown', function () {
		scanModal.find('.ppo-scan-modal .inner').append(html);
		pay_dountdown();
	});

	scanModal.one('pix:modal:hidden', function () {
		clearInterval(pay_order);
		$('body').removeClass('alipay-scan-box-open wepay-scan-box-open');
	});
	ppo_show_modal(scanModal);

}

// 支付监听
function pay_interval(){
	pay_order = setInterval(function () {
		var order_id = Cookies.get('order_id');
		$.ajax({
			type: "post",
			url:Theme.ajaxurl,
			dataType:  'json',
			data: {
				'action':'pay_interval',
                nonce: Theme.pay_nonce,
				order_id: order_id,
				},	

			beforeSend: function () {
				
			},
			success: function(data){
				if(data.state == '1'){
					clearInterval(pay_order);
					toastfy('支付成功，正在跳转..','success');
					Cookies.remove('order_id');
					setTimeout(function () {
						location.reload();
					}, 500);
					
				}	
			}	
		});
	}, 1500);	
}

// 支付倒计时
function pay_dountdown(){
	var countdown = 5 * 60 * 1000; // 5分钟

	// 开始倒计时
	var timer = setInterval(function() {
		var minutes = Math.floor(countdown / 60000);
		var seconds = ((countdown % 60000) / 1000).toFixed(0);
		$('.pay-countdown .left-time').html(minutes + '<b>分</b>' + seconds + '<b>秒</b>');
		
		// 格式化秒数，确保保留两位数
		if (seconds < 10) {
			seconds = '0' + seconds;
		}
		// 减去一秒
		countdown -= 1000;
		
		if (countdown < 0) {
			clearInterval(timer);
			clearInterval(pay_order);
			$('.scan-qrcode').empty();
			$('.ppo-scan-modal').append('<div class="pay-modal-overlay"><i class="ri-close-circle-line pay-error"></i><div class="scan-error">支付超时</div><a class="cancel-pay">支付失败</a></div>')
			// 在倒计时结束后执行的任务
			// Your task here...
		}
	}, 1000);
}

// 等待支付倒计时
function wait_pay_dountdown(){
	var countdown = 5 * 60 * 1000; // 5分钟

	// 开始倒计时
	var timer = setInterval(function() {
		var minutes = Math.floor(countdown / 60000);
		var seconds = ((countdown % 60000) / 1000).toFixed(0);
		$('.wait-pay-time').html(minutes + '<b>分</b>' + seconds + '<b>秒</b>');
		
		// 格式化秒数，确保保留两位数
		if (seconds < 10) {
			seconds = '0' + seconds;
		}
		// 减去一秒
		countdown -= 1000;
		
		if (countdown < 0) {
			clearInterval(timer);
			clearInterval(pay_order);
			$('.pay-modal-overlay').remove();
			$('.ppo-pay-modal').append('<div class="pay-modal-overlay"><i class="ri-close-circle-line pay-error"></i><div class="scan-error">支付超时</div><a class="cancel-pay">支付失败</a></div>');
			// 在倒计时结束后执行的任务
			// Your task here...
		}
	}, 1000);
}

// 关闭支付模态窗口
$('body').on('click','a.cancel-pay',function(){
	if ($('#ppo-scan-modal').length) {
		ppo_hide_modal($('#ppo-scan-modal'));
	}
	if ($('#ppo-pay-modal').length) {
		ppo_hide_modal($('#ppo-pay-modal'));
	}
});

//支付下载模态框
$('body').on('click','.dd-buy-pass',function(){

	var modal = ppo_down_modal(),
		title = $('.download-box .dd-title').html(),
		t = $(this);

	if(t.hasClass('modal')){
		$('#ppo-down-modal').remove();
		$('body').append(modal);
		ppo_show_modal($('#ppo-down-modal'));
	
		if($('.ppo-down-content').length > 0){
			return;
		}
		
		$.ajax({
			type: "post",
			url:Theme.ajaxurl,
			dataType:  'json',
			data: {
				'action':'ppo_down_modal',
				pid: Theme.pid,
				title:title,
				},	
	
			beforeSend: function () {
				loading_start($('.ppo-down-modal .inner'));
			},
			success: function(data){
				loading_done($('.ppo-down-modal .inner'));
				$('.ppo-down-modal .inner').append(data.html);
			},
			error: function(){
				loading_done($('.ppo-down-modal .inner'));
				$('.ppo-down-modal .inner').append('<div class="down-error"><i class="ri-error-warning-line"></i>下载信息加载失败，请刷新后重试</div>');
			}
		});
	} else {
		var url = Theme.home_url+'/downpage?pid='+Theme.pid;
		window.open(url);
	}

	
});


// 密码下载弹窗

$('body').on('click','.dd-buy-pwd',function() {
	$('.pwd-modal-inner .pwd-content').empty();
	var modal = `<div id="pwd-modal" class="pix-modal pix-hs-modal pix-pwd-modal hidden" role="dialog" tabindex="-1" aria-labelledby="pwd-modal-title">
					<div class="pix-modal-dialog hs-overlay-animation-target">
					<div class="pix-modal-panel pwd-modal">
						<div id="pwd-modal-title" class="title"><i class="ri-lock-line"></i>密码验证</div>
						<div class="pwd-modal-inner">
							<div class="pwd-content"></div>
							<div class="pwd-code"><ul class="verify-item"></ul></div>
							<a class="check_pwd_code">确认</a>
						</div>
						<button class="pix-modal-close" type="button" data-pix-modal-close="#pwd-modal" aria-label="关闭"><i class="ri-close-line"></i></button>
					</div>
					</div>
				</div>`;		
	
	if(!$('#pwd-modal').length){
		$('body').append(modal);
		var codeItem = $(".verify-item").code({
			length: 4,
			skin: 'aa',
			keyup: function(d, val) {},
			done: function(p, val) {
				pwd_code = codeItem.getCode();
			},
			before: function(t, val) {
				// 做你想做的事            
				// 每次按下按键       
			},
			backspace: function(t) {
				// 做你想做的事           
				// 监听退格         
			}
		});	
	}	

	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'ppo_pwd_modal',
			pid: Theme.pid,
			},	

		beforeSend: function () {
			loading_start($('.pwd-modal-inner .pwd-content'));
		},
		success: function(data){
			loading_done($('.pwd-modal-inner .pwd-content'));
			$('.pwd-modal-inner .pwd-content').append(data.html);
		}	
	});

	ppo_show_modal($('#pwd-modal'));

});

$('body').on('click','.check_pwd_code',function() {
	if($('.code-input-item input').val() == '') {
		toastfy('请输入密码！','error');
		return;
	}
	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'ppo_pwd_check',
			pwd: pwd_code,
			pid: Theme.pid,
			},	

		beforeSend: function () {
			
		},
		success: function(data){
			if(data.code == 1){
				toastfy(data.msg,'success');
				setTimeout(function() {
					location.reload();
				  }, 2000);
			} else {
				toastfy(data.msg,'error');
			} 
		}	
	});
});

// ==== 点赞、收藏、评论和系统消息 ====

// 文章点赞
$('body').on('click','.like-btn',function(){
	var t = $(this),
		pid = t.attr('pid') ? t.attr('pid') : Theme.pid,
		uid = Theme.uid,
		isLiked = t.attr('action');

		if (!ppo_require_login('请先登录后再点赞')) {
            return;
        }

		$.ajax({
            url: Theme.ajaxurl, // WordPress提供的AJAX处理URL
            type: 'POST',
			dataType:  'json',
            data: {
                action: 'post_like_action', // 自定义的AJAX动作名称
                nonce: Theme.user_nonce,
                post_id: pid,
            },
			beforeSend: function () {
				t.hide();
				t.parent().append('<div class="loading pix-spinner pix-spinner-sm"></div>');
			},
            success: function(data) {
			   t.show();
			   t.siblings('.loading').remove();
			   if(data.success === false){
					toastfy(data.data && data.data.msg ? data.data.msg : '操作失败','error');
					return;
			   }
               toastfy(data.msg,'success');
			   t.children('span').text(data.count);
			   if(data.liked === false || (typeof data.liked === 'undefined' && isLiked == 'liked')) {
					t.find('i').attr('class','ri-heart-3-line');
					t.attr('action','like');
			   } else {
					t.find('i').attr('class','ri-heart-3-fill');
					t.attr('action','liked');
			   }
            },
			error: function(xhr) {
				t.show();
				t.siblings('.loading').remove();
				var msg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.msg ? xhr.responseJSON.data.msg : '操作失败，请重试';
				toastfy(msg,'error');
			}
        });
});

// 文章收藏
$('body').on('click','.collect-btn',function(){
	var t = $(this),
		pid = t.attr('pid') ? t.attr('pid') : Theme.pid,
		uid = Theme.uid,
		is_Coled = t.attr('action');

		if (!ppo_require_login('请先登录后再收藏')) {
            return;
        }

		$.ajax({
            url: Theme.ajaxurl, // WordPress提供的AJAX处理URL
            type: 'POST',
			dataType:  'json',
            data: {
                action: 'post_collect_action', // 自定义的AJAX动作名称
                nonce: Theme.user_nonce,
                post_id: pid,
            },
			beforeSend: function () {
				t.hide();
				t.parent().append('<div class="loading pix-spinner pix-spinner-sm"></div>');
			},
            success: function(data) {
			   t.show();
			   t.siblings('.loading').remove();
			   if(data.success === false){
					toastfy(data.data && data.data.msg ? data.data.msg : '操作失败','error');
					return;
			   }
               toastfy(data.msg,'success');
			   t.children('span').text(data.count);
			   if(data.collected === false || (typeof data.collected === 'undefined' && is_Coled == 'coled')) {
					t.find('i').attr('class','ri-bookmark-3-line');
					t.attr('action','col');
			   } else {
					t.find('i').attr('class','ri-bookmark-3-fill');
					t.attr('action','coled');
			   }
            },
			error: function(xhr) {
				t.show();
				t.siblings('.loading').remove();
				var msg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.msg ? xhr.responseJSON.data.msg : '操作失败，请重试';
				toastfy(msg,'error');
			}
        });
});



/* $(document).ready(function(){

	var uid = Theme.uid;
    var ajaxRequests = [
        { action: 'get_user_power_ajax' },
        { action: 'get_user_join_circle' }
    ];

    function sendAjaxRequest(request) {
        $.ajax({
            type: "post",
            url: Theme.ajaxurl,
            dataType: 'json',
            data: {
                'action': request.action,
                'uid': uid
            },
            beforeSend: function() {
                // 可以放置一些发送前的逻辑，例如加载动画
            },
            success: function(data) {
				if (data && data.data) {
                    ppo_user_data.push(data.data); // 将数据添加到数组中
                    // 可以在这里添加其他处理逻辑，比如更新UI等
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // 添加错误处理逻辑
                //handleError(textStatus, errorThrown);
            }
        });
    }

    function handleResponse(data) {
        // 处理成功响应的数据
        console.log(data); // 根据实际情况替换
    }

    function handleError(textStatus, errorThrown) {
        // 处理错误情况，例如显示错误消息
        console.error("AJAX Error: " + textStatus, errorThrown);
    }

    // 发送所有请求
    ajaxRequests.forEach(sendAjaxRequest);
	console.log(ppo_user_data); 

}); */


//消息中心取消回复按钮
$('body').on('click','.cancel-msg-reply-link',function(){
	var t = $(this),
		temp = $('#comment_form_tmp'),
	    form = $("#t_commentform").prop('outerHTML');
		t.parents('form').remove();
		temp.html(form);
		
		$("#comment_parent").val('');
		$("#comment_post_ID").val('');
});	

// 无限加载评论消息
$(document).ready(function() {
	var com_msg_paged = 2;
    var com_msg_loading = false;
    var com_msg_finished = false; 

	function loadNotifications(action) {
        if (com_msg_loading || com_msg_finished) return;
        com_msg_loading = true;

        $.ajax({
            type: 'POST',
            url: Theme.ajaxurl,
            data: {
                action: action,
                com_msg_paged : com_msg_paged ,
            },
			beforeSend: function() {
                $('.msg-load-more').show().html('<span class="pix-spinner"></span>');
            },
            success: function(response) {
                if (response.status == 1) {
                    var notifications = response.html;
                    if (notifications.length > 0) {
                        $('.msg-box-append').append(notifications);
						refresh_user_runtime($('.msg-box-append'));
                        $('.msg-load-more').hide().empty();
                        com_msg_paged ++;

                    } else {
                        com_msg_finished = true;
                        $('.msg-load-more').show().html('没有更多了');
                    }
                } else {
                   toastfy('加载失败，请刷新页面重试。','error');
                }
                com_msg_loading = false;
            },
            error: function() {
                toastfy('请求失败，请刷新页面重试。','error');
                com_msg_loading = false;
            }
        });
    }

	// 滚动监听
	$(window).on('scroll', function() {
        if (com_msg_loading || com_msg_finished) return;

        var $container = $('.msg-box-append');
        if ($container.length === 0) return;
		var action = $container.attr('action');

        // 容器顶部到页面顶部的距离
        var containerTop = $container.offset().top;

        // 容器底部到页面顶部的距离
        var containerBottom = containerTop + $container.outerHeight();

        // 当前滚动条位置（页面顶部）
        var scrollTop = $(window).scrollTop();

        // 浏览器窗口高度
        var windowHeight = $(window).height();

        // 当页面滚动到容器底部 100px 范围内，触发加载
        if (scrollTop + windowHeight >= containerBottom - 100) {
            loadNotifications(action);
        }
    });

	// 回复按钮
	var $commentForm = $('#comment_form_tmp');

    if ($commentForm.length) {
        var $formContent = $commentForm.children().first(); // 表单内容

        // 事件委托：监听整个 document
        $(document).on('click', '.reply-btn a', function(e) {
            e.preventDefault(); // 阻止默认跳转

            var $btn = $(this);
            var $replyItem = $btn.closest('.reply-item');

            if ($replyItem.length) {
                // 移动表单内容
                $replyItem.append($formContent);
                $commentForm.empty();

                // 设置表单隐藏字段
                var postId = $btn.data('postid');
                var commentId = $btn.data('commentid');

                $formContent.find('#comment_post_ID').val(postId);
                $formContent.find('#comment_parent').val(commentId);

                // 聚焦到 textarea，并清空内容
                $formContent.find('.mo-com-textarea').focus().val('');
            }
        });
    }

	

});

// 评论点赞
$(document).on('click', '.comment-like-btn', function(e) {
	e.preventDefault();

	var $btn = $(this);
	var commentId = $btn.data('comment-id');
	var liked = $btn.hasClass('liked') ? 1 : 0;

	$.ajax({
		url: Theme.ajaxurl,
		type: 'POST',
		data: {
			action: 'like_or_unlike_comment',
			comment_id: commentId,
			liked: liked,
		},
		beforeSend: function() {
			$btn.prop('disabled', true);
		},
		success: function(response) {
			if (response.success) {
				var count = response.data.like_count;
				$btn.find('.count').text(count);

				if (response.data.liked) {
					$btn.find('.like-count').html('<i class="ri-thumb-up-fill"></i>');
					$btn.addClass('liked');
					toastfy('点赞成功！','success');
				} else {
					$btn.find('.like-count').html('<i class="ri-thumb-up-line"></i>');
					$btn.removeClass('liked');
					toastfy('点赞已取消！','info');
				}
			} else {
				// ⚡️未登录或者其他错误
				alert(response.data.message || '请登录后操作');
			}
		},
		complete: function() {
			$btn.prop('disabled', false);
		}
	});
});

// 加载系统消息内页
$(document).on('click', '.system-msg-item', function() {
	var id = $(this).data('id');
	var content = $('.system-msg-modal-content');
	ppo_show_modal($('#system-msg-modal'));

	$('.system-msg-modal-title').empty();
	$('.system-msg-modal-content').empty();
	$('.system-modal-meta').empty();

	var total_count = $('.system-nav').find('.msg-badge').text();

	if(total_count > 0) {
		total_count --;
		$('.system-nav').find('.msg-badge').text(total_count);
	}

	if(total_count <= 1) {
		$('.system-nav').find('.msg-badge').remove();
	}

	$(this).find('.unread-bage').remove();
	$.ajax({
		url: Theme.ajaxurl,
		type: 'POST',
		data: {
			action: 'system_msg_detail',
			id: id,
		},
		beforeSend: function() {
			loading_start(content);
		},
		success: function(response) {
			$('.system-msg-modal-title').html(response.title);
			$('.system-msg-modal-content').html(response.content);
			$('.system-modal-meta').html(response.meta);
			refresh_user_runtime($('.system-msg-modal'));
		},
	});
});

// ==== 消息中心和私信 ====

// 私信模态窗口
var other_chat_id = 0;
 // 上滚动加载
 var chat_loading = false;
 var noMoreHistory = false;
 var scroll_ready = false;
 var beforeMessageId = 0; // 在初始消息加载完后赋值最早一条消息 ID
 var message_mobile_chat_timer = null;
 var message_mobile_swipe = {
	active: false,
	startX: 0,
	startY: 0,
	currentX: 0,
	currentY: 0
 };

function is_message_mobile_view() {
	return window.matchMedia && window.matchMedia('(max-width: 960px)').matches && $('.pix-dashboard-message-page .pix-dashboard-message-whisper-panel').length > 0;
}

function refresh_user_runtime($scope) {
	var scope = $scope && $scope.length ? $scope : $(document);
	if ($.fn.timeago) {
		scope.find('time.timeago').timeago();
	}
	init_user_readmore(scope);
	init_user_level_swiper(scope);
	pix_init_vip_plan_swiper(scope);
	pix_init_vip_faq(scope);
	pix_init_dashboard_vip_mobile(scope);
	pixInitUserAvatarUploader(scope);
	pixInitUserCoverUploader(scope);
	var lazyLoader = window.lazyLoadInstance || window.lazyLoad;
	if (lazyLoader && typeof lazyLoader.update === 'function') {
		lazyLoader.update();
	}
}

function init_user_readmore($scope) {
	if (!window.ReadMore || typeof window.ReadMore.init !== 'function') {
		return;
	}

	var scope = $scope && $scope.length ? $scope : $(document);
	var $targets = scope.find('[data-max-length]');
	if (scope.is && scope.is('[data-max-length]')) {
		$targets = $targets.add(scope);
	}

	$targets.each(function() {
		window.ReadMore.init(this);
	});
}

function init_user_level_swiper($scope) {
	if (!window.Swiper) {
		return;
	}

	var scope = $scope && $scope.length ? $scope : $(document);
	var $swiper = scope.find('#user-lv-detail-list');
	if (!$swiper.length && scope.is && scope.is('#user-lv-detail-list')) {
		$swiper = scope;
	}

	$swiper.each(function() {
		if (this.swiper) {
			this.swiper.update();
			return;
		}

		new Swiper(this, {
			slidesPerView: 5.5,
			paginationClickable: true,
			spaceBetween: 10,
			freeMode: true,
			loop: false
		});
	});
}

window.refresh_user_runtime = refresh_user_runtime;
window.init_user_readmore = init_user_readmore;
window.init_user_level_swiper = init_user_level_swiper;
window.pix_init_vip_plan_swiper = pix_init_vip_plan_swiper;
window.initVipPlansSwiper = pix_init_vip_plan_swiper;
window.pixInitUserAvatarUploader = pixInitUserAvatarUploader;
window.pixInitUserCoverUploader = pixInitUserCoverUploader;

function open_message_mobile_chat($item) {
	if (!is_message_mobile_view()) {
		return;
	}

	var title = $.trim($item.find('.pix-dashboard-message-title > span').first().text()) || '我的消息';
	var $panel = $item.closest('.pix-dashboard-message-whisper-panel');

	clearTimeout(message_mobile_chat_timer);
	$panel.removeClass('is-mobile-chat-closing').addClass('is-mobile-chat-opening');
	$panel.find('.pix-dashboard-message-mobile-chat-title').text(title);

	requestAnimationFrame(function() {
		$panel.addClass('is-mobile-chat-open');
	});

	message_mobile_chat_timer = setTimeout(function() {
		$panel.removeClass('is-mobile-chat-opening');
	}, 340);
}

function close_message_mobile_chat() {
	var $panel = $('.pix-dashboard-message-whisper-panel');

	if (!$panel.hasClass('is-mobile-chat-open')) {
		$panel.removeClass('is-mobile-chat-opening is-mobile-chat-closing');
		return;
	}

	clearTimeout(message_mobile_chat_timer);
	$panel.removeClass('is-mobile-chat-opening').addClass('is-mobile-chat-closing');

	requestAnimationFrame(function() {
		$panel.removeClass('is-mobile-chat-open');
	});

	message_mobile_chat_timer = setTimeout(function() {
		$panel.removeClass('is-mobile-chat-closing');
	}, 340);
}

function update_message_chat_footer(chatBoxHtml) {
	var html = chatBoxHtml || '';
	var hasChatBox = $.trim(html).length > 0;

	$('.chat-box-warp').html(html);
	$('.chat-footer-texarea').toggleClass('is-empty', !hasChatBox);
}

$(document).on('click', '.send-msg-btn', function() {
	var t = $(this),
		receive_id = t.data('uid');
		//sender_id = Theme.uid;

		if (t.hasClass('is-disabled')) {
			toastfy(t.data('message') || '暂时不能发送私信', 'error');
			return;
		}

		other_chat_id = receive_id;
		ppo_show_modal($('#user-chat-modal'));
		$('.private-msg-list-content').empty();
		$.ajax({
			url: Theme.ajaxurl,
			type: 'POST',
			data: {
				action: 'get_private_msg_data',
				receive_id: receive_id,
				//sender_id: sender_id,
			},
			beforeSend: function() {
				loading_start($('.private-msg-list-content'));
			},
			success: function(data) {
				$('.user-chat-modal .avatar').html(data.avatar);
				$('.user-chat-modal .user_name').html(data.user_header || data.name);
				$('.user-chat-modal .private-msg-list-content').html(data.message);
				noMoreHistory = false;
				scroll_ready = true;
				refresh_user_runtime($('.user-chat-modal'));
				scrollChatToBottom();

				if (data.before_id) {
					beforeMessageId = data.before_id;
				}
			},
		});	

});


// 私信页面加载
$(document).on('click', '.chat-user-list-item', function() {
	$('.private-msg-list-content').empty();
	var t = $(this),
		receive_id = t.data('uid'),
		//sender_id = Theme.uid;
		other_chat_id = receive_id;
		//$('.push-private-msg-btn').attr('data-uid', receive_id);
		open_message_mobile_chat(t);

		var total_count = $('.whisper-nav').find('.msg-badge').text();
		if(total_count > 0) {
			total_count --;
			$('.whisper-nav').find('.msg-badge').text(total_count);
		}
	
		if(total_count <= 1) {
			$('.whisper-nav').find('.msg-badge').remove();
		}

		t.find('.chat-unread-count').remove();
		$.ajax({
			url: Theme.ajaxurl,
			type: 'POST',
			data: {
				action: 'get_private_msg_data',
				receive_id: receive_id,
				//sender_id: sender_id,
			},
			beforeSend: function() {
				loading_start($('.private-msg-list-content'));
			},
			success: function(data) {
				//$('.user-chat-modal .avatar').html(data.avatar);
				//$('.user-chat-modal .user_name').html(data.name);
				$('.private-msg-list-content').html(data.message);
				update_message_chat_footer(data.chat_box);
				$('.push-private-msg-btn').attr('data-uid', receive_id);
				noMoreHistory = false;
				scroll_ready = true;
				//chat_loading = false
				refresh_user_runtime($('.pix-dashboard-message-whisper-panel'));
				scrollChatToBottom();

				if (data.before_id) {
					beforeMessageId = data.before_id;
				}
			},
		});	

});

$(document).on('click', '.pix-dashboard-message-mobile-back', function() {
	close_message_mobile_chat();
});

$(document).on('pix:dashboard-page-change', function() {
	if (!is_message_mobile_view()) {
		return;
	}
	$('.pix-dashboard-message-whisper-panel .chat-user-list-item').removeClass('active');
	close_message_mobile_chat();
	refresh_user_runtime($('.pix-dashboard-message-page'));
});

$(document).on('touchstart', '.pix-dashboard-message-whisper-panel.is-mobile-chat-open .pix-dashboard-message-chat-main', function(event) {
	if (!is_message_mobile_view() || event.originalEvent.touches.length !== 1) {
		return;
	}

	var touch = event.originalEvent.touches[0];

	message_mobile_swipe.active = touch.clientX <= 36;
	message_mobile_swipe.startX = touch.clientX;
	message_mobile_swipe.startY = touch.clientY;
	message_mobile_swipe.currentX = touch.clientX;
	message_mobile_swipe.currentY = touch.clientY;
});

$(document).on('touchmove', '.pix-dashboard-message-whisper-panel.is-mobile-chat-open .pix-dashboard-message-chat-main', function(event) {
	if (!message_mobile_swipe.active || !event.originalEvent.touches.length) {
		return;
	}

	var touch = event.originalEvent.touches[0];
	message_mobile_swipe.currentX = touch.clientX;
	message_mobile_swipe.currentY = touch.clientY;
});

$(document).on('touchend touchcancel', '.pix-dashboard-message-whisper-panel.is-mobile-chat-open .pix-dashboard-message-chat-main', function() {
	if (!message_mobile_swipe.active) {
		return;
	}

	var deltaX = message_mobile_swipe.currentX - message_mobile_swipe.startX;
	var deltaY = Math.abs(message_mobile_swipe.currentY - message_mobile_swipe.startY);

	message_mobile_swipe.active = false;

	if (deltaX >= 72 && deltaY <= 48) {
		close_message_mobile_chat();
	}
});

// 发送私信
$(document).on('click', '.push-private-msg-btn', function() {
	var t = $(this),
		receive_id = t.data('uid'),
		//sender_id = Theme.uid,
		msg = $('.private-msg-textarea').val();

		if(t.hasClass('protect')){
			return;
		}

		if(msg == '') {
			toastfy('请输入内容！','error');
			return;
		}

		$.ajax({
			url: Theme.ajaxurl,
			type: 'POST',
			data: {
				action: 'send_private_msg',
				receive_id: receive_id,
				//sender_id: sender_id,
				msg: msg,
				nonce: Theme.msg_nonce,
			},
			beforeSend: function() {
				t.html('<span class="pix-spinner pix-spinner-sm"></span>');
				t.addClass('protect');
			},
			success: function(data) {
				t.html('发送');
				t.removeClass('protect');
				if(!data || data.status != 1){
					toastfy(data && data.msg ? data.msg : '发送失败，请重试','error');
					return;
				}
				$('.private-msg-list-content').append(data.msg);

				$('.private-msg-textarea').val('');
				refresh_user_runtime($('.pix-dashboard-message-whisper-panel'));
				scrollChatToBottom();
			},
			error: function() {
				t.html('发送');
				t.removeClass('protect');
				toastfy('发送失败，请重试','error');
			}
		});	

});

  function scrollChatToBottom() {
    var $chatContainer = $('.chat-scroll-body');
    if ($chatContainer.length) {
      $chatContainer.scrollTop($chatContainer[0].scrollHeight);
    }
  }

$(document).on('scroll', '.chat-scroll-body', function () {
  var $container = $(this);

  if (scroll_ready && $container.scrollTop() <= 50 && !chat_loading && !noMoreHistory) {
    chat_loading = true;
	var receive_id = other_chat_id;
    //var receive_id = $('.push-private-msg-btn').data('uid');
	//var receive_id = $('.chat-user-list-item.active').data('uid');
    var oldScrollHeight = $container[0].scrollHeight;
	//console.log(receive_id);
    $.ajax({
      url: Theme.ajaxurl,
      type: 'POST',
      data: {
        action: 'ppo_load_previous_messages',
        //sender_id: Theme.uid,
        receive_id: receive_id,
        before_id: beforeMessageId || 0
      },
	  beforeSend: function() {
		if($('.ppo-chat-item').length > 0){
			$('.private-msg-list-content').prepend('<div class="chat-loading"><span class="pix-spinner pix-spinner-sm"></span></div>');
		}
		
	  },
      success: function (res) {
		$('.chat-loading').remove();
        if (res.success && Array.isArray(res.data)) {
		  
          var messages = res.data;
          if (messages.length === 0) {
            noMoreHistory = true;
            return;
          }

          // 若返回消息少于 limit（如你设置的是 20），说明没更多了
          if (messages.length < 20) {
            noMoreHistory = true;
          }

          var html = '';
          var minId = beforeMessageId || messages[0].id;

          messages.forEach(msg => {
            html += renderMessageHTML(msg); // 你自己定义的渲染方法
            if (msg.id < minId) {
              minId = msg.id;
            }
          });

          beforeMessageId = minId; // 记录这批消息的最小 ID
          $('.private-msg-list-content').prepend(html);

          // 恢复滚动位置
          const newScrollHeight = $container[0].scrollHeight;
          $container.scrollTop(newScrollHeight - oldScrollHeight);

		  refresh_user_runtime($container);
        } else {
          noMoreHistory = true;
        }
      },
      complete: function () {
        chat_loading = false;
      }
    });
  }
});

// 加载第一个会话内容
$(document).ready(function () {
		if(is_message_mobile_view()) {
			$('.pix-dashboard-message-whisper-panel .chat-user-list-item').removeClass('active');
			close_message_mobile_chat();
			return;
		}

		var receive_id = $('.chat-user-list-item').first().data('uid');

		//sender_id = Theme.uid;
		other_chat_id = receive_id;
		//$('.push-private-msg-btn').attr('data-uid', receive_id);

		if($('.chat-user-list-item').length > 0) {
			$.ajax({
				url: Theme.ajaxurl,
				type: 'POST',
				data: {
					action: 'get_private_msg_data',
					receive_id: receive_id,
					//sender_id: sender_id,
				},
				beforeSend: function() {
					loading_start($('.private-msg-list-content'));
				},
				success: function(data) {
					//$('.user-chat-modal .avatar').html(data.avatar);
					//$('.user-chat-modal .user_name').html(data.name);
					$('.private-msg-list-content').html(data.message);
					update_message_chat_footer(data.chat_box);
					$('.push-private-msg-btn').attr('data-uid', receive_id);
					noMoreHistory = false;
					scroll_ready = true;
					//chat_loading = false
					refresh_user_runtime($('.pix-dashboard-message-whisper-panel'));
					scrollChatToBottom();
	
					if (data.before_id) {
						beforeMessageId = data.before_id;
					}
				},
			});	
		}
});


  function renderMessageHTML(msg) {
	var isMe = (msg.sender_id == Theme.uid);
	var className = isMe ? 'me' : 'other';
	//var avatar = isMe ? '' : '';
	const timeText = msg.show_time ? msg.send_time : '';
	let html = '';

	if (timeText) {
		html += `<div class="ppo-chat-timestamp"><time class="timeago" itemprop="datePublished" datetime="${timeText}">${timeText}</time></div>`;
	}

	html += `
		<div class="ppo-chat-item ${className}">
			<div class="chat-avatar">${msg.avatar}</div>
			<div class="${className}_content">
				<div class="chat-bubble">${msg.message}</div>
			</div>
		</div>
	`;
	return html;
  }

$(document).on('click','.chat-user-list-item',function(){
	$(this).addClass('active').siblings().removeClass('active');
})

// ==== 关注列表、签到和等级刷新 ====

// ajax关注用户
$(document).on('click', '.follow-user-btn', function() {
	var t = $(this),
		following_id = t.data('uid'),
		action_type = t.attr('action');

		if (!ppo_require_login('请先登录后再关注')) {
			return;
		}

		$.ajax({
			url: Theme.ajaxurl,
			type: 'POST',
			data: {
				action: action_type == 'unfollow' ? 'ppo_unfollow_user_ajax' : 'ppo_follow_user_ajax',
				following_id: following_id,
			},
			beforeSend: function() {
				t.prepend('<span class="pix-spinner pix-spinner-xs"></span>');
			},
			success: function(response) {
				if (response.success) {
					if(action_type == 'unfollow') {
						t.text('关注').removeClass('unfollow');
						toastfy('取消关注','success');
						t.attr('action','follow');
						if(t.hasClass('follow-list-btn')){
							t.parents('.follower-item').remove();
						}
					} else {
						t.text('已关注').addClass('unfollow'); // 可根据需要加类名控制样式
						toastfy('关注成功！','success');
						t.attr('action','unfollow');
					}
					
				} else {
					t.text('关注').removeClass('unfollow');
					var msg = response.data || '操作失败，请重试';
					if(String(msg).indexOf('登录') !== -1){
						ppo_open_login_modal(msg);
					} else {
						toastfy(msg,'error');
					}
				}
			},
			error: function(xhr) {
				var msg = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : '操作失败，请重试';
				if(String(msg).indexOf('登录') !== -1){
					ppo_open_login_modal(msg);
				} else {
					toastfy(msg,'error');
				}
			},
		});	

});

// 关注粉丝切换按钮
$(document).on('click', '.follow-nav a', function() {
	var t = $(this);
		$('.follow-nav a').removeClass('active');
		t.addClass('active');
}); 

$(document).on('click', '.user-follow-block a', function() {
	$('.ppo-navtab a').removeClass('active');
});

// 签到通知
$(document).on('htmx:afterOnLoad', '.user-sign-modal-btn', function () {
	ppo_show_modal($('#checkin-modal'));
});

$(document).on('click', '.task-checkin-btn', function () {
	toastfy('数据拉取中', 'normal');
});

$(document).on('click', '.checkin-modal-cancel', function () {
	ppo_hide_modal($('#checkin-modal'));
  });  

$(document).on('pix:modal:hidden', '#checkin-modal', function () {
	$(this).remove();
	$('#checkin-modal-here').empty();
});

  $(document).on('click', '.user-has-sign-btn , .checkin-completed', function() {
	toastfy('今日已签到', 'error');
  });

  // 签到
  $(document).on('click', '.user-sign-btn', function() {
		var t = $(this);
		if (t.hasClass('protected')) {
			return;
		}

		$.ajax({
			url: Theme.ajaxurl,
			type: 'POST',
			data: {
				action: 'ppo_checkin',
			},
			beforeSend: function() {
				t.addClass('protected');
			},
			success: function(data) {
				if (data && data.already_checked) {
					t.addClass('protected');
					t.parent().html('<a class="user-has-sign-btn">今日已签到</a>');
					toastfy(data.msg || '今日已签到', 'error');
					return;
				}

				$('.checkin-list.today').addClass('checked');
				t.parent().html('<a class="user-has-sign-btn">今日已签到</a>');
				if (typeof data.streak !== 'undefined') {
					$('.checkin-streak b').html(data.streak);
				}
				if (typeof data.total !== 'undefined') {
					$('.total-sign').html('已累计签到' + data.total + '天');
				}
				toastfy(data.msg + ', ' + ppo_xp_label() + ': +' + data.xp + '  ' + ppo_asset_pay_label('credit') + ': +' + data.point, 'success');
			},
			error: function() {
				t.removeClass('protected');
				toastfy('签到失败，请稍后再试', 'error');
			}
		});
				
  });

// 用户成长体系拖拽滑动显示
init_user_level_swiper($(document));

// ==== 钱包、充值、积分和兑换卡 ====

// 转账功能占位
$(document).on('click', '.transfer-cash-btn, .transfer-credit-btn', function(e) {
	e.preventDefault();
	toastfy('功能开发中..', 'info');
});

// 充值ajax模态
$(document).on('click', '.charge-cash-btn , .charge-credit-btn', function() {
	var t = $(this);
	if(t.hasClass('charge-cash-btn')){
		charge_action = 'charge_cash_callback';
	} else if(t.hasClass('charge-credit-btn')){
		charge_action = 'charge_credit_callback';
	}
	ppo_show_modal($('#charge-modal'));
});

$(document).on('pix:modal:hidden', '#charge-modal', function () {
	$('.charge-inner-html').empty();
	$('.charge-modal').removeClass('loaded');
});

$(document).on('pix:modal:shown', '#charge-modal', function () {

		var action = charge_action;
		$.ajax({
			url: Theme.ajaxurl,
			type: 'POST',
			data: {
				action: action,
				uid: Theme.uid,
			},
			beforeSend: function() {
				loading_start($('.charge-inner-html'));
			},
			success: function (response) {
				loading_done($('.charge-inner-html'));
				if (response.success) {
					$('.charge-inner-html').html(response.data);
					setTimeout(function() {
					$('.charge-modal').addClass('loaded');
					}, 100); 
				} else {
					toastfy('获取充值内容失败', 'error');
				} 
				
			},
			error: function () {
				loading_done($('.charge-inner-html'));
				$('.charge-modal').removeClass('loaded');
				toastfy('获取充值内容出错', 'error');
			}
		});
	});



//充值金额计算
$(document).on('click', '.charge-cash-set-item', function() {
	var t = $(this);
	//获取这是第几个充值金额选项
	var amount = t.data('amount');
	var index = t.index();
	t.addClass('active');
	$('.charge-cash-set-item').not(t).removeClass('active');
	$('input#charge-cash-amount').val(amount);

	$.ajax({
		url: Theme.ajaxurl,
		type: 'POST',
		data: {
			action: 'charge_cash_calculate_discount',
			uid: Theme.uid,
			index: index,
		},
		beforeSend: function() {
			$('.need-pay').remove();
            $('.need-pay-box').remove();
			$('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay-box"></div>');
		},
		success: function (response) {
			if (response.success) {
				$('.need-pay').remove();
            	$('.need-pay-box').remove();
				$('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay"><span>需支付：</span>¥' + response.data + '</div>');
			} else {
				$('.need-pay').remove();
				$('.need-pay-box').remove();
				toastfy('获取充值金额失败', 'error');
			}
		},
		error: function () {
			$('.need-pay').remove();
			$('.need-pay-box').remove();
			toastfy('获取充值内容出错', 'error');
		}
	});
});

//输入框金额格式化
// 添加防抖函数
function debounce(func, wait) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
}

// 使用防抖包装输入事件处理
const ChargeCashHandleInput = debounce(function() {
    var t = $(this);
    var cash = t.val();

    $.ajax({
        url: Theme.ajaxurl,
        type: 'POST',
        data: {
            action: 'charge_cash_input_format',
            uid: Theme.uid,
            cash: cash,
        },
        beforeSend: function() {
            $('.need-pay').remove();
            $('.need-pay-box').remove();
            $('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay-box"></div>');
        },
        success: function (response) {
            if (response.success == 'success') {
                $('.need-pay-box').remove();
                $('.need-pay').remove(); // 确保只保留一个
                $('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay"><span>需支付：</span>¥ ' + response.data + '</div>');
            } else {
                toastfy(response.data, 'error');
				$('.need-pay-box').remove();
				$('.need-pay').remove();
            }
        },
        error: function () {
			$('.need-pay').remove();
			$('.need-pay-box').remove();
			toastfy('获取充值金额失败，请重试', 'error');
        }
    });
}, 300); // 300ms防抖

$(document).on('input', 'input#charge-cash-amount', ChargeCashHandleInput);

$(document).on('focus', 'input#charge-cash-amount', function(){
	$('.charge-cash-set-item').removeClass('active');
});

// 积分换算
$(document).on('click', '.charge-credit-set-item', function() {
	var t = $(this);
	t.addClass('active');
	$('.charge-credit-set-item').not(t).removeClass('active');
	$('input#charge-credit-amount').val(t.data('amount'));

	$.ajax({
		url: Theme.ajaxurl,
		type: 'POST',
		data: {
			action: 'charge_credit_calculate',
			uid: Theme.uid,
			index: t.index(),
		},
		beforeSend: function() {
			$('.need-pay').remove();
            $('.need-pay-box').remove();
			$('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay-box"></div>');
		},
		success: function (response) {
			if (response.success) {
				$('.need-pay').remove();
            	$('.need-pay-box').remove();
				$('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay"><div class="left"><span>需支付：</span>¥' + response.charge_cash + '</div><div class="right"><span>共计可获得</span>' + response.total_credit + '' + response.credit_name + '</div></div>');
			} else {
				$('.need-pay').remove();
				$('.need-pay-box').remove();
				toastfy('获取充值金额失败', 'error');
			}
		},
		error: function () {
			$('.need-pay').remove();
			$('.need-pay-box').remove();
			toastfy('获取充值内容出错', 'error');
		}
	});
});

const ChargeCreditHandleInput = debounce(function() {
    var t = $(this);
    var cash = t.val();

    $.ajax({
        url: Theme.ajaxurl,
        type: 'POST',
        data: {
            action: 'charge_credit_input_format',
            uid: Theme.uid,
            cash: cash,
        },
        beforeSend: function() {
            $('.need-pay').remove();
            $('.need-pay-box').remove();
            $('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay-box"></div>');
        },
        success: function (response) {
            if (response.success == 'success') {
                $('.need-pay-box').remove();
                $('.need-pay').remove(); // 确保只保留一个
                $('.charge-inner-html').find('.pay-sure-btn').before('<div class="need-pay"><div class="left"><span>需支付：</span>¥' + response.charge_cash + '</div><div class="right"><span>共计可获得</span>' + response.total_credit + '' + response.credit_name + '</div></div>');
            } else {
                toastfy(response.data, 'error');
				$('.need-pay-box').remove();
				$('.need-pay').remove();
            }
        },
        error: function () {
			$('.need-pay').remove();
			$('.need-pay-box').remove();
			toastfy('获取充值金额失败，请重试', 'error');
        }
    });
}, 300); // 300ms防抖

$(document).on('input', 'input#charge-credit-amount', ChargeCreditHandleInput);

$(document).on('focus', 'input#charge-credit-amount', function(){
	$('.charge-credit-set-item').removeClass('active');
});

$(document).on('click', '.dashboard-tab .tab-item', function() {
	var t = $(this);
	t.addClass('active');
	t.siblings().removeClass('active');
});

// ==== Dashboard tab、订单和订单详情 ====

function ppo_update_order_countdowns(){
	var now = Math.floor(Date.now() / 1000);

	$('.order-countdown').each(function(){
		var t = $(this),
			expire = parseInt(t.attr('data-expire'), 10),
			leftSeconds = parseInt(t.attr('data-left'), 10),
			left = expire - now;

		if(!expire && leftSeconds){
			expire = now + leftSeconds;
			t.attr('data-expire', expire).removeAttr('data-left');
			left = leftSeconds;
		}

		if(!expire){
			return;
		}

		if(left <= 0){
			t.html('<i class="ri-time-line"></i> 已超时');
			t.addClass('expired');
			var item = t.closest('.user_order_item');
			var order_id = item.find('.order-btn.pay,.order-btn.cancel').first().data('order');
			item.find('.order-status').removeClass('order-status-0').addClass('order-status-3').text('已关闭');
			item.find('.order-btn.pay,.order-btn.cancel').remove();
			if(!item.find('.order-btn.delete').length){
				item.find('.order-action-right').append('<a href="javascript:;" class="order-btn delete" data-order="'+order_id+'">删除订单</a>');
			}
			return;
		}

		var minutes = Math.floor(left / 60),
			seconds = left % 60,
			hours = Math.floor(minutes / 60);

		minutes = minutes % 60;
		var text = hours > 0 ? hours + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0') : String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
		t.find('b').text(text);
	});
}

$(document).ready(function(){
	ppo_update_order_countdowns();
	setInterval(ppo_update_order_countdowns, 1000);
});

$(document).on('htmx:afterSwap', function(e){
	refresh_user_runtime($(e.target));
	if($(e.target).hasClass('order_record_list') || $(e.target).find('.order_record_list').length){
		ppo_update_order_countdowns();
	}
});

var order_detail_clipboard = null;

$(document).on('click', '.order-detail-link', function(){
        var id = $(this).closest('.user_order_item').data('order-id');
        var nonce = Theme.rest_nonce;
		ppo_show_modal($('#order-detail-modal'));
        $.ajax({
            url: '/wp-json/ppo/v1/user-order-detail?_wpnonce=' + nonce + '&id=' + encodeURIComponent(id),
            type: 'POST',
            beforeSend: function(){
                loading_start($('.order-detail-inner'));
            },
            success: function(data){
                $('.order-detail-inner').html(data.html);
                if (window.ClipboardJS) {
					if (order_detail_clipboard) {
						order_detail_clipboard.destroy();
					}
                    order_detail_clipboard = new ClipboardJS('.copy-order-id', {
						container: document.getElementById('order-detail-modal') || document.body
					});
                }
				refresh_user_runtime($('.order-detail-inner'));
            },
            error: function(){
                toastfy('加载订单详情失败', 'error');
                loading_done($('.order-detail-inner'));
            }
        });
    });

$(document).on('pix:modal:hidden', '#order-detail-modal', function () {
	if (order_detail_clipboard) {
		order_detail_clipboard.destroy();
		order_detail_clipboard = null;
	}
	$('.order-detail-inner').empty();
});

function ppo_order_action_request(t, endpoint, confirm_text, loading_text){
	var order_id = t.data('order');
	var original_text = t.text();
	if(t.hasClass('protect')){
		return;
	}

	if(!order_id){
		toastfy('订单号不存在','error');
		return;
	}

	if(confirm_text && !window.confirm(confirm_text)){
		return;
	}

	$.ajax({
		url: '/wp-json/ppo/v1/' + endpoint + '?_wpnonce=' + Theme.rest_nonce,
		type: 'POST',
		dataType: 'json',
		data: {
			order_id: order_id
		},
		beforeSend: function(){
			t.addClass('protect').text(loading_text);
		},
		success: function(data){
			if(data.error || data.code >= 400){
				toastfy(data.error || data.msg || '操作失败','error');
				t.removeClass('protect').text(original_text);
				return;
			}

			toastfy(data.msg || '操作成功','success');
			setTimeout(function(){
				window.location.reload();
			}, 500);
		},
		error: function(xhr){
			var msg = xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.msg) ? (xhr.responseJSON.error || xhr.responseJSON.msg) : '操作失败，请重试';
			toastfy(msg,'error');
			t.removeClass('protect').text(original_text);
		}
	});
}

$(document).on('click', '.order-btn.cancel', function(e){
	e.preventDefault();
	e.stopPropagation();
	ppo_order_action_request($(this), 'user-order-cancel', '确定取消该订单吗？', '取消中...');
});

$(document).on('click', '.order-btn.delete', function(e){
	e.preventDefault();
	e.stopPropagation();
	ppo_order_action_request($(this), 'user-order-delete', '确定删除该订单吗？删除后前台订单列表将不再显示。', '删除中...');
});

$(document).on('click', '.order-btn.pay', function(e){
	e.preventDefault();
	e.stopPropagation();
	var t = $(this),
		order_id = t.data('order');

	if(!order_id || t.hasClass('protect')){
		return;
	}

	var pay_type = t.data('pay-type');
	if(pay_type == 'balance' || pay_type == 'credit'){
		ppo_show_asset_pay_confirm({
			pay_type: pay_type,
			label: ppo_asset_pay_label(pay_type),
			subject: t.data('subject') || '当前订单',
			amount: ppo_asset_pay_amount_text(t, pay_type, t.data('amount'))
		}, function(){
			ppo_repay_order(t, order_id);
		});
		return;
	}

	ppo_repay_order(t, order_id);
});

function ppo_repay_order(t, order_id){
	$.ajax({
		url: '/wp-json/ppo/v1/user-order-repay?_wpnonce=' + Theme.rest_nonce,
		type: 'POST',
		dataType: 'json',
		data: {
			order_id: order_id
		},
		beforeSend: function(){
			t.addClass('protect').text('拉起支付...');
			toastfy('正在拉起支付..','info');
		},
		success: function(data){
			if(data.error){
				toastfy(data.error,'error');
				t.removeClass('protect').text('继续支付');
				return;
			}

			Cookies.set('order_id', data.order_id, { expires: 7 });

			if(data.jump == 'jump'){
				ppo_open_jump_pay(data.url);
				return;
			}

			if(data.jump == 'scan'){
				pay_qrcode_modal(data, data.icon || '¥');
				pay_interval();
				return;
			}

			if(data.jump == 'balance' || data.jump == 'credit'){
				pay_interval();
				return;
			}

			t.removeClass('protect').text('继续支付');
		},
		error: function(xhr){
			var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : '拉起支付失败，请重试';
			toastfy(msg,'error');
			t.removeClass('protect').text('继续支付');
		}
	});
}

// 卡密充值
$('body').on('click', '.card-redeem-cash-btn, .card-redeem-credit-btn', function() {
	$('#wallet-card-code').val('');
	$('.redeem-result').empty();
	ppo_show_modal($('#card-redeem-modal'));
});

$('body').on('click', '#btn-wallet-redeem', function() {
	var code = $('#wallet-card-code').val().trim().toUpperCase();
	if (!code) {
		$('.redeem-result').html('<span style="color:#e74c3c;"><i class="ri-close-circle-line"></i> 请输入卡密</span>');
		return;
	}

	var $btn = $(this);
	$btn.prop('disabled', true).text('兑换中...');
	$('.redeem-result').empty();

	$.ajax({
		url: Theme.ajaxurl,
		type: 'POST',
		data: {
			action: 'ppo_redeem_card',
			nonce: Theme.card_nonce,
			code: code
		},
		success: function(response) {
			if (response.success) {
				$('.redeem-result').html('<span style="color:#27ae60;"><i class="ri-checkbox-circle-line"></i> ' + response.data.msg + '</span>');
				$('#wallet-card-code').val('');
				setTimeout(function() {
					location.reload();
				}, 1500);
			} else {
				$('.redeem-result').html('<span style="color:#e74c3c;"><i class="ri-close-circle-line"></i> ' + response.data.msg + '</span>');
			}
		},
		error: function() {
			$('.redeem-result').html('<span style="color:#e74c3c;"><i class="ri-close-circle-line"></i> 请求失败，请重试</span>');
		},
		complete: function() {
			$btn.prop('disabled', false).text('兑换');
		}
	});
});

$('body').on('keydown', '#wallet-card-code', function(e) {
	if (e.keyCode === 13) {
		$('#btn-wallet-redeem').trigger('click');
	}
});

// ==== 顶部搜索 ====

function ppo_open_header_search(){
	var overlay = $('.pix-search-overlay');
	if(!overlay.length){
		return;
	}

	overlay.addClass('is-active').attr('aria-hidden', 'false');
	$('body').addClass('pix-search-open');

	setTimeout(function(){
		overlay.find('.pix-search-input').trigger('focus');
	}, 120);
}

function ppo_close_header_search(){
	var overlay = $('.pix-search-overlay');
	overlay.removeClass('is-active').attr('aria-hidden', 'true');
	$('body').removeClass('pix-search-open');
}

$(document).on('click', '.pix-search-trigger', function(e){
	e.preventDefault();
	ppo_open_header_search();
});

$(document).on('click', '.pix-search-close, .pix-search-backdrop', function(e){
	e.preventDefault();
	ppo_close_header_search();
});

$(document).on('keydown', function(e){
	if(e.key === 'Escape'){
		ppo_close_header_search();
	}

	if((e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 'k'){
		e.preventDefault();
		ppo_open_header_search();
	}
});

$(document).on('submit', '.pix-search-form, .pix-search-page-form', function(e){
	var input = $(this).find('input[name="s"]');
	if(!$.trim(input.val())){
		e.preventDefault();
		input.trigger('focus');
		if(typeof toastfy === 'function'){
			toastfy('请输入搜索关键词','info');
		}
	}
});

$(document).on('keydown', '.pix-search-form input[name="s"], .pix-search-page-form input[name="s"]', function(e){
	if(e.key !== 'Enter' || e.originalEvent.isComposing){
		return;
	}

	e.preventDefault();
	$(this).closest('form').trigger('submit');
});
