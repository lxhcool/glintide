<?php
/**
 * 小工具:倒数日组
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! function_exists( 'glintide_countdown_visual_fields' ) ) {
	/**
	 * 倒数/日期卡的视觉字段。
	 *
	 * @return array
	 */
	function glintide_countdown_visual_fields() {
		return array(
			array(
				'id'    => 'background_color',
				'type'  => 'color',
				'title' => '背景颜色',
				'desc'  => '留空时使用默认主题色。',
			),
			array(
				'id'    => 'text_color',
				'type'  => 'color',
				'title' => '文字颜色',
				'desc'  => '留空时使用默认文字颜色。',
			),
			array(
				'id'           => 'background_image',
				'type'         => 'upload',
				'title'        => '背景图片',
				'library'      => 'image',
				'button_title' => '上传图片',
				'remove_title' => '删除图片',
				'preview'      => true,
				'desc'         => '可选；会以封面方式铺满卡片。',
			),
			array(
				'id'    => 'gradient_start',
				'type'  => 'color',
				'title' => '渐变起始色',
			),
			array(
				'id'    => 'gradient_end',
				'type'  => 'color',
				'title' => '渐变结束色',
			),
			array(
				'id'      => 'gradient_direction',
				'type'    => 'button_set',
				'title'   => '渐变方向',
				'options' => array(
					'to right'        => '向右',
					'to bottom'       => '向下',
					'to bottom right' => '右下',
					'135deg'          => '斜向',
				),
				'default' => '135deg',
			),
		);
	}
}

if ( ! function_exists( 'glintide_countdown_widget_fields' ) ) {
	/**
	 * 倒数卡的公共字段。
	 *
	 * @return array
	 */
	function glintide_countdown_widget_fields() {
		return array_merge(
			array(
				array(
					'id'      => 'countdown_label',
					'type'    => 'text',
					'title'   => '倒数说明',
					'default' => '距离明年还有',
				),
				array(
					'id'       => 'target_date',
					'type'     => 'date',
					'title'    => '目标日期',
					'settings' => array(
						'dateFormat'  => 'yy-mm-dd',
						'changeMonth' => true,
						'changeYear'  => true,
					),
					'desc'     => '留空时默认倒数到明年 1 月 1 日。',
				),
			),
			glintide_countdown_visual_fields()
		);
	}
}

if ( ! function_exists( 'glintide_countdown_normalize_target' ) ) {
	/**
	 * 标准化倒数目标日期。
	 *
	 * @param mixed $value 日期值。
	 * @return string
	 */
	function glintide_countdown_normalize_target( $value ) {
		$value   = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
		$matches = array();

		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches ) && checkdate( intval( $matches[2] ), intval( $matches[3] ), intval( $matches[1] ) ) ) {
			return $value;
		}

		$timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
		$now      = new DateTimeImmutable( 'now', $timezone );

		return $now->setDate( intval( $now->format( 'Y' ) ) + 1, 1, 1 )->format( 'Y-m-d' );
	}
}

if ( ! function_exists( 'glintide_countdown_media_url' ) ) {
	/**
	 * 解析小工具图片字段。
	 *
	 * @param mixed $value 图片字段值。
	 * @return string
	 */
	function glintide_countdown_media_url( $value ) {
		if ( is_array( $value ) ) {
			$value = $value['url'] ?? '';
		} elseif ( is_numeric( $value ) ) {
			$value = wp_get_attachment_image_url( absint( $value ), 'large' );
		}

		return is_string( $value ) ? esc_url_raw( $value ) : '';
	}
}

if ( ! function_exists( 'glintide_countdown_card_style' ) ) {
	/**
	 * 构建经白名单处理的卡片视觉变量。
	 *
	 * @param array $instance 小工具配置。
	 * @return string
	 */
	function glintide_countdown_card_style( $instance ) {
		$instance   = is_array( $instance ) ? $instance : array();
		$styles     = array();
		$background = sanitize_hex_color( $instance['background_color'] ?? '' );
		$text       = sanitize_hex_color( $instance['text_color'] ?? '' );
		$image      = glintide_countdown_media_url( $instance['background_image'] ?? '' );
		$start      = sanitize_hex_color( $instance['gradient_start'] ?? '' );
		$end        = sanitize_hex_color( $instance['gradient_end'] ?? '' );
		$directions = array( 'to right', 'to bottom', 'to bottom right', '135deg' );
		$direction  = isset( $instance['gradient_direction'] ) && in_array( $instance['gradient_direction'], $directions, true ) ? $instance['gradient_direction'] : '135deg';

		if ( $background ) {
			$styles[] = '--glintide-countdown-card-custom-bg:' . $background;
		}
		if ( $text ) {
			$styles[] = '--glintide-countdown-card-custom-text:' . $text;
		}
		if ( $image ) {
			$styles[] = '--glintide-countdown-card-image:url("' . $image . '")';
		}
		if ( $start && $end ) {
			$styles[] = '--glintide-countdown-card-gradient:linear-gradient(' . $direction . ',' . $start . ',' . $end . ')';
		}

		return $styles ? implode( ';', $styles ) . ';' : '';
	}
}

if ( ! function_exists( 'glintide_countdown_card_html' ) ) {
	/**
	 * 输出倒数卡。
	 *
	 * @param string $label 倒数说明。
	 * @param string $style 卡片样式变量。
	 * @return string
	 */
	function glintide_countdown_card_html( $label, $style = '' ) {
		$style_attr = $style ? ' style="' . esc_attr( $style ) . '"' : '';

		return '<section class="glintide-countdown-card glintide-countdown-card--countdown"' . $style_attr . ' aria-label="' . esc_attr( $label ) . '">'
			. '<p class="glintide-countdown-label">' . esc_html( $label ) . '</p>'
			. '<div class="glintide-countdown-number-row"><strong class="glintide-countdown-number" data-countdown-days>0</strong><span class="glintide-countdown-unit">天</span></div>'
			. '<div class="glintide-countdown-date-meta"><time data-countdown-date>---</time><span data-countdown-weekday>---</span></div>'
			. '<div class="glintide-countdown-progress" aria-hidden="true"><span class="glintide-countdown-progress-track"><span class="glintide-countdown-progress-fill"></span><i class="glintide-countdown-progress-dot"></i></span></div>'
			. '</section>';
	}
}

if ( ! function_exists( 'glintide_calendar_card_html' ) ) {
	/**
	 * 输出当前日期卡。
	 *
	 * @param string $style 卡片样式变量。
	 * @return string
	 */
	function glintide_calendar_card_html( $style = '' ) {
		$style_attr = $style ? ' style="' . esc_attr( $style ) . '"' : '';

		return '<section class="glintide-countdown-card glintide-countdown-card--calendar"' . $style_attr . ' aria-label="当前日期">'
			. '<p class="glintide-countdown-month" data-calendar-month>---</p>'
			. '<strong class="glintide-countdown-calendar-day" data-calendar-day>--</strong>'
			. '<p class="glintide-countdown-lunar" data-calendar-lunar>农历日期</p>'
			. '<p class="glintide-countdown-weekday-pill" data-calendar-weekday>---</p>'
			. '</section>';
	}
}

class Glintide_Widget_Countdown extends Glintide_Widget {

	public static $id          = 'glintide_countdown_widget';
	public static $title       = 'Glintide 倒数日组合';
	public static $description = '组合显示倒数卡和当前日期卡；也可以分别添加独立小工具。';
	public static $classname   = 'glintide-countdown-widget-wrap';

	public static function fields() {
		return glintide_countdown_widget_fields();
	}

	public static function render( $instance ) {
		$instance = is_array( $instance ) ? $instance : array();
		$label    = isset( $instance['countdown_label'] ) ? sanitize_text_field( $instance['countdown_label'] ) : '';
		$label    = $label ? $label : '距离明年还有';
		$target   = glintide_countdown_normalize_target( $instance['target_date'] ?? '' );
		$style    = glintide_countdown_card_style( $instance );

		$html  = '<div class="glintide-countdown-widget glintide-countdown-widget--group" data-glintide-countdown data-glintide-calendar data-target-date="' . esc_attr( $target ) . '">';
		$html .= '<div class="glintide-countdown-group">';
		$html .= glintide_countdown_card_html( $label, $style );
		$html .= glintide_calendar_card_html( $style );
		$html .= '</div></div>';

		return $html;
	}
}

class Glintide_Widget_Countdown_Card extends Glintide_Widget {

	public static $id          = 'glintide_countdown_card_widget';
	public static $title       = 'Glintide 倒数卡';
	public static $description = '独立显示一张倒数日卡，可与其他小工具自由组合。';
	public static $classname   = 'glintide-countdown-widget-wrap glintide-mini-widget-wrap';

	public static function fields() {
		return glintide_countdown_widget_fields();
	}

	public static function render( $instance ) {
		$instance = is_array( $instance ) ? $instance : array();
		$label    = isset( $instance['countdown_label'] ) ? sanitize_text_field( $instance['countdown_label'] ) : '';
		$label    = $label ? $label : '距离明年还有';
		$target   = glintide_countdown_normalize_target( $instance['target_date'] ?? '' );
		$style    = glintide_countdown_card_style( $instance );

		return '<div class="glintide-countdown-widget glintide-countdown-widget--single" data-glintide-countdown data-target-date="' . esc_attr( $target ) . '">' . glintide_countdown_card_html( $label, $style ) . '</div>';
	}
}

if ( ! function_exists( 'glintide_countdown_widget' ) ) {
	function glintide_countdown_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Countdown::render( $instance );
		echo $args['after_widget'];
	}
}

if ( ! function_exists( 'glintide_countdown_card_widget' ) ) {
	function glintide_countdown_card_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Countdown_Card::render( $instance );
		echo $args['after_widget'];
	}
}
