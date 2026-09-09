<?php
/**
 * Local approximate lunar phase widget.
 *
 * @package glintide
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Mean synodic cycle from the new moon on 2000-01-06 18:14 UTC. */
function glintide_moon_phase_data( $timestamp ) {
	$cycle = 29.530588853 * DAY_IN_SECONDS;
	$phase = fmod( ( $timestamp - 947182440 ) / $cycle, 1.0 );
	$phase = $phase < 0 ? $phase + 1 : $phase;
	$cosine = cos( 2 * M_PI * $phase );
	$names = array( '新月', '蛾眉月', '上弦月', '盈凸月', '满月', '亏凸月', '下弦月', '残月' );
	return array(
		'name' => $names[ (int) floor( $phase * 8 + 0.5 ) % 8 ],
		'illumination' => (int) round( ( 1 - $cosine ) * 50 ),
		'waxing' => $phase < 0.5,
		'ellipse' => number_format( abs( $cosine ) * 100, 3, '.', '' ),
		'bright' => $cosine < 0,
	);
}

class Glintide_Widget_Moon extends Glintide_Widget {
	public static $id = 'glintide_moon_widget';
	public static $title = 'Glintide 月相';
	public static $description = '本地估算月相和照亮比例，采用北半球示意方向，无需配置接口。';
	public static $classname = 'glintide-time-widget-wrap glintide-mini-widget-wrap';

	public static function fields() {
		return array();
	}

	public static function render( $instance ) {
		$data = glintide_moon_phase_data( time() );
		$classes = 'glintide-moon-disc' . ( $data['waxing'] ? ' is-waxing' : ' is-waning' ) . ( $data['bright'] ? ' is-bright' : '' );
		return '<section class="glintide-time-card glintide-time-card--moon" aria-label="' . esc_attr( '今日月相：' . $data['name'] . '，约照亮 ' . $data['illumination'] . '%' ) . '">'
			. '<div class="glintide-time-card__header"><span>今日月相</span><i class="ri-moon-line" aria-hidden="true"></i></div>'
			. '<div class="' . esc_attr( $classes ) . '" style="--moon-ellipse:' . esc_attr( $data['ellipse'] ) . '%" aria-hidden="true"><span></span></div>'
			. '<div class="glintide-time-card__footer"><strong>' . esc_html( $data['name'] ) . '</strong><span>照亮约 ' . esc_html( $data['illumination'] ) . '%</span></div>'
			. '</section>';
	}
}

function glintide_moon_widget( $args, $instance ) {
	echo $args['before_widget'];
	echo Glintide_Widget_Moon::render( $instance );
	echo $args['after_widget'];
}
