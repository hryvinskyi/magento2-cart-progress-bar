/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

/**
 * One placement of the bar (top, minicart, cart). It reads the page's
 * configuration, follows the cart section, and shows the goal the progress
 * model picks, with its text formatted in the store's price format and its
 * amounts set apart from the words. A goal with terms gets a button that
 * shows the terms in place of the text and back. Until the section arrives
 * it shows the first goal's initial state, which is the same for every
 * visitor. Without a valid configuration it shows nothing.
 */
define([
    'uiComponent',
    'ko',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/price-utils',
    'Hryvinskyi_CartProgressBar/js/model/config',
    'Hryvinskyi_CartProgressBar/js/model/progress'
], function (Component, ko, $t, customerData, priceUtils, barConfig, progress) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Hryvinskyi_CartProgressBar/bar',
            placement: ''
        },

        /**
         * @returns {Object} Chainable
         */
        initialize: function () {
            var cart;

            this._super();
            this.config = barConfig.read();
            this.visible = ko.observable(false);
            this.parts = ko.observableArray([]);
            this.percent = ko.observable(0);
            this.phase = ko.observable('initial');
            this.link = ko.observable('');
            this.image = ko.observable('');
            this.termsParts = ko.observableArray([]);
            this.termsShown = ko.observable(false);
            this.goalCode = ko.observable('');
            this.hasTerms = ko.pureComputed(function () {
                return this.termsParts().length > 0;
            }, this);
            this.shownParts = ko.pureComputed(function () {
                return this.termsShown() && this.hasTerms() ? this.termsParts() : this.parts();
            }, this);
            this.termsToggleLabel = ko.pureComputed(function () {
                return this.termsShown() ? $t('Hide the terms') : $t('Show the terms');
            }, this);
            this.classes = ko.pureComputed(function () {
                return 'cart-progress-bar--' + this.phase()
                    + ' cart-progress-bar--' + this.placement
                    + ' cart-progress-bar--goal-' + this.goalCode()
                    + (this.termsShown() ? ' cart-progress-bar--terms' : '');
            }, this);

            if (this.config === null) {
                return this;
            }
            cart = customerData.get('cart');
            this.show(cart());
            cart.subscribe(this.show, this);

            return this;
        },

        /**
         * Show the goal the shopper is working towards with the given cart section.
         *
         * @param {Object} section The cart customer-data section
         */
        show: function (section) {
            var goal = progress.pick(this.config.goals, function (candidate) {
                    return progress.amountAt(section, candidate.amountKey);
                }),
                shown = progress.display(goal, progress.amountAt(section, goal.amountKey), this.price.bind(this));

            this.parts(shown.parts);
            this.percent(shown.percent);
            this.phase(shown.phase);
            this.link(goal.link);
            this.image(goal.image);
            this.termsParts(shown.termsParts);
            this.goalCode(goal.code);
            this.visible(true);
        },

        /**
         * Show the terms in place of the text, or the text again.
         */
        toggleTerms: function () {
            this.termsShown(!this.termsShown());
        },

        /**
         * @param {number} amount
         * @returns {string} The amount in the store's price format
         */
        price: function (amount) {
            return priceUtils.formatPrice(amount, this.config.priceFormat);
        }
    });
});
