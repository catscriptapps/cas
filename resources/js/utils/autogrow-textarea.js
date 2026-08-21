// /resources/js/utils/autogrow-textarea.js
// Makes any `textarea.js-autogrow` grow to fit its content instead of
// scrolling internally, sized correctly for whatever value it already
// holds on load (not just as the user types).

const SELECTOR = 'textarea.js-autogrow';

function resize(el) {
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight}px`;
}

export function autogrowTextarea(el) {
    if (!el || el._autogrowAttached) return;
    el._autogrowAttached = true;

    resize(el);
    el.addEventListener('input', () => resize(el));
}

/**
 * Wire up (and immediately size) every autogrow textarea within a root.
 */
export function initAutogrowTextareas(root = document) {
    root.querySelectorAll(SELECTOR).forEach(autogrowTextarea);
}

/**
 * Re-measure already-wired textareas within a root -- needed after they
 * become visible for the first time (e.g. inside a <details> panel that
 * was collapsed on load, where scrollHeight can't be measured correctly).
 */
export function resizeAutogrowTextareas(root = document) {
    root.querySelectorAll(SELECTOR).forEach(resize);
}
