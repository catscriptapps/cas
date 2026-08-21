<?php
// /resources/views/pages/settings.php

// Page Icon: Cog/Settings
$pageIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.75 7.75 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>';
?>

<div id="settings-page" class="min-h-screen bg-gray-50 dark:bg-gray-950 font-sans pb-20 transition-colors duration-300">

    <div class="max-w-7xl mx-auto pt-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6" data-aos="fade-down">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 text-primary-500 text-[10px] font-black uppercase tracking-widest mb-4 border border-primary-500/20">
                    Control Center
                </div>
                <h1 class="text-5xl font-black text-secondary-900 dark:text-white tracking-tighter">Settings</h1>
            </div>
            <div class="flex gap-4">
                <button class="px-6 py-3 rounded-xl bg-white dark:bg-white/5 text-gray-500 text-xs font-black uppercase border border-gray-200 dark:border-white/10 hover:bg-gray-50 transition-all">Discard Changes</button>
                <button class="px-6 py-3 rounded-xl bg-primary-600 text-white text-xs font-black uppercase shadow-lg shadow-primary-500/30 hover:bg-primary-500 transition-all">Save Preferences</button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            <aside class="w-full lg:w-72 space-y-2" data-aos="fade-right">
                <?php
                $nav = [
                    ['Profile & Type', 'user'],
                    ['Security', 'lock-closed'],
                    ['Notifications', 'bell'],
                    ['Realtor Settings', 'home-modern'],
                    ['Contractor Hub', 'wrench-screwdriver'],
                    ['Display & Language', 'globe-americas']
                ];
                foreach ($nav as $i => $item): ?>
                    <button class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition-all <?= $i === 0 ? 'bg-secondary-900 text-white shadow-xl shadow-secondary-900/20' : 'text-gray-500 hover:bg-white dark:hover:bg-white/5' ?>">
                        <span class="font-black text-sm uppercase tracking-widest"><?= $item[0] ?></span>
                    </button>
                <?php endforeach; ?>
            </aside>

            <main class="flex-1 space-y-10" data-aos="fade-left" data-aos-delay="200">

                <section class="bg-white dark:bg-gray-900/50 rounded-[2.5rem] p-8 lg:p-12 shadow-xl border border-gray-100 dark:border-white/5 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-bl-[100px]"></div>

                    <h2 class="text-2xl font-black text-secondary-900 dark:text-white mb-8 flex items-center gap-3">
                        <span class="w-2 h-8 bg-primary-500 rounded-full"></span>
                        User Identity
                    </h2>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-2">Profile Picture</label>
                            <div class="flex items-center gap-6 p-4 rounded-3xl bg-gray-50 dark:bg-white/5 border border-dashed border-gray-200 dark:border-white/10">
                                <div class="w-20 h-20 rounded-2xl bg-secondary-900 flex items-center justify-center text-white text-2xl font-black shadow-inner">JD</div>
                                <button class="text-xs font-black text-primary-500 uppercase hover:underline">Change Photo</button>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="relative">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-2">User Type</label>
                                <select class="w-full mt-2 px-6 py-4 rounded-2xl bg-gray-50 dark:bg-white/5 border border-transparent focus:border-primary-500 outline-none font-bold text-secondary-900 dark:text-white transition-all appearance-none">
                                    <option>Individual User</option>
                                    <option>Realtor</option>
                                    <option>Contractor</option>
                                    <option>Mortgage Broker</option>
                                    <option>Property Manager</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white dark:bg-gray-900/50 rounded-[2.5rem] p-8 lg:p-12 shadow-xl border border-gray-100 dark:border-white/5">
                    <h2 class="text-2xl font-black text-secondary-900 dark:text-white mb-2 flex items-center gap-3">
                        <span class="w-2 h-8 bg-secondary-500 rounded-full"></span>
                        Marketplace Participation
                    </h2>
                    <p class="text-sm text-gray-500 mb-10 font-medium">Define how you interact with the Home Buyers and Sellers portals.</p>

                    <div class="space-y-6">
                        <div class="flex items-center justify-between p-6 rounded-3xl bg-secondary-900 text-white">
                            <div>
                                <span class="block font-black uppercase tracking-widest text-xs">Realtor Visibility</span>
                                <span class="text-[10px] text-gray-400 font-medium">Allow prospective sellers/buyers to find you in the network.</span>
                            </div>
                            <button class="w-14 h-8 bg-primary-500 rounded-full relative shadow-inner">
                                <div class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow-md"></div>
                            </button>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="p-6 rounded-3xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block font-black uppercase tracking-widest text-xs text-secondary-900 dark:text-white mb-4">Contractor Skills</span>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 bg-white dark:bg-white/10 rounded-lg text-[10px] font-black border border-gray-100 dark:border-white/5">+ Plumbing</span>
                                    <span class="px-3 py-1 bg-white dark:bg-white/10 rounded-lg text-[10px] font-black border border-gray-100 dark:border-white/5">+ Electrical</span>
                                </div>
                            </div>
                            <div class="p-6 rounded-3xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block font-black uppercase tracking-widest text-xs text-secondary-900 dark:text-white mb-4">Quotation Settings</span>
                                <button class="text-[10px] font-black text-primary-500 uppercase tracking-widest">Edit Auto-Quote Rules</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white dark:bg-gray-900/50 rounded-[2.5rem] p-8 lg:p-12 shadow-xl border border-gray-100 dark:border-white/5">
                    <h2 class="text-2xl font-black text-secondary-900 dark:text-white mb-8 flex items-center gap-3">
                        <span class="w-2 h-8 bg-red-500 rounded-full"></span>
                        Security & Access
                    </h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <button class="flex items-center justify-between px-8 py-5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 hover:border-red-500/50 transition-all group">
                            <span class="text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400 group-hover:text-red-500">Change Password</span>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <button class="flex items-center justify-between px-8 py-5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 hover:border-primary-500/50 transition-all group">
                            <span class="text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400 group-hover:text-primary-500">Two-Factor Auth</span>
                            <span class="text-[10px] font-black bg-green-500/10 text-green-500 px-2 py-1 rounded">Enabled</span>
                        </button>
                    </div>
                </section>

            </main>
        </div>
    </div>
</div>