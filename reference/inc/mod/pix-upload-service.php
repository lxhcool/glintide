<?php
/**
 * Pix unified upload service.
 *
 * This is the lightweight upload foundation for PixUploader/PixEditor. It runs
 * next to the legacy FileUploader integration until each feature is migrated.
 */

if (!defined('ABSPATH')) {
    exit;
}

function pix_upload_contexts() {
    $image_mimes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');

    return array(
        'moment_gallery' => array(
            'label' => '片刻图片',
            'type' => 'image',
            'max_size' => (float) get_op('image_max_size', 3),
            'limit' => (int) get_op('mo_gallery_num', 9),
            'mimes' => $image_mimes,
            'convert_webp' => false,
            'quality' => 86,
        ),
        'moment_asset' => array(
            'label' => '片刻附件',
            'type' => 'asset',
            'max_size' => max((float) get_op('image_max_size', 3), (float) get_op('video_max_size', 20), (float) get_op('file_max_size', 10)),
            'limit' => max((int) get_op('mo_gallery_num', 9), (int) get_op('mo_file_num', 3)),
            'mimes' => array(
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'video/mp4',
                'video/webm',
                'video/ogg',
                'video/quicktime',
                'text/plain',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip',
            ),
            'convert_webp' => false,
            'quality' => 86,
        ),
        'moment_video' => array(
            'label' => '片刻视频',
            'type' => 'video',
            'max_size' => (float) get_op('video_max_size', 20),
            'limit' => 1,
            'mimes' => array('video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'),
        ),
        'moment_file' => array(
            'label' => '片刻附件',
            'type' => 'file',
            'max_size' => (float) get_op('file_max_size', 10),
            'limit' => (int) get_op('mo_file_num', 3),
            'mimes' => array(
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'text/plain',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip',
            ),
        ),
        'comment_image' => array(
            'label' => '评论图片',
            'type' => 'image',
            'max_size' => max(1, min(20, (float) get_op('comment_image_max_size', 2))),
            'limit' => max(1, min(12, (int) get_op('comment_image_limit', 4))),
            'mimes' => $image_mimes,
            'convert_webp' => false,
            'quality' => 86,
        ),
        'circle_banner' => array(
            'label' => '圈子头图',
            'type' => 'image',
            'max_size' => 2,
            'limit' => 1,
            'mimes' => $image_mimes,
            'convert_webp' => false,
            'quality' => 86,
        ),
        'circle_logo' => array(
            'label' => '圈子图标',
            'type' => 'image',
            'max_size' => 2,
            'limit' => 1,
            'mimes' => $image_mimes,
            'convert_webp' => false,
            'quality' => 86,
        ),
        'avatar' => array(
            'label' => '用户头像',
            'type' => 'image',
            'max_size' => 2,
            'limit' => 1,
            'mimes' => $image_mimes,
            'convert_webp' => false,
            'quality' => 86,
        ),
        'user_cover' => array(
            'label' => '用户封面',
            'type' => 'image',
            'max_size' => 3,
            'limit' => 1,
            'mimes' => $image_mimes,
            'convert_webp' => false,
            'quality' => 86,
        ),
        'post_thumb' => array(
            'label' => '文章缩略图',
            'type' => 'image',
            'max_size' => 3,
            'limit' => 1,
            'mimes' => $image_mimes,
            'convert_webp' => false,
            'quality' => 86,
        ),
    );
}

function pix_upload_get_context($context) {
    $context = sanitize_key($context);
    $contexts = pix_upload_contexts();

    if (!isset($contexts[$context])) {
        return false;
    }

    $config = $contexts[$context];
    $config['key'] = $context;

    return $config;
}

function pix_upload_send_error($message, $code = 400) {
    wp_send_json(array(
        'status' => 0,
        'msg' => $message,
    ), $code);
}

function pix_upload_require_user() {
    if (!is_user_logged_in()) {
        pix_upload_send_error('请先登录后再上传', 401);
    }

    if (!check_ajax_referer('pix_upload_action', 'nonce', false)) {
        pix_upload_send_error('页面验证已过期，请刷新后重试', 403);
    }
}

function pix_upload_normalize_mime($mime) {
    $mime = strtolower(trim((string) $mime));
    $aliases = array(
        'image/pjpeg' => 'image/jpeg',
        'image/x-png' => 'image/png',
        'application/x-zip-compressed' => 'application/zip',
    );

    return $aliases[$mime] ?? $mime;
}

function pix_upload_detect_mime($file_path, $filename = '') {
    if (!file_exists($file_path)) {
        return '';
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $file_path);
            finfo_close($finfo);
            $mime = pix_upload_normalize_mime($mime);
            if ($mime && $mime !== 'application/octet-stream') {
                return $mime;
            }
        }
    }

    if (function_exists('getimagesize')) {
        $image_info = @getimagesize($file_path);
        if (!empty($image_info['mime'])) {
            return pix_upload_normalize_mime($image_info['mime']);
        }
    }

    $filename = $filename ? sanitize_file_name($filename) : basename($file_path);
    $check = wp_check_filetype_and_ext($file_path, $filename);
    if (!empty($check['type'])) {
        return pix_upload_normalize_mime($check['type']);
    }

    $check = wp_check_filetype($filename);
    return !empty($check['type']) ? pix_upload_normalize_mime($check['type']) : '';
}

function pix_upload_kind_from_mime($mime) {
    $group = strtok((string) $mime, '/');
    if (in_array($group, array('image', 'video', 'audio'), true)) {
        return $group;
    }

    return 'file';
}

function pix_upload_kind_label($kind) {
    if ($kind === 'image') {
        return '图片';
    }

    if ($kind === 'video') {
        return '视频';
    }

    if ($kind === 'audio') {
        return '音频';
    }

    return '文件';
}

function pix_upload_normalize_files($field = 'files') {
    if (empty($_FILES[$field])) {
        return array();
    }

    $input = $_FILES[$field];
    if (!is_array($input['name'])) {
        return array($input);
    }

    $files = array();
    foreach ($input['name'] as $index => $name) {
        $files[] = array(
            'name' => $name,
            'type' => $input['type'][$index] ?? '',
            'tmp_name' => $input['tmp_name'][$index] ?? '',
            'error' => $input['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $input['size'][$index] ?? 0,
        );
    }

    return $files;
}

function pix_upload_prepare_file($file, $config) {
    if (empty($file['tmp_name']) || !empty($file['error'])) {
        return new WP_Error('upload_error', '文件上传失败，请重试');
    }

    $max_size = max(1, (float) $config['max_size']) * 1024 * 1024;
    if (!empty($file['size']) && (int) $file['size'] > $max_size) {
        return new WP_Error('file_too_large', pix_upload_kind_label($config['type'] ?? 'file') . '最大尺寸为 ' . $config['max_size'] . 'MB');
    }

    $real_mime = pix_upload_detect_mime($file['tmp_name'], $file['name'] ?? '');
    if (!$real_mime || !in_array($real_mime, $config['mimes'], true)) {
        return new WP_Error('invalid_mime', '文件类型不符合要求');
    }

    $file['type'] = $real_mime;
    $file['name'] = sanitize_file_name($file['name']);

    return $file;
}

function pix_upload_insert_attachment($file, $config) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $prepared = pix_upload_prepare_file($file, $config);
    if (is_wp_error($prepared)) {
        return $prepared;
    }

    $overrides = array(
        'test_form' => false,
        'mimes' => get_allowed_mime_types(),
    );

    $upload = wp_handle_sideload($prepared, $overrides);
    if (!empty($upload['error'])) {
        return new WP_Error('upload_failed', $upload['error']);
    }

    $attachment = array(
        'guid' => $upload['url'],
        'post_mime_type' => $upload['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
        'post_content' => '',
        'post_status' => 'inherit',
        'post_author' => get_current_user_id(),
    );

    $attach_id = wp_insert_attachment($attachment, $upload['file']);
    if (is_wp_error($attach_id)) {
        return $attach_id;
    }

    update_post_meta($attach_id, '_pix_upload_context', $config['key']);
    if (!empty($config['term_id'])) {
        update_post_meta($attach_id, '_pix_upload_term_id', absint($config['term_id']));
    }
    update_post_meta($attach_id, '_pix_upload_type', strtok((string) $upload['type'], '/'));

    $metadata = wp_generate_attachment_metadata($attach_id, $upload['file']);
    if (!is_wp_error($metadata) && !empty($metadata)) {
        wp_update_attachment_metadata($attach_id, $metadata);
    }

    return pix_upload_format_attachment($attach_id);
}

function pix_upload_format_attachment($attach_id) {
    $attach_id = absint($attach_id);
    $post = get_post($attach_id);
    if (!$post || $post->post_type !== 'attachment') {
        return null;
    }

    $mime = get_post_mime_type($attach_id);
    $url = wp_get_attachment_url($attach_id);
    $thumb = wp_get_attachment_image_url($attach_id, 'medium');
    $mime_group = strtok((string) $mime, '/');
    $type = in_array($mime_group, array('image', 'video', 'audio'), true) ? $mime_group : 'file';
    $poster_id = $type === 'video' ? absint(get_post_meta($attach_id, '_pix_video_poster_id', true)) : 0;
    $poster = $poster_id ? wp_get_attachment_image_url($poster_id, 'medium') : '';
    $file_path = get_attached_file($attach_id);
    $size = ($file_path && file_exists($file_path)) ? filesize($file_path) : 0;
    $metadata = wp_get_attachment_metadata($attach_id);
    $width = is_array($metadata) && !empty($metadata['width']) ? absint($metadata['width']) : 0;
    $height = is_array($metadata) && !empty($metadata['height']) ? absint($metadata['height']) : 0;
    $usage = pix_upload_attachment_usage($attach_id);
    $context = get_post_meta($attach_id, '_pix_upload_context', true);
    $source = $context ? 'pix_upload' : 'wp_library';
    $can_delete = $source === 'pix_upload' && !in_array($context, pix_upload_system_contexts(), true);

    return array(
        'id' => $attach_id,
        'title' => get_the_title($attach_id),
        'filename' => $file_path ? wp_basename($file_path) : wp_basename((string) $url),
        'url' => $url,
        'thumb' => $poster ? $poster : ($thumb ? $thumb : $url),
        'poster_id' => $poster_id,
        'poster' => $poster,
        'mime' => $mime,
        'size' => $size,
        'width' => $width,
        'height' => $height,
        'type' => $type,
        'context' => $context,
        'date' => get_the_date('Y-m-d H:i:s', $attach_id),
        'used' => $usage['used'],
        'used_count' => $usage['count'],
        'source' => $source,
        'can_delete' => $can_delete,
    );
}

function pix_upload_system_contexts() {
    return array('avatar', 'user_cover', 'circle_banner', 'circle_logo');
}

function pix_upload_frontend_include_wp_library() {
    return (bool) get_op('pix_upload_include_wp_library', true);
}

function pix_upload_can_include_wp_library_for_context($context) {
    $context = sanitize_key($context);
    if (!pix_upload_frontend_include_wp_library()) {
        return false;
    }

    if (!$context) {
        return true;
    }

    return !in_array($context, pix_upload_system_contexts(), true);
}

function pix_upload_is_user_system_attachment($attach_id, $user_id = 0) {
    $attach_id = absint($attach_id);
    $user_id = $user_id ? absint($user_id) : get_current_user_id();
    if (!$attach_id || !$user_id) {
        return false;
    }

    $context = get_post_meta($attach_id, '_pix_upload_context', true);
    if ($context && in_array($context, pix_upload_system_contexts(), true)) {
        return true;
    }

    $system_attachment_ids = array(
        absint(get_user_meta($user_id, 'upload_avatar_attachment_id', true)),
        absint(get_user_meta($user_id, 'user_cover_attachment_id', true)),
    );

    return in_array($attach_id, array_filter($system_attachment_ids), true);
}

function pix_upload_mimes_for_library_type($type) {
    $type = sanitize_key($type);
    $mimes = get_allowed_mime_types();
    $matched = array();

    foreach ($mimes as $mime) {
        $group = strtok((string) $mime, '/');
        if ($type === 'file') {
            if (!in_array($group, array('image', 'video', 'audio'), true)) {
                $matched[] = $mime;
            }
        } elseif ($type && $group === $type) {
            $matched[] = $mime;
        }
    }

    return array_values(array_unique($matched));
}

function pix_upload_library_pix_type_meta_query($type) {
    $type = sanitize_key($type);
    if ($type === 'file') {
        return array(
            'key' => '_pix_upload_type',
            'value' => array('application', 'text', 'file'),
            'compare' => 'IN',
        );
    }

    if ($type) {
        return array(
            'key' => '_pix_upload_type',
            'value' => $type,
        );
    }

    return null;
}

function pix_upload_query_library_ids($context, $type, $keyword) {
    $user_id = get_current_user_id();
    $exclude_poster = array(
        'key' => '_pix_upload_is_video_poster',
        'compare' => 'NOT EXISTS',
    );

    $pix_meta_query = array($exclude_poster);
    if ($context) {
        $pix_meta_query[] = array(
            'key' => '_pix_upload_context',
            'value' => $context,
        );
    } else {
        $pix_meta_query[] = array(
            'key' => '_pix_upload_context',
            'compare' => 'EXISTS',
        );
    }

    $type_meta = pix_upload_library_pix_type_meta_query($type);
    if ($type_meta) {
        $pix_meta_query[] = $type_meta;
    }

    $base_args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        's' => $keyword,
    );

    $pix_query = new WP_Query(array_merge($base_args, array(
        'meta_query' => $pix_meta_query,
    )));
    $ids = array_map('absint', $pix_query->posts);

    if (pix_upload_can_include_wp_library_for_context($context)) {
        $wp_args = array_merge($base_args, array(
            'meta_query' => array(
                $exclude_poster,
                array(
                    'key' => '_pix_upload_context',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        ));

        if ($type) {
            $mimes = pix_upload_mimes_for_library_type($type);
            if (empty($mimes)) {
                $wp_args = null;
            } else {
                $wp_args['post_mime_type'] = $mimes;
            }
        }

        if ($wp_args) {
            $wp_query = new WP_Query($wp_args);
            $ids = array_merge($ids, array_map('absint', $wp_query->posts));
        }
    }

    $ids = array_values(array_unique(array_filter($ids)));
    return array_values(array_filter($ids, function($attach_id) use ($user_id) {
        return !pix_upload_is_user_system_attachment($attach_id, $user_id);
    }));
}

function pix_upload_user_total_size($user_id) {
    $query = new WP_Query(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'author' => absint($user_id),
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => array(
            array(
                'key' => '_pix_upload_is_video_poster',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ));

    $total = 0;
    foreach ($query->posts as $attach_id) {
        $file_path = get_attached_file($attach_id);
        if ($file_path && file_exists($file_path)) {
            $total += filesize($file_path);
        }
    }

    return $total;
}

function pix_upload_attachment_file_size($attach_id) {
    $file_path = get_attached_file($attach_id);
    return ($file_path && file_exists($file_path)) ? filesize($file_path) : 0;
}

function pix_upload_attachment_usage($attach_id) {
    $attach_id = absint($attach_id);
    if (!$attach_id) {
        return array('used' => false, 'count' => 0, 'posts' => array());
    }

    $url = wp_get_attachment_url($attach_id);
    $meta_query = array(
        'relation' => 'OR',
        array('key' => 'moment_ga', 'value' => '"' . $attach_id . '"', 'compare' => 'LIKE'),
        array('key' => 'moment_ga', 'value' => 'i:' . $attach_id . ';', 'compare' => 'LIKE'),
        array('key' => 'moment_video', 'value' => '"' . $attach_id . '"', 'compare' => 'LIKE'),
        array('key' => 'moment_video', 'value' => 'i:' . $attach_id . ';', 'compare' => 'LIKE'),
        array('key' => 'moment_file', 'value' => '"' . $attach_id . '"', 'compare' => 'LIKE'),
        array('key' => 'moment_file', 'value' => 'i:' . $attach_id . ';', 'compare' => 'LIKE'),
    );

    if ($url) {
        $meta_query[] = array(
            'key' => 'moment_ga',
            'value' => esc_url_raw($url),
            'compare' => 'LIKE',
        );
    }

    $query = new WP_Query(array(
        'post_type' => 'moment',
        'post_status' => array('publish', 'pending', 'draft', 'private'),
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => $meta_query,
    ));

    $posts = array_values(array_unique(array_map('absint', $query->posts)));
    return array(
        'used' => !empty($posts),
        'count' => count($posts),
        'posts' => array_slice($posts, 0, 5),
    );
}

function pix_upload_limit_settings() {
    return array(
        'rate_per_minute' => max(0, (int) get_op('pix_upload_rate_per_minute', 20)),
        'daily_file_limit' => max(0, (int) get_op('pix_upload_daily_file_limit', 100)),
        'daily_size_limit' => max(0, (float) get_op('pix_upload_daily_size_limit', 300)),
    );
}

function pix_upload_day_key() {
    return current_time('Ymd');
}

function pix_upload_seconds_until_tomorrow() {
    $now = current_time('timestamp');
    $tomorrow = strtotime('tomorrow', $now);
    return max(60, $tomorrow - $now + 60);
}

function pix_upload_files_total_size($files) {
    $total = 0;
    foreach ((array) $files as $file) {
        $total += !empty($file['size']) ? absint($file['size']) : 0;
    }
    return $total;
}

function pix_upload_daily_usage_key($user_id) {
    return 'pix_upload_daily_' . absint($user_id) . '_' . pix_upload_day_key();
}

function pix_upload_daily_usage_from_library($user_id) {
    $start = date('Y-m-d 00:00:00', current_time('timestamp'));
    $end = date('Y-m-d 23:59:59', current_time('timestamp'));
    $query = new WP_Query(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'author' => absint($user_id),
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'date_query' => array(
            array(
                'after' => $start,
                'before' => $end,
                'inclusive' => true,
            ),
        ),
        'meta_query' => array(
            array(
                'key' => '_pix_upload_is_video_poster',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ));

    $size = 0;
    foreach ($query->posts as $attach_id) {
        $size += pix_upload_attachment_file_size($attach_id);
    }

    return array(
        'count' => count($query->posts),
        'size' => $size,
    );
}

function pix_upload_daily_usage($user_id) {
    $key = pix_upload_daily_usage_key($user_id);
    $usage = get_transient($key);
    if (is_array($usage)) {
        $usage['count'] = isset($usage['count']) ? absint($usage['count']) : 0;
        $usage['size'] = isset($usage['size']) ? absint($usage['size']) : 0;
        return $usage;
    }

    $usage = pix_upload_daily_usage_from_library($user_id);
    set_transient($key, $usage, pix_upload_seconds_until_tomorrow());
    return $usage;
}

function pix_upload_check_rate_limit($user_id) {
    $settings = pix_upload_limit_settings();
    $limit = (int) $settings['rate_per_minute'];
    if ($limit <= 0 || current_user_can('manage_options')) {
        return;
    }

    $key = 'pix_upload_rate_' . absint($user_id) . '_' . floor(time() / 60);
    $count = (int) get_transient($key);
    if ($count >= $limit) {
        pix_upload_send_error('上传太频繁了，请稍后再试', 429);
    }

    set_transient($key, $count + 1, 90);
}

function pix_upload_check_daily_limits($user_id, $files) {
    $settings = pix_upload_limit_settings();
    if (current_user_can('manage_options')) {
        return;
    }

    $file_limit = (int) $settings['daily_file_limit'];
    $size_limit_bytes = (float) $settings['daily_size_limit'] * 1024 * 1024;
    if ($file_limit <= 0 && $size_limit_bytes <= 0) {
        return;
    }

    $usage = pix_upload_daily_usage($user_id);
    $file_count = count((array) $files);
    $file_size = pix_upload_files_total_size($files);

    if ($file_limit > 0 && $usage['count'] + $file_count > $file_limit) {
        pix_upload_send_error('今日上传附件数量已达上限', 429);
    }

    if ($size_limit_bytes > 0 && $usage['size'] + $file_size > $size_limit_bytes) {
        pix_upload_send_error('今日上传总量已达上限', 429);
    }
}

function pix_upload_record_daily_usage($user_id, $count, $size) {
    if (current_user_can('manage_options')) {
        return;
    }

    $settings = pix_upload_limit_settings();
    if ((int) $settings['daily_file_limit'] <= 0 && (float) $settings['daily_size_limit'] <= 0) {
        return;
    }

    $key = pix_upload_daily_usage_key($user_id);
    $usage = pix_upload_daily_usage($user_id);
    $usage['count'] = absint($usage['count']) + absint($count);
    $usage['size'] = absint($usage['size']) + absint($size);
    set_transient($key, $usage, pix_upload_seconds_until_tomorrow());
}

function pix_ajax_upload_asset() {
    pix_upload_require_user();

    $user_id = get_current_user_id();
    $config = pix_upload_get_context($_POST['context'] ?? '');
    if (!$config) {
        pix_upload_send_error('上传场景不存在');
    }
    if (($config['key'] ?? '') === 'comment_image' && !get_op('comment_image_enable', true)) {
        pix_upload_send_error('评论图片上传已关闭', 403);
    }

    $files = pix_upload_normalize_files('files');
    if (empty($files)) {
        pix_upload_send_error('请选择需要上传的文件');
    }

    $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
    $poster_for = isset($_POST['poster_for']) ? absint($_POST['poster_for']) : 0;
    $is_moment_context = strpos((string) $config['key'], 'moment_') === 0;
    $effective_kind = '';
    $skip_user_upload_limit = false;

    if ($is_moment_context) {
        if (!$term_id) {
            pix_upload_send_error('请选择圈子后再上传');
        }

        foreach ($files as $file) {
            $mime = pix_upload_detect_mime($file['tmp_name'] ?? '', $file['name'] ?? '');
            $kind = pix_upload_kind_from_mime($mime);
            if ($poster_for) {
                $kind = 'video_poster';
            }
            if ($effective_kind && $effective_kind !== $kind) {
                pix_upload_send_error('一次只能上传同一种类型的附件');
            }
            $effective_kind = $kind;
        }

        if ($poster_for) {
            $policy = pix_moment_upload_policy(get_current_user_id(), $term_id, 'video');
            if (is_wp_error($policy)) {
                pix_upload_send_error($policy->get_error_message(), 403);
            }
            $video = get_post($poster_for);
            if (!$video || $video->post_type !== 'attachment' || ((int) $video->post_author !== get_current_user_id() && !current_user_can('manage_options'))) {
                pix_upload_send_error('视频附件无效或无权设置封面', 403);
            }

            $skip_user_upload_limit = true;
            $config['max_size'] = (float) get_op('image_max_size', 3);
            $config['limit'] = 1;
            $config['mimes'] = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        } else {
            $kind = $effective_kind === 'image' ? 'image' : ($effective_kind === 'video' ? 'video' : 'file');
            $policy = pix_moment_upload_policy(get_current_user_id(), $term_id, $kind);
            if (is_wp_error($policy)) {
                pix_upload_send_error($policy->get_error_message(), 403);
            }

            $config['max_size'] = $policy['max_size'];
            $config['limit'] = $policy['limit'];
            $config['mimes'] = $policy['mimes'];
        }

        $config['term_id'] = $term_id;
    }

    $upload_kind = $config['type'] ?? 'image';
    $permission_scene = 'image';
    $context_key = $config['key'] ?? '';
    if ($is_moment_context) {
        $upload_kind = $poster_for ? 'image' : ($effective_kind === 'video' ? 'video' : (($effective_kind === 'file') ? 'file' : 'image'));
        $permission_scene = $poster_for ? 'moment_image' : ($upload_kind === 'video' ? 'video' : ($upload_kind === 'file' ? 'file' : 'moment_image'));
    } elseif ($context_key === 'avatar') {
        $permission_scene = 'avatar';
    } elseif ($context_key === 'user_cover') {
        $permission_scene = 'cover';
    } elseif ($context_key === 'comment_image') {
        $permission_scene = 'comment_image';
    } elseif ($context_key === 'post_thumb') {
        $permission_scene = 'post_image';
    } elseif ($upload_kind === 'video') {
        $permission_scene = 'video';
    } elseif ($upload_kind === 'file') {
        $permission_scene = 'file';
    }

    if (function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)) {
        if (function_exists('pix_normal_user_upload_allowed')) {
            if (!pix_normal_user_upload_allowed($permission_scene, $user_id)) {
                $permission_messages = array(
                    'avatar' => '普通用户暂不能修改头像',
                    'cover' => '普通用户暂不能修改封面',
                    'comment_image' => '普通用户暂不能上传评论图片',
                    'moment_image' => '普通用户暂不能上传片刻图片',
                    'post_image' => '普通用户暂不能上传文章图片',
                    'video' => '普通用户暂不能上传视频',
                    'file' => '普通用户暂不能上传文件',
                    'image' => '普通用户暂不能上传图片',
                );
                pix_upload_send_error($permission_messages[$permission_scene] ?? '普通用户暂不能上传文件', 403);
            }
        } elseif ($upload_kind === 'video') {
            if (!function_exists('pix_normal_user_can') || !pix_normal_user_can('normal_user_allow_upload_video', false, $user_id)) {
                pix_upload_send_error('普通用户暂不能上传视频', 403);
            }
        } elseif ($upload_kind === 'file') {
            if (!function_exists('pix_normal_user_can') || !pix_normal_user_can('normal_user_allow_upload_file', false, $user_id)) {
                pix_upload_send_error('普通用户暂不能上传文件', 403);
            }
        } else {
            if (!function_exists('pix_normal_user_can') || !pix_normal_user_can('normal_user_allow_upload_image', true, $user_id)) {
                pix_upload_send_error('普通用户暂不能上传图片', 403);
            }
        }
    }

    if (count($files) > (int) $config['limit']) {
        pix_upload_send_error('最多只能上传 ' . (int) $config['limit'] . ' 个文件');
    }

    $original_size = isset($_POST['original_size']) ? absint($_POST['original_size']) : 0;
    $max_size_bytes = max(1, (float) $config['max_size']) * 1024 * 1024;
    if ($original_size && $original_size > $max_size_bytes) {
        $limit_kind = $effective_kind === 'video_poster' ? 'image' : ($effective_kind ?: ($config['type'] ?? 'file'));
        pix_upload_send_error(pix_upload_kind_label($limit_kind) . '最大尺寸为 ' . $config['max_size'] . 'MB');
    }

    if (!$skip_user_upload_limit) {
        pix_upload_check_rate_limit($user_id);
        pix_upload_check_daily_limits($user_id, $files);
    }

    $items = array();
    foreach ($files as $file) {
        $item = pix_upload_insert_attachment($file, $config);
        if (is_wp_error($item)) {
            pix_upload_send_error($item->get_error_message());
        }
        if ($poster_for && !empty($item['id'])) {
            $video = get_post($poster_for);
            if ($video && $video->post_type === 'attachment' && ((int) $video->post_author === get_current_user_id() || current_user_can('manage_options'))) {
                update_post_meta(absint($item['id']), '_pix_upload_is_video_poster', 1);
                update_post_meta($poster_for, '_pix_video_poster_id', absint($item['id']));
            }
        }
        $items[] = $item;
    }

    if (($config['key'] ?? '') === 'avatar' && !empty($items[0]['url'])) {
        update_user_meta($user_id, 'custom_avatar', esc_url_raw($items[0]['url']));
        update_user_meta($user_id, 'upload_avatar', esc_url_raw($items[0]['url']));
        if (!empty($items[0]['id'])) {
            update_user_meta($user_id, 'upload_avatar_attachment_id', absint($items[0]['id']));
        }
        do_action('ppo_user_uploaded_avatar', $user_id, esc_url_raw($items[0]['url']));
    }

    if (($config['key'] ?? '') === 'user_cover' && !empty($items[0]['url']) && !empty($items[0]['id'])) {
        $old_attachment_id = get_user_meta($user_id, 'user_cover_attachment_id', true);
        if ($old_attachment_id && absint($old_attachment_id) !== absint($items[0]['id'])) {
            wp_delete_attachment(absint($old_attachment_id), true);
        }
        update_user_meta($user_id, 'user_cover_image', esc_url_raw($items[0]['url']));
        update_user_meta($user_id, 'user_cover_attachment_id', absint($items[0]['id']));
        do_action('ppo_user_uploaded_banner', $user_id, absint($items[0]['id']));
    }

    if (!$skip_user_upload_limit) {
        pix_upload_record_daily_usage($user_id, count($items), pix_upload_files_total_size($files));
    }

    wp_send_json(array(
        'status' => 1,
        'msg' => '上传成功',
        'items' => $items,
    ));
}
add_action('wp_ajax_pix_upload_asset', 'pix_ajax_upload_asset');

function pix_ajax_media_library() {
    pix_upload_require_user();

    $context = isset($_POST['context']) ? sanitize_key($_POST['context']) : '';
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
    $page = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;
    $per_page = isset($_POST['per_page']) ? min(48, max(1, absint($_POST['per_page']))) : 24;
    $orderby = isset($_POST['orderby']) ? sanitize_key($_POST['orderby']) : 'date';
    $order = isset($_POST['order']) ? strtoupper(sanitize_key($_POST['order'])) : 'DESC';
    $orderby = in_array($orderby, array('date', 'size'), true) ? $orderby : 'date';
    $order = in_array($order, array('ASC', 'DESC'), true) ? $order : 'DESC';

    $ids = pix_upload_query_library_ids($context, $type, $keyword);

    if ($orderby === 'size') {
        usort($ids, function($a, $b) use ($order) {
            $size_a = pix_upload_attachment_file_size($a);
            $size_b = pix_upload_attachment_file_size($b);
            if ($size_a === $size_b) {
                return $order === 'ASC' ? $a <=> $b : $b <=> $a;
            }
            return $order === 'ASC' ? $size_a <=> $size_b : $size_b <=> $size_a;
        });
    } else {
        usort($ids, function($a, $b) use ($order) {
            $time_a = get_post_time('U', true, $a);
            $time_b = get_post_time('U', true, $b);
            if ($time_a === $time_b) {
                return $order === 'ASC' ? $a <=> $b : $b <=> $a;
            }
            return $order === 'ASC' ? $time_a <=> $time_b : $time_b <=> $time_a;
        });
    }

    $total = count($ids);
    $total_pages = max(1, (int) ceil($total / $per_page));
    $page = min($page, $total_pages);
    $paged_ids = array_slice($ids, ($page - 1) * $per_page, $per_page);

    $items = array();
    foreach ($paged_ids as $attach_id) {
        $formatted = pix_upload_format_attachment($attach_id);
        if ($formatted) {
            $items[] = $formatted;
        }
    }

    wp_send_json(array(
        'status' => 1,
        'items' => $items,
        'total' => $total,
        'total_size' => pix_upload_user_total_size(get_current_user_id()),
        'total_pages' => $total_pages,
        'current_page' => $page,
        'orderby' => $orderby,
        'order' => $order,
    ));
}
add_action('wp_ajax_pix_media_library', 'pix_ajax_media_library');

function pix_ajax_delete_media() {
    pix_upload_require_user();

    $attach_id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    $deleted = pix_upload_delete_attachment($attach_id);
    if (is_wp_error($deleted)) {
        pix_upload_send_error($deleted->get_error_message(), (int) ($deleted->get_error_data('status') ?: 400));
    }

    wp_send_json(array(
        'status' => 1,
        'msg' => '删除成功',
    ));
}
add_action('wp_ajax_pix_delete_media', 'pix_ajax_delete_media');

function pix_upload_delete_attachment($attach_id) {
    $attach_id = absint($attach_id);
    $attachment = get_post($attach_id);
    if (!$attachment || $attachment->post_type !== 'attachment') {
        return new WP_Error('pix_attachment_missing', '附件不存在', array('status' => 404));
    }

    if ((int) $attachment->post_author !== get_current_user_id() && !current_user_can('manage_options')) {
        return new WP_Error('pix_attachment_forbidden', '无权删除此附件', array('status' => 403));
    }

    $context = get_post_meta($attach_id, '_pix_upload_context', true);
    if (!$context) {
        return new WP_Error('pix_attachment_wp_library_readonly', '后台媒体库附件只能插入，不能在前台删除', array('status' => 403));
    }

    if (in_array($context, pix_upload_system_contexts(), true)) {
        return new WP_Error('pix_attachment_system_readonly', '系统用途媒体不能在这里删除', array('status' => 403));
    }

    $usage = pix_upload_attachment_usage($attach_id);
    if (!empty($usage['used'])) {
        return new WP_Error('pix_attachment_used', '该媒体正在被片刻使用，不能删除', array('status' => 409));
    }

    $poster_id = absint(get_post_meta($attach_id, '_pix_video_poster_id', true));
    $deleted = wp_delete_attachment($attach_id, true);
    if (!$deleted) {
        return new WP_Error('pix_attachment_delete_failed', '删除失败，请稍后重试');
    }

    if ($poster_id) {
        $poster = get_post($poster_id);
        if ($poster && $poster->post_type === 'attachment' && ((int) $poster->post_author === get_current_user_id() || current_user_can('manage_options'))) {
            wp_delete_attachment($poster_id, true);
        }
    }

    return true;
}

function pix_ajax_delete_media_batch() {
    pix_upload_require_user();

    $ids = isset($_POST['ids']) ? (array) wp_unslash($_POST['ids']) : array();
    $ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
    if (empty($ids)) {
        pix_upload_send_error('请选择需要删除的媒体');
    }

    $deleted = array();
    $skipped = array();
    foreach ($ids as $attach_id) {
        $result = pix_upload_delete_attachment($attach_id);
        if (is_wp_error($result)) {
            $skipped[] = array(
                'id' => $attach_id,
                'msg' => $result->get_error_message(),
            );
            continue;
        }
        $deleted[] = $attach_id;
    }

    wp_send_json(array(
        'status' => 1,
        'msg' => '批量删除完成',
        'deleted' => $deleted,
        'deleted_count' => count($deleted),
        'skipped' => $skipped,
        'skipped_count' => count($skipped),
    ));
}
add_action('wp_ajax_pix_delete_media_batch', 'pix_ajax_delete_media_batch');
