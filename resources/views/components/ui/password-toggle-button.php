<?php
// /resources/views/components/ui/password-toggle-button.php
//
// Show/hide eye-icon button for a password field, driven by
// resources/js/utils/password-toggle.js. The field it belongs to must sit
// inside a `relative` wrapper (this button is positioned absolute against
// that) and have right padding (pr-10) so typed text doesn't run under it.
//
// @var string $targetId The password input's id this button toggles.
?>
<button type="button" data-toggle-password="<?= htmlspecialchars($targetId) ?>" tabindex="-1"
    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors focus:outline-none"
    aria-label="Show password">
    <svg class="eye-show w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    </svg>
    <svg class="eye-hide w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.222 0 2.391.21 3.474.591M8.557 8.557a3.5 3.5 0 104.886 4.886M3 3l18 18" />
    </svg>
</button>
