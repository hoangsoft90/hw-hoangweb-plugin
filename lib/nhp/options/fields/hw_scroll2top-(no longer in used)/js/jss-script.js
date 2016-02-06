jQuery.noConflict();
jQuery(function($) {
 

// Customize Settings: For more information visit www.blogsynthesis.com/plugins/jquery-smooth-scroll/
 
	// When to show the scroll link
	// higher number = scroll link appears further down the page	
	var upperLimit = 100; 
		
	// Our scroll link element
	var scrollElem = $('a#scroll-to-top');
	
	// Scroll Speed. Change the number to change the speed
	var scrollSpeed = 600;
	
	// Choose your easing effect http://jqueryui.com/resources/demos/effect/easing.html
	var scrollStyle = 'swing';
	
/****************************************************
 *													*
 *		JUMP TO ANCHOR LINK SCRIPT START			*
 *													*
 ****************************************************/
	
	// Show and hide the scroll to top link based on scroll position	
	scrollElem.hide();
	$(window).scroll(function () { 			
		var scrollTop = $(document).scrollTop();		
		if ( scrollTop > upperLimit ) {
			$(scrollElem).stop().fadeTo(300, 1); // fade back in			
		}else{		
			$(scrollElem).stop().fadeTo(300, 0); // fade out
		}
	});

	// Scroll to top animation on click
	$(scrollElem).click(function(){ 
		$('html, body').animate({scrollTop:0}, scrollSpeed, scrollStyle ); return false; 
	});

/****************************************************
 *													*
 *	 FOLLOW BLOGSYNTHESIS.COM FOR WORDPRESS TIPS	*
 *													*
 ****************************************************/
 
});