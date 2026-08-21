<?php
/**
 * The template for displaying moment archive pages.
 *
 * Keeps the configured moment slug archive aligned with the moment homepage UI.
 *
 * @package pix
 */

get_header();

$arr = array('catid' => 0);
?>

<div class="pix-content home-box home-classic">
    <?php get_template_part('inc/layouts/moment', 'tpl', $arr); ?>
</div>

<?php
get_footer();
