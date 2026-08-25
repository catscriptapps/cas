<?php
// /resources/views/components/seasons/team-form.php

$reps = $GLOBALS['teamReps'] ?? [];
$groups = $GLOBALS['teamGroups'] ?? [];
?>

<form id="add-team-form" class="hidden space-y-4" novalidate>
    <input type="hidden" name="season_id" id="form-season-id">

    <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Team Name *</label>
        <input type="text" name="team_name" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
    </div>

    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-2">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Team #</label>
            <input type="text" name="team_number" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
        </div>

        <div class="col-span-5">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Group</label>
            <select name="team_group" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                <option value="N/A">N/A</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= htmlspecialchars($group->group_name) ?>">
                        <?= htmlspecialchars($group->group_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-span-5">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Team Representative</label>
            <select name="team_rep_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                <option value="">Select a Rep...</option>
                <?php foreach ($reps as $rep): ?>
                    <option value="<?= $rep->entry_id ?>"><?= htmlspecialchars($rep->full_name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-4 pt-4">
        <button type="button" id="hide-add-team-form" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
            Cancel
        </button>
        <button type="submit" class="px-8 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-primary-500/20">
            Save Team
        </button>
    </div>
</form>
