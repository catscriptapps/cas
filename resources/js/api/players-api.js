// /resources/js/api/players-api.js

const baseUrl = window.APP_CONFIG?.baseUrl || '/';

export async function fetchTeamRoster(teamId) {
    try {
        const response = await fetch(`${baseUrl}api/players?action=roster&team_id=${teamId}`);
        const result = await response.json();
        return result.success ? result.players : [];
    } catch (err) {
        console.error('Failed to fetch team roster:', err);
        return [];
    }
}

export async function fetchAvailableRegistrants(teamId) {
    try {
        const response = await fetch(`${baseUrl}api/players?action=available&team_id=${teamId}`);
        const result = await response.json();
        return result.success ? result.registrants : [];
    } catch (err) {
        console.error('Failed to fetch available registrants:', err);
        return [];
    }
}

export async function addPlayerToTeam(payload) {
    const response = await fetch(`${baseUrl}api/players?action=add`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    });
    return await response.json();
}

export async function removePlayerFromTeam(playerId) {
    try {
        const response = await fetch(`${baseUrl}api/players?action=delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ player_id: playerId }),
        });
        return await response.json();
    } catch (err) {
        return { success: false, messages: [err.message] };
    }
}

export async function updatePlayerGoalieStatus(playerId, isGoalie) {
    try {
        const response = await fetch(`${baseUrl}api/players?action=toggle-goalie`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ player_id: playerId, is_goalie: isGoalie }),
        });
        return await response.json();
    } catch (err) {
        return { success: false, messages: [err.message] };
    }
}
