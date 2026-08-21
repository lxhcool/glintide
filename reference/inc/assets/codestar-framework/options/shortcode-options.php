<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = 'ppo_shortcodes';

//
// Create a shortcoder
//
CSF::createShortcoder( $prefix, array(
  'button_title'   => '添加短代码',
  'select_title'   => '选择短代码',
  'insert_title'   => '插入短代码',
  'show_in_editor' => true,
  'gutenberg'      => array(
   'title'        => 'CSF Shortcodes',
    'description'  => 'CSF Shortcode Block',
     'icon'         => 'screenoptions',
     'category'     => 'widgets',
     'keywords'     => array( 'shortcode', 'csf', 'insert' ),
     'placeholder'  => 'Write shortcode here...',
   )
) );

//
// A shortcode [foo title=""]
//
CSF::createSection( $prefix, array(
  'title'     => '[foo] view: normal',
  'view'      => 'normal',
  'shortcode' => 'foo',
  'fields'    => array(

    array(
      'id'    => 'opt_title',
      'type'  => 'text',
      'title' => 'Title',
    ),

    array(
      'id'    => 'opt_switcher',
      'type'  => 'switcher',
      'title' => 'Switcher',
      'label' => 'The label text of the switcher.',
    ),

  )
) );

//
// A shortcode [foo title=""]content[/foo]
//
CSF::createSection( $prefix, array(
  'title'     => 'PIX按钮',
  'view'      => 'normal',
  'shortcode' => 'pixbtn',
  'fields'    => array(

    array(
      'id'    => 'btn_id',
      'type'  => 'text',
      'title' => '按钮唯一标识',
      'subtitle' => '输入后台设置的按钮组唯一ID'
    ),

    array(
      'id'    => 'btn_title',
      'type'  => 'text',
      'title' => '按钮文本',
      'subtitle' => '按钮显示的文本'
    ),

    array(
      'id'    => 'btn_url',
      'type'  => 'text',
      'title' => '按钮链接',
    ),

    array(
      'id'      => 'btn_icon',
      'type'    => 'icon',
      'title'   => '图标',
      'default' => 'ri-arrow-right-line',
      'subtitle' => '移除则不显示'
    ),

    

  )
) );

//
// A shortcode [content]content[/content][content]content[/content]
//
CSF::createSection( $prefix, array(
  'title'     => '[foo] view: contents',
  'view'      => 'contents',
  'shortcode' => 'content',
  'fields'    => array(

    array(
      'id'    => 'opt_content_1',
      'type'  => 'textarea',
      'title' => 'Content 1',
    ),

    array(
      'id'    => 'opt_content_2',
      'type'  => 'textarea',
      'title' => 'Content 2',
    ),

  )
) );

//
// A shortcode [opt_content_1]content[/opt_content_1][opt_content_2]content[/opt_content_2]
//
CSF::createSection( $prefix, array(
  'title'  => '[foo] view: contents alternative',
  'view'   => 'contents',
  'fields' => array(

    array(
      'id'    => 'opt_content_1',
      'type'  => 'textarea',
      'title' => 'Content 1',
    ),

    array(
      'id'    => 'opt_content_2',
      'type'  => 'textarea',
      'title' => 'Content 2',
    ),

  )
) );

CSF::createSection( $prefix, array(
  'title'           => '[foo] view: group',
  'view'            => 'group',
  'shortcode'       => 'foo',
  'group_shortcode' => 'nested_foo',
  'group_fields'    => array(

    array(
      'id'     => 'opt_title',
      'type'   => 'text',
      'title'  => 'Title',
    ),

    array(
      'id'     => 'content',
      'type'   => 'textarea',
      'title'  => 'Content',
    ),

  )
) );

CSF::createSection( $prefix, array(
  'title'     => '[foo] view: group alternative',
  'view'      => 'group',
  'shortcode' => 'foo',
  'fields'    => array(

    array(
      'id'    => 'opt_switcher',
      'type'  => 'switcher',
      'title' => 'Switcher',
      'label' => 'The label text of the switcher.',
    ),

    array(
      'id'      => 'opt_select',
      'type'    => 'select',
      'title'   => 'Select',
      'options' => array(
        'opt-1' => 'Option 1',
        'opt-2' => 'Option 2',
        'opt-3' => 'Option 3',
      ),
    ),

  ),
  'group_shortcode' => 'nested_foo',
  'group_fields'    => array(

    array(
      'id'    => 'title',
      'type'  => 'text',
      'title' => 'Title',
    ),

    array(
      'id'    => 'content',
      'type'  => 'textarea',
      'title' => 'Content',
    ),

  )
) );

CSF::createSection( $prefix, array(
  'title'     => '[foo] view: repeater',
  'view'      => 'repeater',
  'shortcode' => 'foo',
  'fields'    => array(

    array(
      'id'    => 'opt_title',
      'type'  => 'text',
      'title' => 'Title',
    ),

    array(
      'id'    => 'opt_switcher',
      'type'  => 'switcher',
      'title' => 'Switcher',
      'label' => 'The label text of the switcher.',
    ),

    array(
      'id'      => 'opt_select',
      'type'    => 'select',
      'title'   => 'Select',
      'options' => array(
        'opt-1' => 'Option 1',
        'opt-2' => 'Option 2',
        'opt-3' => 'Option 3',
      ),
    ),

  )
) );

CSF::createSection( $prefix, array(
  'title'     => '[foo] view: repeater alternative',
  'view'      => 'repeater',
  'shortcode' => 'foo',
  'fields'    => array(

    array(
      'id'    => 'opt_title',
      'type'  => 'text',
      'title' => 'Title',
    ),

    array(
      'id'    => 'opt_switcher',
      'type'  => 'switcher',
      'title' => 'Switcher',
      'label' => 'The label text of the switcher.',
    ),

    array(
      'id'      => 'opt_select',
      'type'    => 'select',
      'title'   => 'Select',
      'options' => array(
        'opt-1' => 'Option 1',
        'opt-2' => 'Option 2',
        'opt-3' => 'Option 3',
      ),
    ),

    array(
      'id'    => 'content',
      'type'  => 'textarea',
      'title' => 'Content',
    ),

  )
) );
