   var $pager=jQuery('<div class="yjl-pager"></div>');
   var $cPager=jQuery('<div class="yjl-pager"></div>');
   var $prev=jQuery('<span class="yjl-prev">'+ (yjlSettings.prev==''? 'Prev':yjlSettings.prev) +'</span>');
   var $next=jQuery('<span class="yjl-next">'+ (yjlSettings.next==''? 'Next':yjlSettings.next) +'</span>');
   var $moreFront=jQuery('<span></span>');
   var $moreEnd=jQuery('<span></span>');
   var $commentlist=jQuery('.commentlist');
   var $respond=jQuery('#respond');
   var $message=jQuery('<span class="yjl-mes"></span>').appendTo("#commentform");;
   var $list=$commentlist.children();
   var totalCom=$list.length;
   var $textarea=$respond.find('#comment').attr('rows','4');
   var comPerPage=parseInt((yjlSettings.comPerpage==''? '5':yjlSettings.comPerpage),10);
   
   var currentPage=0,$number,numPerPage,totalPage,$reply; 

   //repostion comment form if enabled
   if(yjlSettings.repForm!='disable'){
       $respond.insertBefore($commentlist.prev()); 
   }
   
   function getTotal($comlist){
       $list=$comlist.children();
       totalCom=$list.length;
       totalPage=Math.ceil(totalCom/comPerPage);
   }
   
   if(yjlSettings.pagination!='disable')pagination();
   function pagination(){
       getTotal($commentlist);
       numPerPage=parseInt((yjlSettings.numPerpage==''? '5':yjlSettings.numPerpage),10);
       if(totalPage<numPerPage)numPerPage=totalPage;
       $moreFront.add($moreEnd).empty();

       if(yjlSettings.pagerPos!='after')$pager.empty().insertBefore($commentlist);
       else $pager.empty().insertAfter($commentlist);
  
       if(comPerPage<totalCom){       
       for (var i=0;i<totalPage;i++){
            jQuery('<span class="page-number"></span>').text(i+1)
              .bind('click',{newPage:i}, function(event){
                currentPage=event.data['newPage'];
                $number.eq(currentPage).addClass('currentPager').siblings().removeClass('currentPager');
                rePagCom();              
                rePager();                
            }).appendTo($pager);
         }
        //select a set of page-numbers for later uses
        $number=$pager.find('span.page-number');

         //if there are more page-numbers front
         jQuery('<span class="yjl-more"></span>').appendTo($moreFront)
            .text('1').click(function(){$number.eq(0).trigger('click');});
         $moreFront.append(' ... ').prependTo($pager);
         $prev.prependTo($pager).click(function(){
             if(currentPage>0)$number.eq(currentPage).prev().trigger('click');
         });
         
         $moreEnd.append(' ... ').appendTo($pager);
         jQuery('<span class="yjl-more"></span>').appendTo($moreEnd)
            .text(totalPage+'').click(function(){$number.eq(totalPage-1).trigger('click');});
         $next.appendTo($pager).click(function(){
             if(currentPage<totalPage-1)$number.eq(currentPage).next().trigger('click');
          }); 
         rePager();
         rePagCom();
       }   
   }//end of pagination function
   
   /*
    *repage all comments
    */
   function rePagCom(){
       $list.hide().slice(currentPage*comPerPage,(currentPage+1)*comPerPage).fadeIn();
   }
 
   /*
    *show and hide page-numbers
    */
   function rePager(){
       $number.eq(currentPage).addClass('currentPager');
       if(currentPage<(numPerPage+1)/2){
          $number.hide().slice(0,numPerPage).show();
       }
       else if(currentPage+1>totalPage-(numPerPage+1)/2){
          $number.hide().slice(totalPage-numPerPage,totalPage+1).show();  
       }
       else{
          $number.hide().slice(currentPage-(numPerPage-1)/2  ,currentPage+(numPerPage-1)/2+1).show();
       }
       if(currentPage==0)$prev.removeClass('yjl-prev').addClass('gray'); 
       else $prev.addClass('yjl-prev').removeClass('gray');

       if(currentPage==totalPage-1)$next.removeClass('yjl-next').addClass('gray'); 
       else $next.addClass('yjl-next').removeClass('gray');
   
       if(currentPage+(numPerPage-1)/2<totalPage-1 && numPerPage!=totalPage)
          $moreEnd.show();else $moreEnd.hide();   
       if(currentPage-(numPerPage-1)/2>0 && numPerPage!=totalPage)
             $moreFront.show();else $moreFront.hide();
       if(yjlSettings.pagerPos=='both')
          $cPager.insertAfter($commentlist).empty().append($pager.children().clone(true));   
   }
   
   //track a reply comment
   jQuery('.comment-reply-link').live('click',function(){
       $reply=true;
   });
   var $cancel=jQuery('#cancel-comment-reply-link').click(function(){
       $reply=false;
   });

   /*
    *if Ajax comment posting is eanbled
    */
   jQuery('#commentform').submit(function(){
       jQuery.ajax({
         beforeSend:function(xhr){
            xhr.setRequestHeader("If-Modified-Since","0");
            $message.empty().append('<img src="'+yjlSettings.gifUrl+'" alt="processing...">');
         },
         type:'post',
         url:jQuery(this).attr('action'),
         data:jQuery(this).serialize(),
         dataType:'html',
         error:function(xhr){
             if(xhr.status==500){
               $message.empty().append(xhr.responseText.split('<p>')[1].split('</p>')[0]);
             }
             else if(xhr.status=='timeout'){
               $message.empty().append((yjlSettings.timeOut!=''?yjlSettings.timeOut:'Error:Server time out,try again!'));
             }
             else{
               $message.empty().append((yjlSettings.fast!=''?yjlSettings.fast:'Please slow down,you are posting to fast!'));
             }
         },
         success:function(data){
            $message.empty().append((yjlSettings.thank!=''?yjlSettings.thank:'Thank you for your comment!'));
            $newComList=jQuery(data).find('.commentlist');
            if(totalCom>0){
               if($reply)$cancel.trigger('click');
               else {
                   if(yjlSettings.order=='desc')currentPage=0;
                   else { getTotal($newComList);currentPage=totalPage-1;}
               }
               if(yjlSettings.pagination=='disable' || yjlSettings.pagerPos=='after')
                        $commentlist.prev().replaceWith($newComList.prev());
               else $commentlist.prev().prev().replaceWith($newComList.prev());                     
               $commentlist.replaceWith($newComList);                            
            }else{
               if(yjlSettings.repForm=='disable')$newComList.prev().andSelf().insertBefore($respond);
               else $newComList.prev().andSelf().insertAfter($respond);
            }
            $commentlist=$newComList;
            if(yjlSettings.pagination!='disable')pagination();
            $textarea.val(''); 
         }
       });//end of ajax
      return false;
   });//end of submit function
   
   if(yjlSettings.autoGrow!='disable'){
      $textarea.autoResize({
       // On resize:
       onResize : function() {
        jQuery(this).css({opacity:0.8});
      },
      // After resize:
      animateCallback : function() {
        jQuery(this).css({opacity:1});
      },
      // Quite slow animation:
      animateDuration : 300,
      // More extra space:
      extraSpace : 20
      });
   }