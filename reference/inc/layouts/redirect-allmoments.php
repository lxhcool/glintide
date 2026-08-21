<?php
get_header(); 

$mo_tag = get_moments_tag_arr();
$moment_label = ppo_moment_label('moment');
$owner_label = ppo_moment_label('owner');
$user_label = ppo_moment_label('user');
?>

<div class="pix-content allmo-warp pix-modern pix-modern-moment pix-moment-all-page">
    <div class="allmo-content pix-moment-all-content">
    <div class="pix-moment-all-grid">
        <div class="pix-moment-all-sidebar">
            <div class="left-nav pix-moment-all-nav">
                <?php 

                    foreach($mo_tag as $v){
                        if(check_motag_child($v)){
                            $section_id = 'pix-moment-all-section-'.md5($v);
                            echo '<li class="pix-moment-all-nav-item"><span class="allmo-tags pix-moment-all-nav-link" data="'.esc_attr($v).'" data-target="'.esc_attr($section_id).'">'.$v.'</span></li>';
                        }
                    }
                
                ?>
            </div>
        </div>
        <div class="pix-moment-all-main-col">
            <div class="right-content pix-moment-all-main">
                <?php 
                    foreach($mo_tag as $v){
                        $terms = get_terms( array(
                            'taxonomy'   => 'moments',
                            'hide_empty' => false,
                            'meta_query' => array(
                                array(
                                    'key' => 'ppo_moments_tag',
                                    'value' => $v,
                                    'compare' => '=',
                                ),
                            )
                        ) );
                        if(is_array($terms) && !empty($terms)){
                            $section_id = 'pix-moment-all-section-'.md5($v);
                            echo '<div class="content-inner pix-moment-all-section" id="'.esc_attr($section_id).'" data-section="'.esc_attr($v).'">';
                            echo '<div class="mos-title pix-moment-all-title">'.$v.'</div>';
                            echo '<div class="inner-list pix-moment-all-list">';

                        if(is_array($terms) && !empty($terms)){
                            $def = THEME_URL.'/img/modef.png';
                            foreach($terms as $term){
                                $term_id = $term->term_id;
                                $mo_data = get_mo_num_data($term_id);
                                $thum = get_term_meta( $term_id, 'mo_cat_img', true );
                                $thum = $thum ? $thum : $def;
                                $user_id = get_term_meta( $term_id, 'mo_owner', true );
                                $user_id = $user_id ? $user_id : 1;
                                $user_info = get_userdata($user_id);
                                $avatar = get_u_avatar($user_id,'img');
                                $ofi = $user_id == 1 ? '<small>官方</small>' : '';
                                echo '<div class="allmo-tags-item pix-moment-all-card rounded" catid="'.$term_id.'">
                                            <a class="link-cover pix-moment-all-card-link" href="'.esc_url(get_term_link($term_id, 'moments')).'"></a>
                                            <div class="left pix-moment-all-card-cover"><img src="'.$thum.'" class="cover-img rounded"></div>
                                            <div class="right pix-moment-all-card-body">
                                                <div class="title pix-moment-all-card-title">'.$term->name.'</div>
                                                <div class="circle-owner pix-moment-all-card-owner pix-moment-all-card-avatar">'.$avatar.''.$user_info->display_name.'('.esc_html($owner_label).')</div>
                                            </div>
                                            <div class="bottom pix-moment-all-card-meta">
                                                <div class="count-mo pix-moment-all-card-count">'.$term->count.esc_html($moment_label).' <span>·</span> '.$mo_data['join'].esc_html($user_label).'</div>
                                                <div class="info pix-moment-all-card-badge pix-moment-all-card-official">'.$ofi.'</div>
                                            </div>
                                            </div>';
                            }
                        }

                            echo '</div></div>';
                        }
                    }
                ?>
            </div>
        </div>
    </div>
        
    </div>
</div>

<?php
get_footer();  
