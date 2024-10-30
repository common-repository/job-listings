jQuery(document).ready(function ($) {
    'use strict';
    // Ajax Form
    var JLT_Ajax_Form = function ($form) {

        var formData = $form.serialize();
        var btn = $form.find('.jlt-btn');
        var note = $form.find('.jlt-ajax-note');

        note.html("");
        btn.prop('disabled', 'disabled').prepend('<i class="jlt-icon jlfa-spinner jlt-spin"></i>');

        $.post(
            JLT_Ajax.ajaxurl, formData,
            function (response) {
                btn.prop('disabled', '').find('i').remove();
                note.append(response.message);
            });
        return false;
    };

    // Validate and Submit
    $('.jlt-form-ajax').each(function () {
        $.validate({
            form: $(this),
            onSuccess: JLT_Ajax_Form
        });
    });


});