<?php
include 'includes/connection.php';

// Initialize stats variables
$stats = ['total_docs' => 0, 'total_advisers' => 0];
$latest_docs = [];
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Logic Execution
if (isset($conn)) {
    // 1. Fetch Stats
    $sql_stats = "SELECT 
                    (SELECT COUNT(*) FROM capstones WHERE status='approved') as total_docs,
                    (SELECT COUNT(DISTINCT adviser) FROM capstones) as total_advisers";

    $result = $conn->query($sql_stats);
    if ($result) {
        $stats = $result->fetch_assoc();
    }

    // 2. Fetch Latest 5 Submissions based on category
    if ($selected_category === 'all') {
        $sql_latest = "SELECT id, title, authors, year, department, abstract, category FROM capstones WHERE status='approved' ORDER BY date_submitted DESC LIMIT 5";
    } else {
        $sql_latest = "SELECT id, title, authors, year, department, abstract, category FROM capstones WHERE status='approved' AND category = ? ORDER BY date_submitted DESC LIMIT 5";
    }
    
    if ($selected_category === 'all') {
        $result_latest = $conn->query($sql_latest);
    } else {
        $stmt = $conn->prepare($sql_latest);
        $stmt->bind_param("s", $selected_category);
        $stmt->execute();
        $result_latest = $stmt->get_result();
    }

    if ($result_latest) {
        while ($row = $result_latest->fetch_assoc()) {
            $latest_docs[] = $row;
        }
    }
}
?>

<!-- Database Error Alert (SweetAlert) -->
<?php if (isset($db_error)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'System Error',
            text: '<?php echo $db_error; ?>',
            footer: 'Please contact the IT Administrator.'
        });
    </script>
<?php endif; ?>

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

    <!-- Internal CSS for Custom Overrides -->
    <style>
        /* Custom Scrollbar for a "Cyber" feel */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #0A3D62;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #FBC531;
        }

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
                        <a href="index.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Home</a>
                        <a href="../git/pages/search.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Browse</a>
                        <a href="../git/pages/role-selection.php" class="px-3 sm:px-4 py-2 bg-sac-gold text-sac-blue font-bold rounded hover:bg-yellow-400 transition text-sm lg:text-base whitespace-nowrap">Login</a>
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
                        <a href="../index.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-1">Home</a>
                        <a href="../pages/search.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-3 sm:mb-4">Browse</a>
                        <a href="../pages/role-selection.php" class="block text-white bg-sac-gold text-sac-blue font-bold rounded hover:bg-yellow-400 transition duration-300 px-4 sm:px-5 py-2.5 sm:py-3 text-sm sm:text-base text-center w-full">Login</a>
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

    <!-- Hero Section -->
    <header class="bg-sac-blue text-white py-20 border-b-4 border-sac-gold">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">SAC Cyberian Centralized Repository</h1>
            <p class="text-lg md:text-xl text-gray-200 mb-2">Preserving and promoting the academic research and capstone</p>
            <p class="text-lg md:text-xl text-gray-200 mb-8">projects of St. Anthony's College BSIT students.</p>

            <!-- Search Bar -->
            <form action="pages/search.php" method="GET" class="relative max-w-2xl mx-auto">
                <input type="text" name="q" placeholder="Search by Title, Author, or Keyword..."
                    class="w-full py-4 px-6 rounded-full text-gray-800 focus:outline-none focus:ring-4 focus:ring-sac-gold shadow-lg">
                <button type="submit" class="absolute right-2 top-2 bg-sac-gold text-sac-blue font-bold py-2 px-6 rounded-full hover:bg-yellow-400 transition">
                    Search
                </button>
            </form>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-12">

        <!-- Category Filter Buttons -->
        <div class="flex flex-wrap items-center gap-2 mb-8 justify-center overflow-x-auto pb-4 md:pb-0">
            <a href="?category=all" class="px-4 py-2 rounded-full text-sm font-medium transition-all <?php echo $selected_category === 'all' ? 'bg-sac-blue text-white shadow-lg hover:shadow-xl' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">All Projects</a>
            <a href="?category=IoT%20%26%20Hardware" class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap <?php echo $selected_category === 'IoT & Hardware' ? 'bg-sac-blue text-white shadow-lg hover:shadow-xl' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">IoT & Hardware</a>
            <a href="?category=Software%20Development" class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap <?php echo $selected_category === 'Software Development' ? 'bg-sac-blue text-white shadow-lg hover:shadow-xl' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">Software Development</a>
            <a href="?category=Artificial%20Intelligence" class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap <?php echo $selected_category === 'Artificial Intelligence' ? 'bg-sac-blue text-white shadow-lg hover:shadow-xl' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">Artificial Intelligence</a>
            <a href="?category=Networking%20%26%20Security" class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap <?php echo $selected_category === 'Networking & Security' ? 'bg-sac-blue text-white shadow-lg hover:shadow-xl' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">Networking & Security</a>
            <a href="?category=Game%20Development" class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap <?php echo $selected_category === 'Game Development' ? 'bg-sac-blue text-white shadow-lg hover:shadow-xl' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">Game Development</a>
            <a href="?category=Management%20Information%20Systems" class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap <?php echo $selected_category === 'Management Information Systems' ? 'bg-sac-blue text-white shadow-lg hover:shadow-xl' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">Management Information Systems</a>
        </div>

        <!-- Statistics Cards -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <!-- Card 1 -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-sac-blue hover:shadow-xl transition transform hover:-translate-y-1">
                <h3 class="text-sac-blue font-semibold uppercase tracking-wider">Total Documents</h3>
                <p class="text-4xl font-bold text-sac-gold mt-2"><?php echo $stats['total_docs']; ?></p>
            </div>
            <!-- Card 2 -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-sac-blue hover:shadow-xl transition transform hover:-translate-y-1">
                <h3 class="text-sac-blue font-semibold uppercase tracking-wider">Advisers Indexed</h3>
                <p class="text-4xl font-bold text-sac-gold mt-2"><?php echo $stats['total_advisers']; ?></p>
            </div>
            <!-- Card 3 -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-sac-blue hover:shadow-xl transition transform hover:-translate-y-1">
                <h3 class="text-sac-blue font-semibold uppercase tracking-wider">Years Preserved</h3>
                <p class="text-4xl font-bold text-sac-gold mt-2"><?php echo date("Y") - 2018; ?> Years</p>
            </div>
        </section>

        <!-- Latest Submissions -->
        <section>
            <div class="flex items-center justify-between mb-8 border-b-2 border-gray-200 pb-2">
                <h2 class="text-2xl font-bold text-sac-blue">Latest Submissions</h2>
                <a href="search.php" class="text-sac-blue hover:text-sac-gold font-semibold">View All &rarr;</a>
            </div>

            <div class="space-y-4">
                <?php if (count($latest_docs) > 0): ?>
                    <?php foreach ($latest_docs as $doc): ?>
                        <div class="bg-white p-6 rounded-lg shadow border border-gray-100 hover:border-sac-gold transition duration-300">
                            <div class="flex flex-col md:flex-row justify-between md:items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <?php if (!empty($doc['category'])): ?>
                                            <span class="inline-block bg-sac-blue text-sac-gold text-xs px-3 py-1 rounded-full font-semibold">
                                                <?php echo htmlspecialchars($doc['category']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($doc['department'])): ?>
                                            <span class="inline-block bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded-full font-semibold">
                                                <?php echo htmlspecialchars($doc['department']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="text-xl font-bold text-sac-blue hover:underline">
                                        <a href="pages/search.php"><?php echo htmlspecialchars($doc['title']); ?></a>
                                    </h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <span class="font-semibold">Authors:</span> <?php echo htmlspecialchars($doc['authors']); ?> &bull;
                                        <span class="font-semibold">Year:</span> <?php echo htmlspecialchars($doc['year']); ?>
                                    </p>
                                    <p class="text-gray-600 mt-2 line-clamp-2"><?php echo htmlspecialchars($doc['abstract']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-10 bg-gray-100 rounded text-gray-500">
                        No documents currently available in the repository.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <!-- Footer -->
    <footer class="bg-cyber-dark text-white mt-auto py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; <?php echo date("Y"); ?> WS102 Final Period - BSIT 4</p>
            <p class="text-gray-400 text-sm mt-2">Preserving Institutional Excellence.</p>
        </div>
    </footer>
</body>

</html>