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
	// 主样式
	wp_enqueue_style( 'glintide-style', get_stylesheet_uri(), array(), GLINTIDE_VERSION );

	// 图标字体(remixicon)
	wp_enqueue_style( 'remixicon', GLINTIDE_URL . '/assets/fonts/remixicon.css', array(), GLINTIDE_VERSION );

	// 音乐播放器使用主题自带 iconfont
	wp_enqueue_style( 'glintide-iconfont', GLINTIDE_URL . '/assets/iconfont/iconfont.css', array(), GLINTIDE_VERSION );
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
	$left_width   = max( 0, (int) glintide_get_option( 'sidebar_left_width', 260 ) );
	$center_width = max( 0, (int) glintide_get_option( 'center_width', 640 ) );
	$right_width  = max( 0, (int) glintide_get_option( 'sidebar_right_width', 260 ) );
	?>
	<style id="glintide-custom-css-vars">
	:root {
		--glintide-radius: <?php echo (int) $radius; ?>px;
		--glintide-shadow-card: <?php echo esc_attr( $shadow ); ?>;
		--glintide-sidebar-left-width: <?php echo (int) $left_width; ?>px;
		--glintide-center-width: <?php echo (int) $center_width; ?>px;
		--glintide-sidebar-right-width: <?php echo (int) $right_width; ?>px;
	}
	</style>
	<?php
}
add_action( 'wp_head', 'glintide_custom_css_vars', 99 );
require_once GLINTIDE_DIR . '/inc/options/home-option.php';

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
