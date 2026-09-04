<?php 

function footer_menu_item($position){
    if($position == 'left'){
        $data = get_op('footmenu_left');
    } else {
        $data = get_op('footmenu_right');
    }

    $html = '';
    if(is_array($data)){
        foreach($data as $list){
            $type = isset($list['fmenu_type']) ? $list['fmenu_type'] : 'normal';
            $icon = isset($list['icon']) ? $list['icon'] : 'ri-home-line';
            $title = $list['title'] ? $list['title'] : '';
            $link = isset($list['link']) ? $list['link'] : '';
            $new = $list['open_new'] == true ? 'target="_blank"' : '';
            if($type == 'normal') {
                $html .= '<li><a href="'.$link.'" '.$new.'><i class="'.$icon .'"></i><span class="title">'.$title.'</span></a></li>';
            } else if($type == 'dark'){
                $html .= '<li class="t_dark"><a><i class="'.$icon .'"></i><span class="title">'.$title.'</span></a></li>';
            } else if($type == 'top'){
                $html .= '<li><a href="#page" uk-scroll><i class="'.$icon .'"></i><span class="title">'.$title.'</span></a></li>';
            } else if($type == 'search'){
                $html .= '<li><a href="#search_modal" uk-toggle><i class="'.$icon .'"></i><span class="title">'.$title.'</span></a></li>';
            }
            
        }

        return $html;
    }
}

?>
<div class="footer_menu">
    <div class="inner">
        <div class="left item">
            <?php echo footer_menu_item('left'); ?>
        </div>
        <?php if(is_user_logged_in()){ ?>
        <div class="center"><a class="mobile_edit" uk-toggle="target: #create_post_box" tabindex="0" aria-expanded="false"><i class="ri-add-line"></i></a></div>
        <?php } ?>
        <div class="right item">
            <?php echo footer_menu_item('right'); ?>
        </div>
    </div>
</div>