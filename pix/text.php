<?php
/*
	Template Name: test
*/
get_header(); ?>

<?php
$res = yiyan_api();
print_r($res['hitokoto']);
get_footer();    

