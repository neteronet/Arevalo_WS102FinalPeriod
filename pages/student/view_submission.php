<?php
session_start();
include '../../includes/connection.php';
include 'dashboard-header.php';

// Require logged-in student
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] ?? 'student') !== 'student') {
    header("Location: login.php");
    exit;
}

$view_error = '';

// Get submission ID and ensure it belongs to current student
$capstone_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$student_id = $_SESSION['user_id'];

if ($capstone_id <= 0) {
    $view_error = 'Invalid submission ID.';
} else {
    $sql = "SELECT title, authors, year, department, category, adviser, keywords, abstract, status, pdf_path, date_submitted, updated_at
            FROM capstones
            WHERE id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $view_error = 'Database error while fetching submission.';
    } else {
        $stmt->bind_param("ii", $capstone_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $capstone = $result->fetch_assoc();
        } else {
            $view_error = 'Submission not found.';
        }

        $stmt->close();
    }
}
?>

<main class="flex-grow max-w-4xl mx-auto w-full px-4 py-12">
    <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-sac-blue">View Submission</h1>
            <a href="dashboard.php" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back to Dashboard</a>
        </div>

        <?php if (!empty($view_error)): ?>
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                <p class="font-semibold text-sm"><?php echo htmlspecialchars($view_error); ?></p>
            </div>
        <?php elseif (!empty($capstone)): ?>
            <div class="space-y-4">
                <div>
                    <h2 class="text-xl font-semibold text-sac-blue mb-1">
                        <?php echo htmlspecialchars($capstone['title']); ?>
                    </h2>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">Authors:</span> <?php echo htmlspecialchars($capstone['authors']); ?> &middot;
                        <span class="font-semibold">Year:</span> <?php echo htmlspecialchars($capstone['year']); ?> &middot;
                        <span class="font-semibold">Department:</span> <?php echo htmlspecialchars($capstone['department']); ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="font-semibold">Category:</span> <?php echo htmlspecialchars($capstone['category']); ?> &middot;
                        <span class="font-semibold">Adviser:</span> <?php echo htmlspecialchars($capstone['adviser'] ?? 'N/A'); ?>
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Abstract</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($capstone['abstract'])); ?>
                    </p>
                </div>

                <?php if (!empty($capstone['keywords'])): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Keywords</h3>
                        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($capstone['keywords']); ?></p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200 mt-4">
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Status:</span>
                        <span class="ml-1 capitalize"><?php echo htmlspecialchars($capstone['status']); ?></span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Submitted:</span>
                        <?php
                        $submitted = new DateTime($capstone['date_submitted']);
                        echo $submitted->format('m/d/Y');
                        ?>
                        &middot;
                        <span class="font-semibold">Last updated:</span>
                        <?php
                        $updated = new DateTime($capstone['updated_at']);
                        echo $updated->format('m/d/Y');
                        ?>
                    </div>
                </div>

                <?php if (!empty($capstone['pdf_path'])): ?>
                    <div class="pt-2">
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Attached PDF</h3>
                        <a href="../../<?php echo htmlspecialchars($capstone['pdf_path']); ?>" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 rounded-md bg-sac-blue text-white text-xs font-semibold hover:bg-blue-800 transition">
                            View PDF
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

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


