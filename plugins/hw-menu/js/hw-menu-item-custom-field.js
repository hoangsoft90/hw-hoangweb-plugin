/**
 * add media dialog
 * @param obj toggle
 */
__hw_navmenu.add_media_dialog = function(obj, target) {
    if (this.window === undefined && typeof wp.media !== 'undefined') {
        this.window = wp.media({
            title: 'Chèn ảnh',          //title of the window
            library: {
                type: 'image'     //filter the media files displayed in the window
                //author: userSettings.uid,    // specific user-posted attachment
            },
            multiple: false,          //by default. user won’t be able to chose more than one file
            button: {text: 'Chèn ảnh'}          //label of the button on which the user will click to validate its choice
        });

        /*retrieve the user’s selection*/
        var self = this; // Needed to retrieve our variable in the anonymous function below
        this.window.on('select', function(){
            var selection = self.window.state().get('selection');
            //If the user can only chose one file, you can retrieve it with the first() method. Then you can convert the obtained object to JSON
            var data = (selection.first().toJSON());

            //save image location in hidden field
            jQuery(target).attr('value',data.url);

            //display image as icon
            jQuery(obj).html('<img src="'+data.url+'" class="hw-menu-item-icon"/>');
        });
    }
    this.window.open();     //open window
    return false;     //in order to prevent the default behavior of the link.
};
