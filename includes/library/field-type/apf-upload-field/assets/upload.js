if(typeof $=='undefined') $=jQuery;   //prevent conflict
/**
 * js uploader
 * https://github.com/drogus/jquery-upload-progress/blob/master/example/index.html
 * http://web.archive.org/web/20120414125425/http://t.wits.sg/2008/06/25/howto-php-and-jquery-upload-progress-bar
 * class HW_Uploader
 * @param args
 */
function HW_Uploader(args) {
    /**
     * //singleton
     * @type {hw_uploader}
     */
    var $this=  this;
    /**
     * identifier
     */
    var id = null;
    /**
     * container
     * @type {null}
     */
    var wrapper = null;
    /**
     * To remove the previous error message
     */
    var msg = null;
    /**
     * image preview for uploading image of mime type
     * @type {$|*|jQuery|HTMLElement}
     */
    var img_previewing = null;
    /**
     * loading image status
     * @type {$|*|jQuery|HTMLElement}
     */
    var loading = $('<img/>', {src: __hw_global_object.loading_image, title: 'Loading..'});
    /**
     * progressbar
     * @type {progressbar|*}
     */
    var progressbar = $('<div/>').progressbar();

    /**
     * file type for input
     * @type {null}
     */
    var file_input = null;
    /**
     * form element
     * @type {null}
     */
    var form = null;
    /**
     * data storing
     * @type {null}
     */
    var config = null;

    /**
     * start upload file
     */
    function start_upload_event(e) {
        msg.empty();
        loading.show();
        progressbar.show(); //show progressbar
        //filter form data
        var data = new FormData($(form).get(0));
        jQuery.each(file_input[0].files, function(i, file) {
            data.append('file-'+i, file);
        });
        var options = {
            //target:   '#output',   // target element(s) to be updated with server response
            url: get_config().upload_handle , // Url to which the request is send
            type: "POST",             // Type of request to be send, called as method
            data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false,        // To send DOMDocument or non processed data file it is set to false

            beforeSubmit:  beforeSubmit_event,  // pre-submit callback
            success:       afterSuccess_event,  // post-submit callback
            uploadProgress: OnProgress_event, //upload progress callback
            resetForm: true ,       // reset the form after successful submit
            clearForm: true        // clear all form fields after successful submit
        };
        //http://jquery.malsup.com/form/#ajaxSubmit
        //$(form).ajaxSubmit($(options).serialize());
        var options = {
            url: get_config().upload_handle , // Url to which the request is send
            type: "POST",             // Type of request to be send, called as method
            data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false,        // To send DOMDocument or non processed data file it is set to false
            success: afterSuccess_event
        };
        //Using the FormData emulation for older browsers
        if(data.fake) {
            // Make sure no text encoding stuff is done by xhr
            options.xhr = function() { var xhr = jQuery.ajaxSettings.xhr(); xhr.send = xhr.sendAsBinary; return xhr; }
            options.contentType = "multipart/form-data; boundary=" + data.boundary;
            options.data = data.toString();
        }
        $.ajax(options);
    }

    /**
     * get file type
     * @param type
     */
    function get_file_type(type) {
        var match_types = {
            "image": ["image/jpeg","image/png","image/jpg"],
            "application": ["application/x-msdownload"]
        };
        for(var _type in match_types) {
            for(var i in match_types[_type]) {
                if(type == match_types[_type][i]) return _type;
            }
        }
    }

    /**
     * check file is image of mime type
     * @param type
     * @returns {boolean}
     */
    function is_image_file(type) {
        return get_file_type(type) == 'image';
    }

    /**
     * get current uploader config
     * @param key
     * @returns {*}
     */
    function get_config(key) {
        if(typeof __hw_apf_field_upload[id] == 'object' && key && typeof __hw_apf_field_upload[id][key] != 'undefined') {
            return __hw_apf_field_upload[id][key] ;
        }
        return __hw_apf_field_upload[id];
    }

    /**
     * A function to be called if request succeeds
     * @param responseText
     */
    function afterSuccess_event(responseText, statusText, xhr, $form) {
        loading.hide();
        if(typeof responseText == 'string' && responseText) data = JSON.parse(responseText);
        else data = responseText;

        msg.html(data.messages);
        if((data.status == 1 || data.code.toLowerCase()=='exists')&& get_config('callbacks') && typeof get_config('callbacks').success =='function') {
            get_config('callbacks').success(data);
        }
        if(get_config('success_callback_js') && typeof get_config('success_callback_js') =='function') {    //check for callback
            get_config('success_callback_js')(data, this);
        }
		else if(get_config('redirect')) {
			if(data.status == 1 || data.code.toLowerCase()=='exists') {
				jQuery.post_to_self({data: JSON.stringify(data), uploaded: '1'},{upload:'success'});	//or 'upload=success'
			}
		}
        
    }
    /**
     * pre-submit callback
     * @returns {boolean}
     */
    function beforeSubmit_event(formData, jqForm, options){
        //check whether client browser fully supports all File API
        if (window.File && window.FileReader && window.FileList && window.Blob)
        {
            var fsize = file_input.get(0).files[0].size; //get file size
            var ftype = file_input.get(0).files[0].type; // get file type
            //allow file types
            if(get_config('allow_types') && !ftype in get_config('allow_types')) {
                msg.html("<b>"+ftype+"</b> Unsupported file type!");
                return false
            }
            /*switch(ftype)
            {
                case 'image/png':
                case 'image/gif':
                case 'image/jpeg':
                case 'image/pjpeg':
                case 'text/plain':
                case 'text/html':
                case 'application/x-zip-compressed':
                case 'application/pdf':
                case 'application/msword':
                case 'application/vnd.ms-excel':
                case 'video/mp4':
                    break;
                default:
                    $("#output").html("<b>"+ftype+"</b> Unsupported file type!");
                    return false
            }*/

            //Allowed file size is less than 5 MB (1048576 = 1 mb)
            if(fsize>5242880)
            {
                alert("<b>"+fsize +"</b> Too big file! <br />File is too big, it should be less than 5 MB.");
                return false
            }
        }
        else
        {
            //Error for older unsupported browsers that doesn't support HTML5 File API
            alert("Please upgrade your browser, because your current browser lacks some new features we need!");
            return false
        }
    }
    /**
     * change file input event
     * @param event
     * @returns {boolean}
     */
    function change_file_event(event) {
        msg.empty(); // To remove the previous error message
        var file = file_input.get(0).files[0];
        if(!file) return ;  //cancel select file

        var imagefile = file.type;
        //var match= [""/*,, "text/xml"*/];
        if(!is_image_file(imagefile) && get_config().image_type)
        {
            img_previewing.attr('src', __hw_apf_field_upload.no_image);
            msg.html("<p id='error'>Please Select A valid Image File</p>"+"<h4>Note</h4>"+"<span id='error_message'>Only jpeg, jpg and png Images type allowed</span>");
            return false;
        }
        else
        {
            msg.empty();
            var reader = new FileReader();
            reader.onload = imageIsLoaded_event;
            reader.readAsDataURL(this.files[0]);
        }
    }

    /**
     * on progressbar
     * @param event
     * @param position
     * @param total
     * @param percentComplete
     * @constructor
     */
    function OnProgress_event(event, position, total, percentComplete)
    {
        console.log(percentComplete);
        //Progress bar
        progressbar.show();
        progressbar.progressbar({value: (percentComplete + '%')}) //update progressbar percent complete

        if(percentComplete>50)
        {
            progressbar.css('color','#000'); //change status text to white after 50%
        }
    }
    /**
     * check image loaded event
     * @param e
     */
    function imageIsLoaded_event(e) {
        file_input.css("color","green");
        img_previewing.attr('src', e.target.result);
        img_previewing.attr('width', '250px');
        img_previewing.attr('height', '230px');
        //save file field
        if(config.save_file) {
            $(config.save_file).val(e.target.result);
        }
    }
    $this.get_config = get_config;
    /**
     * init ajax form upload
     * @param form_ele form or element in form
     */
    $this.init = function(form_ele) {
        if(!$(form_ele).is('form') && $(form_ele).length && $(form_ele).get(0).form) {
            form = $($(form_ele).get(0).form);
        }
        else if($(form_ele).is('form')){
            form = $(form_ele);
        }
        //validation
        if(!form) return ;

        //submit click event
        $(form).on('submit', function(e) {
        //$(wrapper).find('input[type=submit]').on('click', function(e) {
            e.preventDefault();
            start_upload_event(e);
            return false;
        });

        //bind change event to file input element
        file_input = $(form).find('input[type=file]:eq(0)');
        file_input.change(change_file_event) ;

        //other element preparing
        $(wrapper||form).append(loading.hide());
        $(wrapper||form).append(progressbar.hide());
    }
    /**
     * init uploader
     * @param params
     */
    $this.setup = function (params) {
        //initialize
        if(typeof params === 'string') $this.set_container(params) ;
        else if(/*jQuery.isArray(params)*/typeof params =='object') {
            if(params.container) $this.set_container(params.container);
            if(params.message) msg = $(params.message);
            if(params.preview) img_previewing = $(params.preview);
            if(params.id) id = params.id;
        }
        if(params.file_input) $this.init(params.file_input);
        if(!config) config ={};
        $.extend(config, params);
    }
    /**
     * register callbacks
     * @param args
     */
    $this.callbacks = function(args) {
        $this.setup({"callbacks": args});
    }
    /**
     * set upload area
     * @param container
     */
    $this.set_container = function(container) {
        if(container) wrapper = container;
    }
    $this.setup(args);
}

