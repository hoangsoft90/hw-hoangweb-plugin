/**
 * learn more plugin development, see: https://web.archive.org/web/20120731132536/http://www.voofie.com/content/2/ckeditor-plugin-development
 */
CKEDITOR.plugins.add( 'hw_wp_media_button', {
    icons: 'hw_wp_media_button',
    init: function( editor ) {
        editor.addCommand( 'media_dialog', /*new CKEDITOR.dialogCommand( 'abbrDialog' )*/{
            exec : function(editor){
                /*
                editor.document.getBody().getHtml();
                 editor.name;
                 editor.insertText("dfgdg");
                 */


                if (this.window === undefined) {
                    this.window = wp.media({
                        title: 'Chèn ảnh',          //title of the window
                        library: {
                            type: 'image',     //filter the media files displayed in the window
                            author: ''    // specific user-posted attachment
                        },
                        multiple: false,          //by default. user won’t be able to chose more than one file
                        button: {text: 'Chèn media'}          //label of the button on which the user will click to validate its choice
                        //,frame:'post'   //this only allow main wp editor for post ->don;t use this param
                    });

                    /*retrieve the user’s selection*/
                    var self = this; // Needed to retrieve our variable in the anonymous function below
                    this.window.on('select', function(){
                        var selection = self.window.state().get('selection');
                        //If the user can only chose one file, you can retrieve it with the first() method. Then you can convert the obtained object to JSON
                        console.log(selection.first().toJSON());
                        editor.insertHtml('<img src="'+selection.first().toJSON().url+'"/>');
                    });
                }
                this.window.open();     //open window
            },
            async : true,    // The command need some time to complete after exec function returns.
            //canUndo : false    // No support for undo/redo
            editorFocus : false    // The command doesn't require focusing the editing document.
        });
        //Creating the Toolbar Button
        editor.ui.addButton( 'hw_add_image', {
            label: 'Chèn ảnh',
            command: 'media_dialog',
            toolbar: 'insert',       //'insert,0'
            icon : CKEDITOR.plugins.getPath('hw_wp_media_button')+'/icons/hw_wp_media_button.png'
        });
    }
});
