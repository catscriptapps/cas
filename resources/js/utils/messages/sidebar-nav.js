// /resources/js/utils/messages/sidebar-nav.js

export function initSidebarNav(sidebar, mainContainer) { 
    sidebar.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        e.preventDefault();
        const url = link.href;

        // Update UI Active State
        sidebar.querySelectorAll('a').forEach(a => {
            a.classList.remove('bg-primary-50', 'text-primary-700', 'dark:bg-primary-900/20', 'dark:text-primary-400');
            a.classList.add('text-gray-600', 'dark:text-gray-400', 'hover:bg-gray-50', 'dark:hover:bg-gray-800');
        });
        link.classList.add('bg-primary-50', 'text-primary-700', 'dark:bg-primary-900/20', 'dark:text-primary-400');
        
        try {
            mainContainer.style.opacity = '0.5';
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            // Use a broader selector or a specific ID if you prefer
            const newContent = doc.querySelector('main').innerHTML;
            
            mainContainer.innerHTML = newContent;
            window.history.pushState({}, '', url);
        } catch (error) {
            console.error('Failed to load folder:', error);
        } finally {
            mainContainer.style.opacity = '1';
        }
    });
}