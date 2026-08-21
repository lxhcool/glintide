<?php
if (!class_exists('Nav_builder')) {
class Nav_builder {

    public static $hb_item = array(
        'main-nav' => '主菜单',
        'sub-nav'  => '副菜单', 
        'social'   => '社交按钮',
        'logo'     => '站点LOGO',
        'btna'     => '按钮1',
        'btnb'     => '按钮2',
        'html'     => '自定义HTML',
        'cart'     => '购物车',
        'dark'     => '暗黑切换',
        'search'   => '搜索',
        'user'     => '登录|注册', 
        'msg'      => '用户消息',
    );  

    public static $hb_area = array('top','center','bottom');
    public static $top_area = array('top-start','top-start-right','top-middle','top-end-left','top-end');
    public static $center_area = array('center-start','center-start-right','center-middle','center-end-left','center-end');
    public static $bottom_area = array('bottom-start','bottom-start-right','bottom-middle','bottom-end-left','bottom-end');

    public function __construct(){
        
        add_action('ppo_header_builder_item', array($this, 'ppo_header_builder'));
        add_action('ppo_header_builder_area', array($this, 'ppo_builder_row'));
               
    }


    public static function ppo_header_builder($arr){
        $unique = '___' . $arr['prefix'];
        $nav_items = get_cu('nav-desktop-items');
        $nav_items = is_array($nav_items) ? $nav_items : array();
        $in_builder = self::ppo_in_builder_item($nav_items);
        $in_builder = is_array($in_builder) ? $in_builder : array();
        if(!empty($arr['prefix'])){
            $lists = self::$hb_item;
           foreach($lists as $i => $v){
                if(in_array($i,$in_builder)){
                    $type = 'ppo-item-in-builder';
                } else {
                    $type = 'ppo-builder-item';
                }
                echo '<div class="'.$type.'" data-id="'.$i.'">';
                echo '<span>'.$v.'</span>';
                CSF::field(array('id' => 'nav-desktop-items', 'type' => 'hidden'), $i, $unique, 'field/hbgroup');
                echo '</div>';
            }  
            
        }
    }

    public static function ppo_builder_row($arr){
        $arr = is_array($arr) ? $arr : array();
        $area = self::$hb_area;
        foreach($area as $i => $v){
            echo '<div class="ppo-builder-areas ppo-builder-mode-header no-center-items">';
            echo  '<div id="hb-builder-group-'.$v.'" class="ppo-builder-group ppo-builder-group-horizontal">';
            echo  '<div class="hb-builder-set" set-id="hb-'.$v.'"><i class="ri-settings-3-fill"></i></div>';
            echo  '<ul class="hb-inner">';
            echo  '<li class="hb-inner-left hb-inner-column">';
            echo  '<div class="ppo-builder-items hb-primary-column" data-id="['.$v.'-start]">';
            self::ppo_in_builder($arr,$v.'-start');
            echo  '</div>';
            echo  '<div class="ppo-builder-items hb-sub-column" data-id="['.$v.'-start-right]">';
            self::ppo_in_builder($arr,$v.'-start-right');
            echo  '</div>';
            echo  '</li>';
            echo  '<li class="hb-inner-center hb-inner-column">';
            echo  '<div class="ppo-builder-items" data-count="0" data-id="['.$v.'-middle]">';
            self::ppo_in_builder($arr,$v.'-middle');
            echo  '</div>';
            echo  '</li>';
            echo  '<li class="hb-inner-right hb-inner-column">';
            echo  '<div class="ppo-builder-items hb-sub-column" data-id="['.$v.'-end-left]">';
            self::ppo_in_builder($arr,$v.'-end-left');
            echo  '</div>';
            echo  '<div class="ppo-builder-items hb-primary-column" data-id="['.$v.'-end]">';
            self::ppo_in_builder($arr,$v.'-end');
            echo  '</div>';
            echo  '</li>';
            echo  '</ul>';
            echo  '</div>';
            echo  '</div>';
        }
    }

    public static function ppo_in_builder($arr,$type){
        //return $html;
        if(is_array($arr) && isset($arr['value']) && is_array($arr['value'])){
            $in_builder = array_keys($arr['value']);
            if(in_array($type,$in_builder)){
                $output = $arr['value'][$type];
                //var_dump($output);
                if(is_array($output)){
                    foreach($output as $key => $v){
                        if(!is_array($v) || !isset($v['hb_id'])){
                            continue;
                        }
                        $prefix = isset($arr['prefix']) ? $arr['prefix'] : '';
                        $id = isset($arr['id']) ? $arr['id'] : '';
                        $unique = $prefix.'['.$id.']['.$type.']['.$key.']';
                        $hb_name = isset(self::$hb_item[$v['hb_id']]) ? self::$hb_item[$v['hb_id']] : $v['hb_id'];
                        echo '<div class="ppo-builder-item no-after" data-id="'.$v['hb_id'].'">';
                        echo '<span>'.$hb_name.'</span>';
                        CSF::field(array('id' => 'hb_id', 'type' => 'hidden'), $v['hb_id'], $unique, 'field/hbgroup');
                        echo '<div class="hb-remove"><i class="ri-close-line"></i></div>';
                        echo '</div>';
                       
                    }
                }
            }

        } 
    }

    public static function ppo_in_builder_item($arr){
        $new_arr = array();
        if(is_array($arr)){
            foreach($arr as $items){
                if(is_array($items)){
                    foreach($items as $item){
                        if(is_array($item) && isset($item['hb_id'])){
                            $new_arr[] = $item['hb_id'];
                        }
                    }
                }
            }
        }
        return $new_arr;
    }   
    
    public static function nav_layout(){
        $html = '';
        $data = get_cu('nav-desktop-items');
        $data = is_array($data) ? $data : array();
        $in_builder = array_keys($data);
        $center_nav = get_cu('hb_center_tab');
        $center_nav = is_array($center_nav) ? $center_nav : array();
        $top_nav = get_cu('hb_top_tab');
        $top_nav = is_array($top_nav) ? $top_nav : array();
        $bottom_nav = get_cu('hb_bottom_tab');
        $bottom_nav = is_array($bottom_nav) ? $bottom_nav : array();
        $center_state = self::nav_state($center_nav);
        $top_item = self::$top_area;
        $center_item = self::$center_area;
        $bottom_item = self::$bottom_area;

        $top = array_intersect($top_item , $in_builder);
        if(!empty($top)){
            $html = '<div class="top-nav-row main-nav-item '.self::nav_width($top_nav).'"><div class="nav-bg-box">'.self::nav_top_area($data).'</div></div>';
        }
        $center = array_intersect($center_item , $in_builder);
        if(!empty($center)){        
            $blur = isset($center_nav['glass']) && $center_nav['glass'] ? 'blur' : '';       
            $html .= '<div class="center-nav-row main-nav-item '.self::nav_width($center_nav).' '.$blur.'" '.$center_state.'><div class="nav-bg-box">'.self::nav_center_area($data).'</div></div>';       
        }
        $bottom = array_intersect($bottom_item , $in_builder);
        if(!empty($bottom)){
            $html .= '<div class="bottom-nav-row main-nav-item '.self::nav_width($bottom_nav).'"><div class="nav-bg-box">'.self::nav_bottom_area($data).'</div></div>';       
        }
        
        return '<div class="main-header hb-header"><div class="desktop-nav">'.$html.'</div></div>';
    }

    public static function nav_top_area($arr){
        $arr = is_array($arr) ? $arr : array();
        $area = self::$top_area;
        $html = '';
        $pos = 'top';
        $in_builder = array_keys($arr);
        $html = '<div class="hb-top-nav nav-left-warp">'.self::nav_output('start',$pos,$in_builder,$arr).''.self::nav_output('start-right',$pos,$in_builder,$arr).'</div>';
        $html .= '<div class="hb-top-nav nav-middle-warp">'.self::nav_output('middle',$pos,$in_builder,$arr).'</div>';
        $html .= '<div class="hb-top-nav nav-right-warp">'.self::nav_output('end-left',$pos,$in_builder,$arr).''.self::nav_output('end',$pos,$in_builder,$arr).'</div>';

        return $html;
    }

    public static function nav_center_area($arr){
        $arr = is_array($arr) ? $arr : array();
        $area = self::$center_area;
        $html = '';
        $pos = 'center';
        $in_builder = array_keys($arr);
        $html = '<div class="hb-center-nav nav-left-warp">'.self::nav_output('start',$pos,$in_builder,$arr).''.self::nav_output('start-right',$pos,$in_builder,$arr).'</div>';
        $html .= '<div class="hb-center-nav nav-middle-warp">'.self::nav_output('middle',$pos,$in_builder,$arr).'</div>';
        $html .= '<div class="hb-center-nav nav-right-warp">'.self::nav_output('end-left',$pos,$in_builder,$arr).''.self::nav_output('end',$pos,$in_builder,$arr).'</div>';

        return $html;
    }

    public static function nav_bottom_area($arr){
        $arr = is_array($arr) ? $arr : array();
        $area = self::$bottom_area;
        $html = '';
        $pos = 'bottom';
        $in_builder = array_keys($arr);
        $html = '<div class="hb-bottom-nav nav-left-warp">'.self::nav_output('start',$pos,$in_builder,$arr).''.self::nav_output('start-right',$pos,$in_builder,$arr).'</div>';
        $html .= '<div class="hb-bottom-nav nav-middle-warp">'.self::nav_output('middle',$pos,$in_builder,$arr).'</div>';
        $html .= '<div class="hb-bottom-nav nav-right-warp">'.self::nav_output('end-left',$pos,$in_builder,$arr).''.self::nav_output('end',$pos,$in_builder,$arr).'</div>';

        return $html;
    }

    public static function nav_output($area,$pos,$in_builder,$arr){
        $html = '';
        $type = $pos.'-'.$area;
        if(in_array($type,$in_builder) && isset($arr[$type]) && is_array($arr[$type])){
            $data = $arr[$type];
            foreach($data as $item){
                if(!is_array($item) || !isset($item['hb_id'])){
                    continue;
                }
                $data_id = $item['hb_id'];
                $html .= self::hb_item_type($data_id);
            }
            return '<div class="nav-item nav-'.$area.'">'.$html.'</div>';
        }

        return $html;
    }

    public static function hb_item_type($type){
        if($type == 'main-nav'){
            return self::mainnav();
        } else if($type == 'logo'){
            return self::logo();
        } else if($type == 'btna'){
            return self::btna();
        } else if($type == 'btnb'){
            return self::btnb();
        } else if($type == 'sub-nav'){
            return self::subnav();
        } else if($type == 'html'){
            return self::html();
        } else if($type == 'dark'){
            return self::dark();
        } else if($type == 'search'){
            return self::search();
        }
    }

    public static function nav_state($data){
        if(is_array($data)){
            $type = isset($data['sticky']) ? $data['sticky'] : 'sticky';
            if($type == 'sticky'){
                return 'data-pix-sticky="top" data-pix-sticky-start="500"';
            } else if($type == 'showup'){
                return 'data-pix-sticky="showup" data-pix-sticky-start="500"';
            } else {
                return false;
            }
        }
       
    }

    public static function nav_width($data){
        if(is_array($data)){
            $type = isset($data['type']) ? $data['type'] : 'norwidth';
            return $type;
        }
    }

    /*------------------------------------------------------------------------------------------------
    /* 所有元素
    */

    public static function mainnav(){
        $menu_id = get_cu('main_nav_id');
        $data = get_cu('main_nav_tab');
        $sub_e = !empty(get_cu('sub_nav_tab')['effects']) ? get_cu('sub_nav_tab')['effects'] : 'normal';
        if(is_array($data)){
            $menu_type = $data['effects_type'];
            $menu_e = $data['effects'];
            if(isset($menu_id)){
                $primary_nav =  wp_nav_menu( array(
                    'menu' => $menu_id,
                    'menu_id'        => 'primary_menu',
                    'echo'           => false,
                    //'walker' => new pix_Walker_Nav_Menu()
                ) );
            
                $html = $primary_nav;               
            } else {
                $html = '请前往后台设置顶部主导航';
            }
            return '<div class="primary-nav '.$menu_type.' '.$menu_e.' '.$sub_e.'">'.$html.'</div>';
        }
       
    }

    public static function subnav(){
        $menu_id = get_cu('sec_nav_id');
        $data = get_cu('sec_nav_tab');
        $sub_e = !empty(get_cu('secsub_nav_tab')['effects']) ? get_cu('secsub_nav_tab')['effects'] : 'normal';
        if(is_array($data)){
            $menu_type = @$data['sub_effects_type'];
            $menu_e = $data['effects'];
            if(isset($menu_id)){
                $sec_nav =  wp_nav_menu( array(
                    'menu' => $menu_id,
                    'menu_id'        => 'sec_menu',
                    'echo'           => false,
                    //'walker' => new pix_Walker_Nav_Menu()
                ) );
            
                $html = $sec_nav;               
            } else {
                $html = '请前往后台设置顶部副导航';
            }
            return '<div class="sec-nav '.$menu_type.' '.$menu_e.' '.$sub_e.'">'.$html.'</div>';
        }
       
    }

    public static function logo(){
        $html = '';
        $data = get_cu('logo_tab');
        if(is_array($data)){
            $img = $data['hb-site-logo'];
            $img = !empty($img) ? $img : pix_global_logo_url('dark');
            $logo_show = !empty($img) ? 'logo-show' : 'no-logo';
            $title = !empty($data['title']) ? $data['title'] : pix_global_logo_text();
            $des = $data['des'];
            $logo_pos = $data['logo_pos'];
            $logo_algin = $data['logo_algin'];
            $html = '<div class="hb-logo '.$logo_show.' '.$logo_pos.' '.$logo_algin.'">';
            if(!empty($img)){
                $html .= '<div class="hb-logo-box"><a href="'.home_url().'"><img src="'.$img.'"></a></div>';
            }
            $html .= '<div class="hb-logo-title '.$logo_algin.'">';
            if($data['title_on']){
                $html .= '<a href="'.home_url().'"><div class="title">'.$title.'</div></a>';
            }
            if($data['des_on']){
                $html .= '<div class="des">'.$des.'</div>';
            }
            $html .= '</div>';
            $html .= '</div>';    
    
            return $html;
        }

    }

    public static function btna(){
        $data = get_cu('btna_tab');
        if(is_array($data)){
            $url = !empty($data['url']) ? $data['url'] : '#';
            $title = !empty($data['title']) ? $data['title'] : '按钮';
            $icon = !empty($data['icon']) ? '<i class="'.$data['icon'].'"></i>' : '';
            $target = $data['new'] ? 'target="_blank"' : '';
            $rel = $data['nofollow'] ? 'rel="nofollow"' : '';

            $html = '';
            $html = '<div class="hb-btn hb-btna">';
            $html .= '<a href="'.$url.'" '.$target.' '.$rel.'>';
            $html .= ''.$icon.'<span class="btn-title">'.$title.'</span>';
            $html .= '</a>';
            $html .= '</div>';

            return $html;
        }
    }

    public static function btnb(){
        $data = get_cu('btnb_tab');
        if(is_array($data)){
            $url = !empty($data['url']) ? $data['url'] : '#';
            $title = !empty($data['title']) ? $data['title'] : '按钮';
            $icon = !empty($data['icon']) ? '<i class="'.$data['icon'].'"></i>' : '';
            $target = $data['new'] ? 'target="_blank"' : '';
            $rel = $data['nofollow'] ? 'rel="nofollow"' : '';

            $html = '';
            $html = '<div class="hb-btn hb-btnb">';
            $html .= '<a href="'.$url.'" '.$target.' '.$rel.'>';
            $html .= ''.$icon.'<span class="btn-title">'.$title.'</span>';
            $html .= '</a>';
            $html .= '</div>';

            return $html;
        }
    }

    public static function html(){
        $data = get_cu('html_tab');
        $html = '';
        if(is_array($data)){
            $content = !empty($data['html_content']) ? $data['html_content'] : '自定义HTML内容';
            $html = '<div class="hb-html hb-html-content">'.$content.'</div>';
            return $html;
        }
    }

    public static function dark(){
        $data = get_cu('dark_tab');
        $html = '';
        $style = 'line';
        if(is_array($data)){
            $style = !empty($data['icon_type']) ? $data['icon_type'] : 'line';
            $html = '<div class="hb_dark hb_dark_box">
                        <div class="hb_dark_inner">
                            <i class="ri-sun-'.$style.' hb-sun-icon"></i>
                            <i class="ri-moon-'.$style.' hb-moon-icon"></i>
                        </div>
                    </div>';
            return $html;
        }
    }

    public static function search(){
        return '<button type="button" class="hb-search pix-search-trigger" aria-label="搜索"><i class="ri-search-line"></i></button>';
    }

}
}

new Nav_builder;
