/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 *
 * The parse of the bar's configuration block
 * (view/frontend/web/js/model/config.js): text in, the configuration or null
 * out. A goal of the wrong shape is left out; a block that leaves nothing to
 * show gives null, and the bar renders nothing.
 *
 * Run: node --test 'Test/Js/*.test.mjs'
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { loadAmd } from './load-amd.mjs';

const parse = (text) => loadAmd('model/config.js').parse(text);

const PRICE_FORMAT = { pattern: '£%s', precision: 2, decimalSymbol: '.', groupSymbol: ',' };

const goal = (overrides = {}) => ({
    code: 'spend',
    priority: 10,
    amount: 70,
    amountKey: 'goals.spend',
    labels: { initial: 'Spend {{goal}}', progress: '{{goal_left}} to go', achieved: 'Done', terms: 'Terms' },
    link: 'https://shop.example/lamp.html',
    image: 'https://shop.example/lamp.jpg',
    ...overrides,
});

const block = (goals, priceFormat = PRICE_FORMAT) => JSON.stringify({ goals, priceFormat });

test('a valid block gives its goals and price format', () => {
    assert.deepEqual(parse(block([goal()])), {
        goals: [{
            code: 'spend',
            amount: 70,
            amountKey: 'goals.spend',
            labels: { initial: 'Spend {{goal}}', progress: '{{goal_left}} to go', achieved: 'Done', terms: 'Terms' },
            link: 'https://shop.example/lamp.html',
            image: 'https://shop.example/lamp.jpg',
        }],
        priceFormat: PRICE_FORMAT,
    });
});

test('broken JSON gives nothing', () => {
    assert.equal(parse('{"goals": ['), null);
});

test('a block without a price format gives nothing', () => {
    assert.equal(parse(JSON.stringify({ goals: [goal()] })), null);
});

for (const [broken, why] of [
    [{ code: '' }, 'an empty code'],
    [{ amount: 0 }, 'an amount of nothing'],
    [{ amount: '70' }, 'an amount that is not a number'],
    [{ amountKey: '' }, 'an empty amount key'],
    [{ labels: { initial: 'Spend {{goal}}', progress: 5, achieved: 'Done', terms: '' } }, 'a label that is not text'],
    [{ link: null }, 'a link that is not text'],
    [{ image: 7 }, 'an image that is not text'],
]) {
    test(`a goal with ${why} is left out while the valid one stays`, () => {
        const parsed = parse(block([goal({ code: 'broken', ...broken }), goal()]));

        assert.deepEqual(parsed?.goals.map((kept) => kept.code), ['spend']);
    });
}

test('a block whose every goal is of the wrong shape gives nothing', () => {
    assert.equal(parse(block([goal({ amount: -1 })])), null);
});
