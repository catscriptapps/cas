// /resources/js/utils/globals/view-counter.js

/**
 * ViewCounter Utility
 * Handles view incrementing with session-based de-duplication.
 */
export const ViewCounter = {
    async increment(type, id) {
        if (!id || !type) return;

        // 1. Session Check: Don't count the same item twice in one session
        const sessionKey = `viewed_${type}_${id}`;
        if (sessionStorage.getItem(sessionKey)) return;

        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const displayElementId = `view-${type}-views-count`;
        const displayElement = document.getElementById(displayElementId);

        try {
            const response = await fetch(`${baseUrl}api/views-increment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, id })
            });

            const result = await response.json();

            if (result.success && displayElement) {
                // 2. Update the UI in real-time with the new count
                displayElement.textContent = result.newCount;
                // 3. Mark as viewed in this session
                sessionStorage.setItem(sessionKey, 'true');
            }
        } catch (error) {
            console.error(`[ViewCounter] Failed to increment ${type} view:`, error);
        }
    }
};