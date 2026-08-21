<?php
// 小工具
function pix_widget_title( $title ) {
    $title = is_scalar( $title ) ? trim( (string) $title ) : '';

    return $title !== '' ? '<div class="wid_title">' . esc_html( $title ) . '</div>' : '';
}

function pix_widget_notice( $text ) {
    return '<div class="pix-widget-notice wid-item"><i class="ri-information-line"></i><span>' . esc_html( $text ) . '</span></div>';
}

function pix_comment_widget_excerpt( $content ) {
    $content = (string) $content;
    $has_image = preg_match('/\[img=(.*?)\]/', $content);
    $content = preg_replace('/\[img=(.*?)\]/', ' [图片] ', $content);
    $content = preg_replace('/\[d\](.*?)\[\/d\]/', ' [图片] ', $content);
    $content = preg_replace('/<img\b[^>]*>/i', ' [图片] ', $content);
    $content = preg_replace('/\[s=(.*?)\]/', '[$1]', $content);
    $content = preg_replace('/\[a-([^\]]+)\]/', '', $content);
    $content = wp_strip_all_tags( $content );
    $content = preg_replace('/\s+/u', ' ', $content);
    $content = trim( $content );

    if ( $content === '' && $has_image ) {
        return '[图片]';
    }

    return wp_trim_words( $content, 100, '...' );
}

// 广告位
function pix_gg_banner_func( $data ) {
    $image = $data['gg_image'] ?? '';
    $link = $data['gg_link'] ?? '#';
    $target = !empty($data['gg_target']) ? 'target="_blank"' : '';
    $title = $data['title'] ?? '';

    if ( empty( $image ) ) {
        return pix_widget_notice( '请配置广告图片' );
    }

    $html = '<div class="pix-gg-banner">';

    $html .= pix_widget_title( $title );

    $html .= '<div class="pix-gg-image wid-item">';
    $html .= '<a href="' . esc_url( $link ) . '" ' . $target . '>';
    $html .= '<img src="' . esc_url( $image ) . '" alt="广告">';
    $html .= '</a>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

// 评论小工具
function pix_comment_widget_func( $data ) {
    $title = isset( $data['title'] ) ? $data['title'] : '';
    $sort_by = isset( $data['sort_by'] ) ? $data['sort_by'] : 'newest';
    $count = isset( $data['comment_count'] ) ? intval( $data['comment_count'] ) : 5;

    $args = array(
        'number'  => $count,
        'status'  => 'approve',
        'type'    => 'comment',
    );

    if ( $sort_by === 'newest' ) {
        $args['orderby'] = 'comment_date';
        $args['order'] = 'DESC';
    }

    $comments = get_comments( $args );

    if ( $sort_by !== 'newest' ) {
        usort( $comments, function( $a, $b ) {
            $a_likes = intval( get_comment_meta( $a->comment_ID, 'like_count', true ) ?: 0 );
            $b_likes = intval( get_comment_meta( $b->comment_ID, 'like_count', true ) ?: 0 );
            return $b_likes - $a_likes;
        } );
    }

    $html = pix_widget_title( $title );
    $html .= '<div class="pix-comment-widget wid-item">';

    if ( empty( $comments ) ) {
        $html .= '<div class="pix-comment-empty">暂无评论</div>';
    } else {
        foreach ( $comments as $comment ) {
            $comment_id = $comment->comment_ID;
            $user_id = $comment->user_id;
            $author_name = $comment->comment_author;
            $post_url = get_permalink( $comment->comment_post_ID );
            $avatar_url = $user_id ? get_u_avatar( $user_id, 'url' ) : '';
            if ( empty( $avatar_url ) || $avatar_url === 'false' ) {
                $avatar_url = THEME_URL . '/img/avap.png';
            }
            $comment_date = human_time_diff( strtotime( $comment->comment_date ), current_time( 'timestamp' ) ) .'前';
            $comment_content = pix_comment_widget_excerpt( $comment->comment_content );
            $post_title = get_the_title( $comment->comment_post_ID );
            $like_count = get_comment_meta( $comment_id, 'like_count', true ) ?: 0;

            $html .= '<a href="' . esc_url( $post_url ) . '" class="pix-comment-item">';
            $html .= '<img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $author_name ) . '" class="pix-comment-avatar">';
            $html .= '<div class="pix-comment-body">';
            $html .= '<div class="pix-comment-meta">';
            $html .= '<span class="pix-comment-author">' . esc_html( $author_name ) . '</span>';
            $html .= '<span class="pix-comment-time">' . esc_html( $comment_date ) . '</span>';
            $html .= '<span class="pix-comment-likes"><i class="ri-thumb-up-line"></i>' . intval( $like_count ) . '</span>';
            $html .= '</div>';
            $html .= '<div class="pix-comment-content">' . esc_html( $comment_content ) . '</div>';
            $html .= '</div>';
            $html .= '</a>';
        }
    }

    $html .= '</div>';

    return $html;
}

// 图片画廊小工具
function pix_gallery_widget_func( $data ) {
    $title = isset( $data['title'] ) ? $data['title'] : '';
    $images = isset( $data['images'] ) ? $data['images'] : '';

    if ( is_string( $images ) && ! empty( $images ) ) {
        $images = array_filter( explode( ',', $images ) );
    } elseif ( ! is_array( $images ) ) {
        $images = array();
    }

    $html = pix_widget_title( $title );
    $html .= '<div class="pix-gallery-widget wid-item">';

    if ( empty( $images ) ) {
        $html .= pix_widget_notice( '请配置图片画廊' );
    } else {
        $html .= '<div class="pix-gallery-grid">';
        foreach ( $images as $attachment_id ) {
            $full_url = wp_get_attachment_image_url( $attachment_id, 'full' );
            $thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );
            if ( ! $full_url ) continue;
            $html .= '<a href="' . esc_url( $full_url ) . '" class="pix-gallery-item fancy-box" data-fancybox="gallery">';
            $html .= '<img src="' . esc_url( $thumb_url ?: $full_url ) . '" alt="画廊图片">';
            $html .= '</a>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}

// 文章列表
function pix_post_list_func( $data ) {
    $title = $data['title'] ?? '';
    $list_style = $data['list_style'] ?? 'normal';
    $order_by = $data['order_by'] ?? 'views';
    $post_num = isset( $data['post_num'] ) ? absint( $data['post_num'] ) : 5;
    $show_meta = isset( $data['show_meta'] ) ? $data['show_meta'] : array( 'views', 'date' );
    $show_meta = is_array( $show_meta ) ? $show_meta : array();

    $posts = pix_post_list_get_posts( $post_num, $order_by );

    if ( empty( $posts ) ) {
        return pix_widget_notice( '暂无可显示的文章' );
    }

    $html = '<div class="pix-post-list pix-post-list-' . esc_attr( $list_style ) . '">';

    $html .= pix_widget_title( $title );

    $html .= '<div class="pix-post-list-box wid-item">';

    foreach ( $posts as $index => $post ) {
        setup_postdata( $post );
        $post_id = $post->ID;
        $thumb = get_ppo_thum( $post_id, 'thumbnail', 'random' );
        $link = get_permalink( $post_id );
        $post_title = get_the_title( $post_id );

        if ( $list_style === 'featured' && $index === 0 ) {
            $html .= '<div class="pix-post-featured">';
            $html .= '<a href="' . esc_url( $link ) . '">';
            $html .= '<div class="pix-post-thumb"><img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $post_title ) . '"></div>';
            $html .= '<div class="pix-post-overlay">';
            $html .= '<h3 class="pix-post-title">' . esc_html( $post_title ) . '</h3>';
            $html .= '<div class="pix-post-meta">' . pix_post_meta_html( $post_id, $show_meta ) . '</div>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</div>';
        } elseif ( $list_style === 'text' ) {
            $top_label = '';
            if ( $index < 3 ) {
                $top_class = $index === 0 ? 'top-1' : 'top-2';
                $top_label = '<span class="pix-top-label ' . $top_class . '">TOP' . ( $index + 1 ) . '</span>';
            }
            $html .= '<div class="pix-post-text-item">';
            $html .= '<a href="' . esc_url( $link ) . '" class="pix-post-title">' . $top_label . '<span class="pix-title-text">' . esc_html( $post_title ) . '</span></a>';
            if ( !empty( $show_meta ) ) {
                $html .= '<div class="pix-post-meta">' . pix_post_meta_html( $post_id, $show_meta ) . '</div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<div class="pix-post-item">';
            $html .= '<a href="' . esc_url( $link ) . '" class="pix-post-thumb">';
            $html .= '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $post_title ) . '">';
            $html .= '</a>';
            $html .= '<div class="pix-post-info">';
            $html .= '<a href="' . esc_url( $link ) . '" class="pix-post-title">' . esc_html( $post_title ) . '</a>';
            if ( !empty( $show_meta ) ) {
                $html .= '<div class="pix-post-meta">' . pix_post_meta_html( $post_id, $show_meta ) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }
    }

    wp_reset_postdata();

    $html .= '</div></div>';

    return $html;
}

function get_orderby_key( $order_by ) {
    $keys = array(
        'views'    => 'views',
        'comments' => 'comment_count',
        'likes'    => 'likes_count',
        'favorites'=> 'collect_count',
    );
    return $keys[ $order_by ] ?? 'views';
}

function pix_post_list_get_posts( $post_num, $order_by ) {
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

    $meta_key = get_orderby_key( $order_by );

    if ( empty( $meta_key ) || $meta_key === 'comment_count' ) {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';

        return get_posts( $args );
    }

    global $wpdb;

    $orderby_posts_by_meta = function( $clauses ) use ( $wpdb, $meta_key ) {
        $alias = 'pix_post_list_order_meta';

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

function pix_post_meta_html( $post_id, $show_meta ) {
    $meta = array();

    if ( in_array( 'views', $show_meta ) ) {
        $views = absint( get_post_meta( $post_id, 'views', true ) );
        $meta[] = '<span><i class="ri-eye-line"></i>' . pix_k_m_i( $views ) . '</span>';
    }

    if ( in_array( 'comments', $show_meta ) ) {
        $comments = absint( get_comments_number( $post_id ) );
        $meta[] = '<span><i class="ri-chat-1-line"></i>' . pix_k_m_i( $comments ) . '</span>';
    }

    if ( in_array( 'likes', $show_meta ) ) {
        $likes = absint( get_post_meta( $post_id, 'likes_count', true ) );
        $meta[] = '<span><i class="ri-heart-3-line"></i>' . pix_k_m_i( $likes ) . '</span>';
    }

    if ( in_array( 'date', $show_meta ) ) {
        $time = get_the_date( 'c', $post_id );
        $meta[] = '<span><i class="ri-calendar-line"></i><time class="timeago" itemprop="datePublished" datetime="' . esc_attr( $time ) . '">' . esc_html( get_the_date( 'Y-m-d H:i:s', $post_id ) ) . '</time></span>';
    }

    return implode( '', $meta );
}

function pix_k_m_i( $num ) {
    if ( $num >= 1000000 ) {
        return round( $num / 1000000, 1 ) . 'M';
    } elseif ( $num >= 1000 ) {
        return round( $num / 1000, 1 ) . 'k';
    }
    return $num;
}

// 分类推荐
function pix_cat_recommend_func( $data ) {
    $title = $data['title'] ?? '';
    $cat_style = $data['cat_style'] ?? 'banner';
    $cat_ids = $data['cat_list'] ?? array();

    if ( empty( $cat_ids ) ) {
        return pix_widget_notice( '请配置推荐分类' );
    }

    $html = '<div class="pix-cat-recommend pix-cat-' . esc_attr( $cat_style ) . ( $cat_style === 'tag' ? ' pix-cat-tag-mode' : '' ) . '">';

    $html .= pix_widget_title( $title );

    $html .= '<div class="pix-cat-box wid-item">';
    $has_cat = false;

    foreach ( $cat_ids as $index => $cat_id ) {
        $cat = get_category( $cat_id );
        if ( !$cat || is_wp_error( $cat ) ) {
            continue;
        }
        $has_cat = true;

        $cat_name = $cat->name;
        $cat_count = $cat->count;
        $cat_link = get_category_link( $cat_id );
        $cat_desc = $cat->description;

        $tax_meta = get_term_meta( $cat_id, '_ppo_taxonomy_options', true );
        $cat_banner = isset( $tax_meta['cat_banner'] ) ? $tax_meta['cat_banner'] : '';
        if ( !$cat_banner ) {
            $cat_banner = THEME_URL . '/img/banner.jpg';
        }

        if ( $cat_style === 'banner' ) {
            $html .= '<a href="' . esc_url( $cat_link ) . '" class="pix-cat-banner-item">';
            $html .= '<div class="pix-cat-banner"><img src="' . esc_url( $cat_banner ) . '" alt="' . esc_attr( $cat_name ) . '"></div>';
            $html .= '<div class="pix-cat-overlay">';
            $html .= '<h3 class="pix-cat-name">' . esc_html( $cat_name ) . '</h3>';
            $html .= '<span class="pix-cat-count">' . $cat_count . ' 篇文章</span>';
            $html .= '</div>';
            $html .= '</a>';
        } else {
            $is_red = ( $index < 2 );
            $html .= '<a href="' . esc_url( $cat_link ) . '" class="pix-cat-tag-item' . ( $is_red ? ' tag-red' : '' ) . '">';
            $html .= '<i class="ri-apps-fill"></i>';
            $html .= '<span class="pix-cat-name">' . esc_html( $cat_name ) . '</span>';
            $html .= '<span class="pix-cat-count">(' . $cat_count . ')</span>';
            $html .= '</a>';
        }
    }

    if ( ! $has_cat ) {
        $html .= pix_widget_notice( '请配置有效的推荐分类' );
    }

    $html .= '</div></div>';

    return $html;
}

// 用户信息
function pix_user_info_func( $data ) {
    $title = $data['title'] ?? '';

    $html = '<div class="pix-user-info">';

    $html .= pix_widget_title( $title );

    $html .= '<div class="pix-user-box wid-item">';

    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $user = get_userdata( $user_id );
        $avatar = get_u_avatar( $user_id, 'url' );
        $nickname = $user->display_name;

        $vip_level = 0;
        if ( function_exists( 'PPO_Vip' ) ) {
            $vip = PPO_Vip::check_vip_lv();
            if ( $vip ) {
                $vip_level = PPO_Vip::get_vip_index( $vip );
            }
        }
        $user_level = get_user_meta( $user_id, 'user_level', true ) ?: 1;

        $moments_count = count( get_posts( array(
            'post_type'      => 'moment',
            'author'         => $user_id,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) ) );

        $posts_count = count( get_posts( array(
            'post_type'      => 'post',
            'author'         => $user_id,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) ) );

        $followers_count = 0;
        if ( function_exists( 'ppo_get_follower_count' ) ) {
            $followers_count = ppo_get_follower_count( $user_id );
        }

        $html .= '<div class="pix-user-logged">';
        $html .= '<div class="pix-user-main">';
        $html .= '<img src="' . esc_url( $avatar ) . '" alt="' . esc_attr( $nickname ) . '" class="pix-user-avatar">';
        $html .= '<div class="pix-user-meta">';
        $html .= '<div class="pix-user-name">' . esc_html( $nickname ) . '</div>';
        $html .= '<div class="pix-user-badges">';

        $vip_data = ppo_get_user_vip_data( $user_id );
        if ( !empty( $vip_data ) ) {
            $html .= '<span class="pix-vip-badge"><img src="' . esc_url( $vip_data['icon'] ) . '" alt="vip">' . esc_html( $vip_data['title'] ) . '</span>';
        }

        $lv_data = ppo_get_user_level_info( $user_id );
        if ( !empty( $lv_data ) ) {
            $level_tip = 'LV' . intval( $lv_data['lv'] );
            $html .= '<span class="pix-level-badge pix-tooltip" data-pix-tooltip="' . esc_attr( $level_tip ) . '" aria-label="' . esc_attr( $level_tip ) . '" tabindex="0">';
            $html .= '<img src="' . esc_url( $lv_data['icon'] ) . '" alt="lv">';
            $html .= '</span>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $user_url = get_author_posts_url( $user_id );
        $html .= '<div class="pix-user-stats">';
        $html .= '<a href="' . esc_url( add_query_arg( array( 'tab' => 'moment' ), $user_url ) ) . '" class="pix-stat-item">';
        $html .= '<i class="ri-message-3-line"></i>';
        $html .= '<span class="pix-stat-num"><span class="pix-stat-val">' . pix_k_m_i( $moments_count ) . '</span><span class="pix-stat-label">' . esc_html( ppo_moment_label('moment') ) . '</span></span>';
        $html .= '</a>';
        $html .= '<a href="' . esc_url( add_query_arg( array( 'tab' => 'posts' ), $user_url ) ) . '" class="pix-stat-item">';
        $html .= '<i class="ri-book-open-line"></i>';
        $html .= '<span class="pix-stat-num"><span class="pix-stat-val">' . pix_k_m_i( $posts_count ) . '</span><span class="pix-stat-label">文章</span></span>';
        $html .= '</a>';
        $html .= '<a href="' . esc_url( add_query_arg( array( 'tab' => 'follow', 'type' => 'follower' ), $user_url ) ) . '" class="pix-stat-item">';
        $html .= '<i class="ri-user-smile-line"></i>';
        $html .= '<span class="pix-stat-num"><span class="pix-stat-val">' . pix_k_m_i( $followers_count ) . '</span><span class="pix-stat-label">粉丝</span></span>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '<div class="pix-user-actions">';
        $html .= '<a href="' . esc_url( $user_url ) . '" class="pix-action-btn pix-action-home"><i class="ri-home-4-line"></i>首页</a>';
        $html .= '<a href="' . esc_url( home_url( '/dashboard' ) ) . '" class="pix-action-btn pix-action-center"><i class="ri-settings-3-line"></i>管理中心</a>';
        $html .= '</div>';
        $html .= '</div>';
    } else {
        $html .= '<div class="pix-user-logged-out">';
        $html .= '<div class="pix-login-tip"><i class="ri-chat-smile-2-fill"></i>HI，请登录</div>';
        $html .= '<a href="#modal-login" data-pix-auth-open="login" class="pix-btn-login"><span class="login" onclick="jQuery(\'#pop_login\').trigger(\'click\');">登录</span><span class="register" onclick="jQuery(\'#pop_signup\').trigger(\'click\');">注册</span></a>';
        if ( function_exists( 'get_oaouth_btn' ) ) {
            $html .= get_oaouth_btn();
        }
        $html .= '</div>';
    }

    $html .= '</div></div>';

    return $html;
}

// 会员等级
function get_followers_count( $user_id ) {
    if ( function_exists( 'get_followers' ) ) {
        return count( get_followers( $user_id ) );
    }
    return 0;
}

// 图标网格
function pix_icon_grid_func( $data ) {
    $title = $data['title'] ?? '';
    $per_row = $data['per_row'] ?? '4';
    $list = $data['icon_list'] ?? array();

    if ( empty( $list ) ) {
        return pix_widget_notice( '请配置图标网格' );
    }

    $html = '<div class="pix-icon-grid">';

    $html .= pix_widget_title( $title );

    $html .= '<div class="pix-icon-grid-box wid-item pix-icon-col-' . absint( $per_row ) . '">';
    $has_icon = false;

    foreach ( $list as $item ) {
        if ( empty( $item['title'] ) ) {
            continue;
        }

        $link = $item['link'] ?? '#';
        $target = !empty( $item['target'] ) ? 'target="_blank"' : '';
        $icon_html = '';

        if ( !empty( $item['icon_type'] ) && $item['icon_type'] === 'image' && !empty( $item['image'] ) ) {
            $icon_html = '<img class="lazy" data-src="' . esc_url( $item['image'] ) . '" alt="' . esc_attr( $item['title'] ) . '">';
        } elseif ( !empty( $item['icon'] ) ) {
            $icon_html = '<i class="' . esc_attr( $item['icon'] ) . '"></i>';
        }

        if ( empty( $icon_html ) ) {
            continue;
        }
        $has_icon = true;

        $bg_style = '';
        $icon_style = '';
        if ( !empty( $item['icon_type'] ) && $item['icon_type'] === 'icon' ) {
            if ( !empty( $item['icon_color'] ) ) {
                $icon_style = 'color: ' . esc_attr( $item['icon_color'] ) . ';';
            }

            $bg = $item['bg-color'] ?? array();

            if ( !empty( $bg ) && is_array( $bg ) ) {
                $gradient_color = $bg['background-gradient-color'] ?? '';
                $gradient_dir = $bg['background-gradient-direction'] ?? 'to bottom';
                $solid_color = $bg['background-color'] ?? '';

                if ( !empty( $gradient_color ) && !empty( $gradient_dir ) ) {
                    $bg_style = 'background: linear-gradient(' . esc_attr( $gradient_dir ) . ', ' . esc_attr( $solid_color ) . ' 0%, ' . esc_attr( $gradient_color ) . ' 100%);';
                } elseif ( !empty( $solid_color ) ) {
                    $bg_style = 'background-color: ' . esc_attr( $solid_color ) . ';';
                }
            }
        }

        $html .= '<a href="' . esc_url( $link ) . '" ' . $target . ' class="pix-icon-item">';
        $html .= '<div class="pix-icon-wrap"' . ( $bg_style ? ' style="' . $bg_style . '"' : '' ) . '>';

        if ( !empty( $item['icon_type'] ) && $item['icon_type'] === 'image' && !empty( $item['image'] ) ) {
            $html .= '<img class="lazy" data-src="' . esc_url( $item['image'] ) . '" alt="' . esc_attr( $item['title'] ) . '">';
        } elseif ( !empty( $item['icon'] ) ) {
            $html .= '<i class="' . esc_attr( $item['icon'] ) . '"' . ( $icon_style ? ' style="' . $icon_style . '"' : '' ) . '></i>';
        }

        $html .= '</div>';
        $html .= '<div class="pix-icon-title">' . esc_html( $item['title'] ) . '</div>';
        $html .= '</a>';
    }

    if ( ! $has_icon ) {
        $html .= pix_widget_notice( '请配置有效的图标项' );
    }

    $html .= '</div></div>';

    return $html;
}

// 推荐圈子
function mo_sug_list_func($data){
    if(!empty($data)){
        $allmo = home_url().'/allmoments';
        $list = '';
        $def = THEME_URL.'/img/modef.png';
        $moment_label = ppo_moment_label('moment');
        $moments_label = ppo_moment_label('moments');
        $user_label = ppo_moment_label('user');
        $cat_list = $data['mo_sug_list'] ?? false;
        if($cat_list){
            foreach($cat_list as $term_id){
                //$meta = get_term_meta( $term_id, '_ppo_moments_options', true );
                $term_data = get_term_by('id', $term_id, 'moments');
                $mo_data = get_mo_num_data($term_id);
                $thum = get_term_meta( $term_id, 'mo_cat_img' , true);
                $thum = $thum ? $thum : THEME_URL.'/img/modef.png';
                $link = get_term_link((int)$term_id,'moments');
                $list .= '<div class="mo_sug_item">
                            <a href="'.$link.'">
                                <div class="left"><img src="'.$thum.'"></div>
                                <div class="right">
                                    <div class="title">'.$term_data->name.'</div>
                                    <div class="count-mo">'.$term_data->count.$moment_label.' <span>·</span> '.$mo_data['join'].$user_label.'</div>
                                </div>
                            </a>
                        </div>';
            }
        }

        if ( empty( $list ) ) {
            return pix_widget_notice( '请配置推荐圈子' );
        }

        $title_html = pix_widget_title( $data['title'] ?? '' );

        return $title_html.'<div class="mo-sug-list-box wid-item">'.$list.'<a href="'.$allmo.'" class="all-moments-cat">全部'.$moments_label.'</a></div>';
    }

    return pix_widget_notice( '请配置推荐圈子' );
}

// Logo 小工具
function pix_logo_widget_func( $data ) {
    $title = $data['title'] ?? '';
    $mode = $data['logo_mode'] ?? 'auto';
    $image = $data['logo_image'] ?? '';
    $link = $data['logo_link'] ?? '';
    $align = $data['logo_align'] ?? 'left';
    $width = isset( $data['logo_width'] ) ? absint( $data['logo_width'] ) : 0;
    $height = isset( $data['logo_height'] ) ? absint( $data['logo_height'] ) : 0;

    if ( empty( $link ) ) {
        $link = home_url( '/' );
    }

    $size_style = '';
    if ( $width > 0 ) {
        $size_style .= 'width:' . $width . 'px;';
    }
    if ( $height > 0 ) {
        $size_style .= 'height:' . $height . 'px;';
    }

    $logo_html = '';
    if ( $mode === 'image' && ! empty( $image ) ) {
        $logo_html = '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '"' . ( $size_style ? ' style="' . $size_style . '"' : '' ) . '>';
    } elseif ( $mode === 'text' ) {
        $logo_html = '<h3' . ( $size_style ? ' style="' . $size_style . '"' : '' ) . '>' . esc_html( pix_global_logo_text() ) . '</h3>';
    } else {
        $logo_html = site_logo( 'dark' );
        if ( $size_style ) {
            $logo_html = '<span style="' . $size_style . '">' . $logo_html . '</span>';
        }
    }

    $html = '<div class="pix-logo-widget pix-logo-align-' . esc_attr( $align ) . '">';
    $html .= pix_widget_title( $title );
    $html .= '<div class="pix-logo-box wid-item">';
    $html .= '<a href="' . esc_url( $link ) . '" class="pix-logo-link">' . $logo_html . '</a>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

// 一言小工具
function pix_hitokoto_widget_func( $data ) {
    $title = $data['title'] ?? '';
    $show_from = ! empty( $data['show_from'] );
    $show_refresh = ! empty( $data['show_refresh'] );
    $bg_image = $data['bg_image'] ?? '';
    $overlay_color = $data['overlay_color'] ?? '#000000';
    $overlay_opacity = isset( $data['overlay_opacity'] ) ? intval( $data['overlay_opacity'] ) : 55;
    $overlay_opacity = max( 0, min( 100, $overlay_opacity ) );
    $card_height = isset( $data['card_height'] ) ? absint( $data['card_height'] ) : 0;

    $uid = 'pix-hitokoto-' . uniqid();

    $card_style = '';
    if ( $card_height > 0 ) {
        $card_style .= 'min-height:' . $card_height . 'px;';
    }

    $html = '<div class="pix-hitokoto-widget pix-hitokoto-card" id="' . esc_attr( $uid ) . '"' . ( $card_style ? ' style="' . $card_style . '"' : '' ) . '>';
    $html .= '<div class="pix-hitokoto-bg"' . ( $bg_image ? ' style="background-image:url(' . esc_url( $bg_image ) . ')"' : '' ) . '></div>';
    $html .= '<div class="pix-hitokoto-overlay" style="background-color:' . esc_attr( $overlay_color ) . ';opacity:' . ( $overlay_opacity / 100 ) . '"></div>';
    if ( $title ) {
        $html .= '<div class="pix-hitokoto-title">' . esc_html( $title ) . '</div>';
    }
    $html .= '<div class="pix-hitokoto-content">';
    $html .= '<div class="pix-hitokoto-text" data-hitokoto-text>加载中...</div>';
    if ( $show_from ) {
        $html .= '<div class="pix-hitokoto-from" data-hitokoto-from></div>';
    }
    $html .= '</div>';
    if ( $show_refresh ) {
        $html .= '<button type="button" class="pix-hitokoto-refresh" data-hitokoto-refresh aria-label="换一句"><i class="ri-refresh-line"></i></button>';
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

// 音乐播放器小工具
function pix_music_widget_func( $data ) {
    $title = $data['title'] ?? '';
    $playlist_url = $data['playlist_url'] ?? '';
    $show_playlist = ! empty( $data['show_playlist'] );
    $default_volume = isset( $data['default_volume'] ) ? intval( $data['default_volume'] ) : 65;
    $default_volume = max( 0, min( 100, $default_volume ) );

    $uid = 'pix-music-' . uniqid();
    $api_url = rest_url( 'ppo/v1/netease-playlist' );

    $html = '<div class="pix-music-widget pix-music-immersive" id="' . esc_attr( $uid ) . '" data-api="' . esc_url( $api_url ) . '" data-url="' . esc_attr( $playlist_url ) . '" data-volume="' . $default_volume . '" data-show-playlist="' . ( $show_playlist ? '1' : '0' ) . '">';
    $html .= '<div class="pix-music-cover-bg" data-music-cover-bg></div>';
    $html .= '<div class="pix-music-cover-overlay"></div>';
    $html .= '<div class="pix-music-body">';
    $html .= '<div class="pix-music-top">';
    $html .= '<span class="pix-music-title" data-music-title>加载中...</span>';
    $html .= '<div class="pix-music-ctrls">';
    $html .= '<button type="button" class="pix-music-btn" data-music-mode aria-label="播放模式"><i class="ri-repeat-line"></i></button>';
    $html .= '<button type="button" class="pix-music-btn" data-music-prev aria-label="上一首"><i class="ri-skip-back-line"></i></button>';
    $html .= '<button type="button" class="pix-music-btn is-main" data-music-toggle aria-label="播放/暂停"><i class="ri-play-line"></i></button>';
    $html .= '<button type="button" class="pix-music-btn" data-music-next aria-label="下一首"><i class="ri-skip-forward-line"></i></button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="pix-music-sub">';
    $html .= '<span class="pix-music-artist" data-music-artist></span>';
    $html .= '<div class="pix-music-vol">';
    $html .= '<button type="button" class="pix-music-mini" data-music-mute aria-label="静音"><i class="ri-volume-up-line"></i></button>';
    $html .= '<div class="pix-music-vol-track" data-music-vol-track><div class="pix-music-vol-fill" data-music-vol-fill></div></div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="pix-music-progress">';
    $html .= '<span class="pix-music-time" data-music-current>0:00</span>';
    $html .= '<div class="pix-music-track" data-music-track><div class="pix-music-fill" data-music-fill></div></div>';
    $html .= '<span class="pix-music-time" data-music-duration>0:00</span>';
    $html .= '</div>';
    $html .= '</div>';
    if ( $show_playlist ) {
        $html .= '<div class="pix-music-playlist" data-music-playlist></div>';
    }
    $html .= '<p class="pix-music-error" data-music-error></p>';
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
            if (!url) {
                coverBg.style.backgroundImage = "";
                return;
            }
            coverBg.style.backgroundImage = "url(\'" + url + "\')";
            coverBg.classList.remove("is-changing");
            void coverBg.offsetWidth;
            coverBg.classList.add("is-changing");
        }

        function renderPlaylist() {
            if (!playlistEl) return;
            if (!playlist.length) {
                playlistEl.innerHTML = \'<div class="pix-music-empty">暂无歌曲</div>\';
                return;
            }
            var html = "";
            for (var i = 0; i < playlist.length; i++) {
                var t = playlist[i];
                var active = i === index;
                html += \'<button type="button" class="pix-music-track-item\' + (active ? " is-active" : "") + \'" data-index="\' + i + \'">\';
                if (active && isPlaying) {
                    html += \'<span class="pix-music-eq"><span></span><span></span><span></span></span>\';
                } else {
                    html += \'<span class="pix-music-idx">\' + (i + 1) + \'</span>\';
                }
                html += \'<span class="pix-music-trk-title">\' + esc(t.title) + \'</span>\';
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
                    errorEl.textContent = "播放失败，请重试";
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
                // 单曲循环：重播当前
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
                // 单曲循环：重播当前
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

// 菜单小工具
function pix_menu_widget_func( $data ) {
    $html = '<div class="pix-menu-widget wid-item">';
    $has_menu = false;

    if ( ! empty( $data['menu_groups'] ) ) {
        foreach ( $data['menu_groups'] as $group ) {
            if ( ! empty( $group['menu_id'] ) ) {
                $has_menu = true;
                $html .= '<div class="pix-menu-group">';
                if ( ! empty( $group['group_title'] ) ) {
                    $html .= '<div class="pix-menu-group-title">' . esc_html( $group['group_title'] ) . '</div>';
                }
                $html .= '<div class="pix-menu-list" id="pix-menu-' . uniqid() . '">';
                $html .= wp_nav_menu( array(
                    'menu'       => intval( $group['menu_id'] ),
                    'container'  => false,
                    'echo'       => false,
                    'depth'      => 2,
                ) );
                $html .= '</div>';
                $html .= '</div>';
            }
        }
    }

    if ( ! $has_menu ) {
        $html .= pix_widget_notice( '请配置菜单分组' );
    }

    $html .= '</div>';

    $html .= '<script>
    document.querySelectorAll(".pix-menu-list li.menu-item-has-children > a").forEach(function(link) {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            var parent = this.parentElement;
            parent.classList.toggle("open");
        });
    });
    </script>';

    return $html;
}
