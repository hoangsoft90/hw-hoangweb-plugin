if(typeof $=='undefined') 
	var $ = jQuery.noConflict();
/**
 * @class hwYahooskype
 */
function hwYahooskype(){
	var self = this;	//this instance
	var togState;
	arguments.callee.data= arguments.callee.data || {};
	
	this.nick = null;	//nick name
	this.itemData = null;	//contain jquery object of item ui & some elements in it
	
	//console.log(data);
	/*-----static methods-------*/
	/**
	 * get one staff
	 * @param int id: staff id
	 */
	arguments.callee.get = function(base_id,id){	//inside methed, don't repeat arguments.callee to get property of this class 		
		if(hwYahooskype.data[base_id][id]) return hwYahooskype.data[base_id][id];else return hwYahooskype.data[base_id];
	}
	/**
	 * create new instance for new staff
	 * @param int index: index of staff
	 * @param base_id: refer current widget id
	 * @param jquery object 'itemEle' to item container
	 */
	arguments.callee.create = function(index,base_id,itemEle){
		//init
		if(index !== undefined) {
			if(!hwYahooskype.data[base_id]) hwYahooskype.data[base_id] = [];	//for current widget
			hwYahooskype.data[base_id][parseInt(index)] = new hwYahooskype();	//save this instance
			
			hwYahooskype.data[base_id][parseInt(index)].itemData = {
					ui:itemEle, 
					//parse major elements
					inputPos: function(){return jQuery(itemEle).find('.position');}, 
					label: function (){return jQuery(itemEle).find('.positionText');}
				};
			
		}
	};
	/**
	 * sort staffs base on sortable by user from jquery sortable plugin
	 * @param string base_id: id of widget
	 * @param Array orderValues: list sorted index
	 */
	arguments.callee.sortableItems_change = function(base_id,orderValues){
		var item;
		orderValues.clean(undefined);	//remove all null value
		for(var i in orderValues){
			if(!jQuery.isNumeric(orderValues[i])) continue;
			item = hwYahooskype.get(base_id,orderValues[i]);
			if(item){
				jQuery(item.itemData.inputPos()).attr('value',i);	//save position in input tag
				jQuery(item.itemData.label()).html(i);	//display index position
			}
		}
	};
	/**
	 * custom data return in jquery sortable
	 * @param object|array sortableObj: jquery sortable selector or sortable toArray
	 * @param string attr: get value from this attribute will be return of sorted list 
	 */
	arguments.callee.get_sortable_dataAttr = function(sortableObj,attr){
		var arr; 
		if(jQuery.isArray(sortableObj)) arr = sortableObj;
		else 
			arr = jQuery(sortableObj).sortable('toArray');
		
	    var i, n;
	    var attrs = [];
	    for (i = 0, n = arr.length; i < n; i++) {
	      if(jQuery('#' + arr[i]).length) attrs.push(jQuery('#' + arr[i]).attr(attr));		//.data(attr)	//for element data
	    }
	    return attrs;
	};
}

/**
 * change support service
 * @param itemId
 * @param selectObj
 */
hwYahooskype.prototype.changeService = function(itemId,selectObj){
	var self = this;
	self.serv = (typeof selectObj == 'string')? selectObj :'yahoo';	//default service
	if(typeof selectObj == 'object' && selectObj.value) this.serv = selectObj.value;
	jQuery('.yk-group-services-'+itemId).hide();	//hide all services status setting in chat group
	jQuery('#'+itemId+'_'+this.serv).show();	//show pick item
	
};
/**
 * set icon status for either yahoo/skype
 */
hwYahooskype.prototype.pickIconStatus = function(servName,iconId,disp){
	if(typeof $ == 'undefined') var $= jQuery;
	if(!servName || servName == 'current') servName = this.serv;
	var nick, img;
	if(servName == 'yahoo'){
		nick = this.nick? this.nick : 'quachhoang_2005';	//default yahoo nick
		img = $('<img/>').attr('src','http://opi.yahoo.com/online?u='+nick+'&m=g&t='+iconId);
	}
	if(servName == 'skype'){
		nick = this.nick? this.nick : 'boy_yeu_lap_trinh_9x';	//default skype nick
		nick=nick.replace(/[\s]+/g,/*'%20'*/'');
		img = $('<img/>').attr('src','http://mystatus.skype.com/'+iconId+'/'+nick);
	}
	if(img){
	$(disp).empty().addClass('yk-loading').append(img);
	img.bind('load',function(e){
		if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
			console.log('broken image!');
		} else {
			$(disp).removeClass('yk-loading');
		}
	});
	}
};
/**
 * init yahooskype
 * @param holder
 */
hwYahooskype.init = function(holder){
	jQuery(function($){
		hwYahooskype.hideItems($(holder).find('.yk-item-tog'));	//decide to hide at all of item support form
		//hwYahooskype.showItem($(holder).find('.yk-item-tog:first'));	//show first item
		//tog
		var togs=$(holder).find('.yk-item-tog'); 
		$(togs).each(function(i,v){
			$(v).find('.togbar').bind('click',hwYahooskype.togSupport_evt);
		});
		
	});
}
/**
 * enable unused item
 */
hwYahooskype.showItem= function(obj){
	jQuery(obj).find('.yk-support').show("slow");
}
/**
 * make unused item
 */
hwYahooskype.hideItems= function(objs){
	jQuery(objs).each(function(i,v){
		jQuery(v).find('.yk-support').hide("slow");
	});
}

/**
* bind click event for close button
*/
hwYahooskype.togSupport_evt =function(e){
	var yk_item = jQuery(this).parent().next();	//find content
	if(yk_item.is('table')) yk_item.toggle();	//remove item
	//set state
	if(yk_item.is(":hidden")) jQuery(this).addClass('closed').removeClass('open');
	else jQuery(this).addClass('open').removeClass('closed');
}
hwYahooskype();	//initialize
/**
 * remove multi values in array
 * @param deleteValue: value you want to delete, if empty value you give undefine
 */
Array.prototype.clean = function(deleteValue) {
	  for (var i = 0; i < this.length; i++) {
	    if (this[i] == deleteValue) {         
	      this.splice(i, 1);
	      i--;
	    }
	  }
	  return this;
	};