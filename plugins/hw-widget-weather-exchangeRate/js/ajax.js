if(typeof $ == 'undefined') var $ = jQuery;
/**
 * display weather information
 * @param value
 * @param nonce
 * @param holder
 */
function wexr_Weather(value, nonce,holder)
{
	var loadstatustext = "<div style='text-align:center'><img src='"+myAjax.wexr_plugin_url+"/images/ajax-loader.gif'/></div>";
	jQuery('#'+holder).html(loadstatustext);
	jQuery.ajax({
		url: myAjax.ajaxurl,
		data:{post_id:'1',id:value,nonce: nonce,action:'hwwexr'},
		success:function(data){
			jQuery('#'+holder).html(data);
		}
	});
}
