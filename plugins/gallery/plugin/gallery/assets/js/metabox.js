/* ==========================================================
 * metabox.js
 * http://enviragallery.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Licensed under the GPL License, Version 2.0 or later (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
;(function($){
    $(function(){
        // Initialize the slider tabs.
        var hw_gallery_tabs           = $('#hw-gallery-tabs'),
            hw_gallery_tabs_nav       = $('#hw-gallery-tabs-nav'),
            hw_gallery_tabs_hash      = window.location.hash,
            hw_gallery_tabs_hash_sani = window.location.hash.replace('!', '');

        // If we have a hash and it begins with "hw-gallery-tab", set the proper tab to be opened.
        if ( hw_gallery_tabs_hash && hw_gallery_tabs_hash.indexOf('hw-gallery-tab-') >= 0 ) {
            $('.hw-gallery-active').removeClass('hw-gallery-active');
            hw_gallery_tabs_nav.find('li a[href="' + hw_gallery_tabs_hash_sani + '"]').parent().addClass('hw-gallery-active');
            hw_gallery_tabs.find(hw_gallery_tabs_hash_sani).addClass('hw-gallery-active').show();

            // Update the post action to contain our hash so the proper tab can be loaded on save.
            var post_action = $('#post').attr('action');
            if ( post_action ) {
                post_action = post_action.split('#')[0];
                $('#post').attr('action', post_action + hw_gallery_tabs_hash);
            }
        }

        // Change tabs on click.
        $(document).on('click', '#hw-gallery-tabs-nav li a', function(e){
            e.preventDefault();
            var $this = $(this);
            if ( $this.parent().hasClass('hw-gallery-active') ) {
                return;
            } else {
                window.location.hash = hw_gallery_tabs_hash = this.hash.split('#').join('#!');
                var current = hw_gallery_tabs_nav.find('.hw-gallery-active').removeClass('hw-gallery-active').find('a').attr('href');
                $this.parent().addClass('hw-gallery-active');
                hw_gallery_tabs.find(current).removeClass('hw-gallery-active').hide();
                hw_gallery_tabs.find($this.attr('href')).addClass('hw-gallery-active').show();

                // Update the post action to contain our hash so the proper tab can be loaded on save.
                var post_action = $('#post').attr('action');
                if ( post_action ) {
                    post_action = post_action.split('#')[0];
                    $('#post').attr('action', post_action + hw_gallery_tabs_hash);
                }
            }
        });

        // Load plupload.
        var hw_gallery_uploader;
        hw_galleryPlupload();

        // Conditionally show necessary fields.
        hw_galleryConditionals();

        // Handle the meta icon helper.
        if ( 0 !== $('.hw-gallery-helper-needed').length ) {
            $('<div class="hw-gallery-meta-helper-overlay" />').prependTo('#hw-gallery');
        }

        $(document).on('click', '.hw-gallery-meta-icon', function(e){
            e.preventDefault();
            var $this     = $(this),
                container = $this.parent(),
                helper    = $this.next();
            if ( helper.is(':visible') ) {
                $('.hw-gallery-meta-helper-overlay').remove();
                container.removeClass('hw-gallery-helper-active');
            } else {
                if ( 0 === $('.hw-gallery-meta-helper-overlay').length ) {
                    $('<div class="hw-gallery-meta-helper-overlay" />').prependTo('#hw-gallery');
                }
                container.addClass('hw-gallery-helper-active');
            }
        });

        // Open up the media manager modal.
        $(document).on('click', '.hw-gallery-media-library', function(e){
            e.preventDefault();

            // Show the modal.
            hw_gallery_main_frame = true;
            $('#hw-gallery-upload-ui').appendTo('body').show();
        });

        // Add the selected state to images when selected from the library view.
        $('.hw-gallery-gallery').on('click', '.thumbnail, .check, .media-modal-icon', function(e){
            e.preventDefault();
            if ( $(this).parent().parent().hasClass('hw-gallery-in-gallery') )
                return;
            if ( $(this).parent().parent().hasClass('selected') )
                $(this).parent().parent().removeClass('details selected');
            else
                $(this).parent().parent().addClass('details selected');
        });

        // Load more images into the library view when the 'Load More Images from Library'
        // button is pressed
        $(document).on('click', 'a.hw-gallery-load-library', function(e){
            hw_galleryLoadLibraryImages( $('a.hw-gallery-load-library').attr('data-hw-gallery-offset') );
        });

        // Load more images into the library view when the user scrolls to the bottom of the view
        // Honours any search term(s) specified
        $('.hw-gallery-gallery').bind('scroll', function() {
            if( $(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight ) {
                hw_galleryLoadLibraryImages( $('a.hw-gallery-load-library').attr('data-hw-gallery-offset') );
            }
        });

        // Load images when the search term changes
        $(document).on('keyup keydown', '#hw-gallery-gallery-search', function() {
            delay(function() {
                hw_galelryLoadLibraryImages( 0 );
            }); 
        });

        /**
        * Makes an AJAX call to get the next batch of images
        */
        function hw_galleryLoadLibraryImages( offset ) {
            // Show spinner
            $('.media-toolbar-secondary span.hw-gallery-spinner').css('visibility','visible');

            // AJAX call to get next batch of images
            $.post(
                hw_gallery_metabox.ajax,
                {
                    action:  'hw_gallery_load_library',
                    offset:  offset,
                    post_id: hw_gallery_metabox.id,
                    search:  $('input#hw-gallery-gallery-search').val(),
                    nonce:   hw_gallery_metabox.load_gallery
                },
                function(response) {
                    // Update offset
                    $('a.hw-gallery-load-library').attr('data-hw-gallery-offset', ( Number(offset) + 20 ) );

                    // Hide spinner
                    $('.media-toolbar-secondary span.hw-gallery-spinner').css('visibility','hidden');

                    // Append the response data.
                    if ( offset === 0 ) {
                        // New search, so replace results
                        $('.hw-gallery-gallery').html( response.html );
                    } else {
                        // Append to end of results
                        $('.hw-gallery-gallery').append( response.html );
                    }
                    
                },
                'json'
            );
        }

        // Process inserting slides into slider when the Insert button is pressed.
        $(document).on('click', '.hw-gallery-media-insert', function(e){
            e.preventDefault();
            var $this = $(this),
                text  = $(this).text(),
                data  = {
                    action: 'hw_gallery_insert_images',
                    nonce:   hw_gallery_metabox.insert_nonce,
                    post_id: hw_gallery_metabox.id,
                    images:  {}
                },
                selected = false,
                insert_e = e;
            $this.text(hw_gallery_metabox.inserting);

            // Loop through potential data to send when inserting images.
            // First, we loop through the selected items and add them to the data var.
            $('.hw-gallery-media-frame').find('.attachment.selected:not(.hw-gallery-in-gallery)').each(function(i, el){
                data.images[i] = $(el).attr('data-attachment-id');
                selected       = true;
            });

            // Send the ajax request with our data to be processed.
            $.post(
                hw_gallery_metabox.ajax,
                data,
                function(response){
                    // Set small delay before closing modal.
                    setTimeout(function(){
                        // Re-append modal to correct spot and revert text back to default.
                        append_and_hide(insert_e);
                        $this.text(text);

                        // If we have selected items, be sure to properly load first images back into view.
                        if ( selected )
                            $('.hw-gallery-load-library').attr('data-hw-gallery-offset', 0).addClass('has-search').trigger('click');
                    }, 500);
                },
                'json'
            );

        });

        // Make gallery items sortable.
        var gallery = $('#hw-gallery-output');

        // Use ajax to make the images sortable.
        gallery.sortable({
            containment: '#hw-gallery-output',
            items: 'li',
            cursor: 'move',
            forcePlaceholderSize: true,
            placeholder: 'dropzone',
            update: function(event, ui) {
                // Make ajax request to sort out items.
                var opts = {
                    url:      hw_gallery_metabox.ajax,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action:  'hw_gallery_sort_images',
                        order:   gallery.sortable('toArray').toString(),
                        post_id: hw_gallery_metabox.id,
                        nonce:   hw_gallery_metabox.sort
                    },
                    success: function(response) {
                        return;
                    },
                    error: function(xhr, textStatus ,e) {
                        return;
                    }
                };
                $.ajax(opts);
            }
        });

        // Process image removal from a gallery.
        $('#hw-gallery').on('click', '.hw-gallery-remove-image', function(e){
            e.preventDefault();

            // Bail out if the user does not actually want to remove the image.
            var confirm_delete = confirm(hw_gallery_metabox.remove);
            if ( ! confirm_delete )
                return;

            // Prepare our data to be sent via Ajax.
            var attach_id = $(this).parent().attr('id'),
                remove = {
                    action:        'hw_gallery_remove_image',
                    attachment_id: attach_id,
                    post_id:       hw_gallery_metabox.id,
                    nonce:         hw_gallery_metabox.remove_nonce
                };

            // Process the Ajax response and output all the necessary data.
            $.post(
                hw_gallery_metabox.ajax,
                remove,
                function(response) {
                    $('#' + attach_id).fadeOut('normal', function() {
                        $(this).remove();

                        // Refresh the modal view to ensure no items are still checked if they have been removed.
                        $('.hw-gallery-load-library').attr('data-hw-gallery-offset', 0).addClass('has-search').trigger('click');
                    });
                },
                'json'
            );
        });

        // Open up the media modal area for modifying gallery metadata when clicking the info icon
        $('#hw-gallery').on('click', '.hw-gallery-modify-image', function(e){
            e.preventDefault();
            var attach_id = $(this).parent().data('hw-gallery-image'),
                formfield = 'hw-gallery-meta-' + attach_id;
            
            // Open modal
            openModal(attach_id, formfield);    
        });
        
        // Open modal
        var modal;
        var openModal = function(attach_id, formfield) {
	        
            // Show the modal.
            modal = $('#' + formfield).appendTo('body');
            $(modal).show();
            
	        // Close modal on close button or background click
	        $(document).on('click', '.media-modal-close, .media-modal-backdrop', function(e) {
	            e.preventDefault();
	            closeModal();
	        });
	        
	        // Close modal on esc keypress
	        $(document).on('keydown', function(e) {
	            if ( 27 == e.keyCode ) {
		        	closeModal();    
	            }
	        });
        }
        
        // Close modal
        var closeModal = function() {
	        // Get modal
			var formfield = $(modal).attr('id');
			var formfieldArr = formfield.split('-');
			var attach_id = formfieldArr[(formfieldArr.length-1)];
            	
            // Close modal
	        $('#' + formfield).appendTo('#' + attach_id).hide();
        }
        
        // Save the gallery metadata.
        $(document).on('click', '.hw-gallery-meta-submit', function(e){
            e.preventDefault();
            var $this     = $(this),
                default_t = $this.text(),
                attach_id = $this.data('hw-gallery-item'),
                formfield = 'hw-gallery-meta-' + attach_id,
                meta      = {};

            // Output saving text...
            $this.text(hw_gallery_metabox.saving);

            // Add the title since it is a special field.
            meta.title = $('#hw-gallery-meta-table-' + attach_id).find('textarea[name="_hw_gallery[meta_title]"]').val();

            // Get all meta fields and values.
            $('#hw-gallery-meta-table-' + attach_id).find(':input').not('.ed_button').each(function(i, el){
                if ( $(this).data('hw-gallery-meta') )
                    meta[$(this).data('hw-gallery-meta')] = $(this).val();
            });

            // Prepare the data to be sent.
            var data = {
                action:    'hw_gallery_save_meta',
                nonce:     hw_gallery_metabox.save_nonce,
                attach_id: attach_id,
                post_id:   hw_gallery_metabox.id,
                meta:      meta
            };

            $.post(
                hw_gallery_metabox.ajax,
                data,
                function(res){
                    setTimeout(function(){
                        $('#' + formfield).appendTo('#' + attach_id).hide();
                        $this.text(default_t);
                    }, 500);
                },
                'json'
            );
        });

        // Append spinner when importing a gallery.
        $('#hw-gallery-import-submit').on('click', function(e){
            $(this).next().css('display', 'inline-block');
            if ( $('#hw-gallery-config-import-gallery').val().length === 0 ) {
                e.preventDefault();
                $(this).next().hide();
                alert(hw_gallery_metabox.import);
            }
        });

        // Polling function for typing and other user centric items.
        var delay = (function() {
            var timer = 0;
            return function(callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        // Close the modal window on user action.
        var hw_gallery_main_frame = false;
        var append_and_hide = function(e){
            e.preventDefault();
            $('#hw-gallery-upload-ui').appendTo('#hw-gallery-upload-ui-wrapper').hide();
            hw_galleryRefresh();
            hw_gallery_main_frame = false;
        };
        $(document).on('click', '#hw-gallery-upload-ui .media-modal-close, #hw-gallery-upload-ui .media-modal-backdrop', append_and_hide);
        $(document).on('keydown', function(e){
            if ( 27 == e.keyCode && hw_gallery_main_frame )
                append_and_hide(e);
        });

        // Function to refresh images in the gallery.
        function hw_galleryRefresh(){
            var data = {
                action:  'hw_gallery_refresh',
                post_id: hw_gallery_metabox.id,
                nonce:   hw_gallery_metabox.refresh_nonce
            };

            $('.hw-gallery-media-library').after('<span class="spinner hw-gallery-spinner hw-gallery-spinner-refresh"></span>');
            $('.hw-gallery-spinner-refresh').css({'display' : 'inline-block', 'margin-top' : '-3px'});

            $.post(
                hw_gallery_metabox.ajax,
                data,
                function(res){
                    if ( res && res.success ) {
                        $('#hw-gallery-output').html(res.success);
                        $('#hw-gallery-output').find('.wp-editor-wrap').each(function(i, el){
                            var qt = $(el).find('.quicktags-toolbar');
                            if ( qt.length > 0 ) {
                                return;
                            }

                            var arr = $(el).attr('id').split('-'),
                                id  = arr.slice(4, -1).join('-');
                            quicktags({id: 'hw-gallery-caption-' + id, buttons: 'strong,em,link,ul,ol,li,close'});
                            QTags._buttonsInit(); // Force buttons to initialize.
                        });

                        // Trigger a custom event for 3rd party scripts.
                        $('#hw-gallery-output').trigger({ type: 'hw_galleryRefreshed', html: res.success, id: hw_gallery_metabox.id });
                    }

                    // Remove the spinner.
                    $('.hw-gallery-spinner-refresh').fadeOut(300, function(){
                        $(this).remove();
                    });
                },
                'json'
            );
        }

        // Function to show conditional fields.
        function hw_galleryConditionals() {
            var hw_gallery_crop_option    = $('#hw-gallery-config-crop'),
                hw_gallery_mobile_option  = $('#hw-gallery-config-mobile'),
                hw_gallery_toolbar_option = $('#hw-gallery-config-lightbox-toolbar');
            if ( hw_gallery_crop_option.is(':checked') )
                $('#hw-gallery-config-crop-size-box').fadeIn(300);
            hw_gallery_crop_option.on('change', function(){
                if ( $(this).is(':checked') )
                    $('#hw-gallery-config-crop-size-box').fadeIn(300);
                else
                    $('#hw-gallery-config-crop-size-box').fadeOut(300);
            });
            if ( hw_gallery_mobile_option.is(':checked') )
                $('#hw-gallery-config-mobile-size-box').fadeIn(300);
            hw_gallery_mobile_option.on('change', function(){
                if ( $(this).is(':checked') )
                    $('#hw-gallery-config-mobile-size-box').fadeIn(300);
                else
                    $('#hw-gallery-config-mobile-size-box').fadeOut(300);
            });
            if ( hw_gallery_toolbar_option.is(':checked') )
                $('#hw-gallery-config-lightbox-toolbar-position-box').fadeIn(300);
            hw_gallery_toolbar_option.on('change', function(){
                if ( $(this).is(':checked') )
                    $('#hw-gallery-config-lightbox-toolbar-position-box').fadeIn(300);
                else
                    $('#hw-gallery-config-lightbox-toolbar-position-box').fadeOut(300);
            });
        }

        // Function to initialize plupload.
        function hw_galleryPlupload() {
            // Append the custom loading progress bar.
            $('#hw-gallery .drag-drop-inside').append('<div class="hw-gallery-progress-bar"><div></div></div>');

            // Prepare variables.
            hw_gallery_uploader     = new plupload.Uploader(hw_gallery_metabox.plupload);
            var hw_gallery_bar      = $('#hw-gallery .hw-gallery-progress-bar'),
                hw_gallery_progress = $('#hw-gallery .hw-gallery-progress-bar div'),
                hw_gallery_output   = $('#hw-gallery-output');

            // Only move forward if the uploader is present.
            if ( hw_gallery_uploader ) {
                // Append a link to use images from the user's media library.
                $('#hw-gallery .max-upload-size').append(' <a class="hw-gallery-media-library button button-primary" href="#" title="' + hw_gallery_metabox.gallery + '" style="vertical-align: baseline;">' + hw_gallery_metabox.gallery + '</a>');

                hw_gallery_uploader.bind('Init', function(up) {
                    var uploaddiv = $('#hw-gallery-plupload-upload-ui');

                    // If drag and drop, make that happen.
                    if ( up.features.dragdrop && ! $(document.body).hasClass('mobile') ) {
                        uploaddiv.addClass('drag-drop');
                        $('#hw-gallery-drag-drop-area').bind('dragover.wp-uploader', function(){
                            uploaddiv.addClass('drag-over');
                        }).bind('dragleave.wp-uploader, drop.wp-uploader', function(){
                            uploaddiv.removeClass('drag-over');
                        });
                    } else {
                        uploaddiv.removeClass('drag-drop');
                        $('#hw-gallery-drag-drop-area').unbind('.wp-uploader');
                    }

                    // If we have an HTML4 runtime, hide the flash bypass.
                    if ( up.runtime == 'html4' )
                        $('.upload-flash-bypass').hide();
                });

                // Initialize the uploader.
                hw_gallery_uploader.init();

                // Bind to the FilesAdded event to show the progess bar.
                hw_gallery_uploader.bind('FilesAdded', function(up, files){
                    var hundredmb = 100 * 1024 * 1024,
                        max       = parseInt(up.settings.max_file_size, 10);

                    // Remove any errors.
                    $('#hw-gallery-upload-error').html('');

                    // Show the progress bar.
                    $(hw_gallery_bar).show().css('display', 'block');

                    // Upload the files.
                    plupload.each(files, function(file){
                        if ( max > hundredmb && file.size > hundredmb && up.runtime != 'html5' ) {
                            hw_galleryUploadError( up, file, true );
                        }
                    });

                    // Refresh and start.
                    up.refresh();
                    up.start();
                });

                // Bind to the UploadProgress event to manipulate the progress bar.
                hw_gallery_uploader.bind('UploadProgress', function(up, file){
                    $(hw_gallery_progress).css('width', up.total.percent + '%');
                });

                // Bind to the FileUploaded event to set proper UI display for slider.
                hw_gallery_uploader.bind('FileUploaded', function(up, file, info){
                    // Make an ajax request to generate and output the image in the slider UI.
                    $.post(
                        hw_gallery_metabox.ajax,
                        {
                            action:  'hw_gallery_load_image',
                            nonce:   hw_gallery_metabox.load_image,
                            id:      info.response,
                            post_id: hw_gallery_metabox.id
                        },
                        function(res){
                            $(hw_gallery_output).append(res);
                            $(res).find('.wp-editor-container').each(function(i, el){
                                var id = $(el).attr('id').split('-')[4];
                                quicktags({id: 'hw-gallery-caption-' + id, buttons: 'strong,em,link,ul,ol,li,close'});
                                QTags._buttonsInit(); // Force buttons to initialize.
                            });
                        },
                        'json'
                    );
                });

                // Bind to the UploadComplete event to hide and reset the progress bar.
                hw_gallery_uploader.bind('UploadComplete', function(){
                    $(hw_gallery_bar).hide().css('display', 'none');
                    $(hw_gallery_progress).removeAttr('style');
                });

                // Bind to any errors and output them on the screen.
                hw_gallery_uploader.bind('Error', function(up, error) {
                    var hundredmb = 100 * 1024 * 1024,
                        error_el  = $('#hw-gallery-upload-error'),
                        max;
                    switch (error) {
                        case plupload.FAILED:
                        case plupload.FILE_EXTENSION_ERROR:
                            error_el.html('<p class="error">' + pluploadL10n.upload_failed + '</p>');
                            break;
                        case plupload.FILE_SIZE_ERROR:
                            hw_galleryUploadError(up, error.file);
                            break;
                        case plupload.IMAGE_FORMAT_ERROR:
                            wpFileError(fileObj, pluploadL10n.not_an_image);
                            break;
                        case plupload.IMAGE_MEMORY_ERROR:
                            wpFileError(fileObj, pluploadL10n.image_memory_exceeded);
                            break;
                        case plupload.IMAGE_DIMENSIONS_ERROR:
                            wpFileError(fileObj, pluploadL10n.image_dimensions_exceeded);
                            break;
                        case plupload.GENERIC_ERROR:
                            wpQueueError(pluploadL10n.upload_failed);
                            break;
                        case plupload.IO_ERROR:
                            max = parseInt(uploader.settings.max_file_size, 10);

                            if ( max > hundredmb && fileObj.size > hundredmb )
                                wpFileError(fileObj, pluploadL10n.big_upload_failed.replace('%1$s', '<a class="uploader-html" href="#">').replace('%2$s', '</a>'));
                            else
                                wpQueueError(pluploadL10n.io_error);
                            break;
                        case plupload.HTTP_ERROR:
                            wpQueueError(pluploadL10n.http_error);
                            break;
                        case plupload.INIT_ERROR:
                            $('.media-upload-form').addClass('html-uploader');
                            break;
                        case plupload.SECURITY_ERROR:
                            wpQueueError(pluploadL10n.security_error);
                            break;
                        default:
                            hw_galleryUploadError(up, error.file);
                            break;
                    }
                    up.refresh();
                });
            }
        }

        // Function for displaying file upload errors.
        function hw_galleryUploadError( up, file, over100mb ) {
            var message;

            if ( over100mb ) {
                message = pluploadL10n.big_upload_queued.replace('%s', file.name) + ' ' + pluploadL10n.big_upload_failed.replace('%1$s', '<a class="uploader-html" href="#">').replace('%2$s', '</a>');
            } else {
                message = pluploadL10n.file_exceeds_size_limit.replace('%s', file.name);
            }

            $('#hw-gallery-upload-error').html('<div class="error fade"><p>' + message + '</p></div>');
            up.removeFile(file);
        }
    });
}(jQuery));