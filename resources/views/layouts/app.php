<?php
// /resources/views/layouts/app.php

declare(strict_types=1);

// Cache-busting token for the JS/CSS bundles below -- derived from the
// build's own manifest mtime so it changes on every `npm run build` without
// needing content-hashed filenames. Without this, browsers can keep serving
// a stale cached page script (or a stale dynamically-imported chunk) after a
// deploy, since app.min.js/*.min.js are otherwise requested from the exact
// same URL every time.
$buildVersion = @filemtime(__DIR__ . '/../../public/assets/js/page-manifest.json') ?: time();

/** @var string $title */
/** @var string $appName */
/** @var string $assetBase */
/** @var string $baseUrl */
/** @var array<string, mixed>|null $protectedPaths */
/** @var object|null $currentUser */
/** @var bool $isLoggedIn */
/** @var string $pageFile */
/** @var string|null $rootPageScript */
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth overflow-x-hidden"
    x-data="{ darkMode: document.documentElement.classList.contains('dark') }"
    @dark-toggle.window="darkMode = document.documentElement.classList.contains('dark')"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <title><?= htmlspecialchars($title) . ' | ' . htmlspecialchars($appName); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="shortcut icon" type="image/x-icon" href="<?= $assetBase ?>images/logo/favicon.ico">
    <link rel="icon" type="image/x-icon" href="<?= $assetBase ?>images/logo/favicon.ico">

    <script>
        window.APP_CONFIG = {
            baseUrl: <?= json_encode($baseUrl) ?>,
            assetBase: <?= json_encode($assetBase) ?>,
            appName: <?= json_encode($appName) ?>,
            protectedPaths: <?= json_encode($protectedPaths ?? []) ?>,
            isLoggedIn: <?= json_encode($isLoggedIn) ?>,
            isAdmin: <?= json_encode(\Src\Service\AuthService::isAdmin()) ?>,
            isCompanyAdmin: <?= json_encode(\Src\Service\AuthService::isCompanyAdmin()) ?>,
            rootPageScript: <?= json_encode($rootPageScript ?? null) ?>,
            mediaLimit: <?= getMediaLimit() ?>,
            buildVersion: <?= json_encode((string)$buildVersion) ?>,

            userDefaults: {
                country_id: <?= json_encode($currentUser->country_id ?? null) ?>,
                region_id: <?= json_encode($currentUser->region_id ?? null) ?>
            }
        };
    </script>

    <link rel="stylesheet" href="<?= $assetBase ?>assets/css/app.min.css?v=<?= $buildVersion ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
        }

        html::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        html::-webkit-scrollbar-track {
            background: transparent;
        }

        html::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.3);
            border-radius: 9999px;
            transition: background-color 0.2s ease;
        }

        html::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.5);
        }

        .dark html {
            scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
        }

        .dark html::-webkit-scrollbar-thumb {
            background-color: rgba(75, 85, 99, 0.3);
        }

        .dark html::-webkit-scrollbar-thumb:hover {
            background-color: rgba(75, 85, 99, 0.5);
        }

        [x-cloak] {
            display: none !important;
        }

        @keyframes customKenBurns {
            0% {
                transform: scale(1) rotate(0.02deg);
                opacity: 0;
            }

            10% {
                opacity: 0.40;
            }

            90% {
                opacity: 0.40;
            }

            100% {
                transform: scale(1.12) rotate(0.02deg);
                opacity: 0;
            }
        }

        .animate-slideshow-zoom {
            animation: customKenBurns 8s ease-in-out infinite both;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800 font-sans antialiased dark:bg-slate-900 dark:text-gray-100 transition-colors duration-300 w-full">
    <?php include __DIR__ . '/../partials/layout-topbar.php'; ?>

    <div id="app-shell-content" class="flex-1 pt-[50px] sm:pt-[52px]">
        <?php include __DIR__ . '/../partials/layout-header.php'; ?>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div id="modal-zone"></div>
            <div id="main-content">
                <?php include $pageFile; ?>
            </div>
        </main>
    </div>

    <?php
    include __DIR__ . '/../partials/image-preview-modal.php';
    include __DIR__ . '/../partials/search-modal.php';
    include __DIR__ . '/../partials/layout-footer.php';
    include __DIR__ . '/../components/scroll-top.php';
    ?>

    <?php if ($isLoggedIn && isset($_SESSION['user_id'])): ?>
        <script>
            window.sessionUserId = <?= json_encode($_SESSION['user_id']) ?>;
        </script>
    <?php endif; ?>

    <script type="module" src="<?= $assetBase ?>assets/js/app.min.js?v=<?= $buildVersion ?>"></script>
</body>

</html>