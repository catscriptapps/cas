// /resources/js/api/divisions-api.js

export async function fetchSports() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    try {
        const res = await fetch(`${baseUrl}api/sports`);
        const json = await res.json();
        return json.success ? json.data.map(s => ({ id: s.sport_id, name: s.sport_name })) : [];
    } catch (error) {
        console.error('Fetch Sports Error:', error);
        return [];
    }
}

export async function fetchLeagues(sportId = null) {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const url = sportId ? `${baseUrl}api/leagues?sport_id=${sportId}` : `${baseUrl}api/leagues`;
    try {
        const res = await fetch(url);
        const json = await res.json();
        return json.success ? json.data.map(l => ({ id: l.league_id, name: l.league, sportId: l.sport_id })) : [];
    } catch (error) {
        console.error('Fetch Leagues Error:', error);
        return [];
    }
}

/**
 * Admin-only variants: every sport/league regardless of active status, for
 * the League Management parent-selection dropdowns (an inactive parent
 * still needs to show up so existing children can be edited correctly).
 */
export async function fetchAllSports() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    try {
        const res = await fetch(`${baseUrl}api/sports?status=all`);
        const json = await res.json();
        return json.success ? json.data.map(s => ({ id: s.sport_id, name: s.sport_name, isActive: s.status_id === 1 })) : [];
    } catch (error) {
        console.error('Fetch All Sports Error:', error);
        return [];
    }
}

export async function fetchAllLeagues(sportId = null) {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const url = sportId ? `${baseUrl}api/leagues?status=all&sport_id=${sportId}` : `${baseUrl}api/leagues?status=all`;
    try {
        const res = await fetch(url);
        const json = await res.json();
        return json.success ? json.data.map(l => ({ id: l.league_id, name: l.league, sportId: l.sport_id, isActive: l.status_id === 1 })) : [];
    } catch (error) {
        console.error('Fetch All Leagues Error:', error);
        return [];
    }
}

export async function fetchDivisions(leagueId = null) {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const url = leagueId ? `${baseUrl}api/divisions?league_id=${leagueId}` : `${baseUrl}api/divisions`;
    try {
        const res = await fetch(url);
        const json = await res.json();
        return json.success ? json.data.map(d => ({ id: d.division_id, name: d.division, price: parseFloat(d.price), leagueId: d.league_id })) : [];
    } catch (error) {
        console.error('Fetch Divisions Error:', error);
        return [];
    }
}

/**
 * Every division regardless of status, with sport_name/league_name embedded
 * -- used by the Schedules "Add Season" form's grouped division dropdown.
 */
export async function fetchAllDivisionsGrouped() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    try {
        const res = await fetch(`${baseUrl}api/divisions?status=all`);
        const json = await res.json();
        return json.success ? json.data : [];
    } catch (error) {
        console.error('Fetch All Divisions Error:', error);
        return [];
    }
}

export async function fetchSources() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    try {
        const res = await fetch(`${baseUrl}api/sources`);
        const json = await res.json();
        return json.success ? json.data.map(s => ({ id: s.entry_id, name: s.hear_about_us })) : [];
    } catch (error) {
        console.error('Fetch Sources Error:', error);
        return [];
    }
}
