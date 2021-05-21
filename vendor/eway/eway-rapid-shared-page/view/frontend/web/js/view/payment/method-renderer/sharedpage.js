define(
    [
        'jquery',
        'Eway_EwayRapid/js/view/payment/method-renderer/common',
        'Magento_Checkout/js/action/set-payment-information'
    ],
    function ($, Component, setPaymentInformationAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_IFrame/payment/ewayrapid-iframe'
            },

            sharedPaymentUrl: '',
            accessCode: '',

            loadScript: function () {
                var state = this.scriptLoaded;
                state(true);
            },

            canCreateToken: function () {
                return this.getMethodConfigData('can_create_token');
            },

            getAccessCode: function () {
                $('body').trigger('processStart');
                var self = this;
                var url = this.getMethodConfigData('get_access_code_url');
                var data = {};
                if (this.isTokenEnabled()) {
                    var tokenID = (this.selectedToken() ? this.selectedToken().token_id : '');
                    if (tokenID) {
                        data['TokenAction'] = 'use';
                        data['TokenID'] = tokenID;
                    } else if (this.saveCard() && this.canCreateToken()) {
                        data['TokenAction'] = 'new';
                    }
                }
                return $.ajax(url, {method: "POST", data: data})
                    .done(function (data) {
                        self.sharedPaymentUrl = data['shared_payment_url'];
                        self.accessCode = data['access_code'];
                    }).fail(function (jqXHR) {
                        var data = jqXHR.responseJSON;
                        alert(data['error'] ? data['error'] : 'Unknown error happened');
                        $('body').trigger('processStop');
                    });
            },

            placeOrder: function () {
                var self = this;
                if (self.validate()) {
                    self.isPlaceOrderActionAllowed(false);
                    $.when(
                        setPaymentInformationAction(
                            this.messageContainer,
                            {
                                method: this.getCode()
                            }
                        )
                    )
                    .then(self.getAccessCode.bind(self))
                    .done(function () {
                        window.location.href = self.sharedPaymentUrl;
                    })
                    .fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    })
                }
            }
        });
    }
);
