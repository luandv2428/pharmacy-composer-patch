define(
    [
        'jquery',
        'Eway_EwayRapid/js/view/payment/method-renderer/common',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function ($, Component, creditCardData) {
        'use strict';

        return Component.extend({
            PAYMENT_TYPE: {
                PAYPAL: 'PayPal',
                CREDITCARD: 'Credit Card',
                MASTERPASS: 'MasterPass',
                VISACHECKOUT: 'VisaCheckout'
            },

            defaults: {
                template: 'Eway_DirectConnection/payment/ewayrapid-direct',
                showFullCard: true,
                creditCardName: '',
                visaCheckoutResponse: '',
                visaCheckoutCallId: '',
                paymentType: 'Credit Card',
                imports: {
                    onPaymentTypeChange: 'paymentType'
                }
            },

            getClientSideEncryptionKey: function () {
                return this.getMethodConfigData('encryptionKey');
            },

            initObservable: function () {
                this._super()
                    .observe('showFullCard creditCardName paymentType visaCheckoutResponse visaCheckoutCallId');
                return this;
            },

            isEnablePaypal: function () {
                return this.getMethodConfigData('enable_paypal');
            },

            isEnableVisaCheckout: function () {
                return this.getMethodConfigData('enable_visa_checkout');
            },

            isEnableMasterPass: function () {
                return this.getMethodConfigData('enable_masterpass');
            },

            isEnableDigitalWallets: function () {
                return this.isEnablePaypal() || this.isEnableVisaCheckout() || this.isEnableMasterPass();
            },

            isCreditCardSelected: function () {
                return this.paymentType() == this.PAYMENT_TYPE.CREDITCARD;
            },

            placeOrder: function (submitOrder) {
                if (submitOrder !== true && this.paymentType() == this.PAYMENT_TYPE.VISACHECKOUT) {
                    $('body').trigger('processStart');
                    $('#ewayrapid-visacheckout-button').click();
                } else {
                    this._super();
                }
            },

            visaCheckoutPaymentSuccess: function (payment) {
                this.visaCheckoutCallId(payment["callid"]);
                this.placeOrder(true);
            },

            onPaymentTypeChange: function (paymentType) {
                var self = this;
                if (paymentType == this.PAYMENT_TYPE.VISACHECKOUT) {
                    $('body').trigger('processStart');
                    require([this.getMethodConfigData('visa_sdk_url')], function () {
                        window.onVisaCheckoutReady = function () {
                            V.init({ apikey: self.getMethodConfigData('visa_checkout_apikey')});
                            V.on("payment.success", function (payment) {
                                $('body').trigger('processStop');
                                self.visaCheckoutPaymentSuccess(payment);
                            });

                            V.on("payment.cancel", function (payment) {
                                $('body').trigger('processStop');
                                self.isPlaceOrderActionAllowed(true);
                            });
                            V.on("payment.error", function (payment, error) {
                                $('body').trigger('processStop');
                                self.isPlaceOrderActionAllowed(true);
                            });
                        }
                        window.onVisaCheckoutReady();
                        $('body').trigger('processStop');
                    });
                }
            },

            onTokenSelect: function (token) {
                this._super(token);

                if (token) {
                    this.creditCardName(token.owner);
                    this.creditCardExpYear(token.exp_year);
                    this.creditCardExpMonth(token.exp_month);
                } else {
                    this.creditCardName('');
                    this.creditCardExpYear('');
                    this.creditCardExpMonth('');
                }

                if (token && token.type) {
                    this.selectedCardType(token.type);
                    creditCardData.creditCard = {code: {size: (token.type == 'AE') ? 4 : 3}};
                } else {
                    this.selectedCardType(null);
                }
                this.showFullCard(!token);
                this.paymentType(this.PAYMENT_TYPE.CREDITCARD);
            },

            editToken: function () {
                this._super();
                this.showFullCard(true);
            },

            cancelEditToken: function () {
                this._super();
                this.showFullCard(false);
            },

            loadScript: function () {
                var state = this.scriptLoaded;
                var self = this;
                $('body').trigger('processStart');
                require(['ewayecrypt'], function () {
                    state(true);
                    $('body').trigger('processStop');
                });
            },

            getAdditionalData: function () {
                if (this.paymentType() == this.PAYMENT_TYPE.VISACHECKOUT) {
                    return {'SecuredCardData': 'VCOCallID:' + this.visaCheckoutCallId()}
                }

                if (typeof eCrypt != 'undefined') {
                    var data = {
                        'Name': this.creditCardName(),
                        'CVN':  eCrypt.encryptValue(this.creditCardVerificationNumber()),
                        'ExpiryYear': this.creditCardExpYear(),
                        'ExpiryMonth': this.creditCardExpMonth()
                    };
                    if (!this.isEditing()) {
                        data['Number'] = eCrypt.encryptValue(this.creditCardNumber());
                    }

                    return data;
                } else {
                    return {};
                }
            },

            getForm: function () {
                return $('#co-ewayrapid-direct-form');
            },

            validate: function () {
                var form = this.getForm();
                form.validate().form();

                // validate parent form
                var hasInvalidField = !! form.validate().errorList.length;
                if (hasInvalidField) {
                    $('body').trigger('processStop');
                }

                return ! hasInvalidField;
            }
        });
    }
);
