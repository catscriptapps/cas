// /resources/js/pages/verify-account-page.js

import { AnimationEngine } from '../utils/animations';

/**
 * Initialize the Verify Account page: reads the token/email from the URL,
 * confirms them with the server, and reveals the success/error state.
 */
export function init() {
    AnimationEngine.refresh();
    verifyAccount();
}

async function verifyAccount() {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');
    const email = params.get('email');
    const redirect = params.get('redirect');

    const loader = document.getElementById('verifying-loader');
    const successEl = document.getElementById('verification-success');
    const errorEl = document.getElementById('verification-error');
    const errorMsgEl = document.getElementById('error-message');
    const continueBtn = document.getElementById('verification-continue-btn');

    const showError = (message) => {
        loader?.classList.add('hidden');
        errorEl?.classList.remove('hidden');
        if (errorMsgEl) errorMsgEl.textContent = message;
    };

    if (!token || !email) {
        showError('Missing verification parameters. Please check the link and try again.');
        return;
    }

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const res = await fetch(`${baseUrl}api/verify-account`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, token })
        });

        const result = await res.json();

        if (result.success) {
            loader?.classList.add('hidden');
            successEl?.classList.remove('hidden');

            if (continueBtn) {
                // URLSearchParams already decodes the value, so it's a clean path like "/apply/ACC-26-..."
                continueBtn.href = redirect ? `${baseUrl}${redirect.replace(/^\//, '')}` : `${baseUrl}dashboard`;
            }
        } else {
            showError(result.messages?.[0] || 'Verification failed.');
        }
    } catch (err) {
        console.error('Account verification error:', err);
        showError('A network error occurred. Please try again.');
    }
}
