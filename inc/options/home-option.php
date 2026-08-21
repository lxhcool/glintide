<?php
/**
 * Glintide 首页设置
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$prefix = 'glintide_options';

CSF::createSection(
	$prefix,
	array(
		'id'       => 'home_panel',
		'title'    => '首页',
		'icon'     => 'ri-home-4-line',
		'priority' => 4,
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'classic_banner_setting',
		'parent' => 'home_panel',
		'title'  => '顶部封面',
		'fields' => array(
			array(
				'type'    => 'submessage',
				'style'   => 'notice',
				'class'   => 'tab-msg-w',
				'content' => '封面设置',
			),
			array(
				'id'      => 'cls_banner_type',
				'type'    => 'radio',
				'title'   => '封面类型',
				'inline'  => true,
				'options' => array(
					'upload' => '媒体库上传',
					'link'   => '自定义外链',
				),
				'default' => 'upload',
			),
			array(
				'id'          => 'cls_banner_upload',
				'type'        => 'gallery',
				'title'       => '封面上传',
				'add_title'   => '添加封面',
				'edit_title'  => '编辑封面',
				'clear_title' => '移除封面',
				'subtitle'    => '可上传多张封面',
				'dependency'  => array( 'cls_banner_type', '==', 'upload' ),
			),
			array(
				'id'          => 'cls_banner_link',
				'type'        => 'textarea',
				'title'       => '自定义封面外链',
				'placeholder' => '一行一个封面链接',
				'dependency'  => array( 'cls_banner_type', '==', 'link' ),
			),
			array(
				'type'    => 'submessage',
				'style'   => 'notice',
				'class'   => 'tab-msg-w',
				'content' => '封面内容',
			),
			array(
				'id'      => 'cls_banner_content',
				'type'    => 'radio',
				'title'   => '内容类型',
				'inline'  => true,
				'options' => array(
					'text' => '自定义文字展示',
					'ava'  => '头像信息展示',
				),
				'default' => 'ava',
			),
			array(
				'id'            => 'opt-wp-editor-2',
				'type'          => 'wp_editor',
				'title'         => '',
				'tinymce'       => true,
				'quicktags'     => true,
				'media_buttons' => true,
				'height'        => '200px',
				'dependency'    => array( 'cls_banner_content', '==', 'text' ),
			),
			array(
				'type'       => 'content',
				'content'    => '头像信息机制：未登录状态显示站长头像、昵称和个人描述，登录后显示当前登录用户信息。',
				'dependency' => array( 'cls_banner_content', '==', 'ava' ),
			),
		),
	)
);

CSF::createSection(
	$prefix,
	array(
		'id'     => 'classic_home_setting',
		'parent' => 'home_panel',
		'title'  => '首页设置',
		'fields' => array(
			array(
				'id'      => 'cls_home_type',
				'type'    => 'radio',
				'title'   => '首页类型',
				'inline'  => true,
				'options' => array(
					'blog'   => '博客',
					'moment' => '片刻',
				),
				'default' => 'blog',
			),
			array(
				'id'          => 'cls_show_cats',
				'type'        => 'select',
				'title'       => '文章首页筛选分类',
				'placeholder' => '选择分类',
				'chosen'      => true,
				'multiple'    => true,
				'sortable'    => true,
				'options'     => 'categories',
				'desc'        => '如果设定了默认展示分类，此处不要重复添加。',
				'dependency'  => array( 'cls_home_type', '==', 'blog' ),
			),
			array(
				'id'          => 'cls_show_cats_de',
				'type'        => 'select',
				'title'       => '文章默认展示分类',
				'placeholder' => '选择分类',
				'chosen'      => true,
				'options'     => 'categories',
				'desc'        => '默认展示的分类文章，不选择则展示全部分类。',
				'dependency'  => array( 'cls_home_type', '==', 'blog' ),
			),
			array(
				'id'          => 'moment_default_cat',
				'type'        => 'select',
				'title'       => '发布默认圈子',
				'placeholder' => '不设置默认圈子',
				'options'     => 'categories',
				'chosen'      => true,
				'query_args'  => array(
					'taxonomy'  => 'moments',
					'hide_empty' => false,
				),
				'desc'        => '设置后，在片刻首页发布时默认选中该圈子；如果用户未加入该圈子，仍需手动选择可发布的圈子。',
				'dependency'  => array( 'cls_home_type', '==', 'moment' ),
			),
			array(
				'id'         => 'mos_home_hot_show',
				'type'       => 'switcher',
				'title'      => '开启片刻首页圈子推荐',
				'dependency' => array( 'cls_home_type', '==', 'moment' ),
				'default'    => false,
			),
			array(
				'id'          => 'mos_home_hot',
				'type'        => 'select',
				'title'       => '片刻首页圈子推荐展示',
				'placeholder' => '选择圈子',
				'options'     => 'categories',
				'chosen'      => true,
				'multiple'    => true,
				'sortable'    => true,
				'settings'    => array(
					'number' => 6,
				),
				'query_args'  => array(
					'taxonomy' => 'moments',
				),
				'desc'        => '选择多个圈子，首页最多推荐展示 6 个。',
				'dependency'  => array( 'cls_home_type|mos_home_hot_show', '==|==', 'moment|true' ),
			),
			array(
				'id'         => 'mos_home_show_type',
				'type'       => 'radio',
				'title'      => '推荐圈子显示方式',
				'options'    => array(
					'grid'   => '网格',
					'slider' => '轮播',
				),
				'inline'     => true,
				'default'    => 'grid',
				'dependency' => array( 'cls_home_type|mos_home_hot_show', '==|==', 'moment|true' ),
			),
			array(
				'id'         => 'mos_home_notice_show',
				'type'       => 'switcher',
				'title'      => '开启片刻首页公告',
				'dependency' => array( 'cls_home_type', '==', 'moment' ),
				'default'    => false,
			),
			array(
				'id'          => 'mos_home_notice',
				'type'        => 'textarea',
				'title'       => '片刻首页公告',
				'placeholder' => '公告内容',
				'default'     => '这是一条公告',
				'dependency'  => array( 'cls_home_type|mos_home_notice_show', '==|==', 'moment|true' ),
			),
			array(
				'id'         => 'mos_home_notice_link',
				'type'       => 'link',
				'title'      => '片刻首页公告链接',
				'desc'       => '链接文字不用填写。',
				'dependency' => array( 'cls_home_type|mos_home_notice_show', '==|==', 'moment|true' ),
			),
		),
	)
);
