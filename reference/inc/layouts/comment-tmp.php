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
<div class="moment-comments comments comments-area pix-moment-comments" data="moment">

	<div class="comments-title pix-moment-comments-title">
		<i class="ri-discuss-line"></i>讨论 | <span class="noticom pix-moment-comments-count"><?php comments_popup_link('暂无评论', '1 评论', '% 评论'); ?> </span>
	</div>

	<div class="toi_comments_main pix-moment-comments-main">
		<div class="toi_respond_<?php echo $post_id; ?> respond_box pix-moment-comments-respond"></div>
	</div>

</div>
