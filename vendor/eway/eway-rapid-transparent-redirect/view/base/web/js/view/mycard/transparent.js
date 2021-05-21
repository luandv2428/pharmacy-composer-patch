define(
    [
        'jquery',
        'Eway_DirectConnection/js/view/mycard/direct'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eway_TransparentRedirect/mycard/transparent',
                isEditing: window.ewayRapidConfig.payment['ewayrapid']['is_editing'],
                accessCode: ''
            },

            initObservable: function () {
                this._super().observe('formActionUrl accessCode');
                return this;
            },

            initFormEvents: function () {
                this.getForm().on('saveCard', this.processSaveCard.bind(this));
            },

            getDirectConnectionConfigData: function (key) {
                var methodConfig = this.getConfigData('direct');
                return methodConfig[key];
            },

            getCcAvailableTypes: function () {
                return this.getDirectConnectionConfigData('availableTypes');
            },

            getCcMonths: function () {
                return this.getDirectConnectionConfigData('months');
            },

            getCcYears: function () {
                return this.getDirectConnectionConfigData('years');
            },

            loadScript: function () {
                var state = this.scriptLoaded;
                if (!state()) {
                    $('body').trigger('processStart');
                    require(['ewaytransparent'], function () {
                        state(true);
                        $('body').trigger('processStop');
                    });

                    this.initFormEvents();
                    this.selectedToken(null);
                    this.initTokenList();
                }
            },

            getAccessCode: function () {
                var self = this;
                var url = this.getMethodConfigData('mycard_get_access_code_url');
                var dataExcludeCreditCard = $('.billing-address input, .billing-address select')
                return $.ajax(url, {method: "POST", data: dataExcludeCreditCard.serialize()})
                    .then(function (data) {
                        self.getForm().attr('action', data['form_action_url']);
                        self.accessCode(data['access_code']);
                    }, function (jqXHR) {
                        var data = jqXHR.responseJSON;
                        $('body').trigger('processStop');
                        alert(data['error'] ? data['error'] : 'Unknown error happened');
                    });
            },

            processSaveCard: function () {
                var form = this.getForm();
                form.validate().form();
                form.trigger('afterValidate.beforeSubmit');

                // validate parent form
                var result = this.validate();
                if (form.validate().errorList.length || !result) {
                    $('body').trigger('processStop');
                    return false;
                }
                var self = this;
                this.getAccessCode()
                    .done(function () {
                        eWAY.process(
                            self.getForm()[0],
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
                                },
                                onTimeout: function (e) {
                                    alert('The request has timed out.');
                                    $('body').trigger('processStop');
                                }
                            }
                        );
                    });
            }
        });
    }
);
