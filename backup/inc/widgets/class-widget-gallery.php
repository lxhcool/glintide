<?php
/**
 * 小工具:图片画廊
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Gallery extends Glintide_Widget {

	public static $id          = 'glintide_gallery_widget';
	public static $title       = 'PPO · 图片画廊';
	public static $description = '展示图片画廊,支持九宫格布局';
	public static $classname   = 'ppo-widget glintide_gallery_widget';

	public static function fields() {
		return array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'title' => '标题',
			),
			array(
				'id'    => 'images',
				'type'  => 'gallery',
				'title' => '图片集',
			),
		);
	}

	public static function render( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : '';
		$images = isset( $instance['images'] ) ? $instance['images'] : '';

		if ( is_string( $images ) && ! empty( $images ) ) {
			$images = array_filter( explode( ',', $images ) );
		} elseif ( ! is_array( $images ) ) {
			$images = array();
		}

		$html  = glintide_widget_title( $title );
		$html .= '<div class="glintide-gallery-widget wid-item">';

		if ( empty( $images ) ) {
			$html .= glintide_widget_notice( '请配置图片画廊' );
		} else {
			$html .= '<div class="glintide-gallery-grid">';
			foreach ( $images as $attachment_id ) {
				$full_url  = wp_get_attachment_image_url( $attachment_id, 'full' );
				$thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );
				if ( ! $full_url ) {
					continue;
				}
				$html .= '<a href="' . esc_url( $full_url ) . '" class="glintide-gallery-item">';
				$html .= '<img src="' . esc_url( $thumb_url ?: $full_url ) . '" alt="画廊图片">';
				$html .= '</a>';
			}
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_gallery_widget' ) ) {
	function glintide_gallery_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Gallery::render( $instance );
		echo $args['after_widget'];
	}
}