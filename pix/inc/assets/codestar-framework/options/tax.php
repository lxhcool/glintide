<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = '_pix_taxonomy_options';

//
// Create taxonomy options
//
CSF::createTaxonomyOptions( $prefix, array(
		'taxonomy'  => array('post_tag', 'category'),
    'data_type' => 'unserialize',
) );

//
// Create a section
//
CSF::createSection( $prefix, array(
  'fields' => array(

    array(
      'id'           => 'cat_img',
      'type'         => 'upload',
      'title'        => '分类特色图',
      'library'      => 'image',
      'button_title' => '上传图像',
      'remove_title' => '移除图像',
      'default'		 => THEME_URL .'/img/banner.jpg',
    ),

  )
) );
