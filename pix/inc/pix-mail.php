<?php 
/*-----------------------------------------------------------------------------------*/
/* smtp发信
/*-----------------------------------------------------------------------------------*/
if (get_op('site_smtp') == true) {  
add_action('phpmailer_init', 'mail_smtp');
}
function mail_smtp( $phpmailer ) { 
	$phpmailer->FromName = get_op( 'smtp_name' ); 
	$phpmailer->Host = get_op( 'smtp_server' );
	$phpmailer->Port = get_op( 'smtp_port' ); 
	$phpmailer->Username = get_op( 'smtp_email' ); 
	$phpmailer->Password = get_op( 'smtp_password' ); 
	$phpmailer->From = get_op( 'smtp_email' ); 
	$phpmailer->SMTPAuth = true; 
	$phpmailer->SMTPSecure = get_op( 'smtp_ssl' ); //tls or ssl （port=25留空，465为ssl）
	$phpmailer->IsSMTP();
}
function ea_mail_template($content,$parent_id,$comment_id){
	$blogname =  get_bloginfo('name');//站点名称
	$bloghome = get_bloginfo('url');//站点链接
	$logo = get_op('mail_logo');
	if($logo){//站点LOGO
		$head_title = '<img src="'.$logo.'" style="max-width: 60px;">';
	} else {
		$head_title = '<h1 style="display: inline-block;font-size: 16px;font-family: microsoft yahei; padding: 12px 30px;background: #485dff;color: #fff; border-radius: 3px;">'.$blogname.'</h1>';
	}
	
	$comment = get_comment($comment_id);
	$author_name = $comment->comment_author;//评论者昵称
	$post_id = $comment->comment_post_ID;
	$post = get_post($post_id);
	$post_title = $post->post_title;//文章标题
	
	$parent_comment = get_comment($parent_id);
	$parent_name = $parent_comment->comment_author;//接收者昵称
	$parent_content = $parent_comment->comment_content;
	
	$comment_link = htmlspecialchars(get_permalink($post_id));
	
	$html = '<div class="email_warp" style="max-width:540px;width:100%;background: #fdfdff;margin: 0 auto;padding:30px;border: 1px solid #e0e8ff;border-radius: 4px;">
				<div class="mail_head" style="text-align: center;margin-bottom: 30px;"><a href="'.$bloghome.'" target="_blank">'.$head_title.'</a></div>
				<p style="margin:0">Hi, '.$parent_name.' , 您曾在['.$blogname.']的片刻中发表的评论：</p>
				<div class="mail_content" style="background: #ebebff;padding: 20px 20px;margin-bottom: 40px;margin-top: 10px;"><p style="margin:0">'.$parent_content.'</p></div>
				<p style="margin:0">'.$author_name.' 给您的回复如下：</p>
				<div class="mail_content" style="background: #ebebff;padding: 20px 20px;margin-bottom: 40px;margin-top: 10px;"><p style="margin:0">'.$content.'</p></div>
				<p>您可以点击<a style="color: #3b37ca;text-decoration: none;" href="'.$comment_link.'">查看完整回复内容</a></p>
				<div class="mail_footer" style="text-align: center;margin-top: 60px;"><p>Copyright © <a href="'.$bloghome.'" target="_blank" style="color: #3b37ca;text-decoration: none;">'.$blogname.'</a></div>
			</div>';
	return $html;			
}

/* Basic Mail */
function ea_basic_mail($from,$to,$title,$content,$parent_id,$comment_id){
	date_default_timezone_set ('Asia/Shanghai');
	$message = ea_mail_template($content,$parent_id,$comment_id);
	$name = get_bloginfo('name');
	if(empty($from)){$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));}else{$wp_email=$from;}
	$fr = "From: \"" . $name . "\" <$wp_email>";
	$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
	//发送邮件
	wp_mail( $to, $title, $message, $headers );
}

/* 评论回复邮件
/* -------------- */
if(get_op('comments_notify') == true) {
add_action('wp_insert_comment','ea_global_send_mail');
}
function ea_global_send_mail($comment_id){
	//取得插入评论的id
	$comment = get_comment($comment_id);
	//取得评论的父级id
	$comment_parent = $comment->comment_parent;
	//取得评论的内容
	$comment_content = $comment->comment_content;
	//评论者email
	$comment_author_email = $comment->comment_author_email;
	$title = '新评论回复！['.get_bloginfo('name').']';
	$pid = $comment->comment_post_ID; //文章id
	$post = get_post($pid);
	$post_author_id = $post->post_author;
	$post_author_email = get_the_author_meta('user_email',$post_author_id);

//给父级评论发邮件
if($comment_parent !=0) {//如果有父级评论
		$parent = get_comment($comment_parent); //获取父级评论信息
		$to = $parent->comment_author_email;
			
		if($to == $comment_author_email || $to == $post_author_email) {//排除自己给自己评论
			return;
		}
		//加载封装函数	
		ea_basic_mail($comment_author_email,$to,$title,$comment_content,$comment_parent,$comment_id);	
	}
	
}