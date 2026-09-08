<?php
/**
 * 小工具:广告位
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_GG_Banner extends Glintide_Widget {

	public static $id          = 'glintide_gg_banner';
	public static $title       = 'PPO · 广告位';
	public static $description = '图片广告位,支持上传图片和添加外链';
	public static $classname   = 'ppo-widget glintide_gg_banner';

	public static function fields() {
		return array(
			array(
				'id'           => 'gg_image',
				'type'         => 'upload',
				'title'        => '广告图片',
				'library'      => 'image',
				'placeholder'  => 'http://',
				'button_title' => '上传图片',
				'remove_title' => '删除图片',
				'preview'      => true,
				'desc'         => '可以上传图片或直接输入图片链接,外链时请添加http://或https://',
			),
			array(
				'id'    => 'gg_link',
				'type'  => 'text',
				'title' => '广告链接',
				'desc'  => '广告点击跳转链接',
			),
			array(
				'id'      => 'gg_target',
				'type'    => 'switcher',
				'title'   => '是否在新窗口打开',
				'desc'    => '点击广告是否在新窗口打开',
				'default' => true,
			),
		);
	}

	public static function render( $instance ) {
		$image  = $instance['gg_image'] ?? '';
		$link   = $instance['gg_link'] ?? '#';
		$target = ! empty( $instance['gg_target'] ) ? 'target="_blank"' : '';

		if ( empty( $image ) ) {
			return glintide_widget_notice( '请配置广告图片' );
		}

		$html  = '<div class="glintide-gg-banner">';
		$html .= '<div class="glintide-gg-image wid-item">';
		$html .= '<a href="' . esc_url( $link ) . '" ' . $target . '>';
		$html .= '<img src="' . esc_url( $image ) . '" alt="广告">';
		$html .= '</a>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_gg_banner' ) ) {
	function glintide_gg_banner( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_GG_Banner::render( $instance );
		echo $args['after_widget'];
	}
}