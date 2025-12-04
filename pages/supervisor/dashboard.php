<?php
session_start();
include '../../includes/connection.php';
include 'dashboard-header.php';

// Check if supervisor is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] ?? '') !== 'supervisor') {
    header("Location: login.php");
    exit;
}

// Fetch supervisor information
$user_id = $_SESSION['user_id'];
$supervisor = [
    'first_name' => '',
    'last_name'  => '',
    'supervisor_id' => '',
    'email'      => ''
];

$sql_user = "SELECT first_name, last_name, supervisor_id, email, department FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);

if ($stmt_user !== false) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user && $result_user->num_rows === 1) {
        $supervisor = $result_user->fetch_assoc();
    }
    $stmt_user->close();
}

// Fetch capstone submissions (all)
$sql_caps = "SELECT id, title, authors, year, department, status, updated_at, date_submitted 
             FROM capstones 
             ORDER BY date_submitted DESC";
$stmt_caps = $conn->prepare($sql_caps);

if ($stmt_caps !== false) {
    $stmt_caps->execute();
    $result_caps = $stmt_caps->get_result();
    $stmt_caps->close();
} else {
    $result_caps = new class {
        public $num_rows = 0;
        public function fetch_assoc() { return null; }
    };
}

// Calculate statistics
$total_items = 0;
$pending_review = 0;
$approved = 0;
$rejected = 0;

$sql_stats = "SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_review,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
FROM capstones";
$stmt_stats = $conn->prepare($sql_stats);

if ($stmt_stats !== false) {
    $stmt_stats->execute();
    $result_stats = $stmt_stats->get_result();
    if ($result_stats && $row = $result_stats->fetch_assoc()) {
        $total_items    = $row['total_items'] ?? 0;
        $pending_review = $row['pending_review'] ?? 0;
        $approved       = $row['approved'] ?? 0;
        $rejected       = $row['rejected'] ?? 0;
    }
    $stmt_stats->close();
}
?>

<!-- Main Content -->
<main class="flex-grow max-w-7xl mx-auto w-full px-4 py-12">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-sac-blue mb-2">
            Welcome, <?php echo htmlspecialchars($supervisor['first_name'] . ' ' . $supervisor['last_name']); ?>
        </h1>
        <p class="text-gray-600 text-lg">
            Review and manage student research and capstone submissions.
        </p>
        <p class="text-sm text-gray-500 mt-1">
            Supervisor ID: <span class="font-semibold"><?php echo htmlspecialchars($supervisor['supervisor_id'] ?? ''); ?></span>
            &middot;
            Department: <span class="font-semibold"><?php echo htmlspecialchars($supervisor['department'] ?? ''); ?></span>
        </p>
    </div>

    <!-- System Overview Card -->
    <div class="mb-10 bg-white border border-gray-200 rounded-lg shadow-sm p-6 flex flex-col md:flex-row gap-4">
        <div class="flex-shrink-0 hidden md:flex items-center justify-center w-14 h-14 rounded-full bg-sac-blue/10">
            <svg class="w-8 h-8 text-sac-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h4m10-12v6m0 4v-4m0 0l-3-3m3 3l3-3M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2m-2 0H9m0 0H5a2 2 0 00-2 2v0" />
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-sac-blue mb-1">
                SAC Cyberian Centralized Repository System
            </h2>
            <p class="text-gray-700 text-sm leading-relaxed">
                A centralized digital repository designed to store, organize, and manage research and capstone project
                documents of St. Anthony's College BSIT students, promoting accessibility, transparency, and
                long-term preservation of academic works.
            </p>
            <p class="text-gray-500 text-xs mt-2">
                As a supervisor, you play a key role in maintaining the quality and integrity of the repository by
                reviewing, approving, or rejecting student submissions.
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-sac-blue hover:shadow-xl transition">
            <h3 class="text-sac-blue font-semibold uppercase tracking-wider text-sm">Total Submissions</h3>
            <p class="text-4xl font-bold text-sac-gold mt-2"><?php echo $total_items; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500 hover:shadow-xl transition">
            <h3 class="text-yellow-700 font-semibold uppercase tracking-wider text-sm">Pending Review</h3>
            <p class="text-4xl font-bold text-yellow-500 mt-2"><?php echo $pending_review; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 hover:shadow-xl transition">
            <h3 class="text-green-700 font-semibold uppercase tracking-wider text-sm">Approved</h3>
            <p class="text-4xl font-bold text-green-500 mt-2"><?php echo $approved; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500 hover:shadow-xl transition">
            <h3 class="text-red-700 font-semibold uppercase tracking-wider text-sm">Rejected</h3>
            <p class="text-4xl font-bold text-red-500 mt-2"><?php echo $rejected; ?></p>
        </div>
    </div>

    <!-- Submissions List -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-sac-blue">All Student Submissions</h2>
            <span class="text-sm text-gray-500">Most recent first</span>
        </div>

        <?php if ($result_caps->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Title</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Authors</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Year</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Department</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cap = $result_caps->fetch_assoc()): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($cap['title']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($cap['authors']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($cap['year']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($cap['department']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                    $status_class = 'status-draft';
                                    $status_text = ucfirst($cap['status']);

                                    if ($cap['status'] === 'pending') {
                                        $status_class = 'status-pending';
                                    } elseif ($cap['status'] === 'approved') {
                                        $status_class = 'status-approved';
                                    } elseif ($cap['status'] === 'rejected') {
                                        $status_class = 'status-draft';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php
                                    $date = new DateTime($cap['updated_at']);
                                    echo $date->format('m/d/Y');
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-6 text-center">
                <p class="text-gray-500 text-lg mb-4">There are currently no submissions in the system.</p>
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


