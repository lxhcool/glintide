
<?php
$meta = get_post_meta( get_the_ID(), '_pix_posts_options', true );
$mu = isset($meta['mu_on']) ? $meta['mu_on'] : false;
$playid = isset($meta['plays_id']) ? $meta['plays_id'] : '';
$lazy = THEME_URL .'/img/lazyload.png';
?>
<div id="post-<?php the_ID(); ?> post-single" <?php post_class('post-single'); ?>>

    <div class="blog_header">
    
    <?php if($mu && $playid){ ?>

        <div class="single_music_header">
            <div class="mu_img"><img class="round12 shadow lazy" src="<?php echo $lazy  ?>" data-src="<?php echo cst_get_thum( get_the_ID(), 'large'); ?>" alt=""></div>
            <div class="right_info">
                <?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>  
                <div class="header_meta">
                    <span class="single_time">
                        <i class="ri-time-line"></i>
                        <time itemprop="datePublished" datetime="<?php echo get_the_date('c');?>">  <?php echo ''.timeago( get_gmt_from_date(get_the_time('Y-m-d G:i:s')) ); ?></time>
                    </span>
                    <span class="post_views"><i class="ri-eye-line"></i><?php get_post_views($post -> ID); ?></span>
                    <?php echo s_edit_post(); ?>
                </div>
                
                <div class="single-content_header">
                    <span class="single_cat"><?php the_category(); ?> </span>
                </div>

                <div class="mu_des"><?php echo isset($meta['mu_des']) ? $meta['mu_des'] : '' ?></div>
            </div>
        </div>

    <?php } else {  ?>    
        
        <div class="post_header">
            <?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
            <ul>
                <div class="header_meta">
                    <span class="single_time">
                        <i class="ri-time-line"></i>
                        <time itemprop="datePublished" datetime="<?php echo get_the_date('c');?>">  <?php echo ''.timeago( get_gmt_from_date(get_the_time('Y-m-d G:i:s')) ); ?></time>
                    </span>
                    <span class="post_views"><i class="ri-eye-line"></i><?php get_post_views($post -> ID); ?></span>
                    <?php echo s_edit_post(); ?>
                </div>
                
                <div class="single-content_header">
                    <span class="single_cat"><?php the_category(); ?> </span>
                </div>
            </ul>

        </div>

    <?php } ?>

    </div>


		<div class="single-inner">
			
        <?php 
            if($mu && $playid){
                echo '<div class="mu_l_title"><i class="ri-play-list-line"></i>歌曲列表</div><div class="posts_mu_list" pid="'.get_the_ID().'"></div>';           
            }
        ?>

			<div class="single-content">
				
				<?php the_content(); ?>

			</div><!-- .entry-content -->
	
            <div class="single-footer">
                <div class="single_footer_box"> 
                    <?php echo get_like_btn(),share_btn(),donate_btn(); ?>
                </div>
            </div>

		</div>


</div><!-- #post-<?php the_ID(); ?> -->