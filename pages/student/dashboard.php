<?php
session_start();
include '../../includes/connection.php';
include 'dashboard-header.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$user = [
    'first_name' => '',
    'last_name'  => '',
    'student_id' => '',
    'email'      => ''
];

$sql_user = "SELECT first_name, last_name, student_id, email FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);

if ($stmt_user !== false) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user && $result_user->num_rows === 1) {
        $user = $result_user->fetch_assoc();
    }
    $stmt_user->close();
}

// Fetch user submissions
$sql_submissions = "SELECT id, title, authors, status, updated_at, date_submitted 
                    FROM capstones 
                    WHERE student_id = ? 
                    ORDER BY date_submitted DESC";
$stmt_submissions = $conn->prepare($sql_submissions);

if ($stmt_submissions !== false) {
    $stmt_submissions->bind_param("i", $user_id);
    $stmt_submissions->execute();
    $result_submissions = $stmt_submissions->get_result();
    $stmt_submissions->close();
} else {
    // Fallback to empty result-like structure
    $result_submissions = new class {
        public $num_rows = 0;
        public function fetch_assoc() { return null; }
    };
}

// Calculate statistics
$total_items = 0;
$pending_review = 0;
$published = 0;

$sql_stats = "SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_review,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as published
FROM capstones WHERE student_id = ?";
$stmt_stats = $conn->prepare($sql_stats);

if ($stmt_stats !== false) {
    $stmt_stats->bind_param("i", $user_id);
    $stmt_stats->execute();
    $result_stats = $stmt_stats->get_result();
    if ($result_stats && $row = $result_stats->fetch_assoc()) {
        $total_items = $row['total_items'] ?? 0;
        $pending_review = $row['pending_review'] ?? 0;
        $published = $row['published'] ?? 0;
    }
    $stmt_stats->close();
}
?>

<!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 py-12">
        <!-- Welcome Section -->
        <div class="mb-10">
            <h1 class="text-4xl font-bold text-sac-blue mb-2">Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
            <p class="text-gray-600 text-lg">Manage your research and capstone projects.</p>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8 flex flex-col sm:flex-row gap-4">
            <a href="new_submission.php" class="inline-flex items-center justify-center px-6 py-3 bg-sac-blue text-white font-bold rounded-lg hover:bg-blue-800 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Submission
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Total Items -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-sac-blue hover:shadow-xl transition">
                <h3 class="text-sac-blue font-semibold uppercase tracking-wider text-sm">Total Items</h3>
                <p class="text-4xl font-bold text-sac-gold mt-2"><?php echo $total_items; ?></p>
            </div>
            <!-- Pending Review -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500 hover:shadow-xl transition">
                <h3 class="text-yellow-700 font-semibold uppercase tracking-wider text-sm">Pending Review</h3>
                <p class="text-4xl font-bold text-yellow-500 mt-2"><?php echo $pending_review; ?></p>
            </div>
            <!-- Published -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 hover:shadow-xl transition">
                <h3 class="text-green-700 font-semibold uppercase tracking-wider text-sm">Published</h3>
                <p class="text-4xl font-bold text-green-500 mt-2"><?php echo $published; ?></p>
            </div>
        </div>

        <!-- Submissions List -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-sac-blue">Your Submissions</h2>
            </div>

            <?php if ($result_submissions->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Title</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Authors</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Updated</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($submission = $result_submissions->fetch_assoc()): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($submission['title']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($submission['authors']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php 
                                        $status_class = 'status-draft';
                                        $status_text = ucfirst($submission['status']);
                                        
                                        if ($submission['status'] === 'pending') {
                                            $status_class = 'status-pending';
                                        } elseif ($submission['status'] === 'approved') {
                                            $status_class = 'status-approved';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php 
                                        $date = new DateTime($submission['updated_at']);
                                        echo $date->format('m/d/Y');
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="view_submission.php?id=<?php echo $submission['id']; ?>" class="text-sac-blue hover:text-sac-gold font-semibold transition">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-6 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg mb-4">You haven't submitted any projects yet.</p>
                    <a href="new_submission.php" class="inline-block px-6 py-2 bg-sac-blue text-white font-bold rounded-lg hover:bg-blue-800 transition">
                        Create Your First Submission
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-cyber-dark text-white mt-auto py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; <?php echo date("Y"); ?> WS102 Final Period - BSIT 4</p>
            <p class="text-gray-400 text-sm mt-2">Preserving Institutional Excellence.</p>
        </div>
    </footer>

    <?php
    if (isset($conn)) $conn->close();
    ?>
</body>

</html>
