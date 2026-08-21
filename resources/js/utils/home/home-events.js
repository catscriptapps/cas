// /resources/js/utils/home/home-events.js

import { AnimationEngine } from "../animations.js";
import { FormValidator } from "../form-validator.js";

/**
 * Wire the "Get My Report" access-code form on the guest home page. There's
 * no report-lookup backend yet (Inspections is being rebuilt from scratch),
 * so this hands off to a plain full-page navigation -- not a [data-partial]
 * SPA fetch -- so a not-yet-live code cleanly shows the real 404 page
 * instead of failing silently in the console.
 */
function initAccessCodeForm() {
    const form = document.getElementById('access-code-form');
    if (!form || form._listenerAttached) return;
    form._listenerAttached = true;

    const validator = new FormValidator(form);

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!validator.validateForEmptyFields(e)) return;

        const input = document.getElementById('access-code-input');
        const code = input.value.trim();

        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        window.location.href = `${baseUrl}report/${encodeURIComponent(code)}`;
    });
}

export function initHomeEvents() {
    AnimationEngine.refresh();
    initAccessCodeForm();
}
