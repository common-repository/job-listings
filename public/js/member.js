;(function ($) {
    "use strict";
    $(document).ready(function () {

        $('.approve-reject-action').click(function () {
            var $this = $(this);
            var form = $this.closest('.member-manage-table');
            form.block({
                message: null, overlayCSS: {
                    backgroundColor: '#fafafa',
                    opacity: 0.5,
                    cursor: 'wait'
                }
            });
            $.post(
                jltMemberL10n.ajax_url,
                {
                    action: 'jlt_approve_reject_application_modal',
                    application_id: $this.data('application-id'),
                    hander: $this.data('hander'),
                    security: jltMemberL10n.ajax_security

                }, function (respon) {
                    form.unblock();
                    if (respon) {
                        $('body').find('#jlt-popup-ajax').html('');
                        $('body').find('#jlt-popup-ajax').append(respon);
                        $.magnificPopup.open({
                            items: {
                                src: $('body').find('#jlt-popup-ajax').html(),
                                type: 'inline',
                                focus: '#message',
                                modal: true,
                                callbacks: {
                                    beforeOpen: function () {

                                    }
                                },
                            }

                        });
                    }
                });
            return false;
        });

        // Get Application Info
        $('.candidate-message').click(function () {
            var $this = $(this);
            var form = $this.closest('.member-manage-table');
            form.block({
                message: null, overlayCSS: {
                    backgroundColor: '#fafafa',
                    opacity: 0.5,
                    cursor: 'wait'
                }
            });
            $.post(
                jltMemberL10n.ajax_url,
                {
                    action: 'jlt_employer_message_application_modal',
                    application_id: $this.data('application-id'),
                    security: jltMemberL10n.ajax_security,

                }, function (respon) {
                    form.unblock();
                    if (respon) {
                        $('body').find('#jlt-popup-ajax').html('');
                        $('body').find('#jlt-popup-ajax').append(respon);
                        $.magnificPopup.open({
                            items: {
                                src: $('body').find('#jlt-popup-ajax').html(),
                                type: 'inline',
                                modal: true,
                                callbacks: {
                                    beforeOpen: function () {

                                    }
                                },
                            }

                        });
                    }
                });
            return false;
        });

        // Get Application response 

        $('.employer-message').click(function () {
            var $this = $(this);
            var form = $this.closest('.member-manage-table');
            form.block({
                message: null, overlayCSS: {
                    backgroundColor: '#fafafa',
                    opacity: 0.5,
                    cursor: 'wait'
                }
            });
            $.post(
                jltMemberL10n.ajax_url,
                {
                    action: 'jlt_application_response_modal',
                    application_id: $this.data('application-id'),
                    security: jltMemberL10n.ajax_security,

                }, function (respon) {
                    form.unblock();
                    if (respon) {
                        $('body').find('#jlt-popup-ajax').html('');
                        $('body').find('#jlt-popup-ajax').append(respon);
                        $.magnificPopup.open({
                            items: {
                                src: $('body').find('#jlt-popup-ajax').html(),
                                type: 'inline',
                                modal: true,
                                callbacks: {
                                    beforeOpen: function () {

                                    }
                                },
                            }

                        });
                    }
                });
            return false;
        });

        $('.btn-demo').on('click', function (e) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: jltMemberL10n.ajax_url,
                data: formData,
                success: function (data) {

                },
                complete: function (data) {
                },
                error: function (data) {
                }
            });
        });

    });
})(jQuery);
