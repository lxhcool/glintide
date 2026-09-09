<?php
/**
 * Glintide 友链 / 朋友圈 小工具
 *
 * 一个实例内含两组数据(友链 + 朋友圈),Tab 切换;
 * 垂直列表 + 头像 + 简介 + 跳转图标,点击"加载更多"展开剩余条目。
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Links extends Glintide_Widget {

	public static $id          = 'glintide_links_widget';
	public static $title       = 'Glintide · 友链';
	public static $description = '展示友链和朋友圈(头像 + 名称 + 简介),可拖拽排序,超出默认条目点击"加载更多"展开。';
	public static $classname   = 'glintide-links-widget-wrap';

	public static function fields() {
		$link_fields = array(
			array( 'id' => 'name', 'type' => 'text', 'title' => '名称' ),
			array( 'id' => 'url', 'type' => 'text', 'title' => '链接地址', 'desc' => '请填写完整地址,包含 http / https。' ),
			array(
				'id'           => 'avatar',
				'type'         => 'upload',
				'title'        => '头像 / 图标',
				'library'      => 'image',
				'preview'      => true,
				'button_title' => '选择图片',
				'remove_title' => '移除',
				'desc'         => '可选,留空则自动从链接获取站点图标。',
			),
			array( 'id' => 'desc', 'type' => 'text', 'title' => '一句话简介', 'desc' => '可选。' ),
		);

		return array(
			array(
				'id'                     => 'friends',
				'type'                   => 'group',
				'title'                  => '友链列表',
				'accordion_title_number' => true,
				'accordion_title_auto'   => true,
				'accordion_title_by'     => array( 'name' ),
				'fields'                 => $link_fields,
			),
			array(
				'id'                     => 'moments',
				'type'                   => 'group',
				'title'                  => '朋友圈列表',
				'accordion_title_number' => true,
				'accordion_title_auto'   => true,
				'accordion_title_by'     => array( 'name' ),
				'fields'                 => array_merge(
					array( $link_fields[0] ),
					array(
						array( 'id' => 'url', 'type' => 'text', 'title' => '链接地址', 'desc' => '可选;纯动态可不填。' ),
					),
					array_slice( $link_fields, 2, 2 )
				),
			),
			array(
				'id'      => 'page_size',
				'type'    => 'button_set',
				'title'   => '默认显示数量',
				'options' => array( '5' => '5', '8' => '8', '12' => '12', '16' => '16' ),
				'default' => '8',
				'desc'    => '超出该数量的条目点击"加载更多"展开。',
			),
		);
	}

	/**
	 * 清洗单组数据
	 */
	protected static function sanitize_items( $raw ) {
		$items = array();
		if ( ! is_array( $raw ) ) {
			return $items;
		}
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
			if ( '' === $name ) {
				continue;
			}

			$url = isset( $row['url'] ) ? esc_url_raw( trim( (string) $row['url'] ), array( 'http', 'https' ) ) : '';

			$avatar = isset( $row['avatar'] ) ? $row['avatar'] : '';
			if ( is_array( $avatar ) ) {
				$avatar = $avatar['url'] ?? '';
			}
			$avatar = is_string( $avatar ) ? esc_url_raw( $avatar, array( 'http', 'https' ) ) : '';

			$items[] = array(
				'name'    => $name,
				'url'     => $url,
				'avatar'  => $avatar,
				'desc'    => isset( $row['desc'] ) ? sanitize_text_field( $row['desc'] ) : '',
				'initial' => function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 ),
			);
		}
		return $items;
	}

	/**
	 * 渲染单个列表项
	 */
	protected static function render_item( $item ) {
		$initial = $item['initial'];

		// 头像优先级:用户上传 > 自动从 URL 获取 favicon > 首字 fallback
		if ( $item['avatar'] ) {
			$avatar = '<span class="glintide-link-avatar"><img src="' . esc_url( $item['avatar'] ) . '" alt="' . esc_attr( $initial ) . '" loading="lazy" decoding="async"></span>';
		} elseif ( $item['url'] ) {
			$host = wp_parse_url( $item['url'], PHP_URL_HOST );
			if ( $host ) {
				$favicon = 'https://icons.duckduckgo.com/ip3/' . $host . '.ico';
				$avatar  = '<span class="glintide-link-avatar"><img src="' . esc_url( $favicon ) . '" alt="' . esc_attr( $initial ) . '" loading="lazy" decoding="async"></span>';
			} else {
				$avatar = '<span class="glintide-link-avatar" aria-hidden="true">' . esc_html( $initial ) . '</span>';
			}
		} else {
			$avatar = '<span class="glintide-link-avatar" aria-hidden="true">' . esc_html( $initial ) . '</span>';
		}

		$desc = $item['desc'] ? '<span class="glintide-link-desc">' . esc_html( $item['desc'] ) . '</span>' : '';
		$tip  = $item['desc'] ? $item['name'] . ' · ' . $item['desc'] : $item['name'];

		if ( $item['url'] ) {
			return '<a class="glintide-link" href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener noreferrer"'
				. ' data-glintide-no-pjax title="' . esc_attr( $tip ) . '">'
				. $avatar
				. '<span class="glintide-link-body"><span class="glintide-link-name">' . esc_html( $item['name'] ) . '</span>' . $desc . '</span>'
				. '<i class="ri-external-link-line glintide-friend-go" aria-hidden="true"></i>'
				. '</a>';
		}

		// 无链接(纯动态):用 div 包裹,不显示跳转图标
		return '<div class="glintide-link" title="' . esc_attr( $tip ) . '">'
			. $avatar
			. '<span class="glintide-link-body"><span class="glintide-link-name">' . esc_html( $item['name'] ) . '</span>' . $desc . '</span>'
			. '</div>';
	}

	/**
	 * 渲染一个 panel(友链 / 朋友圈)
	 */
	protected static function render_panel( $key, $items, $limit, $active ) {
		$hidden = $active ? '' : ' hidden';

		// 空数据:Tab 仍展示,切过来显示空状态
		if ( empty( $items ) ) {
			$empty_text = ( 'moments' === $key ) ? '还没有朋友圈内容' : '还没有添加友链';

			return '<div class="glintide-links-panel" data-glintide-panel="' . esc_attr( $key ) . '"' . $hidden . '>'
				. '<div class="glintide-links-empty"><i class="ri-inbox-line" aria-hidden="true"></i>'
				. '<span>' . esc_html( $empty_text ) . '</span></div>'
				. '</div>';
		}

		$head     = array_slice( $items, 0, $limit );
		$rest     = array_slice( $items, $limit );
		$has_more = ! empty( $rest );

		$list_html  = '<div class="glintide-links-list">';
		foreach ( $head as $item ) {
			$list_html .= self::render_item( $item );
		}
		$list_html .= '</div>';

		if ( $has_more ) {
			$list_html .= '<div class="glintide-links-list glintide-links-list--more" hidden>';
			foreach ( $rest as $item ) {
				$list_html .= self::render_item( $item );
			}
			$list_html .= '</div>';
		}

		$load_btn = $has_more ? '<button type="button" class="glintide-links-load" data-glintide-links-more>加载更多</button>' : '';

		return '<div class="glintide-links-panel" data-glintide-panel="' . esc_attr( $key ) . '"' . $hidden . '>'
			. $list_html
			. $load_btn
			. '</div>';
	}

	public static function render( $instance ) {
		$instance = is_array( $instance ) ? $instance : array();

		$friends = self::sanitize_items( isset( $instance['friends'] ) && is_array( $instance['friends'] ) ? $instance['friends'] : array() );
		$moments = self::sanitize_items( isset( $instance['moments'] ) && is_array( $instance['moments'] ) ? $instance['moments'] : array() );

		$page_size = isset( $instance['page_size'] ) && in_array( (string) $instance['page_size'], array( '5', '8', '12', '16' ), true )
			? (int) $instance['page_size']
			: 8;

		// 两个 Tab 始终展示;某组为空时切换到该组显示空状态
		$tabs  = '<div class="glintide-links-tabs" role="tablist">';
		$tabs .= '<button type="button" class="glintide-links-tab is-active" data-glintide-tab="friends" role="tab" aria-selected="true">友链</button>';
		$tabs .= '<button type="button" class="glintide-links-tab" data-glintide-tab="moments" role="tab" aria-selected="false">朋友圈</button>';
		$tabs .= '</div>';

		$panels  = self::render_panel( 'friends', $friends, $page_size, true );
		$panels .= self::render_panel( 'moments', $moments, $page_size, false );

		// 内联交互脚本:Tab 切换 + 加载更多。
		// 事件委托到 document:widget 重新渲染(如 PJAX 换页)后旧监听依然有效,
		// 因为 closest() 是点击时动态查找的;全局标记避免重复叠加监听。
		$script = '<script>(function(){'
			. 'if(window.__glintideLinksBound)return;window.__glintideLinksBound=true;'
			. 'document.addEventListener("click",function(e){'
			. 'var tab=e.target.closest(".glintide-links-tab");if(tab){'
			. 'e.preventDefault();var w=tab.closest("[data-glintide-links]");if(!w)return;'
			. 'var key=tab.getAttribute("data-glintide-tab");'
			. 'w.querySelectorAll(".glintide-links-tab").forEach(function(t){var a=t===tab;t.classList.toggle("is-active",a);t.setAttribute("aria-selected",a?"true":"false")});'
			. 'w.querySelectorAll("[data-glintide-panel]").forEach(function(p){p.hidden=p.getAttribute("data-glintide-panel")!==key});'
			. 'return}'
			. 'var more=e.target.closest("[data-glintide-links-more]");if(more){'
			. 'e.preventDefault();var w=more.closest("[data-glintide-links]");if(!w)return;'
			. 'var extras=more.parentNode.querySelectorAll(".glintide-links-list--more");'
			. 'extras.forEach(function(el){el.hidden=false});'
			. 'w.classList.add("is-expanded");more.hidden=true;}'
			. '});'
			. '})();</script>';

		return '<div class="glintide-links" data-glintide-links>' . $tabs . $panels . $script . '</div>';
	}
}

function glintide_links_widget( $args, $instance ) {
	echo $args['before_widget'];
	echo Glintide_Widget_Links::render( $instance );
	echo $args['after_widget'];
}