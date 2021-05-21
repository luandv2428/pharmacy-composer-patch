define(
    [
        'jquery',
        'Eway_DirectConnection/js/view/payment/direct'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_DirectConnection/mycard/direct'
            },

            initObservable: function () {
                this.observe([
                    'creditCardType',
                    'creditCardExpYear',
                    'creditCardExpMonth',
                    'creditCardNumber',
                    'selectedCardType',
                    'creditCardName',
                    'showFullCard',
                    'scriptLoaded',
                    'tokenList',
                    'selectedToken',
                    'isEditing'
                ]);

                return this;
            },

            getForm: function () {
                return $('#mycard_edit_form');
            },

            loadScript: function () {
                this._super();
                this.isEditing(window.ewayRapidConfig.payment['ewayrapid']['is_editing']);
                this.getForm().on('saveCard', this.processSaveCard.bind(this));
                this.selectedToken(null);
                this.initTokenList();
            },

            onTokenSelect: function (token) {
                if (token) {
                    this.creditCardName(token.owner);
                    this.creditCardExpYear(token.exp_year);
                    this.creditCardExpMonth(token.exp_month);
                } else {
                    this.creditCardName('');
                    this.creditCardExpYear('');
                    this.creditCardExpMonth('');
                }
            },

            processSaveCard: function () {
                var form = this.getForm();
                var result = this.validate();
                form.validate().form();
                form.trigger('afterValidate.beforeSubmit');

                // validate parent form
                if (form.validate().errorList.length || !result) {
                    $('body').trigger('processStop');
                    return false;
                }

                if (!this.isEditing()) {
                    $('#ewayrapid_card_number').val(eCrypt.encryptValue(this.creditCardNumber()));
                }

                if (form.data('ajax-submit')) {
                    form.trigger('ajax-submit');
                } else {
                    form.submit();
                }
            }

        });
    }
);
