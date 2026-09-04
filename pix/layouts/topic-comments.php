<?php
//topic评论模板
$post_id = get_the_ID();
$current_user = wp_get_current_user();
$user_id=$current_user->ID;
$comment_sign = get_option('comment_registration') && !is_user_logged_in();
$number = get_option('comments_per_page');
$comments = get_comments('post_id='.$post_id);
$approve = get_comment_pages_count($comments);
$pages = ceil($approve/$number);//总页数
$page = (get_query_var('paged')) ? get_query_var('paged') : 1;
$offset = ( ($page -1) * $number);
?>
<div class="topic_comments comments comments-area" data="moment">

	<div class="comments-title">
		<i class="ri-discuss-line"></i>Comments | <span class="noticom"><?php comments_popup_link('NOTHING', '1 评论', '% 评论'); ?> </span>
	</div>

	<div class="toi_comments_main">		
		<div class="toi_respond_<?php echo $post_id; ?> respond_box"></div>
	</div>

</div>