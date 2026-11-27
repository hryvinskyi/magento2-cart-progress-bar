/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

/**
 * The bar's configuration, from the JSON block the page prints once. The
 * block is found by its data-role alone, wherever a script optimizer moved
 * it. Its text is parsed into the shape the bar needs: a goal of the wrong
 * shape is left out, and a block that leaves no goal, or no price format,
 * gives null, so the bar renders nothing.
 */
define([], function () {
    'use strict';

    var SELECTOR = '[data-role="cart-progress-bar-config"]',
        LABELS = ['initial', 'progress', 'achieved', 'terms'],
        read;

    /**
     * @param {*} value
     * @returns {boolean} Whether the value is a plain object
     */
    function isObject(value) {
        return value !== null && typeof value === 'object' && !Array.isArray(value);
    }

    /**
     * @param {*} value
     * @returns {boolean} Whether the value is a string with something in it
     */
    function isFilled(value) {
        return typeof value === 'string' && value !== '';
    }

    /**
     * @param {*} raw
     * @returns {Object<string, string>|null} The labels by phase, or null
     */
    function parseLabels(raw) {
        var labels = {},
            index;

        if (!isObject(raw)) {
            return null;
        }
        for (index = 0; index < LABELS.length; index++) {
            if (typeof raw[LABELS[index]] !== 'string') {
                return null;
            }
            labels[LABELS[index]] = raw[LABELS[index]];
        }

        return Object.freeze(labels);
    }

    /**
     * @param {*} raw
     * @returns {object|null} The goal, or null when it is of the wrong shape
     */
    function parseGoal(raw) {
        var labels = isObject(raw) ? parseLabels(raw.labels) : null;

        if (labels === null
            || !isFilled(raw.code)
            || typeof raw.amount !== 'number' || !isFinite(raw.amount) || raw.amount <= 0
            || !isFilled(raw.amountKey)
            || typeof raw.link !== 'string'
            || typeof raw.image !== 'string'
        ) {
            return null;
        }

        return Object.freeze({
            code: raw.code,
            amount: raw.amount,
            amountKey: raw.amountKey,
            labels: labels,
            link: raw.link,
            image: raw.image
        });
    }

    /**
     * Parse the text of the configuration block.
     *
     * @param {string} text
     * @returns {{goals: Array<object>, priceFormat: object}|null}
     */
    function parse(text) {
        var raw,
            goals;

        try {
            raw = JSON.parse(text);
        } catch (error) {
            return null;
        }
        if (!isObject(raw) || !isObject(raw.priceFormat) || !Array.isArray(raw.goals)) {
            return null;
        }
        goals = raw.goals.map(parseGoal).filter(function (goal) {
            return goal !== null;
        });

        return goals.length > 0 ? Object.freeze({ goals: Object.freeze(goals), priceFormat: raw.priceFormat }) : null;
    }

    /**
     * The configuration of the page, parsed on the first call.
     *
     * @returns {{goals: Array<object>, priceFormat: object}|null}
     */
    function config() {
        var element;

        if (read === undefined) {
            element = document.querySelector(SELECTOR);
            read = element === null ? null : parse(element.textContent);
            if (element !== null && read === null) {
                console.warn('Cart progress bar: the configuration block is not valid; the bar is not shown.');
            }
        }

        return read;
    }

    return Object.freeze({
        parse: parse,
        read: config
    });
});
