// /resources/js/pages/contact-page.js

/**
 * Contact page form logic (validation + submit)
 * Exported `init()` function is called by app.js after partial load.
 */

import { FormValidator } from '../utils/form-validator.js'; 
import { showToast } from '../ui/toast.js'; 

/**
 * Initializes the contact form logic.
 */
export function init() {
  const form = document.getElementById('contact-form');
  const submitButton = document.getElementById('contact-submit');

  // 1. Exit early if form/button is missing OR if already initialized
  // This prevents the "Double Submit" bug caused by event listener stacking.
  if (!form || !submitButton || form.dataset.initialized) return;

  // 2. Mark the form as initialized
  form.dataset.initialized = "true";

  const validator = new FormValidator(form);

  // --- Step 3: Handle form submission ---
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!validator.validateForEmptyFields(e)) return;

    // UI Feedback: Disable button
    submitButton.disabled = true;
    submitButton.classList.add('opacity-70', 'cursor-not-allowed');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Processing...';

    // Remove old feedback
    form.querySelectorAll('.contact-feedback').forEach((el) => el.remove());

    try {
      const body = JSON.stringify(Object.fromEntries(new FormData(form).entries()));

      const response = await fetch(`${window.APP_CONFIG.baseUrl}api/contact`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body,
      });

      if (!response.ok) throw new Error(`Server status ${response.status}`);

      const result = await response.json();

      if (result.success) {
        showToast('✅ Message sent successfully!', 'success');
        form.reset();
      } else {
        showToast(`❌ ${result.message || 'Error sending message.'}`, 'error');
      }

    } catch (err) {
      console.error('Contact form error:', err);
      showToast('❌ Error sending message.', 'error');
    } finally {
      // Re-enable UI
      submitButton.disabled = false;
      submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
      submitButton.textContent = originalText;
    }
  });

  // --- Step 4: Handle Enter key ---
  form.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
      e.preventDefault();
      validator.validateForEmptyFields(e);
    }
  });
}