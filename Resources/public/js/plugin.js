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
})
(jQuery);