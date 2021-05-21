define(
    [
        'jquery',
        'Eway_SecureFields/js/view/payment/securefields'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_SecureFields/mycard/securefields'
            },

            getForm: function () {
                return $('#mycard_edit_form');
            },

            initObservable: function () {
                this.observe('scriptLoaded tokenList selectedToken isEditing');
                return this;
            },

            loadScript: function () {
                this._super();
                this.isEditing(window.ewayRapidConfig.payment['ewayrapid']['is_editing']);
                this.getForm().on('saveCard', this.processSaveCard.bind(this));
                this.initTokenList();
            },

            onTokenSelect: function (token) {
                // Do nothing
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

                if (form.data('ajax-submit')) {
                    form.trigger('ajax-submit');
                } else {
                    form.submit();
                }
            },

            validate: function (field) {
                if (field == undefined) {
                    var result = true;
                    for (field in this.validFields) {
                        result = this.validate(field) && result;
                    }
                    return result;
                } else {
                    if (this.isEditing() && field == 'card') {
                        // Do not validate card number when update token
                        return true;
                    }

                    if (!this.validFields[field]) {
                        if (this.publicApiKeyError) {
                            $('#eway-secure-field-' + field + '-error').html('Invalid Public API Key');
                        }
                        $('#eway-secure-field-' + field + '-error').show();
                        return false;
                    } else {
                        $('#eway-secure-field-' + field + '-error').hide();
                        return true;
                    }
                }
            },
        });
    }
);
