<?php
// /resources/views/components/schedules/teams-table.php
?>

<div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white font-sans">Registered Teams</h2>
    <span class="bg-primary-50 text-primary-700 text-xs font-bold px-3 py-1 rounded-full border border-primary-100">
        <?= count($teams) ?> Teams Total
    </span>
</div>

<div class="overflow-x-auto">
    <table id="registered-teams-table" class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                    Team Details
                </th>
                <th class="hidden md:table-cell px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                    Group
                </th>
                <th class="hidden md:table-cell px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                    Team Rep
                </th>
                <th class="hidden md:table-cell px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800 text-center">
                    Roster
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <?php if (empty($teams)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                        No teams have been registered for this season yet.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($teams as $team): ?>
                    <tr class="team-row hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-150"
                        data-team-id="<?= $team['team_id'] ?>"
                        data-team-name="<?= htmlspecialchars((string)$team['team_name']) ?>">

                        <td class="px-6 py-4">
                            <div class="flex flex-col space-y-1.5 md:space-y-0">
                                <span class="font-bold text-gray-900 dark:text-white font-sans text-base md:text-sm">
                                    <?= htmlspecialchars((string)$team['team_name']) ?>
                                </span>

                                <div class="flex flex-wrap items-center gap-2 md:hidden">
                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[9px] font-bold uppercase text-gray-600 dark:text-gray-400">
                                        <?= htmlspecialchars((string)$team['group_name']) ?>
                                    </span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                        &bull; <?= htmlspecialchars((string)$team['rep_name']) ?>
                                    </span>
                                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                        (<?= $team['player_count'] ?>)
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-[10px] font-bold uppercase">
                                <?= htmlspecialchars((string)$team['group_name']) ?>
                            </span>
                        </td>
                        <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            <?= htmlspecialchars((string)$team['rep_name']) ?>
                        </td>
                        <td class="hidden md:table-cell px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 font-bold text-xs px-2.5 py-1 rounded-full border border-emerald-100 dark:border-emerald-800/50">
                                <?= $team['player_count'] ?> Players
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
