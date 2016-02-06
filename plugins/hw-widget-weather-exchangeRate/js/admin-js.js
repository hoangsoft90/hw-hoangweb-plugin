var hw_wexr = {
	showMore: function(obj,name,jid){
		if(1||name == 'c_exr'){
			if($(obj).is(':checked')) $('#'+jid).show();else $('#'+jid).hide();
		}
	}
};