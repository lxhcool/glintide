<?php
/**
 * Glintide 主题设置(仅布局骨架,无功能)
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
				'title'   => '站点 LOGO',
				'library' => 'image',
			),
			array(
				'id'    => 'logo_text',
				'type'  => 'text',
				'title' => '文字 LOGO',
			),
		),
	)
);

//
// 布局设置
//
CSF::createSection(
	$prefix,
	array(
		'id'       => 'layout_panel',
		'title'    => '布局设置',
		'icon'     => 'ri-layout-3-line',
		'priority' => 2,
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'layout_basic',
		'parent' => 'layout_panel',
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

//
// 外观设置
//
CSF::createSection(
	$prefix,
	array(
		'id'       => 'appearance_panel',
		'title'    => '外观设置',
		'icon'     => 'ri-palette-line',
		'priority' => 3,
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'appearance_basic',
		'parent' => 'appearance_panel',
		'title'  => '主题外观',
		'fields' => array(
			array(
				'id'      => 'dark_mode',
				'type'    => 'switcher',
				'title'   => '深色模式',
				'default' => false,
			),
		),
	)
);

//
// 关于
//
CSF::createSection(
	$prefix,
	array(
		'id'       => 'about_panel',
		'title'    => '关于',
		'icon'     => 'ri-information-line',
		'priority' => 4,
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'about_basic',
		'parent' => 'about_panel',
		'title'  => '主题信息',
		'fields' => array(
			array(
				'type'    => 'content',
				'content' => '<p>Glintide 主题 v' . esc_html( $glintide_theme_version ) . '</p><p>作者: lxhcool & fuzzz</p><p>born for design</p>',
			),
		),
	)
);