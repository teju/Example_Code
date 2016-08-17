(function(){
    "use strict";

    jQuery(document).ready(function($) {
        $('#offcanvas-togglers').on('click',function(){
            $("#side-panel").animate({"right": "0px"});
            $("#wrapper").css('position', 'relative').animate({"right": "300px"});
            $(".body-overlay").css({'opacity':'1','z-index':'99'});
            return false;
         });
        $('#offcanvas-toggler').on('click',function(){
            $("#side-panel").animate({"right": "0px"});
            $("#wrapper").css('position', 'relative').animate({"right": "300px"});
            $(".body-overlay").css({'opacity':'1','z-index':'99'});
             return false;
         });
         $('#wrapper').on('click',function(){
            $("#side-panel").animate({"right": "-300px"});
            $("#wrapper").animate({"right": "0px"});
            $(".body-overlay").css({'opacity':'0','z-index':'-1'});
            // return false;
         });
         $("#side-panel-trigger-close").on('click',function(event){
            $("#side-panel").animate({"right": "-300px"});
            $("#wrapper").animate({"right": "0px"});
            $(".body-overlay").css({'opacity':'0','z-index':'-1'});
         });
        //search home
        $('.btn-search').on('click',function(){
            $(".input-search").slideToggle("slow");
        });

        $(".dateclick").datepicker({ dateFormat: 'mm/dd/yyyy' });
        //
        $( '.date1').on('click',function(){
            /* Act on the event */
             $(".datepicker.dropdown-menu:first").show();
        });
        $( '.date2').on('click',function(){
            $(".datepicker.dropdown-menu:eq(1)").show();
        });

        //booking2 
        $(".datetimep").datepicker({ dateFormat: 'mm/dd/yyyy' });

        $(".col-content  .box-dotted").hover(function(){
            $(this).next('.border-dotted').css("opacity", "1");
            }, function(){
            $(this).next('.border-dotted').css("opacity", "0");
        });
        
        //image zoom
    	$("#img_01").elevateZoom({gallery:'gallery_01', cursor: 'pointer', galleryActiveClass: 'active', imageCrossfade: true, loadingIcon: ''}); 
        $(".img_011").bind("click", function(e) { var ez = $('#img_01').data('elevateZoom'); $.fancybox(ez.getGalleryList()); return false; });

        var ocClients = $("#oc-clients-full");
            ocClients.owlCarousel({
                items: 4,
                margin: 150,
                loop: true,
                nav: false,
                autoplay: true,
                dots: false,
                autoplayHoverPause: true,
                responsive:{
                    0:{ items:1 },
                    480:{ items:2 },
                    768:{ items:3 },
                    992:{ items:4 },
                    1200:{ items:4 }
                }
            });

        
        $("#upcoming-events").owlCarousel({
                 autoPlay :true,
               items : 1,
               pagination:true,
               scrollPerPage:true,
        });
        $("#testimonial_carousel").owlCarousel({
            items: 1,
            autoPlay : true,
                //Transitions
            transitionStyle : false,
        });
    });
}) (jQuery);