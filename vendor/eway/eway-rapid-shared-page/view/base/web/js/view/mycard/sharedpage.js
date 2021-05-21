define(
    [
        'jquery',
        'Eway_EwayRapid/js/view/payment/common'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_SharedPage/mycard/sharedpage',
                isEditing: window.ewayRapidConfig.payment['ewayrapid']['is_editing']
            },
            sharedPaymentUrl: '',

            initObservable: function () {
                this.observe('scriptLoaded tokenList selectedToken isEditing');
                return this;
            },

            initFormEvents: function () {
                this.getForm().on('saveCard', this.processSaveCard.bind(this));
            },

            getForm: function () {
                return $('#mycard_edit_form');
            },

            loadScript: function () {
                var state = this.scriptLoaded;
                if (!state()) {
                    state(true);
                    this.initFormEvents();
                    this.selectedToken(null);
                    this.initTokenList();
                }
            },

            canCreateToken: function () {
                return this.getMethodConfigData('can_create_token');
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
                var self = this;
                this.getAccessCode()
                    .done(function () {
                        window.location.href = self.sharedPaymentUrl;
                    });
            }
        });
    }
);
