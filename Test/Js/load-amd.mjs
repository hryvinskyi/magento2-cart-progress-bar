/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 *
 * Loads one of this module's dependency-free AMD modules in Node: the file
 * runs as a function of a `define` that hands back what the module's factory
 * returns. It runs in the test's own realm, so the values it returns compare
 * with deepStrictEqual like any other.
 */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';

/**
 * @param {string} path Path of the module under view/frontend/web/js
 * @returns {object} What the module's factory returns
 */
export function loadAmd(path) {
    const source = readFileSync(
        fileURLToPath(new URL(`../../view/frontend/web/js/${path}`, import.meta.url)),
        'utf8'
    );
    let exported;
    const define = (dependencies, factory) => {
        if (dependencies.length !== 0) {
            throw new Error(`${path} must not depend on other modules; it asks for ${dependencies.join(', ')}.`);
        }
        exported = factory();
    };
    vm.compileFunction(source, ['define'], { filename: path })(define);
    if (exported === undefined) {
        throw new Error(`${path} defined no module.`);
    }

    return exported;
}
