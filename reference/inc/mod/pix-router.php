<?php 
// 模板路由

// 添加查询变量
add_filter('query_vars', 'users_query_vars');
function users_query_vars($vars) {
    // add lid to the valid list of variables
    $vars[] = 'user';
    $vars[] = 'ppo_user_page';
    $vars[] = 'ppo_admin_page';
    $vars[] = 'msg_action';
    
    return $vars;
}

//全局查询变量值
function ppo_init_globals() {
    global $ppo_page_type;
    $ppo_page_type = apply_filters( 'ppo_page_type',
    array('dashboard','open','login','allmoments','moment-edit','moment-manage','reply','system','whisper','like','msg','resetpwd'));
    //注册page类型标签
    add_rewrite_tag('%ppo_page_type%','([^&]+)');
}
add_action( 'init','ppo_init_globals',10,0 );

//注册路由规则 
function ppo_rewrite_rules( $wp_rewrite ) {
    global $ppo_page_type;
    $new_rules = array();
    $new_rules['dashboard/?$'] = 'index.php?ppo_page_type=dashboard';
    $new_rules['dashboard/([^/]+)/?$'] = 'index.php?ppo_page_type=dashboard&ppo_admin_page=$matches[1]';
    $new_rules['user/([0-9]+)/([^/]+)/?$'] = 'index.php?author=$matches[1]&ppo_user_page=$matches[2]';
    $new_rules['user/([0-9]+)/?$'] = 'index.php?author=$matches[1]';
    $new_rules['msg/([a-z]+)/?$'] = 'index.php?ppo_page_type=msg&msg_action=$matches[1]';
    foreach ($ppo_page_type as $page) {
        $new_rules[$page] = 'index.php?ppo_page_type='.$page;
    }
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    return $wp_rewrite->rules;
}
add_filter('generate_rewrite_rules','ppo_rewrite_rules');

// 自动加载模板
function ppo_auto_redirects() {
    $type = strtolower(get_query_var('ppo_page_type'));
    if ($type) {
        get_template_part( 'inc/layouts/redirect',$type);
        exit;
    }
}
add_filter( 'template_redirect', 'ppo_auto_redirects' );

add_filter('redirect_canonical', function($redirect_url, $requested_url){
    if (preg_match('#/msg/|/dashboard/#', $requested_url)) {
        return false;
    }
    return $redirect_url;
}, 10, 2);

