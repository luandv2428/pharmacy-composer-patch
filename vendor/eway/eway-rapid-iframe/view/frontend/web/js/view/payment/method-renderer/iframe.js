define(
    [
        'jquery',
        'Eway_EwayRapid/js/view/payment/method-renderer/common',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_IFrame/payment/ewayrapid-iframe'
            },

            sharedPaymentUrl: '',
            accessCode: '',
            shouldPlaceOrder: false,

            loadScript: function () {
                var state = this.scriptLoaded;
                $('body').trigger('processStart');
                require(['ewayecrypt'], function () {
                    state(true);
                    eCrypt.init();
                    $('body').trigger('processStop');
                });
            },

            canCreateToken: function () {
                return this.getMethodConfigData('can_create_token');
            },

            getAccessCode: function () {
                var self = this;
                var url = this.getMethodConfigData('get_access_code_url');
                var data = {};
                if (quote.guestEmail) {
                    data['GuestEmail'] = quote.guestEmail;
                }
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
                        self.isPlaceOrderActionAllowed(true);
                        self.shouldPlaceOrder = false;
                    });
            },

            iframeResultCallback: function (result, transactionID, errors) {
                if (result == "Complete") {
                    this.shouldPlaceOrder = true;
                    this.placeOrder();
                } else {
                    if (result == "Error") {
                        alert("There was a problem completing the payment: " + errors);
                    }
                    $('body').trigger('processStop');
                    this.isPlaceOrderActionAllowed(true);
                    this.shouldPlaceOrder = false;
                }
            },

            getPlaceOrderDeferredObject: function () {
                var self = this;
                return this._super().fail(function () {
                    $('body').trigger('processStop');
                    self.shouldPlaceOrder = false;
                    self.isPlaceOrderActionAllowed(true);
                });
            },

            getAdditionalData: function () {
                return {
                    'AccessCode': this.accessCode
                };
            },

            placeOrder: function () {
                if (this.shouldPlaceOrder) {
                    this._super();
                } else {
                    if (this.validate()) {
                        $('body').trigger('processStart');
                        this.getAccessCode().then(function () {
                            var eWAYConfig = {
                                sharedPaymentUrl: this.sharedPaymentUrl
                            };
                            eCrypt.showModalPayment(eWAYConfig, this.iframeResultCallback.bind(this));
                        }.bind(this));
                    }
                }
            }
        });
    }
);
