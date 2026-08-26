<?php
/**
 * Glintide REST API - 网易云歌单解析
 *
 * 移植自参考主题 glintide-rest.php 的网易云歌单逻辑
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * 注册 REST 路由
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'glintide/v1',
			'/netease-playlist',
			array(
				'methods'             => 'GET',
				'callback'            => 'glintide_netease_playlist_api',
				'permission_callback' => '__return_true',
				'args'                => array(
					'url' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);
	}
);

/**
 * 歌单解析 API
 */
function glintide_netease_playlist_api( $request ) {
	$url = sanitize_text_field( $request->get_param( 'url' ) );
	$id  = glintide_extract_netease_playlist_id( $url );
	if ( ! $id ) {
		return new WP_Error( 'invalid_url', '无效的网易云歌单链接', array( 'status' => 400 ) );
	}

	$headers = array(
		'Referer'    => 'https://music.163.com/',
		'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
	);

	// 1. 获取歌单详情
	$playlist_resp = wp_remote_get(
		'https://music.163.com/api/v6/playlist/detail?id=' . urlencode( $id ) . '&n=100000&s=8',
		array( 'headers' => $headers, 'timeout' => 15 )
	);
	if ( is_wp_error( $playlist_resp ) ) {
		return new WP_Error( 'fetch_failed', '获取歌单失败', array( 'status' => 502 ) );
	}
	$playlist_body = json_decode( wp_remote_retrieve_body( $playlist_resp ), true );
	$playlist      = $playlist_body['result'] ?? $playlist_body['playlist'] ?? null;
	if ( ! $playlist ) {
		return new WP_Error( 'not_found', '歌单不存在', array( 'status' => 404 ) );
	}

	// 2. 提取歌曲 ID
	$track_ids = array();
	if ( ! empty( $playlist['trackIds'] ) ) {
		foreach ( $playlist['trackIds'] as $t ) {
			if ( ! empty( $t['id'] ) ) {
				$track_ids[] = $t['id'];
			}
		}
	}

	// 3. 获取歌曲详情
	$tracks = array();
	if ( ! empty( $track_ids ) ) {
		$tracks = glintide_netease_get_songs( $track_ids, $headers );
	} elseif ( ! empty( $playlist['tracks'] ) ) {
		$tracks = $playlist['tracks'];
	}

	// 4. 按歌单顺序排序
	if ( ! empty( $track_ids ) && ! empty( $tracks ) ) {
		$order = array_flip( array_map( 'strval', $track_ids ) );
		usort(
			$tracks,
			function ( $a, $b ) use ( $order ) {
				$ia = $order[ strval( $a['id'] ?? '' ) ] ?? 0;
				$ib = $order[ strval( $b['id'] ?? '' ) ] ?? 0;
				return $ia - $ib;
			}
		);
	}

	// 5. 格式化
	$formatted = array_map( 'glintide_format_netease_song', $tracks );
	$formatted = array_values(
		array_filter(
			$formatted,
			function ( $t ) {
				return ! empty( $t['id'] );
			}
		)
	);

	// 6. 注入音频地址
	$formatted = glintide_netease_inject_audio_urls( $formatted, $headers );

	return array(
		'id'         => $id,
		'title'      => $playlist['name'] ?? '',
		'cover'      => $playlist['coverImgUrl'] ?? '',
		'trackCount' => count( $track_ids ) ?: count( $tracks ),
		'tracks'     => $formatted,
	);
}

/**
 * 从歌单链接提取 ID
 */
function glintide_extract_netease_playlist_id( $url ) {
	if ( preg_match( '/[?&#]id=(\d+)/', $url, $m ) ) {
		return $m[1];
	}
	if ( preg_match( '#playlist/(\d+)#', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * 批量获取歌曲详情
 */
function glintide_netease_get_songs( $ids, $headers ) {
	$songs = array();
	foreach ( array_chunk( $ids, 200 ) as $chunk ) {
		$resp = wp_remote_get(
			'https://music.163.com/api/song/detail/?ids=[' . implode( ',', array_map( 'urlencode', array_map( 'strval', $chunk ) ) ) . ']',
			array( 'headers' => $headers, 'timeout' => 15 )
		);
		if ( is_wp_error( $resp ) ) {
			continue;
		}
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( ! empty( $body['songs'] ) ) {
			$songs = array_merge( $songs, $body['songs'] );
		}
	}
	return $songs;
}

/**
 * 格式化歌曲
 */
function glintide_format_netease_song( $song ) {
	$id           = isset( $song['id'] ) ? strval( $song['id'] ) : '';
	$artists      = $song['artists'] ?? $song['ar'] ?? array();
	$artist_names = array();
	foreach ( $artists as $a ) {
		if ( ! empty( $a['name'] ) ) {
			$artist_names[] = $a['name'];
		}
	}
	$album    = $song['album'] ?? $song['al'] ?? array();
	$duration = $song['duration'] ?? $song['dt'] ?? 0;
	$duration = is_numeric( $duration ) && $duration > 0 ? intval( $duration / 1000 ) : 0;

	return array(
		'id'          => $id,
		'title'       => $song['name'] ?? '',
		'artist'      => implode( ' / ', $artist_names ),
		'album'       => $album['name'] ?? '',
		'year'        => ! empty( $song['publishTime'] ) ? gmdate( 'Y', intval( $song['publishTime'] / 1000 ) ) : '',
		'cover'       => $album['picUrl'] ?? '',
		'duration'    => $duration,
		'externalUrl' => $id ? 'https://music.163.com/#/song?id=' . $id : '',
		'embedUrl'    => $id ? 'https://music.163.com/outchain/player?type=2&id=' . $id . '&auto=0&height=66' : '',
		'audioUrl'    => '',
	);
}

/**
 * 注入音频播放地址
 */
function glintide_netease_inject_audio_urls( $tracks, $headers ) {
	$ids = array();
	foreach ( $tracks as $t ) {
		if ( ! empty( $t['id'] ) ) {
			$ids[] = $t['id'];
		}
	}
	if ( empty( $ids ) ) {
		return $tracks;
	}

	$resp = wp_remote_post(
		'https://music.163.com/api/song/enhance/player/url',
		array(
			'headers' => array_merge( array( 'Content-Type' => 'application/x-www-form-urlencoded' ), $headers ),
			'body'    => 'ids=[' . implode( ',', $ids ) . ']&br=320000',
			'timeout' => 15,
		)
	);
	if ( is_wp_error( $resp ) ) {
		return $tracks;
	}
	$body   = json_decode( wp_remote_retrieve_body( $resp ), true );
	$url_map = array();
	if ( ! empty( $body['data'] ) ) {
		foreach ( $body['data'] as $item ) {
			if ( ! empty( $item['id'] ) && ! empty( $item['url'] ) ) {
				$url_map[ strval( $item['id'] ) ] = $item['url'];
			}
		}
	}
	foreach ( $tracks as &$track ) {
		if ( ! empty( $url_map[ $track['id'] ] ) ) {
			$track['audioUrl'] = $url_map[ $track['id'] ];
		}
	}
	return $tracks;
}
