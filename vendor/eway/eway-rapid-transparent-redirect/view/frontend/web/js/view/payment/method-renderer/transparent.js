define(
    [
        'jquery',
        'Eway_DirectConnection/js/view/payment/method-renderer/direct',
        'Magento_Checkout/js/action/set-payment-information'
    ],
    function ($, Component, setPaymentInformationAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_TransparentRedirect/payment/ewayrapid-transparent',
                formActionUrl: '',
                accessCode: ''
            },

            initObservable: function () {
                this._super()
                    .observe('formActionUrl accessCode');

                return this;
            },

            loadScript: function () {
                var state = this.scriptLoaded;
                if (!state()) {
                    $('body').trigger('processStart');
                    require(['ewaytransparent'], function () {
                        state(true);
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
                        self.formActionUrl(data['form_action_url']);
                        self.accessCode(data['access_code']);
                    }).fail(function (jqXHR) {
                        var data = jqXHR.responseJSON;
                        alert(data['error'] ? data['error'] : 'Unknown error happened');
                    });
            },

            getForm: function () {
                return $('#co-ewayrapid-transparent-form');
            },

            visaCheckoutPaymentSuccess: function (payment) {
                $('body').trigger('processStart');
                //Change Json payment obj to string
                var str = JSON.stringify(payment);
                //Save full encrypted response and callid on page for later use
                str = encodeURIComponent(str);
                this.visaCheckoutResponse(str);
                this.visaCheckoutCallId(payment["callid"]);

                this.getForm().submit();
            },

            placeOrder: function () {
                var self = this;
                if (self.validate()) {
                    $('body').trigger('processStart');
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
                        if (self.paymentType() == self.PAYMENT_TYPE.VISACHECKOUT) {
                            $('#ewayrapid-visacheckout-button').click();
                        } else {
                            self.transparentSubmit();
                        }
                    })
                    .fail(function () {
                        $('body').trigger('processStop');
                        self.isPlaceOrderActionAllowed(true);
                    })
                }
            },

            transparentSubmit: function () {
                eWAY.process(
                    document.getElementById("co-ewayrapid-transparent-form"),
                    {
                        autoRedirect: true,
                        onComplete: function (data) {
                            if (data.Is3DSecure) {
                                window.location.replace(data.RedirectUrl);
                            }
                        },
                        onError: function (e) {
                            alert('There was an error processing the request');
                            $('body').trigger('processStop');
                            self.isPlaceOrderActionAllowed(true);
                        },
                        onTimeout: function (e) {
                            alert('The request has timed out.');
                            $('body').trigger('processStop');
                            self.isPlaceOrderActionAllowed(true);
                        }
                    }
                );
            }
        });
    }
);
