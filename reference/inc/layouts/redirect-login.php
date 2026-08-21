<?php
session_start();
$type = isset($_GET['type']) ? sanitize_key($_GET['type']) : false;
$mod = isset($_GET['mod']) ? sanitize_key($_GET['mod']) : false;
$allowed_mods = array('plogin', 'clogin');
$official_types = array('qq', 'weibo', 'weixin');

$callback = esc_url(home_url('/open/?type='.$type));
if($type){
    if (!in_array($mod, $allowed_mods, true)) {
        wp_die('社交登录方式不存在，请重新尝试');
    }

    $_SESSION['oauth_rurl'] = !empty($_REQUEST['rurl']) ? wp_validate_redirect(wp_unslash($_REQUEST['rurl']), home_url()) : home_url();
    $config = get_op('open_'.$type.'_data');

    // 使用聚合登录
    if($mod == 'clogin'){
        ppo_juhe_login($type);
    }

    if (!in_array($type, $official_types, true)) {
        wp_die('社交登录方式不存在，请重新尝试');
    }

    // 使用官方登录
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


    $url = $OAuth->getAuthUrl();
    $_SESSION['YURUN_'.strtoupper($type).'_STATE'] = $OAuth->state;
    $_SESSION['oauth_mod'] = 'plogin';
    header('location:' . $url);
}
