<?php
/**
 * 小工具:一言
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Hitokoto extends Glintide_Widget {

	public static $id          = 'glintide_hitokoto_widget';
	public static $title       = 'PPO · 一言';
	public static $description = '调用一言 API 展示随机句子,卡片式带背景图';
	public static $classname   = 'ppo-widget glintide_hitokoto_widget';

	public static function fields() {
		return array(
			array(
				'id'    => 'title',
				'type'  => 'text',
				'title' => '标题',
			),
			array(
				'id'      => 'show_from',
				'type'    => 'switcher',
				'title'   => '显示来源',
				'default' => true,
			),
			array(
				'id'      => 'show_refresh',
				'type'    => 'switcher',
				'title'   => '显示刷新按钮',
				'default' => true,
			),
			array(
				'id'    => 'bg_image',
				'type'  => 'upload',
				'title' => '背景图',
				'desc'  => '留空则使用纯色背景',
			),
			array(
				'id'      => 'overlay_color',
				'type'    => 'color',
				'title'   => '蒙层颜色',
				'default' => '#000000',
			),
			array(
				'id'      => 'overlay_opacity',
				'type'    => 'slider',
				'title'   => '蒙层透明度',
				'desc'    => '数值越大蒙层越深,文字越清晰',
				'min'     => 0,
				'max'     => 100,
				'step'    => 1,
				'unit'    => '%',
				'default' => 55,
			),
			array(
				'id'      => 'card_height',
				'type'    => 'number',
				'title'   => '卡片高度',
				'desc'    => '单位 px,留空或 0 为自适应',
				'default' => 0,
			),
		);
	}

	public static function render( $instance ) {
		$title           = $instance['title'] ?? '';
		$show_from       = ! empty( $instance['show_from'] );
		$show_refresh    = ! empty( $instance['show_refresh'] );
		$bg_image        = $instance['bg_image'] ?? '';
		$overlay_color   = $instance['overlay_color'] ?? '#000000';
		$overlay_opacity = isset( $instance['overlay_opacity'] ) ? intval( $instance['overlay_opacity'] ) : 55;
		$overlay_opacity = max( 0, min( 100, $overlay_opacity ) );
		$card_height     = isset( $instance['card_height'] ) ? absint( $instance['card_height'] ) : 0;

		$uid = 'glintide-hitokoto-' . uniqid();

		$card_style = '';
		if ( $card_height > 0 ) {
			$card_style .= 'min-height:' . $card_height . 'px;';
		}

		$html  = '<div class="glintide-hitokoto-widget glintide-hitokoto-card" id="' . esc_attr( $uid ) . '"' . ( $card_style ? ' style="' . $card_style . '"' : '' ) . '>';
		$html .= '<div class="glintide-hitokoto-bg"' . ( $bg_image ? ' style="background-image:url(' . esc_url( $bg_image ) . ')"' : '' ) . '></div>';
		$html .= '<div class="glintide-hitokoto-overlay" style="background-color:' . esc_attr( $overlay_color ) . ';opacity:' . ( $overlay_opacity / 100 ) . '"></div>';
		if ( $title ) {
			$html .= '<div class="glintide-hitokoto-title">' . esc_html( $title ) . '</div>';
		}
		$html .= '<div class="glintide-hitokoto-content">';
		$html .= '<div class="glintide-hitokoto-text" data-hitokoto-text>加载中...</div>';
		if ( $show_from ) {
			$html .= '<div class="glintide-hitokoto-from" data-hitokoto-from></div>';
		}
		$html .= '</div>';
		if ( $show_refresh ) {
			$html .= '<button type="button" class="glintide-hitokoto-refresh" data-hitokoto-refresh aria-label="换一句"><i class="ri-refresh-line"></i></button>';
		}
		$html .= '</div>';

		$html .= '<script>
	(function(){
		var root = document.getElementById("' . esc_attr( $uid ) . '");
		if (!root) return;
		var textEl = root.querySelector("[data-hitokoto-text]");
		var fromEl = root.querySelector("[data-hitokoto-from]");
		var refreshBtn = root.querySelector("[data-hitokoto-refresh]");
		var loading = false;
		function load(){
			if (loading) return;
			loading = true;
			if (refreshBtn) refreshBtn.classList.add("is-loading");
			fetch("https://v1.hitokoto.cn/?encode=json&charset=utf-8")
				.then(function(r){ return r.json(); })
				.then(function(data){
					if (textEl) textEl.textContent = data.hitokoto || "";
					if (fromEl) {
						var from = data.from || "";
						if (data.from_who) from = data.from_who + " · " + from;
						fromEl.textContent = from ? "—— " + from : "";
					}
				})
				.catch(function(){
					if (textEl) textEl.textContent = "一言获取失败";
				})
				.finally(function(){
					loading = false;
					if (refreshBtn) refreshBtn.classList.remove("is-loading");
				});
		}
		if (refreshBtn) refreshBtn.addEventListener("click", load);
		load();
	})();
	</script>';

		return $html;
	}
}

// 前端渲染函数(CSF 按 widget ID 调用)
if ( ! function_exists( 'glintide_hitokoto_widget' ) ) {
	function glintide_hitokoto_widget( $args, $instance ) {
		echo $args['before_widget'];
		echo Glintide_Widget_Hitokoto::render( $instance );
		echo $args['after_widget'];
	}
}