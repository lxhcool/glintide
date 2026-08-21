<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = '_ppo_profile_options';
$pix_profile_options = get_option('ppo_options');
$pix_profile_cash_name = (is_array($pix_profile_options) && !empty($pix_profile_options['cash_name'])) ? $pix_profile_options['cash_name'] : '余额';
$pix_profile_credit_name = (is_array($pix_profile_options) && !empty($pix_profile_options['credit_name'])) ? $pix_profile_options['credit_name'] : '积分';
$pix_profile_xp_name = (is_array($pix_profile_options) && !empty($pix_profile_options['xp_slug'])) ? $pix_profile_options['xp_slug'] : '经验值';

//
// Create profile options
//
CSF::createProfileOptions( $prefix, array(
  'data_type' => 'unserialize',
) );

//
// Create a section
//
CSF::createSection( $prefix, array(
  'title'  => '会员设置',
  'fields' => array(

    array(
      'id'          => 'ppo_balance',
      'type'        => 'number',
      'title'       => $pix_profile_cash_name,
      'default'     => 0,
      'unit'      => $pix_profile_cash_name
    ),

    array(
      'id'          => 'ppo_credit',
      'type'        => 'number',
      'title'       => $pix_profile_credit_name,
      'default'     => 0,
      'unit'      => $pix_profile_credit_name
    ),

    array(
      'id'          => 'ppo_user_xp',
      'type'        => 'number',
      'title'       => $pix_profile_xp_name,
      'default'     => 0,
      'unit'      => $pix_profile_xp_name
    ),

    array(
      'id'          => 'ppo_user_level',
      'type'        => 'number',
      'title'       => '用户等级',
      'default'     => 0,
      'attributes'  => array(
        'readonly' => 'readonly',
      ),
      'desc' => '只读显示：此值会根据用户'.$pix_profile_xp_name.'和后台等级配置自动同步，请通过修改'.$pix_profile_xp_name.'来调整等级'
    ),

    array(
      'id'          => 'ppo_vip',
      'type'        => 'select',
      'title'       => '会员等级',
      'chosen'      => true,
      'empty_message' => '普通',
      'width'   => '250px',
      'settings' => array(
        'width' => '150px'
      ),
      'options'     => 'get_all_lv',
      'default'     => '',
    ),

    array(
      'id'       => 'ppo_vip_end',
      'type'     => 'date',
      'title'    => '会员到期时间',
      'subtitle' => '填写0为永久时间',
      'settings' => array(
        'dateFormat'      => 'yy-mm-dd',
        'changeMonth'     => true,
        'changeYear'      => true,
        'showButtonPanel' => true,     
      ),
    ),

    array(
      'id'    => 'ppo_dark_room',
      'type'  => 'switcher',
      'title' => '小黑屋',
      'text_on' => '自由',
      'text_off' => '禁闭',
      'default' => true
    ),

  )
) );
