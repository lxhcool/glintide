<?php
// 付费隐藏内容
if (!class_exists('PPO_Hide')) {
    class PPO_Hide {

        public function __construct(){
            add_shortcode('ppo_hide', array($this, 'ppo_hide_shortcode'));      
            add_action('init', array($this, 'register_hide_block'));
            add_filter('block_categories_all', array($this, 'register_pixpro_block_category'), 10, 2);
            add_filter('the_content', array($this, 'ppo_content_remove_contenteditable'));           
        }

        public function register_pixpro_block_category($categories, $post)
        {
            foreach ($categories as $category) {
                if (!empty($category['slug']) && $category['slug'] === 'pixpro') {
                    return $categories;
                }
            }

            array_unshift($categories, array(
                'slug' => 'pixpro',
                'title' => 'Glintide主题',
                'icon' => null,
            ));

            return $categories;
        }

        public function register_hide_block()
        {
            if (!function_exists('register_block_type')) {
                return;
            }

            $script_path = THEME_DIR . '/inc/assets/js/ppo-hide-block.js';
            $style_path = THEME_DIR . '/inc/assets/css/ppo-hide-block-editor.css';

            wp_register_script(
                'pixpro-ppo-hide-block',
                THEME_URL . '/inc/assets/js/ppo-hide-block.js',
                array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n'),
                file_exists($script_path) ? filemtime($script_path) : PIX_VERSION,
                true
            );

            wp_register_style(
                'pixpro-ppo-hide-block-editor',
                THEME_URL . '/inc/assets/css/ppo-hide-block-editor.css',
                array('wp-edit-blocks'),
                file_exists($style_path) ? filemtime($style_path) : PIX_VERSION
            );

            register_block_type('pixpro/ppo-hide', array(
                'api_version' => 2,
                'editor_script' => 'pixpro-ppo-hide-block',
                'editor_style' => 'pixpro-ppo-hide-block-editor',
                'render_callback' => array($this, 'render_hide_block'),
            ));
        }

        public function render_hide_block($attributes, $content)
        {
            return self::hide_box($content);
        }

        public static function hide_box($content){

            global $post; 
            $pid = $post->ID;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $html = '';
            $meta = get_post_meta($pid,'_ppo_post_options',true);
        
            //return $content;
            if(is_array($meta) && !empty($meta)) {
                $dd_type = $meta['dd_type'];

                if($dd_type == 'read'){
                    $data = $meta['dd_box_data'];
                    $data['pid'] = $pid;
                    $pay_type = isset($data['pay_type']) ? $data['pay_type'] : 'login';
                    // 支付/VIP 类隐藏改为登录可见（支付功能已移除）
                    $bought = is_user_logged_in() && in_array($pay_type, array('cash', 'credit', 'limits', 'login'));
                    $hide_img = THEME_URL.'/img/icon/lock.png';
    
                    $html = '<div class="ppo-hide-box">
                                <div class="hide-inner">
                                <div class="hide_img"><img src="'.$hide_img.'"></div>
                                    <div class="left-info">
                                        <div class="title"><i class="ri-eye-close-line"></i>隐藏内容</div>
                                    </div>
                                    <div class="right-btn">'.self::hide_box_btn($data).'</div>
                                </div>
                            </div>';
    
                    $show = '<div class="ppo-show-box">
                                <div class="show-tips"><i class="ri-lock-unlock-line"></i>内容已解锁</div>
                                <div class="show-mce-content"><div class="show-inner">'.do_shortcode($content).'</div></div>
                            </div>'; 
    
                    if($bought) {
                        return $show;
                    } else {
                        return $html;
                    }
                }

            }

        }

        // 按钮
        public static function hide_box_btn($data){

            $type = $data['pay_type'];
            // 支付/VIP 类按钮统一为登录查看（支付功能已移除）
            if (in_array($type, array('cash', 'credit', 'limits'), true)) {
                $type = 'login';
            }

            switch ($type) {
                case 'login': 
                    $btn = '<a class="dd-buy-login need-login"><i class="ri-user-follow-line"></i>登录查看</a>';
                    break;

               case 'cmt': 
                    $btn = '<a href="#comments" class="dd-buy-cmt" data-pix-smooth-scroll><i class="ri-message-3-line"></i>评论查看</a>';
                    break;  

                case 'limits': 
                    $btn = '<a href="'.home_url().'/vip" target="_blank" class="dd-up-vip"><i class="ri-vip-crown-2-line"></i>立即升级</a>';
                    break;  
                    
                case 'pwd': 
                    $btn = '<a class="dd-buy-pwd"><i class="ri-lock-line"></i>密码查看</a>';
                    break;      
            }

            return '<div class="dd-buy-btn">'.$btn.'</div>';

        }


        // 注册短代码
        public static function ppo_hide_shortcode($atts, $content = null) {
            
            //$content = preg_replace('/<div class="qt-hide">(.*?)<\/div>/', '$1', $content);
            return self::hide_box($content);
        }

        
        function ppo_content_remove_contenteditable($content)
        {
            $content = preg_replace('/<p\s+class=["\']qt-hide["\']>\s*(\[\/?ppo_hide\])\s*<\/p>/i', '$1', $content);
            $content = preg_replace('/<div\s+class=["\']hide-content["\'][^>]*>\s*<div\s+class=["\']hide-title["\'][^>]*>.*?<\/div>\s*/is', '', $content);
            $content = preg_replace('/(\[\/ppo_hide\])\s*<\/div>/i', '$1', $content);
            $content = str_replace(' contenteditable="', ' mce-contenteditable="', $content);
            return $content;
        }



    }
}

new PPO_Hide;
