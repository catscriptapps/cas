// /resources/js/api/teams-api.js

export async function fetchTeams(seasonId = null) {
    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        if (!seasonId) return [];

        const response = await fetch(`${baseUrl}api/teams?season_id=${seasonId}&status=1`);
        const result = await response.json();

        if (result.success && Array.isArray(result.data)) {
            return result.data;
        }

        return Array.isArray(result) ? result : [];
    } catch (err) {
        console.error('Failed to fetch teams:', err);
        return [];
    }
}
