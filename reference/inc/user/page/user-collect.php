<?php
$current_user_id = get_current_user_id();
$curauth = $wp_query->get_queried_object(); // 当前用户页的用户对象
$view_user_id = $curauth->ID;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limits = defined('PPO_USER_POSTS_PER_PAGE') ? PPO_USER_POSTS_PER_PAGE : 9;
?>

<div class="user-collect-page pix-user-home-collect-page">
    <div class="collect-content user-index-comments user-index-box pix-user-home-panel pix-user-home-collect-panel" id="user-content">
         <?php echo ppo_render_user_collect($view_user_id, $paged, $limits); ?>
    </div>  
</div>
