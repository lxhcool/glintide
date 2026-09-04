<div class="blog_list">
    <div class="blog_list_inner" uk-height-viewport="offset-top: true">

        <?php echo get_moment_cat(); ?>

        <div id="post_item" class="moment_list">

				<?php 
				
					$args = array(
						'post_type' => 'moment', 
						//'offset' => 10,
						//'paged' => 5
						);
					$my_query = new WP_Query($args);
					/*
					if( $my_query->have_posts() ) {
						while ($my_query->have_posts()) : $my_query->the_post();
						get_template_part( 'tpl/content','moment');
					endwhile; wp_reset_postdata(); }
					*/
				?>       
					
			
			</div>

			<?php if (  $my_query->max_num_pages > 1 )
				echo '<div id="t_pagination"><div class="post-paging"><a> LOAD MORE </a></div></div>'; 
			?>

            <div id="comment_form_reset"><?php get_template_part( 'layouts/topic_form'); ?></div>
    </div>
        </div>   

        
    
