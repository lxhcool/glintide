<?php
$user_id = get_current_user_id();
//mark_all_comment_msg_unread($user_id);
?>

<div class="msg-right-content pix-dashboard-message-panel pix-dashboard-message-reply-panel">
    <div class="title pix-dashboard-message-heading">回复我的</div>
    <div class="reply-msg-box msg-box-append pix-dashboard-message-list pix-dashboard-message-reply-list pix-dashboard-list" action="load_comment_msg">
        <?php echo get_msg_reply_list($user_id = null ,$paged = 1, 9); ?>
    </div>

    <div class="msg-load-more pix-dashboard-message-load-more"></div>

    <div id="comment_form_tmp"><?php get_template_part( 'inc/layouts/moment-comment'); ?></div>
</div>
