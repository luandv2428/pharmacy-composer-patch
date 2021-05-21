define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        var connectionType = window.checkoutConfig.payment.ewayrapid.connectionType;
        var methodRenderer = window.checkoutConfig.payment.ewayrapid[connectionType]['method_renderer'];
        rendererList.push(
            {
                type: 'ewayrapid',
                component: methodRenderer
            }
        );

        /**
         * Add view logic here if needed
         */

        return Component.extend({});
    }
);
