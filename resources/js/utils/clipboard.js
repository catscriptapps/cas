// /resources/js/utils/clipboard.js

/**
 * Copies text to the clipboard, falling back to the legacy execCommand
 * approach when the async Clipboard API isn't available -- e.g. when the
 * app is served over plain HTTP on a LAN address rather than HTTPS/localhost,
 * where navigator.clipboard is undefined entirely. Calling .writeText() on
 * it directly throws synchronously in that case, which is why copy buttons
 * built that way silently do nothing (the error never reaches a .catch()).
 * @returns {Promise<boolean>} whether the copy actually succeeded
 */
export async function copyToClipboard(text) {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            console.warn('Clipboard API copy failed, falling back:', err);
        }
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    let succeeded = false;
    try {
        succeeded = document.execCommand('copy');
    } catch (err) {
        console.warn('execCommand copy fallback failed:', err);
    }

    document.body.removeChild(textarea);
    return succeeded;
}
