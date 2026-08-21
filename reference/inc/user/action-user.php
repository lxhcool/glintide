<?php

//用户资料设置
function ajax_user_edit(){
    $current_user = wp_get_current_user();
    if (!is_user_logged_in()) {
        wp_send_json(array('code'=>1,'msg'=>'请先登录'));
    }

    check_ajax_referer('ppo_user_action', 'nonce');

    $value = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
    $allowed_fields = array(
        'display_name' => 'sanitize_text_field',
        'nickname' => 'sanitize_text_field',
        'first_name' => 'sanitize_text_field',
        'last_name' => 'sanitize_text_field',
        'description' => 'sanitize_textarea_field',
        'user_url' => 'esc_url_raw',
        'user_gender' => 'absint',
    );

    if (!isset($allowed_fields[$type])) {
        wp_send_json(array('code'=>1,'msg'=>'不允许修改该字段'));
    }

    if($current_user->ID == $user_id || current_user_can('edit_users')){
    if($current_user->ID == $user_id && !current_user_can('edit_users') && function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $allow_profile = function_exists('pix_normal_user_can') ? pix_normal_user_can('normal_user_allow_edit_profile', true, $user_id) : true;
        if(!$allow_profile){
            wp_send_json(array('code'=>1,'msg'=>'普通用户暂不能修改个人资料'));
        }
    }

    $msg = '';

    if($type == 'display_name'){
        $nickname_check = check_nickname(sanitize_text_field($value));
        if($nickname_check){
            $msg = $nickname_check['error'];
        }
    }

    if($msg){
        wp_send_json(array('code'=>1,'msg'=>$msg));
    } else {
        $value = call_user_func($allowed_fields[$type], $value);
        if ($type === 'user_gender') {
            if (!in_array($value, array(0, 1, 2), true)) {
                wp_send_json(array('code'=>1,'msg'=>'性别参数错误'));
            }
            update_user_meta($user_id, 'user_gender', $value);
            wp_send_json(array('code'=>0,'msg'=>'修改成功'));
        }

        $arr = array(
            'ID' => $user_id, 
        );

        $arr[$type] = $value;

        $update_user_id = wp_update_user($arr);

        if ( ! is_wp_error( $update_user_id ) ){
			wp_send_json(array('code'=>0,'msg'=>'修改成功'));
        } 
        
    }
} else {
    wp_send_json(array('code'=>1,'msg'=>'没有权限'));
}

}
//add_action( 'wp_ajax_nopriv_ajax_user_edit', 'ajax_user_edit' );
add_action( 'wp_ajax_ajax_user_edit', 'ajax_user_edit' );

// 用户安全

function user_safe_edit(){
    if (!is_user_logged_in()) {
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('ppo_user_action', 'nonce');

    $current_user = wp_get_current_user();
    $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';

    if ($current_user->ID !== $user_id && !current_user_can('edit_users')) {
        wp_send_json(array('code'=>0,'msg'=>'没有权限'));
    }
    
    $output = safe_form_type($type,$user_id);
    if (!$output) {
        wp_send_json(array('code'=>0,'msg'=>'参数错误'));
    }

    wp_send_json(array('html' => $output));

}
add_action( 'wp_ajax_user_safe_edit', 'user_safe_edit' );

function user_pass_edit(){
    if (!is_user_logged_in()) {
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('ppo_user_action', 'nonce');

    $current_user = wp_get_current_user();
    $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    if($type != 'user_pass'){
        wp_send_json(array('code'=>0,'msg'=>'参数错误'));
    }
    if ($current_user->ID !== $user_id && !current_user_can('edit_users')) {
        wp_send_json(array('code'=>0,'msg'=>'没有权限'));
    }
    
    $output = safe_pass_form($type,$user_id);
    wp_send_json(array('code'=>1,'html' => $output));
}
add_action( 'wp_ajax_user_pass_edit', 'user_pass_edit' );

function safe_form_type($type,$user_id){
    switch ($type)
        {
        /* case 'user_pass':
            return safe_pass_form($type,$user_id);
            break; */
        case 'user_email':
            return safe_email_form($type,$user_id);
            break;
        case 'user_phone':
            return safe_email_form($type,$user_id);
            break;
        }
}

function safe_pass_form($type,$user_id){
    $html = '';
    if(ppo_user_has_custom_password($user_id)){
        $html .= '<div class="tips">* 修改密码需要先验证当前旧密码</div>';
        $html .= '<label><i class="ri-lock-password-line"></i><input type="password" id="old_pass" class="user-safe-form" name="'.$type.'" value="" placeholder="输入旧密码"></label>';
    } else {
        $html .= '<div class="tips">* 当前账号尚未设置自定义密码，可直接设置新密码</div>';
    } 
    
    $html .= '<label><i class="ri-lock-password-line"></i><input type="password" id="userpass1" class="user-safe-form" name="'.$type.'" value="" placeholder="输入新密码"></label>';
    $html .= '<label><i class="ri-refresh-line"></i><input type="password" id="userpass2" class="user-safe-form" name="'.$type.'" value="" placeholder="重复新密码"></label>';
    $html .= '<input type="hidden" class="user-safe-action" name="user-safe-action" value="'.$type.'">';
    return $html;
}

function safe_email_form($type,$user_id){
    //$bind = check_email_bind($user_id);
    //$user_info = get_userdata($user_id);
    //$v = $bind ? $user_info->user_email : '';
    //$d = $bind ? 'disabled' : '';

    if($type == 'user_email'){
        $icon = '<i class="ri-mail-line"></i>';
        $name = '邮箱';
    } else if($type == 'user_phone') {
        $icon = '<i class="ri-smartphone-line"></i>';
        $name = '手机';
    }

    $html = '<div class="tips">* 输入新'.$name.'以获取验证码</div>';
    if ($type == 'user_phone' && function_exists('ppo_sms_service_ready') && !ppo_sms_service_ready()) {
        $html .= '<div class="tips error">* 当前站点短信接口未配置，暂时无法发送手机验证码</div>';
    }
    $html .= '<label>'.$icon.'<input type="text" id="email_phone" class="user-safe-form" name="'.$type.'" value="" autocomplete="off" placeholder="输入新'.$name.'"></label>';
    $html .= '<label><i class="ri-shield-keyhole-line"></i><input type="text" id="bind-email-code" autocomplete="off" class="user-safe-form" value="" placeholder="请输入验证码">'.captcha_type('sms_safe').'</label>';
    $html .= '<input type="hidden" class="user-safe-action" name="user-safe-action" value="'.$type.'">';
    return $html;
}

function user_safe_save(){
    $current_user = wp_get_current_user();
    if (!is_user_logged_in()) {
        wp_send_json(array('code' => 1, 'msg' => '请先登录'));
    }

    check_ajax_referer('ppo_user_action', 'nonce');

    $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    $code = isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '';
    $value = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';
    $msg = '';

    if($current_user->ID == $user_id || current_user_can('edit_users')){
    $identity = ppo_validate_identity_value($type, $value, $user_id);
    if (isset($identity['error'])) {
        wp_send_json(array('code' => 1, 'msg' => $identity['error']));
    }
    $value = $identity['value'];

    // 验证码检查
	$check = check_verify_code($value,$code); 
	if(isset($check['error'])){
		$msg = $check['error'];
	}

    if($msg){
        wp_send_json(array('code' => 1, 'msg' =>$msg ));
    } else {
        
        // 检查用户之前的邮箱，用于判断是否首次绑定（直接查询数据库，更快更准确）
        global $wpdb;
        $old_email = $wpdb->get_var($wpdb->prepare(
            "SELECT user_email FROM $wpdb->users WHERE ID = %d",
            $user_id
        ));
        
        // 如果是绑定手机号码，提前获取旧值用于判断是否首次绑定
        $old_phone = '';
        if ($type == 'user_phone') {
            global $wpdb;
            $old_phone = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = 'user_phone'",
                $user_id
            ));
        }
        
        ppo_identity_update_allow($user_id, $type, $value);

        if ($type === 'user_email') {
            $update_user_id = wp_update_user(array(
                'ID' => $user_id,
                'user_email' => $value,
            ));
        } else {
            $update_user_id = update_user_meta($user_id, 'user_phone', $value);
        }

        $updated = !is_wp_error($update_user_id) && ($update_user_id || ($type === 'user_phone' && get_user_meta($user_id, 'user_phone', true) === $value));

        if ( $updated ){
            //删除验证缓存
		    delete_transient('auth_'.$value.'');
            
            // 如果是绑定邮箱且之前没有邮箱，触发邮箱任务奖励    
            if ($type == 'user_email' && empty($old_email)) {
                if (function_exists('ppo_give_new_user_reward')) {
                    $result = ppo_give_new_user_reward($user_id, 'email', '用户中心绑定邮箱');
                } 
            } 
            
            // 如果是绑定手机号码且之前没有手机号码，触发手机号码任务奖励
            if ($type == 'user_phone' && empty($old_phone)) {
                if (function_exists('ppo_give_new_user_reward')) {
                    $result = ppo_give_new_user_reward($user_id, 'phone', '用户中心绑定手机号码');
                }
            }
            
			wp_send_json(array('code' => 0, 'msg' => __('绑定成功','ppo')));
		}

        $error_msg = is_wp_error($update_user_id) ? $update_user_id->get_error_message() : '绑定失败，请重试';
        wp_send_json(array('code' => 1, 'msg' => $error_msg));
    }
} else {
    wp_send_json(array('code' => 1, 'msg' => '没有权限'));
}
    
}
add_action( 'wp_ajax_user_safe_save', 'user_safe_save' );

// 用户后台修改密码
function user_pass_save(){
    $current_user = wp_get_current_user();
    check_ajax_referer('ppo_user_action', 'nonce');

    $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    $old_pass = isset($_POST['old_pass']) ? $_POST['old_pass'] : '';
    $pass1 = isset($_POST['pass1']) ? $_POST['pass1'] : '';
    $pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : '';
    
    // 验证用户登录状态
    if (!is_user_logged_in()) {
        wp_send_json(array('code' => 1, 'msg' => '请先登录'));
    }
    if ($type !== 'user_pass') {
        wp_send_json(array('code' => 1, 'msg' => '参数错误'));
    }
    
    // 验证当前用户权限
    if ($current_user->ID != $user_id && !current_user_can('edit_users')) {
        wp_send_json(array('code' => 1, 'msg' => '没有权限'));
    }

    if (function_exists('get_op') && get_op('captcha_type', 'normal') === 'pixcap') {
        if (!function_exists('pixcap_check')) {
            wp_send_json(array('code' => 1, 'msg' => 'Pixcap 资源未加载，请联系管理员'));
        }
        $captcha = isset($_POST['logincaptcha']) ? wp_unslash($_POST['logincaptcha']) : '';
        $captcha_data = pixcap_check($captcha);
        if ($captcha_data && !empty($captcha_data['code'])) {
            wp_send_json($captcha_data);
        }
    }
    
    // 验证密码
    if (empty($pass1) || empty($pass2)) {
        wp_send_json(array('code' => 1, 'msg' => '请填写新密码'));
    }

    $password_msg = ppo_validate_password_strength($pass1);
    if ($password_msg) {
        wp_send_json(array('code' => 1, 'msg' => __($password_msg, 'ppo')));
    }
    
    if ($pass1 !== $pass2) {
        wp_send_json(array('code' => 1, 'msg' => '两次密码不一致'));
    }
    
    // 获取用户对象
    $user = get_user_by('id', $user_id);
    if (!$user) {
        wp_send_json(array('code' => 1, 'msg' => '用户不存在'));
    }
    
    // 如果是修改密码（存在旧密码），验证旧密码
    if (!empty($old_pass)) {
        if (!wp_check_password($old_pass, $user->user_pass, $user_id)) {
            wp_send_json(array('code' => 1, 'msg' => '旧密码错误'));
        }
    }
    
    // 只有明确标记为社交登录创建且未设置过密码的账号，才允许首次免旧密码。
    $can_set_without_old = function_exists('ppo_user_can_set_password_without_old')
        ? ppo_user_can_set_password_without_old($user_id)
        : (get_user_meta($user_id, 'password_set_by_user', true) === '0');
    
    // 如果是修改密码且不是社交登录首次设置，验证旧密码
    if (empty($old_pass) && !$can_set_without_old) {
        wp_send_json(array('code' => 1, 'msg' => '请填写旧密码'));
    }
    
    // 重置密码
    wp_set_password($pass1, $user_id);

    // 更新社交登录用户的密码设置标记
    if ($can_set_without_old) {
        update_user_meta($user_id, 'password_set_by_user', '1');
    }

    // 清除当前用户的登录状态
    wp_clear_auth_cookie();
    wp_set_current_user(0);

    // 返回成功并告知前端需要重新登录
    $msg = empty($old_pass) ? '密码设置成功，请重新登录' : '密码修改成功，请重新登录';
    wp_send_json(array(
        'code' => 0,
        'msg' => $msg,
        'need_relogin' => true
    ));
}
add_action( 'wp_ajax_user_pass_save', 'user_pass_save' );

// 密码找回第一步：验证身份
function reset_pwd(){
    global $wpdb;
    check_ajax_referer('ppo_user_action', 'nonce');

    $number = isset($_POST['email_phone']) ? sanitize_text_field($_POST['email_phone']) : '';
    $smscode = isset($_POST['smscode']) ? sanitize_text_field($_POST['smscode']) : '';

    // 参数验证
    if (empty($number)) {
        wp_send_json(array('code' => 0, 'msg' => __('请填写手机号或邮箱','ppo')));
    }
    if (empty($smscode)) {
        wp_send_json(array('code' => 0, 'msg' => __('请填写验证码','ppo')));
    }

    // 判断是邮箱还是手机号
    $is_email = is_email($number);
    $is_mobile = preg_match('/^1[3-9]\d{9}$/', $number);

    if (!$is_email && !$is_mobile) {
        wp_send_json(array('code' => 0, 'msg' => __('请输入正确的手机号或邮箱','ppo')));
    }

    // 用户存在性验证
    if ($is_email) {
        if (!email_exists($number)) {
            wp_send_json(array('code' => 0, 'msg' => __('该邮箱不存在','ppo')));
        }
    } else {
        if (!ppo_get_user_by('phone', $number)) {
            wp_send_json(array('code' => 0, 'msg' => __('该手机号码不存在','ppo')));
        }
    }

    // 验证码校验（包含号码一致性检查）
    $sms_code_check = check_verify_code($number, $smscode);
    if (isset($sms_code_check['error'])) {
        wp_send_json(array('code' => 0, 'msg' => $sms_code_check['error']));
    }

    // 获取用户ID
    if ($is_email) {
        $user = get_user_by('email', $number);
        $user_id = $user->ID;
    } else {
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'user_phone' AND meta_value = %s",
            $number
        ));
        $user_id = $user->user_id;
    }

    // 生成一次性 token
    $token = wp_hash($user_id . time() . wp_generate_password(16));

    // 存储 token，有效期10分钟
    set_transient('reset_token_'.$token, array(
        'user_id' => $user_id,
        'number' => $number,
        'type' => $is_email ? 'email' : 'mobile',
        'time' => time()
    ), 600);

    // 删除验证码，防止重复使用
    delete_transient('auth_'.$number);

    wp_send_json(array(
        'code' => 1,
        'msg' => __('验证成功，请设置密码','ppo'),
        'token' => $token
    ));
}
add_action( 'wp_ajax_reset_pwd', 'reset_pwd' );
add_action( 'wp_ajax_nopriv_reset_pwd', 'reset_pwd' );



// 密码找回第二步：使用 token 重置密码
function do_reset_pwd(){
    check_ajax_referer('ppo_user_action', 'nonce');

    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // 参数验证
    if (empty($token)) {
        wp_send_json(array('code' => 0, 'msg' => __('验证已过期，请重新验证','ppo')));
    }
    if (empty($new_password)) {
        wp_send_json(array('code' => 0, 'msg' => __('请填写新密码','ppo')));
    }
    $password_msg = ppo_validate_password_strength($new_password);
    if ($password_msg) {
        wp_send_json(array('code' => 0, 'msg' => __($password_msg, 'ppo')));
    }
    if ($new_password !== $confirm_password) {
        wp_send_json(array('code' => 0, 'msg' => __('两次密码不一致','ppo')));
    }

    // 获取 token 数据
    $token_data = get_transient('reset_token_'.$token);

    if (!$token_data) {
        wp_send_json(array('code' => 0, 'msg' => __('验证已过期，请重新验证','ppo')));
    }

    $user_id = $token_data['user_id'];

    // 重置密码
    wp_set_password($new_password, $user_id);

    // 删除 token（一次性使用）
    delete_transient('reset_token_'.$token);

    wp_send_json(array(
        'code' => 1,
        'msg' => __('密码重置成功，请使用新密码登录','ppo')
    ));
}
add_action( 'wp_ajax_do_reset_pwd', 'do_reset_pwd' );
add_action( 'wp_ajax_nopriv_do_reset_pwd', 'do_reset_pwd' );



function ppo_avatar_upload(){
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( '用户未登录' );
    }

    check_ajax_referer('ppo_user_action', 'nonce');

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    if(function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $allow_avatar = function_exists('pix_normal_user_upload_allowed') ? pix_normal_user_upload_allowed('avatar', $user_id) : true;
        if(!$allow_avatar){
            wp_send_json_error('普通用户暂不能修改头像');
        }
    }

    define( 'AVATARS_PATH', ABSPATH.'/wp-content/uploads/ppo-avatars/' );
    $upfile = AVATARS_PATH;
    if( !is_dir($upfile) ){
        mkdir($upfile,0755,true);
    } 

    $configuration = [
		'limit' => 1,
		'fileMaxSize' => 2,
		'extensions' => ['image/*'],
		'title' => 'user-'.$user_id.'-avatar',
		'uploadDir' => $upfile,
		'replace' => true,
		'editor' => [
			'maxWidth' => 150,
			'maxHeight' => 150,
			'crop' => false,
			'quality' => 80
		]
	];

    if (isset($_POST['fileuploader']) && isset($_POST['name'])) {
		$name = str_replace(array('/', '\\'), '', $_POST['name']);
		$editing = isset($_POST['editing']) && $_POST['editing'] == true;
		
		if (is_file($configuration['uploadDir'] . $name)) {
			$configuration['title'] = $name;
			$configuration['replace'] = true;
		}
	}

	// initialize FileUploader
    $FileUploader = new FileUploader('avatar-pond', $configuration);
	
	// call to upload the files
    $data = $FileUploader->upload();
    
    // change file's public data
    if (!empty($data['files'])) {
        $item = $data['files'][0];
        
        $data['files'][0] = array(
            'title' => $item['title'],
            'name' => $item['name'],
            'size' => $item['size'],
            'size2' => $item['size2']
        );

        $avatar = get_bloginfo('url').'/wp-content/uploads/ppo-avatars/'.$item['name'];
        update_user_meta($user_id,'custom_avatar',$avatar);
        update_user_meta($user_id,'upload_avatar',$avatar);

        // 上传头像动作
        do_action('ppo_user_uploaded_avatar', $user_id, $avatar);
    }
    /* $file = $_FILES['avatar-pond'];
    
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( '用户未登录' );
    }

    // 检查是否有内容上传
    if ( empty( $file ) ) {
        wp_send_json_error( '没有选择文件' );
    }

    // 检查是否为图片文件
    if ( ! in_array( $file['type'], array( 'image/jpeg', 'image/png', 'image/gif' ) ) ) {
        wp_send_json_error( '只允许上传图片文件' );
    }

    // 上传文件到 WordPress 媒体库
    $attachment_id = media_handle_upload( 'avatar-pond', 0 );

    // 检查是否成功上传
    if ( is_wp_error( $attachment_id ) ) {
        wp_send_json_error( $attachment_id->get_error_message() );
    }

    // 获取图片的 URL
    $image_url = wp_get_attachment_url( $attachment_id ); */

    // 返回图片的 URL 给前端
    wp_send_json($data);
}
add_action( 'wp_ajax_ppo_avatar_upload', 'ppo_avatar_upload' );

function ppo_avatar_remove(){
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( '用户未登录' );
    }

    check_ajax_referer('ppo_user_action', 'nonce');

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    if(function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $allow_avatar = function_exists('pix_normal_user_upload_allowed') ? pix_normal_user_upload_allowed('avatar', $user_id) : true;
        if(!$allow_avatar){
            wp_send_json_error('普通用户暂不能修改头像');
        }
    }

    define( 'AVATARS_PATH', ABSPATH.'/wp-content/uploads/ppo-avatars/' );
    $upfile = AVATARS_PATH;
    if (isset($_POST['file'])) {
        $file = $upfile . str_replace(array('/', '\\'), '', $_POST['file']);
        
        if(file_exists($file)){
            update_user_meta($user_id,'custom_avatar','');
            update_user_meta($user_id,'upload_avatar','');
            unlink($file);
        }
            
    }

}
add_action( 'wp_ajax_ppo_avatar_remove', 'ppo_avatar_remove' );

//切换其他头像
function change_avatar(){
    if ( ! is_user_logged_in() ) {
        wp_send_json(array('code'=>1,'msg'=>'用户未登录'));
    }

    check_ajax_referer('ppo_user_action', 'nonce');

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $uid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    $src = isset($_POST['src']) ? esc_url($_POST['src']) : '';

    if($user_id == $uid){
        if(function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
            $allow_avatar = function_exists('pix_normal_user_upload_allowed') ? pix_normal_user_upload_allowed('avatar', $user_id) : true;
            if(!$allow_avatar){
                wp_send_json(array('code'=>1,'msg'=>'普通用户暂不能修改头像'));
            }
        }

        $res = update_user_meta($user_id,'custom_avatar',$src);

        if(!$res){
            $msg = array('code'=>1,'msg'=>'更换失败，请重试');
        } else {
            $msg = array('code'=>0,'msg'=>'更换成功，正在跳转..');
        }
    } else {
        $msg = array('code'=>1,'msg'=>'没有权限');
    }

    wp_send_json($msg);

}
add_action( 'wp_ajax_change_avatar', 'change_avatar' );


// 用户收藏列表回调
function ppo_get_user_collect($request){
    $user_id  = intval($request->get_param('user_id'));
    $page     = max(1, intval($request->get_param('page')));

    if ($target = $request->get_param('target')) {
        $_GET['target'] = sanitize_text_field($target);
    }
    if ($push_url_base = $request->get_param('push_url_base')) {
        $_GET['push_url_base'] = sanitize_text_field($push_url_base);
    }

    $per_page = defined('PPO_USER_POSTS_PER_PAGE') ? PPO_USER_POSTS_PER_PAGE : 9;
    $html = ppo_render_user_collect($user_id, $page, $per_page);

    echo $html;
    exit;
}

function ppo_render_user_collect($user_id, $page = 1, $per_page = 9){
    $collect_list = get_user_meta($user_id, 'post_collect', true);
    $args = array(
        'post_type' => 'post',
        'author'         => $user_id,
        'post_status'    => 'publish',
        'post__in' => $collect_list,
        'posts_per_page' => $per_page,
        'paged' => $page
    );

    $my_query = new WP_Query($args);

    ob_start();

        if( $my_query->have_posts() ) {

            echo '<div class="collect-nav pix-user-home-collect-nav">
                    <a href="javascript:;" class="col-post active pix-user-home-collect-tab">文章</a>
                    <a class="col-none pix-user-home-collect-tab">待添加</a>
                </div>';

            echo '<div class="pix-user-home-collect-grid">';

            while ($my_query->have_posts()) : $my_query->the_post();

            get_template_part( 'tpl/content','grid');

            endwhile; 

            echo '</div>';

            $total_pages = $my_query->max_num_pages;
            
                echo '<div class="pix-user-home-pagination-wrap">';
                echo ppo_htmx_pager([
                    'base_url'    => '/wp-json/ppo/v1/user-collect',
                    'user_id'     => $user_id,
                    'total_pages' => $total_pages,
                    'current'     => $page,
                    'target'      => '#user-content',
                    'query_args'  => [], // 可传入 tab=posts 等
                    'push_url'    => true,
                    'push_url_base' => get_author_posts_url($user_id), // 比如 /user/123
                    'query_args'  => ['tab' => 'collect'],
                    'class'       => 'pix-user-home-pagination',
                ]);
                echo '</div>';
            
         
        } else {
            echo '<div class="nodata pix-user-home-empty pix-user-home-empty-state"><img src="'.THEME_URL.'/img/empty.png" alt="暂无数据"></div>';
        }

        wp_reset_postdata();

       return ob_get_clean();
}
