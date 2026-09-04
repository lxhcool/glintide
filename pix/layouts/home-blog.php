<?php
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
					  /*if ( have_posts() ) :

                        while ( have_posts() ) :
                        
                            the_post();
                            
                            post_show_type();
                            
                        endwhile;
                        
                    else :

                        get_template_part( 'tpl/content', 'none' );

                    endif;
                    */
				?>       
					
			
			</div>

			<?php post_pagenav(); ?>

            
    </div>
        </div>   

        
    
