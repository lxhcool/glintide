<?php
$user_id = get_current_user_id();
$list = ppo_msg_get_likes_and_favorites($user_id, $paged = 1, $per_page = 10);
//$asread = mark_like_collect_as_read($user_id);
?>
<div class="msg-right-content pix-dashboard-message-panel pix-dashboard-message-like-panel">
    <div class="title pix-dashboard-message-heading">点赞收藏</div>
    <div class="like-msg-box msg-box-append pix-dashboard-message-list pix-dashboard-message-like-list pix-dashboard-list" action="load_like_msg">
       <?php echo get_msg_like_list(1, 9);?>
    </div>

    <div class="msg-load-more pix-dashboard-message-load-more"></div>
</div>

<?php //wp_footer(); ?>
