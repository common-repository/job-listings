jQuery(document).ready(function ($) {
    // image upload
    $(document).on('click', '.jlt-wpmedia', function (e) {
        e.preventDefault();
        var $this = $(this);
        var custom_uploader = wp.media({
            title: JlAdminJS.title_wpmedia,
            button: {
                text: JlAdminJS.button_wpmedia
            },
            multiple: false  // Set this to true to allow multiple files to be selected
        })
            .on('select', function () {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $this.val(attachment.id).change();

            })
            .open();
    });

    $('.parent-control').change(function () {
        var $this = $(this);
        var parent_active = false;
        var parent_type = $this.attr('type');
        var parent_id = $this.attr('id');
        if (parent_type == 'text') {
            parent_active = ($this.val() !== '');
        } else if (parent_type == 'checkbox') {
            parent_active = ($this.is(':checked'));
        }

        if (parent_active) {
            $('.' + parent_id + '-child').show().find('input.parent-control').change();
        } else {
            $('.' + parent_id + '-child').hide().find('input.parent-control').change();
        }
    });

    $('.jlt-slider').each(function () {
        var $this = $(this);

        var $slider = $('<div>', {id: $this.attr("id") + "-slider"}).insertAfter($this);
        $slider.slider(
            {
                range: "min",
                value: $this.val() || $this.data('min') || 0,
                min: $this.data('min') || 0,
                max: $this.data('max') || 100,
                step: $this.data('step') || 1,
                slide: function (event, ui) {
                    $this.val(ui.value).attr('value', ui.value).change();
                }
            }
        );

        $this.change(function () {
            $slider.slider("option", "value", $this.val());
        });
    });

    $('.jlt-ajax-btn').click(function (e) {
        e.preventDefault();

        var $this = $(this);
        var action = $this.data('action');
        if (action) {
            $.ajax({
                url: JlAdminJS.ajax_url,
                dataType: 'json',
                type: 'POST',
                data: $this.data(),
            })
                .done(function (data) {
                    if (typeof data == 'object' && data != null) {
                        // _this.find('.jlt-ajax-result').show().html(data.message);
                        if (data.success === true) {
                            if (data.redirect) {
                                document.location.href = data.redirect;

                                return false;
                            }
                        }
                    }

                    document.location.reload();
                })
                .fail(function () {
                    document.location.reload();
                })
                .always(function () {
                    // console.log("complete");
                });
        }

        return false;
    });

});

