<?php
$key = get_search_query();
$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
$number = 12;
$count_posts = wp_count_posts();
$published_posts = $count_posts->publish;
$pages = ceil($published_posts/$number);
$offset = ($paged-1)*$number;
$arr = array(
    'post_type' => 'moment',
    's' => $key,
    'post_status' => 'publish',
    'posts_per_page' => 12,
    'offset' => $offset,
);
$type = 'post';

$lists = new WP_Query($arr);
echo '<div class="s_main" uk-height-viewport="offset-top: true">
    <div class="s_tips">片刻 | 关键词为<span>'.$key.'</span>的搜索结果为：</div>
     <div class="se_inner">';
if ( $lists->have_posts() ) {

    while ( $lists->have_posts() ) {
        $lists->the_post();
        get_template_part( 'tpl/moment', 'search');
    }
} else { echo '</div>';
     get_template_part( 'tpl/content', 'none' );
}       

wp_reset_postdata();

echo '</div>';