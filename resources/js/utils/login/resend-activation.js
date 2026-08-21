// /resources/js/utils/login/resend-activation.js

/**
 * Utility to handle resending activation links
 */
export async function resendActivationLink(email, messageContainer) {
    if (!email) return;

    try {
        const response = await fetch(`${window.APP_CONFIG.baseUrl}api/resend-verification`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });

        const result = await response.json();

        if (messageContainer) {
            messageContainer.innerHTML = result.messages
                .map(msg => `<p class="${result.success ? 'text-green-500' : 'text-red-500'} text-sm mt-2 font-bold">${msg}</p>`)
                .join('');
        }
    } catch (err) {
        console.error('Resend failed:', err);
    }
}