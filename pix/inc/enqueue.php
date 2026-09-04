<?php
/**
 * Enqueue scripts and styles.
 */
function pix_scripts() {
	wp_enqueue_style( 'pix-style', get_stylesheet_uri(), array(), _S_VERSION );
    wp_enqueue_style( 'fancybox.css', THEME_URL . '/inc/assets/css/jquery.fancybox.min.css', array(), _S_VERSION );
	wp_enqueue_style( 'nprogress.css', THEME_URL . '/inc/assets/css/nprogress.css', array(), _S_VERSION );
	wp_enqueue_style( 'iconfont', THEME_URL . '/inc/assets/fonts/remixicon.css', array(), _S_VERSION );
	wp_enqueue_style( 'uikit.css', THEME_URL . '/inc/assets/css/uikit.min.css', array(), _S_VERSION );
	wp_enqueue_style( 'highlight.css', THEME_URL . '/inc/assets/css/highlight.css', array(), _S_VERSION );
    wp_enqueue_style( 'main.css', THEME_URL . '/inc/assets/css/main.css', array(), _S_VERSION );
	theme_color();
	wp_enqueue_style( 'dark', THEME_URL . '/inc/assets/css/dark.css', array(), _S_VERSION );
	wp_enqueue_style( 'mobile', THEME_URL . '/inc/assets/css/mobile.css', array(), _S_VERSION );
	

	wp_enqueue_script( 'jquery.min', THEME_URL . '/inc/assets/js/jquery.min.js', array(), _S_VERSION, true );//加载JQ库
	wp_enqueue_script( 'uikit.js', THEME_URL . '/inc/assets/js/uikit.min.js', array(), _S_VERSION, true ); 
	wp_enqueue_script( 'jquery.cookie', THEME_URL . '/inc/assets/js/jquery.cookie.js', array(), _S_VERSION, true ); 
	wp_enqueue_script( 'fancybox.js', THEME_URL . '/inc/assets/js/jquery.fancybox.min.js', array(), _S_VERSION, true ); 
	wp_enqueue_script( 'highlight.js', THEME_URL . '/inc/assets/js/highlight.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'jquery.form', THEME_URL . '/inc/assets/js/jquery.form.js', array(), _S_VERSION, true ); 
	wp_enqueue_script( 'jquery.validate', THEME_URL . '/inc/assets/js/jquery.validate.js', array(), _S_VERSION, true ); 
	wp_enqueue_script( 'mesage', THEME_URL . '/inc/assets/js/coco-message.js', array(), _S_VERSION, true ); 
	wp_enqueue_script( 'lazyload', THEME_URL . '/inc/assets/js/lazyload.min.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'moment-push', THEME_URL . '/inc/assets/js/moment-push.js', array(), _S_VERSION, true );	
	wp_enqueue_script( 'nprogress', THEME_URL . '/inc/assets/js/nprogress.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'poster', THEME_URL . '/inc/assets/js/poster.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'pjax', THEME_URL . '/inc/assets/js/jquery.pjax.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'pixplayer.js', THEME_URL . '/inc/assets/js/pixplayer.js', array(), _S_VERSION, true );
    wp_enqueue_script( 'app', THEME_URL . '/inc/assets/js/app.js', array(), _S_VERSION, true ); 

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		//wp_enqueue_script( 'comment-reply' );
	}
    //禁止加载自带jq库
	wp_deregister_script('jquery');

	?>

	<script type="text/javascript">
		var Theme = <?php echo cst_script_parameter(); ?>;
	</script>

	<?php
}
add_action( 'wp_enqueue_scripts', 'pix_scripts' );

/* JS parameters */
function cst_script_parameter(){
	global $wp_query;
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$object = array();
	//global
	$object['ajaxurl'] = admin_url( 'admin-ajax.php' );
	$object['admin_url'] = admin_url();
	$object['wp_url'] = get_bloginfo('url');
	$object['cst_url'] = THEME_URL;
	$object['home_url'] = home_url();
	$object['is_single'] = is_paged();
	$object['single'] = is_single();

	$object['redirecturl'] = cst_get_curl();
	$object['paged']	= get_query_var('paged')?(int)get_query_var('paged'):1; 
	$object['cpage']	= get_query_var('cpage')?(int)get_query_var('cpage'):1; 
	$object['is_admin'] = current_user_can('edit_users')?1:0;
	$object['posts'] = json_encode( $wp_query->query_vars ); //update 5.18
	$object['current_page'] = $wp_query->query_vars['paged'] ? $wp_query->query_vars['paged'] : 1; //update 5.18
	$object['max_page'] = $wp_query->max_num_pages; //update 5.18
	$object['posts_per_page'] = get_option('posts_per_page');

	//bgm
	$object['bgm_open'] = get_op('bgm_open') ? true : false;
	//pjax
	$object['pjax'] = get_op('site_pjax') ? true : false;
	$object['min_push'] = get_op('min_push_num') ? get_op('min_push_num') : '6';
	//login
	$object['loadingmessage'] = '正在请求中，请稍等...';
	//comments
	$object['order'] = get_option('comment_order');
	$object['formpostion'] = 'top';
	//user
	$object['user_name'] = $current_user->display_name;	
	$object['uid'] = (int)get_current_user_id();
	//$object['charge_min'] = (int)get_cst('charge_min');
	//$object['avatar'] =	cst_get_avatar($user_id , '100' , cst_avatar_type($user_id));
	if(is_single()){
		global $post;
		$object['pid'] = $post->ID;
		$object['post_type'] = $post->type;
	}
	$object_json = json_encode($object);
	return $object_json;
}

function admin_mycss() {
    wp_enqueue_style( 'admin.css', THEME_URL . '/inc/assets/css/admin.css', array(), '' );
}
add_action('admin_init', 'admin_mycss');


//加载文件，勿动
require THEME_DIR . '/inc/base.php';  //加载基础函数化
require THEME_DIR . '/inc/opt.php';  //加载优化函数
require THEME_DIR . '/inc/pix-fn.php';  //加载通用函数
require THEME_DIR . '/inc/pix-comment.php';  //加载评论函数
require THEME_DIR . '/inc/pix-post.php';  //加载文章函数
require THEME_DIR . '/inc/pix-moment.php';  //加载片刻函数
require THEME_DIR . '/inc/pix-mail.php';  //加载邮件函数
require THEME_DIR . '/inc/pix-widget.php';  //加载小工具函数
require THEME_DIR . '/inc/pix-login.php';  //加载登录函数
require THEME_DIR . '/inc/pix-seo.php';  //加载seo函数
require THEME_DIR . '/inc/pix-icon.php';  //remixicon
require THEME_DIR . '/inc/pix-music.php';  //音乐函数
require THEME_DIR . '/inc/pix-msg.php';  //加载回复数据
require THEME_DIR . '/inc/pix-type.php';  //片刻类型
require THEME_DIR . '/inc/pix-walkernav.php';  //walkernav
require THEME_DIR . '/inc/pix-rewrite.php';  //重写规则
require THEME_DIR . '/inc/pix-poster.php';  //海报
require THEME_DIR . '/inc/lib/aq_resizer.php';  //媒体库文件裁剪
require THEME_DIR . '/inc/lib/music-api.php';  //pix音乐api

