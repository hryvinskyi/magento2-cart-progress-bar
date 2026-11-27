/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

/**
 * The bar's arithmetic and wording, free of the page: the phase of a goal for
 * the shopper's amount, the goal the bar shows when there are several, the
 * label with its placeholders filled, and the amount read from the cart
 * section. Amounts are compared in hundredths, so a float sum never misses
 * the goal by a fraction of a penny.
 */
define([], function () {
    'use strict';

    /**
     * @param {number} amount
     * @returns {number} The amount in hundredths
     */
    function hundredths(amount) {
        return Math.round(amount * 100);
    }

    /**
     * Where the shopper stands towards a goal.
     *
     * @param {number} goal Amount of the goal
     * @param {number} amount The shopper's amount towards it
     * @returns {{phase: string, left: number, percent: number}}
     */
    function state(goal, amount) {
        var goalHundredths = hundredths(goal),
            amountHundredths = hundredths(amount);

        if (goalHundredths <= 0 || amountHundredths >= goalHundredths) {
            return { phase: 'achieved', left: 0, percent: 100 };
        }
        if (amountHundredths <= 0) {
            return { phase: 'initial', left: goalHundredths / 100, percent: 0 };
        }

        return {
            phase: 'progress',
            left: (goalHundredths - amountHundredths) / 100,
            percent: Math.floor(amountHundredths * 100 / goalHundredths)
        };
    }

    /**
     * The goal the bar shows: the first one, by priority, that the shopper
     * has not reached; the first goal when every one is reached.
     *
     * @param {Array<{amount: number}>} goals Goals sorted by priority
     * @param {function(object): number} amountOf The shopper's amount towards a goal
     * @returns {object|null}
     */
    function pick(goals, amountOf) {
        var index;

        for (index = 0; index < goals.length; index++) {
            if (state(goals[index].amount, amountOf(goals[index])).phase !== 'achieved') {
                return goals[index];
            }
        }

        return goals.length > 0 ? goals[0] : null;
    }

    /**
     * The label of a phase, with every `{{goal}}` and `{{goal_left}}` filled.
     * The result is text, never markup.
     *
     * @param {Object<string, string>} labels Labels by phase
     * @param {string} phase
     * @param {string} formattedGoal The goal, formatted as a price
     * @param {string} formattedLeft The amount left, formatted as a price
     * @returns {string}
     */
    function message(labels, phase, formattedGoal, formattedLeft) {
        var label = labels && typeof labels[phase] === 'string' ? labels[phase] : '';

        return label.split('{{goal}}').join(formattedGoal).split('{{goal_left}}').join(formattedLeft);
    }

    /**
     * The amount at a dot path of the cart section: a finite number not
     * below nothing, or 0 for anything else.
     *
     * @param {*} section The cart customer-data section
     * @param {string} path Dot path, e.g. `some_module.12`
     * @returns {number}
     */
    function amountAt(section, path) {
        var value = section,
            keys = String(path).split('.'),
            index;

        for (index = 0; index < keys.length; index++) {
            if (value === null || typeof value !== 'object'
                || !Object.prototype.hasOwnProperty.call(value, keys[index])
            ) {
                return 0;
            }
            value = value[keys[index]];
        }

        return typeof value === 'number' && isFinite(value) && value >= 0 ? value : 0;
    }

    return Object.freeze({
        state: state,
        pick: pick,
        message: message,
        amountAt: amountAt
    });
});
