<?php
/*
	Template Name: 文章
*/
get_header(); 
global $wp_query;
$type = get_op('post_list_type');
$grid = '';
$class = '';
if($type == 'grid'){
    $grid = 'uk-grid';
    $class = 'uk-grid-small';
}
?>

<div class="normal_list">
    <div class="normal_list_inner" uk-height-viewport="offset-top: true">

        <?php echo get_posts_cat(); ?>

        <div id="post_item" class="norpost_list <?php echo $class ?>" <?php echo $grid ?>>

				<?php 
				

					$args = array(
						'post_type' => 'post', 
						//'offset' => 10,
						//'paged' => 5
						);
					$my_query = new WP_Query($args);
					if( $my_query->have_posts() ) {
						while ($my_query->have_posts()) : $my_query->the_post();
						post_show_type();
					endwhile; wp_reset_postdata(); }
				?>       
					
			
			</div>

			<?php 
				$type = get_op('post_pagenav');
				if (  $my_query->max_num_pages > 1 ){
					if($type == 'more') {
						echo '<div id="pagination"><div class="post-paging"><a> LOAD MORE </a></div></div>'; 
					} else {
						echo '<div id="post_pager"><div class="pager_inner" paged="1"><a class="prev" type="prev" style="display:none"><i class="ri-arrow-left-s-line"></i> 上一页</a><a class="next" type="next">下一页 <i class="ri-arrow-right-s-line"></i></a></div><div class="c_paged">第<span>1</span>页</div></div>';
					}
				}	
			 ?>

            
    </div>
        </div>   

    
<?php
get_footer();    
