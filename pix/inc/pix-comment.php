<?php

//评论表情
function smile_img(){
    $smile = '';
    $arr = array('&#128515;','&#128522','&#128521','&#128530','&#128536','&#128076','&#128077','&#128150','&#128139','&#128079','&#128514','&#128540','&#9996','&#127873','&#9749','&#127801','&#129318','&#128064','&#128518','&#129314','&#128520','&#128123','&#128586','&#128549','&#128566','&#128528','&#128548','&#128561','&#128591','&#128170');

        foreach($arr as $v){
            $smile .= '<a class="smile_btn">'.$v.'</a>';
        }

        return $smile;

}

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
        $comment_merge = $_POST['comment'].$_POST['comment_media'];
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
        $user_id = $user->ID;
        do_action('set_comment_cookies', $comment, $user);
        $GLOBALS['comment'] = $comment; //根据你的评论结构自行修改，如使用默认主题则无需修改
        $avatar = get_avatar( $comment, 50 );
        $url = get_comment_author_url();

        $comment_parent = $_POST['comment_parent'];
        $post = get_post($_POST['comment_post_ID']);
        $post_author = $post->post_author;
        

        //Events::add($_POST['comment_post_ID'],$user_id,$to_id,'reply',$_POST['comment'],'0',$comment_parent,$remark);
        ?>
        <li id="li-comment-<?php comment_ID() ?>" <?php comment_class(); ?>>
                <div id="comment-<?php comment_ID(); ?>" class="comment_body contents">	
                    <div class="profile">
                        <a href="<?php echo $url ?>" target="_blank"><?php echo pix_comment_ava($user_id,$comment) ?></a>
                    </div>	
                    <div class="com_right">				
                            <section class="commeta">
                                <div class="left">
                                    <h4 class="author"><a href="<?php echo $url ?>" target="_blank"><?php echo pix_comment_name($user_id); ?></a></h4>	
                                    <time itemprop="datePublished" datetime="<?php echo get_comment_date( 'c' );?>">待审核</time>									
                                </div>
                                <div class="right">
                                    <div class="info">
                                    </div>	
                                </div>
                            </section>
                        <div class="body">
                            <?php comment_text(); ?>
                        </div>
                    </div>    
                </div>
        <?php die();
    }

endif;

add_action('wp_ajax_nopriv_ajax_comment', 'fa_ajax_comment_callback');
add_action('wp_ajax_ajax_comment', 'fa_ajax_comment_callback');

/*-----------------------------------------------------------------------------------*/
/* COMMENT FORMATTING
/*-----------------------------------------------------------------------------------*/

if(!function_exists('cst_comment_format')){
    function cst_comment_format($comment, $args, $depth){
        $GLOBALS['comment'] = $comment;
        $user_id = $comment->user_id;
        $avatar = get_avatar( $comment, 50 );
        $url = get_comment_author_url();
        ?>
            <li id="li-comment-<?php comment_ID() ?>" <?php comment_class(); ?>>
                <div id="comment-<?php comment_ID(); ?>" class="comment_body contents">	
                    <div class="profile">
                        <a href="<?php echo $url ?>" target="_blank"><?php echo pix_comment_ava($user_id,$comment) ?></a>
                    </div>	
                    <div class="com_right">				
                            <section class="commeta">
                                <div class="left">
                                    <h4 class="author"><a href="<?php echo $url ?>" target="_blank"><?php echo pix_comment_name($user_id); ?></a><?php is_master($user_id); ?></h4>
                                    <time itemprop="datePublished" datetime="<?php echo get_comment_date( 'c' );?>"> · <?php echo timeago(get_gmt_from_date(get_comment_date('Y-m-d G:i:s'))); ?></time>									
                                </div>
                                <div class="right">
                                    <div class="info"> 
                                        <?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth'] , 'reply_text' => '<i class="ri-chat-1-line"></i>'))); ?>                               
                                    </div>	
                                </div>
                            </section>
                        <div class="body">
                            <?php comment_text(); ?>
                        </div>
                        <div class="comment_list_footer">
                            
                        </div> 
                    </div>    
                </div>				
        <?php
    }
}

//ajax评论分页
add_action('wp_ajax_nopriv_ajax_comment_page_nav', 'ajax_comment_page_nav');
add_action('wp_ajax_ajax_comment_page_nav', 'ajax_comment_page_nav');
function ajax_comment_page_nav(){
    global $post,$wp_query, $wp_rewrite;
    $postid = $_POST["post_id"];
    $pageid = $_POST["paged"];
    $comments = get_comments('post_id='.$postid.'&orderby=comment_date&order=desc');
    $post = get_post($postid);
    if( 'desc' != get_option('comment_order')){
        $comments = array_reverse($comments);
    }
    $wp_query->is_singular = true;
    $baseLink = '';
    if ($wp_rewrite->using_permalinks()) {
        $baseLink = '&base=' . user_trailingslashit(get_permalink($postid) . 'comment-page-%#%', 'commentpaged');
    }
    echo '<ul class="comment-list" >';
        wp_list_comments('page=' . $pageid . '&per_page=' . get_option('comments_per_page') . '&callback=cst_comment_format', $comments);//如果你的主题使用了回调函数，则要设置
    echo '</ul>';
    echo '<nav class="commentnav" data-fuck="'.$postid.'">';
    paginate_comments_links('total=' . get_comment_pages_count($comments).  '&current=' . $pageid . '&prev_text=<i class="ri-arrow-left-s-line"></i>&next_text=<i class="ri-arrow-right-s-line"></i>');
    echo '</nav>';
    die;
}

// 评论添加@
function cst_comment_add_at( $comment_text, $comment = '') {
    if( $comment->comment_parent > 0) {
        $parent_id = $comment->comment_parent;
        $comment_parent = get_comment($parent_id);
        $comment_content = $comment_parent->comment_content;
      $comment_text = '<a class="parents_at" uk-tooltip="title: '.$comment_content.'; pos: top" href="#comment-' . $comment->comment_parent . '">@'.get_comment_author( $comment->comment_parent ) . '</a> ' . $comment_text;
    }
    
    return $comment_text;
  }
  add_filter( 'comment_text' , 'cst_comment_add_at', 20, 2);
  
/**
 * 评论高亮作者
 */
function is_master($user_id = '') {
    if( empty($user_id) ) return;
    if( $user_id == 1 ) {
    echo '<i class="ri-bear-smile-line" uk-tooltip="title: 管理员; pos: top"></i>';
    }
}

//评论昵称
function pix_comment_name($user_id = ''){
    if( $user_id == 1 ) {
        return get_nickname();
    } else {
        return comment_author();
    }
}

//评论头像
function pix_comment_ava($user_id = '',$comment){
    if( $user_id == 1 ) {
        return get_user_avatar();
    } else {
        return get_avatar( $comment, 50 );
    }
}

//ajax加载话题评论
function load_t_comment(){
    global $post,$wp_query, $wp_rewrite;
    $pid = $_POST['pid'];
    $current_user = wp_get_current_user();
	$user_id = $current_user->ID;
    $comments = get_comments(array('post_id'=>$pid,'status'=> 'approve'));
    $number = get_option('comments_per_page');
    $pages = get_comment_pages_count($comments);
    $pageid = 1;

    if( 'desc' != get_option('comment_order')){
        $comments = array_reverse($comments);
        $pageid = $pages;
    }
    $wp_query->is_singular = true;
    $baseLink = '';
    if ($wp_rewrite->using_permalinks()) {
        $baseLink = '&base=' . user_trailingslashit(get_permalink($postid) . 'comment-page-%#%', 'commentpaged');
    }

    echo '<ul class="comment-list uk-animation-slide-bottom-small">';
        if($comments){
            wp_list_comments('page=' . $pageid . '&per_page=' . get_option('comments_per_page') . '&callback=cst_comment_format', $comments);//如果你的主题使用了回调函数，则要设置
        } else {
            echo '<p class="nodata"><i class="ri-ghost-line"></i>空空如也！</p>';
        }
     
    echo '</ul>';
    if($pages > 1){
        echo '<nav class="commentnav" data-fuck="'.$pid.'">';
        paginate_comments_links('total=' . get_comment_pages_count($comments).  '&current=' . $pageid . '&prev_text=<i class="ri-arrow-left-s-line"></i>&next_text=<i class="ri-arrow-right-s-line"></i>');
        echo '</nav>';
    }

    die();
}
add_action('wp_ajax_nopriv_load_t_comment', 'load_t_comment');
add_action('wp_ajax_load_t_comment', 'load_t_comment');

//评论表情
function show_smile_btn(){
    $html = '<div class="comment_smile_box">
                <a class="com_smile_btn"><i class="ri-emotion-line"></i></a>
                <div class="com_smile_show shadow" uk-drop="mode: click;toggle:.com_smile_btn"><div class="inner round8">'.smile_img().'</div></div>
             </div>';

    return $html;       
}

//访客评论头像获取
function comment_visitor( $user_id, $author_name, $author_email, $avatar_size ) {
    $user_info = get_userdata($user_id);
	if ( $user_id ) { // 用户
		$user = get_userdata( $user_id );
		$avatar = get_avatar_url( $user->user_email, array( 'size' => $avatar_size ) );
		$condition = '<a class="edit-profile login_avatar" href="'. wp_logout_url( cst_get_curl() ) .'" target="_top" >'.pix_comment_ava($user_id,$author_email).'<small>'.pix_comment_name($user_id).' , 登出</small></a>';
	}
	elseif ( $author_name ) { // 访客
		$avatar = get_avatar_url( $author_email, array( 'size' => $avatar_size ) );
		$condition = '<a class="edit-profile edit-card"><img src="'. $avatar .'" height="50" width="50" class="v-avatar avatar avatar-50"><small>'.$author_name.' , 修改信息</small></a>';
	}
	else { // 匿名
		$avatar = get_bloginfo( 'template_directory' ).'/img/avatar.png';
		$condition = '<a class="edit-profile edit-card"><img src="'. $avatar .'"  height="50" width="50" class="v-avatar avatar avatar-50"><small>点击填写昵称和邮箱，方可发布评论</small></a>';
	}

	echo $condition;
}

//ajax获取评论头像
add_action('wp_ajax_nopriv_ajax_avatar_get', 'ajax_avatar_get');
add_action('wp_ajax_ajax_avatar_get', 'ajax_avatar_get');
function ajax_avatar_get(){
    $email = isset($_POST['email']) ? $_POST['email'] : false;
    $name = isset($_POST['name']) ? $_POST['name'] : '神秘访客';
    if($email){
        $res = get_avatar_url( $email, array( 'size'=>50 ) );
        $avatar = preg_replace("/http:\/\/(www|\d).gravatar.com\/avatar\//","https://sdn.geekzu.org/avatar/",$res);
     echo json_encode(array('avatar'=>$avatar,'name'=>$name?$name:'神秘访客'));
     exit();
    } else {
        return;
    }
}

//防垃圾评论
function refused_spam_comments( $comment_data ) { 
    $pattern = '/[一-龥]/u'; 
    if(!preg_match($pattern,$comment_data['comment_content'])) { 
        fa_ajax_comment_err('评论中必须含中文!'); 
    } 
    return( $comment_data ); 
    } 
    add_filter('preprocess_comment','refused_spam_comments');
    
// WordPress禁止日文评论
function pix_comment_jp_post( $incoming_comment ) {
    $jpattern ='/[ぁ-ん]+|[ァ-ヴ]+/u';
    if(preg_match($jpattern, $incoming_comment['comment_content'])){
        fa_ajax_comment_err( "评论中禁止发日文字符！" );
    }
    return( $incoming_comment );
    }
add_filter('preprocess_comment', 'pix_comment_jp_post');

//机器人验证
function no_robot_check(){
    $html = '';
    $html = '<div class="comment-form-validate">
                <input class="pix-checkbox-radio" type="checkbox" name="no-robot">
                <label for="default" class="comment_check_style"></label>			
            </div>';
    return  $html;       
}

function pix_robot_comment(){
    if ( !$_POST['no-robot'] && !is_user_logged_in()) {
        fa_ajax_comment_err(__('请解锁后再提交评论'));
    }
  }
if(get_op('com_robot')){add_action('pre_comment_on_post', 'pix_robot_comment');}



