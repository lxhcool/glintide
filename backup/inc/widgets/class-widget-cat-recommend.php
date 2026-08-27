<?php
/**
 * 小工具:分类推荐
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Cat_Recommend extends Glintide_Widget {

	public static $id          = 'glintide_cat_recommend';
	public static $title       = 'PPO · 分类推荐';
	public static $description = '展示文章分类推荐';
	public static $classname   = 'ppo-widget glintide_cat_recommend';

	public static function fields() {
		return array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'title' => '标题',
			),
			array(
				'id'      => 'cat_style',
				'type'    => 'radio',
				'title'   => '显示样式',
				'options' => array(
					'banner' => '全宽图模式',
					'tag'    => '标签模式',
				),
				'inline'  => true,
				'default' => 'banner',
			),
			array(
				'id'          => 'cat_list',
				'type'        => 'select',
				'title'       => '选择分类',
				'chosen'      => true,
				'multiple'    => true,
				'sortable'    => true,
				'placeholder' => '选择分类',
				'options'     => 'categories',
			),
		);
	}

	public static function render( $instance ) {
		$title     = $instance['title'] ?? '';
		$cat_style = $instance['cat_style'] ?? 'banner';
		$cat_ids   = $instance['cat_list'] ?? array();

		if ( empty( $cat_ids ) ) {
			return glintide_widget_notice( '请配置推荐分类' );
		}

		$html = '<div class="glintide-cat-recommend glintide-cat-' . esc_attr( $cat_style ) . ( $cat_style === 'tag' ? ' glintide-cat-tag-mode' : '' ) . '">';
		$html .= glintide_widget_title( $title );
		$html .= '<div class="glintide-cat-box wid-item">';
		$has_cat = false;

		foreach ( $cat_ids as $index => $cat_id ) {
			$cat = get_category( $cat_id );
			if ( ! $cat || is_wp_error( $cat ) ) {
				continue;
			}
			$has_cat = true;

			$cat_name  = $cat->name;
			$cat_count = $cat->count;
			$cat_link  = get_category_link( $cat_id );

			$tax_meta   = get_term_meta( $cat_id, '_glintide_taxonomy_options', true );
			$cat_banner = isset( $tax_meta['cat_banner'] ) ? $tax_meta['cat_banner'] : '';
			if ( is_array( $cat_banner ) ) {
				$cat_banner = $cat_banner['url'] ?? '';
			}

			if ( $cat_style === 'banner' ) {
				$html .= '<a href="' . esc_url( $cat_link ) . '" class="glintide-cat-banner-item">';
				$html .= '<div class="glintide-cat-banner">' . ( $cat_banner ? '<img src="' . esc_url( $cat_banner ) . '" alt="' . esc_attr( $cat_name ) . '">' : '' ) . '</div>';
				$html .= '<div class="glintide-cat-overlay">';
				$html .= '<h3 class="glintide-cat-name">' . esc_html( $cat_name ) . '</h3>';
				$html .= '<span class="glintide-cat-count">' . $cat_count . ' 篇文章</span>';
				$html .= '</div>';
				$html .= '</a>';
			} else {
				$is_red = ( $index < 2 );
				$html .= '<a href="' . esc_url( $cat_link ) . '" class="glintide-cat-tag-item' . ( $is_red ? ' tag-red' : '' ) . '">';
				$html .= '<i class="ri-apps-fill"></i>';
				$html .= '<span class="glintide-cat-name">' . esc_html( $cat_name ) . '</span>';
				$html .= '<span class="glintide-cat-count">(' . $cat_count . ')</span>';
				$html .= '</a>';
			}
		}

		if ( ! $has_cat ) {
			$html .= glintide_widget_notice( '请配置有效的推荐分类' );
		}

		$html .= '</div></div>';

		return $html;
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_cat_recommend' ) ) {
	function glintide_cat_recommend( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Cat_Recommend::render( $instance );
		echo $args['after_widget'];
	}
}