<?php
/**
 * PIX经典首页
 *
 */

$nav_h = cls_nav_height();
$type = get_cu('cls_home_type','blog');

get_template_part( 'inc/layouts/classic', $type ); 