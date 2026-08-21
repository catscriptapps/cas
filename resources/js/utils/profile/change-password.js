// /resources/js/utils/profile/change-password.js

import { FormValidator } from '../form-validator.js';
import { buttonSpinner } from '../spinner-utils.js';
import { showToast } from '../../ui/toast.js';

/**
 * Initializes the self-service "Update Password" form on the Profile page.
 */
export function initChangePassword() {
    const form = document.getElementById('change-password-form');
    if (!form || form._changePasswordListenerAttached) return;
    form._changePasswordListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('#btn-change-password');
    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validator.validateForEmptyFields(e)) {
            return;
        }

        const newPassword = form.querySelector('#new_password').value;
        const confirmPassword = form.querySelector('#confirm_password').value;

        if (newPassword.length < 8) {
            showToast('New password must be at least 8 characters long.', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            showToast('New passwords do not match.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;

        try {
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            const response = await fetch(`${baseUrl}api/change-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.messages?.[0] || 'Password updated successfully!', 'success');
                form.reset();
            } else {
                showToast(result.messages?.[0] || 'Unable to update password.', 'error');
            }
        } catch (err) {
            console.error('Change Password Error:', err);
            showToast('Server connection error', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;
        }
    });
}
