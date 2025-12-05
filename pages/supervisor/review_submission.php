<?php
session_start();
include '../../includes/connection.php';
include 'dashboard-header.php';

// Ensure supervisor is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] ?? '') !== 'supervisor') {
    header("Location: login.php");
    exit;
}

$review_error = '';
$review_success = '';

// Get submission ID
$capstone_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($capstone_id <= 0) {
    $review_error = 'Invalid submission ID.';
} else {
    // Handle approve/reject actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $allowed = ['approve', 'reject'];

        if (!in_array($action, $allowed, true)) {
            $review_error = 'Invalid action.';
        } else {
            $new_status = $action === 'approve' ? 'approved' : 'rejected';

            $sql_update = "UPDATE capstones SET status = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update === false) {
                $review_error = 'Database error while updating status.';
            } else {
                $stmt_update->bind_param("si", $new_status, $capstone_id);

                if ($stmt_update->execute()) {
                    $review_success = $new_status === 'approved'
                        ? 'Submission has been approved successfully.'
                        : 'Submission has been rejected.';
                } else {
                    $review_error = 'Failed to update submission status. Please try again.';
                }

                $stmt_update->close();
            }
        }
    }

    // Fetch capstone details
    $capstone = null;
    $sql_cap = "SELECT title, authors, year, department, category, adviser, keywords, abstract, status, pdf_path, date_submitted 
                FROM capstones 
                WHERE id = ?";
    $stmt_cap = $conn->prepare($sql_cap);

    if ($stmt_cap !== false) {
        $stmt_cap->bind_param("i", $capstone_id);
        $stmt_cap->execute();
        $result_cap = $stmt_cap->get_result();

        if ($result_cap && $result_cap->num_rows === 1) {
            $capstone = $result_cap->fetch_assoc();
        } else {
            $review_error = 'Submission not found.';
        }

        $stmt_cap->close();
    } else {
        $review_error = 'Database error while fetching submission.';
    }
}
?>

<?php if (!empty($review_success)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Status Updated',
                html: '<?php echo addslashes($review_success); ?>',
                confirmButtonText: 'Back to Dashboard',
                confirmButtonColor: '#0A3D62'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'dashboard.php';
                }
            });
        });
    </script>
<?php endif; ?>

<main class="flex-grow max-w-4xl mx-auto w-full px-4 py-12">
    <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-sac-blue">Review Submission</h1>
            <a href="dashboard.php" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back to Dashboard</a>
        </div>

        <?php if (!empty($review_error)): ?>
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                <p class="font-semibold text-sm"><?php echo htmlspecialchars($review_error); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($capstone): ?>
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

                <?php if (!empty($capstone['pdf_path'])): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Attached PDF</h3>
                        <a href="../../<?php echo htmlspecialchars($capstone['pdf_path']); ?>" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 rounded-md bg-sac-blue text-white text-xs font-semibold hover:bg-blue-800 transition">
                            View PDF
                        </a>
                    </div>
                <?php endif; ?>

                <div class="pt-4 mt-4 border-t border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Current Status:</span>
                        <span class="ml-1 capitalize"><?php echo htmlspecialchars($capstone['status']); ?></span>
                    </div>

                    <div class="flex gap-3">
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                                Approve
                            </button>
                        </form>
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
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


