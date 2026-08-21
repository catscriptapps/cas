// /resources/js/components/icon-picker.js

/**
 * Wires up a grid of icon buttons (rendered by sectionForm()) to a hidden
 * input, toggling the selected style as the admin clicks between them.
 */
export function enableIconPicker(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const picker = form.querySelector('[id$="-icon-picker"]');
    const hiddenInput = form.querySelector('input[name="icon"]');
    if (!picker || !hiddenInput) return;

    picker.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-icon-option]');
        if (!btn) return;

        hiddenInput.value = btn.dataset.iconOption;

        picker.querySelectorAll('[data-icon-option]').forEach((el) => {
            el.classList.remove('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20', 'text-primary-600', 'dark:text-primary-400');
            el.classList.add('border-gray-200', 'dark:border-gray-700', 'text-gray-500', 'dark:text-gray-400');
        });

        btn.classList.add('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20', 'text-primary-600', 'dark:text-primary-400');
        btn.classList.remove('border-gray-200', 'dark:border-gray-700', 'text-gray-500', 'dark:text-gray-400');
    });
}
