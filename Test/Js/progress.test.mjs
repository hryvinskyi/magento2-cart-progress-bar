/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 *
 * The bar's arithmetic and wording (view/frontend/web/js/model/progress.js):
 * the phase of a goal for an amount, the goal the bar shows when there are
 * several, the label with its placeholders filled and cut into its words and
 * its amounts, and the shopper's amount
 * read from the cart section.
 *
 * Run: node --test 'Test/Js/*.test.mjs'
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { loadAmd } from './load-amd.mjs';

const progress = () => loadAmd('model/progress.js');

const LABELS = {
    initial: 'Spend {{goal}} & get a FREE lamp!',
    progress: 'Spend {{goal_left}} more to unlock your FREE lamp!',
    achieved: 'You have unlocked your FREE lamp!',
    terms: 'One lamp per order.',
};

test('an empty cart has not started towards the goal', () => {
    assert.deepEqual(progress().state(70, 0), { phase: 'initial', left: 70, percent: 0 });
});

test('a cart below the goal shows what is left and the whole percent reached', () => {
    assert.deepEqual(progress().state(70, 50), { phase: 'progress', left: 20, percent: 71 });
});

test('a cart a penny short is still in progress, a penny left, and the percent is not rounded up', () => {
    assert.deepEqual(progress().state(70, 69.99), { phase: 'progress', left: 0.01, percent: 99 });
});

test('a cart that reaches the goal exactly has achieved it', () => {
    assert.deepEqual(progress().state(70, 70), { phase: 'achieved', left: 0, percent: 100 });
});

test('a goal of nothing is achieved whatever the cart holds', () => {
    assert.deepEqual(progress().state(0, 0), { phase: 'achieved', left: 0, percent: 100 });
});

const A = { code: 'a', amount: 50 };
const B = { code: 'b', amount: 70 };

for (const [amount, expected, why] of [
    [60, 'b', 'the first goal reached, the next one not yet: the next one'],
    [40, 'a', 'no goal reached: the first one'],
    [80, 'a', 'every goal reached: the first one'],
]) {
    test(`with two goals and ${amount} in the cart the bar shows ${why}`, () => {
        assert.equal(progress().pick([A, B], () => amount).code, expected);
    });
}

test('the initial label carries the goal', () => {
    assert.equal(
        progress().message(LABELS, 'initial', '£70.00', '£70.00'),
        'Spend £70.00 & get a FREE lamp!'
    );
});

test('the progress label carries what is left', () => {
    assert.equal(
        progress().message(LABELS, 'progress', '£70.00', '£20.00'),
        'Spend £20.00 more to unlock your FREE lamp!'
    );
});

test('the achieved label is shown as it is written', () => {
    assert.equal(
        progress().message(LABELS, 'achieved', '£70.00', '£0.00'),
        'You have unlocked your FREE lamp!'
    );
});

test('every placeholder of a label is filled, each one wherever it occurs', () => {
    const labels = { ...LABELS, progress: '{{goal_left}} to go of {{goal}}: only {{goal_left}}!' };

    assert.equal(
        progress().message(labels, 'progress', '£70.00', '£20.00'),
        '£20.00 to go of £70.00: only £20.00!'
    );
});

test('a phase without a label shows no text', () => {
    assert.equal(progress().message({ initial: 'Spend {{goal}}' }, 'progress', '£70.00', '£20.00'), '');
});

test('what the bar shows for a goal fills the terms as it fills the label of the phase, and cuts both into words and amounts', () => {
    const goal = {
        amount: 70,
        labels: { ...LABELS, terms: 'Free Gift is available for orders over {{goal}} only, {{goal_left}} to go.' },
    };

    assert.deepEqual(progress().display(goal, 50, (amount) => `£${amount.toFixed(2)}`), {
        phase: 'progress',
        percent: 71,
        text: 'Spend £20.00 more to unlock your FREE lamp!',
        parts: [
            { text: 'Spend ', amount: false },
            { text: '£20.00', amount: true },
            { text: ' more to unlock your FREE lamp!', amount: false },
        ],
        terms: 'Free Gift is available for orders over £70.00 only, £20.00 to go.',
        termsParts: [
            { text: 'Free Gift is available for orders over ', amount: false },
            { text: '£70.00', amount: true },
            { text: ' only, ', amount: false },
            { text: '£20.00', amount: true },
            { text: ' to go.', amount: false },
        ],
    });
});

test('the amounts a label carries are parts of their own between its words, and no part is empty', () => {
    const goal = { amount: 70, labels: { ...LABELS, progress: '{{goal_left}} to go of {{goal}}{{goal_left}}!' } };

    assert.deepEqual(progress().display(goal, 50, (amount) => `£${amount.toFixed(2)}`).parts, [
        { text: '£20.00', amount: true },
        { text: ' to go of ', amount: false },
        { text: '£70.00', amount: true },
        { text: '£20.00', amount: true },
        { text: '!', amount: false },
    ]);
});

test('a label without placeholders is one part of words', () => {
    assert.deepEqual(progress().display({ amount: 70, labels: LABELS }, 70, (amount) => `£${amount}`).parts, [
        { text: 'You have unlocked your FREE lamp!', amount: false },
    ]);
});

test('the amount is read at a dot path of the cart section', () => {
    assert.equal(progress().amountAt({ goals: { gift: 50 } }, 'goals.gift'), 50);
});

for (const [section, why] of [
    [{ goals: {} }, 'a path the section does not hold'],
    [{ goals: { gift: '50' } }, 'a value that is not a number'],
    [{ goals: { gift: -5 } }, 'a negative value'],
]) {
    test(`${why} counts as nothing spent`, () => {
        assert.equal(progress().amountAt(section, 'goals.gift'), 0);
    });
}
