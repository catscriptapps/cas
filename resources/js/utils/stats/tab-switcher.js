// /resources/js/utils/stats/tab-switcher.js

/**
 * Pure class-toggling between the Regular Season / Playoffs tabs on the
 * Stats detail page -- no fetch, both panes are already rendered server-side.
 */
export function initTabSwitcher() {
    const tabs = document.querySelectorAll('.stats-tab-btn');
    if (!tabs.length) return;

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((t) => setTabState(t, t === tab));

            const targetId = tab.id.replace('tab-', 'pane-');
            document.querySelectorAll('.stats-pane').forEach((pane) => {
                pane.classList.toggle('hidden', pane.id !== targetId);
            });
        });
    });
}

function setTabState(tab, active) {
    tab.classList.toggle('bg-primary-500', active);
    tab.classList.toggle('text-white', active);
    tab.classList.toggle('shadow-sm', active);
    tab.classList.toggle('bg-transparent', !active);
    tab.classList.toggle('text-gray-500', !active);
    tab.classList.toggle('dark:text-gray-400', !active);
}
