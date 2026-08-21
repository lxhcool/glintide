window.captcha = {};

function buildCaptchaPayload() {
    var payload = {};
    if (!window.captcha || typeof window.captcha !== 'object') {
        return payload;
    }

    if (window.captcha.pixcap && typeof window.captcha.pixcap === 'object') {
        return window.captcha.pixcap;
    }

    Object.keys(window.captcha).forEach(function(key) {
        if (key === '_this' || key === 'pass' || key === 'pixcapInstance' || key === 'error') {
            return;
        }
        if (key === 'ppo' && window.captcha.ppo && typeof window.captcha.ppo === 'object') {
            return;
        }
        payload[key] = window.captcha[key];
    });

    return payload;
}

function getPixcapConfig(scope) {
    if (scope === 'content') {
        return window.PixcapContentConfig || {};
    }
    if (window.PixcapConfig && typeof window.PixcapConfig === 'object') {
        return window.PixcapConfig;
    }
    return {};
}

function pixcapResolveContentHost(context, triggerEl) {
    var trigger = triggerEl && triggerEl.nodeType === 1 ? triggerEl : null;
    var host = null;

    if (trigger && typeof trigger.closest === 'function') {
        host = trigger.closest('.com_push, .push-box, .pix-moment-composer-submit-group, .comment-respond, form');
    }

    if (!host) {
        if (context === 'moment') {
            host = document.querySelector('.pix-moment-composer-submit-group');
        } else {
            host = document.querySelector('.com_push');
        }
    }

    if (!host && trigger && trigger.form) {
        host = trigger.form;
    }

    if (!host) {
        host = document.body;
    }

    if (host !== document.body) {
        if (window.getComputedStyle(host).position === 'static') {
            host.style.position = 'relative';
        }
        host.classList.add('pixcap-content-bubble-host');
    }

    return host;
}

function pixcapClearContentBubble(host) {
    var nodes = [];

    if (host && host.querySelector) {
        nodes = nodes.concat(Array.prototype.slice.call(host.querySelectorAll('.pixcap-content-floating')));
    }

    nodes = nodes.concat(Array.prototype.slice.call(document.querySelectorAll('.pixcap-content-bubble-host .pixcap-content-floating')));

    nodes.forEach(function(floating) {
        if (!floating || !floating.parentNode || floating.dataset.pixcapContentClosing === '1') {
            return;
        }

        var widget = floating.querySelector('.pixcap-widget');
        floating.dataset.pixcapContentClosing = '1';

        if (widget) {
            widget.classList.remove('pixcap-floating-show');
            widget.classList.add('pixcap-floating-hide');
        }

        window.setTimeout(function() {
            if (floating && floating.parentNode) {
                floating.parentNode.removeChild(floating);
            }
        }, 320);
    });
}

function pixcapTagContentBubble(host) {
    if (!host || !host.querySelector) {
        return;
    }

    var floating = host.querySelector('.pixcap-floating-container:not(.pixcap-content-floating)');
    if (floating) {
        floating.classList.add('pixcap-content-floating');
    }
}

window.pixcapClearContentVerification = function(context, triggerEl, delay) {
    window.setTimeout(function() {
        pixcapClearContentBubble(pixcapResolveContentHost(context, triggerEl));
    }, typeof delay === 'number' ? delay : 0);
};

window.pixcapVerifyContent = function(context, triggerEl) {
    var config = getPixcapConfig('content');
    var contentType = (window.Theme && Theme.content_protect_type) || 'smart';
    var host = null;
    var instance = null;

    if (contentType !== 'pixcap') {
        return Promise.resolve(null);
    }

    if (!window.Pixcap || !config.challengeUrl) {
        return Promise.reject(new Error('Pixcap 资源未加载，请联系管理员'));
    }

    host = pixcapResolveContentHost(context, triggerEl);
    pixcapClearContentBubble(host);

    return new Promise(function(resolve, reject) {
        instance = new window.Pixcap({
            mode: 'bubble',
            container: host,
            button: null,
            apiEndpoint: config.challengeUrl,
            verifyEndpoint: config.verifyUrl,
            theme: config.theme || 'business',
            size: 'compact',
            logoUrl: config.logoUrl || '',
            language: (window.Theme && Theme.locale) || 'zh-CN',
            initialState: 'verifying',
            minVerifyingMs: 1400,
            showExpireCountdown: false,
            onSuccess: function(payload) {
                window.pixcapClearContentVerification(context, triggerEl, 2600);
                resolve(payload);
            },
            onError: function(error) {
                window.pixcapClearContentVerification(context, triggerEl, 1800);
                reject(error || new Error('验证失败，请重试'));
            }
        });

        pixcapTagContentBubble(host);
        instance.verify().catch(function(error) {
            window.pixcapClearContentVerification(context, triggerEl, 1800);
            reject(error || new Error('验证失败，请重试'));
        });
    });
};

function pixcapResolveAuthHost(triggerEl, mode) {
    var trigger = triggerEl && triggerEl.nodeType === 1 ? triggerEl : null;
    var host = null;

    if (!trigger || mode === 'hidden') {
        return null;
    }

    if (mode === 'bubble') {
        host = trigger.closest('.push-login, .pe_code_box, .log-form-item, .reg-form-item, .reset-pwd-btn, .pix-dashboard-modal-footer, .pix-dashboard-modal-dialog, .ajax-auth, form');
        if (host) {
            host.querySelectorAll('.pixcap-login-bubble, .pixcap-login-inline-mount').forEach(function(item) {
                item.remove();
            });
            if (window.getComputedStyle(host).position === 'static') {
                host.style.position = 'relative';
            }
            host.style.overflow = 'visible';
        }
        return host;
    }

    host = document.getElementById('pixcap-login-mount');
    if (!host) {
        host = document.createElement('div');
        host.id = 'pixcap-login-mount';
    }
    host.className = 'pixcap-login-inline-mount';
    host.innerHTML = '';

    var row = trigger.closest('.push-login, .pe_code_box, .log-form-item, .reg-form-item, .reset-pwd-btn, .pix-dashboard-modal-footer, .ajax-auth, form');
    if (row) {
        row.parentNode.insertBefore(host, row);
    } else {
        document.body.appendChild(host);
    }

    return host;
}

window.pixcapVerifyStandalone = function(triggerEl) {
    var captchaType = (window.Theme && Theme.captcha_type) || '';
    var config = getPixcapConfig();
    var mode = (config && config.mode) || (window.Theme && Theme.pixcap_mode) || 'bubble';
    var host = null;
    var instance = null;

    if (captchaType !== 'pixcap') {
        return Promise.resolve(null);
    }

    if (!window.Pixcap || !config.challengeUrl) {
        return Promise.reject(new Error('Pixcap 资源未加载，请联系管理员'));
    }

    document.querySelectorAll('.pixcap-login-bubble, .pixcap-login-inline-mount').forEach(function(item) {
        item.remove();
    });

    host = pixcapResolveAuthHost(triggerEl, mode);

    return new Promise(function(resolve, reject) {
        instance = new window.Pixcap({
            mode: mode,
            container: mode === 'hidden' ? null : host,
            button: null,
            apiEndpoint: config.challengeUrl,
            verifyEndpoint: config.verifyUrl,
            theme: config.theme || (window.Theme && Theme.pixcap_theme) || 'business',
            size: mode === 'hidden' ? 'default' : 'compact',
            logoUrl: config.logoUrl || '',
            language: (window.Theme && Theme.locale) || 'zh-CN',
            initialState: mode === 'hidden' ? 'unverified' : 'verifying',
            minVerifyingMs: 1400,
            showExpireCountdown: false,
            onSuccess: function(payload) {
                window.pixcapLoginInstance = instance;
                pixcapClearLoginBubble(1900);
                resolve(payload);
            },
            onError: function(error) {
                pixcapClearLoginBubble(1200);
                reject(error || new Error('验证失败，请重试'));
            }
        });

        window.pixcapLoginInstance = instance;
        if (host && mode === 'bubble') {
            var floating = host.querySelector('.pixcap-floating-container');
            if (floating) {
                floating.classList.add('pixcap-login-bubble');
                floating.style.pointerEvents = 'none';
                var widget = floating.querySelector('.pixcap-widget');
                if (widget) {
                    widget.style.pointerEvents = 'none';
                }
            }
        } else if (host && mode === 'inline') {
            host.classList.add('is-visible');
        }

        instance.verify().catch(function(error) {
            pixcapClearLoginBubble(1200);
            reject(error || new Error('验证失败，请重试'));
        });
    });
};

// 回车键触发表单提交
$(document).on('keydown', 'input', function(e) {
    if (e.which === 13 || e.keyCode === 13) {
        e.preventDefault();

        var $input = $(this);
        var $form = $input.closest('#login, #register, #resetpwd-form');

        // 账号密码登录
        if ($form.is('#login') && $input.closest('#nor-login').length) {
            var $uname = $form.find('#username');
            var $pwd = $form.find('#login-password');
            if ($uname.val() === '' || $pwd.val() === '') {
                toastfy('请填写用户名或密码', 'error');
                return;
            }
            $('.captcha-push').trigger('click');
        }
        // 免密登录
        else if ($form.is('#login') && $input.closest('#nopass-login').length) {
            $('.nopass-push').trigger('click');
        }
        // 注册表单
        else if ($form.is('#register')) {
            var $uname = $form.find('#user_name');
            var $nick = $form.find('#nick_name');
            var $rpwd = $form.find('#reg-password');
            var $rpwd2 = $form.find('#reg-password-confirm');
            if ($uname.length && $uname.val() === '') {
                toastfy('请填写账户信息', 'error');
                return;
            }
            if ($nick.length && $nick.val() === '') {
                toastfy('请填写昵称', 'error');
                return;
            }
            if ($rpwd.val() === '') {
                toastfy('请填写密码', 'error');
                return;
            }
            if ($rpwd2.val() !== $rpwd.val()) {
                toastfy('两次密码输入不一致', 'error');
                return;
            }
            $('.reg-push, .register-push').first().trigger('click');
        }
        // 重置密码表单
        else if ($input.closest('#resetpwd-form').length || $input.closest('.resetpwd-step-1-wrap').length) {
            $('.next-pwd').trigger('click');
        }
    }
});

// 密码显隐切换
$(document).on('click', '.pwd-toggle-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();

    var $btn = $(this);
    var $input = $($btn.data('target'));
    var $icon = $btn.find('i');
    var isPassword = $input.attr('type') === 'password';

    $input.attr('type', isPassword ? 'text' : 'password');

    if (isPassword) {
        $icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
        $btn.addClass('visible');
    } else {
        $icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
        $btn.removeClass('visible');
    }

    setTimeout(function() {
        $input.focus();
    }, 10);
});

// 手机号/邮箱输入检测 - 达到有效长度后显示验证码输入框
function toggleCodeBox($input) {
    var val = $input.val();
    var $form = $input.closest('.ajax-auth');
    var $codeBox = $form.find('.pe_code_box, .pwd-send-code').first();
    if (val.length >= 6 && (val.indexOf('@') > -1 || /^\d{6,}$/.test(val))) {
        $codeBox.addClass('visible');
    } else {
        $codeBox.removeClass('visible').hide().find('#smscode').val('');
    }
}

$('body').on('input', '#email_phone', function(){
    toggleCodeBox($(this));
});

$('body').on('input', '#register #user_name', function(){
    toggleCodeBox($(this));
});

// 快捷登录切换
$('body').on('click','#fast-login , #normal-login',function(){
    var text = '登录';
    if($(this).attr('id') == 'fast-login'){
        var text = '免密登录';
    }
    $('.login-form-tab').addClass('active');
    $(this).parents('.login-form-tab').removeClass('active');
    $('form#login .log_title').text(text);
    $('.login-form-tab .pe_code_box').removeClass('visible').hide().find('#smscode').val('');
    return false;
});    

//切换注册按钮
$('body').on('click', '#pop_login, #pop_signup', function() {
	formToFadeOut = $('form#register');
	formtoFadeIn = $('form#login');
	
	if ($(this).attr('id') == 'pop_signup') {
	    formToFadeOut = $('form#login');
	    formtoFadeIn = $('form#register');
	}
	formToFadeOut.fadeOut(50, function () {
	    formtoFadeIn.fadeIn();
	})
	$('#user_name, #email_phone').val('');
	$('.pe_code_box').removeClass('visible').hide().find('#smscode').val('');
	return false;
});	

$('body').on('click', '.reg_protocol a', function(e) {
    e.stopPropagation();
});

//ajax登录
function ajax_login_action(){
    var t = $('.captcha-push');

	action = 'ajaxlogin';
	security = $('form#login #security').val();
	username = $('form#login #username').val();
	password = $('form#login #login-password').val();

    if(username == '' || password == ''){
        toastfy('请填写用户名或密码','error');
        window.captcha.pass = false;
        return false;
    }

    if (window.captcha) {
        logincaptcha = buildCaptchaPayload();
        window.captcha = {};
    }

	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'username': username,
	        'password': password,
	        'security': security,
            'logincaptcha': logincaptcha,
	    },
	    success: function (data) {
			if (data.code == 0) {
                t.addClass('protect');
                toastfy(data.msg || '登录成功，正在跳转...','success');
			    setTimeout(function(){
			        var redirectUrl = Theme.redirecturl + (Theme.redirecturl.indexOf('?') > -1 ? '&' : '?') + '_t=' + Date.now();
			        document.location.href = redirectUrl;
			    }, 1000);
			} else {
                toastfy(data.msg,'error');
            }

	    }
        ,
        complete: function() {
            window.captcha.pass = false;
            pixcapClearLoginBubble(900);
        }
	});
}


//验证按钮 调用验证或直接登录
$('body').on('click','.captcha-push , .sms-code-btn , .register-push ',function(){ 
    var t = $(this);
    if(t.hasClass('protect')){
        return false;
    }

    window.captcha._this = t;
    var type = t.attr('type');
    var action_f = t.attr('action');
   if(!window.captcha.pass && type != 'normal'){
        window.captcha_action = action_f;
        var ret = captcha_type(type);
        if (ret === false) return false;
   }

   if(action_f == 'login'){
        ajax_login_action();
   } else if(action_f == 'normal_reg'){
        ajax_reg_action();
   } else {
        ajax_sms_send(); 
   }
     
});

function captcha_type(type){
    var needWait = false;
    switch(type){
        case 'ppoc':
            ppo_captcha();
          break;
        case 'pixcap':
            pixcap_captcha();
            break;
        case 'geetest':
            geetest_captcha();       
            break;
        case 'code':
            get_captcha_code();       
            break;
      }
      if (needWait) {
            return false;
        }
        if (type == 'ppoc' || type == 'pixcap' || type == 'geetest' || type == 'code') {
            return false;
        }
  }

function proceed_action(){
    var action = window.captcha_action;
    window.captcha_action = '';
    if (action == 'login') {
        ajax_login_action();
    } else if (action == 'normal_reg') {
        ajax_reg_action();
    } else if (action == 'resetpwd_send') {
        resetpwd_send_code();
    } else {
        ajax_sms_send();
    }
}

function pixcap_captcha(){
    var t = window.captcha._this;
    var mode = (window.PixcapConfig && PixcapConfig.mode) || (window.Theme && Theme.pixcap_mode) || 'bubble';
    var theme = (window.PixcapConfig && PixcapConfig.theme) || (window.Theme && Theme.pixcap_theme) || 'business';
    var challengeUrl = (window.PixcapConfig && PixcapConfig.challengeUrl) || (window.Theme ? Theme.ajaxurl + '?action=pixcap_challenge' : '');
    var verifyUrl = (window.PixcapConfig && PixcapConfig.verifyUrl) || (window.Theme ? Theme.ajaxurl + '?action=pixcap_verify' : '');
    var logoUrl = (window.PixcapConfig && PixcapConfig.logoUrl) || '';
    var container = null;

    if (!window.Pixcap || !challengeUrl) {
        toastfy('Pixcap 资源未加载，请联系管理员', 'error');
        return;
    }

    document.querySelectorAll('.pixcap-login-bubble, .pixcap-login-inline-mount').forEach(function(item) {
        item.remove();
    });

    if (mode === 'bubble') {
        container = t.closest('.push-login, .pe_code_box, .log-form-item, .reg-form-item, .ajax-auth').get(0);
        if (container) {
            container.querySelectorAll('.pixcap-floating-container').forEach(function(item) {
                item.remove();
            });
            if (window.getComputedStyle(container).position === 'static') {
                container.style.position = 'relative';
            }
            container.style.overflow = 'visible';
        }
    } else if (mode === 'inline') {
        var row = t.closest('.push-login, .pe_code_box, .log-form-item, .reg-form-item');
        var mount = document.getElementById('pixcap-login-mount');
        if (!mount) {
            mount = document.createElement('div');
            mount.id = 'pixcap-login-mount';
        }
        mount.className = 'pixcap-login-inline-mount';
        mount.innerHTML = '';
        if (row.length) {
            if (row.hasClass('push-login')) {
                row.before(mount);
            } else {
                row.after(mount);
            }
        } else {
            document.body.appendChild(mount);
        }
        container = mount;
    }

    if (window.captcha.pixcapInstance && typeof window.captcha.pixcapInstance.reset === 'function') {
        try { window.captcha.pixcapInstance.reset(); } catch (e) {}
    }

    window.captcha.pixcapInstance = new window.Pixcap({
        mode: mode,
        container: mode === 'hidden' ? null : container,
        button: null,
        apiEndpoint: challengeUrl,
        verifyEndpoint: verifyUrl,
        theme: theme,
        size: mode === 'hidden' ? 'default' : 'compact',
        logoUrl: logoUrl,
        language: (window.Theme && Theme.locale) || 'zh-CN',
        initialState: mode === 'hidden' ? 'unverified' : 'verifying',
        minVerifyingMs: 1400,
        showExpireCountdown: false,
        onSuccess: function(payload) {
            window.captcha.pass = true;
            window.captcha.pixcap = payload;
            proceed_action();
        },
        onError: function(error) {
            window.captcha.pass = false;
            pixcapClearLoginBubble(1200);
            toastfy((error && error.message) || '验证失败，请重试', 'error');
        }
    });

    window.pixcapLoginInstance = window.captcha.pixcapInstance;
    if (container && mode === 'bubble') {
        var floating = container.querySelector('.pixcap-floating-container');
        if (floating) {
            floating.classList.add('pixcap-login-bubble');
            floating.style.pointerEvents = 'none';
            var widget = floating.querySelector('.pixcap-widget');
            if (widget) {
                widget.style.pointerEvents = 'none';
            }
        }
    } else if (container && mode === 'inline') {
        container.classList.add('is-visible');
    }

    window.captcha.pixcapInstance.verify().then(function(success){
        if (!success) {
            return;
        }
    });
}

function pixcapClearLoginBubble(delay) {
    window.setTimeout(function() {
        var instance = window.pixcapLoginInstance || (window.captcha && window.captcha.pixcapInstance);
        if (instance && typeof instance._clearExpireCountdown === 'function') {
            instance._clearExpireCountdown();
        }

        document.querySelectorAll('.pixcap-login-bubble').forEach(function(floating) {
            if (floating.dataset.pixcapLoginClosing === '1') {
                return;
            }

            floating.dataset.pixcapLoginClosing = '1';
            var widget = floating.querySelector('.pixcap-widget');
            if (widget) {
                widget.classList.remove('pixcap-floating-show');
                widget.classList.add('pixcap-floating-hide');
            }

            window.setTimeout(function() {
                if (floating && floating.parentNode) {
                    floating.parentNode.removeChild(floating);
                }
            }, 320);
        });

        document.querySelectorAll('.pixcap-login-inline-mount').forEach(function(mount) {
            if (mount.dataset.pixcapLoginClosing === '1') {
                return;
            }

            mount.dataset.pixcapLoginClosing = '1';
            mount.classList.add('is-hiding');
            window.setTimeout(function() {
                if (mount && mount.parentNode) {
                    mount.parentNode.removeChild(mount);
                }
            }, 260);
        });
    }, typeof delay === 'number' ? delay : 0);
}

//ajax免密登录
$('body').on('click','.nopass-push',function(){ 
    var t = $(this);
    action = 'ajax_nopass_login';
	security = $('form#login #security').val();
	email_phone = $('form#login #email_phone').val();
    smscode = $('form#login #smscode').val();

    if(smscode == ''){
        toastfy('请填写验证码','error');
        return;
    }

    $.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'email_phone': email_phone,
	        'smscode': smscode,
	        'security': security,
	    },
	    success: function (data) {
			if(data.code == 0){
                t.addClass('protect');
                toastfy(data.msg || '登录成功，正在跳转...','success');
			    setTimeout(function(){
			        var redirectUrl = Theme.redirecturl + (Theme.redirecturl.indexOf('?') > -1 ? '&' : '?') + '_t=' + Date.now();
			        document.location.href = redirectUrl;
			    }, 1000);
            } else {
                toastfy(data.msg,'error');
            }

	    }
	});
}); 

//ajax注册
function ajax_reg_action(){
    var t = window.captcha._this;
    var form = t.parents('.ajax-auth');
    action = 'ajaxregister';
    user_name = form.find('#user_name').val();
    nick_name = form.find('#nick_name').val();
    smscode = form.find('#smscode').val();
    password = form.find('#reg-password').val();
    password_confirm = form.find('#reg-password-confirm').val();
    security = form.find('#signonsecurity').val();
    if(form.find('input.protocol-check').length > 0){
        if(!form.find('.protocol-check').is(':checked')) {
            toastfy('未勾选用户协议和隐私政策','error');
            window.captcha.pass = false;
            return;
        }
    }

    if (password !== password_confirm) {
        toastfy('两次密码输入不一致','error');
        window.captcha.pass = false;
        return;
    }

    if (window.captcha) {
        logincaptcha = buildCaptchaPayload();
        window.captcha = {};
    }
    
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: Theme.ajaxurl,
	    data: {
	        'action': action,
	        'user_name': user_name,
	        'nick_name': nick_name,
	        'smscode': smscode,
	        'password': password,
	        'password_confirm': password_confirm,
	        'security': security,
            'logincaptcha': logincaptcha,
	    },
	    success: function (data) {
			if(data.code == 0){
                t.addClass('protect');
                toastfy(data.msg || '注册成功，正在跳转...','success');
			    setTimeout(function(){
			        var redirectUrl = Theme.redirecturl + (Theme.redirecturl.indexOf('?') > -1 ? '&' : '?') + '_t=' + Date.now();
			        document.location.href = redirectUrl;
			    }, 1000);
			} else {
                toastfy(data.msg,'error');
            }

	    }
        ,
        complete: function() {
            window.captcha.pass = false;
            pixcapClearLoginBubble(900);
        }
	});
} 

//发送短信
function ajax_sms_send(){
    var t = window.captcha._this;
    var form = t.parents('.ajax-auth');
    var mode = t.attr('action');
    var $accountField = form.find('#user_name').length ? form.find('#user_name') : form.find('#email_phone');
    var accountValue = $accountField.val();
    security = form.find('#signonsecurity').val();

    if(accountValue == ''){
        toastfy('请填写手机号/邮箱','error');
        window.captcha.pass = false;
        return;
    }

    if (window.captcha) {
        logincaptcha = buildCaptchaPayload();
        window.captcha = {};
    }

    toastfy('正在发送验证码，请稍候...','info');

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': 'send_phone_code',
            'email_phone': accountValue,
            'mode': mode,
            'form_name': $accountField.attr('name') || '',
            'uid': Theme.uid,
            'security': security,
            'nonce': Theme.user_nonce,
            logincaptcha: logincaptcha,
        },
        success: function (data) {
            if(data.code == 0){
                toastfy(data.msg,'success');
                code_countdown(t);
            } else {
                toastfy(data.msg,'error');
            }
        },
        complete: function() {
            window.captcha.pass = false;
            pixcapClearLoginBubble(900);
        }
    });
}

//倒计时
function code_countdown(btn){
    var count = 60;
    var btnId = btn.attr('data-countdown-id') || btn.attr('id') || 'btn_' + Math.random().toString(36).substr(2, 9);
    var storageKey = 'sms_countdown_' + btnId;

    if (!btn.attr('data-countdown-id')) {
        btn.attr('data-countdown-id', btnId);
    }

    if (localStorage.getItem(storageKey)) {
        var remaining = Math.ceil((parseInt(localStorage.getItem(storageKey)) - Date.now()) / 1000);
        if (remaining > 0) {
            count = remaining;
        } else {
            localStorage.removeItem(storageKey);
        }
    }

    var endTime = Date.now() + (count * 1000);
    localStorage.setItem(storageKey, endTime);

    btn.text(count + '秒后重新发送');
    btn.addClass('disabled');

    var timer = setInterval(function(){
        count--;
        if(count <= 0){
            clearInterval(timer);
            localStorage.removeItem(storageKey);
            btn.text('获取验证码');
            btn.removeClass('disabled');
        } else {
            btn.text(count + '秒后重新发送');
        }
    },1000);
}

// 页面加载时恢复倒计时状态
$(document).ready(function(){
    setTimeout(function(){
        $('a[data-action="send-code"], .next-pwd, .reg-push, .nopass-push, [class*="send-code"]').each(function(){
            var btn = $(this);
            var btnId = btn.attr('data-countdown-id') || btn.attr('id') || btn.attr('class');

            if (!btnId) return;

            var storageKey = 'sms_countdown_' + btnId;

            if (localStorage.getItem(storageKey)) {
                var remaining = Math.ceil((parseInt(localStorage.getItem(storageKey)) - Date.now()) / 1000);
                if (remaining > 0) {
                    code_countdown(btn);
                } else {
                    localStorage.removeItem(storageKey);
                }
            }
        });
    }, 100);
});

function reset_pwd_success(){
    $('.modal-reset-pwd').removeClass('show');
    toastfy('密码修改成功','success');
}

function pwd_input_html(){
    var html = '<div class="pwd-input-box">'+
                    '<div class="pwd-input-item">'+
                        '<label class="log-form-item">'+
                            '<i class="ri-lock-line logonicon"></i>'+
                            '<input type="password" name="pwd" id="pwd" placeholder="6-16位，数字/字母组合">'+
                        '</label>'+
                    '</div>'+
                    '<div class="pwd-input-item">'+
                        '<label class="log-form-item">'+
                            '<i class="ri-lock-line logonicon"></i>'+
                            '<input type="password" name="confirm_pwd" id="confirm_pwd" placeholder="再次输入新密码">'+
                        '</label>'+
                    '</div>'+
                '</div>';
    return html;
}

function overlay_loading(close){
    if(close){
        $('.overlay-loading').hide();
        return;
    }
    $('.overlay-loading').show();
}

function geetest_captcha(){
    var t = window.captcha._this;
    var captchaId = t.attr('geetest-id') || Theme.geetest_id;
    initGeetest4({
        captchaId: captchaId,
        product: 'bind',
    }, function(captcha){
        captcha.onReady(function(){
            captcha.showCaptcha();
        }).onSuccess(function(){
            var result = captcha.getValidate();
            window.captcha.pass = true;
            window.captcha.lot_number = result.lot_number;
            window.captcha.captcha_output = result.captcha_output;
            window.captcha.pass_token = result.pass_token;
            window.captcha.gen_time = result.gen_time;
            proceed_action();
        }).onError(function(){
            toastfy('验证失败，请重试','error');
        });
    });
}

function ppo_captcha(){
    var t = window.captcha._this;
    ppoCaptcha({ 
        container: t,
        theme: {
            color: "#0052cc",
        },
        success: function(captchaObj) {
            console.log("captchaObj",captchaObj)
            window.captcha.pass = true;
            window.captcha.ppo = captchaObj;
            proceed_action();
        },
        fail: function() {
            toastfy('验证失败，请重试','error');
        }
    });
}

//图形验证码
function get_captcha_code(){
    var t = window.captcha._this;

    if ($('#captcha-code-modal').length) {
        $('#captcha-code-modal').remove();
    }

    var modal = $(
        '<div id="captcha-code-modal" class="captcha-code-overlay">' +
            '<div class="captcha-code-popup">' +
                '<div class="captcha-code-img-wrap">' +
                    '<div class="captcha-code-loading"><i class="ri-loader-4-line"></i></div>' +
                    '<img class="captcha-code-img" src="" alt="验证码" style="display:none;">' +
                '</div>' +
                '<div class="captcha-code-input-wrap">' +
                    '<input type="text" id="captcha-code-input" name="captcha-code-input" class="captcha-code-input" maxlength="6" placeholder="请输入图片中的验证码" autocomplete="off">' +
                    '<a class="captcha-code-refresh" title="换一张"><i class="ri-refresh-line"></i></a>' +
                '</div>' +
                '<div class="captcha-code-actions">' +
                    '<a class="captcha-code-btn captcha-code-confirm">确定</a>' +
                    '<a class="captcha-code-btn captcha-code-cancel">取消</a>' +
                '</div>' +
            '</div>' +
        '</div>'
    );

    var container = t.closest('.pix-auth-modal-panel, .pix-modal-panel, .ajax-auth');
    if (container.length) {
        modal.appendTo(container);
        modal.css({ position: 'absolute' });
    } else {
        modal.appendTo('body');
    }

    var input = modal.find('.captcha-code-input');
    setTimeout(function(){ input.focus(); }, 100);

    function loadCodeImg() {
        var $loading = modal.find('.captcha-code-loading');
        var $img = modal.find('.captcha-code-img');

        $loading.show();
        $img.hide().removeAttr('src');

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: Theme.ajaxurl,
            data: { action: 'captcha_code_data' },
            success: function(data) {
                if (data.code == 200 && data.res) {
                    $img[0].onload = function() {
                        $loading.hide();
                        $img.fadeIn(150);
                    };
                    $img[0].onerror = function() {
                        toastfy('验证码图片加载失败', 'error');
                        $loading.hide();
                    };
                    $img.attr('src', data.res);
                } else {
                    toastfy(data.msg || '获取验证码失败，请刷新重试', 'error');
                    $loading.hide();
                }
            },
            error: function() {
                toastfy('获取验证码失败，请检查网络', 'error');
                $loading.hide();
            }
        });
    }

    loadCodeImg();

    modal.find('.captcha-code-refresh').on('click', function(e) {
        e.stopPropagation();
        loadCodeImg();
    });

    modal.find('.captcha-code-cancel').on('click', function(e) {
        e.stopPropagation();
        modal.find('.captcha-code-popup').css({ animation: 'none', transition: 'all .25s ease-in', transform: 'scale(0.85)', opacity: 0 });
        modal.css({ transition: 'background .2s', background: 'rgba(0,0,0,0)' });
        setTimeout(function(){ modal.remove(); }, 250);
    });

    modal.find('.captcha-code-confirm').on('click', function(e) {
        e.stopPropagation();
        var code = modal.find('.captcha-code-input').val();
        if (!code) {
            toastfy('请输入验证码', 'error');
            return;
        }
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: Theme.ajaxurl,
            data: {
                action: 'captcha_code_check',
                captcha_code: code
            },
            success: function(data) {
                if (data.code == 200) {
                    modal.find('.captcha-code-popup').addClass('success-state');
                    modal.find('.captcha-code-img-wrap').html('<div class="captcha-success-icon"><i class="ri-check-line"></i><span>验证通过</span></div>');
                    modal.find('.captcha-code-input-wrap, .captcha-code-actions').fadeOut(100);
                    setTimeout(function(){
                        modal.find('.captcha-code-popup').css({ animation: 'none', transition: 'all .25s ease-in', transform: 'scale(0.85)', opacity: 0 });
                        modal.css({ transition: 'background .2s', background: 'rgba(0,0,0,0)' });
                        setTimeout(function(){
                            modal.remove();
                            window.captcha.pass = true;
                            proceed_action();
                        }, 250);
                    }, 800);
                } else {
                    toastfy(data.msg || '验证码错误', 'error');
                    loadCodeImg();
                    modal.find('.captcha-code-input').val('').focus();
                }
            },
            error: function() {
                toastfy('验证失败，请重试', 'error');
            }
        });
    });

    modal.on('click', function(e) {
        if ($(e.target).is('.captcha-code-overlay')) {
            modal.find('.captcha-code-popup').css({ animation: 'none', transition: 'all .25s ease-in', transform: 'scale(0.85)', opacity: 0 });
            modal.css({ transition: 'background .2s', background: 'rgba(0,0,0,0)' });
            setTimeout(function(){ modal.remove(); }, 250);
        }
    });

    input.on('keydown', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            modal.find('.captcha-code-confirm').trigger('click');
        }
    });
}

function btn_loader(object){
    object.append('<div class="btn-loader"></div>');
}
function btn_remove_loader(object){
    object.find('.btn-loader').remove();
}

// 重置密码 - 下一步按钮
$('body').on('click', '.next-pwd', function(){
    var form = $('#resetpwd-form');
    var email_phone = form.find('#email_phone').val();
    var smscode = form.find('#smscode').val();

    if(email_phone == ''){
        toastfy('请输入手机号或邮箱','error');
        return false;
    }

    if(email_phone.length < 6 || (email_phone.indexOf('@') == -1 && !/^\d{6,}$/.test(email_phone))){
        toastfy('请输入有效的手机号或邮箱','error');
        return false;
    }

    if(smscode == ''){
        toastfy('请先获取验证码','error');
        return false;
    }

    verify_resetpwd_code(email_phone, smscode);
});

// 验证重置密码验证码
function verify_resetpwd_code(email_phone, smscode) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': 'reset_pwd',
            'email_phone': email_phone,
            'smscode': smscode,
            'nonce': Theme.user_nonce,
        },
        beforeSend: function () {
            toastfy('验证中...', 'info');
        },
        success: function(data) {
            if (data.code == 1) {
                show_resetpwd_step2(email_phone, data.token);
            } else {
                toastfy(data.msg, 'error');
            }
        }
    });
}

// 显示设置新密码界面
function show_resetpwd_step2(email_phone, token) {
    $('.resetpwd-step-1-wrap').hide();
    var step2 = $('.resetpwd-step-2-wrap');
    if (step2.length === 0) {
        var html = '<div class="resetpwd-step-2-wrap resetpwd-step-panel">' +
            '<form id="reset_password" class="resetpwd-form ajax-auth" method="post">' +
                '<p class="tips">验证成功，请为 ' + email_phone + ' 设置新密码</p>' +
                pwd_input_html() +
                '<input type="hidden" id="reset_token" name="token" value="' + token + '">' +
            '</form>' +
            '<div class="reset-pwd-btn"><a class="do-reset-pwd">确认重置</a></div>' +
        '</div>';
        $('.resetpwd-box').append(html);
    }
    $('.resetpwd-progress .progress-item').eq(0).addClass('completed');
    $('.resetpwd-progress .progress-item').eq(1).addClass('active');
}

// 确认重置密码
$('body').on('click', '.do-reset-pwd', function() {
    var token = $('#reset_token').val();
    var pwd = $('#pwd').val();
    var confirm_pwd = $('#confirm_pwd').val();

    if (pwd == '') {
        toastfy('请填写新密码', 'error');
        return;
    }
    if (pwd.length < 8 || pwd.length > 64) {
        toastfy('密码长度需8-64位', 'error');
        return;
    }
    if (!/[a-zA-Z]/.test(pwd) || !/[0-9]/.test(pwd)) {
        toastfy('密码必须包含数字和英文', 'error');
        return;
    }
    if (pwd != confirm_pwd) {
        toastfy('两次输入的密码不一致', 'error');
        return;
    }

    overlay_loading();
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': 'do_reset_pwd',
            'token': token,
            'new_password': pwd,
            'confirm_password': confirm_pwd,
            'nonce': Theme.user_nonce,
        },
        success: function(data) {
            if (data.code == 1) {
                toastfy(data.msg, 'success');
                $('.resetpwd-progress .progress-item').eq(1).addClass('completed');
                $('.resetpwd-progress .progress-item').eq(2).addClass('active');
                setTimeout(function() {
                    window.location.href = window.location.origin;
                }, 2000);
            } else {
                toastfy(data.msg, 'error');
                overlay_loading(true);
            }
        }
    });
});

// 发送重置密码验证码
function resetpwd_send_code(){
    var form = $('#resetpwd-form');
    var email_phone = form.find('#email_phone').val();

    toastfy('正在发送验证码，请稍候...','info');

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': 'send_phone_code',
            'email_phone': email_phone,
            'mode': 'reset_pwd',
            'security': '',
            'nonce': Theme.user_nonce
        },
        success: function (data) {
            if(data.code == 0){
                toastfy(data.msg || '验证码已发送，请注意查收','success');
                code_countdown($('.next-pwd'));
            } else {
                toastfy(data.msg,'error');
            }
        }
    });
}
