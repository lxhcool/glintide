<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = '_ppo_taxonomy_options';

//
// Create taxonomy options
//
CSF::createTaxonomyOptions( $prefix, array(
  'taxonomy' => 'category',
) );

$pix_taxonomy_options = get_option('ppo_options');
$cash = (is_array($pix_taxonomy_options) && !empty($pix_taxonomy_options['cash_icon'])) ? $pix_taxonomy_options['cash_icon'] : '¥';
//
// Create a section
//
CSF::createSection( $prefix, array(
  'fields' => array(

    //
    // A text field
    //
    array(
      'id'    => 'opt-text',
      'type'  => 'text',
      'title' => 'Text',
    ),

    array(
      'id'    => 'opt-textarea',
      'type'  => 'textarea',
      'title' => 'Textarea',
      'help'  => 'The help text of the field.',
    ),

    array(
      'id'    => 'opt-upload',
      'type'  => 'upload',
      'title' => 'Upload',
    ),

    array(
      'id'    => 'opt-switcher',
      'type'  => 'switcher',
      'title' => 'Switcher',
      'label' => 'The label text of the switcher.',
    ),

    array(
      'id'      => 'opt-color',
      'type'    => 'color',
      'title'   => 'Color',
      'default' => '#3498db',
    ),

    array(
      'id'    => 'opt-checkbox',
      'type'  => 'checkbox',
      'title' => 'Checkbox',
      'label' => 'The label text of the checkbox.',
    ),

    array(
      'id'      => 'opt-radio',
      'type'    => 'radio',
      'title'   => 'Radio',
      'options' => array(
        'yes'   => 'Yes, Please.',
        'no'    => 'No, Thank you.',
      ),
      'default' => 'yes',
    ),

    array(
      'id'          => 'opt-select',
      'type'        => 'select',
      'title'       => 'Select',
      'placeholder' => 'Select an option',
      'options'     => array(
        'opt-1'     => 'Option 1',
        'opt-2'     => 'Option 2',
        'opt-3'     => 'Option 3',
      ),
    ),

  )
) );

//片刻分类设置
$moments_cat = '_ppo_moments_options';

CSF::createTaxonomyOptions( $moments_cat, array(
  'taxonomy' => 'moments',
  'class'      => 'pix-options tax-content',
  'data_type'  => 'unserialize',
) );

CSF::createSection( $moments_cat, array(
  'title' => '片刻圈子设置',
  'fields' => array(

    array(
      'id'      => 'seo_title',
      'type'    => 'text',
      'title'   => 'SEO标题',
    ),

    array(
      'id'      => 'seo_keywords',
      'type'    => 'text',
      'title'   => 'SEO关键词',
    ),

    array(
      'id'         => 'ppo_moments_tag',
      'type'       => 'radio',
      'title'      => '圈子所属标签',
      'inline'       => true,
      'options'    => 'get_moments_tag_arr',
    ),

    array(
      'id'           => 'mo_cat_img',
      'type'         => 'upload',
      'title'        => '圈子图标',
      'library'      => 'image',
      'placeholder'  => 'http(s)://',
      'button_title' => '添加图标',
      'remove_title' => '移除图标',
      'preview'       => true,
    ),

    array(
      'id'           => 'mo_cat_banner',
      'type'         => 'upload',
      'title'        => '圈子封面',
      'library'      => 'image',
      'placeholder'  => 'http(s)://',
      'button_title' => '添加封面',
      'remove_title' => '移除封面',
      'preview'       => true,
    ),

    array(
      'id'          => 'mo_owner',
      'type'        => 'select',
      'title'       => '圈主',
      'placeholder' => '设置圈主',
      'chosen'      => true,
      'ajax'        => true,
      'options'     => 'users',
      'default'     => 1,
      'desc'        => '默认为管理员'
    ),

    // 圈子类型
    array(
      'id'         => 'mo_join_type',
      'type'       => 'radio',
      'title'      => '圈子加入方式',
      'inline'       => true,
      'options'    => array(
        '0' => '免费',
        '1' => '付费',
        '2' => '权限',
      ),
      'default' => '0'
    ),

    array(
      'id'     => 'mo_join_pay',
      'type'   => 'fieldset',
      'title'  => '付费圈子配置',
      'fields' => array(
        array(
          'id'    => 'mp',
          'type'  => 'text',
          'title' => '月付金额',
          'before' => $cash,
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'qp',
          'type'  => 'text',
          'title' => '季付金额',
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'hp',
          'type'  => 'text',
          'title' => '半年金额',
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'op',
          'type'  => 'text',
          'title' => '年付金额',
          'class' => 'mini-input'
        ),

        array(
          'id'    => 'fp',
          'type'  => 'text',
          'title' => '永久金额',
          'class' => 'mini-input'
        ),
      
      ),
      'dependency' => array( 'mo_join_type', '==', '1' ),
    ),

    array(
      'id'         => 'mo_join_limits',
      'type'       => 'checkbox',
      'title'      => '圈子权限配置',
      'inline'       => true,
      'options'    => 'all_lv_merge',
      'check_all' => true,
      'check_all_text' => '全选/取消全选',
      'dependency' => array( 'mo_join_type', '==', '2' ),
    ),


  )
) );

