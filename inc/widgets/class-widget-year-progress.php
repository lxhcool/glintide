<?php
/**
 * Calendar-year progress in the WordPress timezone.
 *
 * @package glintide
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Completed calendar days, independent of daylight-saving day lengths. */
function glintide_year_progress_data( DateTimeImmutable $date ) {
	$total = '1' === $date->format( 'L' ) ? 366 : 365;
	$elapsed = (int) $date->format( 'z' );
	return array(
		'year' => $date->format( 'Y' ),
		'elapsed' => $elapsed,
		'total' => $total,
		'remaining' => $total - $elapsed,
		'percent' => round( $elapsed / $total * 100, 1 ),
	);
}

class Glintide_Widget_Year_Progress extends Glintide_Widget {
	public static $id = 'glintide_year_progress_widget';
	public static $title = 'Glintide 年度进度';
	public static $description = '按站点时区显示本年已过去的完整天数比例，剩余天数包含今天。无需配置。';
	public static $classname = 'glintide-time-widget-wrap glintide-mini-widget-wrap';

	public static function fields() {
		return array();
	}

	public static function render( $instance ) {
		$data = glintide_year_progress_data( new DateTimeImmutable( 'now', wp_timezone() ) );
		$percent = number_format( $data['percent'], 1, '.', '' );
		$cells = '';
		for ( $i = 0; $i < 40; $i++ ) {
			$fill = max( 0, min( 1, $data['percent'] / 2.5 - $i ) );
			$cells .= '<span><i style="transform:scaleX(' . esc_attr( number_format( $fill, 3, '.', '' ) ) . ')"></i></span>';
		}
		$label = $data['year'] . ' 年已过 ' . $percent . '%，剩余 ' . $data['remaining'] . ' 天';
		return '<section class="glintide-time-card glintide-time-card--year" aria-label="' . esc_attr( $label ) . '">'
			. '<div class="glintide-time-card__header"><span>年度进度</span><span class="glintide-year-label">' . esc_html( $data['year'] ) . '</span></div>'
			. '<div class="glintide-time-card__number"><strong>' . esc_html( $percent ) . '</strong><span>%</span></div>'
			. '<div class="glintide-year-footer">'
			. '<div class="glintide-year-track" role="progressbar" aria-label="本年已过" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . esc_attr( $percent ) . '"><span class="glintide-year-cells" aria-hidden="true">' . $cells . '</span></div>'
			. '<span>剩余 ' . esc_html( $data['remaining'] ) . ' 天</span></div>'
			. '</section>';
	}
}

function glintide_year_progress_widget( $args, $instance ) {
	echo $args['before_widget'];
	echo Glintide_Widget_Year_Progress::render( $instance );
	echo $args['after_widget'];
}
