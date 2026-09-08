<?php
/**
 * 小工具:图标网格
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Icon_Grid extends Glintide_Widget {

	public static $id          = 'glintide_icon_grid';
	public static $title       = 'PPO · 图标网格';
	public static $description = '以网格形式展示图标链接';
	public static $classname   = 'ppo-widget glintide_icon_grid';

	public static function fields() {
		return array(
			array(
				'id'      => 'per_row',
				'type'    => 'radio',
				'title'   => '每行显示',
				'options' => array(
					'3' => '3个',
					'4' => '4个',
				),
				'inline'  => true,
				'default' => '4',
			),
			array(
				'id'     => 'icon_list',
				'type'   => 'group',
				'title'  => '图标列表',
				'fields' => array(
					array(
						'id'    => 'title',
						'type'  => 'text',
						'title' => '标题',
					),
					array(
						'id'      => 'icon_type',
						'type'    => 'button_set',
						'title'   => '图标类型',
						'options' => array(
							'icon'  => '字体图标',
							'image' => '图片',
						),
						'default' => 'icon',
					),
					array(
						'id'         => 'icon',
						'type'       => 'icon',
						'title'      => '图标',
						'dependency' => array( 'icon_type', '==', 'icon' ),
					),
					array(
						'id'         => 'icon_color',
						'type'       => 'color',
						'title'      => '图标颜色',
						'dependency' => array( 'icon_type', '==', 'icon' ),
					),
					array(
						'id'                        => 'bg-color',
						'type'                      => 'background',
						'title'                     => '图标背景色',
						'background_image'          => false,
						'background_attachment'     => false,
						'background_size'           => false,
						'background_position'       => false,
						'background_repeat'         => false,
						'background_gradient'       => true,
						'default'                   => array(
							'background-color'              => '#f0f0f0',
							'background-gradient-color'     => '#ddd',
							'background-gradient-direction' => 'to bottom',
						),
						'dependency'                => array( 'icon_type', '==', 'icon' ),
					),
					array(
						'id'         => 'image',
						'type'       => 'upload',
						'title'      => '图片',
						'library'    => 'image',
						'dependency' => array( 'icon_type', '==', 'image' ),
						'preview'    => true,
						'desc'       => '图片需为正方形,支持外链图片',
					),
					array(
						'id'    => 'link',
						'type'  => 'text',
						'title' => '链接',
					),
					array(
						'id'      => 'target',
						'type'    => 'switcher',
						'title'   => '新窗口打开',
						'default' => true,
					),
				),
			),
		);
	}

	public static function render( $instance ) {
		$per_row = $instance['per_row'] ?? '4';
		$list    = $instance['icon_list'] ?? array();

		if ( empty( $list ) ) {
			return glintide_widget_notice( '请配置图标网格' );
		}

		$html  = '<div class="glintide-icon-grid">';
		$html .= '<div class="glintide-icon-grid-box wid-item glintide-icon-col-' . absint( $per_row ) . '">';
		$has_icon = false;

		foreach ( $list as $item ) {
			if ( empty( $item['title'] ) ) {
				continue;
			}

			$link      = $item['link'] ?? '#';
			$target    = ! empty( $item['target'] ) ? 'target="_blank"' : '';
			$icon_html = '';

			if ( ! empty( $item['icon_type'] ) && $item['icon_type'] === 'image' && ! empty( $item['image'] ) ) {
				$icon_html = '<img src="' . esc_url( $item['image'] ) . '" alt="' . esc_attr( $item['title'] ) . '">';
			} elseif ( ! empty( $item['icon'] ) ) {
				$icon_html = '<i class="' . esc_attr( $item['icon'] ) . '"></i>';
			}

			if ( empty( $icon_html ) ) {
				continue;
			}
			$has_icon = true;

			$bg_style   = '';
			$icon_style = '';
			if ( ! empty( $item['icon_type'] ) && $item['icon_type'] === 'icon' ) {
				if ( ! empty( $item['icon_color'] ) ) {
					$icon_style = 'color: ' . esc_attr( $item['icon_color'] ) . ';';
				}

				$bg = $item['bg-color'] ?? array();
				if ( ! empty( $bg ) && is_array( $bg ) ) {
					$gradient_color = $bg['background-gradient-color'] ?? '';
					$gradient_dir   = $bg['background-gradient-direction'] ?? 'to bottom';
					$solid_color    = $bg['background-color'] ?? '';

					if ( ! empty( $gradient_color ) && ! empty( $gradient_dir ) ) {
						$bg_style = 'background: linear-gradient(' . esc_attr( $gradient_dir ) . ', ' . esc_attr( $solid_color ) . ' 0%, ' . esc_attr( $gradient_color ) . ' 100%);';
					} elseif ( ! empty( $solid_color ) ) {
						$bg_style = 'background-color: ' . esc_attr( $solid_color ) . ';';
					}
				}
			}

			$html .= '<a href="' . esc_url( $link ) . '" ' . $target . ' class="glintide-icon-item">';
			$html .= '<div class="glintide-icon-wrap"' . ( $bg_style ? ' style="' . $bg_style . '"' : '' ) . '>';
			$html .= $icon_html;
			$html .= '</div>';
			$html .= '<div class="glintide-icon-title">' . esc_html( $item['title'] ) . '</div>';
			$html .= '</a>';
		}

		if ( ! $has_icon ) {
			$html .= glintide_widget_notice( '请配置有效的图标项' );
		}

		$html .= '</div></div>';

		return $html;
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_icon_grid' ) ) {
	function glintide_icon_grid( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Icon_Grid::render( $instance );
		echo $args['after_widget'];
	}
}