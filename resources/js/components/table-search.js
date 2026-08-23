/**
 * Reusable AJAX search module - Works for Tables and Accordion Cards
 */
export function enableTableSearch(config) {
    const {
        searchInputId,
        tbodyId,         // This is the #search-container
        countId,
        endpoint,
        resourceLabel,
        addButtonId
    } = config;

    const input = document.getElementById(searchInputId);
    const container = document.getElementById(tbodyId);
    const countDiv = document.getElementById(countId);
    const addButton = addButtonId ? document.getElementById(addButtonId) : null;

    if (!input || !container || !countDiv) return;

    let lastQuery = "";
    let controller = null;
    let extraParams = config.extraParams || {};

    const irregularPlurals = { property: 'properties', company: 'companies' };
    const pluralize = (label) => irregularPlurals[label] || label + "s";

    function updateCount(current, total) {
        const label = resourceLabel.toLowerCase();
        const labelPlural = pluralize(label);

        // Safeguard total if meta is missing
        const finalTotal = typeof total !== 'undefined' ? total : current;

        if (current === finalTotal) {
            countDiv.textContent = `Showing ${finalTotal} ${finalTotal === 1 ? label : labelPlural}`;
        } else {
            countDiv.textContent = `Showing ${current} of ${finalTotal} ${finalTotal === 1 ? label : labelPlural}`;
        }
    }

    async function performSearch(query) {
        if (controller) controller.abort();
        controller = new AbortController();

        const params = new URLSearchParams({ q: query, ...extraParams });
        const url = endpoint + `?${params.toString()}`;

        try {
            const res = await fetch(url, { 
                signal: controller.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();

            if (!json.success) return;

            const rows = json.data || [];
            const meta = json.meta || { total: rows.length };

            if (rows.length === 0) {
                // 1. Define the sleek "No Results" content
                const emptyContent = `
                    <div class="flex flex-col items-center justify-center p-12 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 w-full">
                        <p class="text-gray-500 dark:text-gray-400 font-bold text-lg text-center">No ${pluralize(resourceLabel.toLowerCase())} found.</p>
                        <p class="text-gray-400 dark:text-gray-500 text-sm text-center">Try adjusting your search terms.</p>
                    </div>`;

                // 2. Check the container type
                if (container.tagName.toLowerCase() === 'tbody') {
                    // Find how many columns are in the header to span the whole table
                    const colCount = container.closest('table').querySelector('thead tr').children.length;
                    container.innerHTML = `
                        <tr>
                            <td colspan="${colCount}" class="p-4">
                                ${emptyContent}
                            </td>
                        </tr>`;
                } else {
                    // For non-table containers, just inject the content directly
                    container.innerHTML = emptyContent;
                }
            } else {
                // Join the rowHtml from our objects
                container.innerHTML = rows.map(row => row.cardHtml ?? row.rowHtml ?? row.html ?? "").join("");
            }

            updateCount(rows.length, meta.total);
        } catch(e) {
            if (e.name !== "AbortError") console.error('Search Fetch Error:', e);
        }
    }

    input.addEventListener("input", (e) => {
        const q = e.target.value.trim();
        lastQuery = q;
        performSearch(q);
    });

    if (addButton) {
        addButton.addEventListener("click", () => {
            if (lastQuery !== "") {
                input.value = "";
                lastQuery = "";
                performSearch("");
            }
        });
    }

    // Initial load count - works for <tr> or <div>
    updateCount(
        container.children.length,
        container.children.length
    );

    // Lets a caller (e.g. status filter cards) layer an extra filter on top
    // of whatever's currently in the search box, without duplicating the
    // fetch/render logic here.
    return {
        setExtraParams(newParams) {
            extraParams = newParams || {};
            performSearch(lastQuery);
        },
    };
}