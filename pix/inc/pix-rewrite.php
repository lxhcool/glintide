<?php
//自定义查询变量值
function pix_init_globals() {
    global $pix_page_type;
    $pix_page_type = apply_filters( 'pix_page_type',array('musicapi'));
    //注册page类型标签
    add_rewrite_tag('%pix_page_type%','([^&]+)');
	//add_rewrite_tag('%manage_page%','([^&]+)');
}
add_action( 'init','pix_init_globals',10,0 );

//注册路由规则 暂无 保留备用
function pix_rewrite_rules( $wp_rewrite ) {
    global $pix_page_type;
    $new_rules = array();
	//$new_rules['manage/([^&]+)/?'] = 'index.php?pix_page_type=manage&manage_page=$matches[1]';
    foreach ($pix_page_type as $page) {
        $new_rules[$page] = 'index.php?pix_page_type='.$page;
    }
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    return $wp_rewrite;
}
add_filter('generate_rewrite_rules','pix_rewrite_rules');


//加载自定义模板
function pix_template_redirects() {
    $type = get_query_var('pix_page_type');
    if ($type) {
        get_template_part( 'inc/action/rewrite',$type);
        exit;
    }
}
add_filter( 'template_redirect', 'pix_template_redirects' );

/*获取自定义页面的网址*/
function pix_get_custom_page_link($name){
    if(get_option('permalink_structure')){
     return esc_url(home_url('/'.$name));
    }else{
     return esc_url(home_url('?pix_page_type='.$name));
    }
}