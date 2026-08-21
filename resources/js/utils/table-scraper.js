// /resources/js/utils/table-scraper.js

/**
 * Scrapes a table body to generate a list of items for dropdowns.
 * Assumes rows have data-encoded-id and a name/title within a specific cell.
 * * @param {string} tbodySelector - The ID/Class of the tbody to scrape
 * @param {string} nameSelector - The class of the element containing the text name
 * @returns {Array} [{ id: '...', name: '...' }]
 */
export function scrapeTableData(tbodySelector, nameSelector) {
    const tbody = document.querySelector(tbodySelector);
    if (!tbody) return [];

    const rows = Array.from(tbody.querySelectorAll('tr[data-encoded-id]'));
    
    return rows.map(row => {
        const nameEl = row.querySelector(nameSelector);
        // Check if the row contains "Inactive" - usually in a badge
        const isInactive = row.innerText.includes('Inactive');
        
        return {
            id: row.dataset.encodedId,
            numericId: row.id.split('-').pop(), 
            name: nameEl ? nameEl.textContent.trim() : 'Unknown',
            active: !isInactive
        };
    });
}