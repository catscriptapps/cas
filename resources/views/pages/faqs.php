<?php
// /resources/views/pages/faqs.php
declare(strict_types=1);

/** @var string $appName */

use Src\Service\AuthService;

// Initialize Controller
$controller = new \Src\Controller\FaqsController();
$controller->index();

// Pull rows from the global scope (populated by controller->index)
$faqRows = $GLOBALS['faqRows'] ?? '';
$isAdmin = (AuthService::isAdmin());
?>

<div class="py-10 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">
    <?php
    $breadcrumbs = ['FAQs' => '/faqs'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="relative mb-12">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8 pb-10">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 text-xs font-black uppercase tracking-widest mb-4 border border-primary-100 dark:border-primary-900/50">
                    Help Center
                </div>
            </div>

            <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
                <div class="relative flex-1 md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="faqs-search"
                        class="block w-full rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2 pl-10 pr-3 text-sm placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:text-white transition-all font-sans"
                        placeholder="Search knowledge base...">
                </div>

                <?php if ($isAdmin): ?>
                    <button type="button" id="add-faq-btn" data-tooltip="Add FAQ"
                        class="shrink-0 flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-700 transition-all active:scale-95 focus:ring-2 focus:ring-primary-500 font-sans">
                        <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Add FAQ</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="faqs-container" class="space-y-6" data-logged-in="<?= $isAdmin ? '1' : '0' ?>">
        <?php if (empty($faqRows)): ?>
            <div class="p-20 rounded-[3rem] bg-gray-50 dark:bg-gray-900/30 border-2 border-dashed border-gray-200 dark:border-gray-800 text-center">
                <div class="flex flex-col items-center">
                    <svg class="h-16 w-16 text-gray-300 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8.227 9.164a3.001 3.001 0 115.546 1.672c-.57 1.11-1.77 1.528-2.81 2.003a.75.75 0 00-.416.68v.75m.75 3h.008" />
                    </svg>
                    <p class="text-xl font-black text-gray-400 dark:text-gray-600">No questions found.</p>
                    <p class="text-sm text-gray-500 mt-2 font-medium">Try adjusting your search or add a new entry.</p>
                </div>
            </div>
        <?php else: ?>
            <?= $faqRows ?>
        <?php endif; ?>
    </div>

    <div class="mt-10">
        <?php
        $footerCountName = 'faqs';
        include __DIR__ . '/../components/ui/footer-count.php';
        ?>
    </div>

    <div class="h-24"></div>
</div>

<div class="hidden rotate-180 text-primary-600 transition-transform duration-300"></div>