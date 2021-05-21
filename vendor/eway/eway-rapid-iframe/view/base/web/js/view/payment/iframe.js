define(
    [
        'jquery',
        'Eway_EwayRapid/js/view/payment/common'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_IFrame/payment/ewayrapid-iframe'
            },
            sharedPaymentUrl: '',

            loadScript: function () {
                var state = this.scriptLoaded;
                if (!state()) {
                    $('body').trigger('processStart');
                    require(['ewayecrypt'], function () {
                        state(true);
                        eCrypt.init();
                        $('body').trigger('processStop');
                    });
                }
            },

            canCreateToken: function () {
                return this.getMethodConfigData('can_create_token');
            },

            getAccessCode: function () {
                var self = this;
                var url = this.getMethodConfigData('get_access_code_url');

                var data = {form_key: FORM_KEY};
                if (this.isTokenEnabled()) {
                    var tokenID = (this.selectedToken() ? this.selectedToken().token_id : '');
                    if (tokenID) {
                        data['TokenAction'] = 'use';
                        data['TokenID'] = tokenID;
                    } else if (this.saveCard() && this.canCreateToken()) {
                        data['TokenAction'] = 'new';
                    }
                }

                $('body').trigger('processStart');
                return $.ajax(url, {method: "POST", data: data})
                    .done(function (data) {
                        self.sharedPaymentUrl = data['shared_payment_url'];
                        $('#ewayrapid_access_code').val(data['access_code']);
                    }).fail(function (jqXHR) {
                        var data = jqXHR.responseJSON;
                        alert(data['error'] ? data['error'] : 'Unknown error happened');
                        $('body').trigger('processStop');
                    });
            },

            _submitOrder: function () {
                this.getAccessCode().then(function () {
                    var eWAYConfig = {
                        sharedPaymentUrl: this.sharedPaymentUrl
                    };
                    eCrypt.showModalPayment(eWAYConfig, this.iframeResultCallback.bind(this));
                }.bind(this));
            },

            iframeResultCallback: function (result, transactionID, errors) {
                if (result == "Complete") {
                    this.getForm().trigger('realOrder');
                } else {
                    if (result == "Error") {
                        alert("There was a problem completing the payment: " + errors);
                    }
                    $('body').trigger('processStop');
                }
            }
        });
    }
);