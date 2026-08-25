// /resources/js/utils/gamesheets/save-handler.js

/**
 * Admin-only inline-edit auto-save for a roster row's stat cells (see
 * components/gamesheets/roster-tables.php). Fires on `change` (blur/enter),
 * not `input`, since these are free-text fields (period/time_of_goal) with
 * no live-recalculated derived value worth debouncing on every keystroke --
 * unlike Stats' auto-save-handler.js.
 */
export async function handleAutoSave(e) {
    const input = e.target;
    if (!input.classList.contains('stat-input')) return;

    const row = input.closest('tr');
    const table = input.closest('table');

    const statusContainer = document.querySelector('#save-status');
    const statusText = statusContainer?.querySelector('span');
    const statusDot = statusContainer?.querySelector('div');

    if (statusText) statusText.innerText = 'Saving...';
    if (statusDot) {
        statusDot.classList.remove('bg-emerald-500', 'bg-red-500');
        statusDot.classList.add('bg-amber-500', 'animate-pulse');
    }

    const payload = {
        action: 'update_stat',
        game_id: table.getAttribute('data-game-id'),
        player_id: row.getAttribute('data-player-id'),
        field: input.getAttribute('data-field'),
        value: input.value,
    };

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/gamesheets`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (result.success) {
            if (statusText) statusText.innerText = 'System Live: All changes saved';
            if (statusDot) {
                statusDot.classList.remove('bg-amber-500', 'animate-pulse');
                statusDot.classList.add('bg-emerald-500');
            }

            input.classList.add('bg-emerald-50/50', 'dark:bg-emerald-900/10');
            setTimeout(() => {
                input.classList.remove('bg-emerald-50/50', 'dark:bg-emerald-900/10');
            }, 600);
        } else {
            throw new Error(result.messages?.[0] || 'Save failed');
        }
    } catch (err) {
        console.error('Gamesheet auto-save error:', err);
        if (statusText) statusText.innerText = 'Save Error - Check Connection';
        if (statusDot) {
            statusDot.classList.remove('bg-amber-500', 'animate-pulse');
            statusDot.classList.add('bg-red-500');
        }
        input.classList.add('bg-red-50', 'dark:bg-red-900/20');
    }
}
