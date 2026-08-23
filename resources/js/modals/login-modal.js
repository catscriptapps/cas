// /resources/js/modals/login-modal.js

import { Modal } from '../factories/modal-factory.js';
import { FormValidator } from '../utils/form-validator.js';
import { loginFormHTML } from '../forms/login-form.js';
import { buttonSpinner } from '../utils/spinner-utils.js';
import { initForgotPassword } from '../utils/login/forgot-password.js';

export class LoginModal {
    constructor(signInButtonSelector) {
        this.selector = signInButtonSelector;

        // Initialize the reusable modal
        this.modal = new Modal({
            id: 'login-modal',
            title: 'Sign In',
            content: loginFormHTML,
            size: 'sm',
            showFooter: false,
        });

        this.initEventListeners();
        initForgotPassword();
    }

    initEventListeners() {
        // 1. Listen for clicks to OPEN the modal (Delegated)
        document.body.addEventListener('click', (event) => {
            const button = event.target.closest(this.selector);
            if (!button) return;

            event.preventDefault();
            this.returnTo = button.dataset.returnTo || null;
            this.modal.open();

            // FOCUS: Focus the first field on initial open
            setTimeout(() => {
                document.getElementById('login-email')?.focus();
            }, 100);
        });

        // 💎 NEW: Password Visibility Toggle (Delegated)
        document.addEventListener('click', (e) => {
            const toggleBtn = e.target.closest('#toggle-password');
            if (!toggleBtn) return;

            const passInput = document.getElementById('login-password');
            const eyeShow = document.getElementById('eye-show');
            const eyeHide = document.getElementById('eye-hide');

            if (!passInput || !eyeShow || !eyeHide) return;

            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';

            // Toggle visibility of the icons
            eyeShow.classList.toggle('hidden', isPassword);
            eyeHide.classList.toggle('hidden', !isPassword);
        });

        // 3. Listen for the form SUBMIT (Delegated to document)
        // This solves the "Multiple Logs" issue because this listener is only added ONCE.
        document.addEventListener('submit', async (e) => {
            // Only act if the submitting form is our login form
            if (e.target.id !== 'login-form') return;

            e.preventDefault();
            e.stopImmediatePropagation();

            const form = e.target;

            // Check if already processing to prevent "Machine-gun" clicks
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn.disabled) return;

            const validator = new FormValidator(form);
            const apiMessageContainer = document.getElementById('login-api-message');

            if (!validator.validateForEmptyFields(e)) return;

            // UI Feedback
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = buttonSpinner;
            if (apiMessageContainer) apiMessageContainer.innerHTML = '';

            try {
                const loginUrl = `${window.APP_CONFIG.baseUrl}api/login`;
                const formData = new FormData(form);
                const body = Object.fromEntries(formData.entries());
                if (this.returnTo) body.return_to = this.returnTo;

                const response = await fetch(loginUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                });

                const result = await response.json();

                if (result.success) {
                    if (apiMessageContainer) {
                        apiMessageContainer.innerHTML = result.messages
                            .map(msg => `<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-md mt-2 flex items-center gap-2"><span>${msg}</span></div>`)
                            .join('');
                        submitBtn.style.display = 'none';
                    }

                    setTimeout(() => {
                        this.modal.close();

                        // Use the redirect_url from the server if it exists, otherwise default to /dashboard.
                        // This handles both user and landlord logins gracefully.
                        const redirectTarget = result.redirect_url || '/dashboard';
                        if (redirectTarget) {
                            window.location.href = `${window.APP_CONFIG.baseUrl}${redirectTarget.replace(/^\//, '')}`;
                        } else {
                            window.location.href = `${window.APP_CONFIG.baseUrl}dashboard`;
                        }
                    }, 1200);
                } else {
                    if (apiMessageContainer) {
                        apiMessageContainer.innerHTML = result.messages.map(msg => `<p class="text-red-500 text-sm mt-1">${msg}</p>`).join('');
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (err) {
                console.error('Login request failed:', err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }
}