define(["uiComponent", "dataServicesBase", "jquery", "Magento_Catalog/js/price-utils"], function (
    Component,
    ds,
    $,
    priceUnits
) {
    "use strict"
    return Component.extend({
        defaults: {
            template:
                "Magento_ProductRecommendationsLayout/recommendations.html",
            recs: [],
        },
        initialize: function (config) {
            this._super(config)
            this.pagePlacement = config.pagePlacement
            this.placeholderUrl = config.placeholderUrl
            this.priceFormat = config.priceFormat
            this.priceUnits = priceUnits
            this.currencyConfiguration = config.currencyConfiguration
            this.alternateEnvironmentId = config.alternateEnvironmentId
            return this
        },
        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this._super().observe(["recs"])
        },

        //Helper function to add addToCart button & convert currency
        /**
         *
         * @param {@} response is type Array.
         * @returns type Array.
         */
        processResponse(response) {
            const units = []
            if (!response.length) {
                return units
            }

            for (let i = 0; i < response.length; i++) {
                response[i].products = response[i].products.slice(
                    0,
                    response[i].displayNumber,
                )
                for (let j = 0; j < response[i].products.length; j++) {
                    if (response[i].products[j].productId) {
                        const form_key = $.cookie("form_key")
                        const url = this.createAddToCartUrl(
                            response[i].products[j].productId,
                        )
                        const postUenc = this.encodeUenc(url)
                        const addToCart = {form_key, url, postUenc}
                        response[i].products[j].addToCart = addToCart
                    }

                    if (
                        this.currencyConfiguration &&
                        response[i].products[j].currency !==
                        this.currencyConfiguration.currency
                    ) {
                        response[i].products[j].prices.minimum.final =
                            (response[i].products[j].prices &&
                            response[i].products[j].prices.minimum &&
                            response[i].products[j].prices.minimum.final)
                                ? this.convertPrice(response[i].products[j].prices.minimum.final)
                                : null;
                        response[i].products[
                            j
                        ].currency = this.currencyConfiguration.currency
                    }
                }
                units.push(response[i])
            }
            units.sort((a, b) => a.displayOrder - b.displayOrder)
            return units
        },

        loadJsAfterKoRender: function (self, unit) {
            const renderEvent = new CustomEvent("render", {detail: unit})
            document.dispatchEvent(renderEvent)
        },

        convertPrice: function (price) {
            return parseFloat(price * this.currencyConfiguration.rate)
        },

        createAddToCartUrl(productId) {
            const currentLocation = document.location.href
            const currentLocationUENC = encodeURIComponent(
                this.encodeUenc(currentLocation),
            )
            const httpVersion = currentLocation.split("/")[0]
            const domain = httpVersion + "//" + document.domain
            const postUrl =
                domain +
                "/checkout/cart/add/uenc/" +
                currentLocationUENC +
                "/product/" +
                productId
            return postUrl
        },

        encodeUenc: function (value) {
            const regex = /=/gi
            return btoa(value).replace(regex, ",")
        },
    })
})
