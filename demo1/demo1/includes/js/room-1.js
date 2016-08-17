(function () {
    "use strict";
/*Back to top*/
$(document).ready(function() {

    var offset=250, // At what pixels show Back to Top Button
    scrollDuration=300; // Duration of scrolling to top
        $(window).scroll(function() {
	    if ($(this).scrollTop() > offset) {
                $('.back-top').fadeIn(500); // Time(in Milliseconds) of appearing of the Button when scrolling down.
                } else {
		$('.back-top').fadeOut(500); // Time(in Milliseconds) of disappearing of Button when scrolling up.
		}		
	});

	// Smooth animation when scrolling
    $('.back-top').on('click',function(){
	event.preventDefault();
            $('html, body').animate({
	        scrollTop: 0}, scrollDuration);
    
                });
	$(".date-time").datepicker({ dateFormat: 'mm/dd/yyyy' });
        //
    $('.date1').on('click',function(){
        /* Act on the event */
         $(".datepicker.dropdown-menu:first").show();
    });
    $('.date2').on('click',function(){
        $(".datepicker.dropdown-menu:eq(1)").show();
    });
	});
}());
