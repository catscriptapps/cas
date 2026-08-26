<?php
// /resources/views/pages/register.php

declare(strict_types=1);

/** @var string $baseUrl */
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 lg:py-16 font-sans" id="register-wizard" data-current-step="1">

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "Register Now." + its summary for every viewer (see
    // layout-header.php and NavigationConfig::resolveMetaForPath()'s
    // page-only fallback list, since /register has no nav-link entry of its
    // own to source that from). Freeing this vertical space means the Step 1
    // sport buttons are visible without scrolling on first load.
    ?>

    <!-- Step indicator -->
    <div class="flex items-center justify-center gap-2 mb-10" id="register-step-indicator">
        <?php foreach (['Sport', 'League', 'Division', 'Details', 'Waiver', 'Payment'] as $i => $label): ?>
            <div class="flex items-center gap-2">
                <div data-step-dot="<?= $i + 1 ?>" class="h-2.5 w-2.5 rounded-full transition-colors <?= $i === 0 ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700' ?>"></div>
                <?php if ($i < 5): ?><span class="w-4 sm:w-8 h-px bg-gray-200 dark:bg-gray-700"></span><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Step 1: Sport -->
    <section data-step="1" class="register-step">
        <h2 class="text-lg font-black text-secondary-900 dark:text-white mb-5 text-center">Choose a Sport</h2>
        <div id="register-sports" class="grid grid-cols-2 gap-4"></div>
    </section>

    <!-- Step 2: League -->
    <section data-step="2" class="register-step hidden">
        <h2 class="text-lg font-black text-secondary-900 dark:text-white mb-5 text-center">Choose a League</h2>
        <div id="register-leagues" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
        <button type="button" data-back-step="1" class="mt-6 text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-primary-600 transition-colors">&larr; Back</button>
    </section>

    <!-- Step 3: Division -->
    <section data-step="3" class="register-step hidden">
        <h2 class="text-lg font-black text-secondary-900 dark:text-white mb-5 text-center">Choose a Division</h2>
        <div id="register-divisions" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
        <button type="button" data-back-step="2" class="mt-6 text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-primary-600 transition-colors">&larr; Back</button>
    </section>

    <!-- Step 4: Details -->
    <section data-step="4" class="register-step hidden">
        <div class="bg-white dark:bg-gray-900 p-6 lg:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <h2 class="text-lg font-black text-secondary-900 dark:text-white mb-6">Your Details</h2>
            <form id="register-details-form" class="grid grid-cols-1 sm:grid-cols-2 gap-5" novalidate>
                <div>
                    <label for="reg-first-name" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">First Name</label>
                    <input type="text" id="reg-first-name" name="first_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-last-name" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Last Name</label>
                    <input type="text" id="reg-last-name" name="last_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-age" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Age</label>
                    <input type="number" min="1" max="99" id="reg-age" name="age" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-email" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Email</label>
                    <input type="email" id="reg-email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-phone" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Phone</label>
                    <input type="text" id="reg-phone" name="phone" required placeholder="(705) 555-0123" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-position" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Desired Position</label>
                    <input type="text" id="reg-position" name="desired_position" placeholder="e.g. Forward, Goalie" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div class="sm:col-span-2">
                    <label for="reg-address" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Address</label>
                    <input type="text" id="reg-address" name="address" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-city" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">City</label>
                    <input type="text" id="reg-city" name="city" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-postal" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Postal Code</label>
                    <input type="text" id="reg-postal" name="postalCode" required placeholder="L4N 0R5" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-country" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Country</label>
                    <select id="reg-country" name="countryId" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                        <option value="">Select Country</option>
                    </select>
                </div>
                <div>
                    <label for="reg-region" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Province / State</label>
                    <select id="reg-region" name="regionId" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                        <option value="">Select Region</option>
                    </select>
                </div>
                <div>
                    <label for="reg-team" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Team (optional)</label>
                    <input type="text" id="reg-team" name="team_name" placeholder="Leave blank if you need a team" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label for="reg-source" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">How Did You Hear About Us?</label>
                    <select id="reg-source" name="hear_about_us" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                        <option value="">Select One</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="reg-notes" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Special Requests (optional)</label>
                    <textarea id="reg-notes" name="special_requests" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all resize-none"></textarea>
                </div>

                <div class="sm:col-span-2 flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" data-back-step="3" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-primary-600 transition-colors">&larr; Back</button>
                    <button type="submit" class="inline-flex items-center gap-2 py-3 px-8 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
                        Continue
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Step 5: Waiver -->
    <section data-step="5" class="register-step hidden">
        <div class="bg-white dark:bg-gray-900 p-6 lg:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <h2 class="text-lg font-black text-secondary-900 dark:text-white mb-4">Waiver &amp; Release</h2>
            <div class="max-h-56 overflow-y-auto text-xs text-gray-500 dark:text-gray-400 leading-relaxed bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-800 mb-5">
                <p class="mb-2">By registering to participate in any Canadian All Star Sports league, I acknowledge that participation in hockey involves inherent risks of injury, and I voluntarily assume all such risks.</p>
                <p class="mb-2">I release Canadian All Star Sports, its organizers, volunteers, referees, and affiliated rinks/venues from any liability for injuries sustained during participation, except in cases of gross negligence.</p>
                <p>I confirm that the information provided during registration is accurate to the best of my knowledge, and that registration fees are non-refundable once a season has begun.</p>
            </div>
            <label class="flex items-start gap-3 cursor-pointer mb-6">
                <input type="checkbox" id="register-agree-checkbox" class="mt-1 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">I have read and agree to the waiver above.</span>
            </label>
            <div id="register-submit-error" class="hidden mb-4 px-4 py-2.5 rounded-xl bg-red-100 border border-red-400 text-red-700 text-sm font-bold"></div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                <button type="button" data-back-step="4" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-primary-600 transition-colors">&larr; Back</button>
                <button type="button" id="register-submit-btn" disabled
                    class="inline-flex items-center gap-2 py-3 px-8 rounded-xl bg-primary-600 hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
                    Continue to Payment
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Step 6: Payment -->
    <section data-step="6" class="register-step hidden">
        <div class="bg-white dark:bg-gray-900 p-6 lg:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm text-center">
            <h2 class="text-lg font-black text-secondary-900 dark:text-white mb-1">Complete Payment</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1"><span id="register-division-label" class="font-bold"></span></p>
            <p class="text-3xl font-black text-primary-600 mb-6">$<span id="register-amount-due">0.00</span> <span class="text-sm text-gray-400 font-bold" id="register-currency-label"></span></p>
            <div id="paypal-buttons" class="max-w-sm mx-auto"></div>
            <div id="register-payment-error" class="hidden mt-4 px-4 py-2.5 rounded-xl bg-red-100 border border-red-400 text-red-700 text-sm font-bold"></div>
            <p id="paypal-unconfigured-notice" class="hidden mt-4 text-xs text-gray-400 font-medium">
                Online payment isn't configured yet -- an admin can mark this registration paid manually from the Registrations tab.
            </p>
        </div>
    </section>

    <!-- Confirmation -->
    <section data-step="7" class="register-step hidden text-center">
        <div class="mx-auto w-20 h-20 mb-6 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30">
            <svg class="w-10 h-10 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h2 class="text-2xl font-black text-secondary-900 dark:text-white mb-2">You're Registered!</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
            Thanks, <span id="register-confirm-name" class="font-bold"></span> -- a confirmation has been recorded for your registration. We'll be in touch with next steps.
        </p>
        <a href="<?= $baseUrl ?>home" data-partial class="inline-flex items-center gap-2 mt-8 py-3 px-8 rounded-xl bg-secondary-500 hover:bg-secondary-400 text-slate-900 font-black text-xs uppercase tracking-widest shadow-lg transition-all active:scale-[0.98]">
            Back to Home
        </a>
    </section>
</div>
