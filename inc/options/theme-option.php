<?php
/**
 * Glintide 主题设置
 *
 * 使用 Codestar Framework,菜单固定在后台最底部
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// 设置存储前缀
$prefix = 'glintide_options';

// 主题版本
$glintide_theme_version = wp_get_theme()->get( 'Version' );

//
// 创建设置页
//
CSF::createOptions(
	$prefix,
	array(
		'framework_title' => '<div class="glintide-admin-header-brand"><span class="glintide-admin-header-name">Glintide</span><span class="glintide-admin-header-version">v' . esc_html( $glintide_theme_version ) . '</span></div>',
		'menu_title'      => 'Glintide 设置',
		'menu_slug'       => 'glintide-settings',
		'menu_position'   => 100.1,
		'class'           => 'glintide-options',
		'menu_icon'       => 'dashicons-admin-generic',
	)
);

//
// 站点设置
//
CSF::createSection(
	$prefix,
	array(
		'id'       => 'site_panel',
		'title'    => '站点设置',
		'icon'     => 'ri-global-line',
		'priority' => 1,
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'site_basic',
		'parent' => 'site_panel',
		'title'  => '站点信息',
		'fields' => array(
			array(
				'id'      => 'site_logo',
				'type'    => 'media',
				'title'   => '站点 LOGO（深色）',
				'library' => 'image',
			),
			array(
				'id'      => 'site_logo_w',
				'type'    => 'media',
				'title'   => '站点 LOGO（浅色）',
				'library' => 'image',
			),
			array(
				'id'      => 'logo_text',
				'type'    => 'text',
				'title'   => '文字 LOGO',
			),
			array(
				'id'      => 'favicon',
				'type'    => 'media',
				'title'   => '网站图标（favicon）',
				'library' => 'image',
			),
			array(
				'id'      => 'admin_login_logo',
				'type'    => 'media',
				'title'   => 'WP 后台登录页 LOGO',
				'library' => 'image',
				'desc'    => '用于 wp-admin 登录页，建议上传 64 x 64px 的正方形 PNG 或 SVG。未设置时使用站点深色 LOGO。',
			),
			array(
				'id'      => 'def_thum_type',
				'type'    => 'radio',
				'title'   => '自定义缩略图类型',
				'inline'  => true,
				'options' => array(
					'local' => '本地上传',
					'link'  => '外链',
				),
				'default' => 'local',
			),
			array(
				'id'          => 'def_thum',
				'type'        => 'gallery',
				'title'       => '自定义默认缩略图',
				'add_title'   => '添加背景',
				'edit_title'  => '编辑背景',
				'clear_title' => '移除背景',
				'desc'        => '可上传多个，建议控制图片尺寸。',
				'dependency'  => array( 'def_thum_type', '==', 'local' ),
			),
			array(
				'id'         => 'def_thum_link',
				'type'       => 'textarea',
				'title'      => '自定义外链缩略图',
				'desc'       => '一行一个，请确保图片来源稳定。',
				'dependency' => array( 'def_thum_type', '==', 'link' ),
			),
		),
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'global_footer_setting',
		'parent' => 'site_panel',
		'title'  => '页脚设置',
		'fields' => array(
			array(
				'id'      => 'footer_text',
				'type'    => 'wp_editor',
				'title'   => '页脚版权信息',
				'desc'    => '支持 HTML 和短代码。',
				'tinymce' => true,
				'default' => '© ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . ' All rights reserved.',
			),
			array(
				'id'           => 'footer_icp',
				'type'         => 'link',
				'title'        => '备案号',
				'desc'         => '填写 ICP 备案号及链接，显示在页脚右侧。',
				'add_title'    => '添加备案号',
				'edit_title'   => '修改备案号',
				'remove_title' => '移除备案号',
			),
		),
	)
);

//
// 外观设置
//
CSF::createSection(
	$prefix,
	array(
		'id'       => 'appearance_panel',
		'title'    => '外观设置',
		'icon'     => 'ri-palette-line',
		'priority' => 2,
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'appearance_global',
		'parent' => 'appearance_panel',
		'title'  => '全局样式',
		'fields' => array(
			array(
				'id'                              => 'bg-c',
				'type'                            => 'background',
				'title'                           => '背景色/图案',
				'background_gradient'             => true,
				'background_origin'               => true,
				'background_clip'                 => true,
				'background_blend_mode'           => true,
				'output'                          => 'body',
				'default'                         => array(
					'background-color'              => '#f3efff',
					'background-gradient-color'     => '#e7f1ff',
					'background-gradient-direction' => '135deg',
					'background-size'               => 'cover',
					'background-position'           => 'center center',
					'background-repeat'             => 'no-repeat',
				),
			),
		),
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'appearance_brand',
		'parent' => 'appearance_panel',
		'title'  => 'LOGO 样式',
		'fields' => array(
			array(
				'id'          => 'classic_logo_h',
				'type'        => 'slider',
				'title'       => '桌面端 LOGO 高度',
				'desc'        => '控制电脑端顶部导航中 LOGO 的显示高度。',
				'min'         => 0,
				'max'         => 100,
				'step'        => 1,
				'unit'        => 'px',
				'default'     => 22,
				'output'      => '.classic-logo a img',
				'output_mode' => 'height',
			),
		),
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'appearance_home_banner',
		'parent' => 'appearance_panel',
		'title'  => '首页封面',
		'fields' => array(
			array(
				'id'          => 'cls_banner_h',
				'type'        => 'slider',
				'title'       => '封面高度',
				'min'         => 100,
				'max'         => 400,
				'step'        => 1,
				'unit'        => 'px',
				'default'     => 200,
				'output'      => '.cls-banner',
				'output_mode' => 'height',
			),
			array(
				'id'      => 'cls_banner_radius_t',
				'type'    => 'slider',
				'title'   => '封面圆角（上面）',
				'desc'    => '控制封面上方左、右两个角的圆角大小。',
				'min'     => 0,
				'max'     => 60,
				'step'    => 1,
				'unit'    => 'px',
				'default' => 0,
				'output'  => array(
					'border-top-left-radius'  => '.cls-banner, .cls-banner img, .cls-banner:before',
					'border-top-right-radius' => '.cls-banner, .cls-banner img, .cls-banner:before',
				),
			),
			array(
				'id'      => 'cls_banner_radius_b',
				'type'    => 'slider',
				'title'   => '封面圆角（下面）',
				'desc'    => '控制封面下方左、右两个角的圆角大小。',
				'min'     => 0,
				'max'     => 60,
				'step'    => 1,
				'unit'    => 'px',
				'default' => 0,
				'output'  => array(
					'border-bottom-left-radius'  => '.cls-banner, .cls-banner img, .cls-banner:before',
					'border-bottom-right-radius' => '.cls-banner, .cls-banner img, .cls-banner:before',
				),
			),
			array(
				'id'          => 'cls_banner_cover',
				'type'        => 'color',
				'title'       => '封面遮罩',
				'output'      => '.cls-banner:before',
				'output_mode' => 'background-color',
				'default'     => 'rgb(53 60 172 / 23%)',
				'subtitle'    => '调整成半透明色，让文字更突出。',
			),
			array(
				'id'          => 'cls_banner_name',
				'type'        => 'color',
				'title'       => '昵称颜色',
				'output'      => '.cls-banner-info .info .name',
				'output_mode' => 'color',
				'default'     => '#ffffff',
			),
			array(
				'id'          => 'cls_banner_des',
				'type'        => 'color',
				'title'       => '个人描述颜色',
				'output'      => '.cls-banner-info .info .des',
				'output_mode' => 'color',
				'default'     => '#dad5ff',
			),
			array(
				'id'          => 'cls_banner_avas',
				'type'        => 'slider',
				'title'       => '头像尺寸',
				'min'         => 0,
				'max'         => 64,
				'step'        => 1,
				'unit'        => 'px',
				'output'      => '.cls-banner-info .ava img',
				'output_mode' => 'width',
				'default'     => 64,
			),
			array(
				'id'          => 'cls_banner_avar',
				'type'        => 'spacing',
				'title'       => '头像圆角',
				'output'      => '.cls-banner-info .ava img',
				'output_mode' => 'border-radius',
				'all'         => true,
				'default'     => array(
					'all'  => '8',
					'unit' => 'px',
				),
			),
		),
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'appearance_footer',
		'parent' => 'appearance_panel',
		'title'  => '页脚与回到顶部',
		'fields' => array(
			array(
				'id'          => 'footer_bg',
				'type'        => 'color',
				'title'       => '页脚背景色',
				'output'      => '.site-footer',
				'output_mode' => 'background-color',
				'default'     => '#ffffff',
			),
			array(
				'id'          => 'footer_text_color',
				'type'        => 'color',
				'title'       => '页脚文本色',
				'output'      => '.site-footer',
				'output_mode' => 'color',
				'default'     => '#39364bff',
			),
			array(
				'id'          => 'back_top_bg_color',
				'type'        => 'color',
				'title'       => '回到顶部按钮颜色',
				'output'      => '.pix-global-back-top',
				'output_mode' => 'background-color',
				'default'     => '#3157ff',
			),
			array(
				'id'          => 'back_top_icon_color',
				'type'        => 'color',
				'title'       => '回到顶部图标颜色',
				'output'      => '.pix-global-back-top',
				'output_mode' => 'color',
				'default'     => '#ffffff',
			),
			array(
				'id'          => 'back_top_radius',
				'type'        => 'number',
				'title'       => '回到顶部按钮圆角',
				'unit'        => 'px',
				'output'      => '.pix-global-back-top',
				'output_mode' => 'border-radius',
				'default'     => 16,
			),
		),
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'appearance_layout',
		'parent' => 'appearance_panel',
		'title'  => '三栏布局',
		'fields' => array(
			array(
				'id'      => 'sidebar_left_enable',
				'type'    => 'switcher',
				'title'   => '启用左栏',
				'default' => true,
			),
			array(
				'id'      => 'sidebar_right_enable',
				'type'    => 'switcher',
				'title'   => '启用右栏',
				'default' => true,
			),
		),
	)
);
