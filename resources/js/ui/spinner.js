// /resources/js/ui/spinner.js

/**
 * Manages the global loading spinner UI element.
 */

const spinner = document.createElement('div');

export function setupSpinner() {
    // Create and append the spinner element only once.
    //
    // `fixed` (not `absolute`) is required here: `absolute` positions the
    // box relative to the nearest *positioned* ancestor, and with none
    // (it's a direct child of <body>) that falls back to the initial
    // containing block -- sized to one viewport's worth of pixels, anchored
    // to the top of the document rather than the browser window. On any
    // page taller than one viewport (i.e. almost every page here), that left
    // the overlay covering only the top slice of the document, so scrolled
    // content -- or the whole page on a short viewport -- sat uncovered.
    // `fixed inset-0` always spans the full viewport regardless of scroll
    // position or document height, matching every other full-page overlay
    // in this app (see modal-factory.js, delete-factory.js). z-[10000]
    // outranks the topbar (z-[9999]) so it's covered too.
    spinner.className = 'fixed inset-0 flex items-center justify-center bg-white dark:bg-gray-900 bg-opacity-80 z-[10000] hidden';
    spinner.innerHTML = `<div class="w-12 h-12 border-4 border-orange-500 border-dashed rounded-full animate-spin"></div>`;
    document.body.appendChild(spinner);
}

export function showSpinner() {
    spinner.classList.remove('hidden');
}

export function hideSpinner() {
    spinner.classList.add('hidden');
}