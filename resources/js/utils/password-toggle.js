// /resources/js/utils/password-toggle.js

/**
 * Generic show/hide toggle for any number of password fields on a page --
 * unlike the login form's own toggle (resources/js/modals/login-modal.js),
 * which hardcodes a single #login-password/#toggle-password/#eye-show/
 * #eye-hide set of ids (fine for a form with exactly one password field),
 * this is id-agnostic: each toggle button just points at its own input via
 * data-toggle-password="<input id>", and its two eye icons live scoped
 * inside the button itself (.eye-show/.eye-hide) instead of needing their
 * own page-unique ids. That's what makes it safe to use on a form with more
 * than one password field, like Register's Password + Confirm Password.
 */
export function initPasswordToggles() {
    if (window._passwordToggleAttached) return;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-toggle-password]');
        if (!btn) return;

        e.preventDefault();

        const input = document.getElementById(btn.dataset.togglePassword);
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');

        btn.querySelector('.eye-show')?.classList.toggle('hidden', isPassword);
        btn.querySelector('.eye-hide')?.classList.toggle('hidden', !isPassword);
    });

    window._passwordToggleAttached = true;
}
