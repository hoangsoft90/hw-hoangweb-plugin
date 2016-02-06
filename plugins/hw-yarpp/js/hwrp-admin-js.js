/**
 * load more skins if plugin 'HW_Taxonomy_Post_List_widget' activated
 */
__hwrp_object.load_more_skins = function(){
    jQuery.ajax({
        url : __hwrp_object.load_skins_ajax,
        success: function(resp){
            console.log(resp);
        }
    });
};