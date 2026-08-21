<?php
// 文章类通用函数

function pix_post_toc_enabled() {
    return (bool) get_op('post_toc_enable', false);
}

function pix_post_toc_levels() {
    $preset = get_op('post_toc_levels', 'h2-h3');

    if ($preset === 'h1-h3') {
        return array(1, 2, 3);
    }

    if ($preset === 'h2-h4') {
        return array(2, 3, 4);
    }

    return array(2, 3);
}

function pix_post_toc_heading_selector() {
    return implode(',', array_map(function($level) {
        return 'h' . intval($level);
    }, pix_post_toc_levels()));
}

function pix_post_toc_has_headings($content = '') {
    if (!pix_post_toc_enabled() || trim((string) $content) === '') {
        return false;
    }

    $levels = implode('', pix_post_toc_levels());
    return preg_match_all('/<h([' . preg_quote($levels, '/') . '])\b[^>]*>.*?<\/h\1>/is', (string) $content) >= 2;
}

function pix_post_toc_slug($text, $index) {
    $slug = sanitize_title($text);
    if ($slug === '') {
        $slug = 'section-' . intval($index);
    }

    return $slug;
}

function pix_post_content_toc($content) {
    if (is_admin() || !is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $GLOBALS['pix_post_toc_items'] = array();

    if (!pix_post_toc_enabled() || trim((string) $content) === '' || stripos($content, '<h') === false || !class_exists('DOMDocument')) {
        return $content;
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="pix-post-toc-root">' . $content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) {
        return $content;
    }

    $xpath = new DOMXPath($dom);
    $headings = $xpath->query('//' . str_replace(',', '|//', pix_post_toc_heading_selector()));
    if (!$headings || !$headings->length) {
        return $content;
    }

    $items = array();
    $used_ids = array();
    $index = 1;

    foreach ($headings as $heading) {
        if (!$heading instanceof DOMElement) {
            continue;
        }

        $title = trim(preg_replace('/\s+/', ' ', $heading->textContent));
        if ($title === '') {
            continue;
        }

        $id = $heading->getAttribute('id');
        if ($id === '') {
            $base_id = pix_post_toc_slug($title, $index);
            $id = $base_id;
            $suffix = 2;
            while (isset($used_ids[$id])) {
                $id = $base_id . '-' . $suffix;
                $suffix++;
            }
            $heading->setAttribute('id', $id);
        }

        $used_ids[$id] = true;
        $items[] = array(
            'id' => $id,
            'title' => $title,
            'level' => intval(substr(strtolower($heading->tagName), 1)),
        );
        $index++;
    }

    $GLOBALS['pix_post_toc_items'] = $items;

    $root = $dom->getElementById('pix-post-toc-root');
    if (!$root) {
        return $content;
    }

    $html = '';
    foreach ($root->childNodes as $child) {
        $html .= $dom->saveHTML($child);
    }

    return $html ?: $content;
}
add_filter('the_content', 'pix_post_content_toc', 19);

function pix_post_toc_render() {
    $items = isset($GLOBALS['pix_post_toc_items']) && is_array($GLOBALS['pix_post_toc_items']) ? $GLOBALS['pix_post_toc_items'] : array();
    if (count($items) < 2) {
        return '';
    }

    $min_level = min(array_map(function($item) {
        return intval($item['level']);
    }, $items));

    $html = '<nav class="pix-post-toc wid-box" aria-label="文章目录">';
    $html .= '<div class="wid_title pix-post-toc-title"><i class="ri-list-check-2"></i>文章目录</div>';
    $html .= '<ol class="pix-post-toc-list">';

    foreach ($items as $item) {
        $depth = max(0, intval($item['level']) - $min_level);
        $html .= '<li class="pix-post-toc-item pix-post-toc-depth-' . esc_attr($depth) . '">';
        $html .= '<a href="#' . esc_attr($item['id']) . '" data-pix-toc-link>' . esc_html($item['title']) . '</a>';
        $html .= '</li>';
    }

    $html .= '</ol></nav>';

    return $html;
}

function pix_post_lightbox_is_image_url($url) {
    $path = parse_url((string) $url, PHP_URL_PATH);
    return is_string($path) && preg_match('/\.(?:jpe?g|png|gif|webp|avif|bmp)$/i', $path);
}

function pix_post_lightbox_get_full_image_url($img) {
    if (!$img instanceof DOMElement) {
        return '';
    }

    $class = $img->getAttribute('class');
    if ($class && preg_match('/wp-image-(\d+)/', $class, $matches)) {
        $full_url = wp_get_attachment_image_url((int) $matches[1], 'full');
        if ($full_url) {
            return $full_url;
        }
    }

    return $img->getAttribute('src');
}

function pix_post_content_image_lightbox($content) {
    if (is_admin() || !is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    if (!get_op('post_image_lightbox', true) || trim((string) $content) === '' || stripos($content, '<img') === false) {
        return $content;
    }

    if (!class_exists('DOMDocument')) {
        return $content;
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="pix-post-lightbox-root">' . $content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) {
        return $content;
    }

    $xpath = new DOMXPath($dom);
    $imgs = $xpath->query('//img');
    if (!$imgs || !$imgs->length) {
        return $content;
    }

    $group = 'post-' . get_the_ID();

    foreach (iterator_to_array($imgs) as $img) {
        if (!$img instanceof DOMElement) {
            continue;
        }

        $src = pix_post_lightbox_get_full_image_url($img);
        if (!$src || stripos($src, 'data:') === 0) {
            continue;
        }

        $parent = $img->parentNode;
        $link = ($parent instanceof DOMElement && strtolower($parent->tagName) === 'a') ? $parent : null;

        if ($link) {
            if (!$link->hasAttribute('data-fancybox') && pix_post_lightbox_is_image_url($link->getAttribute('href'))) {
                $link->setAttribute('data-fancybox', $group);
                $link->setAttribute('class', trim($link->getAttribute('class') . ' pix-post-lightbox-link'));
            }
            continue;
        }

        if ($parent instanceof DOMElement && strtolower($parent->tagName) === 'picture') {
            $picture = $parent;
            $parent = $picture->parentNode;
            if (!$parent) {
                continue;
            }
            $link = $dom->createElement('a');
            $link->setAttribute('href', esc_url_raw($src));
            $link->setAttribute('data-fancybox', $group);
            $link->setAttribute('class', 'pix-post-lightbox-link');
            $parent->replaceChild($link, $picture);
            $link->appendChild($picture);
            continue;
        }

        if (!$parent) {
            continue;
        }

        $link = $dom->createElement('a');
        $link->setAttribute('href', esc_url_raw($src));
        $link->setAttribute('data-fancybox', $group);
        $link->setAttribute('class', 'pix-post-lightbox-link');
        $parent->replaceChild($link, $img);
        $link->appendChild($img);
    }

    $root = $dom->getElementById('pix-post-lightbox-root');
    if (!$root) {
        return $content;
    }

    $html = '';
    foreach ($root->childNodes as $child) {
        $html .= $dom->saveHTML($child);
    }

    return $html ?: $content;
}
add_filter('the_content', 'pix_post_content_image_lightbox', 20);

// 获取文章元数据 评论 点赞 浏览量
function show_post_meta($wrap_class = '', $item_class = ''){
    global $post; 
    $like_count = get_post_meta($post->ID, 'likes_count', true);
    $like_count = !empty($like_count) ? $like_count : '0';
    $html = '';
    $wrap_class = trim('post-data-meta '.$wrap_class);
    $item_class = trim('item '.$item_class);
    $html = '<div class="'.esc_attr($wrap_class).'">';
    $html .= '<div class="post-views '.esc_attr($item_class).'"><i class="ri-eye-line"></i><span class="number">'.get_post_views ($post->ID).'</span></div>';
    $html .= '<div class="post-comments '.esc_attr($item_class).'"><i class="ri-chat-4-line"></i><span class="number">'.get_comments_number().'</span></div>';
    $html .= '<div class="post-likes '.esc_attr($item_class).'"><i class="ri-heart-3-line"></i><span class="number">'.$like_count.'</span></div>';
    $html .= '</div>';

    return $html;
}

// 获取文章第一个分类
function get_post_first_cat(){
    $category = get_the_category();
    return $category[0]->cat_name;
}

// 文章分类筛选
function post_cat_filter(){
    $arr = get_cu('cls_show_cats');
    $lists = get_terms( array(
        'taxonomy'     => 'category',
        'include'      => $arr,
        'count'        => true,
        'hide_empty'   => 0,
        'orderby'      => 'include',
    ) );

    $de_cat = get_cu('cls_show_cats_de');
    $de_id = !empty($de_cat) ? $de_cat : '0';
    $de_cat_name = !empty($de_cat) ? get_cat_name( $de_cat ) : '全部';

    $nav_class = 'posts_cat_nav pix-home-cat-nav border-b border-pix-line-soft px-10 py-[25px]';
    $list_class = 'flex flex-row items-center justify-start gap-2';
    $link_class = 'block rounded-[5px] bg-pix-primary-subtle px-2.5 py-[5px] text-[13px] leading-tight text-pix-primary-muted';
    $active_link_class = $link_class.' active bg-pix-primary-strong text-white';

    echo '<div class="'.esc_attr($nav_class).'"><ul class="'.esc_attr($list_class).'"><li class="flex"><a data="'.esc_attr($de_id).'" class="'.esc_attr($active_link_class).'"><span>'.esc_html($de_cat_name).'</span></a></li>';
    foreach($lists as $list){
        $name = $list->name;
        $id = $list->term_id;
        $count = $list->count;
        //$link = get_term_link($id,'category');
        $html = '<li class="flex">
                    <a data="'.esc_attr($id).'" class="'.esc_attr($link_class).'"><span>'.esc_html($name).'</span></a>
                </li>';

        echo $html;     
    }
    echo '</ul></div>';
}

function pix_blog_ajax_query_args($cat = 0, $paged = 1, $found_rows = true){
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'paged' => max(1, absint($paged)),
        'ignore_sticky_posts' => true,
    );

    if(!$found_rows){
        $args['no_found_rows'] = true;
    }

    $cat = absint($cat);
    if($cat > 0){
        $args['cat'] = $cat;
    }

    return $args;
}

function pix_blog_render_query(WP_Query $query){
    if(!$query->have_posts()){
        return '<div class="no-moment"><img src="'.esc_url(THEME_URL.'/img/empty.png').'"></div>';
    }

    ob_start();
    while($query->have_posts()){
        $query->the_post();
        get_template_part( 'tpl/content' );
    }
    wp_reset_postdata();

    return ob_get_clean();
}

function pix_blog_pagenav_html($current, $total, $base_url){
    if($total < 2){
        return '';
    }

    $base_url = remove_query_arg('paged', $base_url);
    $base_url = preg_replace('#/page/\d+/?$#', '/', $base_url);

    ob_start();
    echo paginate_links(array(
        'base' => trailingslashit($base_url) . 'page/%#%/',
        'format' => '?paged=%#%',
        'current' => max(1, absint($current)),
        'total' => max(1, absint($total)),
        'prev_text' => '上一页',
        'next_text' => '下一页',
        'type' => 'list'
    ));

    return ob_get_clean();
}

function pix_archive_ajax_query_args($paged){
    $archive_type = isset($_POST['archive_type']) ? sanitize_key(wp_unslash($_POST['archive_type'])) : '';
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'paged' => max(1, absint($paged)),
        'ignore_sticky_posts' => true,
    );

    if($archive_type === 'category'){
        $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
        if($term_id > 0){
            $args['cat'] = $term_id;
        }
    } elseif($archive_type === 'tag'){
        $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
        if($term_id > 0){
            $args['tag_id'] = $term_id;
        }
    } elseif($archive_type === 'tax'){
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_key(wp_unslash($_POST['taxonomy'])) : '';
        $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
        if($taxonomy && taxonomy_exists($taxonomy) && $term_id > 0){
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id,
                ),
            );
        }
    } elseif($archive_type === 'date'){
        $year = isset($_POST['year']) ? absint($_POST['year']) : 0;
        $monthnum = isset($_POST['monthnum']) ? absint($_POST['monthnum']) : 0;
        $day = isset($_POST['day']) ? absint($_POST['day']) : 0;
        if($year > 0){
            $args['year'] = $year;
        }
        if($monthnum > 0){
            $args['monthnum'] = $monthnum;
        }
        if($day > 0){
            $args['day'] = $day;
        }
    } elseif($archive_type === 'post_type'){
        $post_type = isset($_POST['post_type']) ? sanitize_key(wp_unslash($_POST['post_type'])) : 'post';
        if(post_type_exists($post_type)){
            $args['post_type'] = $post_type;
        }
    }

    return $args;
}

function pix_archive_render_query(WP_Query $query){
    if(!$query->have_posts()){
        return '<div class="pix-search-empty"><div class="pix-search-empty-icon"><i class="ri-inbox-archive-line"></i></div><h2>暂无内容</h2><p>这个归档下暂时没有公开内容。</p></div>';
    }

    ob_start();
    while($query->have_posts()){
        $query->the_post();
        if('post' === get_post_type()){
            get_template_part('tpl/content', 'grid');
        } else {
            get_template_part('tpl/content', 'search');
        }
    }
    wp_reset_postdata();

    return ob_get_clean();
}

// ajax加载文章
function cls_load_posts(){
    check_ajax_referer('post_ajax', 'security');

    $context = isset($_POST['context']) ? sanitize_key(wp_unslash($_POST['context'])) : '';
    if($context === 'archive'){
        $paged = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;
        $args = pix_archive_ajax_query_args($paged);
        $query = new WP_Query($args);
        $base_url = isset($_POST['baseurl']) ? esc_url_raw(wp_unslash($_POST['baseurl'])) : home_url('/');

        wp_send_json(array(
            'content' => pix_archive_render_query($query),
            'pagenav' => pix_blog_pagenav_html($paged, $query->max_num_pages, $base_url),
            'max_page' => (int) $query->max_num_pages,
            'current_page' => $paged,
        ));
    }

    $nav_type = get_op('post_nav','btn');
    $cat = !empty($_POST['cat']) ? absint($_POST['cat']) : 0;
    $requested_page = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;
    $paged = $nav_type != 'pagenav' ? $requested_page + 1 : $requested_page;
    $args = pix_blog_ajax_query_args($cat, $paged, $nav_type == 'pagenav');
    $query = new WP_Query($args);
    $page_nav_html = '';

    if($nav_type == 'pagenav'){
        $base_url = isset($_POST['baseurl']) ? esc_url_raw($_POST['baseurl']) : home_url('/');
        $page_nav_html = pix_blog_pagenav_html($paged, $query->max_num_pages, $base_url);
    }

    wp_send_json(array(
        'content' => pix_blog_render_query($query),
        'pagenav' => $page_nav_html,
        'max_page' => (int) $query->max_num_pages,
        'current_page' => $paged,
    ));
}

add_action('wp_ajax_nopriv_cls_load_posts', 'cls_load_posts');
add_action('wp_ajax_cls_load_posts', 'cls_load_posts');

// ajax文章筛选
function cls_filter_posts(){
    check_ajax_referer('post_ajax', 'security');

    $page_nav_html = '';
    $nav_type = get_op('post_nav','btn');
    $paged = 1;
    $cat = !empty($_POST['cat']) ? absint($_POST['cat']) : 0;
    $args = pix_blog_ajax_query_args($cat, $paged, true);
    $query = new WP_Query($args);
    $posts_html = pix_blog_render_query($query);

   if($nav_type == 'pagenav') {
      
    $base_url = isset($_POST['baseurl']) ? esc_url_raw($_POST['baseurl']) : '';
    $page_nav_html = pix_blog_pagenav_html($paged, $query->max_num_pages, $base_url);
    
}

   wp_send_json( array(
		'posts' => json_encode( $query->query_vars ),
		'max_page' => $query->max_num_pages,
		'found_posts' => $query->found_posts,
		'content' => $posts_html,
        'pagenav' => $page_nav_html,
	) );
 

}
add_action('wp_ajax_nopriv_cls_filter_posts', 'cls_filter_posts');
add_action('wp_ajax_cls_filter_posts', 'cls_filter_posts');

function pix_search_allowed_post_types(){
    $post_types = get_post_types(array(
        'public' => true,
        'exclude_from_search' => false,
    ), 'names');

    $post_types = array_values(array_filter($post_types, function($post_type){
        return post_type_exists($post_type);
    }));

    return !empty($post_types) ? $post_types : array('post', 'page', 'moment');
}

function pix_search_ajax_query_args($search_query, $paged = 1){
    return array(
        's' => $search_query,
        'post_status' => 'publish',
        'post_type' => pix_search_allowed_post_types(),
        'paged' => max(1, absint($paged)),
    );
}

function pix_search_render_query(WP_Query $query){
    ob_start();

    while($query->have_posts()){
        $query->the_post();

        if('post' === get_post_type()){
            get_template_part('tpl/content');
        } elseif('moment' === get_post_type()){
            get_template_part('tpl/content', 'mgrid');
        } else {
            get_template_part('tpl/content', 'search');
        }
    }

    wp_reset_postdata();

    return ob_get_clean();
}

function pix_search_pagenav_html($search_query, $current, $total){
    $total = max(1, absint($total));
    if($total < 2){
        return '';
    }

    $base = add_query_arg('s', $search_query, home_url('/'));
    $base .= (strpos($base, '?') === false ? '?' : '&') . 'paged=%#%';

    $pagination = paginate_links(array(
        'base' => $base,
        'format' => '',
        'current' => max(1, absint($current)),
        'total' => $total,
        'end_size' => 1,
        'mid_size' => 1,
        'prev_text' => '<i class="ri-arrow-left-s-line"></i>',
        'next_text' => '<i class="ri-arrow-right-s-line"></i>',
        'type' => 'list',
    ));

    if(!$pagination){
        return '';
    }

    return '<div class="pix-search-pagination pix-home-pagination-box" data-pix-search-pagination>' . $pagination . '</div>';
}

function pix_ajax_search_page(){
    check_ajax_referer('post_ajax', 'security');

    $search_query = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';
    $paged = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;
    $query = new WP_Query(pix_search_ajax_query_args($search_query, $paged));
    $max_pages = max(1, (int) $query->max_num_pages);

    wp_send_json_success(array(
        'content' => pix_search_render_query($query),
        'pagination' => pix_search_pagenav_html($search_query, $paged, $max_pages),
        'page_label' => sprintf(
            '第 %1$s 页 / 共 %2$s 页',
            number_format_i18n($paged),
            number_format_i18n($max_pages)
        ),
        'current_page' => $paged,
        'max_page' => $max_pages,
        'found_posts' => (int) $query->found_posts,
    ));
}
add_action('wp_ajax_nopriv_pix_search_page', 'pix_ajax_search_page');
add_action('wp_ajax_pix_search_page', 'pix_ajax_search_page');


/*-----------------------------------------------------------------------------------*/
/* 文章浏览量
/*-----------------------------------------------------------------------------------*/
function get_post_views ($post_id) {   
  
    $count_key = 'views';   
    $count = get_post_meta($post_id, $count_key, true);   
  
    if ($count == '') {   
        delete_post_meta($post_id, $count_key);   
        add_post_meta($post_id, $count_key, '0');   
        $count = '0';   
    }   
  
    return number_format_i18n($count);   
  
}   
  
function set_post_views ($post_id) {   
    global $post; 
    $post_id = isset($post->ID) ? $post->ID : false;
    if($post_id){
        $count_key = 'views';   
        $count = get_post_meta($post_id, $count_key, true);   
      
        if (is_single() || is_page()) {   
      
            if ($count == '') {   
                delete_post_meta($post_id, $count_key);   
                add_post_meta($post_id, $count_key, '0');   
            } else {   
                update_post_meta($post_id, $count_key, $count + 1);   
            }   
      
        }   
    }
  
}   
add_action('get_header', 'set_post_views'); 

// 收藏按钮
function post_collect_btn($post_id){
    $collect_count = get_post_meta($post_id, 'collect_count', true);
    $collect_count = $collect_count ? $collect_count : 0;
    $user_collect = get_user_meta(get_current_user_id(), 'post_collect', true);
    $user_collect = $user_collect ? $user_collect : array();
    if(in_array($post_id, $user_collect)){
        $class = 'coled';
        $icon = '<i class="ri-bookmark-3-fill"></i>';
    } else {
        $class = 'col';
        $icon = '<i class="ri-bookmark-3-line"></i>';
    }

    $btn = '<a class="collect-btn" action="'.$class.'" pid="'.absint($post_id).'">'.$icon.'<span>'.absint($collect_count).'</span></a>';
    return $btn;
}

// 点赞按钮
function post_like_btn($post_id){
    $like_count = get_post_meta($post_id, 'likes_count', true);
    $like_count = $like_count ? $like_count : 0;
    $user_like = get_user_meta(get_current_user_id(), 'post_likes', true);
    $user_like = $user_like ? $user_like : array();
    if(in_array($post_id, $user_like)){
        $class = 'liked';
        $icon = '<i class="ri-heart-3-fill"></i>';
    } else {
        $class = 'like';
        $icon = '<i class="ri-heart-3-line"></i>';
    }

    $btn = '<a class="like-btn" action="'.$class.'" pid="'.absint($post_id).'">'.$icon.'<span>'.absint($like_count).'</span></a>';
    return $btn;
}

// ajax文章收藏
function post_collect_action(){
    check_ajax_referer('ppo_user_action', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('msg' => '请先登录'), 401);
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $user_id = get_current_user_id();
    $post_data = get_post($post_id);

    if (!$post_data || !in_array($post_data->post_type, array('post', 'page', 'moment'), true)) {
        wp_send_json_error(array('msg' => '内容不存在'), 404);
    }

    $collect_count = absint(get_post_meta($post_id, 'collect_count', true));
    $user_collect = get_user_meta($user_id, 'post_collect', true);
    $user_collect = is_array($user_collect) ? array_values(array_unique(array_map('absint', $user_collect))) : array();
    $is_coled = in_array($post_id, $user_collect, true);

    $post_type = $post_data->post_type;
    $type_arr = [
        'post' => '文章',
        'page' => '页面',
        'moment' => '片刻',
    ];

    $type_name = isset($type_arr[$post_type]) ? $type_arr[$post_type] : '内容';

    if ($is_coled) {
        // 取消收藏
        $collect_count = max(0, $collect_count - 1);
        if (($key = array_search($post_id, $user_collect, true)) !== false) {
            unset($user_collect[$key]);
        }
        ppo_msg_delete_collect_post($user_id, $post_data->post_author, $post_id);
        $msg = array('msg'=>'收藏已取消','count'=>$collect_count,'collected'=>false);
    } else {
        // 收藏
        $user_collect[] = $post_id;
        $collect_count++;
        if ((int) $post_data->post_author !== $user_id) {
            $msg_data = [
                'receive_user' => $post_data->post_author,
                'send_id' => $user_id,
                'type' => 'post_collect',
                'title' => '收藏了您的'.$type_name,
                'content' => '',
                'related_id' => $post_id,
            ];
            ppo_msg_add($msg_data);
        }
        $note = $type_name.'收藏';
        do_action('ppo_collect_content', $user_id, $note,$post_id);
        $msg = array('msg'=>$type_name.'已收藏','count'=>$collect_count,'collected'=>true);

    }

    update_post_meta($post_id, 'collect_count', $collect_count);
    update_user_meta($user_id, 'post_collect', array_values(array_unique(array_map('absint', $user_collect))));

    wp_send_json( $msg );
}
add_action('wp_ajax_post_collect_action', 'post_collect_action');

// ajax文章点赞
function post_like_action(){
    check_ajax_referer('ppo_user_action', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('msg' => '请先登录'), 401);
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $user_id = get_current_user_id();
    $post_data = get_post($post_id);

    if (!$post_data || !in_array($post_data->post_type, array('post', 'page', 'moment'), true)) {
        wp_send_json_error(array('msg' => '内容不存在'), 404);
    }

    $like_count = absint(get_post_meta($post_id, 'likes_count', true));
    $user_like = get_user_meta($user_id, 'post_likes', true);
    $user_like = is_array($user_like) ? array_values(array_unique(array_map('absint', $user_like))) : array();
    $is_liked = in_array($post_id, $user_like, true);
    $post_type = $post_data->post_type;
    $type_arr = [
        'post' => '文章',
        'page' => '页面',
        'moment' => '片刻',
    ];

    $type_name = isset($type_arr[$post_type]) ? $type_arr[$post_type] : '内容';

    if ($is_liked) {
        // 取消点赞
        $like_count = max(0, $like_count - 1);
        if (($key = array_search($post_id, $user_like, true)) !== false) {
            unset($user_like[$key]);
        }
        if ($post_type === 'moment') {
            $moment_likes = get_post_meta($post_id, 'moment_likes', true);
            $moment_likes = is_array($moment_likes) ? array_values(array_unique(array_map('absint', $moment_likes))) : array();
            if (($key = array_search($user_id, $moment_likes, true)) !== false) {
                unset($moment_likes[$key]);
            }
            update_post_meta($post_id, 'moment_likes', array_values($moment_likes));
        }
        ppo_msg_delete_like_post($user_id, $post_data->post_author, $post_id);
        $msg = array('msg'=>'点赞已取消','count'=>$like_count,'liked'=>false);
    } else {
        // 点赞
        $user_like[] = $post_id;
        $like_count++;
        if ($post_type === 'moment') {
            $moment_likes = get_post_meta($post_id, 'moment_likes', true);
            $moment_likes = is_array($moment_likes) ? array_values(array_unique(array_map('absint', $moment_likes))) : array();
            $moment_likes[] = $user_id;
            update_post_meta($post_id, 'moment_likes', array_values(array_unique($moment_likes)));
        }
        $msg = array('msg'=>'感谢您的支持','count'=>$like_count,'liked'=>true);
        if ((int) $post_data->post_author !== $user_id) {
            $msg_data = [
                'receive_user' => $post_data->post_author,
                'send_id' => $user_id,
                'type' => 'post_like',
                'title' => '赞了您的'.$type_name,
                'content' => '',
                'related_id' => $post_id,
            ];
            ppo_msg_add($msg_data);
        }

        //任务动作
        $note = $type_name;
        do_action('ppo_like_content', $user_id, $note,$post_id);
    }

    update_post_meta($post_id, 'likes_count', $like_count);
    update_user_meta($user_id, 'post_likes', array_values(array_unique(array_map('absint', $user_like))));


    wp_send_json( $msg );
}
add_action('wp_ajax_post_like_action', 'post_like_action');

// 文章海报按钮和弹窗
function poster_modal_btn($post_id){
    $html = '<a class="poster-btn" href="#poster-modal" data-pix-modal-open="#poster-modal"><i class="ri-share-forward-fill"></i><span>分享</span></a>
                <div id="poster-modal" class="pix-modal pix-hs-modal pix-poster-modal hidden" role="dialog" tabindex="-1" aria-labelledby="poster-modal-title">
                <div class="pix-modal-dialog">
                <div class="pix-modal-panel poster-modal">

                    <button class="pix-modal-close" type="button" data-pix-modal-close="#poster-modal" aria-label="关闭"><i class="ri-close-line"></i></button>
                    <span id="poster-modal-title" class="screen-reader-text">分享海报</span>

                    <div class="poster-canvas"></div>
                    <div class="share-tool">
                        <a href="javascript:;" class="share-qq"><i class="ri-qq-line"></i></a>
                        <a href="javascript:;" class="share-zone"><i class="ri-chrome-line"></i></a>
                        <a href="javascript:;" class="copy-link"><i class="ri-link"></i></a>
                        <a href="javascript:;" class="download-poster"><i class="ri-download-line"></i></a>
                    </div>
                    <img id="output" style="display:none;"/>

                </div>
                </div>
            </div>';

            return $html;
}

// ajax加载海报内容
function load_poster_modal(){
    $post_id = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
    $post = get_post($post_id);
    if(!$post || !in_array($post->post_type, array('post', 'page', 'moment'), true)){
        wp_send_json(array('html'=>'', 'msg'=>'内容不存在'));
    }

    $img = get_ppo_thum( $post_id, 'large','random');
    if($post->post_type === 'moment'){
        $moment_type = get_post_meta($post_id, 'moment_type', true);
        if($moment_type === 'gallery'){
            $gallery = get_post_meta($post_id, 'moment_ga', true);
            if(!empty($gallery[0]['src'])){
                $img = esc_url_raw($gallery[0]['src']);
            } elseif(!empty($gallery[0]['thum'])){
                $img = esc_url_raw($gallery[0]['thum']);
            }
        } elseif($moment_type === 'video'){
            $video = get_post_meta($post_id, 'moment_video', true);
            if(!empty($video[0]['poster'])){
                $img = esc_url_raw($video[0]['poster']);
            } elseif(!empty($video[0]['thumb'])){
                $img = esc_url_raw($video[0]['thumb']);
            }
        }
    }

    $post_date = $post->post_date;
    $timestamp = strtotime($post_date);
    $d = date('d', $timestamp);
    $ym = date('Y/m', $timestamp);
    $title = get_the_title( $post_id );
    if($post->post_type === 'moment' && trim(wp_strip_all_tags((string)$title)) === ''){
        $title = '分享片刻';
    }
    $logo = site_logo('light');
    $permalink = get_the_permalink($post_id);
    $qrcode = create_qrcode($permalink);
    $content = $post->post_content;
    $content = mb_strimwidth(strip_shortcodes(strip_tags($content)), 0, 120,"...");

    $html = '<div class="poster-img">
                <img src="'.esc_url($img).'">
                <div class="poster-time"><b>'.$d.'</b><span>'.$ym.'</span></div>
            </div>
            <div class="poster-trim"><h4>'.esc_html($title).'</h4>'.esc_html($content).'</div>
            <div class="poster-bottom">
                <div class="logo">'.$logo.'</div>
                <div class="qrcode"><img src="'.esc_url($qrcode).'" crossorigin="anonymous"></div>
            </div>';

    wp_send_json(array(
        'html' => $html,
        'url' => $permalink,
        'title' => $title,
    ));        
}
add_action('wp_ajax_nopriv_load_poster_modal', 'load_poster_modal');
add_action('wp_ajax_load_poster_modal', 'load_poster_modal');

// 文章顶部工具栏
function post_footer_tool(){
    global $post; 
    $post_id = $post->ID ? $post->ID : false;
    $html = '<div class="post-footer-inner">
                <div class="left">
                    <div class="post-poster item">'.poster_modal_btn($post_id).'</div>
                </div>
                <div class="right">
                    <div class="post-comment item"><a class="comment-btn"><i class="ri-chat-1-line"></i><span>'.get_comments_number($post_id).'</span></a></div>
                    <div class="post-colleect item">'.post_collect_btn($post_id).'</div>
                    <div class="post-like item">'.post_like_btn($post_id).'</div>
                </div>
            </div>';

    return $html;    

}

// 上一篇下一篇文章
function prev_next_post(){
    global $post; 
    $prev = get_previous_post();
    $next = get_next_post();
    $html = '<div class="prev-next-inner">';
    if( !empty($prev) ){
        $img = get_ppo_thum( $prev->ID, 'medium','random');
        $html .= '<a class="prev-link" href="'.get_the_permalink($prev->ID).'" title="'.$prev->post_title.'" rel="prev">
                    <div class="prev-icon"><i class="ri-arrow-left-s-line"></i>上一篇</div>
                    <div class="title">'.$prev->post_title.'</div>
                    <div class="pn-thum"><img src="'.esc_url($img).'" alt="" loading="lazy" decoding="async"></div>
                </a>';
    
    }

    if(!empty($next)){
        $img = get_ppo_thum( $next->ID, 'medium','random');
        $html .= '<a class="next-link" href="'.get_the_permalink($next->ID).'" title="'.$next->post_title.'" rel="next">
                    <div class="next-icon">下一篇<i class="ri-arrow-right-s-line"></i></div>
                    <div class="title">'.$next->post_title.'</div>
                    <div class="pn-thum"><img src="'.esc_url($img).'" alt="" loading="lazy" decoding="async"></div>
                </a>';
    }

    $html .='</div>';

    return $html;
}

// 获取用户中心用户发布的文章
function ppo_get_user_posts_html(WP_REST_Request $request) {
    $user_id = intval($request->get_param('user_id'));
    if ($user_id <= 0 || !get_user_by('id', $user_id)) {
        return new WP_REST_Response('<p>无效的用户。</p>', 400);
    }

    $paged = max(1, intval($request->get_param('page')));
    $target = sanitize_text_field($request->get_param('target')) ?: '#user-content';
    $push_url_base = sanitize_text_field($request->get_param('push_url_base')) ?: get_author_posts_url($user_id);
    $per_page = defined('PPO_USER_POSTS_PER_PAGE') ? PPO_USER_POSTS_PER_PAGE : 9;

    $query = new WP_Query([
        'post_type'      => 'post',
        'author'         => $user_id,
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
    ]);

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="pix-user-home-posts-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('tpl/content', 'grid'); // 替换为你的卡片模板
        }
        echo '</div>';

        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            echo ppo_htmx_pager([
                'user_id'     => $user_id,
                'total_pages' => $total_pages,
                'current'     => $paged,
                'target'      => $target,
                'query_args'  => ['tab' => 'posts', 'target' => $target, 'push_url_base' => $push_url_base],
                'push_url'    => true,
                'push_url_base' => $push_url_base,
                'class'       => 'pix-user-home-pagination',
            ]);
        }
    } else {
        echo '<div class="nodata pix-user-home-empty"><img src="'.get_template_directory_uri().'/img/empty.png" alt="暂无数据"></div>';
    }


    wp_reset_postdata();

    echo ob_get_clean();
    exit;
}
