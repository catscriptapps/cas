// /resources/js/utils/stats/auto-save-handler.js

/**
 * Admin-only inline-edit auto-save for both the standings table (team rows,
 * .stats-team-row) and the player-stats roster (.stats-player-row, goalie
 * rows additionally flagged data-is-goalie="1"). Every `.stats-input` in a
 * row is recalculated client-side immediately on input (PTS/Diff/GAA), then
 * the whole row is POSTed to api/stats-update after a short per-row debounce
 * so rapid keystrokes don't fire a request per character.
 */

const DEBOUNCE_MS = 800;
const debounceTimers = {};

export function initStatsAutoSave() {
    document.addEventListener('input', handleStatsInput);
}

function handleStatsInput(e) {
    const input = e.target;
    if (!input.classList.contains('stats-input')) return;

    const row = input.closest('tr');
    if (!row) return;

    recalculateRow(row);
    scheduleSave(row);
}

function recalculateRow(row) {
    if (row.classList.contains('stats-team-row')) {
        const wins = readInt(row, 'wins');
        const ties = readInt(row, 'ties');
        const gf = readInt(row, 'goals_for');
        const ga = readInt(row, 'goals_against');
        const pts = wins * 2 + ties;
        const diff = gf - ga;

        const ptsCell = row.querySelector('.pts-cell');
        if (ptsCell) ptsCell.textContent = String(pts);

        const diffCell = row.querySelector('.diff-cell');
        if (diffCell) {
            diffCell.textContent = diff > 0 ? `+${diff}` : String(diff);
            diffCell.classList.toggle('text-emerald-500', diff > 0);
            diffCell.classList.toggle('text-rose-500', diff < 0);
            diffCell.classList.toggle('text-gray-400', diff === 0);
        }
    } else if (row.dataset.isGoalie === '1') {
        const gp = readInt(row, 'games_played');
        const ga = readInt(row, 'goals_against');
        const gaaCell = row.querySelector('.gaa-cell');
        if (gaaCell) gaaCell.textContent = gp > 0 ? (ga / gp).toFixed(2) : '';
    } else if (row.classList.contains('stats-player-row')) {
        const goals = readInt(row, 'goals');
        const assists = readInt(row, 'assists');
        const ptsCell = row.querySelector('.player-pts-cell');
        if (ptsCell) ptsCell.textContent = String(goals + assists);
    }
}

function readInt(row, field) {
    const input = row.querySelector(`[data-field="${field}"]`);
    return input ? parseInt(input.value, 10) || 0 : 0;
}

function scheduleSave(row) {
    const key = row.classList.contains('stats-team-row')
        ? `team-${row.dataset.teamId}`
        : `player-${row.dataset.playerId}`;

    clearTimeout(debounceTimers[key]);
    debounceTimers[key] = setTimeout(() => saveStats(row), DEBOUNCE_MS);
}

async function saveStats(row) {
    row.classList.remove('row-saved-success', 'row-save-error');
    row.classList.add('row-saving');

    const isTeam = row.classList.contains('stats-team-row');
    const stats = {};
    row.querySelectorAll('.stats-input').forEach((input) => {
        stats[input.dataset.field] = input.value;
    });

    const payload = {
        type: isTeam ? 'team' : 'player',
        id: isTeam ? row.dataset.teamId : row.dataset.playerId,
        season_id: row.dataset.seasonId,
        is_playoff: row.dataset.isPlayoff || 0,
        stats,
    };

    try {
        const base = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${base}api/stats-update`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();

        row.classList.remove('row-saving');
        if (result.success) {
            row.classList.add('row-saved-success');
            setTimeout(() => row.classList.remove('row-saved-success'), 1500);
        } else {
            row.classList.add('row-save-error');
        }
    } catch (err) {
        console.error('Stats save error:', err);
        row.classList.remove('row-saving');
        row.classList.add('row-save-error');
    }
}
