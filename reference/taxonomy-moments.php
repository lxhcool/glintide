<?php
get_header(); 
$nav_h = cls_nav_height();
global $wp_query;
$term_id = get_queried_object()->term_id;
$arr = array('catid' => $term_id);
?>
<div class="pix-content home-box home-classic">

 <?php get_template_part( 'inc/layouts/moment', 'tpl',$arr ); ?>
</div>

<?php
get_footer();    