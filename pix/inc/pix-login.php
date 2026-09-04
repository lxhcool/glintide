<?php
function ajax_auth_init(){	

    // 允许未登录用户使用ajax
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
}

if (!is_user_logged_in()) {
    add_action('init', 'ajax_auth_init');
}

function ajax_login(){

    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-login-nonce', 'security' );

	auth_user_login($_POST['username'], $_POST['password'], ''); 

    die();
}

function auth_user_login($user_login, $password, $login)
{
	$info = array();
    $info['user_login'] = $user_login;
    $info['user_password'] = $password;
    $info['remember'] = true;
	
	$user_signon = wp_signon( $info, '' );
    if ( is_wp_error($user_signon) ){
		echo json_encode(array('loggedin'=>false, 'message'=>__('用户名或密码错误.')));
    } else {
		wp_set_current_user($user_signon->ID); 
        echo json_encode(array('loggedin'=>true, 'message'=>__(' 登录成功, 正在提交...')));
    }
	
	exit();
}