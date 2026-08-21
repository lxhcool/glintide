<?php
session_start();
$type = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';
$mod = isset($_SESSION['oauth_mod']) ? sanitize_key($_SESSION['oauth_mod']) : '';

if(empty($mod)){
    wp_die('社交方式不存在，请重新尝试');
}

if($mod == 'clogin'){
    $oauth_result = juhe_oauth_callback($_GET);
} else if($mod == 'plogin') {
    $oauth_result = open_oauth_callback($type);
} else {
    wp_die('社交方式不存在，请重新尝试');
}

 
if($oauth_result['error']) {
    wp_die($oauth_result['msg']);
} else {
    $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : $oauth_result['return_uri'];
    $rurl = wp_validate_redirect($rurl, home_url());
    wp_safe_redirect($rurl);
    exit;
} 

wp_safe_redirect(home_url());
exit; 
