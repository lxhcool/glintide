<?php
//创建评论回复数据表
function pix_reply(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'pix_com_msg';   
    if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) :   
		$sql = " CREATE TABLE `$table_name` (
			`ID` int NOT NULL AUTO_INCREMENT, 
			PRIMARY KEY(ID),
			`r_comid` int,
			`r_email` text,
			`r_parent` int,
			`state` int
		) ENGINE = MyISAM CHARSET=utf8;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');   
			dbDelta($sql);   
    endif;
}
add_action( 'admin_menu', 'pix_reply' );

//根据文章ID获取作者邮箱
function pix_get_author_email($pid){
	$post = get_post($pid);
	$author = $post->post_author;
	$email = get_the_author_meta('user_email',$author);
	return $email;
}

//发布评论插入数据库
add_action('wp_insert_comment','insert_comment_msg');
function insert_comment_msg($comment_id){
	$comment = get_comment($comment_id);
	$pid = $comment->comment_post_ID;
	$parent = $comment->comment_parent;
	if($parent != 0){
		$parent_data = get_comment($parent); //获取父级评论信息
		$email = $parent_data->comment_author_email;
	} else {
		$email = pix_get_author_email($pid);
	}

	$res = create_comment_msg($comment_id, $email, $parent);
}

function create_comment_msg($comment_id, $email, $parent){
	global $wpdb;
	$table_name = $wpdb->prefix . 'pix_com_msg';
	$params = array(
		"r_comid" => $comment_id, 
		"r_email"=> $email, 
		"r_parent" => $parent,
		"state" => 0, //0 未读 1 已读
		);
		
		
	$insert = $wpdb->insert($table_name, $params, array("%d", "%s", "%d", "%d"));
	return $insert ? true : false;
}

//获取未读消息id
function get_unread_msg_id($email,$number){
	global $wpdb;
	$table_name = $wpdb->prefix . 'pix_com_msg';
	$data = $wpdb->get_results($wpdb->prepare("SELECT r_comid FROM $table_name WHERE r_email = %s AND state = 0 ORDER BY ID DESC LIMIT %d" , $email , $number));
	return $data;
}

//获取已读消息id
function get_read_msg_id($email,$number){
	global $wpdb;
	$table_name = $wpdb->prefix . 'pix_com_msg';
	$data = $wpdb->get_results($wpdb->prepare("SELECT r_comid FROM $table_name WHERE r_email = %s AND state = 1 ORDER BY ID DESC LIMIT %d" , $email , $number));
	return $data;
}

//获取游客邮箱
function get_visitor_email(){
	global $current_user;
    $user_id = $current_user->ID;
    $user = get_userdata( $user_id );
	$email = '';
	if($user_id){
        $email = $user->user_email;
    } else {
        $email = @$_COOKIE["comment_author_email_" . COOKIEHASH];
    }

	return $email;
}

//删除消息
function delete_pix_msg($comment_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'pix_com_msg';
	$res = $wpdb->get_var($wpdb->prepare("DELETE FROM $table_name where r_comid = %d;" , $comment_id));
    return $res;	
}

add_action('comment_unapproved_to_spam', 'remove_spam_msg');
add_action('comment_approved_to_spam', 'remove_spam_msg');
function remove_spam_msg($comment){
	$comment_id = $comment->comment_ID;
	$res = delete_pix_msg($comment_id);
}


//未读消息列表
function get_msg_list($type){
	global $current_user;
    $user_id = $current_user->ID;
    $user = get_userdata( $user_id );
    $email = '';
	$html = '';
	$lists  = '';
	$number = '10';
    if($user_id){
        $email = $user->user_email;
    } else {
        $email = @$_COOKIE["comment_author_email_" . COOKIEHASH];
    } 

	//$nomsg = $email ? '<p class="no_posts"><small># 暂无消息 #</small><img class="s_nodata" src="'.THEME_URL.'/img/nodata.png"></p>' : '<div class="msg_log"># 您需要首次评论以获取消息 #</div>';

	if($type == 'read'){
		$lists = get_read_msg_id($email,$number);
	} else if($type == 'unread'){
		$lists = get_unread_msg_id($email,$number);
	}

	if(isset($email)){
		if(isset($lists)){
			foreach($lists as $list ){
				$comment = get_comment($list->r_comid);
				if(!empty($comment) && $comment->comment_approved !== 'spam'){
				    $content = $comment->comment_content;
    				$date = $comment->comment_date;
    				$name =  $comment->comment_author;
    				$re_email = $comment->comment_author_email;
    				$pid = $comment->comment_post_ID;
    				$parent = $comment->comment_parent;
				
				if($parent !=0){
					$parent_comment = get_comment($parent);
					$target_content = '<div class="target_comment">引用评论:<span>'.$parent_comment->comment_content.'</span></div>';
				} else {
					$target_content = '';
				}
				
		
				$html .= '<div class="vi_reply_item">
							'.$target_content.'
							<div class="box">
								<div class="avatar_top">'.get_avatar($re_email,'50').'</div>
								<div class="reply">
								<div class="right"><div class="name"><span>'.$name.'</span>回复道：</div><div class="meta"><time>'.timeago(get_gmt_from_date($date)).'</time><a href="'.get_permalink($pid).'">查看</a></div></div>
									<div class="content">'.$content.'</div>
								</div>
							</div>
							</div>';
				}			
			}
		}
	} else {
		$html = '<div class="msg_log"># 您需要首次评论以获取消息 #</div>';
	}
	
	
	return $html;
}

//获取游客未读消息数
function get_unread_number(){
	global $wpdb;
	$email = get_visitor_email();
	$table_name = $wpdb->prefix . 'pix_com_msg';
    $number = $wpdb->get_var("SELECT COUNT(*) FROM $table_name where r_email = '$email' AND state = 0");
    return $number;	
}

//更新未读消息
function update_unread_msg(){
	$email = get_visitor_email();
	global $wpdb;
	$table_name = $wpdb->prefix . 'pix_com_msg';
	$wpdb->query( "UPDATE $table_name SET state = 1 WHERE  r_email = '$email';" );
}

//ajax已读消息
add_action('wp_ajax_nopriv_up_unread_msg', 'up_unread_msg');
add_action('wp_ajax_up_unread_msg', 'up_unread_msg');
function up_unread_msg(){
	$email = get_visitor_email();
	if(!empty($email)){
		update_unread_msg();
		$msg = array('email' => $email , 'code' => '0');
	} else {
		$msg = array('email' => '' , 'code' => '1');
	}

	echo json_encode($msg);
	exit();
}


