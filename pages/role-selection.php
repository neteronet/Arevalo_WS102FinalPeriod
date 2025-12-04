<?php
// Role Selection Page
session_start();

// If role is already selected, redirect
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'student') {
        header('Location: login.php');
    } elseif ($_SESSION['user_role'] === 'supervisor') {
        header('Location: supervisor-login.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Selection - SAC Cyberian Repository</title>

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
</head>

<body class="bg-gray-50 flex flex-col min-h-screen text-gray-800">
    <!-- Navigation -->
    <nav class="bg-sac-blue shadow-lg sticky top-0 z-50">
        <div class="w-full">
            <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
                <div class="flex justify-between items-center h-14 sm:h-16 md:h-16">
                    <div class="flex items-center min-w-0 flex-1">
                        <a href="../index.php" class="text-sac-gold text-base sm:text-lg md:text-2xl font-bold tracking-wide truncate hover:text-yellow-300 transition">SAC Cyberian</a>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-4 lg:space-x-8">
                        <a href="../index.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Home</a>
                        <a href="search.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Browse</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-4xl">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-sac-blue mb-4">Select Your Role</h1>
                <p class="text-lg text-gray-600">Choose your role to continue with the SAC Cyberian Repository</p>
            </div>

            <!-- Role Selection Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Student Card -->
                <div class="role-card bg-white p-8 rounded-lg shadow-lg border-2 border-gray-200 hover:shadow-xl transition">
                    <div class="text-center">
                        <div class="mb-4 flex justify-center">
                            <svg class="w-20 h-20 text-sac-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-sac-blue mb-3">Student</h2>
                        <p class="text-gray-600 mb-6">Access and browse capstone projects, search for research documents, and view institutional repositories.</p>
                        <div class="space-y-2 text-sm text-gray-500 mb-6">
                            <p class="flex items-center justify-center">
                                <span class="inline-block w-2 h-2 bg-sac-gold rounded-full mr-2"></span>
                                Browse all projects
                            </p>
                            <p class="flex items-center justify-center">
                                <span class="inline-block w-2 h-2 bg-sac-gold rounded-full mr-2"></span>
                                Search by category
                            </p>
                            <p class="flex items-center justify-center">
                                <span class="inline-block w-2 h-2 bg-sac-gold rounded-full mr-2"></span>
                                View abstracts & details
                            </p>
                        </div>
                        <form action="student/login.php" method="GET">
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="w-full bg-sac-blue text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-800 transition">
                                Continue as Student
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Supervisor Card -->
                <div class="role-card bg-white p-8 rounded-lg shadow-lg border-2 border-gray-200 hover:shadow-xl transition">
                    <div class="text-center">
                        <div class="mb-4 flex justify-center">
                            <svg class="w-20 h-20 text-sac-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-sac-blue mb-3">Supervisor</h2>
                        <p class="text-gray-600 mb-6">Manage and review student capstone projects, track submissions, and oversee academic research progress.</p>
                        <div class="space-y-2 text-sm text-gray-500 mb-6">
                            <p class="flex items-center justify-center">
                                <span class="inline-block w-2 h-2 bg-sac-gold rounded-full mr-2"></span>
                                Review submissions
                            </p>
                            <p class="flex items-center justify-center">
                                <span class="inline-block w-2 h-2 bg-sac-gold rounded-full mr-2"></span>
                                Track projects
                            </p>
                            <p class="flex items-center justify-center">
                                <span class="inline-block w-2 h-2 bg-sac-gold rounded-full mr-2"></span>
                                Manage approvals
                            </p>
                        </div>
                        <form action="supervisor/login.php" method="GET">
                            <input type="hidden" name="role" value="supervisor">
                            <button type="submit" class="w-full bg-sac-blue text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-800 transition">
                                Continue as Supervisor
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-8">
                <p class="text-gray-600">
                    <a href="../index.php" class="text-sac-blue hover:text-sac-gold font-semibold">Back to Home</a>
                </p>
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
