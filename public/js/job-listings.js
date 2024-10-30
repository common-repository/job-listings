(function ($) {
    'use strict';
    console.log("Thanks for use plugin Job Listings! Look more info in http://nootheme.com");

})(jQuery);

jQuery(document).ready(function ($) {
    'use strict';

    // Form chosen
    $('.jlt-form-control-chosen').chosen();

    $('.jlt-btn-apply-form').magnificPopup({
        type: 'inline',
        preloader: false,
        focus: '#candidate_name',
        removalDelay: 300,
        mainClass: 'mfp-fade'
        // modal: true,
    });

    $('.jlt-btn-apply-form-linkedin').magnificPopup({
        type: 'inline',
        preloader: false,
        focus: '#candidate_name',
        removalDelay: 300,
        mainClass: 'mfp-fade'
        // modal: true,
    });

    // Text Editor

    function updateTextAreasWithContentFromTinyMCE() {
        $('textarea').each(function () {
            var content = getTinyMCEContentForTextarea(this);
            if (content !== undefined) {
                $(this).val(content);
            }
        });
    }

    function getTinyMCEContentForTextarea(textareaElem) {
        var id = textareaElem.id || textareaElem.name;
        var i = 0;
        for (i; i < tinyMCE.editors.length; i++) {
            if (tinyMCE.editors[i].id == id) {
                return tinyMCE.editors[i].getContent();
            }
        }
        return undefined;
    }

    $('form').on('submit', updateTextAreasWithContentFromTinyMCE);


    $('.jlt-editor-required').attr('data-validation', 'required');

    // Form without ajax validate

    $('.jlt-form').not('.jlt-form-ajax').each(function () {
        $.validate({
            form: $(this),
            modules: 'html5',
            validateHiddenInputs: true,
            errorElementClass: 'jlt-control-validation-error',
        });
    });


    $('.add-new-location').click(function () {
        $('.add-new-location-content').toggle();
        return false;
    });

    $("#select_all").change(function () {
        $(".jlt-checkbox").prop('checked', $(this).prop("checked"));
    });

    $('.jlt-checkbox').change(function () {
        if (false == $(this).prop("checked")) {
            $("#select_all").prop('checked', false);
        }
        if ($('.jlt-checkbox:checked').length == $('.jlt-checkbox').length) {
            $("#select_all").prop('checked', true);
        }
    });

    // Add new location
    $('body').on('click', '.add-new-location-submit', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var _this = $(this);
        var _location = _this.closest('.add-new-location-content').find('input').val();
        if ($.trim(_location) != '') {
            _this.prop('disabled', 'disabled').prepend('<i class="jlt-icon jlfa-spinner jlt-spin"></i>');
            $.post(jltMemberL10n.ajax_url, {
                action: 'add_new_job_location',
                location: _location,
                security: jltMemberL10n.ajax_security
            }, function (res) {
                if (res.success == true) {
                    _this.closest('.add-new-location-content').find('input').val('');
                    var option = $('<option>');
                    var chosenLocation = _this.closest('fieldset').find('.jlt-form-control');
                    var value = _this.data('return-type') === 'id' ? res.location_id : res.location_slug;
                    option.text(res.location_title).val(value);
                    option.prop('selected', true).attr('selected', 'selected');
                    if (!chosenLocation.is("[multiple]")) {
                        chosenLocation.find("option:selected").attr("selected", false);
                    }
                    chosenLocation.append(option);
                    chosenLocation.trigger('chosen:updated');
                }
                _this.prop('disabled', '').find('i').remove();
            }, 'json');
        }
        return false;
    });

    // Datetimepicker

    var date_format = Jobboard_i18n.date_format ? Jobboard_i18n.date_format : 'Y/m/d';

    $('.jlt-form-datepicker').datetimepicker({
        format: date_format,
        timepicker: false,
        scrollMonth: false,
        scrollTime: false,
        scrollInput: false,
        step: 15,
        validateOnBlur: false,
        onChangeDateTime: function (dp, $input) {
            if ($input.next('.jlt-form-datepicker-value').length) {
                $input.next('.jlt-form-datepicker-value').val(parseInt(dp.getTime() / 1000) - 60 * dp.getTimezoneOffset());
            }
        }
    });

    jQuery(function () {
        jQuery('.jlt-form-datepicker-start').datetimepicker({
            format: date_format,
            timepicker: false,
            scrollMonth: false,
            scrollTime: false,
            scrollInput: false,
            step: 15,
            validateOnBlur: false,
            onShow: function (ct) {
                this.setOptions({
                    maxDate: jQuery('.jlt-form-datepicker-end').val() ? jQuery('.jlt-form-datepicker-end').val() : false,
                    format: date_format,
                })
            }
        });
        jQuery('.jlt-form-datepicker-end').datetimepicker({
            format: date_format,
            timepicker: false,
            scrollMonth: false,
            scrollTime: false,
            scrollInput: false,
            step: 15,
            validateOnBlur: false,
            onShow: function (ct) {
                this.setOptions({
                    minDate: jQuery('.jlt-form-datepicker-start').val() ? jQuery('.jlt-form-datepicker-start').val() : false,
                    format: date_format,
                })
            }
        });
    });


    $('.jlt-form-datepicker').change(function () {
        var $this = $(this);
        if ($this.val() == '') {
            $this.next('input[type="hidden"]').val('');
        }
    });


    // Search and Paging ajax

    var job_archive_content = $('.jlt-job-archive-content');
    var job_lists_container = $(".jlt-jobs-listing");
    var job_count = $(".job-count");
    var btn = $('.jlt-search-btn .jlt-btn');
    var form_search = $(".jlt-job-search");

    var enable_ajax_filter_job = JLT_Ajax.enable_ajax_filter_job;

    if (enable_ajax_filter_job) {
        if (job_lists_container.length) {

            form_search.on("change", function (event) {
                event.preventDefault();
                ajax_filter($(this));

            });
        }
        $(document).on('click', '.jlt-job-archive-content .jlt-pagination .page-numbers a', function (event) {
            var page = jlt_find_page_number($(this).clone());
            event.preventDefault();
            ajax_filter(form_search, page);
            return false;
        });
    }


    function ajax_filter(form, page) {

        var data = form.serialize();

        if (page !== '') {
            data = data + '&page=' + page;
        }
        data = data + '&action=jlt_job_search_ajax';

        history.pushState(null, null, "?" + form.serialize());

        job_lists_container.block({
            message: null, overlayCSS: {
                backgroundColor: '#fafafa',
                opacity: 0.5,
                cursor: 'wait'
            }
        });

        btn.prop('disabled', 'disabled').prepend('<i class="jlt-icon jlfa-spinner jlt-spin"></i>');

        $('html, body').animate({
            scrollTop: job_archive_content.offset().top - 100
        }, 400);

        $.ajax({
            url: JLT_Ajax.ajaxurl,
            data: data,
        }).success(function (data) {

            btn.prop('disabled', '').find('i').remove();
            job_lists_container.unblock();

            if (data !== "-1") {

                $('body').find(job_lists_container).html(data.list_job);
                job_count.html(data.job_count);
                job_lists_container.next().html(data.paging);

            } else {
                location.reload();
            }
        });
    }

    function jlt_find_page_number(element) {
        element.find('span').remove();
        return parseInt(element.html());
    }


});