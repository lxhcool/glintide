<?php
/**
 * Pix主题 - 优化函数
 * 
 * 主题性能优化、功能禁用等
 * 
 * @package pix
 * @author lxhcool
 * @version 1.0.4
 */

/*-----------------------------------------------------------------------------------*/
/* Disable the emoji's
/*-----------------------------------------------------------------------------------*/
function disable_emojis() {
	 remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	 remove_action( 'wp_print_styles', 'print_emoji_styles' );
	 remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	 remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
	 remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	 //add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
	}
	add_action( 'init', 'disable_emojis' );
	 
	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 * 
	 * @param    array  $plugins  
	 * @return   array             Difference betwen the two arrays
	 */
	function disable_emojis_tinymce( $plugins ) {
	 if ( is_array( $plugins ) ) {
	 return array_diff( $plugins, array( 'wpemoji' ) );
	 } else {
	 return array();
	 }
	}
	
/*-----------------------------------------------------------------------------------*/
/* 去除头部菜单
/*-----------------------------------------------------------------------------------*/
	add_filter( 'show_admin_bar', '__return_false' );
/*-----------------------------------------------------------------------------------*/
/* 去除头部冗余代码
/*-----------------------------------------------------------------------------------*/	
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'wp_generator' ); //隐藏wordpress版本
    remove_filter('the_content', 'wptexturize'); //取消标点符号转义
    
	remove_action('rest_api_init', 'wp_oembed_register_route');
	remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
	remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
	remove_filter('oembed_response_data', 'get_oembed_response_data_rich', 10, 4);
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');
	// Remove the Link header for the WP REST API
	// [link] => <http://cnzhx.net/wp-json/>; rel="https://api.w.org/"
	remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
	
/*-----------------------------------------------------------------------------------*/
/* 去除谷歌字体
/*-----------------------------------------------------------------------------------*/
	function coolwp_remove_open_sans_from_wp_core() {
		if ( ! is_admin() ) {
			wp_deregister_style( 'open-sans' );
			wp_register_style( 'open-sans', false );
			wp_enqueue_style('open-sans','');
		}
	}
	add_action( 'init', 'coolwp_remove_open_sans_from_wp_core' );
	
/*-----------------------------------------------------------------------------------*/
/* 修改后台字体
/*-----------------------------------------------------------------------------------*/
	/* function admin_lettering(){
    echo'<style type="text/css">
     *{ font-family: Microsoft YaHei;}
    </style>';
    }
	add_action('admin_head', 'admin_lettering'); */
	
/*-----------------------------------------------------------------------------------*/
/* Gravatar头像使用中国服务器
/*-----------------------------------------------------------------------------------*/	
if ( ! function_exists( 'get_cravatar_url' ) ) {
    /**
     * 替换 Gravatar 头像为 Cravatar 头像
     *
     * Cravatar 是 Gravatar 在中国的完美替代方案，你可以在 https://cravatar.cn 更新你的头像
     */
    function get_cravatar_url( $url ) {
        $sources = array(
            'www.gravatar.com',
            '0.gravatar.com',
            '1.gravatar.com',
            '2.gravatar.com',
            'secure.gravatar.com',
            'cn.gravatar.com',
            'gravatar.com',
        );
        return str_replace( $sources, 'cravatar.cn', $url );
    }
    add_filter( 'um_user_avatar_url_filter', 'get_cravatar_url', 1 );
    add_filter( 'bp_gravatar_url', 'get_cravatar_url', 1 );
    add_filter( 'get_avatar_url', 'get_cravatar_url', 1 );
}
if ( ! function_exists( 'set_defaults_for_cravatar' ) ) {
    /**
     * 替换 WordPress 讨论设置中的默认头像
     */
    function set_defaults_for_cravatar( $avatar_defaults ) {
        $avatar_defaults['gravatar_default'] = 'Cravatar 标志';
        return $avatar_defaults;
    }
    add_filter( 'avatar_defaults', 'set_defaults_for_cravatar', 1 );
}
if ( ! function_exists( 'set_user_profile_picture_for_cravatar' ) ) {
    /**
     * 替换个人资料卡中的头像上传地址
     */
    function set_user_profile_picture_for_cravatar() {
        return '<a href="https://cravatar.cn" target="_blank">您可以在 Cravatar 修改您的资料图片</a>';
    }
    add_filter( 'user_profile_picture_description', 'set_user_profile_picture_for_cravatar', 1 );
}
	
/*-----------------------------------------------------------------------------------*/
/* 阻止站内文章互相Pingback
/*-----------------------------------------------------------------------------------*/	
	function theme_noself_ping( &$links ) { 
	$home = get_option( 'home' );
	foreach ( $links as $l => $link )
	if ( 0 === strpos( $link, $home ) )
	unset($links[$l]); 
	}
	add_action('pre_ping','theme_noself_ping');
	
/*-----------------------------------------------------------------------------------*/
/* 删除后台某些版权和链接@wpdx
/*-----------------------------------------------------------------------------------*/		 
	add_filter('admin_title', 'wpdx_custom_admin_title', 10, 2);
	function wpdx_custom_admin_title($admin_title, $title){
		return $title.' &lsaquo; '.get_bloginfo('name');
	}
/*-----------------------------------------------------------------------------------*/
/* 去掉wordpress logo
/*-----------------------------------------------------------------------------------*/	
	function remove_logo($wp_toolbar) {
		$wp_toolbar->remove_node('wp-logo');
	}
	add_action('admin_bar_menu', 'remove_logo', 999);

/*-----------------------------------------------------------------------------------*/
/* 去掉wp版权
/*-----------------------------------------------------------------------------------*/	
	function change_footer_admin () {return '';}  
	add_filter('admin_footer_text', 'change_footer_admin', 9999);  
	function change_footer_version() {return '';}  
	add_filter( 'update_footer', 'change_footer_version', 9999);

/*-----------------------------------------------------------------------------------*/
/* 去掉挂件
/*-----------------------------------------------------------------------------------*/	
	function disable_dashboard_widgets() {   
		//remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');//近期评论 
		//remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal');//近期草稿
		remove_meta_box('dashboard_primary', 'dashboard', 'core');//wordpress博客  
		remove_meta_box('dashboard_secondary', 'dashboard', 'core');//wordpress其它新闻  
		remove_meta_box('dashboard_right_now', 'dashboard', 'core');//wordpress概况  
		//remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');//wordresss链入链接  
		//remove_meta_box('dashboard_plugins', 'dashboard', 'core');//wordpress链入插件  
		//remove_meta_box('dashboard_quick_press', 'dashboard', 'core');//wordpress快速发布   
	}  
	add_action('admin_menu', 'disable_dashboard_widgets');
	
/*-----------------------------------------------------------------------------------*/
/* 去掉无用小工具
/*-----------------------------------------------------------------------------------*/	
	function wpdocs_remove_calendar_widget() {
		unregister_widget( 'WP_Widget_Pages' );
		unregister_widget( 'WP_Widget_Calendar' );
		unregister_widget( 'WP_Widget_Meta' );
		unregister_widget( 'WP_Widget_Search' );
		unregister_widget( 'WP_Widget_Categories' );
		unregister_widget( 'WP_Widget_Recent_Posts' );
		unregister_widget( 'WP_Widget_Recent_Comments' );
		unregister_widget( 'WP_Widget_RSS' );
	}
add_action( 'widgets_init', 'wpdocs_remove_calendar_widget' );

add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );

//禁止自动裁剪

add_filter('big_image_size_threshold', '__return_false');
 
//禁用其他尺寸
 
function shapeSpace_disable_medium_large_images($sizes) {
 
unset($sizes['medium_large']); // disable 768px size images
 
unset($sizes['1536x1536']);    // disable 2x medium-large size 
unset($sizes['2048x2048']);    // disable 2x large size return $sizes;
 
return $sizes;
 
}
add_filter('intermediate_image_sizes_advanced', 'shapeSpace_disable_medium_large_images');

// 禁用 WordPress 默认注册页面（当主题设置关闭注册时）
add_action('login_init', function () {
    if (isset($_GET['action']) && $_GET['action'] === 'register') {
        if (!get_op('reg_open', true)) {
            wp_redirect(wp_login_url());
            exit;
        }
    }
});

// 禁止非管理员通过 wp-admin 添加用户
add_action('admin_init', function () {
    if (!current_user_can('manage_options') && !get_op('reg_open', true)) {
        global $pagenow;
        if ($pagenow === 'user-new.php') {
            wp_redirect(admin_url());
            exit;
        }
    }
});