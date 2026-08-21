;(function( $, window ) {

  var PPO   = PPO || {};
  PPO.funcs = {};

  PPO.vars  = {
    onloaded: false,
    $body: $('body'),
    $window: $(window),
    $document: $(document),
    is_confirm: false,
  };

  PPO.helper = {

    preg_quote: function( str ) {
      return (str+'').replace(/(\[|\])/g, "\\$1");
    },

    //update sortble
    name_nested_replace: function( $selector, field_id ) {

      var checks = [];
      var regex  = new RegExp(PPO.helper.preg_quote(field_id +'[\\d+]'), 'g');

      $selector.find(':radio').each(function() {
        if ( this.checked || this.orginal_checked ) {
          this.orginal_checked = true;
        }
      });

      $selector.each( function( index ) {
        $(this).find(':input').each(function() {
          this.name = this.name.replace(regex, field_id +'['+ index +']');
          if ( this.orginal_checked ) {
            this.checked = true;
          }
        });
      });

    },


  }

   //导航构建器
   $.fn.csf_field_hbgroup = function() {
    return this.each( function() {
          var $this           = $(this),
          $ppo_builder     = $this.find('.ppo-header-builder-item');
          var list       = document.getElementById('ppo-builder-list-box');
          //左边列表
          new Sortable(list, { 
            group: {
              name: 'shared',
              pull: 'clone',
              put: false 
          },    
            animation: 0,
            draggable: ".ppo-builder-item",
            ghostClass: "sortable-ghost",
            dragClass: "sortable-drag",
            filter: ".hb-remove",
            sort: false,
            forceFallback: true,
            fallbackOnBody: true,
            fallbackTolerance:5,
            
         
            onStart: function (evt) {
               //移动拖拽对象到光标中心
                var drag = $('.ppo-builder-item.sortable-drag');
                var cursorX = evt.originalEvent.clientX; 
                var cursorY = evt.originalEvent.clientY;
                var itemw = drag.outerWidth();
                var itemh = drag.outerHeight();
                var x = cursorX - itemw/2;
                var y = cursorY - itemh/2;
                drag.css({'left':x,'top':y});
                
                $('body').addClass('ppo-builder-item-draging');

                $('#customize-preview').prepend('<div class="darg-overlay"></div>');

            },

            onEnd: function (evt) {
              $('body').removeClass('ppo-builder-item-draging');
              $('.darg-overlay').remove();
              //console.log(evt);

              var to_items = $(evt.to);
              var c_count = to_items.children('.ppo-builder-item').length;
              if(to_items.parent().hasClass('hb-inner-center')){
                to_items.attr('data-count',c_count);
                if(c_count > 0) {
                   to_items.parents('.ppo-builder-areas').addClass('has-center-items').removeClass('no-center-items');
                }
              }

              //移除抓手，添加删除按钮
              var item = $(evt.item);
              if(item.parent().hasClass('ppo-builder-items')){
                item.addClass('no-after');
                item.append('<div class="hb-remove"><i class="ri-close-line"></i></div>');
              }
                           
              console.log(evt);

              //更新数据
              var str = item.find('input').attr('name');
              var id = item.data('id');
              var item_area = item.parent().data('id');
              var count = evt.newIndex;
              newStr = str.replace("___","").replace(/\][^]*$/, "]"+item_area+"["+count+"][hb_id]");
              item.find('input').attr('name',newStr);

              $ppo_builder.csf_customizer_refresh();
              //console.log(newStr);
            },

            onClone: function (evt) {
              //console.log(evt);
              evt.clone.classList.replace('ppo-builder-item','ppo-item-in-builder');
              
            
            },
            
            
          });

        
          var hb_area = $('.ppo-builder-items');
          $(hb_area).each(function (i,e) {
             Sortable.create(e, {
                  group: 'shared',     
                  animation: 0,
                  ghostClass: "sortable-ghost",
                  dragClass: "sortable-drag",
                  filter: ".hb-remove",
                  forceFallback: true,
                  fallbackOnBody: true,
                  fallbackTolerance:5,

                onEnd: function (evt) {
                  var from = $(evt.from);  // dragged HTMLElement
                  var to = $(evt.to);
                  var area = from.parents('.ppo-builder-areas');
                  var to_area = to.parents('.ppo-builder-areas');
                  // 更新元素个数
                  var count = area.find('.hb-inner-center .ppo-builder-items').children('.ppo-builder-item').length;
                  area.find('.hb-inner-center .ppo-builder-items').attr('data-count',count);
                  if(count < 1){
                    area.addClass('no-center-items').removeClass('has-center-items');
                  } else {
                    area.addClass('has-center-items').removeClass('no-center-items');
                  }
                  
                  var to_count = to_area.find('.hb-inner-center .ppo-builder-items').children('.ppo-builder-item').length;
                  to_area.find('.hb-inner-center .ppo-builder-items').attr('data-count',to_count);
                  if(to_count < 1){
                    to_area.addClass('no-center-items').removeClass('has-center-items');
                  } else {
                    to_area.addClass('has-center-items').removeClass('no-center-items');
                  }

             
                  // 中间为空时，移动元素
                  $ppo_builder.move_hb_item();

                  $ppo_builder.update_hb_item();
                  // 更新字段值
                  $ppo_builder.csf_customizer_refresh();
                
              }, 

             


                
              });
              
          });
          
          //移除放置元素
          var hb_remove = function( e ) {

            e.preventDefault();
    
            //var count = hb_warp.children('.slot-option-content').length;
            var id = $(this).parents('.ppo-builder-item').data('id');
            var items = $(this).parents('.ppo-builder-items');
            $(this).closest('.ppo-builder-item').remove();
            var item = $('.ppo-builder-list-box .ppo-item-in-builder[data-id="'+id+'"]');
            item.removeClass('ppo-item-in-builder').addClass('ppo-builder-item');
            var count = items.find('.ppo-builder-item').length;          
            items.attr('data-count',count);
            $ppo_builder.move_hb_item();
            $ppo_builder.update_hb_item();
            $ppo_builder.csf_customizer_refresh();
          
          };

          $('.ppo-builder-item').on('click', '.hb-remove', hb_remove);

          //点击跳转单独选项
          $(document).on('click','.ppo-item-in-builder , .ppo-builder-items .ppo-builder-item',function(e){
              var id = $(this).data('id');
              var focus_item = 'hb-'+id+'';
              wp.customize.section(focus_item).focus();
          }); 

          //行跳转
          $(document).on('click','.hb-builder-set',function(e){
            var id = $(this).attr('set-id');
            wp.customize.section(id).focus();
        });
       
        });      
}

//update builder item
$.fn.update_hb_item = function(){
  var items = $(this).find('.ppo-builder-items');
  var unique = 'ppo_customizer';
  //console.log($hb_item);
  items.each(function(){
    var area = $(this).data('id');
    $(this).find(':input[name!="_pseudo"]').each(function(index){
      this.name = 'ppo_customizer[nav-desktop-items]'+area+'['+ index +'][hb_id]';
  });
  });
  
}
   
//移动元素 中间模块为空的时候
$.fn.move_hb_item = function() {
  var item = $(this).find('.hb-sub-column');
  item.each(function(){
     var center = $(this).parent().siblings('.hb-inner-center').children();
     var count = center.children('.ppo-builder-item').length;
     var area = $(this).parent();
     var sub_item = $(this).children();
     if(count < 1 ){
        if(area.hasClass('hb-inner-left')){
          $(this).siblings('.hb-primary-column').append(sub_item);
        } else {
          $(this).siblings('.hb-primary-column').prepend(sub_item);
        }
     }
  });
}

//初始化计数
$.fn.check_hb_count = function() {
    var c = $('.hb-inner-center');
    c.each(function(){
        var count = $(this).find('.ppo-builder-item').length;
        $(this).children().attr('data-count',count);
        if(count > 0) {
          $(this).parents('.ppo-builder-areas').addClass('has-center-items').removeClass('no-center-items');
       }
     $('body').addClass('pix-options');  
    }); 
}  


    //初始化
    $(document).ready( function() {

      $("#customize-control.customize-control-csf").ppo_body_class();
      $(".ppo-header-builder-box").csf_field_hbgroup();
      $(".ppo-header-builder-item").check_hb_count();
    });

    //打开面板动作
    $.fn.ppo_body_class = function() {

      var header_group = $('#sub-accordion-section-nav-group');

      var navBuilderPanel = wp.customize.panel('nav-builder');
      if (navBuilderPanel) {
        navBuilderPanel.expanded.bind(function (isExpanded) {
          if( isExpanded ) {
            $('body').addClass('ppo-nav-builder-is-active');
            header_group.addClass('ppo-nav-builder-active');
          } else {
            $('body').removeClass('ppo-nav-builder-is-active');
            header_group.removeClass('ppo-nav-builder-active');
          }

          var $section = $('#customize-control-ppo_customizer-hblist');

          // 获取名为 "new_section" 的部分元素
          var $newSection = $('#accordion-section-hb-draglist');
    
          // 将 my_section 部分的所有控件移动到 new_section 部分中
          $section.find('.csf-field-hblist').appendTo($newSection);
          $newSection.find('h3').remove();
        });
      }

     

    };


})( jQuery, window );

