(function ($) {
    $.ajaxFlash = function (types, callback) {

        var singleMode = !$.isFunction(types);

        if (!singleMode) {
            var temp = callback;
            callback = types;
            types = temp ? temp : '*';
        }

        types = types == '*'.trim() ? '*' : types.trim().split(' ');

        $(document).ajaxComplete(function (event, jqXHR) {

            var messages = $.parseJSON(jqXHR.getResponseHeader('X-Ajax-Flash'));
            var config = $.parseJSON(jqXHR.getResponseHeader('X-Ajax-Flash-Config'));

            if (!messages) {
                return;
            }

            $.each(messages, function (type, messages) {
                config[type] = $.extend({title: '', icon: ''}, config[type]);
            });

            if (singleMode) {
                if (types == '*') {
                    $.each(messages, function (type, messages) {
                        $.each(messages, function (index, msj) {
                            callback(msj, type, config[type]['title'], config[type]['icon']);
                        });
                    });
                } else {
                    $.each(types, function (x, type) {
                        if (messages[type]) {
                            $.each(messages[type], function (x, message) {
                                callback(message, type, config[type]['title'], config[type]['icon']);
                            });
                        }
                    });
                }
            } else {
                if (types == '*') {
                    $.each(messages, function (type, messages) {
                        callback(messages, type, config[type]['title'], config[type]['icon']);
                    });
                } else {
                    $.each(types, function (x, type) {
                        if (messages[type]) {
                            callback(messages[type], type, config[type]['title'], config[type]['icon']);
                        }
                    });
                }
            }
        });
    };

    $(document).ajaxSend(function (event, jqXHR) {
        jqXHR.onErrors = function (fn) {
            jqXHR._onErrors = fn;

            return jqXHR;
        };
        jqXHR.onFormErrors = function (fn) {
            jqXHR._onFormErrors = fn;

            return jqXHR;
        };
        jqXHR.onCloseModal = function (fn) {
            jqXHR._onCloseModal = fn;

            return jqXHR;
        };
    });

    $(document).ajaxComplete(function (event, jqXHR, c) {
        if (jqXHR.getResponseHeader('X-Ajax-Triggers')) {
            var triggers = $.parseJSON(jqXHR.getResponseHeader('X-Ajax-Triggers'));
            $.each(triggers, function () {
                $(document).trigger(this[0], this[1]);
            });
        }
        if (jqXHR.status == 278 && jqXHR.getResponseHeader('Location')) {
            window.location.href = jqXHR.getResponseHeader('Location');
        }
        if (jqXHR.getResponseHeader('X-Ajax-Close-Modal')) {
            var data = $.parseJSON(jqXHR.getResponseHeader('X-Ajax-Close-Modal'));
            $(document).trigger('ajax.close_modal', data.success)
            if (jqXHR._onCloseModal) {
                jqXHR._onCloseModal(data.success);
            }
        }
        if (jqXHR._onErrors && jqXHR.getResponseHeader('X-Ajax-Errors')) {
            var data = $.parseJSON(jqXHR.getResponseHeader('X-Ajax-Errors'));
            jqXHR._onErrors($.parseJSON(jqXHR.responseText), data.is_html);
        }
        if (jqXHR._onFormErrors && jqXHR.getResponseHeader('X-Ajax-Form-Errors')) {
            var data = $.parseJSON(jqXHR.getResponseHeader('X-Ajax-Form-Errors'));
            jqXHR._onFormErrors($.parseJSON(jqXHR.responseText), data.is_html);
        }
    });
})
(jQuery);