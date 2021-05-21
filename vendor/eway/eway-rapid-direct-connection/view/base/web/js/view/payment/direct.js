define(
    [
        'jquery',
        'Eway_EwayRapid/js/view/payment/common',
        'underscore',
        'Eway_DirectConnection/js/model/credit-card-validation/validator'
    ],
    function ($, Component, _) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_DirectConnection/payment/ewayrapid-direct',
                showFullCard: true,
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardSsIssue: '',
                creditCardVerificationNumber: '',
                creditCardName: '',
                selectedCardType: null

            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'creditCardSsIssue',
                        'selectedCardType',
                        'creditCardName',
                        'showFullCard'
                    ]);

                return this;
            },

            loadScript: function () {
                var state = this.scriptLoaded;
                var self = this;
                $('body').trigger('processStart');
                self.getForm().attr('data-eway-encrypt-key', this.getMethodConfigData('encryptionKey'));
                require(['ewayecrypt'], function () {
                    state(true);
                    $('body').trigger('processStop');
                });
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

                this.showFullCard(!token);
            },

            editToken: function () {
                this._super();
                this.showFullCard(true);
            },

            cancelEditToken: function () {
                this._super();
                this.showFullCard(false);
            },

            getCcAvailableTypes: function () {
                return this.getMethodConfigData('availableTypes');
            },

            getCcMonths: function () {
                return this.getMethodConfigData('months');
            },

            getCcYears: function () {
                return this.getMethodConfigData('years');
            },

            getCcAvailableTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });
            },

            getCcMonthsValues: function () {
                return _.map(this.getCcMonths(), function (value, key) {
                    return {
                        'value': key,
                        'month': value
                    };
                });
            },

            getCcYearsValues: function () {
                return _.map(this.getCcYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });
            },

            submitOrder: function () {
                if (this.selectedToken()) {
                    $('#ewayrapid_cc_number').attr('disabled', 'disabled');
                };
                this._super();
            },

            _submitOrder: function () {
                if (!this.isEditing()) {
                    $('#ewayrapid_card_number').val(eCrypt.encryptValue(this.creditCardNumber()));
                }
                this._super();
            },
        });
    }
);