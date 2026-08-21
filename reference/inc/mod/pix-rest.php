<?php 
//ppo rest api

function ppo_rest_logged_in_permission() {
    return is_user_logged_in();
}

function ppo_rest_write_permission($request) {
    if (!is_user_logged_in()) {
        return false;
    }

    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce) {
        $nonce = $request->get_param('_wpnonce');
    }

    if (!is_scalar($nonce)) {
        return false;
    }

    return (bool) wp_verify_nonce((string) $nonce, 'wp_rest');
}

// 用户中心获取用户发布文章
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-posts', [
        'methods'             => 'GET',
        'callback'            => 'ppo_get_user_posts_html',
        'permission_callback' => '__return_true',
        'args' => [
            'target' => [
                'required'          => false,
                'type'              => 'string',
                'default'           => '#user-content',
            ],
            'push_url_base' => [
                'required'          => false,
                'type'              => 'string',
                'default'           => '',
            ],
        ],
    ]);
});

// 用户中心获取用户发布评论
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-comments', [
        'methods'  => 'GET',
        'callback' => 'ppo_get_user_comments',
        'permission_callback' => '__return_true',
        'args' => [
            'user_id' => [
                'required' => true,
                'type'     => 'integer',
            ],
            'page' => [
                'required' => false,
                'type'     => 'integer',
                'default'  => 1,
            ],
        ],
    ]);
});

// 用户中心获取用户发布片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-moment', [
        'methods'  => 'GET',
        'callback' => 'ppo_get_user_moment',
        'permission_callback' => '__return_true',
    ]);
});

// 用户中心获取用户发布收藏
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-collect', [
        'methods'  => 'GET',
        'callback' => 'ppo_get_user_collect',
        'permission_callback' => '__return_true',
    ]);
});

// 用户中心获取用户加入的圈子
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-moments', [
        'methods'  => 'GET',
        'callback' => 'ppo_get_user_moments',
        'permission_callback' => '__return_true',
    ]);
});

// 获取用户粉丝和关注
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-follow', [
       'methods'  => 'GET',
        'callback' => 'ppo_get_user_follow',
        'permission_callback' => '__return_true',
    ]);
});

// 获取用户经验明细
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-exp', [
        'methods'  => 'GET',
        'callback' => 'ppo_display_user_xp_detail_rest',
        'permission_callback' => 'ppo_rest_logged_in_permission'
    ]);
});

// 获取积分记录
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/user-credit-records', [
        'methods'  => 'GET',
        'callback' => 'ppo_get_user_credit_records',
        'permission_callback' => 'ppo_rest_logged_in_permission'
    ]);
});

// ==================== 片刻 REST API ====================

// 获取片刻列表
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments', [
        'methods'  => 'GET',
        'callback' => 'ppo_rest_get_moments',
        'permission_callback' => '__return_true',
        'args' => [
            'page' => [
                'required' => false,
                'type' => 'integer',
                'default' => 1,
            ],
            'per_page' => [
                'required' => false,
                'type' => 'integer',
                'default' => 10,
            ],
            'category' => [
                'required' => false,
                'type' => 'integer',
            ],
            'filter' => [
                'required' => false,
                'type' => 'string',
                'default' => 'new',
            ],
        ],
    ]);
});

// 获取单个片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments/(?P<id>\d+)', [
        'methods'  => 'GET',
        'callback' => 'ppo_rest_get_moment',
        'permission_callback' => '__return_true',
    ]);
});

// 创建片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments', [
        'methods'  => 'POST',
        'callback' => 'ppo_rest_create_moment',
        'permission_callback' => 'ppo_rest_write_permission',
    ]);
});

// 更新片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments/(?P<id>\d+)', [
        'methods'  => 'PUT',
        'callback' => 'ppo_rest_update_moment',
        'permission_callback' => 'ppo_rest_write_permission',
    ]);
});

// 删除片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments/(?P<id>\d+)', [
        'methods'  => 'DELETE',
        'callback' => 'ppo_rest_delete_moment',
        'permission_callback' => 'ppo_rest_write_permission',
    ]);
});

// 点赞片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments/(?P<id>\d+)/like', [
        'methods'  => 'POST',
        'callback' => 'ppo_rest_like_moment',
        'permission_callback' => 'ppo_rest_write_permission',
    ]);
});

// 取消点赞片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments/(?P<id>\d+)/like', [
        'methods'  => 'DELETE',
        'callback' => 'ppo_rest_unlike_moment',
        'permission_callback' => 'ppo_rest_write_permission',
    ]);
});

// 举报片刻
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moments/(?P<id>\d+)/report', [
        'methods'  => 'POST',
        'callback' => 'ppo_report_moment',
        'permission_callback' => 'ppo_rest_write_permission',
    ]);
});

// 获取用户通知列表
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moment-notifications', [
        'methods'  => 'GET',
        'callback' => 'ppo_get_notifications_api',
        'permission_callback' => 'ppo_rest_logged_in_permission',
    ]);
});

// 标记通知已读
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moment-notifications/read', [
        'methods'  => 'POST',
        'callback' => 'ppo_mark_notifications_read_api',
        'permission_callback' => 'ppo_rest_write_permission',
    ]);
});

// 获取未读通知数量
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/moment-notifications/count', [
        'methods'  => 'GET',
        'callback' => 'ppo_get_notification_count_api',
        'permission_callback' => 'ppo_rest_logged_in_permission',
    ]);
});



// ==================== 网易云音乐歌单解析 ====================
add_action('rest_api_init', function () {
    register_rest_route('ppo/v1', '/netease-playlist', [
        'methods'             => 'GET',
        'callback'            => 'pix_netease_playlist_api',
        'permission_callback' => '__return_true',
        'args'                => [
            'url' => [
                'required' => true,
                'type'     => 'string',
            ],
        ],
    ]);
});

function pix_netease_playlist_api($request) {
    $url = sanitize_text_field($request->get_param('url'));
    $id = pix_extract_netease_playlist_id($url);
    if (!$id) {
        return new WP_Error('invalid_url', '无效的网易云歌单链接', ['status' => 400]);
    }

    $headers = [
        'Referer'    => 'https://music.163.com/',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
    ];

    // 1. 获取歌单详情
    $playlist_resp = wp_remote_get(
        'https://music.163.com/api/v6/playlist/detail?id=' . urlencode($id) . '&n=100000&s=8',
        ['headers' => $headers, 'timeout' => 15]
    );
    if (is_wp_error($playlist_resp)) {
        return new WP_Error('fetch_failed', '获取歌单失败', ['status' => 502]);
    }
    $playlist_body = json_decode(wp_remote_retrieve_body($playlist_resp), true);
    $playlist = $playlist_body['result'] ?? $playlist_body['playlist'] ?? null;
    if (!$playlist) {
        return new WP_Error('not_found', '歌单不存在', ['status' => 404]);
    }

    // 2. 提取歌曲 ID
    $track_ids = [];
    if (!empty($playlist['trackIds'])) {
        foreach ($playlist['trackIds'] as $t) {
            if (!empty($t['id'])) {
                $track_ids[] = $t['id'];
            }
        }
    }

    // 3. 获取歌曲详情
    $tracks = [];
    if (!empty($track_ids)) {
        $tracks = pix_netease_get_songs($track_ids, $headers);
    } elseif (!empty($playlist['tracks'])) {
        $tracks = $playlist['tracks'];
    }

    // 4. 按歌单顺序排序
    if (!empty($track_ids) && !empty($tracks)) {
        $order = array_flip(array_map('strval', $track_ids));
        usort($tracks, function ($a, $b) use ($order) {
            $ia = $order[strval($a['id'] ?? '')] ?? 0;
            $ib = $order[strval($b['id'] ?? '')] ?? 0;
            return $ia - $ib;
        });
    }

    // 5. 格式化
    $formatted = array_map('pix_format_netease_song', $tracks);
    $formatted = array_values(array_filter($formatted, function ($t) {
        return !empty($t['id']);
    }));

    // 6. 注入音频地址
    $formatted = pix_netease_inject_audio_urls($formatted, $headers);

    return [
        'id'         => $id,
        'title'      => $playlist['name'] ?? '',
        'cover'      => $playlist['coverImgUrl'] ?? '',
        'trackCount' => count($track_ids) ?: count($tracks),
        'tracks'     => $formatted,
    ];
}

function pix_extract_netease_playlist_id($url) {
    if (preg_match('/[?&#]id=(\d+)/', $url, $m)) {
        return $m[1];
    }
    if (preg_match('#playlist/(\d+)#', $url, $m)) {
        return $m[1];
    }
    return '';
}

function pix_netease_get_songs($ids, $headers) {
    $songs = [];
    foreach (array_chunk($ids, 200) as $chunk) {
        $resp = wp_remote_get(
            'https://music.163.com/api/song/detail/?ids=[' . implode(',', array_map('urlencode', array_map('strval', $chunk))) . ']',
            ['headers' => $headers, 'timeout' => 15]
        );
        if (is_wp_error($resp)) {
            continue;
        }
        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if (!empty($body['songs'])) {
            $songs = array_merge($songs, $body['songs']);
        }
    }
    return $songs;
}

function pix_format_netease_song($song) {
    $id = isset($song['id']) ? strval($song['id']) : '';
    $artists = $song['artists'] ?? $song['ar'] ?? [];
    $artist_names = [];
    foreach ($artists as $a) {
        if (!empty($a['name'])) {
            $artist_names[] = $a['name'];
        }
    }
    $album = $song['album'] ?? $song['al'] ?? [];
    $duration = $song['duration'] ?? $song['dt'] ?? 0;
    $duration = is_numeric($duration) && $duration > 0 ? intval($duration / 1000) : 0;

    return [
        'id'          => $id,
        'title'       => $song['name'] ?? '',
        'artist'      => implode(' / ', $artist_names),
        'cover'       => $album['picUrl'] ?? '',
        'duration'    => $duration,
        'externalUrl' => $id ? 'https://music.163.com/#/song?id=' . $id : '',
        'embedUrl'    => $id ? 'https://music.163.com/outchain/player?type=2&id=' . $id . '&auto=0&height=66' : '',
        'audioUrl'    => '',
    ];
}

function pix_netease_inject_audio_urls($tracks, $headers) {
    $ids = [];
    foreach ($tracks as $t) {
        if (!empty($t['id'])) {
            $ids[] = $t['id'];
        }
    }
    if (empty($ids)) {
        return $tracks;
    }

    $resp = wp_remote_post('https://music.163.com/api/song/enhance/player/url', [
        'headers' => array_merge(['Content-Type' => 'application/x-www-form-urlencoded'], $headers),
        'body'    => 'ids=[' . implode(',', $ids) . ']&br=320000',
        'timeout' => 15,
    ]);
    if (is_wp_error($resp)) {
        return $tracks;
    }
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    $url_map = [];
    if (!empty($body['data'])) {
        foreach ($body['data'] as $item) {
            if (!empty($item['id']) && !empty($item['url'])) {
                $url_map[strval($item['id'])] = $item['url'];
            }
        }
    }
    foreach ($tracks as &$track) {
        if (!empty($url_map[$track['id']])) {
            $track['audioUrl'] = $url_map[$track['id']];
        }
    }
    return $tracks;
}
