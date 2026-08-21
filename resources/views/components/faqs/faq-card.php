<?php
// /resources/views/components/faqs/faq-card.php

/** @var \App\Models\Faq $faq */
/** @var bool $isAdmin */

$encodedId = \App\Utils\IdEncoder::encode((int)$faq->id);
$statusActive = (int)$faq->status_id === \App\Models\Faq::STATUS_ACTIVE;

$faqDataAttrs = [
    'encoded-id'    => $encodedId,
    'question'      => htmlspecialchars($faq->question),
    'answer'        => htmlspecialchars($faq->answer),
    'display-order' => $faq->display_order,
    'status-id'     => $faq->status_id,
];

$statusBadge = $statusActive
    ? '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-700 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>'
    : '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>';
?>

<div id="faq-row-<?= $faq->id ?>"
    class="faq-item mb-4 bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 transition-all"
    data-display-order="<?= $faq->display_order ?>"
    data-encoded-id="<?= $encodedId ?>">

    <div class="faq-toggle-btn w-full px-6 sm:px-8 py-6 flex items-center justify-between gap-4 text-left cursor-pointer select-none"
        role="button"
        aria-expanded="false">

        <div class="flex-1 min-w-0 pointer-events-none">
            <?php if ($isAdmin): ?>
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-primary-600 dark:text-primary-400 font-black text-xs uppercase tracking-widest">
                        Q. <?= $faq->display_order ?>
                    </span>
                    <div class="shrink-0"><?= $statusBadge ?></div>
                </div>
            <?php endif; ?>

            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors line-clamp-1">
                <?= htmlspecialchars($faq->question) ?>
            </h3>
        </div>

        <div class="flex items-center shrink-0 gap-3 min-w-fit">
            <?php if ($isAdmin): ?>
                <div class="hidden md:flex items-center gap-2 opacity-100 transition-all duration-200">
                    <?php
                    $dataAttrs = $faqDataAttrs;
                    $isMobile = false;
                    include __DIR__ . '/../ui/faq-actions.php';
                    ?>
                </div>
            <?php endif; ?>

            <div class="toggle-icon-container w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 group-hover:bg-primary-50 group-hover:text-primary-600 transition-all">
                <svg class="faq-arrow w-5 h-5 transition-transform duration-300 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>

    <div class="faq-answer-container max-h-0 overflow-hidden transition-all duration-500 ease-in-out bg-gray-50/50 dark:bg-gray-800/30">
        <div class="px-6 sm:px-8 py-8 border-t border-gray-100 dark:border-gray-800">
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                <?= nl2br(htmlspecialchars($faq->answer)) ?>
            </div>

            <?php if ($isAdmin): ?>
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 md:hidden flex justify-end">
                    <?php
                    $isMobile = true;
                    $dataAttrs = $faqDataAttrs;
                    include __DIR__ . '/../ui/faq-actions.php';
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>