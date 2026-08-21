<?php

//加载聚合登录SDK
require THEME_DIR . '/inc/mod/pix-juhe.php';

//获取社交登录按钮
function get_open_loginbtn()
 {
     $html = '';
     if (get_op('open_qq') || get_op('open_weixin') || get_op('open_weibo')) {
         $rurl = home_url(add_query_arg(array()));
         $oauthArr = array('qq','weixin','weibo');
         
             foreach ($oauthArr as $value) {
                 if (get_op('open_'.$value.'')) {    
                       
                    $html .= '<a href="'.esc_url(home_url('/login?type='.$value.'&rurl='.$rurl.'&mod=plogin')).'" class="btn btn-'.$value.'"><img src="'.THEME_URL.'/img/open/'.oauth2juhe($value).'.png"></a>';                    
                 }
             }  

         return $html;
     }
 }

 // 聚合登录按钮
 function juhe_open_loginbtn(){
    $html = '';
    $rurl = home_url(add_query_arg(array()));
    $juhe_data = get_op('open_juhe_data');
    $juheArr = isset($juhe_data['juhe_type']) ? $juhe_data['juhe_type'] : array();

    if (is_array($juheArr)) {
        foreach($juheArr as $value){
            $html .= '<a href="'.esc_url(home_url('/login?type='.$value.'&rurl='.$rurl.'&mod=clogin')).'" class="btn btn-'.$value.'"><img src="'.THEME_URL.'/img/open/'.$value.'.png"></a>'; 
        }
    }

    return $html;
 }

 // 获取所有社交登录按钮
 function get_oaouth_btn(){
    $open_btns = get_open_loginbtn();
    $juhe_btns = juhe_open_loginbtn();

    if ( empty( $open_btns ) && empty( $juhe_btns ) ) {
        return '';
    }

    $html = '<div class="open-login-box">';
    $html .= '<div class="open-oauth-title"><span>或以下方式登录</span></div>';
    $html .= '<div class="open_oauth">';
    $html .= $open_btns;
    $html .= $juhe_btns;
    $html .= '</div>';
    $html .= '</div>';

    return $html;
 }

 // 绑定列表
function oauth_bind_list($user_id){
    $rurl = home_url(add_query_arg(array()));
    $coauth = '';
    $poauth = '';
    // 官方
    $oauthArr = array('qq','weixin','weibo');
    foreach ($oauthArr as $value) {
        if (get_op('open_'.$value.'')) {
           //$value = oauth2juhe($value);
           $bind = oauth_bind_info($value,'bind',$user_id);   
           $title = oauth_bind_info($value,'title',$user_id);    
           $class = oauth_bind_info($value,'class',$user_id); 
           $b = oauth_bind_info($value,'type',$user_id); 
           
           $name = $value == 'qq' ? 'QQ(官方)' : oauth_name($value);

           $url = $b ? '#' : esc_url(home_url('/login?type='.$value.'&rurl='.$rurl.'&mod=plogin'));
           $icon_url = esc_url(THEME_URL.'/img/open/'.oauth2juhe($value).'.png');
           $poauth .= '<div class="oauth-bind-item pix-dashboard-edit-oauth-item">
                        <div class="left"><div class="lo pix-dashboard-edit-oauth-logo"><img src="'.$icon_url.'" alt="'.esc_attr($name).'"></div><div class="info pix-dashboard-edit-oauth-info"><h4>'.esc_html($name).'</h4><span>'.esc_html($bind).'</span></div></div>
                        <a href="'.$url.'" class="btn btn-'.esc_attr($value).' '.esc_attr($class).' pix-dashboard-edit-oauth-action" type="'.esc_attr($value).'">'.esc_html($title).'</a>
                      </div>';                    
        }
    } 

    // 聚合登录
    $juhe_data = get_op('open_juhe_data');
    $juheArr = isset($juhe_data['juhe_type']) ? $juhe_data['juhe_type'] : array();
    if (is_array($juheArr)) {
        foreach($juheArr as $value){
            $bind = oauth_bind_info($value,'bind',$user_id);   
            $title = oauth_bind_info($value,'title',$user_id);    
            $class = oauth_bind_info($value,'class',$user_id); 
            $b = oauth_bind_info($value,'type',$user_id);

            $url = $b ? '#' : esc_url(home_url('/login?type='.$value.'&rurl='.$rurl.'&mod=clogin'));
            
            $oauth_name = oauth_name($value);
            $icon_url = esc_url(THEME_URL.'/img/open/'.$value.'.png');
            $coauth .= '<div class="oauth-bind-item pix-dashboard-edit-oauth-item">
                            <div class="left"><div class="lo pix-dashboard-edit-oauth-logo"><img src="'.$icon_url.'" alt="'.esc_attr($oauth_name).'"></div><div class="info pix-dashboard-edit-oauth-info"><h4>'.esc_html($oauth_name).'</h4><span>'.esc_html($bind).'</span></div></div>
                            <a href="'.$url.'" class="btn btn-'.esc_attr($value).' '.esc_attr($class).' pix-dashboard-edit-oauth-action" type="'.esc_attr($value).'">'.esc_html($title).'</a>
                          </div>';  
        }
    }

    return $poauth.$coauth;
}

// 绑定按钮参数
function oauth_bind_info($value,$info,$user_id){
    $poauth = array('qq','weixin','weibo');
    if(in_array($value,$poauth)){
        $value = oauth2juhe($value);
    }
    $info_data = array(
        'bind' => '未绑定',
        'title' => '绑定',
        'class' => 'bind-btn',
        'type' => 0,
    );

    $bind = get_user_meta($user_id,'open_'.$value.'_openid');
    if($bind){
        $info_data['bind'] = '已绑定';
        $info_data['title'] = '解绑';
        $info_data['class'] = 'unbind-btn';
        $info_data['type'] = 1;
    }

    return $info_data[$info];
}

// 社交登录中文名
function oauth_name($type){
    $arr = array(
        'qq'  => 'QQ',
        'wx'  => '微信',
        'weixin'  => '微信(官方)',
        'weibo'  => '微博(官方)',
        'alipay'  => '支付宝',
        'sina'  => '微博',
        'baidu'  => '百度',
        'xiaomi'  => '小米',
        'microsoft'  => '微软',
        'facebook'  => 'Facebook',
        'dingtalk'  => '钉钉',
        'gitee'  => 'Gitee',
        'github'  => 'GitHub',
        'google'  => '谷歌',
        'huawei' => '华为',
    );

    return $arr[$type];

}


// 转换名称
function oauth2juhe($type){
    $arr = array(
        'weixin' => 'wx',
        'qq' => 'qq',
        'weibo' => 'sina',
    );

    return $arr[$type];
}


 // 社交登录回调
function open_oauth_callback($type){
    //session_start();
    $Mod = strtoupper($type);

    if (empty($_SESSION['YURUN_'.$Mod.'_STATE'])) {
        wp_die('非法访问，没有经过第三方登录返回');
    }

    $callback = esc_url(home_url('/open/?type='.$type));
    $config = get_op('open_'.$type.'_data');

    switch ($type)
    {
    case "qq":
        $OAuth  = new \Yurun\OAuthLogin\QQ\OAuth2($config['appid'], $config['appkey'], $callback);
        break;
    case "weibo":
        $OAuth  = new \Yurun\OAuthLogin\Weibo\OAuth2($config['appid'], $config['appkey'], $callback);
        break;
    case "weixin":
        $OAuth  = new \Yurun\OAuthLogin\Weixin\OAuth2($config['appid'], $config['appkey'], $callback);
        break;
    }

    $accessToken = $OAuth->getAccessToken($_SESSION['YURUN_'.$Mod.'_STATE']);

    $openid   = $OAuth->openid; // 唯一ID
    $userInfo = $OAuth->getUserInfo(); //第三方用户信息

    //print_r($userInfo);

    $open_data = array(
        'type' => oauth2juhe($type),
        'openid' => $openid,
        'name'   => isset($userInfo['screen_name']) ? $userInfo['screen_name'] : '',
        'avatar' => isset($userInfo['avatar_large']) ? $userInfo['avatar_large'] : '',
        'description' => '',
        'userinfo' => $userInfo,
    );

    if ($openid && $userInfo) {
        return open_oauth_update($open_data);
    }
}

// 聚合登录回调
function juhe_oauth_callback($data){
    //session_start();

    if (empty($_SESSION['Oauth_state'])) {
        wp_safe_redirect(home_url());
        exit;
    }

    $juhe_data = get_op('open_juhe_data');
    $Oauth_config = array(
        'apiurl' => $juhe_data['juhe_url'],
        'appid' => $juhe_data['appid'],
        'appkey' => $juhe_data['appkey'],
        'callback' => esc_url(home_url('/open')),
    );

    if($_GET['code']){
        if($_GET['state'] != $_SESSION['Oauth_state']){
            wp_die("状态码错误，请重新尝试");
        }
        $Oauth = new Oauth($Oauth_config);
        $arr = $Oauth->callback();
        
        if(isset($arr['code']) && $arr['code']==0){

            $openid = $arr['social_uid'];
            $access_token = $arr['access_token'];
            /* 处理用户登录逻辑 */
            $open_data = array(
                'type' => $arr['type'],
                'openid' => $openid,
                'name'   => isset($arr['nickname']) ? $arr['nickname'] : '',
                'avatar' => isset($arr['faceimg']) ? $arr['faceimg'] : '',
                'description' => '',
                'userinfo' => $arr,
            );
            
            if ($openid && $arr) {
                return open_oauth_update($open_data);
            }
    
        } elseif (isset($arr['code'])) {

            wp_die('登录失败，返回错误原因：'.$arr['msg']);

        } else {

            wp_die('获取登录数据失败');
        }
    }
}

// 判断用户是否设置了自定义密码
// 普通注册用户默认知道密码（返回true），社交登录用户需要检查标记
function ppo_user_has_custom_password($user_id) {
    $password_set = get_user_meta($user_id, 'password_set_by_user', true);

    return $password_set !== '0';
}

function ppo_user_can_set_password_without_old($user_id) {
    return get_user_meta($user_id, 'password_set_by_user', true) === '0';
}

// 社交登录处理
function open_oauth_update($data){
    $defaults = array(
        'type'        => '',
        'openid'      => '',
        'name'        => '',
        'avatar'      => '',
        'description' => '',
        'userinfo' => array(),
    );

    $args = wp_parse_args((array) $data, $defaults);

    $_prefix          = $data['type'];
    $_openid_meta_key = 'open_' . $_prefix . '_openid';

    $return_data = array(
        'return_uri' => '',
        'msg' => '',
        'error' => true,
    );

    global $wpdb, $current_user;

    // 查询该openid是否已存在
    $user_exist = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s", $_openid_meta_key, $args['openid']));

    // 查询已登录用户
    $current_user_id = get_current_user_id();

    // 如果已经登录，并且该openid已经被占用
    if ($current_user_id && isset($user_exist) && $current_user_id != $user_exist) {
        $return_data['msg'] = '绑定失败，可能之前已有其他账号绑定，请先登录其他账户解绑';
        return $return_data;
    }

    // 如果已经绑定且未登录，那么直接登录
    if (isset($user_exist) && (int) $user_exist > 0) {
        $user_exist = (int) $user_exist;

        //登录
        $user = get_user_by('id', $user_exist);
        wp_set_current_user($user_exist);
        wp_set_auth_cookie($user_exist, true);
        do_action('wp_login', $user->user_login, $user);

        $return_data['return_uri'] = $_SESSION['oauth_rurl']; 
        $return_data['error']        = false;
        return $return_data;
    }

    //用户中心绑定
    if ($current_user_id) {
        // 已经登录，但openid未占用，则绑定，更新用户字段
        // 更新用户meta
        $args['user_id'] = $current_user_id;

        //绑定用户不更新以下数据
        $args['name']        = '';
        $args['description'] = '';

        // 更新用户信息
        ppo_oauth_update_u_meta($args);

        // 准备返回数据
        $return_data['return_uri'] = $_SESSION['oauth_rurl']; //重定向链接到用户中心
        $return_data['error']        = false;
        return $return_data;
    }

    // 该开放平台账号未连接过WP系统，使用它登录并分配和绑定一个WP本地新用户
    $login_name = "user" . mt_rand(1000, 9999) . mt_rand(1000, 9999);
    $user_pass  = wp_create_nonce(rand(10, 1000));
    $nickname   = trim($args['name']);
    $userdata   = array(
        'user_login'   => $login_name,
        //'user_email'   => $login_name.'_mail@no.com',
        'display_name' => $nickname,
        'nickname'     => $nickname,
        'user_pass'    => $user_pass,
        'role'         => get_option('default_role'),
        'first_name'   => $nickname,
    );

    $user_id = wp_insert_user($userdata);

    if (is_wp_error($user_id)){
        $return_data['msg'] = $user_id->get_error_message();
    } else {

        // 新建用户成功，更新用户数据
        $args['user_id']    = $user_id;
        $args['login_name'] = $login_name;
        ppo_oauth_update_u_meta($args);

        // 标记用户尚未设置过自定义密码（社交登录创建的随机密码）
        update_user_meta($user_id, 'ppo_social_login_created', '1');
        update_user_meta($user_id, 'ppo_social_login_provider', $args['type']);
        update_user_meta($user_id, 'password_set_by_user', '0');

        if (function_exists('ppo_send_contact_completion_notice')) {
            ppo_send_contact_completion_notice($user_id);
        }

        $user = get_user_by('id', $user_id);
        wp_set_current_user($user_id, $user->user_login);
        wp_set_auth_cookie($user_id, true);
        do_action('wp_login', $user->user_login, $user);

        // 装备返回数据
        $return_data['return_uri'] = $_SESSION['oauth_rurl']; //重定向链接到用户中心
        $return_data['error']        = false;      
    }

    return $return_data; 
}

// 聚合登录
function ppo_juhe_login($type){
    $_SESSION['oauth_mod'] = 'clogin';

    $juhe_data = get_op('open_juhe_data');
    $type_arr = $juhe_data['juhe_type'];
    $callback = esc_url(home_url('/open'));
    $Oauth_config = array(
        'apiurl' => $juhe_data['juhe_url'],
        'appid' => $juhe_data['appid'],
        'appkey' => $juhe_data['appkey'],
        'callback' => $callback,
    );

    if(is_array($type_arr) && in_array($type, $type_arr)){
        $Oauth = new Oauth($Oauth_config);
	    $arr = $Oauth->login($type);
        if(isset($arr['code']) && $arr['code']==0){
            header('location:' . $arr['url']);
            exit;
            //exit("<script language='javascript'>window.location.href='{$arr['url']}';</script>");
        }elseif(isset($arr['code'])){
            wp_die('登录接口返回：'.$arr['msg']);
        }else{
            wp_die('获取登录地址失败');
        }
    }
}

// 更新用户数据
function ppo_oauth_update_u_meta($data){
    $defaults = array(
        'user_id'     => '',
        'type'        => '',
        'openid'      => '',
        'name'        => '',
        'login_name'  => '',
        'avatar'      => '',
        'description' => '',
        'userinfo' => array(),
    );

    $args = wp_parse_args((array) $data, $defaults);

    $userinfo = $args['userinfo'];
    unset($args['userinfo']);
    $userinfo['name'] = $args['name'];
    $userinfo['avatar'] = $args['avatar'];
    //$userinfo['bind'] = 1;

    update_user_meta($args['user_id'],'open_'.$args['type'].'_openid',$args['openid']); // 更新社交id
    update_user_meta($args['user_id'],'open_'.$args['type'].'_userinfo',$userinfo); // 更新社交资料

    // 更新头像
    $custom_avatar = get_user_meta($args['user_id'], 'custom_avatar', true);
    if ($args['avatar'] && !$custom_avatar) {
        update_user_meta($args['user_id'], 'custom_avatar', $args['avatar']);
    }

    // 更新简介
    $description = get_user_meta($args['user_id'], 'description', true);
    if ($args['description'] && !$description) {
        update_user_meta($args['user_id'], 'description', $args['description']);
    }

}

// 解绑社交登录
function unbind_oauth(){
    if (!is_user_logged_in()) {
        wp_send_json(array('code'=>0,'msg'=>'请先登录'));
    }

    check_ajax_referer('ppo_oauth_action', 'nonce');

    $current_user_id = get_current_user_id();
    $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    $allowed_types = array('qq', 'weixin', 'weibo', 'github', 'gitee', 'google', 'facebook', 'baidu', 'alipay', 'dingtalk', 'huawei', 'microsoft', 'sina', 'wx', 'xiaomi');

    if ($user_id !== $current_user_id && !current_user_can('edit_users')) {
        wp_send_json(array('code'=>0,'msg'=>'没有权限'));
    }

    if (!in_array($type, $allowed_types, true)) {
        wp_send_json(array('code'=>0,'msg'=>'社交方式不存在'));
    }
    
    $openid = delete_user_meta($user_id, 'open_'.$type.'_openid');
    $userinfo = delete_user_meta($user_id, 'open_'.$type.'_userinfo');

    // 更新头像
    
    if(!$openid || !$userinfo){
        $msg = array('code'=>0,'msg'=>'解绑出现问题，请重试');
    } else {
        $msg = array('code'=>1,'msg'=>'解绑成功');
    }
    wp_send_json($msg);

}
add_action( 'wp_ajax_unbind_oauth', 'unbind_oauth' );


