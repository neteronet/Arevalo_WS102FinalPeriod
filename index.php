<?php
include 'includes/db_config.php';
include 'includes/header.php';

// Initialize stats variables
$stats = ['total_docs' => 0, 'total_advisers' => 0];
$latest_docs = [];

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

    // 2. Fetch Latest 5 Submissions
    $sql_latest = "SELECT id, title, authors, year, department, abstract FROM capstones WHERE status='approved' ORDER BY date_submitted DESC LIMIT 5";
    $result_latest = $conn->query($sql_latest);
    
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

<!-- Hero Section -->
<header class="bg-sac-blue text-white py-20 border-b-4 border-sac-gold">
    <div class="max-w-4xl mx-auto text-center px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">SAC Cyberian Repository</h1>
        <p class="text-lg md:text-xl text-gray-200 mb-8">The official digital archive for institutional research and capstone projects.</p>
        
        <!-- Search Bar -->
        <form action="search.php" method="GET" class="relative max-w-2xl mx-auto">
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
                        <div class="flex flex-col md:flex-row justify-between md:items-center">
                            <div>
                                <h4 class="text-xl font-bold text-sac-blue hover:underline">
                                    <a href="#"><?php echo htmlspecialchars($doc['title']); ?></a>
                                </h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    <span class="font-semibold">Authors:</span> <?php echo htmlspecialchars($doc['authors']); ?> &bull; 
                                    <span class="font-semibold">Year:</span> <?php echo htmlspecialchars($doc['year']); ?>
                                </p>
                                <p class="text-gray-600 mt-2 line-clamp-2"><?php echo htmlspecialchars($doc['abstract']); ?></p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <span class="inline-block bg-gray-100 text-sac-blue text-xs px-3 py-1 rounded-full font-semibold">
                                    <?php echo htmlspecialchars($doc['department']); ?>
                                </span>
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

<?php 
// Close logic
if (isset($conn)) $conn->close();
include 'includes/footer.php'; 
?>