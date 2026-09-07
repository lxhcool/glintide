<?php
/**
 * Glintide functions and definitions
 *
 * @package glintide
 */

if ( ! defined( 'GLINTIDE_VERSION' ) ) {
	define( 'GLINTIDE_VERSION', '1.0.0' );
}

define( 'GLINTIDE_DIR', get_template_directory() );
define( 'GLINTIDE_URL', get_template_directory_uri() );

// 兼容 Codestar Framework 的常量引用
define( 'THEME_DIR', GLINTIDE_DIR );
define( 'THEME_URL', GLINTIDE_URL );
if ( ! defined( 'PIX_VERSION' ) ) {
	define( 'PIX_VERSION', GLINTIDE_VERSION );
}

/**
 * 本地开发时禁止前台页面缓存,确保主题资源和模板改动立即生效。
 */
function glintide_local_no_cache_headers() {
	if ( is_admin() ) {
		return;
	}

	$environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : '';
	if ( 'local' === $environment ) {
		nocache_headers();
	}
}
add_action( 'send_headers', 'glintide_local_no_cache_headers', 1 );

/**
 * 提前建立第三方视频播放器连接,减少嵌入播放器首次打开的等待时间。
 *
 * @param array  $urls          资源提示 URL。
 * @param string $relation_type 资源提示类型。
 * @return array
 */
function glintide_video_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' !== $relation_type ) {
		return $urls;
	}

	$urls[] = 'https://player.bilibili.com';
	$urls[] = 'https://www.youtube.com';

	return array_values( array_unique( $urls ) );
}
add_filter( 'wp_resource_hints', 'glintide_video_resource_hints', 10, 2 );

/**
 * 主题基础设置
 */
function glintide_setup() {
	// 标题标签
	add_theme_support( 'title-tag' );

	// 特色图
	add_theme_support( 'post-thumbnails' );

	// HTML5 支持
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// 自定义 Logo
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 72,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// 注册菜单
	register_nav_menus(
		array(
			'top' => esc_html__( '顶部主导航', 'glintide' ),
		)
	);
}
add_action( 'after_setup_theme', 'glintide_setup' );

/**
 * 注册小工具区
 */
function glintide_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( '左栏小工具', 'glintide' ),
			'id'            => 'sidebar-left',
			'description'   => esc_html__( '三栏布局左侧栏小工具区域', 'glintide' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '右栏小工具', 'glintide' ),
			'id'            => 'sidebar-right',
			'description'   => esc_html__( '三栏布局右侧栏小工具区域', 'glintide' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'glintide_widgets_init' );

/**
 * 加载样式与脚本
 */
function glintide_scripts() {
	// 照片预览组件 lightGallery(自带缩略图条/缩放/手势,GPLv3)
	wp_enqueue_style(
		'glintide-lightgallery-style',
		GLINTIDE_URL . '/assets/css/lightgallery-bundle.min.css',
		array(),
		filemtime( GLINTIDE_DIR . '/assets/css/lightgallery-bundle.min.css' )
	);

	// 主样式
	$glintide_style_file    = GLINTIDE_DIR . '/style.css';
	$glintide_style_version = filemtime( $glintide_style_file ) . '-' . substr( md5_file( $glintide_style_file ), 0, 12 );
	wp_enqueue_style( 'glintide-style', get_stylesheet_uri(), array( 'glintide-lightgallery-style' ), $glintide_style_version );

	// 内容卡片交互(点赞、无限加载所需的 AJAX 配置)
	wp_enqueue_script(
		'glintide-photo-swiper',
		GLINTIDE_URL . '/assets/js/glintide-photo-swiper.js',
		array( 'jquery' ),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-photo-swiper.js' ),
		true
	);
	wp_localize_script(
		'glintide-photo-swiper',
		'glintide_card_ajax',
		array(
			'url'        => admin_url( 'admin-ajax.php' ),
			'lgUrl'      => GLINTIDE_URL . '/assets/js/lightgallery.min.js',
			'lgThumbUrl' => GLINTIDE_URL . '/assets/js/lg-thumbnail.min.js',
			'lgZoomUrl'  => GLINTIDE_URL . '/assets/js/lg-zoom.min.js',
		)
	);
	wp_enqueue_style(
		'glintide-music-backup',
		GLINTIDE_URL . '/assets/css/glintide-music-backup.css',
		array( 'glintide-style' ),
		filemtime( GLINTIDE_DIR . '/assets/css/glintide-music-backup.css' )
	);

	// 音乐卡片播放器(网易云直链解析 + 播放 + 降级)
	wp_enqueue_script(
		'glintide-card-music',
		GLINTIDE_URL . '/assets/js/glintide-card-music.js',
		array(),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-card-music.js' ),
		true
	);
	wp_localize_script(
		'glintide-card-music',
		'glintideMusic',
		array(
			'restUrl' => rest_url( 'glintide/v1/netease-song' ),
		)
	);

	// 视频卡片播放交互
	wp_enqueue_script(
		'glintide-card-video',
		GLINTIDE_URL . '/assets/js/glintide-card-video.js',
		array(),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-card-video.js' ),
		true
	);

	// 首页卡片无限瀑布流加载(依赖内容卡片交互脚本提供的 glintide_card_ajax)
	wp_enqueue_script(
		'glintide-card-feed',
		GLINTIDE_URL . '/assets/js/glintide-card-feed.js',
		array( 'glintide-photo-swiper', 'jquery' ),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-card-feed.js' ),
		true
	);
	wp_enqueue_script(
		'glintide-link-preview',
		GLINTIDE_URL . '/assets/js/glintide-link-preview.js',
		array( 'glintide-card-feed' ),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-link-preview.js' ),
		true
	);

	// PJAX 无刷新导航(尽早加载以便拦截链接点击)
	wp_enqueue_script(
		'glintide-pjax',
		GLINTIDE_URL . '/assets/js/glintide-pjax.js',
		array(),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-pjax.js' ),
		false
	);
	wp_add_inline_script(
		'glintide-pjax',
		'window.glintideStyleVersion = ' . wp_json_encode( $glintide_style_version ) . ';',
		'before'
	);

	// 内容卡片详情弹窗
	wp_enqueue_script(
		'glintide-card-modal',
		GLINTIDE_URL . '/assets/js/glintide-card-modal.js',
		array(),
		filemtime( GLINTIDE_DIR . '/assets/js/glintide-card-modal.js' ),
		true
	);

	// 图标字体(remixicon)
	wp_enqueue_style( 'remixicon', GLINTIDE_URL . '/assets/fonts/remixicon.css', array(), GLINTIDE_VERSION );

	// 音乐播放器使用主题自带 iconfont
	wp_enqueue_style( 'glintide-iconfont', GLINTIDE_URL . '/assets/iconfont/iconfont.css', array(), GLINTIDE_VERSION );
	wp_enqueue_script(
		'glintide-theme-controls',
		GLINTIDE_URL . '/assets/js/theme-controls.js',
		array(),
		filemtime( GLINTIDE_DIR . '/assets/js/theme-controls.js' ),
		false
	);
}
add_action( 'wp_enqueue_scripts', 'glintide_scripts' );

/**
 * 后台:加载 Codestar Framework 与主题设置
 */
require_once GLINTIDE_DIR . '/inc/assets/codestar-framework/codestar-framework.php';
require_once GLINTIDE_DIR . '/inc/options/theme-option.php';

/**
 * 前台:REST API(网易云歌单解析等)
 */
require_once GLINTIDE_DIR . '/inc/mod/glintide-rest.php';

/**
 * 前台:小工具管理器(插拔式,自动扫描 inc/widgets/)
 */
require_once GLINTIDE_DIR . '/inc/widgets/class-glintide-widgets.php';
Glintide_Widgets::init();
require_once GLINTIDE_DIR . '/inc/frontend/navigation.php';
require_once GLINTIDE_DIR . '/inc/frontend/site-tools.php';

/**
 * 读取主题设置
 *
 * @param string $option  设置键名
 * @param mixed  $default 默认值
 * @return mixed
 */
function glintide_get_option( $option = '', $default = null ) {
	$options = get_option( 'glintide_options' );
	return ( isset( $options[ $option ] ) ) ? $options[ $option ] : $default;
}

/**
 * 输出主题设置驱动的 CSS 变量(卡片圆角/阴影/栏宽等)
 */
function glintide_custom_css_vars() {
	$radius = (int) glintide_get_option( 'card_radius', 16 );
	$radius = max( 0, min( 64, $radius ) );

	$shadow = glintide_get_option(
		'card_shadow',
		'rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.12) 0px 8px 40px -12px'
	);
	$shadow = wp_strip_all_tags( (string) $shadow );

	// 三栏宽度
	$left_width   = max( 1, (int) glintide_get_option( 'sidebar_left_width', 260 ) );
	$center_width = max( 1, (int) glintide_get_option( 'center_width', 640 ) );
	$right_width  = max( 1, (int) glintide_get_option( 'sidebar_right_width', 260 ) );
	$layout_width = $left_width + $center_width + $right_width;
	?>
	<style id="glintide-custom-css-vars">
	:root {
		--glintide-radius: <?php echo (int) $radius; ?>px;
		--glintide-shadow-card: <?php echo esc_attr( $shadow ); ?>;
		--glintide-sidebar-left-width: <?php echo (int) $left_width; ?>px;
		--glintide-center-width: <?php echo (int) $center_width; ?>px;
		--glintide-sidebar-right-width: <?php echo (int) $right_width; ?>px;
		--glintide-layout-width: <?php echo (int) $layout_width; ?>px;
	}
	</style>
	<?php
}
add_action( 'wp_head', 'glintide_custom_css_vars', 99 );
require_once GLINTIDE_DIR . '/inc/options/home-option.php';
require_once GLINTIDE_DIR . '/inc/frontend/home-banner.php';
require_once GLINTIDE_DIR . '/inc/mod/glintide-content-cards.php';

/**
 * 内容宽度
 */
function glintide_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'glintide_content_width', 640 );
}
add_action( 'after_setup_theme', 'glintide_content_width', 0 );

/**
 * 站点 Logo(文字或图片)
 */
function glintide_site_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
	} else {
		echo '<a class="site-logo-text" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
	}
}

/**
 * 首页顶部封面地址
 */
function glintide_home_banner_url() {
	$options = get_option( 'glintide_options', array() );
	$banner  = isset( $options['home_banner'] ) ? $options['home_banner'] : array();

	if ( is_array( $banner ) && ! empty( $banner['url'] ) ) {
		return $banner['url'];
	}

	return GLINTIDE_URL . '/assets/images/banner.jpg';
}

/**
 * 分页
 */
function glintide_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'prev_text' => '<i class="ri-arrow-left-line" aria-hidden="true"></i>',
			'next_text' => '<i class="ri-arrow-right-line" aria-hidden="true"></i>',
			'class'     => 'glintide-pagination',
		)
	);
}

/**
 * 文章浏览量
 */
function glintide_post_views( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$count   = absint( get_post_meta( $post_id, 'views', true ) );
	return number_format_i18n( $count );
}

/**
 * 文章第一个分类名
 */
function glintide_post_first_cat() {
	$categories = get_the_category();
	if ( empty( $categories ) ) {
		return '';
	}
	return $categories[0]->name;
}

/**
 * 文章卡片底部元信息(浏览量/评论/点赞)
 */
function glintide_post_card_meta() {
	$html  = '<div class="post-views item"><i class="ri-eye-line" aria-hidden="true"></i><span class="number">' . esc_html( glintide_post_views() ) . '</span></div>';
	$html .= '<div class="post-comments item"><i class="ri-chat-4-line" aria-hidden="true"></i><span class="number">' . esc_html( get_comments_number() ) . '</span></div>';
	$html .= '<div class="post-likes item"><i class="ri-heart-3-line" aria-hidden="true"></i><span class="number">' . esc_html( absint( get_post_meta( get_the_ID(), 'likes_count', true ) ) ) . '</span></div>';
	return $html;
}
