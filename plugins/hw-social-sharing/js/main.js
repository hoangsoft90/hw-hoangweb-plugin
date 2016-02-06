if(typeof $ == 'undefined') var $ = jQuery.noConflict();
/**
@init resposive socials sharing with Socialite
*/
jQuery(document).ready(function() {
	var isload_Socialite = false;
	/*unitilities*/
	function load_Socialite(){
		if(!isload_Socialite && typeof Socialite != 'undefined') {
			Socialite.load(jQuery(this)[0]);
			isload_Socialite = true;
		}
	}
	
	 //event: responsible for triggering the social buttons
	jQuery('.wrap_socialite').one('mouseenter', function() {
		load_Socialite();
	});
	//auto initial socials buttons 
	setTimeout(function(){
		 load_Socialite();
	 },2000);
});