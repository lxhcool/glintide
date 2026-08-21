<?php
if (!function_exists('pix_admin_premium_features_available')) {
    function pix_admin_premium_features_available() {
        return function_exists('pix_is_authorized') && pix_is_authorized();
    }
}

// 添加管理菜单
function notification_admin_menu() {
    if (!pix_admin_premium_features_available()) {
        return;
    }

    add_menu_page(
        '系统消息推送',
        '消息推送',
        'manage_options',
        'notification-system',
        'notification_list_page',
          'dashicons-megaphone',
          100
      );
      
      add_submenu_page(
          null,
          '创建新消息',
          '创建消息',
          'manage_options',
          'notification-system-new',
          'notification_new_page'
      );

      add_submenu_page(
        null,
        '编辑消息',
        '', // 不在菜单中显示
        'manage_options',
        'notification-system-edit',
        'notification_edit_page'
    );
  }
  add_action('admin_menu', 'notification_admin_menu');
  
// 接收用户数组
function get_receive_users() {
    $receive_user = array();
    $add_user = array(
        'all_user' => '所有用户',
        'all_vip' => '所有会员',
        'chose_user' => '选择用户'
    );

    $new_array = array_merge($add_user, $receive_user);

    return $new_array;
}


  // 消息列表页面
function notification_list_page() {
    if (!pix_admin_premium_features_available()) {
        wp_die('此功能需要激活主题授权后使用。', '主题未授权', array('response' => 403));
    }

    global $wpdb;
$table_name = $wpdb->prefix . 'ppo_msg';

// 创建nonce
$nonce = wp_create_nonce('save_notification');

// 处理搜索参数
$search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// 标签和分类
$tabs = array(
    'all' => '全部消息',
    'system-msg' => '系统消息',
    'member-msg' => '会员消息',
    'activity-msg' => '活动消息'
);
$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'all';

// 分页参数
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

// 构建查询
$where = [];
$args = [];
$count_args = [];

$where[] = "type = 'system_msg'"; // 统一类型字段

// 处理tab过滤
if ($current_tab !== 'all') {
    $tab_to_slug = array(
        'system-msg' => 'system_msg',
        'member-msg' => 'member_msg',
        'activity-msg' => 'activity_msg'
    );
    if (isset($tab_to_slug[$current_tab])) {
        $where[] = "extra LIKE %s";
        $like = '%' . $wpdb->esc_like('"type_slug";s:' . strlen($tab_to_slug[$current_tab]) . ':"' . $tab_to_slug[$current_tab]) . '%';
        $args[] = $like;
        $count_args[] = $like;
    }
}

// 搜索关键词处理
if (!empty($search_term)) {
    $search_term_like = '%' . $wpdb->esc_like($search_term) . '%';
    $where[] = "(title LIKE %s OR content LIKE %s)";
    $args[] = $search_term_like;
    $args[] = $search_term_like;
    $count_args[] = $search_term_like;
    $count_args[] = $search_term_like;
}

// 拼接SQL
$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT * FROM $table_name $where_sql ORDER BY create_time DESC LIMIT %d OFFSET %d";
$args = array_merge($args, [$per_page, $offset]);

$query_count = "SELECT COUNT(ID) FROM $table_name $where_sql";

// 查询总数
if (!empty($count_args)) {
    $total_items = $wpdb->get_var($wpdb->prepare($query_count, $count_args));
} else {
    $total_items = $wpdb->get_var($query_count);
}

// 查询分页内容
if (!empty($args)) {
    $notifications = $wpdb->get_results($wpdb->prepare($query, $args));
} else {
    $notifications = $wpdb->get_results($query);
}

// 分页
$total_pages = max(1, ceil($total_items / $per_page));
      
      ?>
      <div class="wrap ppo-msg-list update-msg-wrap admin-msg-page">
          <h1>系统消息推送</h1>
        <!-- 搜索表单 -->
        <div class="top-bar">
         <a href="admin.php?page=notification-system-new" class="page-title-action">创建新消息</a>

            <form method="get" class="search-form">
                <input type="hidden" name="page" value="notification-system">
                <?php if ($current_tab !== 'all') { ?>
                    <input type="hidden" name="tab" value="<?php echo esc_attr($current_tab); ?>">
                <?php } ?>
                <input type="text" name="s" placeholder="搜索消息..." value="<?php echo esc_attr($search_term); ?>">
                <input type="submit" value="搜索" class="button">
                <?php if (!empty($search_term)) { ?>
                    <a href="<?php echo esc_url('admin.php?page=notification-system&tab=' . $current_tab); ?>" class="button">清除搜索</a>
                <?php } ?>
            </form>
        </div>

         <div class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab_label) {
                $active_class = ($tab_key === $current_tab) ? 'nav-tab-active' : '';
                $tab_url = 'admin.php?page=notification-system';
                if ($tab_key !== 'all') {
                    $tab_url .= '&tab=' . $tab_key;
                }
                // 如果有搜索关键词，保留在标签链接中
                if (!empty($search_term)) {
                    $tab_url .= '&s=' . urlencode($search_term);
                }
            ?>
                <a href="<?php echo esc_url($tab_url); ?>" class="nav-tab <?php echo $active_class; ?>">
                    <?php echo $tab_label; ?>
                </a>
            <?php } ?>
        </div>
          
          <table class="wp-list-table widefat fixed striped">
              <thead>
                  <tr>
                      <th width="8%">类型</th>
                      <th width="8%">接收用户</th>
                      <th width="14%">已读用户</th>
                      <th width="10%">创建时间</th>
                      <th width="25%">标题</th>
                      <th width="30%">内容</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($notifications as $notification) { 
                  
                      $content = $notification->content;
                      $content = strip_tags($content);
                      $content = wp_trim_words($content, 50, '...');
                      $msg_meta = unserialize($notification->extra);
                      $readed_user = !empty($msg_meta['user_readed']) ? count(json_decode($msg_meta['user_readed'],true)).'个已读' : '未读';
                      $receive_user_type = isset($notification->info_meta) ? $notification->info_meta : '所有用户';

                  ?>
                  <tr>
                      <td>
                        <?php echo $msg_meta['type']; ?>
                        <div class="edit-row">
                            <a href="admin.php?page=notification-system-edit&id=<?php echo $notification->ID; ?>" class="edit-notification">编辑</a>
                            <a href="#" class="delete-notification" data-id="<?php echo $notification->ID; ?>">删除</a>
                        </div>
                    </td>
                      <td ><?php echo receive_user_convert($receive_user_type); ?></td>
                      <td><?php echo $readed_user; ?></td>
                      <td><?php echo $notification->create_time; ?></td>
                      <td><?php echo $notification->title; ?></td>
                      <td><?php echo $content; ?></td>
                  </tr>
                  <?php } ?>
              </tbody>
          </table>

           <!-- 分页导航 -->
        
           <div class="tablenav bottom ppo-pagenav">
            
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo sprintf(_n('%s 条消息', '%s 条消息', $total_items), number_format_i18n($total_items)); ?></span>
                <span class="pagination-links">
                    <?php
                    $args = array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'total' => $total_pages,
                        'current' => $paged,
                        'prev_text' => '<span class="screen-reader-text">上一页</span><span aria-hidden="true">«</span>',
                        'next_text' => '<span class="screen-reader-text">下一页</span><span aria-hidden="true">»</span>',
                        'show_all' => false,
                        'add_args' => array('page' => 'notification-system'),
                        'mid_size' => 2, // 中间显示的页码数量
                        'end_size' => 1, // 两端显示的页码数量
                    );
                    
                    if (!empty($search_term)) {
                        $args['add_args']['s'] = $search_term;
                    }
                    
                    echo paginate_links($args);
                    
                    // 显示当前页和总页数信息
                    if ($total_pages > 1) {
                        echo '<span class="paging-input">';
                        printf(
                            '<label for="current-page-selector" class="screen-reader-text">%s</label>',
                            __('当前页')
                        );
                        echo '<span class="now-paged">第'.$paged.'页</span> , ';
                        echo '<span class="total-page">共'.number_format_i18n($total_pages).'页</span>';
                        echo '</span>';
                    }
                    ?>
                </span>
            </div>
        </div>

      </div>
      
      <script>
      jQuery(document).ready(function($) {
          $('.delete-notification').click(function(e) {
              e.preventDefault();
              var notification_nonce = '<?php echo $nonce; ?>';
              if (confirm('确定要删除这条消息吗？')) {
                  var id = $(this).data('id');
                  $.ajax({
                      url: ajaxurl,
                      type: 'POST',
                      data: {
                          action: 'delete_notification',
                          id: id,
                          notification_nonce: notification_nonce
                      },
                      success: function(response) {
                          if (response.success) {
                              location.reload();
                          } else {
                              alert('删除失败：' + response.data);
                          }
                      }
                  });
              }
          });
      });
      </script>
      <?php
  }
  
  // 创建消息页面
function notification_new_page() {
      if (class_alias( 'CSF' ,'PCSF')) {
          // 获取所有用户用于下拉选择
          
          $msg_prefix = 'ppo_msg_system';
        
          

          echo '<div class="wrap pix-options create-msg-wrap admin-msg-page">';
          echo '<h1>创建新消息</h1>';
          echo '<div class="top-bar"><a href="admin.php?page=notification-system" class="page-title-action">返回列表</a></div>';
          echo '<form id="notification-form" method="post" enctype="multipart/form-data">';
          
          echo '<div class="csf-onload">';
          
          CSF::$enqueue = true;
          CSF::add_admin_enqueue_scripts();

          // 使用CSF框架创建表单
          CSF::field(array(
              'id' => 'receive_user',
              'type' => 'select',
              'title' => '接收用户',
              'options' => get_receive_users(),
              'subtitle' => '必填，请选择接收推送的群体或用户',
              'default' => 'all_user',
          ), '', $msg_prefix);

          CSF::field(array(
            'id' => 'chose_user',
            'type' => 'select',
            'title' => '指定用户',
            'chosen'      => true,
            'ajax'        => true,
            'options' => 'users',
            'multiple' => true,
            'subtitle' => '请选择需要推送的用户,若不选择，则默认发送给所有人',
            'placeholder' => '搜索用户...',
            'settings' => array(
              'min_length' => 1,
              'searching_text' => '搜索用户...',
            
            ),
            'dependency' => array( 'receive_user', '==', 'chose_user' ),
        ), '', $msg_prefix);

          CSF::field(array(
            'id' => 'type',
            'type' => 'select',
            'title' => '类型',
            'options' => array(
                'activity_msg' => '活动消息',
                'member_msg' => '会员消息',
                'system_msg' => '系统消息',
            ),
            'subtitle' => '必填，选择一种推送消息类型',
            'default' => 'system_msg',
            'attributes' => array(
                'required' => 'required'
            )
        ), '', $msg_prefix);
          
          CSF::field(array(
              'id' => 'logo',
              'type' => 'upload',
              'title' => '消息图标',
              'subtitle' => '选填，可上传图片作为消息图标',
          ), '', $msg_prefix);

          CSF::field(array(
            'id' => 'title',
            'type' => 'text',
            'title' => '标题',
            'placeholder' => '请输入消息标题',
            'subtitle' => '必填，字数建议在10-30字之间，不要过长',
            'attributes' => array(
                'required' => 'required'
            )
        ), '', $msg_prefix);
          
          CSF::field(array(
              'id' => 'content',
              'type' => 'wp_editor',
              'title' => '内容',
              'tinymce'       => true,
              'quicktags'     => true,
              'media_buttons' => true,
              'height'        => '300px',
              'attributes' => array(
                  'required' => 'required'
              )
          ), '', $msg_prefix);
          
          
          echo '</div>';
          
          echo '<input type="hidden" name="action" value="save_notification">';
          echo wp_nonce_field('save_notification', 'notification_nonce', true, false);
          
          echo '<p class="submit">';
          echo '<input type="submit" class="button button-primary" value="发布消息">';
          echo '</p>';
          
          echo '</form>';
          echo '</div>';
          
          ?>
          <script>
          jQuery(document).ready(function($) {
              $('#notification-form').on('submit', function(e) {
                  e.preventDefault();
                  
                  var formData = new FormData(this);
                  
                  $.ajax({
                      url: ajaxurl,
                      type: 'POST',
                      data: formData,
                      contentType: false,
                      processData: false,
                      success: function(response) {
                          if (response.success) {
                              alert('消息发布成功！');
                              window.location.href = 'admin.php?page=notification-system';
                          } else {
                              alert('发布失败：' + response.data);
                          }
                      },
                      error: function() {
                          alert('网络错误，请重试！');
                      }
                  });
              });
          });
          </script>
          <?php
      } else {
          echo '<div class="error"><p>请先安装并激活CodeStar Framework插件</p></div>';
      }
  }

// 编辑消息页面
function notification_edit_page() {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        wp_die('无效的消息ID');
    }
    
    $msg_prefix = 'ppo_msg_system';

    $id = intval($_GET['id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';
    
    $notification = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", $id));
    
    if (!$notification) {
        wp_die('消息不存在');
    }
    
    if (class_exists('CSF')) {
        
        $content = $notification->content;
        $type = $notification->type;
        $title = $notification->title;
        $msg_meta = unserialize($notification->extra);
        $readed_user = !empty($msg_meta['readed_user']) ? $msg_meta['readed_user'] : '未读';
        $receive_user_type = isset($msg_meta['receive_user'])? $msg_meta['receive_user'] : '所有用户';
        $icon = isset($msg_meta['icon'])? $msg_meta['icon'] : '';

        echo '<div class="wrap pix-options edit-msg-wrap admin-msg-page">';
        echo '<h1>编辑推送消息</h1>';
        echo '<div class="top-bar"><a href="admin.php?page=notification-system" class="page-title-action">返回列表</a>';
        echo '<a href="admin.php?page=notification-system-new" class="page-title-action">创建新消息</a>';
        echo '<div class="notice-info">*当前正在编辑消息，请谨慎操作</div>';
        echo '</div>';

        echo '<form id="notification-edit-form" method="post" enctype="multipart/form-data">';
        
        echo '<div class="csf-onload">';
        
         CSF::$enqueue = true;
          CSF::add_admin_enqueue_scripts();

          // 使用CSF框架创建表单
          CSF::field(array(
              'id' => 'receive_user',
              'type' => 'select',
              'title' => '接收用户',
              'options' => get_receive_users(),
              'subtitle' => '必填，请选择接收推送的群体或用户',
              'default' => 'all_user'
          ), $receive_user_type, $msg_prefix);

          CSF::field(array(
            'id' => 'chose_user',
            'type' => 'select',
            'title' => '指定用户',
            'chosen'      => true,
            'ajax'        => true,
            'options' => 'users',
            'multiple' => true,
            'subtitle' => '请选择需要推送的用户',
            'placeholder' => '搜索用户...',
            'settings' => array(
              'min_length' => 1,
              'searching_text' => '搜索用户...',   
            ),
            'dependency' => array( 'receive_user', '==', 'chose_user' ),
        ), '', $msg_prefix);

          CSF::field(array(
            'id' => 'type',
            'type' => 'select',
            'title' => '类型',
            'options' => array(
                'activity_msg' => '活动消息',
                'member_msg' => '会员消息',
                'system_msg' => '系统消息',
            ),
            'subtitle' => '必填，选择一种推送消息类型',
            'default' => 'system_msg',
            'attributes' => array(
                'required' => 'required'
            )
        ), $type, $msg_prefix);
          
          CSF::field(array(
              'id' => 'logo',
              'type' => 'upload',
              'title' => '消息图标',
              'subtitle' => '选填，可上传图片作为消息图标',
          ), $icon, $msg_prefix);

          CSF::field(array(
            'id' => 'title',
            'type' => 'text',
            'title' => '标题',
            'placeholder' => '请输入消息标题',
            'subtitle' => '必填，字数建议在10-30字之间，不要过长',
            'attributes' => array(
                'required' => 'required'
            )
        ), $title, $msg_prefix);
          
          CSF::field(array(
              'id' => 'content',
              'type' => 'wp_editor',
              'title' => '内容',
              'tinymce'       => true,
              'quicktags'     => true,
              'media_buttons' => true,
              'attributes' => array(
                  'required' => 'required'
              )
          ), $content, $msg_prefix);
        
        echo '</div>';
        
        echo '<input type="hidden" name="action" value="update_notification">';
        echo '<input type="hidden" name="id" value="' . esc_attr($id) . '">';
        echo wp_nonce_field('update_notification', 'notification_nonce', true, false);
        
        echo '<p class="submit">';
        echo '<input type="submit" class="button button-primary" value="更新消息">';
        echo '</p>';
        
        echo '</form>';
        echo '</div>';
        
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('#notification-edit-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            alert('消息更新成功！');
                            window.location.href = 'admin.php?page=notification-system';
                        } else {
                            alert('更新失败：' + response.data);
                        }
                    },
                    error: function() {
                        alert('网络错误，请重试！');
                    }
                });
            });
        });
        </script>
        <?php
    } else {
        echo '<div class="error"><p>请先安装并激活CodeStar Framework插件</p></div>';
    }
}

  // 消息类型转换
  function notification_type_convert($type) {
      switch ($type) {
          case 'activity_msg': return '活动消息';
          case 'member_msg': return '会员消息';
          case 'system_msg': return '系统消息';
          default: return '未知类型';
      }
  }

  // 接收用户转换
    function receive_user_convert($data) {
            $receive_user = array();
            $receive_user['all_user'] = '所有用户';
            $receive_user['all_vip'] = '所有会员';
            $receive_user['chose_user'] = '指定用户';

            return $receive_user[$data];
    }


  // 保存消息处理
  function save_notification() {
     check_ajax_referer('save_notification', 'notification_nonce');
      
      if (!current_user_can('manage_options')) {
          wp_send_json_error('权限不足');
          wp_die();
      }

      // 获取并验证表单数据
      $form_data = isset($_POST['ppo_msg_system']) ? $_POST['ppo_msg_system'] : array();
      $title = isset($form_data['title']) ? sanitize_text_field($form_data['title']) : '';
      $content = isset($form_data['content']) ? wp_kses_post($form_data['content']) : '';
      $type = isset($form_data['type']) ? sanitize_text_field($form_data['type']) : 'system_msg';
      //$type = notification_type_convert($type);
      $receive_user = isset($form_data['receive_user']) ? $form_data['receive_user'] : 'all_user';
      $icon = isset($form_data['logo']) ? esc_url_raw($form_data['logo']) : '';
      $chose_user = [];

      if($receive_user == 'chose_user'){
        $chose_user = isset($form_data['chose_user']) ? json_encode(array_map('intval', $form_data['chose_user'])) : [];
      }

      $extra = array(
        //'receive_user' => $receive_user,
        'user_readed' => json_encode([]),
        'type' => notification_type_convert($type),
        'type_slug' => $type,
      );

      if (empty($title) || empty($content) || empty($type)) {
        wp_send_json_error('请填写所有必填字段');
        wp_die();
     }

      if(!empty($icon)){
        $extra['icon'] = $icon;
      }

      global $wpdb;
      $table_name = $wpdb->prefix . 'ppo_msg';

      // 准备数据数组
      $data = array(
        'receive_user' => 0,
        'send_id' => 1, 
        'type' => 'system_msg',
        'title' => $title,
        'content' => $content,
        'create_time' => current_time('mysql'),
        'status' => 'unread',
        'extra' => serialize($extra),
        'info_meta' => $receive_user,
        'other' => $chose_user
    );

    // 准备数据格式数组
    $format = array(
        '%d', // receive_user
        '%d', // send_id
        '%s', // type
        '%s', // title
        '%s', // content
        '%s', // create_time
        '%s', // status
        '%s',  // extra
        '%s',  // info_meta
        '%s'  // other
    );

    // 插入数据到数据库
    $result = $wpdb->insert($table_name, $data, $format);

    if ($result) { 
        wp_send_json_success();
    } else {

        wp_send_json_error('数据库插入失败');
    }
    
    wp_die();
  }
  add_action('wp_ajax_save_notification', 'save_notification');

// 更新消息处理
function update_notification() {
    check_ajax_referer('update_notification', 'notification_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
        wp_die();
    }
    
    // 获取并验证表单数据
    $id = intval($_POST['id']);
    
    $form_data = isset($_POST['ppo_msg_system']) ? $_POST['ppo_msg_system'] : array();
    $title = isset($form_data['title']) ? sanitize_text_field($form_data['title']) : '';
    $content = isset($form_data['content']) ? wp_kses_post($form_data['content']) : '';
    $type = isset($form_data['type']) ? sanitize_text_field($form_data['type']) : 'system_msg';
    //$type = notification_type_convert($type);
    $receive_user = isset($form_data['receive_user']) ? $form_data['receive_user'] : 'all_user';
    $icon = isset($form_data['logo']) ? esc_url_raw($form_data['logo']) : '';

    $chose_user = [];

      if($receive_user == 'chose_user'){
        $chose_user = isset($form_data['chose_user']) ? json_encode($form_data['chose_user']) : [];
      }

      $extra = array(
        //'receive_user' => $receive_user,
        'user_readed' => json_encode([]),
        'type' => notification_type_convert($type),
        'type_slug' => $type,
      );
    
    // 验证必要字段
    if (empty($id) || empty($title) || empty($content) || empty($type)) {
        wp_send_json_error('请填写所有必填字段');
        wp_die();
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';
    
     // 准备数据数组
     $data = array(
        'receive_user' => 0,
        'send_id' => 1, 
        'type' => 'system_msg',
        'title' => $title,
        'content' => $content,
        'create_time' => current_time('mysql'),
        'status' => 'unread',
        'extra' => serialize($extra),
        'info_meta' => $receive_user,
        'other' => $chose_user
    );

    // 准备数据格式数组
    $format = array(
        '%d', // receive_user
        '%d', // send_id
        '%s', // type
        '%s', // title
        '%s', // content
        '%s', // create_time
        '%s', // status
        '%s',  // extra
        '%s',  // info_meta
        '%s'  // other
    );
    
    // 更新数据到数据库
    $result = $wpdb->update(
        $table_name,
        $data,
        array('ID' => $id),
        $format,
        array('%d')
    );
    
    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('数据库更新失败');
    }
    
    wp_die();
}
add_action('wp_ajax_update_notification', 'update_notification');

// 删除消息处理
function delete_notification() {
    //check_ajax_referer('save_notification', 'notification_nonce');
    $nonce = isset($_POST['notification_nonce']) ? $_POST['notification_nonce'] : '';
    $nonce_check = wp_verify_nonce($nonce, 'save_notification');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
        wp_die();
    }
    
    $id = intval($_POST['id']);
    
    if (empty($id)) {
        wp_send_json_error('无效的消息ID');
        wp_die();
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';
    
    $result = $wpdb->delete($table_name, array('ID' => $id), array('%d'));
    
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error('删除失败');
    }
    
    wp_die();
}
add_action('wp_ajax_delete_notification', 'delete_notification');




  
