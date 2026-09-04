<?php

//获取文章分类
function get_posts_cat(){
	$arr = get_op('posts_cat_list');
    $lists = get_terms( array(
        'taxonomy'     => 'category',
        'include'      => $arr,
        'count'        => true,
        'hide_empty'   => 0,
        'orderby'      => 'include',
    ) );
    
    echo '<div class="posts_cat_nav"><ul><li><a data="0" class="active">全部<span></span></a></li>';
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

/*-----------------------------------------------------------------------------------*/
/* blog首页循环 
/*-----------------------------------------------------------------------------------*/
function blog_mod_loop(){

	global $wp_query;
	$sticky_html = '';
	$cat = !empty($_POST['cat']) ? esc_sql($_POST['cat']) : '';
	$sticky = get_option('sticky_posts');
	$args = array(
		'post_type' => 'post', 
		'cat' => $cat,
		'post_status' => 'publish',
	);

	if(empty($_POST['cat'])){ //只在全部的时候显示置顶
        $args['post__not_in'] = $sticky;
        $sticky_html = posts_sticky_loop();
    }

	query_posts( $args );
 
	if( have_posts() ) :
 
 		ob_start(); 
 
		while( have_posts() ): the_post();
 
		post_show_type();
 
		endwhile;
 
 		$posts_html = ob_get_contents(); // we pass the posts to variable
   		ob_end_clean(); // clear the buffer
	else:
		$posts_html = '<p class="no_posts"><img class="s_nodata" src="'.THEME_URL.'/img/nodata.png"></p>';
	endif;
 
	// no wp_reset_query() required
	if(get_op('qiniu_cdn')) {
        $posts_html = ajax_qiniu_cdn_replace($posts_html);
    }
 	echo json_encode( array(
		'posts' => json_encode( $wp_query->query_vars ),
		'max_page' => $wp_query->max_num_pages,
		'found_posts' => $wp_query->found_posts,
		'content' => $sticky_html.$posts_html
	) );
 
	exit();
	
}
add_action('wp_ajax_nopriv_blog_mod_loop', 'blog_mod_loop');
add_action('wp_ajax_blog_mod_loop', 'blog_mod_loop');

//置顶文章列表
function posts_sticky_loop(){
    $sticky = get_option('sticky_posts');
	$args = array(
		'post_type' => 'post', 
        'post__in' => $sticky,
        'posts_per_page' => 5,       
	);

    if(is_array($sticky) && !empty($sticky)){
        $stick_query = new WP_Query($args);
        if( $stick_query->have_posts() ) {
            ob_start();
            while ($stick_query->have_posts()) : $stick_query->the_post();
            post_show_type();
        endwhile; 
        $posts_html = ob_get_contents(); // we pass the posts to variable
   		ob_end_clean(); // clear the buffer
        wp_reset_postdata(); 
        }

        return $posts_html;
    }
 
}

/*-----------------------------------------------------------------------------------*/
/* blog首页ajax加载文章 update 5.18
/*-----------------------------------------------------------------------------------*/
function blog_mod_load(){
	global $wp_query;
	$sticky_html = '';
	$type = isset($_POST['pager_type']) ? $_POST['pager_type'] : '';
	$params = json_decode( stripslashes( $_POST['query'] ), true ); 
	if(!empty($type)){
		$params['paged'] = $_POST['page']; 
		$sticky = get_option('sticky_posts');
		if($_POST['page'] == '1' && empty($_POST['cat'])){ //回退到首页显示置顶
			$params['post__not_in'] = $sticky;
			$sticky_html = posts_sticky_loop();
		}
	} else {
		$params['paged'] = $_POST['page'] + 1;
	}
	$params['post_status'] = 'publish';
 

	query_posts( $params );
 
	if( have_posts() ) :
		ob_start(); 
		// run the loop
		while( have_posts() ): the_post();
 			
		post_show_type();
		
		endwhile;
		$posts_html = ob_get_contents(); // we pass the posts to variable
   		ob_end_clean(); // clear the buffer
	endif;

	if(get_op('qiniu_cdn')) {
        $posts_html = ajax_qiniu_cdn_replace($posts_html);
		$sticky_html = ajax_qiniu_cdn_replace($sticky_html);
    }
	echo json_encode( array(
		'content' => $sticky_html.$posts_html,
	) );
    
	exit();  

}	
add_action('wp_ajax_nopriv_blog_mod_load', 'blog_mod_load');
add_action('wp_ajax_blog_mod_load', 'blog_mod_load');



//文章图片灯箱
add_filter('the_content', 'fancybox');
function fancybox ($content){
    global $post;
    $pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>(.*?)<\/a>/i";
    $replacement = '<a$1href=$2$3.$4$5 class="fancybox" data-fancybox="image-'.$post->ID.'"$6>$7</a>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}

//文章展示类型
function post_show_type(){
	$type = get_op('post_list_type');
	switch ($type)
		{
		case 'normal':
		get_template_part( 'tpl/content', get_post_type() );
		break;
		case 'grid':
		get_template_part( 'tpl/content', 'grid' );
		break;
		case 'card':
		get_template_part( 'tpl/content', 'card' );
		break;    
		default:
		get_template_part( 'tpl/content', 'card' );
		}
}

//文章分页
function post_pagenav(){
	global $wp_query;
	$type = get_op('post_pagenav');
	if (  $wp_query->max_num_pages > 1 ){
		if($type == 'more') {
			echo '<div id="pagination"><div class="post-paging"><a> LOAD MORE </a></div></div>'; 
		} else {
			echo '<div id="post_pager"><div class="pager_inner" paged="1"><a class="prev" type="prev" style="display:none"><i class="ri-arrow-left-s-line"></i> 上一页</a><a class="next" type="next">下一页 <i class="ri-arrow-right-s-line"></i></a></div><div class="c_paged">第<span>1</span>页</div></div>';
		}
	}	
}

//社交分享
function pix_poster_share() { //poster normal
	global $post;
	$html = '';
	$post_id = $post->ID;
	$post_title = get_the_title($post_id);
	$url = get_permalink($post_id);
	$img = cst_get_thum( $post_id, 'medium');

	$type = get_post_type($post_id);

	if($type == 'moment') {
		$post_title = !empty($post_title) ? $post_title :  ''.get_bloginfo('name').' - 片刻';
	}

	
	$share_link_weibo = sprintf('https://service.weibo.com/share/share.php?url=%s&type=button&language=zh_cn&pic=%s&title=%s',urlencode($url),$img,$post_title);
	$share_link_qzone = sprintf('https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=%s&title=%s&pics=%s',urlencode($url),$post_title,$img);
	$share_link_qq = sprintf('http://connect.qq.com/widget/shareqq/index.html?url=%s&title=%s&pics=%s',urlencode($url),$post_title,$img);
	$html = '<div class="post_share_box hide">
			<a href="'.$share_link_weibo.'" uk-tooltip="title: 微博分享; pos: top;" target="_blank"><i class="ri-weibo-line"></i></a>
			<a href="'.$share_link_qzone.'" uk-tooltip="title: QQ分享; pos: top;" target="_blank"><i class="ri-chrome-line"></i></a>
			<a href="'.$share_link_qq.'" uk-tooltip="title: QQ好友分享; pos: top;" target="_blank"><i class="ri-qq-line"></i></a>
			<a class="poster_download" uk-tooltip="title: 下载海报; pos: top;"><i class="ri-download-line"></i></a>
		 </div>';
		return  $html;
}

//分享按钮
function share_btn(){
	global $post;
	$post_id = $post->ID;
	$html = '';
	$html = '<div class="pix_share_btn">
				<a class="pix_icon share_btn_icon cr_poster" poster-data="'.$post_id.'" uk-toggle="target: #share_modal_'.$post_id.'" uk-tooltip="title: 片刻分享; pos: top;"><i class="ri-share-forward-box-line"></i></a>
				<div id="share_modal_'.$post_id.'" class="uk-flex-top poster_modal" uk-modal>
					<div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
						<button class="uk-modal-close-outside" type="button" uk-close></button>	
						<div class="poster_box_ap"></div>	
							'.pix_poster_share().'
					</div>
				</div>
			</div>';
		return  $html;
}

//打赏按钮
function donate_btn(){
	//global $post;
	//$post_id = $post->ID;
	$des = get_op('donate_des');
	$pic = get_op('donate_pic');
	$on = get_op('donate_on');
	$html = '';
	$html = '<div class="pix_donate_btn">
				<a class="pix_icon donate_btn_icon" uk-toggle="target: #donate_modal" uk-tooltip="title: 打赏作者; pos: top;"><i class="ri-money-cny-box-line"></i></a>
				<div id="donate_modal" class="uk-flex-top donate_modal" uk-modal>
					<div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
						<button class="uk-modal-close-outside" type="button" uk-close></button>	
						<div class="donate_des">'.$des.'</div>
						<div class="donate_pic"><img src="'.$pic.'"></div>	
					</div>
				</div>
			</div>';
	if($on){
		return  $html;
	}		
}

//编辑文章按钮
function s_edit_post(){
	global $post;
	$post_id = $post->ID;
	$ed_link = get_edit_post_link($post_id);
	if($ed_link){
		return '<span class="edit_post"><a href="'.$ed_link.'" target="_blank"><i class="ri-edit-line"></i>编辑</a></span>';
	}
}