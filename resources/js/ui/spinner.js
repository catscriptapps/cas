// /resources/js/ui/spinner.js

/**
 * Manages the global loading spinner UI element.
 */

const spinner = document.createElement('div');

export function setupSpinner() {
    // Create and append the spinner element only once
    spinner.className = 'absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-900 bg-opacity-80 z-50 hidden';
    spinner.innerHTML = `<div class="w-12 h-12 border-4 border-orange-500 border-dashed rounded-full animate-spin"></div>`;
    document.body.appendChild(spinner);
}

export function showSpinner() {
    spinner.classList.remove('hidden');
}

export function hideSpinner() {
    spinner.classList.add('hidden');
}