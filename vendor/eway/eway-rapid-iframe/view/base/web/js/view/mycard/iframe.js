define(
    [
        'jquery',
        'Eway_IFrame/js/view/payment/iframe'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_IFrame/mycard/iframe',
                isEditing: window.ewayRapidConfig.payment['ewayrapid']['is_editing']
            },

            initObservable: function () {
                this.observe('scriptLoaded tokenList selectedToken isEditing');
                return this;
            },

            initFormEvents: function () {
                var form = this.getForm();
                form.on('realOrder', function () {
                    if (form.data('ajax-submit')) {
                        form.trigger('ajax-submit');
                    } else {
                        form.submit();
                    }
                }.bind(this));

                form.on('saveCard', this.processSaveCard.bind(this));
            },

            getForm: function () {
                return $('#mycard_edit_form');
            },

            loadScript: function () {
                this._super();
                this.initFormEvents();
                this.selectedToken(null);
                this.initTokenList();
            },

            onTokenSelect: function (token) {
                // Do nothing
            },

            getAccessCode: function () {
                var self = this;
                var url = this.getMethodConfigData('mycard_get_access_code_url');

                return $.ajax(url, {method: "POST", data: self.getForm().serialize()})
                    .then(function (data) {
                        self.sharedPaymentUrl = data['shared_payment_url'];
                        $('#ewayrapid_access_code').val(data['access_code']);
                    }, function (jqXHR) {
                        var data = jqXHR.responseJSON;
                        $('body').trigger('processStop');
                        alert(data['error'] ? data['error'] : 'Unknown error happened');
                    });
            },

            processSaveCard: function () {
                var form = this.getForm();
                form.validate().form();
                form.trigger('afterValidate.beforeSubmit');

                // validate parent form
                var result = this.validate();
                if (form.validate().errorList.length || !result) {
                    $('body').trigger('processStop');
                    return false;
                }

                this._submitOrder();
            }
        });
    }
);
