<?php
// 消息台页面
if (!is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

$pix_message_body_class = function($classes) {
    $classes[] = 'pix-message-viewport-page';
    return $classes;
};
add_filter('body_class', $pix_message_body_class);

get_header();
$type = get_query_var('msg_action') ? get_query_var('msg_action') : 'whisper';
?>

<div class="pix-content home-box home-classic pix-modern pix-modern-dashboard pix-dashboard-message-page">
    <div class="user-center pix-dashboard-message-shell">
        <div class="user-left pix-dashboard-message-sidebar">
            <div class="user-left-nav pix-dashboard-message-nav">
                <?php echo msg_center_nav($type); ?>
            </div>
        </div>

        <div class="user-right pix-dashboard-message-main">
            <div class="user-index pix-dashboard-message-content-wrap">
                <?php get_template_part( 'inc/layouts/msg', $type ); ?>
            </div>
        </div>
    </div>
</div>


<?php
get_footer();   
?>

<script type="text/javascript">
  $(document).ready(function(){

    var type = '<?php echo $type;?>';

    if(type == 'like' || type == 'reply'){

    switch (type) {
        case "like":
            action = 'upadte_like_msg_read';
            break;
    
            case "reply":
            action = 'upadte_comment_msg_read';
            break;
    }
        
        $.ajax({
            type: 'POST',
            url: Theme.ajaxurl,
            data: {
                action: action,
                nonce: Theme.msg_nonce,
            },
			beforeSend: function() {
                
            },
            success: function(response) {
                
            },
            error: function() {
               
            }
        });
        			
        };
    }); 

</script>
