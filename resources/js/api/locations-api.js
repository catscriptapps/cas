// /resources/js/api/locations-api.js

export async function fetchLocations() {
    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/locations`);
        const result = await response.json();

        if (Array.isArray(result)) return result;
        if (result.success && Array.isArray(result.data)) return result.data;

        return [];
    } catch (err) {
        console.error('Failed to fetch locations:', err);
        return [];
    }
}
