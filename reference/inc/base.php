<?php
/**
 * Pix主题 - 基础函数
 * 
 * 主题初始化、菜单、图像尺寸等基础功能
 * 
 * @package pix
 * @author lxhcool
 * @version 1.0.4
 */

function pix_setup() {

	//load_theme_textdomain( 'pix', get_template_directory() . '/languages' );

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );

	//注册菜单
	register_nav_menus(
		array(
            'top' => esc_html__( '顶部主导航', 'pix' ),
			'top_sub' => esc_html__( '顶部副导航', 'pix' ),
			'left' => esc_html__( '侧边导航(需侧边导航模式下)', 'pix' ),
		)
	);

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

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

}
add_action( 'after_setup_theme', 'pix_setup' );

/**
 * 获取站点时区下某一天的起止时间。
 *
 * @param int|null $timestamp Unix 时间戳，不传则使用当前时间。
 * @return array
 */
function pix_site_day_bounds($timestamp = null) {
	$timezone = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC');

	if ($timestamp === null) {
		$now = new DateTimeImmutable('now', $timezone);
	} else {
		$now = (new DateTimeImmutable('@' . intval($timestamp)))->setTimezone($timezone);
	}

	$start = $now->setTime(0, 0, 0);
	$end = $now->setTime(23, 59, 59);
	$expires_in = max(60, $end->getTimestamp() - time() + 1);

	return array(
		'date' => $start->format('Y-m-d'),
		'start' => $start->format('Y-m-d H:i:s'),
		'end' => $end->format('Y-m-d H:i:s'),
		'expires_in' => $expires_in,
	);
}

/**
 * 注册小工具
 */
function pix_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( '博客页左侧', 'pix' ),
			'id'            => 'blog-left',
			'description'   => esc_html__( '博客页面左侧小工具区域', 'pix' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '博客页右侧', 'pix' ),
			'id'            => 'blog-right',
			'description'   => esc_html__( '博客页面右侧小工具区域', 'pix' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '片刻页左侧', 'pix' ),
			'id'            => 'moment-left',
			'description'   => esc_html__( '片刻页面左侧小工具区域', 'pix' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '片刻页右侧', 'pix' ),
			'id'            => 'moment-right',
			'description'   => esc_html__( '片刻页面右侧小工具区域', 'pix' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '页面左侧', 'pix' ),
			'id'            => 'page-left',
			'description'   => esc_html__( '普通页面左侧小工具区域', 'pix' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '页面右侧', 'pix' ),
			'id'            => 'page-right',
			'description'   => esc_html__( '普通页面右侧小工具区域', 'pix' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '文章页左侧', 'pix' ),
			'id'            => 'post-left',
			'description'   => esc_html__( '文章内页左侧小工具区域', 'pix' ),	
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '文章页右侧', 'pix' ),
			'id'            => 'post-right',
			'description'   => esc_html__( '文章内页右侧小工具区域', 'pix' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s box wid-p wid-box">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( '页脚小工具', 'pix' ),
			'id'            => 'footer-widget',
			'description'   => esc_html__( '页脚区域小工具，显示在版权信息上方', 'pix' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="footer-widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'pix_widgets_init' );

/**
 * 保存并恢复 PixPro 的小工具区域，避免切换主题后被移到未启用区域。
 */
function pix_widget_sidebar_ids() {
    return array(
        'blog-left',
        'blog-right',
        'moment-left',
        'moment-right',
        'page-left',
        'page-right',
        'post-left',
        'post-right',
        'footer-widget',
    );
}

function pix_save_widget_sidebar_snapshot() {
    $sidebars = get_option( 'sidebars_widgets', array() );
    if ( ! is_array( $sidebars ) ) {
        return;
    }

    $snapshot = array();
    foreach ( pix_widget_sidebar_ids() as $sidebar_id ) {
        $snapshot[$sidebar_id] = isset( $sidebars[$sidebar_id] ) && is_array( $sidebars[$sidebar_id] )
            ? array_values( $sidebars[$sidebar_id] )
            : array();
    }

    update_option( 'pix_widget_sidebar_snapshot', $snapshot, false );
}

function pix_restore_widget_sidebar_snapshot() {
    $snapshot = get_option( 'pix_widget_sidebar_snapshot', array() );
    $sidebars = get_option( 'sidebars_widgets', array() );
    if ( ! is_array( $snapshot ) || ! is_array( $sidebars ) ) {
        return;
    }

    $snapshot_ids = array();
    foreach ( pix_widget_sidebar_ids() as $sidebar_id ) {
        $saved_widgets = isset( $snapshot[$sidebar_id] ) && is_array( $snapshot[$sidebar_id] )
            ? $snapshot[$sidebar_id]
            : array();

        foreach ( $saved_widgets as $widget_id ) {
            if ( is_string( $widget_id ) && $widget_id !== '' ) {
                $snapshot_ids[$widget_id] = true;
            }
        }
    }

    if ( empty( $snapshot_ids ) ) {
        delete_option( 'pix_widget_sidebar_snapshot' );
        return;
    }

    foreach ( $sidebars as $sidebar_id => $widgets ) {
        if ( ! is_array( $widgets ) ) {
            continue;
        }

        $sidebars[$sidebar_id] = array_values( array_filter( $widgets, function( $widget_id ) use ( $snapshot_ids ) {
            return ! isset( $snapshot_ids[$widget_id] );
        } ) );
    }

    foreach ( pix_widget_sidebar_ids() as $sidebar_id ) {
        $saved_widgets = isset( $snapshot[$sidebar_id] ) && is_array( $snapshot[$sidebar_id] )
            ? array_values( array_filter( $snapshot[$sidebar_id], 'is_string' ) )
            : array();
        $sidebars[$sidebar_id] = $saved_widgets;
    }

    update_option( 'sidebars_widgets', $sidebars );
    delete_option( 'pix_widget_sidebar_snapshot' );
}

add_action( 'switch_theme', 'pix_save_widget_sidebar_snapshot', 1 );
add_action( 'after_switch_theme', 'pix_restore_widget_sidebar_snapshot', 20 );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function pix_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'pix_content_width', 640 );
}
add_action( 'after_setup_theme', 'pix_content_width', 0 );
