// /resources/js/utils/companies/infinite-scroll-companies.js

export function initCompanyInfiniteScroll() {
    const tableBody = document.getElementById('companies-tbody');
    if (!tableBody) return;

    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let throttleTimeout = null;

    const loadMoreCompanies = async () => {
        if (isLoading || !hasMore) return;

        isLoading = true;
        currentPage++;

        const searchInput = document.getElementById('companies-search');
        const query = searchInput ? searchInput.value : '';

        try {
            const response = await fetch(`${window.APP_CONFIG?.baseUrl}api/companies?page=${currentPage}&q=${encodeURIComponent(query)}`);
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                const rowsHtml = result.data.map(item => item.rowHtml).join('');

                tableBody.insertAdjacentHTML('beforeend', rowsHtml);

                const countEl = document.getElementById('companies-count');
                if (countEl && result.meta) {
                    const currentCount = tableBody.querySelectorAll('tr').length;
                    countEl.textContent = `Showing ${currentCount} of ${result.meta.total} companies`;
                }

                hasMore = result.meta.hasMore;
            } else {
                hasMore = false;
            }
        } catch (error) {
            console.error("Infinite scroll error:", error);
            currentPage--;
        } finally {
            isLoading = false;
        }
    };

    const handleScroll = () => {
        if (throttleTimeout) return;

        throttleTimeout = setTimeout(() => {
            throttleTimeout = null;

            const scrollBottom = window.innerHeight + window.scrollY;
            const threshold = document.documentElement.scrollHeight - 400;

            if (scrollBottom >= threshold) {
                loadMoreCompanies();
            }
        }, 200);
    };

    window.addEventListener('scroll', handleScroll);
}
