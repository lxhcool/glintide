<?php
$current_user_id = get_current_user_id();
$curauth = $wp_query->get_queried_object(); // 当前用户页的用户对象
$view_user_id = $curauth->ID;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limits = 20;

?>

<div class="user-index-moment user-index-box pix-user-home-panel pix-user-home-moment-panel pix-modern-moment" id="user-content">
  <?php echo ppo_render_user_moment_html($view_user_id, $paged, $limits); ?>
</div>

<div id="comment_form_tmp"><?php get_template_part( 'inc/layouts/moment-comment'); ?></div>
