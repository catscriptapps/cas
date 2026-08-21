<?php
// /resources/views/pages/profile.php

use App\Utils\IdEncoder;

/** @var \App\Models\User $currentUser */
/** @var \App\Models\User $user */
/** @var string $assetBase */


$user = $currentUser;

// New Model Mapping
$fullName = $user->full_name; // Uses the getFullNameAttribute() accessor
$initials = strtoupper(substr($user->first_name ?? 'U', 0, 1));
$statusIsActive = ((int)$user->status_id === 1);

// Location Logic
$regionName = $user->region->name ?? $user->region->region ?? '';
$countryName = $user->country->name ?? $user->country->country ?? '';

// Avatar Logic (Preserved)
$hasAvatar = !empty($user->avatar_url);
$AVATAR_DIR_PREFIX = $assetBase . 'images/uploads/avatars/';
$avatarUrl = $hasAvatar ? htmlspecialchars($AVATAR_DIR_PREFIX . $user->avatar_url) : '';

// Role Mapping - DYNAMIC DB FETCH (Replacing static array)
if (!isset($GLOBALS['allUserTypes'])) {
    $types = \Src\Controller\UserTypesController::list();
    $GLOBALS['allUserTypes'] = [];
    foreach ($types as $t) {
        $GLOBALS['allUserTypes'][$t->user_type_id] = $t->user_type;
    }
}

$primaryRole = 'User Profile';
?>

<div id="partial-profile" class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] px-4 sm:px-6 lg:px-8 py-10">
    <div id="profile-page-container" class="max-w-6xl mx-auto">
        <?php
        $breadcrumbs = ['Profile' => '/profile'];
        include __DIR__ . '/../components/ui/breadcrumbs.php';
        ?>

        <!-- Identity card -->
        <div class="relative overflow-hidden rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm mb-6" data-aos="fade-up" data-aos-duration="600">
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-primary-500/[0.06] dark:bg-primary-500/10 rounded-full blur-[90px] pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-secondary-500/[0.06] dark:bg-secondary-500/10 rounded-full blur-[90px] pointer-events-none"></div>

            <div class="relative p-6 md:p-8 flex flex-col md:flex-row items-center gap-8">

                <div class="relative shrink-0 group" id="avatar-preview-wrapper">
                    <div id="avatar-container"
                        data-action="view-avatar"
                        data-img-src="<?= $avatarUrl; ?>"
                        class="h-28 w-28 md:h-32 md:w-32 rounded-[1.75rem] overflow-hidden ring-4 ring-gray-50 dark:ring-white/5 shadow-xl bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center transition-all duration-500 group-hover:scale-105 <?= $hasAvatar ? 'cursor-zoom-in' : ''; ?>">

                        <span id="avatar-initial" class="text-4xl font-black text-white tracking-tighter <?= $hasAvatar ? 'hidden' : 'block'; ?>">
                            <?= $initials; ?>
                        </span>

                        <img id="avatar-img" src="<?= $avatarUrl; ?>" alt="Profile"
                            class="w-full h-full object-cover <?= $hasAvatar ? 'block' : 'hidden'; ?>">

                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" stroke-width="3" />
                            </svg>
                        </div>
                    </div>

                    <button id="change-avatar-btn" data-action="upload" title="Change photo"
                        class="absolute -bottom-1 -right-1 p-2.5 bg-primary-500 text-white rounded-xl shadow-lg hover:bg-primary-400 transition-all z-10 border-2 border-white dark:border-gray-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" stroke-width="2.5" />
                        </svg>
                    </button>
                    <input type="file" id="avatar-file-input" class="hidden" accept="image/*">
                </div>

                <div class="flex-1 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-primary-500/10 text-primary-600 dark:text-primary-400 text-[9px] font-black uppercase tracking-widest mb-2">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-primary-500"></span>
                        </span>
                        <?= $primaryRole ?>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-black text-secondary-900 dark:text-white tracking-tight leading-tight mb-1" data-field="fullName">
                        <?= htmlspecialchars($fullName); ?>
                    </h1>

                    <p class="text-sm text-gray-400 font-medium mb-4">
                        Member since <?= $user->date_created->format('M Y') ?>
                    </p>

                    <?php if ($user->user_type_id): ?>
                        <div class="flex flex-wrap justify-center md:justify-start gap-1.5">
                            <?php $roleName = htmlspecialchars((string)($GLOBALS['allUserTypes'][$user->user_type_id] ?? 'User')); ?>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-gray-50 dark:bg-slate-800 text-slate-500 dark:text-slate-300 border border-gray-100 dark:border-slate-700">
                                <?= $roleName ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="shrink-0">
                    <button
                        data-action="edit-user-profile"
                        data-encoded-id="<?= IdEncoder::encode($user->id); ?>"
                        data-first-name="<?= htmlspecialchars($user->first_name); ?>"
                        data-last-name="<?= htmlspecialchars($user->last_name); ?>"
                        data-full-name="<?= htmlspecialchars($user->full_name); ?>"
                        data-email="<?= htmlspecialchars($user->email); ?>"
                        data-city="<?= htmlspecialchars($user->city ?? ''); ?>"
                        data-country-id="<?= $user->country_id ?? 0; ?>"
                        data-region-id="<?= $user->region_id ?? 0; ?>"
                        data-is-active="<?= $statusIsActive ? '1' : '0'; ?>"
                        data-avatar-url="<?= htmlspecialchars($user->avatar_url ?? ''); ?>"
                        data-user-type-id="<?= (int)($user->user_type_id ?? 0); ?>"
                        class="px-5 py-2.5 bg-secondary-900 dark:bg-gray-100 text-white dark:text-secondary-900 rounded-xl font-black text-xs transition-all hover:-translate-y-0.5 shadow-lg flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Profile</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            <!-- Account details -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-[2rem] p-6 border border-gray-100 dark:border-white/5 shadow-sm" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                <h2 class="text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 mb-5">Account Details</h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Email Address</label>
                        <div class="flex items-center gap-2">
                            <p class="text-base text-secondary-900 dark:text-white font-bold truncate" data-field="email"><?= htmlspecialchars($user->email); ?></p>
                            <?php if ($user->email_verified): ?>
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" title="Verified">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                </svg>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Location</label>
                        <p class="text-base text-secondary-900 dark:text-white font-bold">
                            <?= htmlspecialchars($user->city ?: 'Unset') ?><?= $regionName ? ", $regionName" : "" ?>
                            <span class="text-gray-400 font-medium text-sm"><?= $countryName ? "($countryName)" : "" ?></span>
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-50 dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Last Sign-In</p>
                        <p class="text-sm font-bold text-secondary-900 dark:text-white"><?= $user->user_last_log ? $user->user_last_log->diffForHumans() : 'New Connection' ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Account Status</p>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider <?= $statusIsActive ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' ?>">
                            <span class="h-1.5 w-1.5 rounded-full <?= $statusIsActive ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                            <?= $statusIsActive ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Photo management -->
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] p-6 border border-gray-100 dark:border-white/5 shadow-sm flex flex-col" data-aos="fade-up" data-aos-duration="600" data-aos-delay="150">
                <h2 class="text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4">Profile Photo</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-5">
                    Your photo appears alongside reports and activity you're associated with. Use the pencil icon on your avatar to upload a new one.
                </p>

                <div id="delete-avatar-container" class="mt-auto" style="display: <?= $hasAvatar ? 'block' : 'none'; ?>;">
                    <button id="delete-avatar-btn"
                        data-action="delete-avatar"
                        data-id="<?= IdEncoder::encode($user->id); ?>"
                        class="w-full py-3 bg-red-50 hover:bg-red-500 dark:bg-red-500/10 dark:hover:bg-red-600 text-red-500 hover:text-white dark:text-red-400 border border-red-100 dark:border-red-500/20 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Remove Photo
                    </button>
                </div>
            </div>
        </div>

        <!-- Update password -->
        <div class="bg-white dark:bg-gray-900 rounded-[2rem] p-6 md:p-8 border border-gray-100 dark:border-white/5 shadow-sm" data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
            <div class="flex items-center gap-3 mb-1">
                <div class="h-9 w-9 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-lock text-sm"></i>
                </div>
                <h2 class="text-lg font-black text-secondary-900 dark:text-white tracking-tight">Update Password</h2>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-6 ml-12">
                Choose a strong password you don't use anywhere else. You'll need your current password to confirm the change.
            </p>

            <form id="change-password-form" class="grid md:grid-cols-3 gap-4" novalidate>
                <div class="space-y-1.5">
                    <label for="current_password" class="text-[9px] font-black uppercase tracking-widest text-gray-400">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold placeholder-gray-400 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                </div>
                <div class="space-y-1.5">
                    <label for="new_password" class="text-[9px] font-black uppercase tracking-widest text-gray-400">New Password</label>
                    <input type="password" id="new_password" name="new_password" required autocomplete="new-password" minlength="8"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold placeholder-gray-400 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                </div>
                <div class="space-y-1.5">
                    <label for="confirm_password" class="text-[9px] font-black uppercase tracking-widest text-gray-400">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" minlength="8"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold placeholder-gray-400 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                </div>

                <div class="md:col-span-3 flex justify-end pt-1">
                    <button type="submit" id="btn-change-password"
                        class="inline-flex items-center justify-center gap-2 py-3 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all duration-300 active:scale-[0.98]">
                        Save New Password
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
