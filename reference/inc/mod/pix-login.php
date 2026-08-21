<?php 
/* 前台登录注册 */

function ppo_identity_update_allow($user_id, $field, $value) {
    $GLOBALS['ppo_identity_update_allow'] = array(
        'user_id' => intval($user_id),
        'field'   => sanitize_key($field),
        'value'   => (string) $value,
    );
}

function ppo_identity_update_is_allowed($user_id, $field, $value) {
    if (current_user_can('manage_options')) {
        return true;
    }

    $allow = isset($GLOBALS['ppo_identity_update_allow']) ? $GLOBALS['ppo_identity_update_allow'] : array();
    return !empty($allow)
        && intval($allow['user_id']) === intval($user_id)
        && $allow['field'] === sanitize_key($field)
        && hash_equals((string) $allow['value'], (string) $value);
}

function ppo_phone_exists($phone, $exclude_user_id = 0) {
    $user = ppo_get_user_by('phone', $phone);
    if (!$user) {
        return false;
    }

    return intval($user->ID) !== intval($exclude_user_id);
}

function ppo_validate_identity_value($type, $value, $user_id = 0) {
    $value = trim((string) $value);
    $user_id = intval($user_id);

    if ($type === 'user_email') {
        $value = sanitize_email($value);
        if (!is_email($value)) {
            return array('error' => '邮箱格式错误');
        }

        $exists = email_exists($value);
        if ($exists && intval($exists) !== $user_id) {
            return array('error' => '该邮箱已被其他账号绑定');
        }

        return array('value' => $value);
    }

    if ($type === 'user_phone') {
        $value = sanitize_text_field($value);
        if (!is_phone($value)) {
            return array('error' => '手机号码格式错误');
        }

        if (ppo_phone_exists($value, $user_id)) {
            return array('error' => '该手机号码已被其他账号绑定');
        }

        return array('value' => $value);
    }

    return array('error' => '不允许修改该字段');
}

function ppo_guard_native_profile_sensitive_fields($errors, $update, $user) {
    if (!$update || empty($user->ID)) {
        return;
    }

    $old_user = get_userdata($user->ID);
    if (!$old_user) {
        return;
    }

    $new_email = isset($user->user_email) ? sanitize_email($user->user_email) : '';
    if ($new_email && $new_email !== $old_user->user_email && !ppo_identity_update_is_allowed($user->ID, 'user_email', $new_email)) {
        $errors->add('ppo_email_verify_required', '邮箱需要在前台账户安全中完成验证码验证后才能修改。');
    }
}
add_action('user_profile_update_errors', 'ppo_guard_native_profile_sensitive_fields', 10, 3);

function ppo_guard_user_email_update_data($data, $update, $user_id, $userdata) {
    if (!$update || empty($user_id) || empty($data['user_email'])) {
        return $data;
    }

    $old_user = get_userdata($user_id);
    if (!$old_user) {
        return $data;
    }

    $new_email = sanitize_email($data['user_email']);
    if ($new_email && $new_email !== $old_user->user_email && !ppo_identity_update_is_allowed($user_id, 'user_email', $new_email)) {
        $data['user_email'] = $old_user->user_email;
    }

    return $data;
}
add_filter('wp_pre_insert_user_data', 'ppo_guard_user_email_update_data', 10, 4);

function ppo_guard_user_phone_meta_update($check, $object_id, $meta_key, $meta_value) {
    if ($meta_key !== 'user_phone') {
        return $check;
    }

    if (ppo_identity_update_is_allowed($object_id, 'user_phone', $meta_value)) {
        return $check;
    }

    return false;
}
add_filter('add_user_metadata', 'ppo_guard_user_phone_meta_update', 10, 4);
add_filter('update_user_metadata', 'ppo_guard_user_phone_meta_update', 10, 4);
add_filter('delete_user_metadata', 'ppo_guard_user_phone_meta_update', 10, 4);

function ppo_send_register_system_msg($user_id, $request = array()) {
	$user_id = intval($user_id);
	$content = trim((string) get_op('reg_msg', ''));

	if (!$user_id || $content === '') {
		return false;
	}

	$user = get_userdata($user_id);
	$nickname = '';
	if (is_array($request) && !empty($request['nickname'])) {
		$nickname = (string) $request['nickname'];
	} elseif ($user) {
		$nickname = $user->display_name;
	}
	$username = is_array($request) && !empty($request['username']) ? (string) $request['username'] : ($user ? $user->user_login : '');

	$content = strtr($content, array(
		'{name}' => $nickname,
		'{nickname}' => $nickname,
		'{username}' => $username,
	));

	$bot = get_op('sys_bot', array());
	$icon = '';
	if (is_array($bot) && !empty($bot['avatar'])) {
		$icon = is_array($bot['avatar']) && !empty($bot['avatar']['url']) ? $bot['avatar']['url'] : $bot['avatar'];
	}

	$extra = array(
		'user_readed' => wp_json_encode(array()),
		'type' => '系统消息',
		'type_slug' => 'system_msg',
	);
	if (!empty($icon)) {
		$extra['icon'] = esc_url_raw($icon);
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'ppo_msg';

	$inserted = $wpdb->insert(
		$table_name,
		array(
			'receive_user' => 0,
			'send_id' => 1,
			'type' => 'system_msg',
			'title' => '欢迎加入',
			'content' => wp_kses_post($content),
			'create_time' => current_time('mysql'),
			'status' => 'unread',
			'extra' => serialize($extra),
			'info_meta' => 'chose_user',
			'other' => wp_json_encode(array($user_id)),
		),
		array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
	);

	return $inserted ? $wpdb->insert_id : false;
}

function ppo_validate_password_strength($password, $min = 8, $max = 64) {
    if (mb_strlen($password) < $min || mb_strlen($password) > $max) {
        return '密码长度需' . $min . '-' . $max . '位';
    }

    if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return '密码必须包含数字和英文';
    }

    return '';
}

function ppo_auth_rate_key($scope, $identity = '') {
    return 'ppo_auth_rate_' . md5(sanitize_key($scope) . '|' . get_real_ip() . '|' . strtolower((string) $identity));
}

function ppo_auth_rate_check($scope, $identity = '', $limit = 10, $ttl = 900) {
    $key = ppo_auth_rate_key($scope, $identity);
    $count = intval(get_transient($key));
    if ($count >= $limit) {
        return array('error' => '操作过于频繁，请稍后再试');
    }

    return array('key' => $key, 'count' => $count, 'ttl' => $ttl);
}

function ppo_auth_rate_hit($rate) {
    if (empty($rate['key'])) {
        return;
    }

    set_transient($rate['key'], intval($rate['count']) + 1, intval($rate['ttl']));
}

function ppo_auth_rate_clear($scope, $identity = '') {
    delete_transient(ppo_auth_rate_key($scope, $identity));
}

/*获取当前网址 默认固定链接无法使用*/
function ppo_get_curl(){
    global $wp;
    return home_url(add_query_arg(array(),$wp->request));
}

//安全验证方式 action: login | sms_log | sms_reg | reg | sms_safe
function captcha_type($action){ 
	$type = get_op('captcha_type','normal');

	$btn = push_btn_class($action);
	$geeid = '';

	if($type == 'geetest'){
		$geeid = get_op('geetest_id','');
	}

	$html = '<a class="'.$btn['class'].'" action="'.$action.'" type="'.$type.'" geetest-id="'.$geeid.'">'.$btn['title'].'</a>';

	return $html;
}

// 按钮类型
function push_btn_class($action){ 
	$title = '发送验证码';
	switch ($action)
	{
	case "login":
		$class =  'captcha-push';
		$title = '快速登录';
		break;
	case "sms_log":
		$class = 'sms-code-btn';
		break;
	case "sms_reg":
		$class = 'sms-code-btn';
		break;	
	case "sms_safe":
		$class = 'sms-code-btn';
		break;		
	case "reset_pwd":
		$class = 'sms-code-btn';
		break;		
	}

	return array('class' => $class , 'title' => $title);
}

// 注册按钮 
function reg_push_btn(){
	//如果没有开启验证注册，则开启行为验证
	$reg_type = get_op('reg_check_type','normal');
	$captcha_type = get_op('captcha_type','normal');

	$geeid = '';
	if($captcha_type == 'geetest'){
		$geeid = get_op('geetest_id','');
	}
	
		$action = 'action="normal_reg"';
		$type = 'type="'.$captcha_type.'"';
		$gid = 'geetest-id="'.$geeid.'"';

	if(!get_op('reg_action_verify')){
		$type = 'type="normal"';
	}

	if($reg_type != 'normal'){
		$type = 'type="normal"';
	}

	$html = '<a class="register-push" '.$action.' '.$type.' '.$gid.'>立即注册</a>';
	return $html;
}

// 默认登录方式
function def_login(){
	if(get_op('free_login') && get_op('def_login','normal') == 'nopass'){
		return true;
	} else {
		return false;
	}
}

// 账户密码登录
function normal_log(){
	$login_title = def_login() ? '免密登录' : '登录';

	$html = '<form id="login" class="ajax-auth" action="login" method="post">';
	$html .= '<p class="log_title">'.$login_title.'</p>';
	$html .= wp_nonce_field('ajax-login-nonce', 'security',true,false);
	$html .= normal_login();
	$html .= phone_login();
	if (get_op('reg_open', true)) {
		$html .= '<h3>没有账号？<a id="pop_signup" href="">立即创建</a></h3>';
	}
	$html .= '</form>';

	return $html;
}

function normal_login(){
	$active = 'active';
	if(def_login()){
		$active = 'hide';
	}

	$html = '<div id="nor-login" class="login-form-tab pix-auth-form-panel nor-login '.$active.'">';
	$html .= '<label for="username" class="log-form-item">';
	$html .= '<i class="ri-account-pin-circle-line logonicon"></i>';
	$html .= '<input id="username" type="text" class="required" name="username" placeholder="用户名/邮箱/手机号">';
	$html .= '</label>';
	$html .= '<label for="password" class="log-form-item">';
	$html .= '<i class="ri-lock-line logonicon"></i>';
	$html .= '<input id="login-password" type="password" class="required" name="password" autocomplete="off" placeholder="输入密码">';
	$html .= '<button type="button" class="pwd-toggle-btn" data-target="#login-password"><i class="ri-eye-off-line"></i></button>';
	$html .= '</label>';
	$html .= '<div class="login-tools"><a id="pop_forgot" class="text-link" href="'.home_url('/resetpwd').'">忘记密码?</a>';
	if(get_op('free_login')){
		$html .= '<a id="fast-login" class="text-link" href="">免密登录</a>';
	}
	$html .= '</div>';
	$html .= '<div class="push-login">';
	$html .= captcha_type('login');
	$html .= '</div>';
	$html .= '</div>';

	return $html;
}

//免密登录
function phone_login() {
	$active = 'hide';
	if(def_login()){
		$active = 'active';
	}

	$check_type = get_op('nopass_check_type','all');
	if($check_type == 'phone'){
		$pla = '请输入手机号码';
		$icon = 'ri-smartphone-line';
	} else if($check_type == 'email'){
		$pla = '请输入邮箱';
		$icon = 'ri-mail-line';
	} else {
		$pla = '请输入手机号或邮箱';
		$icon = 'ri-smartphone-line';
	}

	$html = '<div id="nopass-login" class="login-form-tab pix-auth-form-panel nopass-login '.$active.'">';
	$html .= '<label for="email_phone" class="log-form-item">';
	$html .= '<i class="'.$icon.' logonicon"></i>';
	$html .= '<input id="email_phone" type="text" class="required" name="email_phone" placeholder="'.$pla.'">';
	$html .= '</label>';
	$html .= '<label for="smscode" class="pe_code_box log-form-item">';
	$html .= '<i class="ri-shield-keyhole-line logonicon"></i>';
	$html .= '<input id="smscode" type="smscode" class="required" name="smscode" placeholder="请输入验证码">';
    $html .= captcha_type('sms_log');
	$html .= '</label>';
	$html .= '<div class="login-tools"><a id="pop_forgot" class="text-link" href="'.home_url('/resetpwd').'">忘记密码?</a>';
    $html .= '<a id="normal-login" class="text-link" href="">账号密码登录</a></div>';
	$html .= '<div class="push-login">';
	$html .= '<input class="submit_type" type="hidden" value="nopass">';
	$html .= '<a class="nopass-push">立即登录</a>';
	$html .= '</div>';
	$html .= '</div>';
	
	if(get_op('free_login')){
		return $html;
	}
}

//账户注册
function normal_reg() {
	$nick_required = get_op('reg_nickname_required', true);
	$html = '<form id="register" class="ajax-auth" action="register" method="post">';
	$html .= '<p class="log_title">注册</p>';
	$html .= wp_nonce_field('ajax-register-nonce', 'signonsecurity',true,false);
	if ($nick_required) {
		$html .= '<label for="nick_name" class="reg-form-item">';
		$html .= '<i class="ri-account-pin-circle-line logonicon"></i>';
		$html .= '<input id="nick_name" type="text" class="required" name="nick_name" placeholder="昵称">';
		$html .= '</label>';
	}
	$html .= reg_type();
	$html .= '<label for="password" class="reg-form-item">';
	$html .= '<i class="ri-lock-line logonicon"></i>';
	$html .= '<input id="reg-password" type="password" class="required" name="password" autocomplete="off" placeholder="输入密码">';
	$html .= '<button type="button" class="pwd-toggle-btn" data-target="#reg-password"><i class="ri-eye-off-line"></i></button>';
	$html .= '</label>';
	$html .= '<label for="reg-password-confirm" class="reg-form-item">';
	$html .= '<i class="ri-lock-2-line logonicon"></i>';
	$html .= '<input id="reg-password-confirm" type="password" class="required" name="password_confirm" autocomplete="off" placeholder="确认密码">';
	$html .= '<button type="button" class="pwd-toggle-btn" data-target="#reg-password-confirm"><i class="ri-eye-off-line"></i></button>';
	$html .= '</label>';
	$html .= reg_protocol();
	$html .= '<div class="push-login">';
	$html .= reg_push_btn();
	$html .= '</div>';
	$html .= '<h3>已有账号？<a id="pop_login" href="">立即登录</a></h3>';
	$html .= '</form>';
	
	return $html;
}

// 注册类型
function reg_type(){
	$type = get_op('reg_check_type','normal');
	switch ($type)
	{
	case "phone":
		return phone_check_reg($type);
		break;
	case "email":
		return phone_check_reg($type);
		break;
	case "all":
		return phone_check_reg($type);
		break;	
	case "invite":
		return '';
		break;		
	case "normal":
		return normal_reg_html();
		break;			
	}
}

// 普通注册
function normal_reg_html(){
	$html = '<label for="user_name" class="reg-form-item">';
	$html .= '<i class="ri-user-4-line logonicon"></i>';
	$html .= '<input id="user_name" type="text" class="required" name="user_name" placeholder="账户名">';
	$html .= '</label>';

	return $html;
}

//手机验证注册
function phone_check_reg($type){
	$pla = '请输入邮箱或手机号';
	$icon = 'ri-smartphone-line';
	if($type == 'email'){
		$pla = '请输入邮箱';
		$icon = 'ri-mail-line';
	} else if($type == 'phone'){
		$pla = '请输入手机号码';
	}

	$html = '<label for="user_name" class="reg-form-item">';
	$html .= '<i class="'.$icon.' logonicon"></i>';
	$html .= '<input id="user_name" type="text" class="required" name="user_name" placeholder="'.$pla.'">';
	$html .= '</label>';
	$html .= '<label for="smscode" class="pe_code_box reg-form-item">';
	$html .= '<i class="ri-shield-keyhole-line logonicon"></i>';
	$html .= '<input id="smscode" type="smscode" class="required" name="smscode" placeholder="请输入验证码">';
    $html .= captcha_type('sms_reg');
	$html .= '</label>';

	return $html;
}

//全局模态登录注册窗口
function global_login_modal(){
    $logo = site_logo('dark');
    $site_name = pix_global_logo_text();
    $html = '<a class="login-btn" href="#modal-login" data-pix-auth-open="login" aria-haspopup="dialog" aria-expanded="false" aria-controls="modal-login"><i class="ri-user-4-line"></i></a>
                <div id="modal-login" class="pix-modal pix-hs-modal pix-auth-modal hidden" role="dialog" tabindex="-1" aria-labelledby="modal-login-title">
                <div class="pix-modal-dialog hs-overlay-animation-target">
                <div class="pix-modal-panel login-modal-inner pix-auth-modal-panel">
                <button class="pix-modal-close pix-auth-modal-close" type="button" data-pix-modal-close="#modal-login" aria-label="关闭"><i class="ri-close-line"></i></button>
                    <div class="login-banner type-top">
                        <div class="inner">
                            <div class="login-logo">'.$logo.'</div>
                            <h3 id="modal-login-title" class="title">WELCOME TO '.esc_html($site_name).'</h3>
                        </div>
                    </div>
                    '.normal_log().'
					'.normal_reg().'
					'.get_oaouth_btn().'
                </div>
                </div>
            </div>';

    return $html;        
}

function ajax_auth_init(){	
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
	add_action( 'wp_ajax_nopriv_ajaxregister', 'ajax_register' );
	add_action( 'wp_ajax_nopriv_ajax_nopass_login', 'ajax_nopass_login' );
}

if (!is_user_logged_in()) {
    add_action('init', 'ajax_auth_init');
}

// ajax登录
function ajax_login(){
	$type = get_op('captcha_type','normal');
    check_ajax_referer( 'ajax-login-nonce', 'security' );

	$captcha = isset($_POST['logincaptcha']) ? $_POST['logincaptcha'] : '';
	if($type == 'geetest'){
		$captcha_data = geetest_check($captcha);
		if($captcha_data && $captcha_data['code'] == 1){
			wp_send_json($captcha_data);
		}
	} elseif($type == 'pixcap'){
		$captcha_data = pixcap_check($captcha);
		if($captcha_data && $captcha_data['code'] == 1){
			wp_send_json($captcha_data);
		}
	} elseif($type == 'code'){
		if (!isset($_SESSION)) session_start();
		if (empty($_SESSION['captcha_verified'])) {
			wp_send_json(array('code' => 1, 'msg' => '请先完成验证码验证'));
		}
		unset($_SESSION['captcha_verified']);
	}


	auth_user_login($_POST['username'], $_POST['password']);
	 
}


function auth_user_login($user_login, $password)
{
    $user_login = sanitize_text_field(wp_unslash($user_login));
    $rate = ppo_auth_rate_check('login', $user_login);
    if (isset($rate['error'])) {
        wp_send_json(array('code' => 1, 'msg' => $rate['error']));
    }

	$info = array();
    $info['user_login'] = $user_login;
    $info['user_password'] = $password;
    $info['remember'] = true;
	
	$user_signon = wp_signon( $info, '' ); // From false to '' since v4.9
    if ( is_wp_error($user_signon) ){
        ppo_auth_rate_hit($rate);
		$res = array('code' => 1 , 'msg'=>__('用户名或密码错误.'));
    } else {
        ppo_auth_rate_clear('login', $user_login);
		wp_set_current_user($user_signon->ID);
		$res = array('code' => 0 , 'msg'=>__('登录成功，跳转中...'));
    }
	
	wp_send_json($res);
}

//ajax免密登录
function ajax_nopass_login(){
	check_ajax_referer( 'ajax-login-nonce', 'security' );
	$username = isset($_POST['email_phone']) ? $_POST['email_phone'] : '';
	$smscode = isset($_POST['smscode']) ? $_POST['smscode'] : '';

	// 验证码检查
	$check = check_verify_code($username,$smscode); 
	if(isset($check['error'])){
		$msg = $check['error'];
	}

	if(is_email($username)){
		$user = get_user_by('email', $username);
        if (!$user) {
            $msg = __('未找到此邮件注册账户','ppo');
        }
	} else if(is_phone($username)){
		$user = ppo_get_user_by('phone', $username);
        if (!$user) {
            $msg = __('未找到此手机注册账户','ppo');
        }
	}

	if($msg){
		wp_send_json(array('code' => 1, 'msg' => $msg));
	} else {
		//执行登录
		wp_set_current_user($user->ID, $user->user_login);
		wp_set_auth_cookie($user->ID, true);
		do_action('wp_login', $user->user_login, $user);

		//删除验证缓存
		delete_transient('auth_'.$username.'');
		wp_send_json(array('code' => 0, 'msg' => __('登录成功，跳转中..','ppo')));
	}
	
}

// ajax注册
function ajax_register(){

    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-register-nonce', 'security' );

    // 检查是否开启注册
    if (!get_op('reg_open', true)) {
        wp_send_json(array('code' => 1, 'msg' => __('注册功能已关闭，请联系管理员')));
    }

    $msg = '';
    $reg_type = get_op('reg_check_type','normal');
    $nick_required = get_op('reg_nickname_required', true);

    $user_name = isset($_POST['user_name']) ? sanitize_user($_POST['user_name']) : '';
    $nick_name = isset($_POST['nick_name']) ? sanitize_text_field($_POST['nick_name']) : '';

    $request['username'] = $user_name;
    $request['nickname'] = $nick_name;
    $request['user_pass'] = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
    $request['password_confirm'] = isset($_POST['password_confirm']) ? sanitize_text_field($_POST['password_confirm']) : '';

    // Nonce is checked, get the POST data and sign user on
    $info = array();

    // 昵称处理：有填就用，没填就留空
    if (!empty($request['nickname'])) {
        $nicename = sanitize_title($request['nickname']);
        if (empty($nicename)) {
            $nicename = 'user_' . time() . '_' . mt_rand(100, 999);
        }
        $info['user_nicename'] = $nicename;
        $info['nickname'] = $info['display_name'] = $info['first_name'] = $request['nickname'];
    } else {
        $info['user_nicename'] = 'user_' . time() . '_' . mt_rand(100, 999);
        $info['nickname'] = $info['display_name'] = $info['first_name'] = $request['username'];
    }

    $info['user_login'] = $request['username'];
    $info['user_pass'] = $request['user_pass'];

    // 行为验证检测：邮箱/手机验证码注册已在发码时完成安全验证，提交注册时只校验收到的验证码。
    $captcha_type = get_op('captcha_type','normal');
    $captcha = isset($_POST['logincaptcha']) ? $_POST['logincaptcha'] : '';
    $need_register_captcha = ($reg_type === 'normal' && get_op('reg_action_verify'));
    if($need_register_captcha){
        if($captcha_type == 'geetest'){
            $captcha_data = geetest_check($captcha);
            if($captcha_data && $captcha_data['code'] == 1){
                $msg = $captcha_data['msg'];
            }
        } elseif($captcha_type == 'pixcap'){
            $captcha_data = pixcap_check($captcha);
            if($captcha_data && $captcha_data['code'] == 1){
                $msg = $captcha_data['msg'];
            }
        } elseif($captcha_type == 'code'){
            if (!isset($_SESSION)) session_start();
            if (empty($_SESSION['captcha_verified'])) {
                $msg = '请先完成验证码验证';
            }
            unset($_SESSION['captcha_verified']);
        }
    }

    // 检查昵称（仅当填写了昵称时）
    if (!empty($request['nickname'])) {
        $nickname_check = check_nickname($request['nickname']);
        if($nickname_check){
            $msg = $nickname_check['error'];
        }
    } elseif ($nick_required) {
        $msg = '请输入昵称';
    }

    // 检查用户名
    $username_check = username_check($request['username']);
    if($username_check){
        $msg = $username_check['error'];
    }

    // 检查确认密码
    if ($request['password_confirm'] !== $request['user_pass']) {
        $msg = '两次密码输入不一致';
    }

    // 手机邮箱验证码检查
    if($reg_type == 'email' || $reg_type == 'phone' || $reg_type == 'all'){
        $verify_code = isset($_POST['smscode']) ? $_POST['smscode'] : '';
        $check = check_verify_code($request['username'],$verify_code);
        if(isset($check['error'])){
            $msg = $check['error'];
        }
    }

    $password_msg = ppo_validate_password_strength($request['user_pass']);
    if ($password_msg) {
        $msg = __($password_msg, 'ppo');
    }


    // Register the user
    if($msg){
        wp_send_json(array('code' => 1, 'msg' => $msg));
    } else {
    $user_register = wp_insert_user( $info );

    if ( is_wp_error($user_register) ){	
        $error  = $user_register->get_error_codes();
        
        if(in_array('empty_user_login', $error))
            wp_send_json(array('code' => 1, 'msg' => __($user_register->get_error_message('empty_user_login'))));		
        elseif(in_array('existing_user_login',$error))
            wp_send_json(array('code' => 1, 'msg' => __('该用户名已存在')));
            
        elseif(in_array('existing_user_email',$error))
            wp_send_json(array('code' => 1, 'msg' => __('该邮箱已注册')));
        else
            wp_send_json(array('code' => 1, 'msg' => $user_register->get_error_message()));
    } else {
     
        reg_after_callback($reg_type,$request,$user_register);    
    }
 
    } 
}

// 注册成功回调
function reg_after_callback($type,$request,$user_id){
	$email = '';
	if($type == 'phone' || $type == 'email' || $type == 'all'){
		//如果是邮箱注册，更新用户名
		// 生成更随机的用户名后缀（时间戳 + 随机数）
		$rand = time() . '_' . mt_rand(1000, 9999);
		if(is_email($request['username'])){
			// 修改用户名
			$email = $request['username'];
			global $wpdb;
            $wpdb->update($wpdb->users, array('user_login' => 'user'.$user_id.'_'.$rand), array('ID' => (int)$user_id));
		} 
		if(is_phone($request['username'])) {
			// 添加手机字段
            ppo_identity_update_allow($user_id, 'user_phone', $request['username']);
			update_user_meta($user_id,'user_phone',$request['username']);
		}

		// 删除验证码缓存
		delete_transient('auth_'.$request['username'].'');
		
	} 

	// 更新昵称和邮箱：昵称非必填时，避免把已有显示名覆盖成空。
	$arr = array(
		'ID'=>$user_id,
	);

	if (!empty($request['nickname'])) {
		$arr['display_name'] = $request['nickname'];
		$arr['nickname'] = $request['nickname'];
		$arr['first_name'] = $request['nickname'];
	}

	if (is_email($email)) {
		$arr['user_email'] = $email;
	}

	if (isset($arr['user_email'])) {
		ppo_identity_update_allow($user_id, 'user_email', $email);
	}
	if (count($arr) > 1) {
		wp_update_user($arr);
	}

	ppo_send_register_system_msg($user_id, $request);

	auth_user_login($request['username'], $request['user_pass'], '');
	wp_send_json(array('code' => 0, 'msg' => __('恭喜，注册成功！')));

}

//隐私政策和用户协议
function reg_protocol(){
	$privacy = trim((string) get_op('reg_privacy', ''));
	$protocol = trim((string) get_op('reg_protocol', ''));

	if (empty($privacy) && empty($protocol)) {
		return;
	}

	$html = '<div class="reg_protocol">';
	$html .= '<label><input class="pix-auth-checkbox protocol-check" type="checkbox">';
	$html .= '<span>我已同意</span>';
	if (!empty($protocol)) {
		$html .= '<a href="'.esc_url($protocol).'" target="_blank" rel="noopener noreferrer">用户协议</a>';
	}
	if (!empty($privacy) && !empty($protocol)) {
		$html .= '<span>和</span>';
	}
	if (!empty($privacy)) {
		$html .= '<a href="'.esc_url($privacy).'" target="_blank" rel="noopener noreferrer">隐私政策</a>';
	}
	$html .= '</label></div>';

	return $html;
}

//昵称重复检查
function check_nickname($nickname){
	global $wpdb;
	$table_name = $wpdb->prefix . 'users';
	$result = $wpdb->get_row($wpdb->prepare(
		"SELECT * FROM $table_name WHERE display_name = %s" , 
		$nickname
	));

	if($result){
		return array('error' => __('该昵称已存在，请换一个','ppo'));
	}

}

// 用户名监测
function username_check($username){
	if($username == '') return array('error'=>__('请输入用户名','ppo'));

	if(is_email($username) && email_exists($username)){
		return array('error'=>__('该邮箱已被注册','ppo'));
	}

	if(is_phone($username) && ppo_get_user_by('phone', $username)){
        return array('error'=>__('该手机号码已被注册','ppo'));
    }

	if(username_exists($username)){
		return array('error'=>__('该用户名已被注册','ppo'));
	}
}

// 根据meta获取用户信息
function ppo_get_user_by($field , $value){
	$cache = wp_cache_get($value, 'user_by_' . $field, true);
    if (false !== $cache) {
        return $cache;
    }

	$query = new WP_User_Query(array('meta_key' => 'user_phone', 'meta_value' => $value));

    if (!is_wp_error($query) && !empty($query->get_results())) {
        $user = $query->get_results()[0];
        wp_cache_set($value, $user, 'user_by_' . $field);
        return $user;
    } else {
        return false;
    }
}


