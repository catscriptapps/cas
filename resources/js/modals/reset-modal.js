/**
 * ResetModal Class
 * ----------------
 * Handles modal display, form validation, and AJAX admin reset request.
 * Features:
 * - Custom form validation with shake animation for empty input
 * - Single-line input group (password + submit button)
 * - Spinner and "Processing..." feedback while request is pending
 * - Hides input group and displays green success alert on successful reset
 * - Displays red alert with shake animation on failure
 */

import { Modal } from '../factories/modal-factory.js';
import { FormValidator } from '../utils/form-validator.js';
import { resetFormHTML } from '../forms/reset-form.js';
import { buttonSpinner } from '../utils/spinner-utils.js';

export class ResetModal {
  constructor(resetButtonSelector = '[data-reset-button]') {
    this.resetButton = document.querySelector(resetButtonSelector);
    if (!this.resetButton) return;

    // Initialize modal
    this.modal = new Modal({
      id: 'reset-modal',
      title: 'Database Reset',
      content: resetFormHTML,
      size: 'md',
      showFooter: false,
    });

    this.attachResetListener();
  }

  attachResetListener() {
    this.resetButton.addEventListener('click', (e) => {
      e.preventDefault();
      this.modal.open();

      const form = document.getElementById('reset-form');
      if (!form) return;

      // Initialize custom validator for shake animation & live error clearing
      const validator = new FormValidator(form);

      const apiMessageContainer = document.getElementById('reset-api-message');
      const inputGroup = document.getElementById('reset-input-group');

      // Form submission handler
      form.addEventListener('submit', async (ev) => {
        ev.preventDefault();

        // Validate required field
        if (!validator.validateForEmptyFields(ev)) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        // Disable button + show spinner
        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;

        // Clear previous API messages
        apiMessageContainer.innerHTML = '';
        apiMessageContainer.classList.add('hidden');

        try {
          const body = JSON.stringify(Object.fromEntries(new FormData(form).entries()));

          const response = await fetch(`${window.APP_CONFIG.baseUrl}api/reset`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
          });

          const result = await response.json();

          if (result.success) {
            // ✅ Success: hide input group and display green alert
            if (inputGroup) inputGroup.classList.add('hidden');

            apiMessageContainer.innerHTML = `
              <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mt-3 animate-fade-in">
                <p class="font-semibold mb-1">Database Reset Successful</p>
                <ul class="list-disc pl-5 text-sm space-y-1">
                  ${result.messages.map(msg => `<li>${msg}</li>`).join('')}
                </ul>
              </div>
            `;
            apiMessageContainer.classList.remove('hidden');

          } else {
            // ❌ Failure: show red alert
            apiMessageContainer.innerHTML = `
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mt-3 animate-shake">
                <p class="font-semibold mb-1">Reset Failed</p>
                <ul class="list-disc pl-5 text-sm space-y-1">
                  ${result.messages.map(msg => `<li>${msg}</li>`).join('')}
                </ul>
              </div>
            `;
            apiMessageContainer.classList.remove('hidden');
          }
        } catch (err) {
          console.error(err);
          apiMessageContainer.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mt-3 animate-shake">
              <p class="font-semibold">Unexpected error occurred.</p>
            </div>
          `;
          apiMessageContainer.classList.remove('hidden');
        } finally {
          // Restore button
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      });
    });
  }
}
