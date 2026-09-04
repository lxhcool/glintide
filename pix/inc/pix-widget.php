<?php
//小工具
function adminsocial_widget($arr,$title){
    $html = '';
    if($arr){
        foreach($arr as $list){
            $name = $list['ad_title'];
            $icon = $list['ad_icon'];
            $img = $list['ad_img'];
            $new = $list['ad_open_new'];
            $open = $new ? 'target="_blank"' : '';
            $tips = $name ? 'uk-tooltip="'.$name.'"' : '';
            $bg = $list['s_btn_bg'];
            $type = isset($list['ad_show_type']) ? $list['ad_show_type'] : 'link';
            $qrcode = $list['ad_qrcode'] ? $list['ad_qrcode'] : '';
            if($icon) {
                $s_icon = '<i class="'.$icon.'"></i>';
            } else {
                $s_icon = '<img src="'.$img.'">';
            }

            $link = $type == 'link' ? $list['ad_link'] : '';
            $qrcode_box = $type == 'qrcode' ? '<div class="sw_qrcode"><img src="'.$qrcode.'"></div>' : '';

            $html .= '<div class="sw_item"><a href="'.$link.'" class="sw_social" '.$open.' '.$tips.' style="background:'.$bg.'">'.$s_icon.'</a>'.$qrcode_box.'</div>';
        }
    }
    

    echo '<div class="wid_title">'.$title.'</div><div class="items wid_sw_social">'.$html.'</div>';
}

function admininfo_widget($arr, $title){
    $html = '';
    if($arr){
    foreach($arr as $list){
        $name = $list['ba_title'];
        $icon = $list['ba_icon'];
        $img = $list['ba_img'];
        $des = $list['ba_des'];
        $link = $list['ba_link'];
        $new = $list['ba_open_new'];
        $open = $new ? 'target="_blank"' : '';
        $tips = $name ? 'uk-tooltip="title:'.$name.';pos: right"' : '';
        if($icon) {
            $s_icon = '<i class="'.$icon.'"></i>';
        } else {
            $s_icon = '<img src="'.$img.'">';
        }

        if($link){
            $s_des = '<a href="'.$link.'" '.$open.'>'.$des.'</a>';
        } else {
            $s_des = '<p>'.$des.'</p>';
        }

        $html .= '<div class="ad_info"><div class="name">'.$s_icon.'</div>
                <div class="meta" '.$tips.'>'.$s_des.'</div></div>';
    }
}

    echo '<div class="wid_title">'.$title.'</div><div class="items wid_ad_info">'.$html.'</div>';
}

//最近文章
function pix_posts_show_widget($posts_number,$show_style,$show_order,$title){
	global $posts,$post;
    $output = '';
	//排序
	if($show_order == 'date'){
		$order = 'date';
	} else if($show_order == 'views') {
		$order = 'meta_value_num';
	} else if($show_order == 'rand') {
		$order = 'rand';
	} else {
		$order = 'comment_count';
	}
	$args = array(
	    'posts_per_page' => $posts_number,
	    'orderby' => $order,
		'meta_key' => 'views',		
	);
	$output = '<div class="wid_title">'.$title.'</div><ul class="items posts_show '.$show_style.'">';
	$postslist = get_posts( $args );
	if (isset($postslist) && is_array($postslist)) {
		foreach ($postslist as $post) : setup_postdata( $post );

			$output .= '<li class="item">';
			$output .= '<div class="image"><a href="'.get_permalink().'"><img class="round8" data-src="'.cst_get_thum( get_the_ID(), 'large' ,true ).'" width="50" height="50" alt="" uk-img ></a></div>';
			$output .= '<div class="info nowrap">';
			$output .= '<h4 class="title nowrap"><a href="'. get_permalink() .'">'. get_the_title() .'</a></h4>';
			$output .= '<div class="meta">'. get_comments_number() .' REPLIES  ， '.$post->views.' VIEWS</div>';
			$output .= '</div>';
			$output .= '</li>';
		endforeach; wp_reset_postdata();
	} else { return false; }
	$output .= '</ul>';
	echo $output;
	
}

//专题展示
function cst_cats_show_widget($cat,$title) {
    $output = '';
	$arry = array(
		'taxonomy' => 'category',
		'include' => $cat,
	);
	
	$lists = get_terms($arry);
	
	$output = '<div class="wid_title">'.$title.'</div><ul class="items cats_show">';
    if(isset($lists) && is_array($lists)){
        foreach ( $lists as $list ) {
            $cat_img = get_term_meta( $list->term_id, 'cat_img', true );
            $output .= '<li><a href="'. get_term_link($list->term_id).'">';
            $output .= '<div class="cat_img"><img class="round8" data-src="'. $cat_img .'" width="50" height="50" alt="" uk-img ></div>';
            $output .= '<div class="cat_name"><h1>'. $list->name .'</h1></div>';
            $output .= '</a></li>';			  
        }
    }
	$output .= '</ul>';
	echo $output; 
}

//最近评论
function cst_widget_com($number,$type,$title){
    $html = '';
	$arr = array(
		'number' => $number,
		'post_type' => $type,
        'orderby' => 'comment_date',
        'order' => 'DESC',
	);

	$lists = get_comments($arr);
    if(isset($lists) && is_array($lists)){
	foreach($lists as $list){
		$user_id = $list->user_id;
		
        $name = $list->comment_author;
        $link = $list->comment_author_url; 
        $avatar = get_avatar($list->comment_author_email, 100);
		
		$body = $list->comment_content;
		$time = timeago(get_gmt_from_date($list->comment_date));

		$html .= '<li class="wid_comment_item">
					<div class="left"><a href="'.$link.'">'.$avatar.'</a></div>
					<div class="right">
						<a href="'.$link.'" class="name">'.$name.'</a>
						<div class="body">'.$body.'</div>
						<div class="meta">'.$time.'</div>
					</div>
				</li>';
	}
}

	echo '<div class="wid_title">'.$title.'</div><ul class="items wid_comment">'.$html.'</ul>';
}

//站点统计
function pix_site_tongji($data,$title,$build_date){
    global $wpdb;
    $output = '';
    $count_posts = wp_count_posts();
    $count_moments = wp_count_posts('moment');
    $build_date = isset($build_date) ? $build_date : '1992-10-12';

    $posts_count = '<span>文章</span><small>'.$count_posts->publish.'</small>';
    $moments_count = '<span>片刻</span><small>'.$count_moments->publish.'</small>';
    $tags_count = '<span>标签</span><small>'.wp_count_terms('post_tag').'</small>';
    $comments_count = '<span>留言</span><small>'.$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments where user_id!='1'").'</small>';
    $cat_count = '<span>分类</span><small>'.wp_count_terms('category').'</small>';
    $links_count = '<span>邻居</span><small>'. $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->links WHERE link_visible = 'Y'").'</small>';
    $site_day = '<span>运营</span><small>'.floor((time()-strtotime($build_date))/86400).'天</small>';

    if(isset($data) && is_array($data)){
        foreach($data as $list) {
            
            $output .= '<li class="wid_tongji_item">'.$$list.'</li>';
        }
    }

    echo '<div class="wid_title">'.$title.'</div><ul class="items wid_tongji">'.$output.'</ul>';

}

//一言
function pix_site_yiyan($yiyan_bg,$title){
    $output = '';
    date_default_timezone_set("Asia/Shanghai");
    $yiyan = yiyan_api();
    $ym = date("Y/m");
    $d = date("d");
    $output = '<div class="yiyan_box">
                <img class="lazy" data-src="'.$yiyan_bg.'">
                <div class="yiyan_info">
                    <div class="time"><div class="left"><span class="day">'.$d.'</span><span class="ym">'.$ym.'</span></div><a class="change"><i class="ri-refresh-line"></i></a></div>
                    <p uk-tooltip="'.$yiyan['from'].'">'.$yiyan['hitokoto'].'</p>
                </div>
            </div>';
    echo '<div class="wid_title">'.$title.'</div><ul class="items wid_yiyan">'.$output.'</ul>';
}

function yiyan_api(){
    $url = 'https://v1.hitokoto.cn/';
    $response = wp_remote_get( $url );
    if ( is_array( $response ) && !is_wp_error($response) && $response['response']['code'] == '200' ) {
        $header = $response['headers']; // array of http header lines
        $body = $response['body']; // use the content
    }
    $data = json_decode(($body), true);
    return $data;
    exit();
}


