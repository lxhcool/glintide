<?php
function pix_comment_render_image_token($matches, $comment_id, $placeholder = false){
    $url = isset($matches[1]) ? esc_url_raw($matches[1]) : '';
    if(!$url || !wp_http_validate_url($url)){
        return '';
    }

    $content = $placeholder ? '[图片]' : '<img src="'.esc_url($url).'" alt="评论图片">';
    return '<a href="'.esc_url($url).'" class="fancy-box" data-fancybox="comment-img-'.absint($comment_id).'" data-type="image">'.$content.'</a>';
}

function pix_comment_render_emoji_token($matches){
    $name = isset($matches[1]) ? sanitize_file_name($matches[1]) : '';
    if(!$name || !preg_match('/^[a-zA-Z0-9_-]+$/', $name)){
        return '';
    }

    return '<img class="wp-smiley" src="'.esc_url(THEME_URL.'/img/emoji/'.$name.'.png').'" alt="'.esc_attr('emoji['.$name.']').'">';
}

//评论  内容过滤
function ppo_comment_filter($content,$comment_id){
    $content = convert_smilies($content);
    $content = preg_replace_callback('/\[img=(.*?)\]/', function($matches) use ($comment_id){
        return pix_comment_render_image_token($matches, $comment_id, false);
    }, $content);
    $content = preg_replace('/\[d\](.*?)\[\/d\]/', '<span class="comt-img-wrap">$1</span>', $content);
    $content = preg_replace_callback('/\[s=(.*?)\]/', 'pix_comment_render_emoji_token', $content);

    $content = str_replace('[code]', '<pre class="prettyprint linenums"><code>', $content);
    $content = str_replace('[/code]', '</code></pre>', $content);
    $content = wp_kses_post($content);
    return $content;
}

//评论内容过滤 - 用户中心版（图片显示为[图片]占位符，点击弹出fancybox）
function ppo_comment_filter_user($content, $comment_id){
    $content = convert_smilies($content);
    $content = preg_replace_callback('/\[img=(.*?)\]/', function($matches) use ($comment_id){
        return pix_comment_render_image_token($matches, $comment_id, true);
    }, $content);
    $content = preg_replace('/\[d\](.*?)\[\/d\]/', '<span class="comt-img-wrap">$1</span>', $content);
    $content = preg_replace_callback('/\[s=(.*?)\]/', 'pix_comment_render_emoji_token', $content);
    $content = preg_replace('/^@[\w\-]+：\s*/', '', $content);

    $content = str_replace('[code]', '<pre class="prettyprint linenums"><code>', $content);
    $content = str_replace('[/code]', '</code></pre>', $content);
    $content = wp_kses_post($content);
    return $content;
}


// 生成二维码
use Kkokk\Poster\PosterManager;
function create_qrcode($url){
    $size = 6;
    $lv = 'L';
    ob_start();
    PosterManager::Poster()->Qr($url,false,$lv,$size,2);
    $data = ob_get_contents();
    ob_end_clean();

    $imageString = base64_encode($data);
    header("content-type:application/json; charset=utf-8");
    return 'data:image/jpeg;base64,'.$imageString;
}

// add_filter('get_header', 'fanly_ssl');
// 	function fanly_ssl(){
// 		if( is_ssl() ){
// 			function fanly_ssl_main ($content){
// 			$siteurl = get_option('siteurl');
// 			$upload_dir = wp_upload_dir();
// 			$content = str_replace( 'http:'.strstr($siteurl, '//'), 'https:'.strstr($siteurl, '//'), $content);
// 			$content = str_replace( 'http:'.strstr($upload_dir['baseurl'], '//'), 'https:'.strstr($upload_dir['bas
// 			eurl'], '//'), $content);
// 			return $content;
// 		}
// 			ob_start("fanly_ssl_main");
// 		}
// 	}

// 获取文章所属第一个分类和链接
function get_moment_first_cat($pid, $class = ''){
    $type = 'moments';
    $terms = get_the_terms( $pid, $type ); 
    if ( $terms && ! is_wp_error( $terms ) ) {
        // 循环输出分类
        $term = $terms[0];
        $link = get_term_link( $term->term_id, $type );
        $class = trim('mo-cat '.$class);
        return '<a href="'.$link.'" class="'.esc_attr($class).'"><i class="ri-outlet-line"></i> '.$term->name.'</a>';
        
    } else {
        return '未分类';
    }
}

/* add_filter('get_header', 'fanly_ssl');
	function fanly_ssl(){
		if( is_ssl() ){
			function fanly_ssl_main ($content){
			$siteurl = get_option('siteurl');
			$upload_dir = wp_upload_dir();
			$content = str_replace( 'http:'.strstr($siteurl, '//'), 'https:'.strstr($siteurl, '//'), $content);
			$content = str_replace( 'http:'.strstr($upload_dir['baseurl'], '//'), 'https:'.strstr($upload_dir['bas
			eurl'], '//'), $content);
			return $content;
		}
			ob_start("fanly_ssl_main");
		}
	} */

//获取主题设置链接
function ppo_get_admin_csf_url($tab = '')
{
    $tab                = trim(strip_tags($tab));
    $tab_array          = explode("/", $tab);
    $tab_array_sanitize = array();
    foreach ($tab_array as $tab_i) {
        $tab_array_sanitize[] = sanitize_title($tab_i);
    }
    $tab_attr = esc_attr(implode("/", $tab_array_sanitize));
    $url      = add_query_arg('page', 'pix-settings', admin_url('admin.php'));
    $url      = $tab ? $url . '#tab=' . $tab_attr : $url;
    return esc_url($url);
}

// 自定义外观分割线
function ppo_cu_line(){
    echo'<div class="cu-line"></div>';
}

// pix全局通用分页器
function ppo_htmx_pagination($total_pages, $current_page = 1, $range = 2, $ajax_action = 'load_content', $target = '#content-container',$data_skeleton = 'follow-list') {
    if ($total_pages <= 1) return '';

    $html = '<div class="ppo-custom-pagination">';

    // 上一页
    if ($current_page > 1) {
        $html .= '<a href="#" 
            hx-get="' . admin_url('admin-ajax.php') . '?action=' . $ajax_action . '&page=' . ($current_page - 1) . '" 
            hx-target="' . esc_attr($target) . '" 
            hx-swap="innerHTML"
            data-skeleton="'.$data_skeleton.'"
            class="prev">上一页</a>';
    }

    $start = max(1, $current_page - $range);
    $end   = min($total_pages, $current_page + $range);

    if ($start > 1) {
        $html .= '<a href="#" 
            hx-get="' . admin_url('admin-ajax.php') . '?action=' . $ajax_action . '&page=1" 
            hx-target="' . esc_attr($target) . '" 
            data-skeleton="'.$data_skeleton.'"
            hx-swap="innerHTML">1</a>';
        if ($start > 2) {
            $html .= '<span class="dots">…</span>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="current">' . $i . '</span>';
        } else {
            $html .= '<a href="#" 
                hx-get="' . admin_url('admin-ajax.php') . '?action=' . $ajax_action . '&page=' . $i . '" 
                hx-target="' . esc_attr($target) . '" 
                data-skeleton="'.$data_skeleton.'"
                hx-swap="innerHTML">' . $i . '</a>';
        }
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<span class="dots">…</span>';
        }
        $html .= '<a href="#" 
            hx-get="' . admin_url('admin-ajax.php') . '?action=' . $ajax_action . '&page=' . $total_pages . '" 
            hx-target="' . esc_attr($target) . '" 
            data-skeleton="'.$data_skeleton.'"
            hx-swap="innerHTML">' . $total_pages . '</a>';
    }

    if ($current_page < $total_pages) {
        $html .= '<a href="#" 
            hx-get="' . admin_url('admin-ajax.php') . '?action=' . $ajax_action . '&page=' . ($current_page + 1) . '" 
            hx-target="' . esc_attr($target) . '" 
            data-skeleton="'.$data_skeleton.'"
            hx-swap="innerHTML"
            class="next">下一页</a>';
    }

    $html .= '</div>';

    return $html;
}

// 通用htmx分页器，GET类型
function ppo_htmx_pager($args = []) {
    $defaults = [
        'base_url'       => '/wp-json/ppo/v1/user-posts',
        'user_id'        => 0,
        'total_pages'    => 1,
        'current'        => 1,
        'target'         => '#user-content',
        'query_args'     => [],
        'range'          => 2,
        'push_url'       => false,
        'skeleton'       => 'post-list',
        'push_url_base'  => '',
        'allow_dashboard_push_url' => false,
        'wpnonce'        => false,
        'class'          => '',
    ];
    $args = wp_parse_args($args, $defaults);

    if (isset($_GET['target']) && !empty($_GET['target'])) {
        $args['target'] = sanitize_text_field($_GET['target']);
    }
    if (isset($_GET['push_url_base']) && !empty($_GET['push_url_base'])) {
        $args['push_url_base'] = urldecode(sanitize_text_field($_GET['push_url_base']));
    }

    if (strpos($args['push_url_base'], '/dashboard') !== false && empty($args['allow_dashboard_push_url'])) {
        $args['push_url'] = false;
    }

    $total   = (int) $args['total_pages'];
    $current = (int) $args['current'];
    $range   = (int) $args['range'];

    if ($total <= 1) return '';

    // 构建分页链接
    $build_url = function($page, $for_push_url = false) use ($args) {
        $query = array_merge($args['query_args'], [ 'page' => $page ]);

        if (!$for_push_url && !empty($args['user_id'])) {
            $query['user_id'] = $args['user_id'];
        }

        if ($for_push_url && $args['push_url']) {
            unset($query['target'], $query['push_url_base']);
        }

        if (!$for_push_url && $args['push_url']) {
            if (!empty($args['target'])) {
                $query['target'] = $args['target'];
            }
            if (!empty($args['push_url_base'])) {
                $query['push_url_base'] = $args['push_url_base'];
            }
        }

        $query_string = http_build_query($query);

        return esc_url_raw(
            $for_push_url && $args['push_url']
                ? $args['push_url_base'] . '?' . $query_string
                : $args['base_url'] . '?' . $query_string
                . ($args['wpnonce'] ? '&_wpnonce=' . wp_create_nonce('wp_rest') : '')
        );

    };

    $class_names = ['htmx-pagination', 'pix-pagination-list', 'pix-dashboard-pagination'];
    if (!empty($args['class'])) {
        foreach (preg_split('/\s+/', (string) $args['class']) as $class_name) {
            $class_name = sanitize_html_class($class_name);
            if ($class_name) {
                $class_names[] = $class_name;
            }
        }
    }

    $output = '<ul class="' . esc_attr(implode(' ', array_unique($class_names))) . '">';

    // 上一页
    if ($current > 1) {
        $url = $build_url($current - 1);
        $push = $args['push_url'] ? 'hx-push-url="' . $build_url($current - 1, true) . '"' : '';
        $output .= '<li><a class="pix-pagination-prev" aria-label="上一页" href="' . $url . '" hx-get="' . $url . '" hx-target="' . esc_attr($args['target']) . '" hx-swap="innerHTML" ' . $push . ' data-skeleton = '.$args['skeleton'].'>上一页</a></li>';
    }

    // 前段页码（含省略）
    if ($current - $range > 1) {
        $url = $build_url(1);
        $push = $args['push_url'] ? 'hx-push-url="' . $build_url(1, true) . '"' : '';
        $output .= '<li><a href="' . $url . '" hx-get="' . $url . '" hx-target="' . esc_attr($args['target']) . '" hx-swap="innerHTML" ' . $push . ' data-skeleton = '.$args['skeleton'].'>1</a></li>';

        if ($current - $range > 2) {
            $output .= '<li><span class="dot">...</span></li>';
        }
    }

    // 中间页码
    $start = max(1, $current - $range);
    $end   = min($total, $current + $range);
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current) {
            $output .= '<li><span class="current">' . $i . '</span></li>';
        } else {
            $url = $build_url($i);
            $push = $args['push_url'] ? 'hx-push-url="' . $build_url($i, true) . '"' : '';
            $output .= '<li><a href="' . $url . '" hx-get="' . $url . '" hx-target="' . esc_attr($args['target']) . '" hx-swap="innerHTML" ' . $push . ' data-skeleton = '.$args['skeleton'].'>' . $i . '</a></li>';
        }
    }

    // 尾段页码（含省略）
    if ($current + $range < $total) {
        if ($current + $range < $total - 1) {
            $output .= '<li><span>...</span></li>';
        }

        $url = $build_url($total);
        $push = $args['push_url'] ? 'hx-push-url="' . $build_url($total, true) . '"' : '';
        $output .= '<li><a href="' . $url . '" hx-get="' . $url . '" hx-target="' . esc_attr($args['target']) . '" hx-swap="innerHTML" ' . $push . ' data-skeleton = '.$args['skeleton'].'>' . $total . '</a></li>';
    }

    // 下一页
    if ($current < $total) {
        $url = $build_url($current + 1);
        $push = $args['push_url'] ? 'hx-push-url="' . $build_url($current + 1, true) . '"' : '';
        $output .= '<li><a class="pix-pagination-next" aria-label="下一页" href="' . $url . '" hx-get="' . $url . '" hx-target="' . esc_attr($args['target']) . '" hx-swap="innerHTML" ' . $push . ' data-skeleton = '.$args['skeleton'].'>下一页</a></li>';
    }

    $output .= '</ul>';
    return $output;
}






  
