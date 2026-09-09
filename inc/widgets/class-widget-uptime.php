<?php
/**
 * Site age in calendar days, using the WordPress timezone.
 *
 * @package glintide
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function glintide_uptime_start_date( $value, $timezone ) {
	if ( ! is_string( $value ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
		return false;
	}
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, $timezone );
	return $date && $date->format( 'Y-m-d' ) === $value ? $date : false;
}

class Glintide_Widget_Uptime extends Glintide_Widget {
	public static $id = 'glintide_uptime_widget';
	public static $title = 'Glintide 站点运行时间';
	public static $description = '从指定建站日期计算累计天数；不是服务器在线率监测。';
	public static $classname = 'glintide-time-widget-wrap glintide-mini-widget-wrap';

	public static function fields() {
		return array(
			array(
				'id' => 'start_date',
				'type' => 'date',
				'title' => '建站日期',
				'settings' => array( 'dateFormat' => 'yy-mm-dd', 'changeMonth' => true, 'changeYear' => true ),
				'desc' => '按站点时区计算已过去的日历天数，建站当天为 0 天。',
			),
		);
	}

	public static function render( $instance ) {
		$instance = is_array( $instance ) ? $instance : array();
		$timezone = wp_timezone();
		$start = glintide_uptime_start_date( $instance['start_date'] ?? '', $timezone );
		$today = new DateTimeImmutable( 'today', $timezone );
		if ( ! $start || $start > $today ) {
			$message = $start ? '建站日期不能晚于今天' : '请设置有效的建站日期';
			return '<section class="glintide-time-card glintide-time-card--empty" role="status">' . glintide_widget_notice( $message ) . '</section>';
		}
		$days = $start->diff( $today )->days;
		return '<section class="glintide-time-card glintide-time-card--uptime" aria-label="' . esc_attr( '站点已运行 ' . $days . ' 天' ) . '">'
			. '<div class="glintide-time-card__header"><span>站点运行</span><i class="ri-time-line" aria-hidden="true"></i></div>'
			. '<div class="glintide-time-card__number"><strong>' . esc_html( number_format_i18n( $days ) ) . '</strong><span>天</span></div>'
			. '<div class="glintide-time-card__footer"><span>始于</span><time datetime="' . esc_attr( $start->format( 'Y-m-d' ) ) . '">' . esc_html( $start->format( 'Y.m.d' ) ) . '</time></div>'
			. '</section>';
	}
}

function glintide_uptime_widget( $args, $instance ) {
	echo $args['before_widget'];
	echo Glintide_Widget_Uptime::render( $instance );
	echo $args['after_widget'];
}
