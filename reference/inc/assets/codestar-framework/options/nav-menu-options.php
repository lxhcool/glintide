<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = '_ppo_menu_options';

//
// Create menu options
//
CSF::createNavMenuOptions( $prefix, array(
  'data_type' => 'serialize'
) );

CSF::createSection( $prefix, array(
  'fields' => array(

    array(
      'id'      => 'nav_icon',
      'type'    => 'icon',
      'title'   => '菜单字体图标',
      'default' => 'ri-home-line'
    ),

    array(
      'id'           => 'nav_img',
      'type'         => 'upload',
      'title'        => '菜单icon图片',
      'library'      => 'image',
      'button_title' => '添加图片',
      'remove_title' => '移除图片',
      'desc'      => '建议制作一张18x18的png图像',
    ),

    

  )
) );
