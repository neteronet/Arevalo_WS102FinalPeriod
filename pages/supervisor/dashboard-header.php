<?php
// Supervisor dashboard header file - includes navigation with Submissions and Logout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - SAC Cyberian Repository</title>

    <!-- Tailwind CSS (CDN for ease of use) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Configure Tailwind Theme Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sac-blue': '#0A3D62',
                        'sac-gold': '#FBC531',
                        'cyber-dark': '#1f1f2e',
                    }
                }
            }
        }
    </script>

    <!-- Internal CSS for Custom Overrides (match student pages for mobile hamburger) -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        /* Mobile Menu Toggle */
        .mobile-menu {
            display: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .mobile-menu.active {
            display: block;
            max-height: 500px;
        }

        .hamburger-menu {
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 8px;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
        }

        .hamburger-menu span {
            display: block;
            width: 22px;
            height: 2.5px;
            background-color: white;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 2px;
            transform-origin: center;
        }

        .status-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-draft {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen text-gray-800">
    <!-- Navigation -->
    <nav class="bg-sac-blue shadow-lg sticky top-0 z-50">
        <div class="w-full">
            <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
                <div class="flex justify-between items-center h-14 sm:h-16 md:h-16">
                    <div class="flex items-center min-w-0 flex-1">
                        <span class="text-sac-gold text-base sm:text-lg md:text-2xl font-bold tracking-wide truncate">
                            SAC Cyberian 
                        </span>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-4 lg:space-x-8">
                        <a href="../../index.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Home</a>
                        <a href="../search.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Browse</a>
                        <a href="dashboard.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Dashboard</a>

                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && ($_SESSION['role'] ?? '') === 'supervisor'): ?>
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center text-white text-sm lg:text-base whitespace-nowrap cursor-default">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sac-gold text-sac-blue font-bold mr-2">
                                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                </div>
                                <a href="logout.php" class="px-3 sm:px-4 py-2 bg-red-600 text-white font-bold rounded hover:bg-red-700 transition text-sm lg:text-base whitespace-nowrap">Logout</a>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="px-3 sm:px-4 py-2 bg-sac-gold text-sac-blue font-bold rounded hover:bg-yellow-400 transition text-sm lg:text-base whitespace-nowrap">Login</a>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Hamburger Menu - Right Side -->
                    <div class="md:hidden ml-auto">
                        <div class="hamburger-menu" id="hamburger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu - Dropdown -->
                <div class="mobile-menu" id="mobileMenu">
                    <div class="bg-sac-blue border-t border-blue-500 md:hidden px-2 sm:px-4 py-2 sm:py-3">
                        <a href="../../index.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-1">Home</a>
                        <a href="../search.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-1">Browse</a>
                        <a href="dashboard.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-3 sm:mb-4">Dashboard</a>

                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && ($_SESSION['role'] ?? '') === 'supervisor'): ?>
                            <a href="logout.php" class="block text-white bg-red-600 font-bold rounded hover:bg-red-700 transition duration-300 px-4 py-2.5 text-sm sm:text-base text-center w-full">Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="block text-white bg-sac-gold text-sac-blue font-bold rounded hover:bg-yellow-400 transition duration-300 px-4 py-2.5 text-sm sm:text-base text-center w-full">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile Menu Toggle
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobileMenu');

        if (hamburger) {
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                mobileMenu.classList.toggle('active');
            });

            // Close menu when a link is clicked
            const mobileMenuLinks = mobileMenu.querySelectorAll('a');
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    hamburger.classList.remove('active');
                    mobileMenu.classList.remove('active');
                });
            });
        }
    </script>


