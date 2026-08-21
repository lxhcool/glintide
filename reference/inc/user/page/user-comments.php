<?php
$current_user_id = get_current_user_id();
$curauth = $wp_query->get_queried_object(); // 当前用户页的用户对象
$view_user_id = $curauth->ID;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limits = 8;

?>

<div class="user-index-comments user-index-box pix-user-home-panel pix-user-home-comments-panel" id="user-content">
  <?php echo ppo_render_user_comments($view_user_id, $paged, $limits); ?>
</div>
