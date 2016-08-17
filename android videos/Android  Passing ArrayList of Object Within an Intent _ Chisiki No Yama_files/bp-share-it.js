jQuery(document).ready(function(){
		jQuery('.bp-share-it-button').live('click', function(){
			jQuery(this).toggleClass('active').next().slideToggle('fast');	
	});
		jQuery('.bp-share-it-button-blog').live('click', function(){
			jQuery(this).toggleClass('active').next().slideToggle('fast');	
	});
		jQuery('.bp-share-it-button-forum').live('click', function(){
			jQuery(this).toggleClass('active').next().slideToggle('fast');			
	});
		jQuery('.bp-share-it-button-group.generic-button').live('click', function(){
			jQuery(this).toggleClass('active').next().slideToggle('fast');		
	});
	
    	jQuery('a.new-window').live('click', function(){
        	window.open(this.href,'newWindow','width=700,height=350');
        	return false;
    });
    	jQuery('a.new-window-digg').live('click', function(){
        	window.open(this.href,'newWindow','width=720,height=550');
        	return false;
    });
	
});
