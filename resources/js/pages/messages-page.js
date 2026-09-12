// /resources/js/pages/messages-page.js

import { initDataTable } from '../components/data-table.js';
import { initViewMessage } from '../utils/messages/view-message.js';
import { initToggleArchiveMessage } from '../utils/messages/toggle-archive-message.js';
import { initDeleteMessage } from '../utils/messages/delete-message.js';

export function init() {
    initViewMessage();
    initToggleArchiveMessage();
    initDeleteMessage();

    const dataTable = initDataTable({
        tbodyId: 'messages-tbody',
        countId: 'messages-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/messages`,
        resourceLabel: 'message',
        colspan: 5,
        defaultSort: 'date',
        defaultDir: 'desc',
    });

    // Inbox / Archived tabs -- a static `view` param layered on top of
    // whatever filter/sort state the table already has (see data-table.js's
    // setExtraParams()), same idea as the Stats page's Regular/Playoffs
    // tabs but server-side since each view is its own filtered+paginated
    // query rather than two panes both already in the DOM.
    const tabs = document.querySelectorAll('.messages-view-tab-btn');
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            if (tab.classList.contains('bg-primary-500')) return;

            tabs.forEach((t) => {
                const active = t === tab;
                t.classList.toggle('bg-primary-500', active);
                t.classList.toggle('text-white', active);
                t.classList.toggle('shadow-sm', active);
                t.classList.toggle('bg-gray-100', !active);
                t.classList.toggle('dark:bg-gray-800', !active);
                t.classList.toggle('text-gray-500', !active);
                t.classList.toggle('dark:text-gray-400', !active);
            });

            dataTable?.setExtraParams({ view: tab.dataset.view });
        });
    });

    // A message archived/restored from within the view modal (see
    // view-message.js -> performArchiveToggle) removes it from whichever
    // view is currently showing -- if that emptied the table, the count
    // still needs a refresh pass so the footer reflects the server's real
    // total instead of just whatever was rendered client-side.
    document.addEventListener('messages-row-removed', () => {
        const tbody = document.getElementById('messages-tbody');
        if (tbody && tbody.querySelectorAll('tr[data-encoded-id]').length === 0) {
            dataTable?.refresh();
        }
    });
}
