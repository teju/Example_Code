/*! jQuery posts.js
  Implementation of Load More Posts functionality 
  (inspired by http://www.problogdesign.com/wordpress/load-next-wordpress-posts-with-ajax/)
  Author: Thomas W (themezee.com)
*/
jQuery(document).ready(function($) {
	
	// Variables passed from WP Query
	var loadMoreText = themezeeLoadPostsParams.loadMoreText;
	var loadingText = themezeeLoadPostsParams.loadingText;
	var noMoreText = themezeeLoadPostsParams.noMoreText;
	var pageNum = parseInt(themezeeLoadPostsParams.startPage) + 1;
	var maxPages = parseInt(themezeeLoadPostsParams.maxPages);
	var nextLink = themezeeLoadPostsParams.nextLink;
	
	// Add "load more posts" button with javascript
	if(pageNum <= maxPages) {
		
		$('#frontpage-posts-load-more')
			.append('<div class="frontpage-posts-placeholder-'+ pageNum +'"></div>')
			.append('<p id="frontpage-posts-load-more-button"><a href="#">' + loadMoreText + '</a></p>');
			
	}
	
	/* Load new posts when button is clicked */
	$('#frontpage-posts-load-more-button a').click(function() {
	
		// Check if there are still posts to load
		if(pageNum <= maxPages) {
		
			$(this).text(loadingText);
			
			$('.frontpage-posts-placeholder-'+ pageNum).load(nextLink + ' .type-frontpage-post',
				function() {
					
					// Update page number and nextLink.
					pageNum++;
					nextLink = nextLink.replace(/\/page\/[0-9]*/, '/page/'+ pageNum);
					
					// Add a new placeholder, for when user clicks again.
					$('#frontpage-posts-load-more-button')
						.before('<div class="frontpage-posts-placeholder-'+ pageNum +'"></div>')
					
					// Update the button message.
					if(pageNum <= maxPages) {
						$('#frontpage-posts-load-more-button a').text(loadMoreText);
					} else {
						$('#frontpage-posts-load-more-button a').addClass('no-posts');
						$('#frontpage-posts-load-more-button a').text(noMoreText);
					}
					
				}
			);
		}	
		
		return false;
	});
});