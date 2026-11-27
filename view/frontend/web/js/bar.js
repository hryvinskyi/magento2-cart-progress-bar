/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

/**
 * One placement of the bar (top, minicart, cart). It reads the page's
 * configuration, follows the cart section, and shows the goal the progress
 * model picks, with its text formatted in the store's price format. Until
 * the section arrives it shows the first goal's initial state, which is the
 * same for every visitor. Without a valid configuration it shows nothing.
 */
define([
    'uiComponent',
    'ko',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/price-utils',
    'Hryvinskyi_CartProgressBar/js/model/config',
    'Hryvinskyi_CartProgressBar/js/model/progress'
], function (Component, ko, customerData, priceUtils, barConfig, progress) {
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
            this.text = ko.observable('');
            this.percent = ko.observable(0);
            this.phase = ko.observable('initial');
            this.link = ko.observable('');
            this.image = ko.observable('');
            this.terms = ko.observable('');
            this.goalCode = ko.observable('');
            this.classes = ko.pureComputed(function () {
                return 'cart-progress-bar--' + this.phase()
                    + ' cart-progress-bar--' + this.placement
                    + ' cart-progress-bar--goal-' + this.goalCode();
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
                state = progress.state(goal.amount, progress.amountAt(section, goal.amountKey));

            this.text(progress.message(goal.labels, state.phase, this.price(goal.amount), this.price(state.left)));
            this.percent(state.percent);
            this.phase(state.phase);
            this.link(goal.link);
            this.image(goal.image);
            this.terms(goal.labels.terms);
            this.goalCode(goal.code);
            this.visible(true);
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
