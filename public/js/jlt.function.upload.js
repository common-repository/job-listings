(function ($) {

    $.fn.jlt_upload = function (options) {
        // -- set value default
        var defaults = {

            max_file_size: '2mb',
            runtimes: 'html5,flash,html4',
            multipart: true,
            urlstream_upload: true,
            max_files: 0,
            upload_enabled: true,
            multi_upload: false,
            url: jltUpload.url,
            delete_url: jltUpload.delete_url,
            flash_swf_url: jltUpload.flash_swf_url,
            resize: {}

        };
        // --
        options = $.extend(defaults, options);

        // -- get value tag
        var flash_swf_url = options.flash_swf_url;
        var tag_thumb = options.tag_thumb;
        var thumb_preview = $('#' + tag_thumb);
        var input_name = options.input_name;
        var multi_upload = options.multi_upload;

        // -- Call wp plupload

        var uploader = new plupload.Uploader({
            browse_button: options.browse_button,
            file_data_name: 'aaiu_upload_file',
            multi_selection: options.multi_upload,
            url: options.url,

            flash_swf_url: flash_swf_url,
            filters: [
                {title: "extensions", extensions: "jpg,jpeg,gif,png"},
            ],
            resize: options.resize,
            views: {thumb: true},
            init: {
                PostInit: function () {
                    thumb_preview.innerHTML = '';
                },

                FilesAdded: function (up, files) {
                    plupload.each(files, function (file) {
                        // var co = co + '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
                        // thumb_preview.html( co );
                        if (multi_upload === false) {
                            $('#' + options.browse_button).parent().find('.jlt_upload-status').get(0).innerHTML = '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
                        } else {
                            $('#' + options.browse_button).parent().find('.jlt_upload-status').get(0).innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
                        }
                    });

                    // up.refresh(); // Reposition Flash/Silverlight
                    uploader.start();
                },

                UploadProgress: function (up, file) {
                    if ($('#' + file.id)) {
                        $('#' + file.id).find('b').get(0).innerHTML = '<span>' + file.percent + "%</span>";
                    }
                },

                Error: function (up, err) {
                    if (multi_upload === false) {
                        thumb_preview.html("\nError #" + err.code + ": " + err.message);
                    } else {
                        thumb_preview.get(0).innerHTML += "\nError #" + err.code + ": " + err.message;
                    }
                },

                FileUploaded: function (up, file, response) {
                    var result = $.parseJSON(response.response);
                    $('#' + file.id).remove();
                    if (result.success) {
                        // var img_id += result.image_id + ', ';
                        // thumb_preview.append(result.image);
                        // thumb_preview.('img').attr('src', result.image);
                        // var img += '<img src="' + result.image + '" />';
                        if (multi_upload === false) {
                            thumb_preview.append(
                                '<div class="image-upload-thumb">' +
                                '<img width="150" src="' + result.image + '" />' +
                                '<input type="hidden" name="' + input_name + '" value="' + result.image_id + '" />' +
                                '<a class="delete-uploaded" data-fileid="' + result.image_id + '" href="#" title="' + jltUpload.remove_txt + '"><i class="' + jltUpload.remove_icon + '"></i></a>' +
                                '</div>'
                            );
                        } else {
                            thumb_preview.append(
                                '<div class="image-upload-thumb">' +
                                '<img width="150" src="' + result.image + '" />' +
                                '<input type="hidden" name="' + input_name + '[]" value="' + result.image_id + '" />' +
                                '<a class="delete-uploaded" data-fileid="' + result.image_id + '" href="#" title="' + jltUpload.remove_txt + '"><i class="' + jltUpload.remove_icon + '"></i></a>' +
                                '</div>'
                            );
                            // $('#' + name).val(img_id);
                        }
                    }
                }
            }
        });

        uploader.init();
        thumb_preview.on('click', '.image-upload-thumb .delete-uploaded', function (e) {
            e.preventDefault();
            var el = $(this);
            el.parent('.image-upload-thumb').remove();
            var data = {
                'attach_id': el.data('fileid')
            };
            $.post(jltUpload.delete_url, data);

            return false;
        });

    };

})(jQuery);