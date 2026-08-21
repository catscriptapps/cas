<?php
// /resources/views/pages/contact.php

declare(strict_types=1);
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php
    $breadcrumbs = ['Contact' => '/contact'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="mb-12">
        <h1 class="text-3xl lg:text-4xl font-black text-secondary-900 dark:text-white mb-3 tracking-tight">
            Get in <span class="text-primary-600">Touch.</span>
        </h1>
        <p class="text-base text-gray-500 dark:text-gray-400 max-w-xl font-medium">
            Have a question about an existing report, or interested in bringing your inspection company onto the platform? Send us a message and we'll get back to you.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-8">
            <div class="p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary-500/5 rounded-full blur-2xl"></div>

                <div class="space-y-8">
                    <div class="group/item">
                        <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2">Email Us</p>
                        <a href="mailto:info@homecomfortreports.com" class="text-gray-900 dark:text-white font-bold text-base sm:text-lg break-all hover:text-primary-600 transition-colors">
                            info@homecomfortreports.com
                        </a>
                    </div>

                    <div class="pt-8 border-t border-gray-50 dark:border-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                            </span>
                            <h4 class="font-black text-xs uppercase tracking-widest text-gray-900 dark:text-white">Response Time</h4>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium leading-relaxed">
                            We typically reply within one business day. For an existing report, include the access code in your message so we can pull it up quickly.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-8 rounded-3xl bg-primary-900 dark:bg-secondary-900 text-slate-100 shadow-xl border border-slate-800 relative overflow-hidden">
                <svg class="absolute right-0 bottom-0 opacity-5 w-24 h-24 translate-x-4 translate-y-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                </svg>

                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">For Inspection Companies</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black tracking-tighter">Bring Your Team Aboard</span>
                </div>
                <p class="text-xs mt-2 text-slate-400 leading-relaxed">Want your inspectors documenting and delivering reports through Home Comfort Reports? Tell us about your company and we'll be in touch about getting set up.</p>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white dark:bg-gray-900 p-8 lg:p-10 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
                <form id="contact-form" class="grid grid-cols-1 gap-7" novalidate>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-7">
                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Full Name</label>
                            <input type="text" name="full_name"
                                class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none font-semibold text-sm placeholder-gray-400"
                                placeholder="Alex Rivera" required>
                        </div>

                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Email Address</label>
                            <input type="email" name="email"
                                class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none font-semibold text-sm placeholder-gray-400"
                                placeholder="alex@example.com" required>
                        </div>
                    </div>

                    <div class="group">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Subject</label>
                        <input type="text" name="subject"
                            class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none font-semibold text-sm placeholder-gray-400"
                            placeholder="e.g. Question about my inspection report" required>
                    </div>

                    <div class="group">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Message</label>
                        <textarea name="message" rows="5"
                            class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none resize-none font-semibold text-sm placeholder-gray-400"
                            placeholder="Tell us about the property and what you need..." required></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" id="contact-submit"
                            class="group w-full sm:w-auto inline-flex items-center justify-center py-4 px-10 rounded-xl shadow-lg shadow-secondary-400/20 text-white bg-orange-900 dark:bg-primary-600 hover:bg-orange-800 dark:hover:bg-primary-500 transition-all duration-300 font-black uppercase tracking-widest text-xs active:scale-[0.97]">
                            <span class="flex items-center gap-3">
                                Send Message
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
