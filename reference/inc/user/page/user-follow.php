<?php
//$collect_list = get_user_meta(get_current_user_id(), 'post_collect', true);
$current_user_id = get_current_user_id();
$curauth = $wp_query->get_queried_object(); // 当前用户页的用户对象
$view_user_id = $curauth->ID;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$type = isset($_GET['type'])? $_GET['type'] : 'follower';
$limits = 12;
$follower_list = follower_list($view_user_id, $paged,$limits);
$following_list = following_list($view_user_id, $paged,$limits);
$follower_count = ppo_get_follower_count($view_user_id);
$following_count = ppo_get_following_count($view_user_id);
?>

<div class="user-index-follow user-index-box pix-user-home-panel pix-user-home-follow-panel" id="user-content">
            <?php
                 if($type == 'follower'){
                    echo $follower_list;
                } else {
                    echo $following_list;
                }
             ?>
 
</div>
