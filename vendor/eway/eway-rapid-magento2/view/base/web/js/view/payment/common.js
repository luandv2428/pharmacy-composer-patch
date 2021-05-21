define(
    [
        'jquery',
        'uiElement'
    ],
    function ($, Element) {
        'use strict';

        return Element.extend({
            defaults: {
                scriptLoaded: false,
                saveCard: window.ewayRapidConfig.payment['ewayrapid']['save_card_checked'],
                canEditToken: true,
                imports: {
                    onTokenSelect: 'selectedToken'
                },
                tokenList: [],
                isEditing: false,
                selectedToken: null
            },

            getForm: function () {
                return $('#edit_form');
            },

            initObservable: function () {
                this._super()
                    .observe('scriptLoaded saveCard tokenList selectedToken isEditing canEditToken');

                var form = this.getForm();
                var self = this;

                form.on('changePaymentMethod', function (event, method) {
                    if (method == 'ewayrapid') {
                        form.off('submitOrder')
                            .on('submitOrder.ewayrapid', self.submitOrder.bind(self));
                    } else {
                        form.off('submitOrder.ewayrapid');
                    }
                });

                form.trigger(
                    'changePaymentMethod',
                    [
                        form.find(':radio[name="payment[method]"]:checked').val()
                    ]
                );

                if (self.isTokenEnabled()) {
                    self.initTokenList();
                }

                return self;
            },

            submitOrder: function () {
                var form = this.getForm();
                form.validate().form();
                form.trigger('afterValidate.beforeSubmit');
                $('body').trigger('processStop');

                // validate parent form
                if (form.validate().errorList.length || !this.validate()) {
                    return false;
                }
                this.getTokenData();
                this._submitOrder();
            },

            _submitOrder: function () {
                // for specific method to overwrite
                this.getForm().trigger('realOrder');
            },

            loadScript: function () {
                // for specific method to overwrite
            },

            getConfigData: function (key) {
                return window.ewayRapidConfig.payment['ewayrapid'][key];
            },

            getMethodConfigData: function (key) {
                var connectionType = this.getConfigData('connectionType');
                var methodConfig = this.getConfigData(connectionType);
                return methodConfig[key];
            },

            isTokenEnabled: function () {
                return this.getConfigData('token_enabled');
            },

            getSaveText: function () {
                return this.getConfigData('save_text');
            },

            validate: function () {
                // for specific method to overwrite
                return true;
            },

            initTokenList: function () {
                var tokenArr = this.getConfigData('token_list');
                var selectedToken = this.getConfigData('selected_token');
                if (!$.isEmptyObject(tokenArr)) {
                    for (var tokenId in tokenArr) {
                        var token = tokenArr[tokenId];
                        this.tokenList.push(token);
                        if (selectedToken == tokenId) {
                            this.selectedToken(token);
                        }
                    }
                }
            },

            onTokenSelect: function (token) {
                this.canEditToken(token);
                this.isEditing(false);
            },

            editToken: function () {
                this.isEditing(true);
            },

            cancelEditToken: function () {
                this.isEditing(false);
            },

            getSelectedCardNumber: function () {
                if (this.selectedToken()) {
                    return this.selectedToken().card;
                }
                return '';
            },

            getTokenData: function () {
                if (this.isTokenEnabled()) {
                    var tokenID = (this.selectedToken() ? this.selectedToken().token_id : '');
                    var tokenAction = '';

                    if (tokenID) {
                        if (this.isEditing()) {
                            // has token id & editing ==> update
                            tokenAction = 'update';
                        } else {
                            // has token id & not edit ==> use
                            tokenAction = 'use';
                        }
                    } else if (this.saveCard()) {
                        // if save card is checked, action is 'new'
                        tokenAction = 'new';
                    }

                    $('#ewayrapid_token_id').val(tokenID);
                    $('#ewayrapid_token_action').val(tokenAction);
                }
            }
        });
    }
);