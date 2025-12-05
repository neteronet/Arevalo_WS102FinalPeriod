<?php
include '../../includes/supervisor_login_process.php';
include '../../includes/connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAC Cyberian Repository</title>

    <!-- Tailwind CSS (CDN for ease of use) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- External CSS Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">
    
    <!-- Configure Tailwind Theme Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sac-blue': '#0A3D62', // Deep Blue
                        'sac-gold': '#FBC531', // Accent Gold
                        'cyber-dark': '#1f1f2e',
                    }
                }
            }
        }
    </script>

    <!-- Internal CSS for mobile hamburger (match student pages) -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

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
    </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen text-gray-800">
    <!-- Navigation -->
    <nav class="bg-sac-blue shadow-lg sticky top-0 z-50">
        <div class="w-full">
            <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
                <div class="flex justify-between items-center h-14 sm:h-16 md:h-16">
                    <div class="flex items-center min-w-0 flex-1">
                        <span class="text-sac-gold text-base sm:text-lg md:text-2xl font-bold tracking-wide truncate">SAC Cyberian</span>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-4 lg:space-x-8">
                        <a href="../../index.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Home</a>
                        <a href="../search.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Browse</a>
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
                        <a href="../search.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-3 sm:mb-4">Browse</a>
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

    <!-- Login Form Section -->
    <main class="flex-grow flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 border border-gray-200">
            <div class="text-center mb-8">
                <div class="mb-4 flex justify-center">
                    <svg class="w-16 h-16 text-sac-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-sac-blue mb-2">Supervisor Login</h2>
                <p class="text-gray-600">Access the SAC Cyberian Repository as a Supervisor</p>
                <p class="text-sm text-gray-500 mt-2">Review and manage student submissions</p>
            </div>

            <!-- Error Message Display -->
            <?php if (!empty($login_error)): ?>
                <div id="error-alert" class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-md" role="alert">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="font-bold text-base">Login Failed</p>
                            <p class="text-sm mt-1"><?php echo htmlspecialchars($login_error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- The form now uses action="" to post back to this same file -->
            <form action="" method="POST" class="space-y-6">
                <!-- Username/Email Field -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Supervisor ID or Email</label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                        placeholder="Enter your supervisor ID or email"
                        value="<?php echo isset($username_or_email) ? htmlspecialchars($username_or_email) : ''; ?>">
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                        placeholder="Enter your password">
                </div>

                <!-- Remember Me and Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-sac-blue focus:ring-sac-gold border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>
                    <div class="text-sm">
                        <a href="forgot-password.php" class="text-sac-blue hover:text-sac-gold font-medium">Forgot password?</a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full bg-sac-blue text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-sac-gold focus:ring-offset-2 transition duration-200 font-semibold">
                        Sign In as Supervisor
                    </button>
                </div>
            </form>

            <!-- Role Selection and Support Links -->
            <div class="text-center mt-6 space-y-3">
                <p class="text-sm text-gray-600">
                    <a href="../role-selection.php" class="text-sac-blue hover:text-sac-gold font-medium">Switch Role</a>
                </p>
                <p class="text-sm text-gray-600">Don't have an account? <a href="signup.php" class="text-sac-blue hover:text-sac-gold font-medium">Sign up here</a></p>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-4">
                <a href="../../index.php" class="text-sm text-gray-500 hover:text-gray-700">← Back to Home</a>
            </div>
        </div>
    </main>
    <!-- Footer -->
    <footer class="bg-cyber-dark text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; <?php echo date("Y"); ?> WS102 Final Period - BSIT 4</p>
            <p class="text-gray-400 text-sm mt-2">Preserving Institutional Excellence.</p>
        </div>
    </footer>
</body>

</html>