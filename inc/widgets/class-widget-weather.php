<?php
/**
 * 小工具:和风天气
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! function_exists( 'glintide_weather_normalize_host' ) ) {
	/**
	 * 标准化和风天气 API Host。
	 *
	 * @param mixed $value API Host。
	 * @return string
	 */
	function glintide_weather_normalize_host( $value ) {
		$value = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
		$value = preg_replace( '#^https?://#i', '', trim( $value ) );
		$value = trim( $value, "/ \t\n\r\0\x0B" );

		if ( ! $value || ! preg_match( '/^[a-z0-9.-]+(?::[0-9]{1,5})?$/i', $value ) ) {
			return '';
		}

		return strtolower( $value );
	}
}

if ( ! function_exists( 'glintide_weather_normalize_coordinate' ) ) {
	/**
	 * 标准化经纬度。
	 *
	 * @param mixed $value 坐标值。
	 * @param float $minimum 最小值。
	 * @param float $maximum 最大值。
	 * @return string
	 */
	function glintide_weather_normalize_coordinate( $value, $minimum, $maximum ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';

		if ( '' === $value || ! is_numeric( $value ) ) {
			return '';
		}

		$value = (float) $value;
		if ( $value < $minimum || $value > $maximum ) {
			return '';
		}

		return rtrim( rtrim( number_format( $value, 6, '.', '' ), '0' ), '.' );
	}
}

if ( ! function_exists( 'glintide_weather_request' ) ) {
	/**
	 * 请求和风天气并缓存成功响应。
	 *
	 * @param string $url 请求地址。
	 * @param string $api_key API Key。
	 * @param string $cache_key 缓存键。
	 * @return array|WP_Error
	 */
	function glintide_weather_request( $url, $api_key, $cache_key ) {
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 6,
				'decompress' => true,
				'headers'    => array(
					'Accept'          => 'application/json',
					'Accept-Encoding' => 'gzip',
					'X-QW-Api-Key'   => $api_key,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status < 200 || $status >= 300 || ! is_array( $body ) ) {
			return new WP_Error( 'glintide_weather_request_failed', '天气接口暂时不可用。' );
		}

		set_transient( $cache_key, $body, 15 * MINUTE_IN_SECONDS );

		return $body;
	}
}

if ( ! function_exists( 'glintide_weather_resolve_location' ) ) {
	/**
	 * 使用和风 GeoAPI 将城市名称解析成坐标。
	 *
	 * @param string $host API Host。
	 * @param string $api_key API Key。
	 * @param string $location 城市名称或 LocationID。
	 * @param string $administrative_area 上级行政区。
	 * @return array|WP_Error
	 */
	function glintide_weather_resolve_location( $host, $api_key, $location, $administrative_area = '' ) {
		$location            = is_scalar( $location ) ? trim( sanitize_text_field( (string) $location ) ) : '';
		$administrative_area = is_scalar( $administrative_area ) ? trim( sanitize_text_field( (string) $administrative_area ) ) : '';

		if ( ! $location ) {
			return array();
		}

		$query_args = array(
			'location' => $location,
			'lang'     => 'zh',
			'number'   => 1,
		);
		if ( $administrative_area ) {
			$query_args['adm'] = $administrative_area;
		}

		$query = http_build_query( $query_args, '', '&', PHP_QUERY_RFC3986 );
		$data  = glintide_weather_request(
			'https://' . $host . '/geo/v2/city/lookup?' . $query,
			$api_key,
			'glintide_weather_location_' . md5( implode( '|', array( $host, $api_key, $location, $administrative_area ) ) )
		);

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$locations = isset( $data['location'] ) && is_array( $data['location'] ) ? $data['location'] : array();
		foreach ( $locations as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$latitude  = glintide_weather_normalize_coordinate( $item['lat'] ?? '', -90, 90 );
			$longitude = glintide_weather_normalize_coordinate( $item['lon'] ?? '', -180, 180 );
			if ( '' === $latitude || '' === $longitude ) {
				continue;
			}

			$name = isset( $item['name'] ) ? sanitize_text_field( (string) $item['name'] ) : $location;
			$adm2 = isset( $item['adm2'] ) ? sanitize_text_field( (string) $item['adm2'] ) : '';
			if ( $adm2 && $adm2 !== $name ) {
				$name = $adm2 . ' · ' . $name;
			}

			return array(
				'latitude'  => $latitude,
				'longitude' => $longitude,
				'name'      => $name,
			);
		}

		return new WP_Error( 'glintide_weather_location_not_found', '没有找到这个城市，请检查名称或所属行政区。' );
	}
}


if ( ! function_exists( 'glintide_weather_get_data' ) ) {
	/**
	 * 获取实况与当天温度范围。
	 *
	 * @param array $instance 小工具配置。
	 * @return array|WP_Error
	 */
	function glintide_weather_get_data( $instance ) {
		$instance       = is_array( $instance ) ? $instance : array();
		$api_key_value  = $instance['api_key'] ?? '';
		$location_value = $instance['location_name'] ?? '';
		$adm_value      = $instance['location_adm'] ?? '';
		$host           = glintide_weather_normalize_host( $instance['api_host'] ?? '' );
		$api_key        = is_scalar( $api_key_value ) ? trim( sanitize_text_field( (string) $api_key_value ) ) : '';
		$location       = is_scalar( $location_value ) ? trim( sanitize_text_field( (string) $location_value ) ) : '';
		$adm            = is_scalar( $adm_value ) ? trim( sanitize_text_field( (string) $adm_value ) ) : '';
		$latitude  = glintide_weather_normalize_coordinate( $instance['latitude'] ?? '', -90, 90 );
		$longitude = glintide_weather_normalize_coordinate( $instance['longitude'] ?? '', -180, 180 );

		if ( ! $host || ! $api_key ) {
			return new WP_Error( 'glintide_weather_missing_config', '请先配置和风天气 API Host 与 API Key。' );
		}

		if ( $location ) {
			$resolved = glintide_weather_resolve_location( $host, $api_key, $location, $adm );
			if ( is_wp_error( $resolved ) ) {
				return $resolved;
			}

			$latitude  = $resolved['latitude'];
			$longitude = $resolved['longitude'];
			$location  = $resolved['name'];
		} elseif ( '' === $latitude || '' === $longitude ) {
			return new WP_Error( 'glintide_weather_missing_config', '请填写城市名称，或填写备用经纬度。' );
		}

		$base_url  = 'https://' . $host;
		$query     = http_build_query(
			array(
				'lang'      => 'zh',
				'localTime' => 'true',
			),
			'',
			'&',
			PHP_QUERY_RFC3986
		);
		$cache_key = md5( implode( '|', array( $host, $api_key, $latitude, $longitude ) ) );

		$current = glintide_weather_request(
			$base_url . '/weather/v1/current/' . rawurlencode( $latitude ) . '/' . rawurlencode( $longitude ) . '?' . $query,
			$api_key,
			'glintide_weather_current_' . $cache_key
		);

		if ( is_wp_error( $current ) ) {
			return $current;
		}

		$daily = glintide_weather_request(
			$base_url . '/weather/v1/daily/' . rawurlencode( $latitude ) . '/' . rawurlencode( $longitude ) . '?' . $query . '&days=1',
			$api_key,
			'glintide_weather_daily_' . $cache_key
		);

		return array(
			'current'       => $current,
			'daily'         => is_wp_error( $daily ) ? array() : $daily,
			'location_name' => $location,
		);
	}
}

if ( ! function_exists( 'glintide_weather_temperature_text' ) ) {
	/**
	 * 格式化温度数值。
	 *
	 * @param mixed $value 温度值。
	 * @return string
	 */
	function glintide_weather_temperature_text( $value ) {
		return is_numeric( $value ) ? (string) round( (float) $value ) : '--';
	}
}

if ( ! function_exists( 'glintide_weather_icon_class' ) ) {
	/**
	 * 将和风天气代码映射到主题现有 iconfont。
	 *
	 * @param mixed $code 和风天气现象代码。
	 * @return string
	 */
	function glintide_weather_icon_class( $code ) {
		$code = preg_replace( '/[^0-9]/', '', (string) $code );
		$code = (int) $code;

		if ( 100 === $code ) {
			return 'ri-sun-line';
		}
		if ( 150 === $code ) {
			return 'ri-moon-clear-line';
		}
		if ( $code >= 151 && $code <= 154 ) {
			return 'ri-moon-cloudy-line';
		}
		if ( $code >= 101 && $code <= 103 ) {
			return 'ri-sun-cloudy-line';
		}
		if ( 104 === $code ) {
			return 'ri-cloudy-line';
		}
		if ( $code >= 302 && $code <= 304 ) {
			return 'ri-thunderstorms-line';
		}
		if ( $code >= 300 && $code <= 399 ) {
			return 'ri-rainy-line';
		}
		if ( $code >= 400 && $code <= 499 ) {
			return 'ri-snowy-line';
		}
		if ( $code >= 500 && $code <= 515 ) {
			return 'ri-mist-line';
		}
		if ( 900 === $code ) {
			return 'ri-temp-hot-line';
		}
		if ( 901 === $code ) {
			return 'ri-temp-cold-line';
		}
		if ( 999 === $code ) {
			return 'ri-cloud-off-line';
		}

		return 'ri-cloudy-line';
	}
}

if ( ! function_exists( 'glintide_weather_empty_html' ) ) {
	/**
	 * 输出天气小工具的空状态。
	 *
	 * @param string $message 提示文案。
	 * @return string
	 */
	function glintide_weather_empty_html( $message ) {
		return '<div class="glintide-weather-widget glintide-weather-widget--empty" role="status">'
			. glintide_widget_notice( $message )
			. '</div>';
	}
}

class Glintide_Widget_Weather extends Glintide_Widget {

	public static $id          = 'glintide_weather_widget';
	public static $title       = 'Glintide 天气';
	public static $description = '和风天气实时天气方卡，服务端缓存 API 数据。';
	public static $classname   = 'glintide-weather-widget-wrap glintide-mini-widget-wrap';

	public static function fields() {
		return array(
			array(
				'id'      => 'location_name',
				'type'    => 'text',
				'title'   => '城市/地区名称',
				'default' => '上海',
				'desc'    => '支持城市名、区县名或和风 LocationID，后台会自动解析经纬度。',
			),
			array(
				'id'    => 'location_adm',
				'type'  => 'text',
				'title' => '所属行政区（可选）',
				'desc'  => '同名城市较多时填写，例如“北京”或“广东”。',
			),
			array(
				'id'      => 'latitude',
				'type'    => 'text',
				'title'   => '备用纬度（可选）',
				'default' => '31.2304',
			),
			array(
				'id'      => 'longitude',
				'type'    => 'text',
				'title'   => '备用经度（可选）',
				'default' => '121.4737',
			),
			array(
				'id'    => 'api_host',
				'type'  => 'text',
				'title' => '和风 API Host',
				'desc'  => '填写控制台“设置”里的专用 API Host，例如 abcxyz.qweatherapi.com，不要填完整 URL。',
			),
			array(
				'id'    => 'api_key',
				'type'  => 'text',
				'title' => '和风 API Key',
				'desc'  => '仅保存在 WordPress 小工具配置中，由服务器请求和风天气。',
			),
		);
	}

	public static function render( $instance ) {
		$payload = glintide_weather_get_data( $instance );

		if ( is_wp_error( $payload ) ) {
			$message = in_array( $payload->get_error_code(), array( 'glintide_weather_missing_config', 'glintide_weather_location_not_found' ), true )
				? $payload->get_error_message()
				: '天气数据暂时不可用，请检查和风 API 配置。';

			return glintide_weather_empty_html( $message );
		}

		$current       = isset( $payload['current'] ) && is_array( $payload['current'] ) ? $payload['current'] : array();
		$condition     = isset( $current['condition'] ) && is_array( $current['condition'] ) ? $current['condition'] : array();
		$temp          = isset( $current['temperature']['value'] ) ? glintide_weather_temperature_text( $current['temperature']['value'] ) : '--';
		$condition_text = isset( $condition['text'] ) ? sanitize_text_field( (string) $condition['text'] ) : '天气状况';
		$icon_code     = $condition['code'] ?? '';
		$icon_class    = glintide_weather_icon_class( $icon_code );
		$icon_modifier = in_array( $icon_class, array( 'ri-sun-line', 'ri-sun-cloudy-line' ), true ) ? ' glintide-weather-icon--sun' : '';
		$daily         = isset( $payload['daily']['days'][0] ) && is_array( $payload['daily']['days'][0] ) ? $payload['daily']['days'][0] : array();
		$minimum       = isset( $daily['temperatureMin']['value'] ) ? glintide_weather_temperature_text( $daily['temperatureMin']['value'] ) : '--';
		$maximum       = isset( $daily['temperatureMax']['value'] ) ? glintide_weather_temperature_text( $daily['temperatureMax']['value'] ) : '--';
		$location      = ! empty( $payload['location_name'] ) ? sanitize_text_field( $payload['location_name'] ) : '天气';
		$night         = (int) $icon_code >= 150 && (int) $icon_code <= 154;

		$effects = array(
			'ri-sun-line'          => 'sun',
			'ri-moon-clear-line'   => 'moon',
			'ri-sun-cloudy-line'   => 'cloud',
			'ri-moon-cloudy-line'  => 'cloud',
			'ri-cloudy-line'       => 'cloud',
			'ri-rainy-line'        => 'rain',
			'ri-thunderstorms-line'=> 'rain',
			'ri-snowy-line'        => 'snow',
			'ri-mist-line'         => 'mist',
		);
		$effect = isset( $effects[ $icon_class ] ) && is_numeric( $icon_code ) && 999 !== (int) $icon_code ? $effects[ $icon_class ] : '';
		$scene = '';
		if ( $effect ) {
			$scene = '<div class="glintide-weather-scene glintide-weather-scene--' . esc_attr( $effect ) . '" aria-hidden="true">';
			if ( in_array( $effect, array( 'rain', 'snow' ), true ) ) {
				for ( $i = 0; $i < 8; $i++ ) {
					$scene .= '<span class="glintide-weather-particle"></span>';
				}
			} elseif ( 'cloud' === $effect ) {
				$scene .= '<i class="ri-cloud-fill"></i><i class="ri-cloud-fill"></i>';
			} elseif ( 'mist' === $effect ) {
				$scene .= '<i class="ri-mist-line"></i>';
			}
			$scene .= '</div>';
		}

		$aria = sprintf( '%s天气：%s，%s摄氏度', $location, $condition_text, $temp );

		return '<section class="glintide-weather-widget' . ( $night ? ' glintide-weather-widget--night' : '' ) . '" data-weather-effect="' . esc_attr( $effect ) . '" aria-label="' . esc_attr( $aria ) . '">'
			. $scene
			. '<div class="glintide-weather-header"><span class="glintide-weather-location">' . esc_html( $location ) . '</span><span class="glintide-weather-live">实时</span></div>'
			. '<div class="glintide-weather-main"><div class="glintide-weather-temperature"><strong>' . esc_html( $temp ) . '</strong><span>°C</span></div>'
			. '<div class="glintide-weather-icon' . esc_attr( $icon_modifier ) . '" aria-hidden="true"><i class="' . esc_attr( $icon_class ) . '"></i></div>'
			. '<p class="glintide-weather-condition">' . esc_html( $condition_text ) . '</p></div>'
			. '<div class="glintide-weather-range"><span>最低<strong>' . esc_html( $minimum ) . '°C</strong></span><span>最高<strong>' . esc_html( $maximum ) . '°C</strong></span></div>'
			. '</section>';
	}
}

if ( ! function_exists( 'glintide_weather_widget' ) ) {
	function glintide_weather_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Weather::render( $instance );
		echo $args['after_widget'];
	}
}
