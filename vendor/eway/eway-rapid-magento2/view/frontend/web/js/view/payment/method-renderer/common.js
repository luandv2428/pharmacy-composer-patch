define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, additionalValidators) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                scriptLoaded: false,
                saveCard: window.checkoutConfig.payment['ewayrapid']['save_card_checked'],
                canEditToken: window.checkoutConfig.payment['ewayrapid']['can_edit_token'],
                imports: {
                    onActiveChange: 'active',
                    onTokenSelect: 'selectedToken'
                },
                tokenList: [],
                selectedToken: null,
                isEditing: false
            },

            initObservable: function () {
                this._super()
                    .observe('active scriptLoaded saveCard tokenList selectedToken isEditing canEditToken');

                return this;
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

            getCode: function () {
                return 'ewayrapid';
            },

            getConfigData: function (key) {
                return window.checkoutConfig.payment[this.getCode()][key];
            },

            getMethodConfigData: function (key) {
                var connectionType = this.getConfigData('connectionType');
                var methodConfig = this.getConfigData(connectionType);
                return methodConfig[key];
            },

            isActive: function () {
                var active = (this.getCode() === this.isChecked());

                this.active(active);

                return active;
            },

            onActiveChange: function (isActive) {
                if (isActive && !this.scriptLoaded()) {
                    this.loadScript();

                    if (this.isTokenEnabled()) {
                        this.initTokenList();
                    }
                }
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
                this.canEditToken(this.customerAllowEditToken() && token);
                this.isEditing(false);
            },

            customerAllowEditToken: function () {
                return window.checkoutConfig.payment['ewayrapid']['can_edit_token'];
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

            loadScript: function () {
                // for specific method to overwrite
            },

            getData: function () {
                var additionalData = this.getAdditionalData();
                if (this.isTokenEnabled()) {
                    additionalData = this.getTokenData(additionalData);
                }
                return {
                    'method': this.item.method,
                    'additionalData': additionalData
                };
            },

            getTokenData: function (additionalData) {
                var tokenID = (this.selectedToken() ? this.selectedToken().token_id : '');
                additionalData['TokenID'] = tokenID;
                if (tokenID) {
                    if (this.isEditing()) {
                        // has token id & editing ==> update
                        additionalData['TokenAction'] = 'update';
                    } else {
                        // has token id & not edit ==> use
                        additionalData['TokenAction'] = 'use';
                    }
                } else if (this.saveCard()) {
                    // if save card is checked, action is 'new'
                    additionalData['TokenAction'] = 'new';
                } else {
                    // other case do not use token
                    additionalData['TokenAction'] = '';
                }

                return additionalData;
            },

            getAdditionalData: function () {
                return {};
            },

            placeOrder: function () {
                if (additionalValidators.validate() && this.validate()) {
                    this._super();
                }
            }
        });
    }
);
