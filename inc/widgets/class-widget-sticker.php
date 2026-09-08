<?php
/**
 * 小工具:贴纸照片
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Sticker extends Glintide_Widget {

	public static $id          = 'glintide_sticker_widget';
	public static $title       = 'Glintide 贴纸';
	public static $description = '上传一张照片，以正方形贴纸卡片显示。';
	public static $classname   = 'glintide-sticker-widget-wrap glintide-mini-widget-wrap';

	public static function fields() {
		return array(
			array(
				'id'           => 'sticker_image',
				'type'         => 'upload',
				'title'        => '贴纸照片',
				'library'      => 'image',
				'button_title' => '上传照片',
				'remove_title' => '删除照片',
				'preview'      => true,
				'desc'         => '只能上传一张照片；建议使用正方形图片。',
			),
		);
	}

	public static function render( $instance ) {
		$instance = is_array( $instance ) ? $instance : array();
		$image    = $instance['sticker_image'] ?? '';

		if ( is_array( $image ) ) {
			$image = $image['url'] ?? '';
		} elseif ( is_numeric( $image ) ) {
			$image = wp_get_attachment_image_url( absint( $image ), 'large' );
		}

		$image = is_string( $image ) ? esc_url_raw( $image ) : '';
		if ( ! $image ) {
			return glintide_widget_notice( '请上传一张贴纸照片' );
		}

		return '<div class="glintide-sticker-widget"><img src="' . esc_url( $image ) . '" alt="贴纸照片" loading="lazy" decoding="async"></div>';
	}
}

if ( ! function_exists( 'glintide_sticker_widget' ) ) {
	function glintide_sticker_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Sticker::render( $instance );
		echo $args['after_widget'];
	}
}
