<?php
/**
 * 小工具:菜单组件
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Menu extends Glintide_Widget {

	public static $id          = 'glintide_menu_widget';
	public static $title       = 'PPO · 菜单组件';
	public static $description = '自定义菜单组件,支持多菜单和分组标题';
	public static $classname   = 'ppo-widget glintide_menu_widget';

	public static function fields() {
		return array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'title' => '标题',
			),
			array(
				'id'     => 'menu_groups',
				'type'   => 'group',
				'title'  => '菜单分组',
				'fields' => array(
					array(
						'id'    => 'group_title',
						'type'  => 'text',
						'title' => '分组标题',
					),
					array(
						'id'          => 'menu_id',
						'type'        => 'select',
						'title'       => '选择菜单',
						'placeholder' => '选择菜单',
						'options'     => 'menus',
					),
				),
			),
		);
	}

	public static function render( $instance ) {
		$html     = '<div class="glintide-menu-widget wid-item">';
		$has_menu = false;

		if ( ! empty( $instance['menu_groups'] ) ) {
			foreach ( $instance['menu_groups'] as $group ) {
				if ( ! empty( $group['menu_id'] ) ) {
					$has_menu = true;
					$html .= '<div class="glintide-menu-group">';
					if ( ! empty( $group['group_title'] ) ) {
						$html .= '<div class="glintide-menu-group-title">' . esc_html( $group['group_title'] ) . '</div>';
					}
					$html .= '<div class="glintide-menu-list">';
					$html .= wp_nav_menu(
						array(
							'menu'      => intval( $group['menu_id'] ),
							'container' => false,
							'echo'      => false,
							'depth'     => 2,
						)
					);
					$html .= '</div>';
					$html .= '</div>';
				}
			}
		}

		if ( ! $has_menu ) {
			$html .= glintide_widget_notice( '请配置菜单分组' );
		}

		$html .= '</div>';

		return $html;
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_menu_widget' ) ) {
	function glintide_menu_widget( $args, $instance ) {
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		echo Glintide_Widget_Menu::render( $instance );

		echo $args['after_widget'];
	}
}