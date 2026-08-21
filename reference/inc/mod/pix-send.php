<?php
//发送短信验证码

function send_sms_code($mobile,$verify_code) {
    require THEME_DIR . '/inc/plugin/pix-sms.php';
    $result = Sms::send_sms($mobile,$verify_code);
    
    // 如果返回的是数组且有 error，直接返回
    if (is_array($result) && isset($result['error'])) {
        return $result;
    }
    
    // 如果返回 true，表示成功
    if ($result === true) {
        return true;
    }
    
    // 其他情况返回错误
    return array('error' => '短信发送失败，请检查配置');
}    

function ppo_sms_service_ready() {
    $sms_type = get_op('sms_type');
    if (empty($sms_type)) {
        return false;
    }

    if ($sms_type !== 'aliyunsms') {
        return false;
    }

    $ali_config = get_op('alisms');
    return is_array($ali_config)
        && !empty($ali_config['keyid'])
        && !empty($ali_config['keysecret']);
}

// 发送邮箱验证码
function send_email_code($email,$verify_code) {
	$blogname =  get_bloginfo('name');//站点名称
	$bloghome = get_bloginfo('url');//站点链接
	//$from_email = get_cst('smtp_email');
	$title = '['.$blogname.']-请查收您的验证码';
	$message = '<div class="email_warp" style="max-width:380px;width:100%;background: #fdfdff;margin: 0 auto;padding:30px;border: 1px solid #e0e8ff;border-radius: 4px;">
					<div class="mail_header" style="border-radius: 5px;padding:20px; margin-bottom: 20px; color:#2b3178; font-size: 18px;text-align: center;font-weight: 600;">您的验证码为:</div>
					<div class="mail_body">
						<div style="background: #e5e3ff;padding: 10px 20px;border-radius: 5px;color: #0d00b3;margin-top: 20px;font-size: 28px;text-align: center;font-weight: 600;">'.$verify_code.'</div>
						<p>此验证码5分钟内有效，为了保障您的账户安全，请勿向他人泄漏验证码信息</p>
						</div>
					<div class="mail_footer" style="text-align: center;margin-top: 60px;"><p>Copyright © <a href="'.$bloghome.'" target="_blank" style="color: #3b37ca;text-decoration: none;">'.$blogname.'</a></div>	
				</div>';
	$headers = array('Content-Type: text/html; charset=UTF-8','From: '.$blogname.'');
	
	$res = wp_mail( $email, $title, $message, $headers );
	if(!$res){
		return array('error'=>__('验证码发送失败，请联系管理员','ppo'));
	}
	return true;
}

// ajax发送手机验证码
add_action( 'wp_ajax_send_phone_code', 'send_phone_code' );
add_action( 'wp_ajax_nopriv_send_phone_code', 'send_phone_code' );
function send_phone_code(){
    check_ajax_referer('ppo_user_action', 'nonce');

    $mobile = isset($_POST['email_phone']) ? sanitize_text_field(wp_unslash($_POST['email_phone'])) : '';
	$mode = isset($_POST['mode']) ? sanitize_key($_POST['mode']) : '';
	$form_name = isset($_POST['form_name']) ? sanitize_key($_POST['form_name']) : '';
	$uid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
	$logincaptcha = isset($_POST['logincaptcha']) ? wp_unslash($_POST['logincaptcha']) : '';
	$verify_code = mt_rand(100000,999999);
    $ip = get_real_ip();
	$code = get_transient('auth_'.$mobile.'');
	$now = time();
    $allowed_modes = array('login', 'sms_log', 'sms_reg', 'sms_safe', 'reset_pwd', 'normal_reg');

    if (!in_array($mode, $allowed_modes, true)) {
        wp_send_json(array('code' => 1, 'msg' => '验证码场景错误'));
    }

    if ($mode === 'sms_safe') {
        if (!is_user_logged_in()) {
            wp_send_json(array('code' => 1, 'msg' => '请先登录'));
        }

        if ($uid !== get_current_user_id() && !current_user_can('edit_users')) {
            wp_send_json(array('code' => 1, 'msg' => '没有权限'));
        }
    }

    $ip_key = 'auth_send_ip_' . md5($ip . '|' . $mode);
    $ip_count = intval(get_transient($ip_key));
    if ($ip_count >= 20) {
        wp_send_json(array('code' => 1, 'msg' => '验证码发送过于频繁，请稍后再试'));
    }

	if(is_array($code) && !empty($code['code']) && $now - $code['time'] < 60){ 
		$res = array('code' => 1 , 'msg' => '请不要重复提交');
		wp_send_json($res);
	}

    if (is_phone($mobile) && !ppo_sms_service_ready()) {
        wp_send_json(array('code' => 1, 'msg' => '短信接口未配置，暂时无法发送手机验证码，请联系站点管理员'));
    }

	$captcha = get_op('captcha_type','normal');
	if($captcha == 'geetest'){
		$captcha_data = geetest_check($logincaptcha);
		if($captcha_data && $captcha_data['code'] == 1){
			wp_send_json($captcha_data);
		}
	} elseif($captcha == 'pixcap'){
		$captcha_data = pixcap_check($logincaptcha);
		if($captcha_data && $captcha_data['code'] == 1){
			wp_send_json($captcha_data);
		}
	} elseif($captcha == 'code'){
		if (!isset($_SESSION)) session_start();
		if (empty($_SESSION['captcha_verified'])) {
			wp_send_json(array('code' => 1, 'msg' => '请先完成验证码验证'));
		}
		unset($_SESSION['captcha_verified']);
	}
	
    if($mode == 'sms_safe'){
		$send_check = safe_send_check($form_name,$mobile,$uid);
	} else  {
		$send_check = code_send_check($mode,$mobile);
	}

	if(isset($send_check['error'])){
		$res = array('code' => 1 , 'msg' => $send_check['error']);
		wp_send_json($res);
	}

	$send = false;
	if(is_phone($mobile)){
		$send = send_sms_code($mobile,$verify_code);
	}

	if(is_email($mobile)){
		$send = send_email_code($mobile,$verify_code);
	}

	// 检查发送结果
	if($send === true || (is_array($send) && empty($send['error']))){
        set_transient($ip_key, $ip_count + 1, HOUR_IN_SECONDS);
		$auth_data = array(
			'code' => $verify_code,
			'time' => time(),
			'username' => $mobile,
			'ip' => $ip,
		);
		//将验证数据存入数据库，如果开启 redis 缓存则存入内存 300s
		set_transient('auth_'.$mobile.'',$auth_data,300); 
		
		$res = array('code' => 0 , 'msg' => '验证码已发送');
	} else {
		$error_msg = is_array($send) ? ($send['error'] ?? '发送失败') : '发送失败';
		$res = array('code' => 1 , 'msg' => $error_msg);
	}

	wp_send_json($res);
    
}

function is_phone($mobile) {
	return preg_match('/^1[3-9]\d{9}$/', $mobile);
}

// 用户安全检验
function safe_send_check($mode,$username,$uid){
	if($mode == 'user_email'){

		//$bind = check_email_bind($uid);
		//if(!$bind){
			
			if($username == '') return array('error'=>__('请输入要绑定的邮箱','ppo'));

			if(!is_email($username)){
				return array('error'=>__('您输入的不是邮箱','ppo'));
			}

			if(is_email($username) && email_exists($username)){
				return array('error'=>__('该邮箱已绑定，请换一个或解绑','ppo'));
			} 
		//}

	} else if($mode == 'user_phone'){
		//查询手机号是否绑定
		//$bind = get_user_meta($uid,'user_phone',true);

		//if(!$bind){ //如果没绑定
			
			if($username == '') return array('error'=>__('请输入要绑定的手机','ppo'));

			if(!is_phone($username)){
				return array('error'=>__('您输入的不是手机号码','ppo'));
			}

			if(is_phone($username) && ppo_get_user_by('phone', $username)){
				return array('error'=>__('该手机已绑定，请换一个或解绑','ppo'));
			} 
		//}
	}
	
}

//验证码发送类型
function code_send_check($mode,$username){
	if($username == '') return array('error'=>__('请输入邮箱或手机号码','ppo'));

	if($mode == 'sms_log'){
		$type = get_op('nopass_check_type','all');
	} else if($mode == 'sms_reg') {
		$type = get_op('reg_check_type','all');
	} else {
		$type = get_op('pwd_check_type','all');
	}

	switch ($type)
	{
	case "phone":
		if(!is_phone($username)){
			return array('error'=>__('您输入的不是手机号码','ppo'));
		}
		break;
	case "email":
		if(!is_email($username)){
			return array('error'=>__('您输入的不是邮箱','ppo'));
		}
		break;
	case "all":
		if(!is_email($username) && !is_phone($username)){
			return array('error'=>__('您输入的不是邮箱或手机号码','ppo'));
		}
		break;		
	}

	if($mode == 'sms_reg'){
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

	if($mode == 'sms_log'){
		if(is_email($username) && !email_exists($username)){
			return array('error'=>__('该邮箱尚未注册','ppo'));
		} 
		if(is_phone($username) && !ppo_get_user_by('phone', $username)){
			return array('error'=>__('该手机号尚未注册','ppo'));
		}
	}

	if($mode == 'reset_pwd'){
		if(is_email($username) && !email_exists($username)){
			return array('error'=>__('该邮箱不存在','ppo'));
		} 
		if(is_phone($username) && !ppo_get_user_by('phone', $username)){
			return array('error'=>__('该手机号码不存在','ppo'));
		}
	}

}


// 验证
function check_verify_code($mobile,$verify_code){
	if($verify_code == ''){
		return array('error'=>__('请输入验证码','ppo'));
	}

	$check = get_transient('auth_'.$mobile.'');
	if($check){
		if(!isset($check['code']) || $check['code'] != trim(strtolower($verify_code)) ){
			return array('error'=>__('验证码错误','ppo'));
		}

		if(!isset($check['username']) || $check['username'] != $mobile){
			return array('error'=>__('账户或号码不匹配','ppo'));
		}
	} else {
		return array('error'=>__('验证码已过期','ppo'));
	}
	
}


