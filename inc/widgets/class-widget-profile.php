<?php
/**
 * Glintide profile widget.
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Profile extends Glintide_Widget {

	public static $id          = 'glintide_profile_widget';
	public static $title       = 'Glintide 个人信息';
	public static $description = '显示头像、用户名、城市和个性签名。';
	public static $classname   = 'glintide-profile-widget';

	public static function fields() {
		return array(
			array(
				'id'      => 'avatar',
				'type'    => 'upload',
				'title'   => '头像',
				'library' => 'image',
			),
			array(
				'id'    => 'username',
				'type'  => 'text',
				'title' => '用户名',
			),
			array(
				'id'    => 'city',
				'type'  => 'text',
				'title' => '城市',
			),
		);
	}

	public static function render( $instance ) {
		$instance = is_array( $instance ) ? $instance : array();
		$user     = wp_get_current_user();
		$avatar   = isset( $instance['avatar'] ) ? $instance['avatar'] : '';

		if ( is_array( $avatar ) ) {
			$avatar = $avatar['url'] ?? '';
		}

		$avatar = is_string( $avatar ) ? esc_url_raw( $avatar ) : '';
		if ( ! $avatar && $user instanceof WP_User && $user->exists() ) {
			$avatar = glintide_get_avatar_url( $user->ID );
		}
		if ( ! $avatar ) {
			$avatar = glintide_get_default_avatar_url();
		}

		$username = isset( $instance['username'] ) ? sanitize_text_field( $instance['username'] ) : '';
		$username = $username ? $username : ( $user instanceof WP_User && $user->exists() ? $user->display_name : get_bloginfo( 'name' ) );
		$city     = isset( $instance['city'] ) ? sanitize_text_field( $instance['city'] ) : '';
		$uid      = 'glintide-profile-hitokoto-' . wp_rand( 1000, 999999 );

		$html  = '<div class="glintide-profile-card">';
		$html .= '<div class="glintide-profile-main">';
		$html .= '<img class="glintide-profile-avatar" src="' . esc_url( $avatar ) . '" alt="' . esc_attr( $username ) . '" loading="lazy" data-glintide-avatar data-glintide-avatar-fallback="' . esc_url( glintide_get_default_avatar_url() ) . '">';
		$html .= '<div class="glintide-profile-copy">';
		$html .= '<div class="glintide-profile-name">' . esc_html( $username ) . '</div>';
		if ( $city ) {
			$html .= '<div class="glintide-profile-city"><i class="ri-map-pin-2-line" aria-hidden="true"></i><span>' . esc_html( $city ) . '</span></div>';
		}
		$html .= '</div></div>';
		$html .= '<div class="glintide-profile-signature" id="' . esc_attr( $uid ) . '" aria-live="polite">';
		$html .= '<span class="glintide-profile-signature-text">加载中...</span>';
		$html .= '<span class="glintide-profile-progress" aria-hidden="true"><span></span></span>';
		$html .= '</div>';
		$html .= '<script>
(function(){
	var root = document.getElementById("' . esc_js( $uid ) . '");
	if (!root) return;
	var text = root.querySelector(".glintide-profile-signature-text");
	var progress = root.querySelector(".glintide-profile-progress");
	var duration = 30000;
	var timer = null;
	var loading = false;

	function restartProgress() {
		if (!progress) return;
		progress.classList.remove("is-running");
		void progress.offsetWidth;
		progress.classList.add("is-running");
	}

	function load() {
		if (loading) return;
		loading = true;
		fetch("https://v1.hitokoto.cn/?encode=json&charset=utf-8")
			.then(function(response){ return response.json(); })
			.then(function(data){
				if (text) text.textContent = data.hitokoto || "一言获取失败";
				restartProgress();
				window.clearTimeout(timer);
				timer = window.setTimeout(load, duration);
			})
			.catch(function(){
				if (text) text.textContent = "一言获取失败";
				window.clearTimeout(timer);
				timer = window.setTimeout(load, duration);
			})
			.finally(function(){ loading = false; });
	}

	load();
}());
</script>';
		$html .= '</div>';

		return $html;
	}
}

if ( ! function_exists( 'glintide_profile_widget' ) ) {
	function glintide_profile_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Profile::render( $instance );
		echo $args['after_widget'];
	}
}
