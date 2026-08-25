// /resources/js/utils/seasons/players-view.js

/**
 * Manages the transition between the Team List and Player Management
 * sub-views inside the View Teams modal.
 */
export const PlayerViewManager = {
    get container() { return document.getElementById('player-management-view'); },
    get teamList() { return document.getElementById('modal-list-view'); },
    get teamForm() { return document.getElementById('add-team-form'); },
    get modalFooter() { return document.querySelector('#view-teams-modal .border-t'); },

    show(teamId, teamName) {
        const container = this.container;
        const teamList = this.teamList;
        const teamForm = this.teamForm;
        const modalFooter = this.modalFooter;

        if (!container || !teamList) return;

        const nameEl = document.getElementById('player-view-team-name');
        const idInput = document.getElementById('player-form-team-id');

        if (nameEl) nameEl.textContent = teamName;
        if (idInput) idInput.value = teamId;

        teamList.classList.add('hidden');
        if (teamForm) teamForm.classList.add('hidden');
        if (modalFooter) modalFooter.classList.add('hidden');

        container.classList.remove('hidden');
        container.classList.add('animate-in', 'fade-in', 'slide-in-from-right-4', 'duration-300');
    },

    hide() {
        const container = this.container;
        const teamList = this.teamList;
        const modalFooter = this.modalFooter;

        if (container) container.classList.add('hidden');
        if (teamList) teamList.classList.remove('hidden');
        if (modalFooter) modalFooter.classList.remove('hidden');

        const listContainer = document.getElementById('players-list-container');
        const searchSelect = document.getElementById('registration-search-select');

        if (listContainer) listContainer.innerHTML = '';
        if (searchSelect) searchSelect.innerHTML = '<option value="">Loading...</option>';
    }
};
