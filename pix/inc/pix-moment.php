<?php

//增加文章样式-广场
function cst_custom_post_moment() {
	$name = '片刻';
    $labels = array(
        'name'               => sprintf( '%1$s',$name),
        'singular_name'      => sprintf( '%1$s',$name),
        'add_new'            => sprintf( '新建一个%1$s',$name),
        'add_new_item'       => sprintf( '新建一个%1$s',$name),
        'edit_item'          => sprintf( '编辑%1$s',$name),
        'new_item'           => sprintf( '新%1$s',$name),
        'all_items'          => sprintf( '所有%1$s',$name),
        'view_item'          => sprintf( '查看%1$s',$name),
        'search_items'       => sprintf( '搜索%1$s',$name),
        'not_found'          => sprintf( '没有找到有关的%1$s',$name),
        'not_found_in_trash' => sprintf( '回收站里没有%1$s',$name),
        'parent_item_colon'  => '',
        'menu_name'          => sprintf( '%1$s',$name),
    );
    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_position' => 5,
		'menu_icon'     => 'dashicons-marker',
        'supports'      => array( 'title', 'editor', 'thumbnail', 'comments','custom-fields' ),
        'has_archive'   => true,
        //'show_in_rest'  => true,
        'rewrite' => array( 'slug' => 'moment' ),
		//'taxonomies' => array('category', 'post_tag')
    );
    register_post_type( 'moment', $args );
}
add_action( 'init', 'cst_custom_post_moment' );

//添加一个分类法moment
function cst_moment_taxonomy(){
    $name = '片刻';
    $labels = array(
            'name' => sprintf( '%1$s分类',$name),
            'singular_name' => sprintf( '%1$s分类',$name),
            'search_items' => __( '搜索' ,'ziranzhi2' ),
            'popular_items' => sprintf( '热门的%1$s分类',$name),
            'all_items' => sprintf( '所有%1$s分类',$name),
            'edit_item' => sprintf( '编辑%1$s分类',$name),
            'update_item' => sprintf( '更新%1$s分类',$name),
            'add_new_item' => sprintf( '新建%1$s分类',$name),
            'new_item_name' => sprintf( '新的%1$s分类',$name),
    );
    $args = array(
            'labels' => $labels,
            'hierarchical' => true,//分层级
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'           => array( 'slug' => 'moments' ),
    );
    register_taxonomy('moments',array('moment'), $args);
}
add_action('init', 'cst_moment_taxonomy');


add_action( 'init', 'cst_add_taxonomies_to_courses' );
function cst_add_taxonomies_to_courses() {
	register_taxonomy_for_object_type( 'post_tag', 'moment' );
}

//支持置顶
add_action( 'add_meta_boxes', 'pix_add_moment_box' );
function pix_add_moment_box(){
add_meta_box( 'pix_moment_sticky', '置顶片刻', 'pix_moment_sticky', 'moment', 'side', 'high' );
}
function pix_moment_sticky(){ ?>
 <input id="super-sticky" name="sticky" type="checkbox" value="sticky" <?php checked( is_sticky() ); ?> /><label for="super-sticky" class="selectit">置顶片刻</label>
 <?php }


/*-----------------------------------------------------------------------------------*/
 /* 圈子分类列表
 /*-----------------------------------------------------------------------------------*/
 function get_moment_cat(){
	$arr = get_op('moments_cat_list');
    $lists = get_terms( array(
        'taxonomy'     => 'moments',
        'include'      => $arr,
        'count'        => true,
        'hide_empty'   => 0,
        'orderby'      => 'include',
    ) );
    
    echo '<div class="moment_cat_nav"><ul><li><a data="0" class="active">全部<span></span></a></li>';
    foreach($lists as $list){
        $name = $list->name;
        $id = $list->term_id;
        $count = $list->count;
        //$link = get_term_link($id,'category');
        $html = '<li>
                    <a data="'.$id.'"><span>'.$name.'</span></a>
                </li>';

        echo $html;     
    }
    echo '</ul></div>';
    
}

//获取所有圈子id
function get_all_topics_id() {
    $cat_ids = get_terms(
        array(
            'taxonomy' => 'moments',
            'fields'   => 'ids',
            'get'      => 'all',
        )
    );
    
    
    return $cat_ids;
    
    
}

//获取话题分类
function get_t_cat(){
    $arr = get_op('moments_cat_list');
    $topic_category = get_terms( array(
        'taxonomy' => 'moments',	
        'include'      => $arr,					       
        'orderby'    => 'include',
        'count'        => true,
        'hide_empty' => 0,//分类下没有文章也显示
        "number"=>10
    ) );
    if(is_array($topic_category)){
        foreach ($topic_category as $cat) {

        echo '<li class="topic_default_cat_'.$cat->term_id.'" catid="'.$cat->term_id.'"><span class="c_name"><i class="ri-hashtag"></i><span>'.$cat->name.'</span></span><span style="color: #a8c1b0;padding-right: 3px;">'.$cat->count.'</span></li>';
    }
 }
}

//ajax上传图片到媒体库
function upload_topic_img(){
    $valid_formats = array("jpg", "png", "gif", "webp", "jpeg"); 
    $wp_upload_dir = wp_upload_dir();
    $path = $wp_upload_dir['path'] . '/';
    $img_size = 5;
    $max_file_size = 1024000 * $img_size; 
    $max_image_upload = 9;
    $files = $_FILES['topic_img_up'];

    if(!empty($files)){
    if(get_user_role('administrator') || get_user_role('author')){
        //require_once(ABSPATH . 'wp-admin/includes/image.php');
        //require_once(ABSPATH . 'wp-admin/includes/media.php');
        $output = array();

        if( ( count( $files['name'] ) ) > $max_image_upload ) {
            $msg = array('code'=>'1','msg'=>'最多上传'.$max_image_upload.'张图片');
        } else {

        foreach($files['name'] as $fileindex => $filename){
           $file_type = strtolower($files['type'][$fileindex]);
           $extension = pathinfo( $filename, PATHINFO_EXTENSION );
           $new_filename = pix_generate_random_code( 20 )  . '.' . $extension;

           if ( $files['error'][$fileindex] == 0 ) {
                if(! in_array( strtolower( $extension ), $valid_formats )){
                    $msg = array('code'=>'1','msg'=>'文件格式错误！');
                    continue;
                } else if($files['size'][$fileindex] > $max_file_size){
                    $msg = array('code'=>'1','msg'=>'图片最大'.$img_size.'MB');
                    continue;
                } else {
                if( move_uploaded_file( $files["tmp_name"][$fileindex], $path.$new_filename )){
                    $new_file = $path.$new_filename;
                    $filetype = wp_check_filetype( basename( $filename ), null );
                    $wp_upload_dir = wp_upload_dir();
                        $attachment = array(
                            'guid'           => $wp_upload_dir['url'] . '/' . basename( $new_file ),
                            'post_mime_type' => $filetype['type'],
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        );
                    $attach_id = wp_insert_attachment( $attachment, $new_file);
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                           
                    // Generate meta data
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $new_file );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    $attach_url = wp_get_attachment_image_src($attach_id, 'full')[0];

                    array_push($output, array(
                        'thumb' => wp_get_attachment_image_src($attach_id, 'large')[0],
                        'src' => $attach_url,
                    ));

                    $msg = array('code'=>'0','msg' => $output);               
                }
           }
        }
        
        }
    }
        echo json_encode($msg);
        exit();
    }
}

      
}
add_action('wp_ajax_nopriv_upload_topic_img', 'upload_topic_img');
add_action('wp_ajax_upload_topic_img', 'upload_topic_img');

//判断用户角色
function get_user_role($roles){
    global $current_user;
    if(in_array( $roles, $current_user->roles)){
        return true;
    }
}

//随机图片名
function pix_generate_random_code($length=10) {
 
    $string = '';
    $characters = "23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz";
  
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters)-1)];
    }
  
    return $string;
  
 }

//获取媒体库图片
function get_media_imglist(){
    
    $paged = isset( $_POST['paged'] ) ? $_POST['paged'] : '1';
    $posts_per_page = '20';
    $post_offset = ($paged - 1) * $posts_per_page;
    $args = array(
        'post_type'=>'attachment',
        'posts_per_page' => $posts_per_page,
        'post_status'=>'inherit',
        'post_mime_type' => 'image',
        'offset' => $post_offset,
        'orderby'          => 'date',
    );
    $attachments = get_posts($args);
    $post_count = $count = array_sum( (array) wp_count_attachments( $mime_type = 'image' ) );
    $num_pages = ceil($post_count / $posts_per_page);
    $output = array();
    //echo '<div class="get_media_list">';
     if($attachments){
           foreach($attachments as $attachment){
            $attach_id = $attachment->ID;
            $url =  wp_get_attachment_image_src($attach_id, 'full')[0];
            $thum =  wp_get_attachment_image_src($attach_id, 'large')[0];

            array_push($output, array(
                'thumb' => $thum,
                'src' => $url
            ));

            }
       }
    echo json_encode(array('code'=> '0','max_page' => $num_pages,'list'=>$output)); 
    exit();
} 
add_action('wp_ajax_nopriv_get_media_imglist', 'get_media_imglist');
add_action('wp_ajax_get_media_imglist', 'get_media_imglist');

//话题发布
function push_topic(){
    if(!is_user_logged_in()){
		echo json_encode(array('status'=>'0','msg'=>'请登录'));
	}

    $current_user = wp_get_current_user();	
	$user = $current_user->ID;
	$max_length = '800';

    $moment_data = isset( $_POST['moment_data'] ) ? $_POST['moment_data'] : false;
	$content = isset( $_POST['content'] ) ? $_POST['content'] : '';
	$catid = isset( $_POST['catid'] ) ? $_POST['catid'] : '';
    $catname = isset( $_POST['catname'] ) ? $_POST['catname'] : '';
	$title = isset( $_POST['title'] ) ? $_POST['title'] : '';
    $loca = isset( $_POST['loca'] ) ? $_POST['loca'] : '';
    $simi = isset( $_POST['simi'] ) ? $_POST['simi'] : '';
    $act = isset( $_POST['act'] ) ? $_POST['act'] : '';
    $pid = isset( $_POST['pid'] ) ? $_POST['pid'] : '0';
    $moment_type = isset( $_POST['moment_type'] ) ? $_POST['moment_type'] : 'image';

    //$content = '<div class="t_content">'.$content.'</div>';

    if(!$catid && !$catname) {
        echo json_encode(array('status'=>'0','msg'=>'请选择分类'));
        exit;
	}

    if( current_user_can( 'read' ) && !current_user_can( 'edit_posts' ) || !current_user_can( 'edit_posts' ) ) {
        echo json_encode(array('status'=>'0','msg'=>'您没有发布权限！'));
		exit;
	}

    if(mb_strlen($content) > $max_length) {
        echo json_encode(array('status'=>'0','msg'=>'最多发表字数为800'));
		exit;
	}

    $term = get_term_by('name', $catname, 'moments');
	//如果分类不存在,那么创建此分类
	if(!$term){
	    $resout = wp_insert_term(
            $catname,
	      'moments',
	      array(
	        'slug' => $catname,
	      )
	    );
	
	    if(is_wp_error( $resout )){
	        $id = $resout->error_data;
	        $id = $id['term_exists'];
	    }else{
	        $id = $resout['term_id'];
	    }
	    $catid = $id;
	}else{
	    $catid = $term->term_id;
	}

    $push_data = array();

    switch ($moment_type)
        {
        case 'image':
            $push_data['moment_ga'] = $moment_data;
            break;
        case 'card':
            $push_data['moment_card'] = $moment_data;
            break;
        case 'audio':
            $push_data['moment_audio'] = $moment_data;
            break;    
        case 'video':
            $push_data['moment_video'] = $moment_data;
            break;     
        }

    $push_data['mylocal'] = $loca; 
    $push_data['moment_type'] = $moment_type; 


    if($simi == '1'){
        $post_status = 'publish';
    } else if($simi == '0'){
        $post_status = 'private';
    }


    $topic = array(
		'post_title' => $title,
        'post_type' => 'moment',
        'post_author' => $user, 
        'post_content' => $content,
		'post_status' => $post_status,
		'comment_status'=>'open',
		'tax_input' => array(
		    'moments' => array($catid),
		),
        'meta_input' => $push_data
    );
	
    if($act == 'push'){
        $post_id = wp_insert_post( $topic );
    } else if($act == 'update'){
        $topic['ID'] = $pid;
        wp_update_post($topic);
        //更新音乐api参数
        $audio_cache = get_post_meta($pid,'audio_cache',true);
        if($audio_cache){
            delete_post_meta( $pid, 'audio_cache' );
        }
    }
        
    

}
add_action('wp_ajax_nopriv_push_topic', 'push_topic');
add_action('wp_ajax_push_topic', 'push_topic');

//当片刻没有标题时默认显示内容前10字符
add_action( 'load-edit.php', function()
{
    add_filter( 'the_title', function( $title )
    {
        $post = get_post();
        if( is_a( $post, '\WP_Post' ) && ! $post->post_title && $post->post_content )
            $title = wp_trim_words( strip_shortcodes( strip_tags( $post->post_content ) ), 10 );
        return $title;
    } );
} );

//分类筛选
function moment_cat_filter(){
    global $wp_query;
    $sticky_html = '';
	$cat = !empty($_POST['cat']) ? esc_sql($_POST['cat']) : get_all_topics_id();

    $sticky = get_option('sticky_posts');
	$args = array(
		'post_type' => 'moment', 
		'tax_query' => array(
            array(
                'taxonomy' => 'moments',
                'field'    => 'term_id',
                'terms'    => $cat,
            ),
        ),
        'post_status' => 'publish',
	);

    if(empty($_POST['cat'])){ //只在全部的时候显示置顶
        $args['post__not_in'] = $sticky;
        $sticky_html = moment_sticky_loop();
    }

	query_posts( $args );
    
	if( have_posts() ) :
 
 		ob_start(); 
 
		while( have_posts() ): the_post();
 
			get_template_part( 'tpl/content', 'moment' );
 
		endwhile;
 
 		$posts_html = ob_get_contents(); // we pass the posts to variable
   		ob_end_clean(); // clear the buffer
	else:
		$posts_html = '<p class="no_posts"><img class="s_nodata" src="'.THEME_URL.'/img/nodata.png"></p>';
	endif;

	// no wp_reset_query() required
    $output = array();
    $post_list = $wp_query->posts;
    foreach($post_list as $list){
        $pid = $list->ID;
        //$attach = get_post_meta($pid,'moment_ga',true);
        $data = array(
            'content' => $list->post_content,
            'pid' => $pid
            //'attach' => $attach,
        );

        array_push($output, $data);
       
    }  

    if(get_op('qiniu_cdn')) {
        $posts_html = ajax_qiniu_cdn_replace($posts_html);
        $sticky_html = ajax_qiniu_cdn_replace($sticky_html);
    }


 	echo json_encode( array(
		'posts' => json_encode( $wp_query->query_vars ),
		'max_page' => $wp_query->max_num_pages,
		'found_posts' => $wp_query->found_posts,
		'content' => $sticky_html.$posts_html,
        'post_data'=> $output,
	) );
      
	exit();
}
add_action('wp_ajax_nopriv_moment_cat_filter', 'moment_cat_filter');
add_action('wp_ajax_moment_cat_filter', 'moment_cat_filter');


//置顶片刻列表
function moment_sticky_loop(){
    $sticky = get_option('sticky_posts');
	$args = array(
		'post_type' => 'moment', 
        'post__in' => $sticky,
        'posts_per_page' => 5,       
	);

    if(is_array($sticky) && !empty($sticky)){
        $stick_query = new WP_Query($args);
        if( $stick_query->have_posts() ) {
            ob_start();
            while ($stick_query->have_posts()) : $stick_query->the_post();
            get_template_part( 'tpl/content','moment');
        endwhile; 
        $posts_html = ob_get_contents(); // we pass the posts to variable
   		ob_end_clean(); // clear the buffer
        wp_reset_postdata(); 
        }

        return $posts_html;
    }
 
}


/*-----------------------------------------------------------------------------------*/
/* moment文章加载
/*-----------------------------------------------------------------------------------*/
function moment_list_load(){
    global $wp_query;
    
	$params = json_decode( stripslashes( $_POST['query'] ), true ); 
	$params['paged'] = $_POST['page'] + 1; 
	$params['post_status'] = 'publish';
    //$sticky = get_option('sticky_posts');
    //$params['post__in'] = $sticky;
    //$params['ignore_sticky_posts'] = 1;
    //$params['post_type'] = 'moment';
 
	
	query_posts( $params );
 
	if( have_posts() ) :
        ob_start(); 
		// run the loop
		while( have_posts() ): the_post();
 			
		get_template_part( 'tpl/content', 'moment');
		
		endwhile;
        $posts_html = ob_get_contents(); // we pass the posts to variable
   		ob_end_clean(); // clear the buffer
	endif;

    if(get_op('qiniu_cdn')) {
        $posts_html = ajax_qiniu_cdn_replace($posts_html);
    }
    $output = array();
    $post_list = $wp_query->posts;
    foreach($post_list as $list){
        $pid = $list->ID;
        //$attach = get_post_meta($pid,'moment_ga',true);
        $data = array(
            'content' => $list->post_content,
            'pid' => $pid
            //'attach' => $attach,
        );

        array_push($output, $data);
       
    }
 

 	echo json_encode( array(
		//'posts' => json_encode( $wp_query->query_vars ),
		//'max_page' => $wp_query->max_num_pages,
		//'found_posts' => $wp_query->found_posts,
		'content' => $posts_html,
        'post_data'=> $output,
	) );
    
	exit(); 

}	
add_action('wp_ajax_nopriv_moment_list_load', 'moment_list_load');
add_action('wp_ajax_moment_list_load', 'moment_list_load');


//片刻内容链接正则替换
function moment_excerpt(){
    global $post;
    $pid = $post->ID;
    //$max = 30;
    $output = '';
    $max = get_op('read_more_num') ? get_op('read_more_num') : '120';
    $content = get_the_content($pid);
    $length = mb_strlen($content);
    
    if($length > $max && get_op('read_more_op')){
        $exp = mb_substr($content,0,$max);
        $other = mb_substr($content,$max,$length);
        $output = ''.$exp.'<span class="dotd">...</span><span class="rm_hidden">'.$other.'</span><a class="show-more-btn">展开</a><a class="read-less-btn"><i class="ri-arrow-up-line"></i>收起</a>';
    } else {
        $output = $content;
    }
    $pattern = "/(http[s]?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"\s])*)/i";
    $replacement = '<a class="mo_link" target="_blank" href="$1"><i class="ri-links-line"></i>$1</a>';
    $str_content = preg_replace($pattern, $replacement, $output);
    return $str_content;          
}


/*
function moment_readmore(){
    $num = get_op('read_more_num') ? get_op('read_more_num') : '120';
    $output = '';
    if(get_op('read_more_op')){
        $output = '{"type": "text", "limit": '.$num.', "more": "展开", "less": "↑收起"}';
        $output = "data-config = '$output'";
    }
    return $output;
}
*/


//获取图集
function get_gallery(){
    global $post;
    $pid = $post->ID;
    $lists = get_post_meta($pid,'moment_ga',true);
    $html = '';
    if(isset($lists) && is_array($lists)){
        foreach($lists as $index => $list){
            $src = $list['src'];
            $thum = $list['thum'];
            $html .= '<a class="fancybox mo_img" href="'.esc_url($src).'" data-fancybox="post-images-'.$pid.'"><img class="lazy" src="'.THEME_URL .'/img/lazyload.png" data-src="'.esc_url($thum).'"></a>';
        }
    
        return '<div class="img_list">
                    <div class="list_inner">'.$html.'</div>
                </div>';
    }
       
    
}

//正则替换内容中链接
function replace_content_link($content){
    $pattern = "/(http[s]?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"\s])*)/i";
    $replacement = '<a class="mo_link" target="_blank" href="$1"><i class="ri-links-line"></i>$1</a>';
    $str_content = preg_replace($pattern, $replacement, $content);
}

//片刻点赞
function pix_ajax_like(){
    $pid = isset( $_POST['pid'] ) ? $_POST['pid'] : false;
    $action = $_POST["like_action"];
    if ( $action == 'up'){
        $num = get_post_meta($pid,'moment_like',true);
        $expire = time() + 99999999;
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost
        setcookie('pix_like_'.$pid,$pid,$expire,'/',$domain,false);
        if (!$num || !is_numeric($num)) {
            update_post_meta($pid, 'moment_like', 1);
        } 
        else {
            update_post_meta($pid, 'moment_like', ($num + 1));
        }
        echo get_post_meta($pid,'moment_like',true);
    } 
    die;
    
}
add_action('wp_ajax_nopriv_pix_ajax_like', 'pix_ajax_like');
add_action('wp_ajax_pix_ajax_like', 'pix_ajax_like');

function get_like_btn(){
    global $post;   	  
	$post_id = $post -> ID;
	$done = isset($_COOKIE['pix_like_' . $post_id]) ? 'done' : '';
	$num = get_post_meta($post_id, 'moment_like', true);
	$count = $num ? $num : '0';
    $icon = isset($_COOKIE['pix_like_' . $post_id]) ? '<i class="ri-heart-2-fill"></i>' : '<i class="ri-heart-2-line"></i>';
	return '<a class="up_like '.$done.'" data-action="up" data-id="'.$post_id.'">
            '.$icon.'
			<span>'.$count.'</span>
		 </a>';
}

function get_like() {
	$like = get_post_meta( get_the_ID(), 'moment_like', true );
	$like = $like ? $like : 0;
	return $like;
}

//修改话题文章类型固定链接结构
add_filter('post_type_link', 'custom_topic_link', 1, 3);
function custom_topic_link( $link, $post = 0 ){
    if ( $post->post_type == 'moment' ){
        return home_url( 'moment/' . $post->ID .'.html' );
    } else {
        return $link;
    }
}
add_action( 'init', 'topic_rewrites_init' );
function topic_rewrites_init(){
    add_rewrite_rule(
        'moment/([0-9]+)?.html$',
        'index.php?post_type=moment&p=$matches[1]',
        'top' );
    add_rewrite_rule(
        'moment/([0-9]+)?.html/comment-page-([0-9]{1,})$',
        'index.php?post_type=moment&p=$matches[1]&cpage=$matches[2]',
        'top'
        );
}


//置顶样式
function cpt_sticky_class($classes) {
            if ( is_sticky() ) : 
            $classes[] = 'sticky';
            return $classes;
        endif; 
        return $classes;
                }
add_filter('post_class', 'cpt_sticky_class');

//删除片刻 丢入回收站
function trash_moment(){
    $pid = isset( $_POST['pid'] ) ? $_POST['pid'] : false;
    if($pid){
        $res = wp_trash_post($pid);
    }

    if($res == true){
        $msg = array('state'=>'1');
    } else {
        $msg = array('state'=>'0');
    }

    echo json_encode($msg);
    exit(); 
}
add_action('wp_ajax_nopriv_trash_moment', 'trash_moment');
add_action('wp_ajax_trash_moment', 'trash_moment');

//前台编辑片刻
function moment_edit_modal(){
    $pid = isset( $_POST['pid'] ) ? $_POST['pid'] : false;
    if($pid){
        $data = array();
        $type = get_post_meta($pid,'moment_type',true);
        if($type == 'image' || $type == ''){
            $type = 'ga';
        }
        $title = get_the_title($pid);
        $content = get_post($pid)->post_content;
        $data['moment_data'] = get_post_meta($pid,'moment_'.$type,true);
        $data['mylocal'] = get_post_meta($pid,'mylocal',true);
        $data['m_type'] = $type;
        $data['title'] = $title;
        $data['content'] = $content;
        $term = wp_get_post_terms($pid,'moments');
        $data['cat'] = $term[0]->name;
        $data['cid'] = $term[0]->term_id;
        $data['pid'] = $pid;
    }

    echo json_encode($data);
    exit();
}
add_action('wp_ajax_nopriv_moment_edit_modal', 'moment_edit_modal');
add_action('wp_ajax_moment_edit_modal', 'moment_edit_modal');

//获取图片片刻的第一张图
function get_image_moment_f($pid){
    $img = '';
    $type = get_post_meta($pid,'moment_type',true);
    if($type == 'image' || $type == ''){
        $data = get_post_meta($pid,'moment_ga',true);
        if(is_array($data)){
            $img = $data[0]['src'];
        } 
    }

    return $img;
}

//ajax置顶片刻
function stick_moment(){
    $pid = isset( $_POST['pid'] ) ? $_POST['pid'] : false;
    $type =  isset( $_POST['state'] ) ? $_POST['state'] : false;

    if($pid){
        if($type == 'stick'){
            $res = stick_post( $pid );
            $succ = array('state'=>'1','msg' => '置顶成功','type'=>'stick');
        } else {
            $res = unstick_post( $pid );
            $succ = array('state'=>'1','msg' => '取消置顶','type'=>'unstick');
        }
    }

    

    wp_send_json($succ);
}
add_action('wp_ajax_nopriv_stick_moment', 'stick_moment');
add_action('wp_ajax_stick_moment', 'stick_moment');
