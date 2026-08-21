<?php

class Option_Level {

    public static function res(){
        $prefix = 'ppo_options';
        $credit_name = get_op('credit_name', '积分');
        $xp_name = get_op('xp_slug', '经验值');

        CSF::createSection( $prefix, array(
            'parent' => 'user_panel', 
            'title'  => '用户等级',
            'icon'   => 'ri-award-line',
            'fields' => array(

                array(
                  'id'      => 'xp_slug',
                  'type'    => 'text',
                  'title'   => '经验值名称',
                  'default' => '经验值',
                  'class'   => 'mini-input',
                  'subtitle' => '例如：经验值、能量值等',
                ),

                array(
                  'id'           => 'xp_icon',
                  'type'         => 'upload',
                  'title'        => '经验值图标',
                  'library'      => 'image',
                  'placeholder'  => 'http://',
                  'button_title' => '添加图标',
                  'remove_title' => '移除图标',
                  'default'      => THEME_URL.'/img/xp.png',
                  'preview' => true,
                  'subtitle' => '建议尺寸：32*32px',
                ),

                array(
                    'id'        => 'user_level_item',
                    'type'      => 'group',
                    'title'     => '等级设置',
                    'subtitle'  => '<div style="color:red"><strong>#</strong> 请勿随意删除或更改等级的顺序，可在预设基础上做修改，或增加更多自定义等级。等级权益会叠加到普通用户与会员权限上。</div>',
                    'class'     => 'no-drag',
                    'fields'    => array(
                      
                      array(
                        'id'    => 'lv_name',
                        'type'  => 'text',
                        'title' => '等级名称',
                      ),
                      array(
                        'id'           => 'lv_icon',
                        'type'         => 'upload',
                        'title'        => '等级图标',
                        'library'      => 'image',
                        'placeholder'  => 'http://',
                        'button_title' => '添加图标',
                        'remove_title' => '移除图标',
                        'preview' => true,
                      ),
                      array(
                        'id'      => 'lv_xp',
                        'type'    => 'spinner',
                        'title'   => '所需'.$xp_name,
                        'step'    => 1,
                      ),
                      array(
                        'id'      => 'down_num',
                        'type'    => 'text',
                        'class'   => 'mini-input',
                        'title'   => '每日可下载次数',
                        'desc'    => '大于或等于9999视为无限次；留空或0表示不影响下载额度。',
                        'default' => '0',
                      ),
                      array(
                        'id'         => 'limits',
                        'type'       => 'checkbox',
                        'title'      => '等级权限',
                        'options'    => array(
                          'comment'   => '发布评论',
                          'moment'    => '发布片刻',
                          'cr_moment' => '创建圈子',
                          'msg'       => '发送私信',
                          'up_image'  => '上传图片',
                          'up_video'  => '上传视频',
                          'up_file'   => '上传文件',
                        ),
                        'default'    => array(),
                        'inline'     => true,
                        'check_all'  => true,
                        'check_all_text' => '全选/取消全选',
                        'desc'       => '勾选后会额外放行对应功能，适合给高等级用户增加权益。',
                      ),
                    ),
                    
                    'default'   => array(
                      array(
                        'lv_name'     => 'LV1',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-1.png',
                        'lv_xp' => 0,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV2',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-2.png',
                        'lv_xp' => 100,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV3',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-3.png',
                        'lv_xp' => 300,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV4',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-4.png',
                        'lv_xp' => 600,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV5',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-5.png',
                        'lv_xp' => 1000,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV6',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-6.png',
                        'lv_xp' => 1500,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV7',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-7.png',
                        'lv_xp' => 2100,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV8',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-8.png',
                        'lv_xp' => 2800,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                      array(
                        'lv_name'     => 'LV9',
                        'lv_icon'    => THEME_URL.'/img/level/user-lv-9.png',
                        'lv_xp' => 3600,
                        'down_num' => '0',
                        'limits' => array(),
                      ),

                    ),

                  ),
     
    
            )
            ) );  

            // 每日任务
            CSF::createSection( $prefix, array(
              'parent' => 'user_panel', 
              'title'  => '任务和奖励',
              'icon'   => 'ri-trophy-line',
              'fields' => array(
                  
                  // 每日任务
                  array(
                    'id'        => 'daily_tasks',
                    'type'      => 'fieldset',
                    'title'     => '每日任务设置',
                    'fields'    => array(
                      array(
                        'id'        => 'comment',
                        'type'      => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'     => '评论他人',
                        'fields'    => array(
                          array(
                            'id'    => 'xp',
                            'type'  => 'spinner',
                            'title' => $xp_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'point',
                            'type'  => 'spinner',
                            'title' => $credit_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'limits',
                            'type'  => 'spinner',
                            'title' => '每日上限（次）',
                            'step'  => 1,
                          ),
                        ),
                        'default' => array('xp' => 5, 'point' => 3, 'limits' => 5),
                      ),
                      array(
                        'id'      => 'post',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '发布文章',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 10, 'point' => 20, 'limits' => 3),
                      ),
                      array(
                        'id'      => 'moment',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '发布片刻',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 5, 'point' => 15, 'limits' => 5),
                      ),
                      array(
                        'id'      => 'like_content',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '点赞文章/片刻',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 3, 'point' => 3, 'limits' => 10),
                      ),
                      array(
                        'id'      => 'collect_content',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '收藏文章/片刻',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 2, 'point' => 2, 'limits' => 10),
                      ),
                      array(
                        'id'      => 'like_comment',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '点赞评论',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 2, 'point' => 2, 'limits' => 10),
                      ),
                      array(
                        'id'      => 'follow_user',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '关注他人',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 2, 'point' => 2, 'limits' => 5),
                      ),
                      array(
                        'id'      => 'send_msg',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '发送私信',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 2, 'point' => 2, 'limits' => 5),
                      ),
                      array(
                        'id'      => 'buy_resource',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '资源购买',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 25, 'point' => 0, 'limits' => 10),
                      ),
                      array(
                        'id'      => 'recharge',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '充值',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 30, 'point' => 0, 'limits' => 5),
                      ),
                      array(
                        'id'      => 'be_followed',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '被他人关注',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 2, 'point' => 2, 'limits' => 5),
                      ),
                      
                      array(
                        'id'      => 'content_liked',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '文章 / 片刻被点赞',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 3, 'point' => 5, 'limits' => 10),
                      ),
                      
                      array(
                        'id'      => 'comment_liked',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '评论被点赞',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 2, 'point' => 3, 'limits' => 10),
                      ),
                      
                      array(
                        'id'      => 'moment_featured',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '片刻被加精',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 10, 'point' => 15, 'limits' => 3),
                      ),
                      
                      array(
                        'id'      => 'content_favored',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '文章 / 片刻被收藏',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 4, 'point' => 4, 'limits' => 10),
                      ),
                      
                      array(
                        'id'      => 'content_commented',
                        'type'    => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'   => '文章 / 片刻被评论',
                        'fields'  => array(
                          array('id' => 'xp', 'type' => 'spinner', 'title' => $xp_name.'奖励', 'step' => 1),
                          array('id' => 'point', 'type' => 'spinner', 'title' => $credit_name.'奖励', 'step' => 1),
                          array('id' => 'limits', 'type' => 'spinner', 'title' => '每日上限（次）', 'step' => 1),
                        ),
                        'default' => array('xp' => 3, 'point' => 5, 'limits' => 10),
                      ),
                    ),
                  ),
                  
                  // 新手任务
                  array(
                    'id'     => 'new_user_rewards',
                    'type'   => 'fieldset',
                    'title'  => '新用户奖励设置',
                    'fields' => array(
                  
                      array(
                        'id'     => 'register',
                        'type'   => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'  => '首次注册',
                        'fields' => array(
                          array(
                            'id'    => 'xp',
                            'type'  => 'spinner',
                            'title' => $xp_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'point',
                            'type'  => 'spinner',
                            'title' => $credit_name.'奖励',
                            'step'  => 1,
                          ),
                        ),
                        'default' => array('xp' => 20, 'point' => 50),
                      ),
                  
                      array(
                        'id'     => 'email',
                        'type'   => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'  => '完善邮箱',
                        'fields' => array(
                          array(
                            'id'    => 'xp',
                            'type'  => 'spinner',
                            'title' => $xp_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'point',
                            'type'  => 'spinner',
                            'title' => $credit_name.'奖励',
                            'step'  => 1,
                          ),
                        ),
                        'default' => array('xp' => 10, 'point' => 20),
                      ),
                  
                      array(
                        'id'     => 'phone',
                        'type'   => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'  => '完善手机号码',
                        'fields' => array(
                          array(
                            'id'    => 'xp',
                            'type'  => 'spinner',
                            'title' => $xp_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'point',
                            'type'  => 'spinner',
                            'title' => $credit_name.'奖励',
                            'step'  => 1,
                          ),
                        ),
                        'default' => array('xp' => 10, 'point' => 20),
                      ),
                  
                     /*  array(
                        'id'     => 'bind_qq',
                        'type'   => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'  => '绑定 QQ',
                        'fields' => array(
                          array(
                            'id'    => 'xp',
                            'type'  => 'spinner',
                            'title' => $xp_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'point',
                            'type'  => 'spinner',
                            'title' => $credit_name.'奖励',
                            'step'  => 1,
                          ),
                        ),
                        'default' => array('xp' => 10, 'point' => 20),
                      ), */
                  
                      array(
                        'id'     => 'avatar',
                        'type'   => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'  => '上传头像',
                        'fields' => array(
                          array(
                            'id'    => 'xp',
                            'type'  => 'spinner',
                            'title' => $xp_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'point',
                            'type'  => 'spinner',
                            'title' => $credit_name.'奖励',
                            'step'  => 1,
                          ),
                        ),
                        'default' => array('xp' => 8, 'point' => 10),
                      ),
                  
                      array(
                        'id'     => 'cover',
                        'type'   => 'fieldset',
                        'class'     => 'column-fieldset',
                        'title'  => '上传封面图',
                        'fields' => array(
                          array(
                            'id'    => 'xp',
                            'type'  => 'spinner',
                            'title' => $xp_name.'奖励',
                            'step'  => 1,
                          ),
                          array(
                            'id'    => 'point',
                            'type'  => 'spinner',
                            'title' => $credit_name.'奖励',
                            'step'  => 1,
                          ),
                        ),
                        'default' => array('xp' => 8, 'point' => 10),
                      ),
                  
                    )
                  )
                  
                
                )
              )
              );

               // 每日签到
            CSF::createSection( $prefix, array(
              'parent' => 'user_panel', 
              'title'  => '每日签到',
              'icon'   => 'ri-calendar-check-line',
              'fields' => array(

                array(
                  'id'            => 'daily_checkin',
                  'type'          => 'tabbed',
                  'title'         => '每日签到设置',
                  'tabs'          => array(
                    //第一天
                    array(
                      'title'     => '第1天(单次)',
                      'fields'    => array(
                        array(
                          'id'    => 'daily_checkin_1_xp',
                          'type'  => 'spinner',
                          'title' => $xp_name.'奖励',
                          'step'  => 1,
                        ),
                        array(
                          'id'    => 'daily_checkin_1_point',
                          'type'  => 'spinner',
                          'title' => $credit_name.'奖励',
                          'step'  => 1,
                        ),
                      )
                    ),

                    // 第二天
                    array(
                      'title'     => '第2天',
                      'fields'    => array(
                        array(
                          'id'    => 'daily_checkin_2_xp',
                          'type'  => 'spinner',
                          'title' => $xp_name.'奖励',
                          'step'  => 1,
                        ),
                        array(
                          'id'    => 'daily_checkin_2_point',
                          'type'  => 'spinner',
                          'title' => $credit_name.'奖励',
                          'step'  => 1,
                        ),
                      )
                    ),

                    // 第二天
                    array(
                      'title'     => '第3天',
                      'fields'    => array(
                        array(
                          'id'    => 'daily_checkin_3_xp',
                          'type'  => 'spinner',
                          'title' => $xp_name.'奖励',
                          'step'  => 1,
                        ),
                        array(
                          'id'    => 'daily_checkin_3_point',
                          'type'  => 'spinner',
                          'title' => $credit_name.'奖励',
                          'step'  => 1,
                        ),
                      )
                    ),

                    // 第二天
                    array(
                      'title'     => '第4天',
                      'fields'    => array(
                        array(
                          'id'    => 'daily_checkin_4_xp',
                          'type'  => 'spinner',
                          'title' => $xp_name.'奖励',
                          'step'  => 1,
                        ),
                        array(
                          'id'    => 'daily_checkin_4_point',
                          'type'  => 'spinner',
                          'title' => $credit_name.'奖励',
                          'step'  => 1,
                        ),
                      )
                    ),

                    // 第二天
                    array(
                      'title'     => '第5天',
                      'fields'    => array(
                        array(
                          'id'    => 'daily_checkin_5_xp',
                          'type'  => 'spinner',
                          'title' => $xp_name.'奖励',
                          'step'  => 1,
                        ),
                        array(
                          'id'    => 'daily_checkin_5_point',
                          'type'  => 'spinner',
                          'title' => $credit_name.'奖励',
                          'step'  => 1,
                        ),
                      )
                    ),

                    // 第二天
                    array(
                      'title'     => '第6天',
                      'fields'    => array(
                        array(
                          'id'    => 'daily_checkin_6_xp',
                          'type'  => 'spinner',
                          'title' => $xp_name.'奖励',
                          'step'  => 1,
                        ),
                        array(
                          'id'    => 'daily_checkin_6_point',
                          'type'  => 'spinner',
                          'title' => $credit_name.'奖励',
                          'step'  => 1,
                        ),
                      )
                    ),

                    // 第二天
                    array(
                      'title'     => '第7天',
                      'fields'    => array(
                        array(
                          'id'    => 'daily_checkin_7_xp',
                          'type'  => 'spinner',
                          'title' => $xp_name.'奖励',
                          'step'  => 1,
                        ),
                        array(
                          'id'    => 'daily_checkin_7_point',
                          'type'  => 'spinner',
                          'title' => $credit_name.'奖励',
                          'step'  => 1,
                        ),
                      )
                    ),
                   
                  ),

                  'default'       => array(
                    'daily_checkin_1_xp'  => 5,
                    'daily_checkin_1_point'  => 3,  
                    'daily_checkin_2_xp'  => 10,
                    'daily_checkin_2_point'  => 8,  
                    'daily_checkin_3_xp'  => 15,
                    'daily_checkin_3_point'  => 10,  
                    'daily_checkin_4_xp'  => 30,
                    'daily_checkin_4_point'  => 20,  
                    'daily_checkin_5_xp'  => 40,
                    'daily_checkin_5_point'  => 30,  
                    'daily_checkin_6_xp'  => 60,
                    'daily_checkin_6_point'  => 45,  
                    'daily_checkin_7_xp'  => 100,
                    'daily_checkin_7_point'  => 80,
                  ),
                  
                ),
                

              )
            )
              );

            

    }
}

