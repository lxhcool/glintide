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
define( 'PIX_VERSION', GLINTIDE_VERSION );

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
}
add_action( 'wp_enqueue_scripts', 'glintide_scripts' );

/**
 * 后台:加载 Codestar Framework 与主题设置
 */
require_once GLINTIDE_DIR . '/inc/assets/codestar-framework/codestar-framework.php';
require_once GLINTIDE_DIR . '/inc/options/theme-option.php';

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
 * 分页
 */
function glintide_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'prev_text' => '<i class="ri-arrow-left-line" aria-hidden="true"></i>',
			'next_text' => '<i class="ri-arrow-right-line" aria-hidden="true"></i>',
			'class'     => 'pix-pagination',
		)
	);
}