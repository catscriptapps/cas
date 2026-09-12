<?php
// /resources/views/components/messages/data-row.php

/** @var array $rowItem */

$isUnread = empty($rowItem['is_read']);
$isArchived = (int)($rowItem['status_id'] ?? 1) === \App\Models\ContactMessage::STATUS_ARCHIVED;

$snippet = trim((string)($rowItem['message'] ?? ''));
$snippet = mb_strlen($snippet) > 90 ? mb_substr($snippet, 0, 90) . '…' : $snippet;

$messageDataAttrs = [
    'encoded-id' => $rowItem['encoded_id'],
    'full-name' => $rowItem['full_name'] ?? '',
    'email' => $rowItem['email'] ?? '',
    'subject' => $rowItem['subject'] ?? '',
    'message' => $rowItem['message'] ?? '',
    'date-formatted' => $rowItem['date_formatted'] ?? '',
    'is-read' => $isUnread ? '0' : '1',
    'is-archived' => $isArchived ? '1' : '0',
];

$dataAttrString = '';
foreach ($messageDataAttrs as $key => $val) {
    $dataAttrString .= ' data-' . $key . '="' . htmlspecialchars((string)$val) . '"';
}

$nameClasses = $isUnread ? 'font-black text-gray-900 dark:text-white' : 'font-medium text-gray-600 dark:text-gray-400';
$subjectClasses = $isUnread ? 'font-bold text-gray-900 dark:text-white' : 'font-medium text-gray-500 dark:text-gray-400';
?>
<tr id="message-row-<?= $rowItem['entry_id'] ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?>"
    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-b border-gray-100 dark:border-gray-800 font-sans <?= $isUnread ? 'bg-primary-50/30 dark:bg-primary-900/[0.06]' : '' ?>">

    <td class="px-6 py-4 min-w-0">
        <div class="flex items-start gap-3 min-w-0">
            <?php if ($isUnread): ?>
                <span class="mt-1.5 h-2 w-2 rounded-full bg-primary-500 shrink-0" title="Unread"></span>
            <?php else: ?>
                <span class="mt-1.5 h-2 w-2 rounded-full shrink-0"></span>
            <?php endif; ?>

            <div class="view-message-trigger cursor-pointer min-w-0 flex-1" <?= $dataAttrString ?>>
                <div class="text-sm truncate <?= $nameClasses ?>"><?= htmlspecialchars($rowItem['full_name'] ?: 'Unknown') ?></div>
                <div class="text-xs text-gray-400 dark:text-gray-500 truncate"><?= htmlspecialchars($rowItem['email'] ?: '') ?></div>

                <div class="lg:hidden mt-1.5">
                    <div class="text-sm truncate <?= $subjectClasses ?>"><?= htmlspecialchars($rowItem['subject'] ?: '(No subject)') ?></div>
                    <div class="text-xs text-gray-400 truncate"><?= htmlspecialchars($snippet) ?></div>
                    <div class="text-[10px] text-gray-400 mt-1"><?= htmlspecialchars($rowItem['date_formatted'] ?? '') ?></div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-3 lg:hidden">
            <button type="button" class="toggle-archive-message-btn text-xs font-bold text-secondary-600 flex items-center gap-1.5 p-1" data-encoded-id="<?= $rowItem['encoded_id'] ?>">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <?= $isArchived ? 'Restore' : 'Archive' ?>
            </button>
            <button type="button" class="delete-message-btn text-xs font-bold text-red-500 flex items-center gap-1.5 p-1" data-encoded-id="<?= $rowItem['encoded_id'] ?>">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
            </button>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell min-w-0">
        <div class="view-message-trigger cursor-pointer min-w-0" <?= $dataAttrString ?>>
            <div class="text-sm truncate <?= $subjectClasses ?>"><?= htmlspecialchars($rowItem['subject'] ?: '(No subject)') ?></div>
            <div class="text-xs text-gray-400 dark:text-gray-500 truncate"><?= htmlspecialchars($snippet) ?></div>
        </div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell text-sm text-gray-500 dark:text-gray-400">
        <?= htmlspecialchars($rowItem['date_formatted'] ?? '') ?>
    </td>

    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold border
            <?= $isUnread
                ? 'bg-primary-50 text-primary-700 border-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/30'
                : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' ?>">
            <?= $isUnread ? 'Unread' : 'Read' ?>
        </span>
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-right hidden lg:table-cell">
        <div class="flex justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" title="View message" class="view-message-trigger text-primary-600 hover:text-primary-900 p-1" <?= $dataAttrString ?>>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
            <button type="button" title="<?= $isArchived ? 'Restore to inbox' : 'Archive' ?>" class="toggle-archive-message-btn text-gray-400 hover:text-secondary-600 p-1" data-encoded-id="<?= $rowItem['encoded_id'] ?>">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </button>
            <button type="button" title="Delete message" class="delete-message-btn text-gray-400 hover:text-red-600 p-1" data-encoded-id="<?= $rowItem['encoded_id'] ?>">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </td>
</tr>
