<?php

if ( version_compare( $GLOBALS['wp_version'], '4.4-alpha', '<' ) ) {
	wp_die('请升级到4.4以上版本');
}

if(!function_exists('fa_ajax_comment_err')) :

    function fa_ajax_comment_err($a) {
        header('HTTP/1.0 500 Internal Server Error');
        header('Content-Type: text/plain;charset=UTF-8');
        echo $a;
        exit;
    }

endif;

if(!function_exists('fa_ajax_comment_callback')) :

    function fa_ajax_comment_callback(){
        $user_id = get_current_user_id();
        if($user_id && function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
            $allow_comment = function_exists('pix_normal_user_can') ? pix_normal_user_can('normal_user_allow_comment', true, $user_id) : true;
            if(!$allow_comment){
                fa_ajax_comment_err('普通用户暂不能发表评论');
            }
        }

        $img_urls = isset($_POST['comt-uploaded-urls']) ? $_POST['comt-uploaded-urls'] : false;
        $img = isset($_POST['comt-uploded-files']) ? $_POST['comt-uploded-files'] : false;
        $img_list = $img_urls ? explore_comt_img_urls($img_urls) : ($img ? explore_comt_img($img) : '');
        $comment_merge = $_POST['comment'].$img_list;
        if(function_exists('pix_check_forbidden_words')){
            $forbidden_word = pix_check_forbidden_words($_POST['comment']);
            if($forbidden_word){
                fa_ajax_comment_err(pix_forbidden_words_message($forbidden_word));
            }
        }
        if(function_exists('pix_content_submission_guard')){
            $guard = pix_content_submission_guard('comment');
            if(!empty($guard['code'])){
                fa_ajax_comment_err($guard['msg']);
            }
        }
        $_POST['pix_content_guarded'] = 1;
        $_POST['comment'] = $comment_merge;
        $comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
        if ( is_wp_error( $comment ) ) {
            $data = $comment->get_error_data();
            if ( ! empty( $data ) ) {
            	fa_ajax_comment_err($comment->get_error_message());
            } else {
                exit;
            }
        }
        $user = wp_get_current_user();
        do_action('set_comment_cookies', $comment, $user);
        $comment_id = $comment->comment_ID;

        pix_update_comment_location($comment_id);

        //Events::add($_POST['comment_post_ID'],$user_id,$to_id,'reply',$_POST['comment'],'0',$comment_parent,$remark);
        echo pix_render_comment_items(array($comment));
        die();
    }

endif;

add_action('wp_ajax_nopriv_ajax_comment', 'fa_ajax_comment_callback');
add_action('wp_ajax_ajax_comment', 'fa_ajax_comment_callback');

function pix_comment_submission_preprocess($commentdata){
    $user_id = get_current_user_id();
    if($user_id && function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $allow_comment = function_exists('pix_normal_user_can') ? pix_normal_user_can('normal_user_allow_comment', true, $user_id) : true;
        if(!$allow_comment){
            wp_die('普通用户暂不能发表评论', '', array('response' => 403));
        }
    }

    if(!function_exists('pix_content_submission_guard')){
        return $commentdata;
    }

    if(!empty($_POST['pix_content_guarded'])){
        return $commentdata;
    }

    $guard = pix_content_submission_guard('comment');
    if(!empty($guard['code'])){
        wp_die($guard['msg'], '', array('response' => 400));
    }

    return $commentdata;
}
add_filter('preprocess_comment', 'pix_comment_submission_preprocess', 1);

/*-----------------------------------------------------------------------------------*/
/* COMMENT FORMATTING
/*-----------------------------------------------------------------------------------*/

if(!function_exists('ppo_comment_format')){
    function ppo_comment_format($comment, $args, $depth){
        $GLOBALS['comment'] = $comment;
        $user_id = $comment->user_id;
        $avatar = $user_id ? get_u_avatar( $user_id, 'img') : get_avatar( $comment, 50 );
        $url = $user_id ? get_author_posts_url($user_id):get_comment_author_url();
        $comment_id = $comment->comment_ID;
        $comment_like = comment_like_btn($comment_id);
        $badges = function_exists('ppo_user_badges_html') ? ppo_user_badges_html($user_id, 'comment') : '';
        ?>
            <li id="li-comment-<?php comment_ID() ?>" <?php comment_class('pix-moment-comment-item'); ?>>
                <div id="comment-<?php comment_ID(); ?>" class="comment_body contents pix-moment-comment-body">
                    <div class="profile pix-moment-comment-avatar">
                        <a href="<?php echo $url ?>" target="_blank"><?php echo $avatar ?></a>
                    </div>	
                    <div class="com_right pix-moment-comment-content">
                            <section class="commeta pix-moment-comment-meta">
                                <div class="left pix-moment-comment-author-wrap">
                                    <h4 class="author pix-moment-comment-author"><a href="<?php echo $url ?>" target="_blank"><?php comment_author(); ?></a><?php echo $badges; ?><?php //is_master($user_id); ?></h4>
                                </div>
                                <div class="right pix-moment-comment-info-wrap">
                                    <div class="info pix-moment-comment-info">
                                                                       
                                    </div>	
                                </div>
                            </section>
                        <div class="body pix-moment-comment-text">
                            <p><?php echo ppo_comment_filter(get_comment_text($comment_id),$comment_id); ?></p>
                        </div>

                        <div class="footer-meta pix-moment-comment-footer">
                            <div class="left pix-moment-comment-footer-info">
                                <time class="timeago pix-moment-comment-time" itemprop="datePublished" datetime="<?php echo get_comment_date( 'c' );?>"> <?php echo get_gmt_from_date(get_comment_date('Y-m-d G:i:s')); ?></time>
                                <?php echo get_comment_location($comment_id); ?>
                            </div>
                            <div class="right pix-moment-comment-footer-actions">
                              <?php echo $comment_like ?>
                              <?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth'] , 'reply_text' => '<i class="ri-chat-1-line"></i> 回复'))); ?>
                            </div>
                            
                        </div>

                    </div>    
                </div>				
        <?php
    }
}


//评论头像获取
function comment_visitor( $user_id, $author_name, $author_email) {
    $user_info = get_userdata($user_id);
    $url = $user_id ? get_author_posts_url($user_id):get_comment_author_url();
	if ( $user_id ) { // 用户
		$user = get_userdata( $user_id );
		$avatar = get_u_avatar($user_id,$type = 'url');
		$condition = '<a class="edit-profile login_avatar" href="'.$url.'" target="_top"><img src="'. $avatar .'" height="50" width="50" class="v-avatar avatar avatar-50"></a>';
	}
	elseif ( $author_name ) { // 访客
		$avatar = get_avatar_url( $author_email, array( 'size' => '100' ) );
		$condition = '<a class="edit-profile edit-card" href="'.$url.'" target="_top"><img src="'. $avatar .'" height="50" width="50" class="v-avatar avatar avatar-50"></a>';
	}
	else { // 匿名
		$avatar = get_bloginfo( 'template_directory' ).'/img/ava.png';
		$condition = '<a class="edit-profile edit-card"><img src="'. $avatar .'"  height="50" width="50" class="v-avatar avatar avatar-50"></a>';
	}

	return $condition;
}

// 评论添加@ 评论列表使用get_comment_text,需要修复
function ppo_comment_add_at( $comment_text, $comment = '') {
    if( $comment->comment_parent > 0) {
        $parent_id = $comment->comment_parent;
        $comment_parent = get_comment($parent_id);
        $comment_content = $comment_parent->comment_content;
      $comment_text = '<a class="parents_at" href="#comment-' . $comment->comment_parent . '">@'.get_comment_author( $comment->comment_parent ) . '</a> ' . $comment_text;
    }
    
    return $comment_text;
  }
add_filter( 'comment_text' , 'ppo_comment_add_at', 20, 2);
add_filter( 'get_comment_text' , 'ppo_comment_add_at', 20, 2);

//ajax获取评论头像
add_action('wp_ajax_nopriv_ajax_avatar_get', 'ajax_avatar_get');
add_action('wp_ajax_ajax_avatar_get', 'ajax_avatar_get');
function ajax_avatar_get(){
    $email = isset($_POST['email']) ? $_POST['email'] : false;
    $name = isset($_POST['name']) ? $_POST['name'] : '神秘访客';
    if($email){
        $res = get_avatar_url( $email, array( 'size'=>50 ) );
        $avatar = preg_replace("/http:\/\/(www|\d).gravatar.com\/avatar\//","https://cravatar.cn/avatar/",$res);
     wp_send_json(array('avatar'=>$avatar,'name'=>$name));

    } else {
        return;
    }
}  

function pix_comment_per_page(){
    return max(1, min(50, (int)get_op('comment_per_page', 10)));
}

function pix_comment_cache_version($post_id){
    $post_id = absint($post_id);
    if(!$post_id){
        return '0';
    }

    $key = 'pix_comment_cache_ver_'.$post_id;
    $version = get_transient($key);
    if(!$version){
        $version = (string)time();
        set_transient($key, $version, DAY_IN_SECONDS);
    }
    return $version;
}

function pix_comment_page_cache_key($post_id, $page, $per_page){
    return 'pix_comment_page_'.md5(absint($post_id).'|'.absint($page).'|'.absint($per_page).'|'.pix_comment_cache_version($post_id));
}

function pix_comment_count_cache_key($post_id){
    return 'pix_comment_count_'.md5(absint($post_id).'|'.pix_comment_cache_version($post_id));
}

function pix_comment_ids_to_objects($comment_ids){
    $comments = array();
    foreach((array)$comment_ids as $comment_id){
        $comment = get_comment(absint($comment_id));
        if($comment && $comment->comment_approved === '1'){
            $comments[] = $comment;
        }
    }
    return $comments;
}

function pix_comment_top_count($post_id){
    $post_id = absint($post_id);
    $cache_key = pix_comment_count_cache_key($post_id);
    $cached = get_transient($cache_key);
    if($cached !== false){
        return (int)$cached;
    }

    $count = (int)get_comments(array(
        'post_id' => absint($post_id),
        'status' => 'approve',
        'parent' => 0,
        'count' => true,
    ));

    set_transient($cache_key, $count, 60);
    return $count;
}

function pix_comment_page_data($post_id, $page = 1){
    $post_id = absint($post_id);
    $per_page = pix_comment_per_page();
    $top_count = pix_comment_top_count($post_id);
    $pages = max(1, (int)ceil($top_count / $per_page));
    $page = max(1, min(absint($page), $pages));
    $order = 'DESC';
    $cache_key = pix_comment_page_cache_key($post_id, $page, $per_page);
    $cached = get_transient($cache_key);
    if(is_array($cached) && isset($cached['comment_ids'])){
        $cached['comments'] = pix_comment_ids_to_objects($cached['comment_ids']);
        return $cached;
    }

    $top_comments = get_comments(array(
        'post_id' => $post_id,
        'status' => 'approve',
        'parent' => 0,
        'orderby' => 'comment_date_gmt',
        'order' => $order,
        'number' => $per_page,
        'offset' => ($page - 1) * $per_page,
    ));

    $comments = $top_comments;
    $top_ids = wp_list_pluck($top_comments, 'comment_ID');
    if(!empty($top_ids)){
        $children = get_comments(array(
            'post_id' => $post_id,
            'status' => 'approve',
            'parent__in' => array_map('absint', $top_ids),
            'orderby' => 'comment_date_gmt',
            'order' => 'ASC',
        ));
        if(!empty($children)){
            $comments = array_merge($comments, $children);
        }
    }

    $data = array(
        'comments' => $comments,
        'comment_ids' => array_map('absint', wp_list_pluck($comments, 'comment_ID')),
        'page' => $page,
        'pages' => $pages,
        'per_page' => $per_page,
        'top_count' => $top_count,
    );
    set_transient($cache_key, $data, 60);
    return $data;
}

function pix_bump_comment_page_cache_by_post($post_id){
    $post_id = absint($post_id);
    if(!$post_id){
        return;
    }
    set_transient('pix_comment_cache_ver_'.$post_id, (string)microtime(true), DAY_IN_SECONDS);
}

function pix_bump_comment_page_cache($comment_id){
    $comment = get_comment(absint($comment_id));
    if($comment){
        pix_bump_comment_page_cache_by_post($comment->comment_post_ID);
    }
}

add_action('comment_post', 'pix_bump_comment_page_cache', 20);
add_action('edit_comment', 'pix_bump_comment_page_cache');
add_action('deleted_comment', 'pix_bump_comment_page_cache');
add_action('trashed_comment', 'pix_bump_comment_page_cache');
add_action('untrashed_comment', 'pix_bump_comment_page_cache');
add_action('spammed_comment', 'pix_bump_comment_page_cache');
add_action('unspammed_comment', 'pix_bump_comment_page_cache');
add_action('transition_comment_status', function($new_status, $old_status, $comment){
    if($new_status !== $old_status && $comment){
        pix_bump_comment_page_cache_by_post($comment->comment_post_ID);
    }
}, 10, 3);

function pix_render_comment_items($comments){
    ob_start();
    if($comments){
        wp_list_comments(array(
            'callback' => 'ppo_comment_format',
            'style' => 'ul',
            'short_ping' => true,
            'page' => 1,
            'per_page' => 0,
        ), $comments);
    }
    return ob_get_clean();
}

function pix_render_comment_nav($post_id, $pages, $current = 1){
    $post_id = absint($post_id);
    $pages = max(1, absint($pages));
    $current = max(1, min(absint($current), $pages));
    if($pages <= 1){
        return '';
    }

    $type = get_op('comment_nav','pagenav');
    if($type === 'btn'){
        $next = $current + 1;
        if($next > $pages){
            return '';
        }
        return '<div class="commentmore pix-moment-comment-more"><a class="commentmore-btn pix-moment-comment-more-btn" data-page="'.esc_attr($current).'" data-max="'.esc_attr($pages).'" post_id="'.esc_attr($post_id).'">加载更多</a></div>';
    }

    return '<nav class="commentnav pix-moment-comment-nav" data-fuck="'.esc_attr($post_id).'">'.paginate_comments_links(array(
        'total' => $pages,
        'current' => $current,
        'prev_text' => '<i class="ri-arrow-left-s-line"></i>',
        'next_text' => '<i class="ri-arrow-right-s-line"></i>',
        'echo' => false,
    )).'</nav>';
}

//ajax评论分页
add_action('wp_ajax_nopriv_ajax_comment_page_nav', 'ajax_comment_page_nav');
add_action('wp_ajax_ajax_comment_page_nav', 'ajax_comment_page_nav');
function ajax_comment_page_nav(){
    check_ajax_referer('moment_ajax', 'security');

    global $post,$wp_query, $wp_rewrite;
    $postid = absint($_POST["post_id"] ?? 0);
    $pageid = max(1, absint($_POST["paged"] ?? 1));
    $post = get_post($postid);
    $page_data = pix_comment_page_data($postid, $pageid);
    $wp_query->is_singular = true;
    $baseLink = '';
    if ($wp_rewrite->using_permalinks()) {
        $baseLink = '&base=' . user_trailingslashit(get_permalink($postid) . 'comment-page-%#%', 'commentpaged');
    }
    echo '<ul class="comment-list pix-moment-comment-list">';
        echo pix_render_comment_items($page_data['comments']);
    echo '</ul>';
    echo pix_render_comment_nav($postid, $page_data['pages'], $page_data['page']);
    die;
}

//ajax 点击加载更多评论
add_action('wp_ajax_cloadmore', 'ppo_comments_loadmore_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_cloadmore', 'ppo_comments_loadmore_handler'); // wp_ajax_nopriv_{action}

function ppo_comments_loadmore_handler(){
    check_ajax_referer('moment_ajax', 'security');

    $postid = absint($_POST["post_id"] ?? 0);
    $page = max(1, absint($_POST["page"] ?? 2));
    $page_data = pix_comment_page_data($postid, $page);
    $html = pix_render_comment_items($page_data['comments']);

    wp_send_json(array(
        'status' => 1,
        'html' => $html,
        'page' => $page_data['page'],
        'pages' => $page_data['pages'],
        'has_more' => $page_data['page'] < $page_data['pages'],
    ));
}

// 评论翻页类型
function comment_nav_type(){
    global $post,$wp_query;
    if(!$post){
        return '';
    }
    $page_data = pix_comment_page_data($post->ID, 1);
    return pix_render_comment_nav($post->ID, $page_data['pages'], 1);
}

//评论IP归属地
function get_comment_location($comment_id){
    $location = get_comment_meta($comment_id,'comment_location',true);
    if($location && get_op('comment_location', false)){
        return '<div class="com-location"><i class="ri-map-pin-2-line"></i>'.esc_html($location).'</div>';
    }
}

function pix_update_comment_location($comment_id){
    if(!get_op('comment_location', false)){
        return;
    }
    if(get_comment_meta($comment_id, 'comment_location', true)){
        return;
    }
    $location = get_ip_location(get_real_ip());
    if($location){
        update_comment_meta($comment_id, 'comment_location', $location);
    }
}
add_action('comment_post', 'pix_update_comment_location');

// 全局表情
add_filter('smilies_src', 'ppo_smilies_src', 1, 10);
function ppo_smilies_src($img_src, $img, $siteurl)
{
    //$img = rtrim($img, "png");
    return THEME_URL . '/img/emoji/' . $img . 'png';
}

function ppo_emoji_array(){
    $array = array('a-bang','a-bishi','a-bizui','a-cool','a-dabian','a-daku','a-fahuo','a-haixiu','a-han','a-jingya','a-jiong','a-kaixin','a-omg','a-qinmie','a-qinqin','a-sad','a-sikao','a-tushe','a-tushe','a-tuxie','a-wabi','a-wulian','a-wuyu');
    return $array;
}

function ppo_smilies_reset()
{
    global $wpsmiliestrans, $wp_smiliessearch;

    // don't bother setting up smilies if they are disabled
    if (!get_option('use_smilies'))
        return;

    $wpsmiliestrans = array(
        '[a-bang]' => 'a-bang.png',
        '[a-bishi]' => 'a-bishi.png',
        '[a-bizui]' => 'a-bizui.png',
        '[a-cool]' => 'a-cool.png',
        '[a-dabian]' => 'a-dabian.png',
        '[a-daku]' => 'a-daku.png',
        '[a-fahuo]' => 'a-fahuo.png',
        '[a-haixiu]' => 'a-haixiu.png',
        '[a-han]' => 'a-han.png',
        '[a-jingya]' => 'a-jingya.png',
        '[a-jiong]' => 'a-jiong.png',
        '[a-kaixin]' => 'a-kaixin.png',
        '[a-omg]' => 'a-omg.png',
        '[a-qinmie]' => 'a-qinmie.png',
        '[a-qinqin]' => 'a-qinqin.png',
        '[a-sad]' => 'a-sad.png',
        '[a-sikao]' => 'a-sikao.png',
        '[a-tushe]' => 'a-tushe.png',
        '[a-tuxie]' => 'a-tuxie.png',
        '[a-wabi]' => 'a-wabi.png',
        '[a-wulian]' => 'a-wulian.png',
        '[a-wuyu]' => 'a-wuyu.png',
    );
}
add_action('init', 'ppo_smilies_reset');

function ppo_get_emoji()
{
    $arr = ppo_emoji_array(); 
    $output = '';
    foreach ($arr as $emoji) {
        $output .= '<a class="add-smily pix-moment-emoji-item" data-action="addSmily" data-smilies="' . $emoji . '"><img class="wp-smiley" src="' . THEME_URL . '/img/emoji/' . $emoji . '.png" /></a>';
    }
    return $output;
}

add_action('wp_ajax_showemoji', 'showemoji'); 
add_action('wp_ajax_nopriv_showemoji', 'showemoji'); 
function showemoji(){
    $output = ppo_get_emoji();
    wp_send_json(array('html'=> $output));
}

// 评论按钮
function comment_tool_btn(){
    $drop = 'comt-tool-box';
    $html = '';
    $html .= '<div class="com-emoji-box pix-moment-comment-tool pix-moment-comment-emoji-tool"><a class="com-emoji-btn com-footer-btn pix-moment-comment-tool-btn pix-moment-comment-emoji-btn"><i class="ri-emotion-line"></i></a><div class="emoji-box pix-moment-comment-dropdown pix-moment-comment-emoji-dropdown '.$drop.'"><div class="emoji-inner pix-moment-comment-dropdown-inner pix-moment-comment-emoji-inner"></div></div></div>';
    //$html .= '<div class="com-code-box"><a class="com-code-btn com-footer-btn"><i class="ri-code-s-slash-line"></i></a><div class="code-box-drop '.$drop.'"><div class="code-drop-inner">'.comt_code_form().'</div></div></div>';
    if(get_op('comment_image_enable', true)){
        $html .= '<div class="com-img-box pix-moment-comment-tool pix-moment-comment-image-tool"><a class="com-img-btn com-footer-btn pix-moment-comment-tool-btn pix-moment-comment-image-btn"><i class="ri-image-add-line"></i></a><div id="img-box-drop" class="img-box-drop pix-moment-comment-dropdown pix-moment-comment-image-dropdown '.$drop.'"><div class="img-drop-inner pix-moment-comment-dropdown-inner pix-moment-comment-image-inner">'.comt_img_form().'</div></div></div>';
    }
    return $html;
}

// 评论图片表单
function comt_img_form(){
    $html = '';

    $html .= '<div class="comt-upload-wrap pix-moment-comment-upload-wrap">';
    $html .= '<div class="comt-upload-box pix-moment-comment-upload-box"></div>';
    $html .= '</div>';
    
    return $html;
}

// 评论代码表单
function comt_code_form(){
    $html = '';
    
    $html .= '<div class="comt-code-wrap">';  
    $html .= '<span>请输入您的代码:</span>'; 
    $html .= '<textarea rows="6" class="form-code input-textarea" placeholder="在此处粘贴或输入代码"></textarea>';   
    $html .= '<a class="insert-code">插入代码</a>';
    $html .= '</div>';
    
    return $html;
}

// 获取评论图片数组
function explore_comt_img($data){
    $arr = explode(',',$data);
    $img = '';
    if(is_array($arr)){
        foreach($arr as $list){
            $list = trim($list);
            if(!$list) continue;
            $url = get_bloginfo('url').'/wp-content/uploads/ppo-comt/'.$list; 
            $img .= '[img='.$url.']';
        }
    
        return $img ? '[d]'.$img.'[/d]' : '';
    }
    
}

function explore_comt_img_urls($data){
    $arr = explode(',', (string)$data);
    $img = '';
    if(is_array($arr)){
        foreach($arr as $url){
            $url = esc_url_raw(trim($url));
            if(!$url) continue;
            $img .= '[img='.$url.']';
        }
        return $img ? '[d]'.$img.'[/d]' : '';
    }
    return '';
}

// 评论点赞
function comment_like_btn($comment_id){
    $like_count = get_comment_meta( $comment_id, 'like_count', true );
    $like_count = $like_count ? intval($like_count) : 0;

    $liked_users = get_comment_meta( $comment_id, 'liked_users', true );
    $liked_users = is_array($liked_users) ? $liked_users : [];

    $current_user_id = get_current_user_id();
    $current_user_key = $current_user_id ? 'user_' . $current_user_id : null;
    $is_liked = $current_user_key && in_array($current_user_key, $liked_users);

    $like_class = $is_liked ? 'liked' : '';
    $like_icon = $is_liked ? '<i class="ri-thumb-up-fill"></i>' : '<i class="ri-thumb-up-line"></i>';

	$html = '<a href="javascript:void(0);" 
                class="comment-like-btn '.$like_class.'" 
                data-comment-id="'.$comment_id.'">
                    <span class="like-count">'.$like_icon.'</span><span class="count">'.$like_count.'</span>
                </a>';

                return $html;
}

add_action('wp_ajax_like_or_unlike_comment', 'handle_like_or_unlike_comment');
function handle_like_or_unlike_comment() {
    // ✅ 只有登录用户才能点赞
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => '请先登录后再点赞']);
    }

    $comment_id = intval($_POST['comment_id']);
    $liked = isset($_POST['liked']) ? intval($_POST['liked']) : 0;

    if (!$comment_id || get_comment($comment_id) === null) {
        wp_send_json_error(['message' => '评论不存在']);
    }

    $user_id = get_current_user_id();

    $liked_users = get_comment_meta($comment_id, 'liked_users', true);
    $liked_users = is_array($liked_users) ? $liked_users : [];

    $current_user_key = 'user_' . $user_id; // ✅ 只用 user_id 做记录，不用IP了
    $comment_info = get_comment($comment_id);
    if ($liked) {
        // 用户想取消点赞
        if (in_array($current_user_key, $liked_users)) {
            $liked_users = array_diff($liked_users, [$current_user_key]);
            ppo_msg_delete_like_comment($user_id, $comment_info->user_id, $comment_id);
        }
    } else {
        // 用户想点赞
        if (!in_array($current_user_key, $liked_users)) {
            $liked_users[] = $current_user_key;
            
            $msg_data = [
                'receive_user' => $comment_info->user_id, 
                'send_id' => $user_id, 
                'type' => 'comment_like',
                'title' => '赞了您的评论',
                'content' => $comment_info->comment_content,
                'related_id' => $comment_id, 
            ];
            $message_id = ppo_msg_add($msg_data);
            $note = '评论点赞';
            do_action('ppo_like_comment', $user_id, $note,$comment_id);
        }
    }

    update_comment_meta($comment_id, 'liked_users', $liked_users);

    $like_count = count($liked_users);
    update_comment_meta($comment_id, 'like_count', $like_count);

    wp_send_json_success([
        'like_count' => $like_count,
        'liked' => !$liked,
    ]);
}

// 用户中心评论回调
function ppo_render_user_comments($user_id, $page = 1, $per_page = 10) {
    $args = [
        'user_id' => $user_id,
        'status'  => 'approve',
        'number'  => $per_page,
        'offset'  => ($page - 1) * $per_page,
    ];

    $comments = get_comments($args);
    $total    = get_comments([
        'user_id' => $user_id,
        'count'   => true,
        'status'  => 'approve',
    ]);

    ob_start();
    if (!empty($comments)) {
        echo '<div class="user-comments-list pix-user-home-comments-list">';
        foreach ($comments as $comment) {

            $post_id   = $comment->comment_post_ID;
            $post_title = get_the_title($post_id);
            $post       = get_post($post_id);

            $thum = get_ppo_thum($post_id, 'medium','random');

            if (empty($post_title)) {
                $content = strip_shortcodes($post->post_content);
                $content = wp_strip_all_tags($content);
                $content = preg_replace('/<img[^>]+>/', '', $content); // 清除图片标签（保险）
                $title = wp_trim_words($content, 10,'...'); // 提取前10字

                if (empty($title) && $post->post_type === 'moment') {
                    $title = '片刻';
                } elseif (empty($title)) {
                    $title = '（无标题）';
                }
            } else {
                $title = $post_title;
            }
            ?>
            <div class="user-comment-item pix-user-home-comment-item">
                <div class="user-comment-item-inner pix-user-home-comment-card">
                <div class="left pix-user-home-comment-body">
                    <a class="title pix-user-home-comment-title" href="<?php echo esc_url(get_comment_link($comment)); ?>">
                    <i class="ri-share-circle-line"></i>
                        <?php echo esc_html($title); ?>
                    </a>
            
                    <div class="content pix-user-home-comment-content"><?php echo ppo_comment_filter_user($comment->comment_content, $comment->comment_ID); ?></div>
                    <time class="pix-user-home-comment-time"><?php echo esc_html(get_comment_date('', $comment)); ?></time>
                </div>

                <div class="right pix-user-home-comment-media"><a class="thum pix-user-home-comment-thumb-link" href="<?php echo esc_url(get_comment_link($comment)); ?>"><img class="lazy pix-user-home-comment-thumb" data-src="<?php echo esc_url($thum); ?>" alt=""></a></div>
                
                </div>
            </div>
            <?php
        }
        echo '</div>';

        // 分页
        echo '<div class="pix-user-home-pagination-wrap">';
        echo ppo_htmx_pager([
            'base_url'    => '/wp-json/ppo/v1/user-comments',
            'user_id'     => $user_id,
            'total_pages' => ceil($total / $per_page),
            'current'     => $page,
            'target'      => '#user-content',
            'push_url'    => true,
            'push_url_base' => get_author_posts_url($user_id),
            'query_args'  => ['tab' => 'comments'],
            'skeleton' => 'user-comment',
            'class'       => 'pix-user-home-pagination',
        ]);
        echo '</div>';
    } else {
        echo '<div class="nodata pix-user-home-empty pix-user-home-empty-state"><img src="'.get_template_directory_uri().'/img/empty.png" alt="暂无数据"></div>';
    }

    return ob_get_clean();
}

function ppo_get_user_comments($request) {
    $user_id  = intval($request->get_param('user_id'));
    $page     = max(1, intval($request->get_param('page')));

    if ($target = $request->get_param('target')) {
        $_GET['target'] = sanitize_text_field($target);
    }
    if ($push_url_base = $request->get_param('push_url_base')) {
        $_GET['push_url_base'] = sanitize_text_field($push_url_base);
    }

    $html = ppo_render_user_comments($user_id, $page, 8);

    echo $html;
    exit;
}
