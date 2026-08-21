<?php
$current_user_id = get_current_user_id();
$curauth = $wp_query->get_queried_object(); // 当前用户页的用户对象
$view_user_id = $curauth->ID;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = defined('PPO_USER_POSTS_PER_PAGE') ? PPO_USER_POSTS_PER_PAGE : 9;

$data = [
    'post_type'      => 'post',
    'author'         => $view_user_id,
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
];

$query = new WP_Query($data);
?>

<div class="user-index-posts user-index-box pix-user-home-panel pix-user-home-posts-panel" id="user-content">
<?php if ($query->have_posts()) : ?>
    <div class="skeleton-inner pix-user-home-posts-grid">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php 
            get_template_part('tpl/content', 'grid'); ?>
        <?php endwhile; ?>
    </div>

    <?php
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            echo ppo_htmx_pager([
                'user_id'     => $view_user_id,
                'total_pages' => $total_pages,
                'current'     => $paged,
                'target'      => '#user-content',
                'query_args'  => ['tab' => 'posts'], // 可传入 tab=posts 等
                'push_url'    => true,
                'push_url_base' => get_author_posts_url($view_user_id), // 比如 /user/123
                'class'       => 'pix-user-home-pagination',
            ]);
        }
    ?>

<?php else : ?>
    <div class="nodata pix-user-home-empty"><img src="<?php echo get_template_directory_uri(); ?>/img/empty.png" alt="暂无数据"></div>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
</div>
