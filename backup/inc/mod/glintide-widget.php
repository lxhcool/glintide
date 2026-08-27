<?php
/**
 * Glintide 小工具渲染函数
 *
 * 移植自参考主题 glintide-widget.php,适配新主题:
 * - 去掉圈子推荐(依赖 moments 功能)
 * - 音乐播放器重新设计:网易云歌单解析
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * 小工具标题
 */
function glintide_widget_title( $title ) {
	$title = is_scalar( $title ) ? trim( (string) $title ) : '';

	return $title !== '' ? '<div class="wid_title">' . esc_html( $title ) . '</div>' : '';
}

/**
 * 小工具提示
 */
function glintide_widget_notice( $text ) {
	return '<div class="glintide-widget-notice wid-item"><i class="ri-information-line"></i><span>' . esc_html( $text ) . '</span></div>';
}

/**
 * 数字缩写(1.2k / 3.4M)
 */
function glintide_k_m_i( $num ) {
	if ( $num >= 1000000 ) {
		return round( $num / 1000000, 1 ) . 'M';
	} elseif ( $num >= 1000 ) {
		return round( $num / 1000, 1 ) . 'k';
	}
	return $num;
}

/**
 * 获取文章缩略图(特色图 → 首图 → 默认图)
 */
function glintide_get_thumb( $post_id ) {
	$thumb = get_the_post_thumbnail_url( $post_id, 'medium' );
	if ( $thumb ) {
		return $thumb;
	}

	$post = get_post( $post_id );
	if ( $post && preg_match( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches ) ) {
		return $matches[1];
	}

	return '';
}

/**
 * 用户头像(优先自定义头像,回退 Gravatar)
 */
function glintide_get_avatar_url( $user_id ) {
	$custom = get_user_meta( $user_id, 'custom_avatar', true );
	if ( $custom ) {
		return $custom;
	}

	$avatar = get_avatar_url( $user_id, array( 'size' => 96 ) );
	return $avatar ? $avatar : '';
}

/**
 * 站点 Logo(图片或文字)
 */
function glintide_site_logo_html() {
	$logo = glintide_get_option( 'site_logo', '' );
	if ( is_array( $logo ) ) {
		$logo = $logo['url'] ?? '';
	}

	if ( $logo ) {
		return '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
	}

	$text_logo = glintide_get_option( 'logo_text', '' );
	$text_logo = $text_logo ? $text_logo : get_bloginfo( 'name' );

	return '<h3>' . esc_html( $text_logo ) . '</h3>';
}

/* ================================================================
 * 广告位
 * ================================================================ */
function glintide_gg_banner_func( $data ) {
	$image  = $data['gg_image'] ?? '';
	$link   = $data['gg_link'] ?? '#';
	$target = ! empty( $data['gg_target'] ) ? 'target="_blank"' : '';
	$title  = $data['title'] ?? '';

	if ( empty( $image ) ) {
		return glintide_widget_notice( '请配置广告图片' );
	}

	$html  = '<div class="glintide-gg-banner">';
	$html .= glintide_widget_title( $title );
	$html .= '<div class="glintide-gg-image wid-item">';
	$html .= '<a href="' . esc_url( $link ) . '" ' . $target . '>';
	$html .= '<img src="' . esc_url( $image ) . '" alt="广告">';
	$html .= '</a>';
	$html .= '</div>';
	$html .= '</div>';

	return $html;
}

/* ================================================================
 * 图标网格
 * ================================================================ */
function glintide_icon_grid_func( $data ) {
	$title   = $data['title'] ?? '';
	$per_row = $data['per_row'] ?? '4';
	$list    = $data['icon_list'] ?? array();

	if ( empty( $list ) ) {
		return glintide_widget_notice( '请配置图标网格' );
	}

	$html  = '<div class="glintide-icon-grid">';
	$html .= glintide_widget_title( $title );
	$html .= '<div class="glintide-icon-grid-box wid-item glintide-icon-col-' . absint( $per_row ) . '">';
	$has_icon = false;

	foreach ( $list as $item ) {
		if ( empty( $item['title'] ) ) {
			continue;
		}

		$link   = $item['link'] ?? '#';
		$target = ! empty( $item['target'] ) ? 'target="_blank"' : '';
		$icon_html = '';

		if ( ! empty( $item['icon_type'] ) && $item['icon_type'] === 'image' && ! empty( $item['image'] ) ) {
			$icon_html = '<img src="' . esc_url( $item['image'] ) . '" alt="' . esc_attr( $item['title'] ) . '">';
		} elseif ( ! empty( $item['icon'] ) ) {
			$icon_html = '<i class="' . esc_attr( $item['icon'] ) . '"></i>';
		}

		if ( empty( $icon_html ) ) {
			continue;
		}
		$has_icon = true;

		$bg_style   = '';
		$icon_style = '';
		if ( ! empty( $item['icon_type'] ) && $item['icon_type'] === 'icon' ) {
			if ( ! empty( $item['icon_color'] ) ) {
				$icon_style = 'color: ' . esc_attr( $item['icon_color'] ) . ';';
			}

			$bg = $item['bg-color'] ?? array();
			if ( ! empty( $bg ) && is_array( $bg ) ) {
				$gradient_color = $bg['background-gradient-color'] ?? '';
				$gradient_dir   = $bg['background-gradient-direction'] ?? 'to bottom';
				$solid_color    = $bg['background-color'] ?? '';

				if ( ! empty( $gradient_color ) && ! empty( $gradient_dir ) ) {
					$bg_style = 'background: linear-gradient(' . esc_attr( $gradient_dir ) . ', ' . esc_attr( $solid_color ) . ' 0%, ' . esc_attr( $gradient_color ) . ' 100%);';
				} elseif ( ! empty( $solid_color ) ) {
					$bg_style = 'background-color: ' . esc_attr( $solid_color ) . ';';
				}
			}
		}

		$html .= '<a href="' . esc_url( $link ) . '" ' . $target . ' class="glintide-icon-item">';
		$html .= '<div class="glintide-icon-wrap"' . ( $bg_style ? ' style="' . $bg_style . '"' : '' ) . '>';
		$html .= $icon_html;
		$html .= '</div>';
		$html .= '<div class="glintide-icon-title">' . esc_html( $item['title'] ) . '</div>';
		$html .= '</a>';
	}

	if ( ! $has_icon ) {
		$html .= glintide_widget_notice( '请配置有效的图标项' );
	}

	$html .= '</div></div>';

	return $html;
}

/* ================================================================
 * 文章列表
 * ================================================================ */
function glintide_post_list_get_posts( $post_num, $order_by ) {
	$post_num = max( 1, absint( $post_num ) );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $post_num,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $order_by === 'comments' ) {
		$args['orderby'] = array(
			'comment_count' => 'DESC',
			'date'          => 'DESC',
		);
		return get_posts( $args );
	}

	$meta_keys = array(
		'views'     => 'views',
		'likes'     => 'likes_count',
		'favorites' => 'collect_count',
	);
	$meta_key = $meta_keys[ $order_by ] ?? '';

	if ( empty( $meta_key ) ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
		return get_posts( $args );
	}

	global $wpdb;

	$orderby_posts_by_meta = function( $clauses ) use ( $wpdb, $meta_key ) {
		$alias = 'glintide_post_list_order_meta';

		if ( strpos( $clauses['join'], " {$alias} " ) === false ) {
			$clauses['join'] .= $wpdb->prepare(
				" LEFT JOIN {$wpdb->postmeta} AS {$alias} ON ({$wpdb->posts}.ID = {$alias}.post_id AND {$alias}.meta_key = %s)",
				$meta_key
			);
		}

		$clauses['orderby'] = "CAST(COALESCE({$alias}.meta_value, '0') AS UNSIGNED) DESC, {$wpdb->posts}.post_date DESC";

		return $clauses;
	};

	add_filter( 'posts_clauses', $orderby_posts_by_meta );
	$posts = get_posts( $args );
	remove_filter( 'posts_clauses', $orderby_posts_by_meta );

	return $posts;
}

function glintide_post_meta_html( $post_id, $show_meta ) {
	$meta = array();

	if ( in_array( 'views', $show_meta ) ) {
		$views = absint( get_post_meta( $post_id, 'views', true ) );
		$meta[] = '<span><i class="ri-eye-line"></i>' . glintide_k_m_i( $views ) . '</span>';
	}

	if ( in_array( 'comments', $show_meta ) ) {
		$comments = absint( get_comments_number( $post_id ) );
		$meta[] = '<span><i class="ri-chat-1-line"></i>' . glintide_k_m_i( $comments ) . '</span>';
	}

	if ( in_array( 'likes', $show_meta ) ) {
		$likes = absint( get_post_meta( $post_id, 'likes_count', true ) );
		$meta[] = '<span><i class="ri-heart-3-line"></i>' . glintide_k_m_i( $likes ) . '</span>';
	}

	if ( in_array( 'date', $show_meta ) ) {
		$meta[] = '<span><i class="ri-calendar-line"></i>' . esc_html( get_the_date( 'Y-m-d', $post_id ) ) . '</span>';
	}

	return implode( '', $meta );
}

function glintide_post_list_func( $data ) {
	$title      = $data['title'] ?? '';
	$list_style = $data['list_style'] ?? 'normal';
	$order_by   = $data['order_by'] ?? 'views';
	$post_num   = isset( $data['post_num'] ) ? absint( $data['post_num'] ) : 5;
	$show_meta  = isset( $data['show_meta'] ) ? $data['show_meta'] : array( 'views', 'date' );
	$show_meta  = is_array( $show_meta ) ? $show_meta : array();

	$posts = glintide_post_list_get_posts( $post_num, $order_by );

	if ( empty( $posts ) ) {
		return glintide_widget_notice( '暂无可显示的文章' );
	}

	$html  = '<div class="glintide-post-list glintide-post-list-' . esc_attr( $list_style ) . '">';
	$html .= glintide_widget_title( $title );
	$html .= '<div class="glintide-post-list-box wid-item">';

	foreach ( $posts as $index => $post ) {
		setup_postdata( $post );
		$post_id     = $post->ID;
		$thumb       = glintide_get_thumb( $post_id );
		$link        = get_permalink( $post_id );
		$post_title  = get_the_title( $post_id );

		if ( $list_style === 'featured' && $index === 0 ) {
			$html .= '<div class="glintide-post-featured">';
			$html .= '<a href="' . esc_url( $link ) . '">';
			$html .= '<div class="glintide-post-thumb">' . ( $thumb ? '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $post_title ) . '">' : '' ) . '</div>';
			$html .= '<div class="glintide-post-overlay">';
			$html .= '<h3 class="glintide-post-title">' . esc_html( $post_title ) . '</h3>';
			$html .= '<div class="glintide-post-meta">' . glintide_post_meta_html( $post_id, $show_meta ) . '</div>';
			$html .= '</div>';
			$html .= '</a>';
			$html .= '</div>';
		} elseif ( $list_style === 'text' ) {
			$top_label = '';
			if ( $index < 3 ) {
				$top_class  = $index === 0 ? 'top-1' : 'top-2';
				$top_label  = '<span class="glintide-top-label ' . $top_class . '">TOP' . ( $index + 1 ) . '</span>';
			}
			$html .= '<div class="glintide-post-text-item">';
			$html .= '<a href="' . esc_url( $link ) . '" class="glintide-post-title">' . $top_label . '<span class="glintide-title-text">' . esc_html( $post_title ) . '</span></a>';
			if ( ! empty( $show_meta ) ) {
				$html .= '<div class="glintide-post-meta">' . glintide_post_meta_html( $post_id, $show_meta ) . '</div>';
			}
			$html .= '</div>';
		} else {
			$html .= '<div class="glintide-post-item">';
			$html .= '<a href="' . esc_url( $link ) . '" class="glintide-post-thumb">';
			$html .= $thumb ? '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $post_title ) . '">' : '';
			$html .= '</a>';
			$html .= '<div class="glintide-post-info">';
			$html .= '<a href="' . esc_url( $link ) . '" class="glintide-post-title">' . esc_html( $post_title ) . '</a>';
			if ( ! empty( $show_meta ) ) {
				$html .= '<div class="glintide-post-meta">' . glintide_post_meta_html( $post_id, $show_meta ) . '</div>';
			}
			$html .= '</div>';
			$html .= '</div>';
		}
	}

	wp_reset_postdata();

	$html .= '</div></div>';

	return $html;
}

/* ================================================================
 * 分类推荐
 * ================================================================ */
function glintide_cat_recommend_func( $data ) {
	$title    = $data['title'] ?? '';
	$cat_style = $data['cat_style'] ?? 'banner';
	$cat_ids  = $data['cat_list'] ?? array();

	if ( empty( $cat_ids ) ) {
		return glintide_widget_notice( '请配置推荐分类' );
	}

	$html = '<div class="glintide-cat-recommend glintide-cat-' . esc_attr( $cat_style ) . ( $cat_style === 'tag' ? ' glintide-cat-tag-mode' : '' ) . '">';
	$html .= glintide_widget_title( $title );
	$html .= '<div class="glintide-cat-box wid-item">';
	$has_cat = false;

	foreach ( $cat_ids as $index => $cat_id ) {
		$cat = get_category( $cat_id );
		if ( ! $cat || is_wp_error( $cat ) ) {
			continue;
		}
		$has_cat = true;

		$cat_name  = $cat->name;
		$cat_count = $cat->count;
		$cat_link  = get_category_link( $cat_id );

		$tax_meta    = get_term_meta( $cat_id, '_glintide_taxonomy_options', true );
		$cat_banner  = isset( $tax_meta['cat_banner'] ) ? $tax_meta['cat_banner'] : '';
		if ( is_array( $cat_banner ) ) {
			$cat_banner = $cat_banner['url'] ?? '';
		}

		if ( $cat_style === 'banner' ) {
			$html .= '<a href="' . esc_url( $cat_link ) . '" class="glintide-cat-banner-item">';
			$html .= '<div class="glintide-cat-banner">' . ( $cat_banner ? '<img src="' . esc_url( $cat_banner ) . '" alt="' . esc_attr( $cat_name ) . '">' : '' ) . '</div>';
			$html .= '<div class="glintide-cat-overlay">';
			$html .= '<h3 class="glintide-cat-name">' . esc_html( $cat_name ) . '</h3>';
			$html .= '<span class="glintide-cat-count">' . $cat_count . ' 篇文章</span>';
			$html .= '</div>';
			$html .= '</a>';
		} else {
			$is_red = ( $index < 2 );
			$html .= '<a href="' . esc_url( $cat_link ) . '" class="glintide-cat-tag-item' . ( $is_red ? ' tag-red' : '' ) . '">';
			$html .= '<i class="ri-apps-fill"></i>';
			$html .= '<span class="glintide-cat-name">' . esc_html( $cat_name ) . '</span>';
			$html .= '<span class="glintide-cat-count">(' . $cat_count . ')</span>';
			$html .= '</a>';
		}
	}

	if ( ! $has_cat ) {
		$html .= glintide_widget_notice( '请配置有效的推荐分类' );
	}

	$html .= '</div></div>';

	return $html;
}

/* ================================================================
 * 用户信息
 * ================================================================ */
function glintide_user_info_func( $data ) {
	$title = $data['title'] ?? '';

	$html  = '<div class="glintide-user-info">';
	$html .= glintide_widget_title( $title );
	$html .= '<div class="glintide-user-box wid-item">';

	if ( is_user_logged_in() ) {
		$user_id  = get_current_user_id();
		$user     = get_userdata( $user_id );
		$avatar   = glintide_get_avatar_url( $user_id );
		$nickname = $user->display_name;

		$posts_count = count_user_posts( $user_id, 'post', true );

		$html .= '<div class="glintide-user-logged">';
		$html .= '<div class="glintide-user-main">';
		$html .= $avatar ? '<img src="' . esc_url( $avatar ) . '" alt="' . esc_attr( $nickname ) . '" class="glintide-user-avatar">' : '<div class="glintide-user-avatar glintide-user-avatar-empty"><i class="ri-user-3-line"></i></div>';
		$html .= '<div class="glintide-user-meta">';
		$html .= '<div class="glintide-user-name">' . esc_html( $nickname ) . '</div>';
		$html .= '</div>';
		$html .= '</div>';

		$user_url = get_author_posts_url( $user_id );
		$html .= '<div class="glintide-user-stats">';
		$html .= '<a href="' . esc_url( $user_url ) . '" class="glintide-stat-item">';
		$html .= '<i class="ri-book-open-line"></i>';
		$html .= '<span class="glintide-stat-num"><span class="glintide-stat-val">' . glintide_k_m_i( $posts_count ) . '</span><span class="glintide-stat-label">文章</span></span>';
		$html .= '</a>';
		$html .= '</div>';
		$html .= '<div class="glintide-user-actions">';
		$html .= '<a href="' . esc_url( $user_url ) . '" class="glintide-action-btn glintide-action-home"><i class="ri-home-4-line"></i>首页</a>';
		$html .= '<a href="' . esc_url( admin_url( 'profile.php' ) ) . '" class="glintide-action-btn glintide-action-center"><i class="ri-settings-3-line"></i>个人中心</a>';
		$html .= '</div>';
		$html .= '</div>';
	} else {
		$html .= '<div class="glintide-user-logged-out">';
		$html .= '<div class="glintide-login-tip"><i class="ri-chat-smile-2-fill"></i>HI,请登录</div>';
		$html .= '<a href="' . esc_url( wp_login_url() ) . '" class="glintide-btn-login"><span class="login">登录</span><span class="register">注册</span></a>';
		$html .= '</div>';
	}

	$html .= '</div></div>';

	return $html;
}

/* ================================================================
 * 菜单小工具
 * ================================================================ */
function glintide_menu_widget_func( $data ) {
	$html     = '<div class="glintide-menu-widget wid-item">';
	$has_menu = false;

	if ( ! empty( $data['menu_groups'] ) ) {
		foreach ( $data['menu_groups'] as $group ) {
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

/* ================================================================
 * 评论列表
 * ================================================================ */
function glintide_comment_widget_excerpt( $content ) {
	$content = (string) $content;
	$has_image = preg_match( '/<img\b[^>]*>/i', $content );
	$content = preg_replace( '/<img\b[^>]*>/i', ' [图片] ', $content );
	$content = wp_strip_all_tags( $content );
	$content = preg_replace( '/\s+/u', ' ', $content );
	$content = trim( $content );

	if ( $content === '' && $has_image ) {
		return '[图片]';
	}

	return wp_trim_words( $content, 100, '...' );
}

function glintide_comment_widget_func( $data ) {
	$title   = isset( $data['title'] ) ? $data['title'] : '';
	$sort_by = isset( $data['sort_by'] ) ? $data['sort_by'] : 'newest';
	$count   = isset( $data['comment_count'] ) ? intval( $data['comment_count'] ) : 5;

	$args = array(
		'number' => $count,
		'status' => 'approve',
		'type'   => 'comment',
	);

	if ( $sort_by === 'newest' ) {
		$args['orderby'] = 'comment_date';
		$args['order']   = 'DESC';
	}

	$comments = get_comments( $args );

	if ( $sort_by !== 'newest' ) {
		usort(
			$comments,
			function( $a, $b ) {
				$a_likes = intval( get_comment_meta( $a->comment_ID, 'like_count', true ) ?: 0 );
				$b_likes = intval( get_comment_meta( $b->comment_ID, 'like_count', true ) ?: 0 );
				return $b_likes - $a_likes;
			}
		);
	}

	$html  = glintide_widget_title( $title );
	$html .= '<div class="glintide-comment-widget wid-item">';

	if ( empty( $comments ) ) {
		$html .= '<div class="glintide-comment-empty">暂无评论</div>';
	} else {
		foreach ( $comments as $comment ) {
			$user_id      = $comment->user_id;
			$author_name  = $comment->comment_author;
			$post_url     = get_permalink( $comment->comment_post_ID );
			$avatar_url   = $user_id ? glintide_get_avatar_url( $user_id ) : '';
			$comment_date = human_time_diff( strtotime( $comment->comment_date ), current_time( 'timestamp' ) ) . '前';
			$comment_content = glintide_comment_widget_excerpt( $comment->comment_content );
			$post_title   = get_the_title( $comment->comment_post_ID );
			$like_count   = get_comment_meta( $comment->comment_ID, 'like_count', true ) ?: 0;

			$html .= '<a href="' . esc_url( $post_url ) . '" class="glintide-comment-item">';
			$html .= $avatar_url ? '<img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $author_name ) . '" class="glintide-comment-avatar">' : '<div class="glintide-comment-avatar glintide-comment-avatar-empty"><i class="ri-user-3-line"></i></div>';
			$html .= '<div class="glintide-comment-body">';
			$html .= '<div class="glintide-comment-meta">';
			$html .= '<span class="glintide-comment-author">' . esc_html( $author_name ) . '</span>';
			$html .= '<span class="glintide-comment-time">' . esc_html( $comment_date ) . '</span>';
			$html .= '<span class="glintide-comment-likes"><i class="ri-thumb-up-line"></i>' . intval( $like_count ) . '</span>';
			$html .= '</div>';
			$html .= '<div class="glintide-comment-content">' . esc_html( $comment_content ) . '</div>';
			$html .= '</div>';
			$html .= '</a>';
		}
	}

	$html .= '</div>';

	return $html;
}

/* ================================================================
 * 图片画廊
 * ================================================================ */
function glintide_gallery_widget_func( $data ) {
	$title  = isset( $data['title'] ) ? $data['title'] : '';
	$images = isset( $data['images'] ) ? $data['images'] : '';

	if ( is_string( $images ) && ! empty( $images ) ) {
		$images = array_filter( explode( ',', $images ) );
	} elseif ( ! is_array( $images ) ) {
		$images = array();
	}

	$html  = glintide_widget_title( $title );
	$html .= '<div class="glintide-gallery-widget wid-item">';

	if ( empty( $images ) ) {
		$html .= glintide_widget_notice( '请配置图片画廊' );
	} else {
		$html .= '<div class="glintide-gallery-grid">';
		foreach ( $images as $attachment_id ) {
			$full_url  = wp_get_attachment_image_url( $attachment_id, 'full' );
			$thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );
			if ( ! $full_url ) {
				continue;
			}
			$html .= '<a href="' . esc_url( $full_url ) . '" class="glintide-gallery-item">';
			$html .= '<img src="' . esc_url( $thumb_url ?: $full_url ) . '" alt="画廊图片">';
			$html .= '</a>';
		}
		$html .= '</div>';
	}

	$html .= '</div>';

	return $html;
}

/* ================================================================
 * 一言小工具
 * ================================================================ */
function glintide_hitokoto_widget_func( $data ) {
	$title           = $data['title'] ?? '';
	$show_from       = ! empty( $data['show_from'] );
	$show_refresh    = ! empty( $data['show_refresh'] );
	$bg_image        = $data['bg_image'] ?? '';
	$overlay_color   = $data['overlay_color'] ?? '#000000';
	$overlay_opacity = isset( $data['overlay_opacity'] ) ? intval( $data['overlay_opacity'] ) : 55;
	$overlay_opacity = max( 0, min( 100, $overlay_opacity ) );
	$card_height     = isset( $data['card_height'] ) ? absint( $data['card_height'] ) : 0;

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

/* ================================================================
 * 音乐播放器小工具(网易云歌单,通过 REST API 解析)
 * ================================================================ */
function glintide_music_widget_func( $data ) {
	$title          = $data['title'] ?? '';
	$playlist_url   = $data['playlist_url'] ?? '';
	$show_playlist  = ! empty( $data['show_playlist'] );
	$default_volume = isset( $data['default_volume'] ) ? intval( $data['default_volume'] ) : 65;
	$default_volume = max( 0, min( 100, $default_volume ) );

	$uid = 'glintide-music-' . uniqid();
	$api_url = rest_url( 'glintide/v1/netease-playlist' );

	$html  = '<div class="glintide-music-widget glintide-music-immersive" id="' . esc_attr( $uid ) . '" data-api="' . esc_url( $api_url ) . '" data-url="' . esc_attr( $playlist_url ) . '" data-volume="' . $default_volume . '" data-show-playlist="' . ( $show_playlist ? '1' : '0' ) . '">';
	$html .= '<div class="glintide-music-cover-bg" data-music-cover-bg></div>';
	$html .= '<div class="glintide-music-cover-overlay"></div>';
	$html .= '<div class="glintide-music-body">';
	$html .= '<div class="glintide-music-top">';
	$html .= '<span class="glintide-music-title" data-music-title>加载中...</span>';
	$html .= '<div class="glintide-music-ctrls">';
	$html .= '<button type="button" class="glintide-music-btn" data-music-mode aria-label="播放模式"><i class="ri-repeat-line"></i></button>';
	$html .= '<button type="button" class="glintide-music-btn" data-music-prev aria-label="上一首"><i class="ri-skip-back-line"></i></button>';
	$html .= '<button type="button" class="glintide-music-btn is-main" data-music-toggle aria-label="播放/暂停"><i class="ri-play-line"></i></button>';
	$html .= '<button type="button" class="glintide-music-btn" data-music-next aria-label="下一首"><i class="ri-skip-forward-line"></i></button>';
	$html .= '</div>';
	$html .= '</div>';
	$html .= '<div class="glintide-music-sub">';
	$html .= '<span class="glintide-music-artist" data-music-artist></span>';
	$html .= '<div class="glintide-music-vol">';
	$html .= '<button type="button" class="glintide-music-mini" data-music-mute aria-label="静音"><i class="ri-volume-up-line"></i></button>';
	$html .= '<div class="glintide-music-vol-track" data-music-vol-track><div class="glintide-music-vol-fill" data-music-vol-fill></div></div>';
	$html .= '</div>';
	$html .= '</div>';
	$html .= '<div class="glintide-music-progress">';
	$html .= '<span class="glintide-music-time" data-music-current>0:00</span>';
	$html .= '<div class="glintide-music-track" data-music-track><div class="glintide-music-fill" data-music-fill></div></div>';
	$html .= '<span class="glintide-music-time" data-music-duration>0:00</span>';
	$html .= '</div>';
	$html .= '</div>';
	if ( $show_playlist ) {
		$html .= '<div class="glintide-music-playlist" data-music-playlist></div>';
	}
	$html .= '<p class="glintide-music-error" data-music-error></p>';
	$html .= '<audio data-music-audio preload="metadata"></audio>';
	$html .= '</div>';

	$html .= '<script>
	(function(){
		var root = document.getElementById("' . esc_attr( $uid ) . '");
		if (!root) return;
		var apiUrl = root.getAttribute("data-api");
		var playlistUrl = root.getAttribute("data-url");
		var defaultVolume = parseFloat(root.getAttribute("data-volume") || "65") / 100;
		var showPlaylist = root.getAttribute("data-show-playlist") === "1";

		var audio = root.querySelector("[data-music-audio]");
		var coverBg = root.querySelector("[data-music-cover-bg]");
		var titleEl = root.querySelector("[data-music-title]");
		var artistEl = root.querySelector("[data-music-artist]");
		var toggleBtn = root.querySelector("[data-music-toggle]");
		var prevBtn = root.querySelector("[data-music-prev]");
		var nextBtn = root.querySelector("[data-music-next]");
		var muteBtn = root.querySelector("[data-music-mute]");
		var modeBtn = root.querySelector("[data-music-mode]");
		var volTrack = root.querySelector("[data-music-vol-track]");
		var volFill = root.querySelector("[data-music-vol-fill]");
		var trackEl = root.querySelector("[data-music-track]");
		var fillEl = root.querySelector("[data-music-fill]");
		var currentEl = root.querySelector("[data-music-current]");
		var durationEl = root.querySelector("[data-music-duration]");
		var playlistEl = root.querySelector("[data-music-playlist]");
		var errorEl = root.querySelector("[data-music-error]");

		var playlist = [];
		var index = 0;
		var isPlaying = false;
		var volume = defaultVolume;
		var isMuted = false;
		var modes = ["sequential", "shuffle", "repeat", "repeat-one"];
		var mode = 0;
		var shuffleHistory = [];

		function esc(s) {
			var d = document.createElement("div");
			d.textContent = s || "";
			return d.innerHTML;
		}

		function fmt(t) {
			if (!isFinite(t) || t <= 0) return "0:00";
			var m = Math.floor(t / 60);
			var s = Math.floor(t % 60);
			return m + ":" + (s < 10 ? "0" : "") + s;
		}

		function currentTrack() { return playlist[index] || null; }

		function setCover(url) {
			if (!coverBg) return;
			if (!url) { coverBg.style.backgroundImage = ""; return; }
			coverBg.style.backgroundImage = "url(\'" + url + "\')";
			coverBg.classList.remove("is-changing");
			void coverBg.offsetWidth;
			coverBg.classList.add("is-changing");
		}

		function renderPlaylist() {
			if (!playlistEl) return;
			if (!playlist.length) {
				playlistEl.innerHTML = \'<div class="glintide-music-empty">暂无歌曲</div>\';
				return;
			}
			var html = "";
			for (var i = 0; i < playlist.length; i++) {
				var t = playlist[i];
				var active = i === index;
				html += \'<button type="button" class="glintide-music-track-item\' + (active ? " is-active" : "") + \'" data-index="\' + i + \'">\';
				if (active && isPlaying) {
					html += \'<span class="glintide-music-eq"><span></span><span></span><span></span></span>\';
				} else {
					html += \'<span class="glintide-music-idx">\' + (i + 1) + \'</span>\';
				}
				html += \'<span class="glintide-music-trk-title">\' + esc(t.title) + \'</span>\';
				html += \'</button>\';
			}
			playlistEl.innerHTML = html;
		}

		function updateUI() {
			var t = currentTrack();
			titleEl.textContent = t ? t.title : "暂无歌曲";
			artistEl.textContent = t ? t.artist : "";
			setCover(t ? t.cover : "");
			toggleBtn.innerHTML = isPlaying ? \'<i class="ri-pause-line"></i>\' : \'<i class="ri-play-line"></i>\';
			var modeIcons = ["ri-repeat-line", "ri-shuffle-line", "ri-repeat-line", "ri-repeat-one-line"];
			var modeLabels = ["顺序播放", "随机播放", "循环播放", "单曲循环"];
			modeBtn.innerHTML = \'<i class="\' + modeIcons[mode] + \'"></i>\';
			modeBtn.title = modeLabels[mode];
			renderPlaylist();
		}

		function loadPlaylist() {
			if (!playlistUrl) {
				titleEl.textContent = "请配置歌单链接";
				return;
			}
			titleEl.textContent = "加载歌单中...";
			fetch(apiUrl + "?url=" + encodeURIComponent(playlistUrl))
				.then(function(r) { return r.json(); })
				.then(function(data) {
					if (data.tracks && data.tracks.length) {
						playlist = data.tracks;
						index = 0;
						isPlaying = false;
						if (data.title) titleEl.textContent = data.title;
						updateUI();
					} else {
						titleEl.textContent = "歌单为空或解析失败";
					}
				})
				.catch(function() {
					titleEl.textContent = "歌单加载失败";
				});
		}

		function play() {
			var t = currentTrack();
			if (!t) return;
			if (t.audioUrl) {
				audio.src = t.audioUrl;
				audio.play().then(function() {
					isPlaying = true;
					errorEl.textContent = "";
					updateUI();
				}).catch(function() {
					isPlaying = false;
					errorEl.textContent = "播放失败,请重试";
					updateUI();
				});
			} else {
				errorEl.textContent = "该歌曲无可用音频";
				isPlaying = false;
				updateUI();
			}
		}

		function pause() {
			audio.pause();
			isPlaying = false;
			updateUI();
		}

		function toggle() {
			if (!currentTrack()) return;
			if (isPlaying) { pause(); } else { play(); }
		}

		function getShuffleIndex() {
			var len = playlist.length;
			if (len <= 1) return -1;
			var available = [];
			for (var i = 0; i < len; i++) if (i !== index) available.push(i);
			var recent = shuffleHistory.slice(-2);
			var candidates = available.filter(function(i) { return recent.indexOf(i) === -1; });
			var pool = candidates.length ? candidates : available;
			var idx = pool[Math.floor(Math.random() * pool.length)];
			shuffleHistory.push(idx);
			if (shuffleHistory.length > 10) shuffleHistory.shift();
			return idx;
		}

		function nextTrack() {
			if (!playlist.length) return;
			if (mode === 1) {
				var n = getShuffleIndex();
				if (n >= 0) index = n;
			} else if (mode === 2) {
				index = (index + 1) % playlist.length;
			} else if (mode === 3) {
				// 单曲循环:重播当前
			} else {
				if (index >= playlist.length - 1) {
					index = 0;
					isPlaying = false;
					updateUI();
					return;
				}
				index = index + 1;
			}
			isPlaying = true;
			play();
		}

		function prevTrack() {
			if (!playlist.length) return;
			if (mode === 1) {
				var n = getShuffleIndex();
				if (n >= 0) index = n;
			} else if (mode === 3) {
				// 单曲循环:重播当前
			} else {
				index = (index - 1 + playlist.length) % playlist.length;
			}
			isPlaying = true;
			play();
		}

		function selectTrack(i) {
			index = i;
			isPlaying = true;
			play();
		}

		function seek(ratio) {
			if (audio && isFinite(audio.duration) && audio.duration > 0) {
				audio.currentTime = Math.max(0, Math.min(1, ratio)) * audio.duration;
			}
		}

		function updateProgress() {
			var d = isFinite(audio.duration) ? audio.duration : 0;
			var ratio = d > 0 ? Math.min(audio.currentTime / d, 1) : 0;
			fillEl.style.width = (ratio * 100) + "%";
			currentEl.textContent = fmt(audio.currentTime);
			durationEl.textContent = d > 0 ? fmt(d) : "0:00";
		}

		function syncVolume() {
			audio.volume = volume;
			audio.muted = isMuted;
			var icon = isMuted || volume === 0 ? "ri-volume-mute-line" : (volume < 0.5 ? "ri-volume-down-line" : "ri-volume-up-line");
			muteBtn.innerHTML = \'<i class="\' + icon + \'"></i>\';
			volFill.style.width = (isMuted ? 0 : volume * 100) + "%";
		}

		function setVolumeFromEvent(e) {
			var rect = volTrack.getBoundingClientRect();
			var ratio = (e.clientX - rect.left) / rect.width;
			volume = Math.max(0, Math.min(1, ratio));
			isMuted = volume === 0;
			syncVolume();
		}

		// 事件绑定
		toggleBtn.addEventListener("click", toggle);
		prevBtn.addEventListener("click", prevTrack);
		nextBtn.addEventListener("click", nextTrack);
		modeBtn.addEventListener("click", function() {
			mode = (mode + 1) % modes.length;
			updateUI();
		});
		muteBtn.addEventListener("click", function() {
			isMuted = !isMuted;
			syncVolume();
		});
		volTrack.addEventListener("click", setVolumeFromEvent);
		volTrack.addEventListener("mousedown", function(e) {
			setVolumeFromEvent(e);
			var onMove = function(ev) { setVolumeFromEvent(ev); };
			var onUp = function() {
				document.removeEventListener("mousemove", onMove);
				document.removeEventListener("mouseup", onUp);
			};
			document.addEventListener("mousemove", onMove);
			document.addEventListener("mouseup", onUp);
		});
		trackEl.addEventListener("click", function(e) {
			var rect = trackEl.getBoundingClientRect();
			seek((e.clientX - rect.left) / rect.width);
		});
		if (playlistEl) {
			playlistEl.addEventListener("click", function(e) {
				var btn = e.target.closest("[data-index]");
				if (btn) selectTrack(parseInt(btn.getAttribute("data-index"), 10));
			});
		}
		audio.addEventListener("timeupdate", updateProgress);
		audio.addEventListener("loadedmetadata", updateProgress);
		audio.addEventListener("ended", nextTrack);
		audio.addEventListener("error", function() {
			isPlaying = false;
			errorEl.textContent = "音频加载失败";
			updateUI();
		});

		// 初始化
		syncVolume();
		loadPlaylist();
	})();
	</script>';

	return $html;
}
