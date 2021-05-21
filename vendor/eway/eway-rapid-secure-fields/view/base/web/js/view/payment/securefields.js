define(
    [
        'jquery',
        'Eway_EwayRapid/js/view/payment/common'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_SecureFields/payment/ewayrapid-securefields',
                showFullCard: true
            },

            initObservable: function () {
                this._super()
                    .observe('showFullCard');

                return this;
            },

            validFields: {'name': false, 'card': false, 'expiry': false},
            publicApiKeyError: false,

            validate: function (field) {
                if (field == undefined) {
                    var result = true;
                    for (field in this.validFields) {
                        result = this.validate(field) && result;
                    }
                    return result;
                } else {
                    if (!this.showFullCard()) {
                        // Do not validate fields in case using token payment
                        return true;
                    }

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

            loadScript: function () {
                var state = this.scriptLoaded;
                var self = this;
                if (!state()) {
                    $('body').trigger('processStart');
                    require(['ewayrapid'], function () {
                        state(true);
                        self.setupSecureFields();
                        $('body').trigger('processStop');
                    });
                } else {
                    self.setupSecureFields();
                }
            },

            setupSecureFields: function () {
                var publicApiKey = this.getMethodConfigData('publicApiKey');
                var fieldStyles = this.getMethodConfigData('fieldStyles');

                var fields = {
                    nameFieldConfig : {
                        publicApiKey: publicApiKey,
                        fieldDivId: "eway-secure-field-name",
                        fieldType: "name",
                        styles: fieldStyles
                    },
                    cardFieldConfig : {
                        publicApiKey: publicApiKey,
                        fieldDivId: "eway-secure-field-card",
                        fieldType: "card",
                        styles: fieldStyles,
                        maskValues: false
                    },
                    expiryFieldConfig : {
                        publicApiKey: publicApiKey,
                        fieldDivId: "eway-secure-field-expiry",
                        fieldType: "expiry",
                        styles: fieldStyles
                    }
                };
                var callBack = this.secureFieldCallback.bind(this);
                for (var field in fields) {
                    $('#' + fields[field].fieldDivId).empty();
                    eWAY.setupSecureField(fields[field], callBack);
                }
            },

            secureFieldCallback: function (event) {
                var field = event.targetField;
                if (!event.fieldValid || !event.valueIsValid) {
                    this.validFields[field] = false;
                    if (event.errors == 'V6143') {
                        this.publicApiKeyError = true;
                    }
                } else {
                    this.validFields[field] = true;
                    // set the hidden Secure Field Code field
                    $('#secured_card_data').val(event.secureFieldCode);
                }
                this.validate(field);
            },

            onTokenSelect: function (token) {
                this._super(token);
                this.showFullCard(!token);
                if (this.publicApiKeyError) {
                    this.validate();
                }
            },

            editToken: function () {
                this._super();
                this.showFullCard(true);
                if (this.publicApiKeyError) {
                    this.validate();
                }
            },

            cancelEditToken: function () {
                this._super();
                this.showFullCard(false);
            }
        });
    }
);