<?php
/**
 * 小工具:当前日期卡
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Calendar extends Glintide_Widget {

	public static $id          = 'glintide_calendar_widget';
	public static $title       = 'Glintide 日期卡';
	public static $description = '独立显示当前月份、日期、农历和星期。';
	public static $classname   = 'glintide-countdown-widget-wrap glintide-mini-widget-wrap';

	public static function fields() {
		return glintide_countdown_visual_fields();
	}

	public static function render( $instance ) {
		$style = glintide_countdown_card_style( $instance );

		return '<div class="glintide-countdown-widget glintide-countdown-widget--single" data-glintide-calendar>' . glintide_calendar_card_html( $style ) . '</div>';
	}
}

if ( ! function_exists( 'glintide_calendar_widget' ) ) {
	function glintide_calendar_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Calendar::render( $instance );
		echo $args['after_widget'];
	}
}
