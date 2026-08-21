<?php
/*
	Template Name: 片刻
*/
get_header(); 
$nav_h = cls_nav_height();
$arr = array('catid' => 0);
?>
<div class="pix-content home-box home-classic">

    <?php get_template_part( 'inc/layouts/moment', 'tpl',$arr ); ?>
    
</div>

<?php
get_footer();    