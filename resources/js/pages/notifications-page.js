// /resources/js/pages/notifications-page.js

/**
 * Fire-and-forget: mark a notification read without blocking navigation
 * (the row is a data-partial link, so spa-router.js handles the actual nav).
 */
function markRead(id) {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    fetch(`${baseUrl}api/notifications`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ _method: 'MARK_READ', id }),
    }).catch((err) => console.error('Mark notification read error:', err));
}

function initNotificationRows() {
    const list = document.getElementById('notifications-list');
    if (!list || list._readListenerAttached) return;
    list._readListenerAttached = true;

    list.addEventListener('click', (e) => {
        const row = e.target.closest('.notification-row');
        if (!row || row.dataset.isRead === '1') return;
        markRead(row.dataset.id);
    });
}

function initMarkAllRead() {
    const btn = document.getElementById('mark-all-read-btn');
    if (!btn || btn._markAllListenerAttached) return;
    btn._markAllListenerAttached = true;

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        try {
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            await fetch(`${baseUrl}api/notifications`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ _method: 'MARK_ALL_READ' }),
            });

            document.querySelectorAll('.notification-row').forEach((row) => {
                row.dataset.isRead = '1';
                row.classList.add('opacity-70');
                row.querySelector('.bg-primary-500')?.remove();
            });
            btn.remove();
        } catch (err) {
            console.error('Mark all read error:', err);
            btn.disabled = false;
        }
    });
}

export function init() {
    initNotificationRows();
    initMarkAllRead();
}
